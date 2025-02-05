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

namespace App\Http\Controllers\Focus\standard_invoice;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\cuInvoiceNumber\CuInvoiceNumberController;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\additional\Additional;
use App\Models\bank\Bank;
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\term\Term;
use App\Repositories\Focus\standard_invoice\StandardInvoiceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * BanksController
 */
class StandardInvoicesController extends Controller
{
    /**
     * variable to store the repository object
     * @var StandardInvoiceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StandardInvoiceRepository $repository ;
     */
    public function __construct(StandardInvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\bank\ManageBankRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.invoices.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\CreateResponse
     */
    public function create(Request $request)
    {
        $tid = Invoice::getTid();

        $customers = Customer::where('id', '>', 1)->get(['id', 'company']);
        $banks = Bank::all();
        $accounts = Account::whereHas('accountType', fn($q) => $q->whereIn('name', ['Income', 'Other Income']))->get();
        $terms = Term::where('type', 1)->get();  // invoice term type is 1
        $tax_rates = Additional::all();
        $currencies = Currency::all();


        $newCuInvoiceNo = (new CuInvoiceNumberController())->getNext();

        return new ViewResponse('focus.standard_invoices.create', compact('tid', 'customers', 'banks', 'accounts', 'terms', 'tax_rates', 'currencies', 'newCuInvoiceNo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBankRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
//        Validator::make($request->all(),
//            ['cu_invoice_no' => 'unique:invoices,cu_invoice_no', 'unique:credit_notes,cu_invoice_no'],
//            ['cu_invoice_no.unique' => 'The Specified CU Invoice Number is Already Taken']
//        )->validate();

//        $request['cu_invoice_no'] = (new CuInvoiceNumberController())->allocate();

        $data = $request->only([
            'customer_id', 'tid', 'invoicedate', 'tax_id', 'bank_id', 'validity', 'account_id', 'currency_id', 'term_id', 'notes', 
            'taxable', 'subtotal', 'tax', 'total', 'cu_invoice_no',
        ]);
        $data_items = $request->only([
            'numbering', 'description', 'unit', 'product_qty', 'product_price', 'product_tax', 'product_amount', 
            'product_id'
        ]);

        $this->repository->create(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => 'Invoice successfully created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\bank\Bank $bank
     * @param EditBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\EditResponse
     */
    public function edit(Invoice $invoice, Request $request)
    {
        return new ViewResponse('focus.standard_invoices.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Invoice $invoice)
    {
        dd($request->all());
        $request->validate([
            'name' => 'required|string',
            'bank' => 'required|string',
            'number' => 'required'
        ]);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($invoice, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => 'Invoice successfully updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Invoice $invoice, Request  $request)
    {
        dd($invoice);

        //Calling the delete method on repository
        $this->repository->delete($invoice);
        //returning with successfull message
        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => 'Invoice successfully deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Invoice $invoice, Request $request)
    {
        return new ViewResponse('focus.invoices.view', compact('charge'));
    }

    /**
     * Create Customer
     */
    public function create_customer(Request $request)
    {
        // dd(request()->all());
        $request->validate([
            'company' => 'required',
        ]);

        $input = $request->only(['company', 'name', 'email', 'phone', 'address', 'tax_pin']);

        $email_exists = Customer::where('email', $input['email'])->count();
        if ($email_exists) throw ValidationException::withMessages(['Email already exists!']);

        $is_company = Customer::where('company', $input['company'])->count();
        if ($is_company) throw ValidationException::withMessages(['Company already exists!']);

        if (isset($input['tax_pin'])) {
            $taxid_exists = Customer::where('taxid', $input['tax_pin'])->count();
            if ($taxid_exists) throw ValidationException::withMessages(['Tax Pin already exists!']);

            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $input['tax_pin']])->count();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin is not allowed!']);
        } 
        
        $input['taxid'] = $input['tax_pin'];
        unset($input['tax_pin']);
        if (Customer::create($input)) return redirect()->back()->with('flash_success', 'Customer Created Successfully');
        throw ValidationException::withMessages(['Error creating customer']);
    }
}
