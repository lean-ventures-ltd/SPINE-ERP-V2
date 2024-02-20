<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\product;

use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\warehouse\Warehouse;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\product\CreateResponse;
use App\Http\Responses\Focus\product\CreateModalResponse;
use App\Http\Responses\Focus\product\EditResponse;
use App\Repositories\Focus\product\ProductRepository;
use App\Http\Requests\Focus\product\ManageProductRequest;
use App\Http\Requests\Focus\product\CreateProductRequest;
use App\Http\Requests\Focus\product\EditProductRequest;
use App\Models\client_product\ClientProduct;
use App\Models\supplier_product\SupplierProduct;
use App\Models\product\ProductMeta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * ProductsController
 */
class ProductsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductRepository $repository ;
     */
    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\product\ManageProductRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageProductRequest $request)
    {
        $warehouses = Warehouse::get(['id', 'title']);
        $categories = Productcategory::get(['id', 'title']);

        return new ViewResponse('focus.products.index', compact('warehouses', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductRequestNamespace $request
     * @return \App\Http\Responses\Focus\product\CreateResponse
     */
    public function create(CreateProductRequest $request)
    {
        return new CreateResponse('focus.products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateProductRequest $request)
    {
        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) { dd($th);
            if ($th instanceof ValidationException) throw $th;
            return errorHandler($th, 'Error Creating Product');
        }

        return new RedirectResponse(route('biller.products.index'), ['flash_success' => trans('alerts.backend.products.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\product\Product $product
     * @param EditProductRequestNamespace $request
     * @return \App\Http\Responses\Focus\product\EditResponse
     */
    public function edit(Product $product, EditProductRequest $request)
    {
        return new EditResponse($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequestNamespace $request
     * @param App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(EditProductRequest $request, Product $product)
    {
        try {
            $this->repository->update($product, $request->except(['_token']));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler($th, 'Error Updating Product');
        }

        return new RedirectResponse(route('biller.products.index'), ['flash_success' => trans('alerts.backend.products.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Product $product)
    {
        try {
            $this->repository->delete($product);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler($th, 'Error Deleting Product');
        }

        return json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.products.deleted')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductRequestNamespace $request
     * @param App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Product $product, ManageProductRequest $request)
    {
        return new ViewResponse('focus.products.view', compact('product'));
    }

    /**
     * Quote or PI searchable product drop down options
     */
    public function quote_product_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        // fetch pricelist customer products
        if ($request->price_customer_id) {
            $products = ClientProduct::where('customer_id', request('price_customer_id'))
                ->where('descr', 'LIKE', '%'. request('keyword') .'%')->limit(6)->get()
                ->map(function ($v) {
                    $value = $v->row_num > 0 ? "($v->row_num)" : '';
                    return $v->fill([
                        'name' => "{$v->descr} {$value}",
                        'unit' => $v->uom,
                        'price' => $v->rate,
                        'purchase_price' => $v->variation ? $v->variation->purchase_price : 0,
                    ]);
                });

            return response()->json($products);
        }

        // fetch inventory products
        $productvariations = ProductVariation::where(function ($q) {
            $q->whereHas('product', function ($q) {
                $q->where('name', 'LIKE', '%' . request('keyword') . '%')->orWhere('code', 'LIKE', '%' . request('keyword') . '%');
            })->orWhere('name', 'LIKE', '%' . request('keyword') . '%');
        })
        ->with(['warehouse' => fn($q) => $q->select(['id', 'title'])])
        ->with('product')->limit(6)->get()->unique('name');
        
        $products = [];
        foreach ($productvariations as $row) {
            $product = array_intersect_key($row->toArray(), array_flip([
                'id', 'product_id', 'name', 'code', 'qty', 'image', 'purchase_price', 'price', 'alert'
            ]));
            $product = array_replace($product, [
                'taxrate' => @$row->product->taxrate,
                'product_des' => @$row->product->product_des,
                'units' => $row->product? $row->product->units->toArray(): [],
                'warehouse' => $row->warehouse? $row->warehouse->toArray() : [],
            ]);
            // set purchase price using inventory valuation (LIFO) method
            $product['purchase_price'] = $this->repository->eval_purchase_price($row->id, $row->qty, $row->purchase_price);

            // product qty
            $product['qty'] = 0;
            $warehouses = Warehouse::whereHas('products', fn($q) => $q->where('name', 'LIKE', "%{$row->name}%"))
            ->with(['products' => fn($q) => $q->where('name', 'LIKE', "%{$row->name}%")])
            ->get();
            foreach ($warehouses as $key1 => $wh) {
                $product['qty'] += $wh->products->sum('qty');
                $warehouses[$key1]['products_qty'] = $wh->products->sum('qty');
                unset($warehouses[$key1]['products']);
            }
            $product['warehouses'] = $warehouses;

            $products[] =  $product;
        }
        
        return response()->json($products);
    }

    public function purchase_search(Request $request)
    {
       // return 'dd';
        if (!access()->allow('product_search')) return false;

        // fetch pricelist customer products
        if ($request->pricegroup_id) {
            $products = SupplierProduct::where('supplier_id', request('pricegroup_id'))
                ->where('descr', 'LIKE', '%'. request('keyword') .'%')->limit(6)->get()
                ->map(function ($v) {
                    $item = '';
                    if ($v->row_num) {
                        $item = "({$v->row_num})";
                    }
                    return $v->fill([
                        'name' => "{$v->descr} {$item}",
                        'unit' => $v->uom,
                        'price' => $v->rate,
                        'purchase_price' => $v->rate,
                    ]);
                });

            return response()->json($products);
        }

        // fetch inventory products
        $productvariations = ProductVariation::whereHas('product', function ($q) {
            $q->where('name', 'LIKE', '%' . request('keyword') . '%');
        })->with(['warehouse' => function ($q) {
            $q->select(['id', 'title']);
        }])->with('product')->limit(6)->get()->unique('name');
        
        $products = array();
        foreach ($productvariations as $row) {
            $product = array_intersect_key($row->toArray(), array_flip([
                'id', 'product_id', 'name', 'code', 'qty', 'image', 'purchase_price', 'price', 'alert'
            ]));
            $product = $product + [
                'product_des' => $row->product->product_des,
                'units' => $row->product->units,
                'warehouse' => $row->warehouse->toArray()
            ];
            // purchase price set by inventory valuation (LIFO) method
            $product['purchase_price'] = $this->repository->eval_purchase_price($row->id, $row->qty, $row->purchase_price);
                
            $products[] =  $product;
        }

        return response()->json($products);
    }

    // 
    public function product_sub_load(Request $request)
    {
        $q = $request->get('id');
        $result = \App\Models\productcategory\Productcategory::all()->where('c_type', '=', 1)->where('rel_id', '=', $q);

        return json_encode($result);
    }

    // 
    public function quick_add(CreateProductRequest $request)
    {
        return new CreateModalResponse('focus.modal.product');
    }

    /**
     * Point of Sale
     */
    public function pos(Request $request, $bill_type)
    {
        if (!access()->allow('pos')) return false;
        
        $input = $request->except('_token');
        $limit = $request->post('search_limit', 20);
        $bill_type = $request->bill_type ?: $request->type;

        if ($bill_type == 'label' && isset($input['product']['term']))
            $input['keyword'] = $input['product']['term'];

        if ($input['serial_mode'] == 1 && $input['keyword']) {
            $products = ProductMeta::where('value', 'LIKE', '%' . $input['keyword'] . '%')
                ->whereNull('value2')
                ->whereHas('product_serial', function ($q) use ($input) {
                    if ($input['wid'] > 0) $q->where('warehouse_id', $input['wid']);
                })->with(['product_standard'])->limit($limit)->get();

            $output = array();
            foreach ($products as $row) {
                $serial_product = $row->product_serial;
                $stock_product = $serial_product->product;
                $output[] = [
                    'name' => $stock_product->name, 
                    'disrate' => $serial_product->disrate, 
                    'purchase_price' => $this->repository->eval_purchase_price(
                        $stock_product->id, $stock_product->qty, $stock_product->purchase_price
                    ),
                    'price' => $serial_product['price'], 
                    'id' => $serial_product['id'], 
                    'taxrate' => $stock_product['taxrate'], 
                    'product_des' => $stock_product['product_des'], 
                    'units' => $stock_product['units'], 
                    'code' => $serial_product['code'], 
                    'alert' => $serial_product['qty'], 
                    'image' => $serial_product['image'], 
                    'serial' => $row->value,
                ];
            }
        } else {
            $products = ProductVariation::whereHas('product', function ($q) use ($input) {
                $q->where('name', 'LIKE', '%' . $input['keyword'] . '%');
                if ($input['cat_id'] > 0) $q->where('productcategory_id', $input['cat_id']);
            })->when($input['wid'] > 0, function ($q) use ($input) {
                $q->where('warehouse_id', $input['wid']);
            })->limit($limit)->get();

            $output = array();
            foreach ($products as $row) {
                $output[] = [
                    'name' => $row->name ?: $row->product->name,
                    'disrate' => numberFormat($row->disrate),
                    'purchase_price' => $this->repository->eval_purchase_price(
                        $row->id, $row->qty, $row->purchase_price
                    ),
                    'price' => numberFormat($row->price), 
                    'id' => $row->id, 
                    'taxrate' => numberFormat($row->product['taxrate']), 
                    'product_des' => $row->product['product_des'], 
                    'units' => $row->product->units, 
                    'code' => $row->code, 
                    'alert' => $row->qty, 
                    'image' => $row->image, 
                    'serial' => '',
                ];
            }
        }
        
        return view('focus.products.partials.pos')->withDetails($output);
    }
    public function view($code)
    {
        $supplier_pricelist = SupplierProduct::where('product_code', $code)->get();
        return view('focus.products.view_pricelist', compact('supplier_pricelist'));
    }

    public function deleteMultipleProducts()
    {

        $toDelete = [
            1839, 1853, 1911, 1988, 1989, 1990, 2188, 2270, //DP SWITCHES
            2586, 2354, 2300, 2219, 1794, 1691, //COPPER PIPES 1/4
            2545, 2220, 2206, 2152, 2063, 2062, 1950, 1791, 1728, //COPPER PIPES 3/8
            2544, 2543, 1932, 2451, 2224, 1432, //ARMAFLEX 3/8
            2678, 2588, 2322, 1838, 1787, //ARMAFLEX 1/4
            ];

        $missingProdVarEntries = [];
        $missingProdEntries = [];

//        $recoveredProducts = [];

        try{
            DB::beginTransaction();

            ProductVariation::withTrashed()->restore();
            Product::withTrashed()->restore();

//            foreach ($toDelete as $id) {
//
//                $model = ProductVariation::where('id', $id)->first();
//
//                !empty($model) ?
//                    $model->delete()
//                    : array_push($missingProdVarEntries, "Missing ProdVar ID " . $id);
//
//
//                $model2 = Product::where('id', $id)->first();
//
//                !empty($model2) ?
//                    $model2->delete()
//                    : array_push($missingProdEntries, "Missing Product ID " . $id);
//
//
//            }

//            $recProducts = $this->recs();
//
//            foreach ($recProducts as $rec){
//                $prod = new Product();
//
//                $prod->fill($rec);
//
//                $prod->save();
//
//                array_push($recoveredProducts, "Product " . $rec['id'] . " recovered successfully!");
//
//            }

            DB::commit();

        }catch(Exception $exception){

            DB::rollBack();
            return
                ['message' => $exception->getMessage()];
        }


        return [
            'success' => "BULK DELETE SUCCESS!!!",
            'Missing Product variations' => $missingProdVarEntries,
            'Missing Products' => $missingProdEntries,
        ];
    }

    public function clearNegativeQuantities(){

        $negProducts = ProductVariation::where('qty', '<' , 0)->get();

        foreach ($negProducts as $np){

            $np->qty = 0;
            $np->save();
        }


        return ProductVariation::all();
    }




}
