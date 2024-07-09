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
namespace App\Http\Controllers\Focus\stock_issue;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\invoice\Invoice;
use App\Models\product\ProductVariation;
use App\Models\project\BudgetItem;
use App\Models\project\Project;
use App\Models\quote\Quote;
use App\Models\stock_issue\StockIssue;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\stock_issue\StockIssueRepository;
use Illuminate\Http\Request;

class StockIssuesController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockIssueRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockIssueRepository $repository ;
     */
    public function __construct(StockIssueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.stock_issues.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::whereHas('invoices')
            ->orWhereHas('projects')
            ->get(['id', 'company', 'name']);

        $employees = Hrm::where([
            'customer_id' => null,
            'supplier_id' => null,
            'client_vendor_id' => null,
            'client_user_id' => null,
        ])->get(['id', 'first_name', 'last_name']);

        // project status - 16 (continuing)
        $projects = Project::where('status', 16)
        ->with('quotes')
        ->get(['id', 'tid', 'name'])
        ->map(function($v) {
            $v['quote_ids'] = $v->quotes->pluck('id')->toArray();
            unset($v['quotes']);
            return $v;
        });

        $quotes = Quote::whereNotNull('approved_date')
            ->whereNotNull('approved_method')
            ->whereNotNull('approved_by')
            ->get(['id', 'notes', 'tid', 'bank_id', 'customer_id']);
        
        $accounts = Account::where('account_type', 'Expense')->get(['id', 'number', 'holder', 'account_type']);

        return view('focus.stock_issues.create', compact('customers', 'employees', 'projects', 'quotes', 'accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Exception $ex) {
            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];
        }

        return new RedirectResponse(route('biller.stock_issues.index'), ['flash_success' => 'Stock Issue Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StockIssue $stock_issue
     * @return \Illuminate\Http\Response
     */
    public function edit(StockIssue $stock_issue)
    {
        $customers = Customer::whereHas('invoices')
        ->orWhereHas('projects')
        ->get(['id', 'company', 'name']);
        $employees = Hrm::where([
            'customer_id' => null,
            'supplier_id' => null,
            'client_vendor_id' => null,
            'client_user_id' => null,
        ])->get(['id', 'first_name', 'last_name']);
        // project status - continuing
        $projects = Project::where('status', 16)
            ->with('quotes')
            ->get(['id', 'tid', 'name'])
            ->map(function($v) {
                $v['quote_ids'] = $v->quotes->pluck('id')->toArray();
                unset($v['quotes']);
                return $v;
            });

        $quotes = Quote::whereNotNull('approved_date')
            ->whereNotNull('approved_method')
            ->whereNotNull('approved_by')
            ->get(['id', 'notes', 'tid', 'bank_id', 'customer_id']);
        $accounts = Account::where('account_type', 'Expense')->get(['id', 'number', 'holder', 'account_type']);

        $qt = Quote::find($stock_issue->quote_id);

        $budgetDetails = [];

        $budget = $qt ? $qt->budget : [];

        if ($budget) {

            foreach ($stock_issue->items as $item) {

                $budgetItem = BudgetItem::where('budget_id', $qt->budget->id)
                    ->where('product_id', $item['productvar_id'])
                    ->first();

                $bi = [$item['id'] => $budgetItem];

                $budgetDetails = array_merge($budgetDetails, $bi);
            }
        }

        return view('focus.stock_issues.edit', compact('stock_issue', 'customers', 'employees', 'projects', 'quotes', 'budgetDetails','accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  StockIssue $stock_issue
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockIssue $stock_issue)
    {

        try {
            $this->repository->update($stock_issue, $request->except('_token', '_method'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Stock Issue', $th);
        }

        return new RedirectResponse(route('biller.stock_issues.index'), ['flash_success' => 'Stock Issue Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  StockIssue $stock_issue
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockIssue $stock_issue)
    {
        try {
            $this->repository->delete($stock_issue);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Stock Issue', $th);
        }

        return new RedirectResponse(route('biller.stock_issues.index'), ['flash_success' => 'Stock Issue Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  StockIssue $stock_issue
     * @return \Illuminate\Http\Response
     */
    public function show(StockIssue $stock_issue)
    {
        return view('focus.stock_issues.view', compact('stock_issue'));
    }

    /**
     * Quote/PI Stock Items
     */
    public function quote_pi_products(Request $request, $quoteId = 0)
    {
        try {

            $quoteId = empty($quoteId) ? $request->quote_id : $quoteId;

            $quote = Quote::find($quoteId);

            $quote_product_ids = $quote->products()->pluck('product_id')->toArray();

            if ($quote->budget) {
                $quote_product_ids = $quote->budget->items()->pluck('product_id')->toArray();
            }
            
            $budget = $quote->budget;
            $productvars = ProductVariation::whereIn('id', $quote_product_ids)
                ->whereHas('product', fn($q) => $q->where('stock_type', '!=', 'service'))
                ->get()
                ->map(function ($v) use($budget){
                    // dd($budget,  $v->id);
                    $v->unit = @$v->product->unit;
                    $v->budget_qty = 0;
                    if ($budget){
                        $v->budget_qty =  BudgetItem::where('budget_id', $budget->id)
                            ->where('product_id', $v->id)
                            ->sum('new_qty');
                    }
                    unset($v->product);
                    return $v;
                });
            foreach ($productvars as $key => $item) {
                
                $warehouses = Warehouse::whereHas('products', fn($q) => $q->where('name', 'LIKE', "%{$item->name}%"))
                    ->with(['products' => fn($q) => $q->where('name', 'LIKE', "%{$item->name}%")])
                    ->get();

                foreach ($warehouses as $i => $wh) {
                    $warehouses[$i]['products_qty'] = $wh->products->sum('qty');
                    unset($warehouses[$i]['products']);
                }
                $productvars[$key]['warehouses'] = $warehouses;
                //  dd($warehouses);
            }
            // dd($productvars, count(array_filter($quote_product_ids)), count($productvars));

            // $budgetDetails = [];

            // $budget = $quote->budget;

            // if ($budget){

            //     foreach ($productvars as $key => $item) {

            //         $budgetItem = BudgetItem::where('budget_id', $quote->budget->id)
            //             ->where('product_id', $item['id'])
            //             ->first();

            //         $bi = [$item['id'] => $budgetItem];

            //         $budgetDetails = array_merge($budgetDetails, $bi);
            //     }
            // }

            return response()->json(compact('productvars'));
        }
        catch (\Exception $ex) {
                return [
                    'request' => $request->toArray(),
                    'message' => $ex->getMessage(),
                    'code' => $ex->getCode(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine(),
                ];
        }
    }

    /**
     * Fetch client invoices
     */
    public function select_invoices(Request $request)
    {
        $w = $request->search; 
        $invoices = Invoice::whereHas('currency', fn($q) => $q->where('rate', 1))
        // ->whereHas('stock_issues')
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

    public function issue_invoice_items()
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
        foreach ($productvars as $key => $item) {
                
            $warehouses = Warehouse::whereHas('products', fn($q) => $q->where('name', 'LIKE', "%{$item->name}%"))
                ->with(['products' => fn($q) => $q->where('name', 'LIKE', "%{$item->name}%")])
                ->get();

            foreach ($warehouses as $i => $wh) {
                $warehouses[$i]['products_qty'] = $wh->products->sum('qty');
                unset($warehouses[$i]['products']);
            }
            $productvars[$key]['warehouses'] = $warehouses;
        }
        return response()->json($productvars);
    }
}
