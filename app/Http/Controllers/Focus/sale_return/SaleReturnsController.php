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
namespace App\Http\Controllers\Focus\sale_return;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\quote\Quote;
use App\Models\sale_return\SaleReturn;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\sale_return\SaleReturnRepository;
use Illuminate\Http\Request;

class SaleReturnsController extends Controller
{
    /**
     * variable to store the repository object
     * @var SaleReturnRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SaleReturnRepository $repository ;
     */
    public function __construct(SaleReturnRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.sale_returns.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = SaleReturn::max('tid')+1;
        $customers = Customer::whereHas('invoices')->get(['id', 'company', 'name']);
        $warehouses = Warehouse::get();
        
        return view('focus.sale_returns.create', compact('tid', 'customers', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Sale Return', $th);
        }
    
        return new RedirectResponse(route('biller.sale_returns.index'), ['flash_success' => 'Sale Return Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  SaleReturn $sale_return
     * @return \Illuminate\Http\Response
     */
    public function edit(SaleReturn $sale_return)
    {
        $tid = $sale_return->tid;
        $customers = Customer::whereHas('invoices')->get(['id', 'company', 'name']);
        $warehouses = Warehouse::get();

        return view('focus.sale_returns.edit', compact('sale_return', 'tid', 'customers', 'warehouses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  SaleReturn $sale_return
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SaleReturn $sale_return)
    {
        try {
            $this->repository->update($sale_return, $request->except('_token', '_method'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Sale Return', $th);
        }

        return new RedirectResponse(route('biller.sale_returns.index'), ['flash_success' => 'Sale Return Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  SaleReturn $sale_return
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaleReturn $sale_return)
    {
        try {
            $this->repository->delete($sale_return);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Sale Return', $th);
        }

        return new RedirectResponse(route('biller.sale_returns.index'), ['flash_success' => 'Sale Return Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  SaleReturn $sale_return
     * @return \Illuminate\Http\Response
     */
    public function show(SaleReturn $sale_return)
    {
        return view('focus.sale_returns.view', compact('sale_return'));
    }

    /**
     * Fetch client invoices
     */
    public function select_quotes(Request $request)
    {
        $w = $request->search; 
        $invoices = Quote::when(request('reference'), fn($q) => request('reference') == 'proforma'? $q->where('bank_id', '>', 0) : $q->where('bank_id', 0))
        ->whereHas('currency', fn($q) => $q->where('rate', 1))
        ->whereHas('stock_issues')
        ->where('customer_id', $request->customer_id)
        ->where(fn($q) => $q->where('notes', 'LIKE', "%{$w}%")->orWhere('tid', 'LIKE', "%{$w}%"))
        ->limit(6)
        ->get()
        ->map(function($v) {
            $v->notes = gen4tid($v->bank_id? 'PI-' : 'QT-', $v->tid) . ' ' . $v->notes;
            return $v;
        });
            
        return response()->json($invoices);
    }

    /**
     * Fetch client invoices
     */
    public function select_invoices(Request $request)
    {
        $w = $request->search; 
        $invoices = Invoice::whereHas('currency', fn($q) => $q->where('rate', 1))
        ->whereHas('stock_issues')
        ->where('customer_id', $request->customer_id)
        ->where(fn($q) => $q->where('notes', 'LIKE', "%{$w}%")->orWhere('tid', 'LIKE', "%{$w}%"))
        ->limit(6)
        ->get()
        ->map(function($v) {
            $v->notes = gen4tid('INV-', $v->tid) . ' ' . $v->notes;
            return $v;
        });
            
        return response()->json($invoices);
    }

    /**
     * Invoice Stock Items
     */
    public function issued_stock_items(Request $request)
    {
        $productvars = [];
        // invoice stock items
        $invoice = Invoice::find(request('invoice_id'));
        if ($invoice && $invoice->products) {
            $quote_ids = $invoice->products->pluck('quote_id')->toArray();
            $quote_ids = array_unique($quote_ids);
            foreach ($invoice->products as $inv_product) {
                // verification invoice
                if ($inv_product->quote_id) {
                    $quote = $inv_product->quote;
                    if ($quote) {
                        foreach ($quote->verified_products as $verified_prod) {
                            $productvar = $verified_prod->product_variation;
                            if ($productvar) {
                                $productvar['verified_item_id'] = $verified_prod->id;
                                $productvar['uom'] = @$productvar->product->unit->code;
                                $productvars[] = $productvar;
                            }
                        }
                    }
                    if (count($quote_ids) == 1) break;
                } 
                // non-verification invoice (detached)
                elseif ($inv_product->product_id) {
                    $productvar = $inv_product->product_variation;
                    if ($productvar) {
                        $productvar['verified_item_id'] = null;
                        $productvar['uom'] = @$productvar->product->unit->code;
                        $productvars[] = $productvar;
                    }
                }
            }
        }
        // quote / pi stock items
        $quote = Quote::find(request('quote_id'));
        if ($quote) {
            foreach ($quote->products as $quote_prod) {
                $productvar = $quote_prod->variation;
                if ($productvar) {
                    $productvar['verified_item_id'] = null;
                    $productvar['uom'] = @$productvar->product->unit->code;
                    $productvars[] = $productvar;
                }
            }
        }

        return response()->json($productvars);
    }
}
