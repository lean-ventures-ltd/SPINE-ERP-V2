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
namespace App\Http\Controllers\Focus\job_valuation;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\additional\Additional;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\job_valuation\JobValuation;
use App\Models\quote\Quote;
use App\Repositories\Focus\job_valuation\JobValuationRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JobValuationsController extends Controller
{
    /**
     * variable to store the repository object
     * @var JobValuationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param JobValuationRepository $repository ;
     */
    public function __construct(JobValuationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.job_valuations.index');
    }

    public function quote_index()
    {
        return new ViewResponse('focus.job_valuations.quote_index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = JobValuation::max('tid')+1;
        $customers = Customer::whereHas('invoices')->get(['id', 'company', 'name']);
        $additionals = Additional::get();
        $quote = Quote::find(request('quote_id')) ?: new Quote;
        $quote['verified_products'] = $quote->verified_products->map(function($item) {
            $item['productvar_id'] = $item->product_variation? $item->product_id : null;
            return $item;
        });
        
        return view('focus.job_valuations.create', compact('tid', 'customers', 'quote', 'additionals'));
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
            return errorHandler('Error Creating Job Valuation', $th);
        }
    
        return new RedirectResponse(route('biller.job_valuations.index'), ['flash_success' => 'Job Valuation Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  JobValuation $job_valuation
     * @return \Illuminate\Http\Response
     */
    public function edit(JobValuation $job_valuation)
    {
        $tid = $job_valuation->tid;
        $customers = Customer::whereHas('invoices')->get(['id', 'company', 'name']);
        $additionals = Additional::get();

        return view('focus.job_valuations.edit', compact('job_valuation', 'tid', 'customers', 'additionals'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  JobValuation $job_valuation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JobValuation $job_valuation)
    {
        try {
            $this->repository->update($job_valuation, $request->except('_token', '_method'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Job Valuation', $th);
        }

        return new RedirectResponse(route('biller.job_valuations.index'), ['flash_success' => 'Job Valuation Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  JobValuation $job_valuation
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobValuation $job_valuation)
    {
        try {
            $this->repository->delete($job_valuation);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Job Valuation', $th);
        }

        return new RedirectResponse(route('biller.job_valuations.index'), ['flash_success' => 'Job Valuation Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  JobValuation $job_valuation
     * @return \Illuminate\Http\Response
     */
    public function show(JobValuation $job_valuation)
    {
        return view('focus.job_valuations.view', compact('job_valuation'));
    }

    /**
     * Fetch partially and non-valuated quotes
     */
    public function get_quotes(Request $request)
    {
        // request('valuation_status');
        $quotes = Quote::query()->whereColumn('total', '>', 'verified_total')->get();
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'project'], auth()->user()->ins);

        return Datatables::of($quotes)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($quote) {
                return '<input type="checkbox" class="select-row" value="'. $quote->id .'">';
            })
            ->addColumn('tid', function ($quote) use($prefixes) {
                $tid = gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                return '<a class="font-weight-bold" href="'. route('biller.quotes.show',$quote) .'">'. $tid . $quote->revision .'</a>';
            })
            ->addColumn('customer', function ($quote) {
                $customer = $quote->lead? $quote->lead->client_name : '';
                if ($quote->customer) {
                    $customer = "{$quote->customer->company}";
                    if ($quote->branch) $customer .= " - {$quote->branch->name}";
                }
                
                return $customer;
            })
            ->addColumn('total', function ($quote) {
                if ($quote->currency) 
                    return amountFormat($quote->total, $quote->currency->id);
                return numberFormat($quote->total);
            })
            ->addColumn('verified_total', function ($quote) {
                if ($quote->currency) 
                    return amountFormat($quote->verified_total, $quote->currency->id);
                return numberFormat($quote->verified_total);
            })
            ->addColumn('balance', function ($quote) {
                $balance = $quote->total - $quote->verified_total;
                if (round($quote->verified_total) > round($quote->total)) $balance = 0;
                if ($quote->currency) 
                    return amountFormat($balance, $quote->currency->id);
                return numberFormat($quote->verified_total);
            })
            ->addColumn('lpo_number', function($quote) {
                if ($quote->lpo) return 'lpo - ' . $quote->lpo->lpo_no;
            })
            ->addColumn('project_tid', function($quote) use($prefixes) {
                if ($quote->project) 
                return gen4tid("{$prefixes[2]}-", $quote->project->tid);
            })
            ->addColumn('date', function($quote) {
                return dateFormat($quote->date);
            })
            ->make(true);    
    }

    /**
     * Invoice Stock Items
     */
    public function invoice_stock_items(Request $request)
    {
        $productvars = [];
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
                                $productvar['reference'] = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
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
                        $productvar['reference'] = '';
                        $productvar['verified_item_id'] = null;
                        $productvar['uom'] = @$productvar->product->unit->code;
                        $productvars[] = $productvar;
                    }
                }
            }
        }

        return response()->json($productvars);
    }
}
