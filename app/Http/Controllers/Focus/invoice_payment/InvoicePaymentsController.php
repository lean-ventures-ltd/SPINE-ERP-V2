<?php

namespace App\Http\Controllers\Focus\invoice_payment;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice_payment\InvoicePayment;
use App\Repositories\Focus\invoice_payment\InvoicePaymentRepository;
use Illuminate\Http\Request;

class InvoicePaymentsController extends Controller
{
    /**
     * variable to store the repository object
     * @var InvoicePayment
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param InvoicePayment $repository ;
     */
    public function __construct(InvoicePaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::get(['id', 'company']);

        return new ViewResponse('focus.invoice_payments.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = InvoicePayment::max('tid')+1;
        $customers = Customer::get(['id', 'company']);
        $currencies = Currency::get();

        $accounts = Account::whereHas('accountType', fn($q) => $q->where('system', 'bank'))
        ->get(['id', 'holder']);

        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return new ViewResponse('focus.invoice_payments.create', compact('customers', 'accounts', 'tid', 'unallocated_pmts', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'fx_curr_rate' => 'required',
            'account_id' => 'required',
            'amount' => 'required',
            'payment_type' => 'required',
        ]);

        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl', 'payment_mode', 'reference', 
            'payment_id', 'payment_type', 'rel_payment_id', 'note', 'currency_id', 'fx_curr_rate',
        ]);
        $data_items = $request->only(['invoice_id', 'paid']); 
        $data_items = modify_array($data_items);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $result = $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Invoice Payment', $th);
        }

        return new RedirectResponse(route('biller.invoice_payments.index'), ['flash_success' => 'Invoice Payment updated successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(InvoicePayment $invoice_payment)
    {
        return new ViewResponse('focus.invoice_payments.view', compact('invoice_payment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoicePayment $invoice_payment)
    {   
        $tid = $invoice_payment->tid;
        $customers = Customer::get(['id', 'company']);
        $accounts = Account::whereHas('accountType', fn($q) => $q->where('system', 'bank'))->get(['id', 'holder']);
        $currencies = Currency::get();
        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
        ->whereColumn('amount', '!=', 'allocate_ttl')
        ->orderBy('date', 'asc')
        ->get();

        return new ViewResponse('focus.invoice_payments.edit', compact('invoice_payment', 'customers', 'accounts', 'unallocated_pmts', 'tid', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  InvoicePayment $invoice_payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoicePayment $invoice_payment)
    {
        // extract request input
        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl', 'payment_mode', 'reference', 
            'payment_id', 'payment_type', 'rel_payment_id', 'note', 'currency_id', 'fx_curr_rate',
        ]);
        $data_items = $request->only(['id', 'invoice_id', 'paid']); 

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);

        try {
            if ($invoice_payment->reconciliation_items()->exists()) {
                return errorHandler('Not Allowed! Invoice payment has been reconciled');
            }
            $result = $this->repository->update($invoice_payment, compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Invoice Payment', $th);
        }

        return new RedirectResponse(route('biller.invoice_payments.index'), ['flash_success' => 'Invoice Payment updated successfully']);
    }


    /**
     * Remove resource from storage
     */
    public function destroy(InvoicePayment $invoice_payment)
    {
        try {
            if ($invoice_payment->reconciliation_items()->exists()) 
                return errorHandler('Not Allowed! Invoice Payment is attached to a Reconciliation record');

            $this->repository->delete($invoice_payment);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Invoice Payment', $th);
        }

        return new RedirectResponse(route('biller.invoice_payments.index'), ['flash_success' => 'Invoice Payment deleted successfully']);
    }
}
