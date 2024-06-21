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

namespace App\Http\Controllers\Focus\purchaseorder;

use App\Models\PurchaseClass\PurchaseClass;
use App\Models\purchaseorder\Purchaseorder;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\purchaseorder\EditResponse;
use App\Repositories\Focus\purchaseorder\PurchaseorderRepository;

use App\Http\Requests\Focus\purchaseorder\StorePurchaseorderRequest;
use App\Http\Responses\Focus\purchaseorder\CreateResponse;
use App\Http\Responses\RedirectResponse;
use App\Models\supplier\Supplier;
use Request;
use Illuminate\Validation\ValidationException;

/**
 * PurchaseordersController
 */
class PurchaseordersController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseorderRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseorderRepository $repository ;
     */
    public function __construct(PurchaseorderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        $suppliers = Supplier::whereHas('purchase_orders')->get(['id', 'name']);

        return new ViewResponse('focus.purchaseorders.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatePurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\CreateResponse
     */
    public function create(StorePurchaseorderRequest $request)
    {
        return new CreateResponse('focus.purchaseorders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StorePurchaseorderRequest $request)
    {
       // dd($request->all());
        // extract input fields
        $order = $request->only([
            'supplier_id', 'tid', 'date', 'due_date', 'term_id', 'project_id', 'note', 'tax',
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'project_milestone', 'purchase_class',
        ]);
        $order_items = $request->only([
            'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type','product_code','warehouse_id', 'item_purchase_class',
            'product_id', 'supplier_product_id'
        ]);

        if (!empty($order['project'])) $order['purchase_class'] = '';
        if (!empty($order['itemproject_id'])) $order['item_purchase_class'] = '';

        $order['ins'] = auth()->user()->ins;
        $order['user_id'] = auth()->user()->id;
        // modify and filter items without item_id
        $order_items = modify_array($order_items);
        $order_items = array_filter($order_items, function ($v) { return $v['item_id']; });

        try {
            $result = $this->repository->create(compact('order', 'order_items'));
        } catch (\Throwable $th) {
            dd($th);
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Purchase Order', $th);
        }

        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => 'Purchase Order created successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @param EditPurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\EditResponse
     */
    public function edit(Purchaseorder $purchaseorder)
    {
        return new EditResponse($purchaseorder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StorePurchaseorderRequest $request, Purchaseorder $purchaseorder)
    {
        // update purchase order closure status
        if ($request->exists('closure_status')) {
            $purchaseorder->update($request->only('closure_status', 'closure_reason'));
            return redirect()->back()->with('flash_success', 'Closure Status Updated Successfully');
        }
        
        // extract input fields
        $order = $request->only([
            'supplier_id', 'tid', 'date', 'due_date', 'term_id', 'project_id', 'note', 'tax',
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'project_milestone', 'purchase_class',
        ]);
        $order_items = $request->only([
            'id', 'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type','product_code','warehouse_id',
            'product_id', 'supplier_product_id'
        ]);

        $order['ins'] = auth()->user()->ins;
        $order['user_id'] = auth()->user()->id;
        // modify and filter items without item_id
        $order_items = modify_array($order_items);
        $order_items = array_filter($order_items, function ($val) { return $val['item_id']; });

        
        
        try {
            $result = $this->repository->update($purchaseorder, compact('order', 'order_items'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Purchase Order', $th);
        }

        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => 'Purchase Order updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Purchaseorder $purchaseorder)
    {
        try {
            $this->repository->delete($purchaseorder);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Purchase Order', $th);
        }

        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => 'Purchase Order deleted successfully']);        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Purchaseorder $purchaseorder)
    {   
        return new ViewResponse('focus.purchaseorders.view', compact('purchaseorder'));
    }

    /**
     * Purchase Order Goods
     */
    public function goods(Request $request)
    {
        $purchaseorder = Purchaseorder::find(request('purchaseorder_id'));
        $stock_goods = $purchaseorder? $purchaseorder->goods()->where('type', 'Stock')->get() : collect();
        $stock_goods = $stock_goods->map(function($v) {
            if ($v->productvariation) {
                $v->description .= " - {$v->productvariation->code}";
                $v->product_code = $v->productvariation->code;
            }
            if ($v->project){
                $quote_tid = !$v->project->quote ?: gen4tid('QT-', $v->project->quote->tid);
                $customer = !$v->project->customer ?: $v->project->customer->company;
                $branch = !$v->project->branch ?: $v->project->branch->name;
                $project_tid = gen4tid('PRJ-', $v->project->tid);
                $project = $v->project->name;
                $customer_branch = "{$customer}" .'-'. "{$branch}";
                //
                $v['project_tid'] = "[" . $quote_tid ."]"." - " . $customer_branch. " - ".$project_tid." - ".$project;
            }else{
                $v->project_tid = '';
            }
             $v->project_id = Purchaseorder::find($v->purchaseorder_id)->project->id ?? '';
             $v->project_name= Purchaseorder::find($v->purchaseorder_id)->project->name ?? '';
            return $v;
        });

        return response()->json($stock_goods);
    }
}
