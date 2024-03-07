<?php

namespace App\Http\Controllers\Focus\reconciliation;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\JournalItem;
use App\Models\reconciliation\Reconciliation;
use App\Repositories\Focus\reconciliation\ReconciliationRepository;
use Illuminate\Http\Request;

class ReconciliationsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ReconciliationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ReconciliationRepository $repository ;
     */
    public function __construct(ReconciliationRepository $repository)
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
        return new ViewResponse('focus.reconciliations.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $accounts = Account::where('account_type_id', 6)->get(['id', 'holder']);
        $last_day = date('Y-m-t', strtotime(date('Y-m-d')));
    
        return new ViewResponse('focus.reconciliations.create', compact('accounts', 'last_day'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Reconciliation', $th);
        }
        
        return new RedirectResponse(route('biller.reconciliations.index'), ['flash_success' => 'Reconcilliaton Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Reconciliation $reconciliation
     * @return \Illuminate\Http\Response
     */
    public function edit(Reconciliation $reconciliation)
    {
        $accounts = Account::where('account_type_id', 6)->get(['id', 'holder']);
        $last_day = $reconciliation->end_date;

        return new ViewResponse('focus.reconciliations.edit', compact('reconciliation', 'accounts', 'last_day'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Reconciliation $reconciliation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reconciliation $reconciliation)
    {
        try {
            $this->repository->update($reconciliation, $request->except('_token', '_method'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Reconciliation', $th);
        }
        
        return new RedirectResponse(route('biller.reconciliations.index'), ['flash_success' => 'Reconcilliaton Updated Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Reconciliation $reconciliation)
    {
        return new ViewResponse('focus.reconciliations.view', compact('reconciliation'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reconciliation $reconciliation)
    {
        $this->repository->delete($reconciliation);

        return new RedirectResponse(route('biller.reconciliations.index'), ['flash_sucess' => 'Reconciliation deleted successfully']);
    }

    /**
     * Ledger account items
     */
    public function account_items()
    {
        $account_items = collect();
        $struct = ['journal_item_id' => null, 'man_journal_id' => null, 'payment_id' => null, 'deposit_id' => null, 'client_supplier' => null];
        $date = date_for_database(request('end_date'));
        $date = [date('m', strtotime($date)), date('Y', strtotime($date))];
        $journal_items = JournalItem::whereHas('journal', function($q) use($date) {
            $q->whereMonth('date', $date[0])->whereYear('date', $date[1]);
        })
        ->where('account_id', request('account_id'))->get();
        foreach ($journal_items as $key => $item) {
            $acc_item = array_replace($struct, [
                'journal_item_id' => $item->id,
                'man_journal_id' => $item->journal_id,
                'date' => @$item->journal->date,
                'type' => $item->debit == 0? 'cash-out' : 'cash-in',
                'trans_ref' => gen4tid('JNL-', @$item->journal->tid),
                'note' => @$item->journal->note,
                'amount' => $item->debit == 0? $item->credit : $item->debit,
            ]);
            $account_items->add($acc_item); 
        }
        $payments = Billpayment::whereHas('supplier')
        ->where('account_id', request('account_id'))
        ->whereMonth('date', $date[0])
        ->whereYear('date', $date[1])
        ->get();
        foreach ($payments as $key => $item) {
            $acc_item = array_replace($struct, [
                'payment_id' => $item->id,
                'date' => $item->date,
                'type' => 'cash-out',
                'trans_ref' => gen4tid('RMT-', $item->tid),
                'client_supplier' => @$item->supplier->name,
                'note' => $item->note,
                'amount' => $item->amount,
            ]);
            $account_items->add($acc_item); 
        }
        $deposits = InvoicePayment::whereHas('customer')
        ->where('account_id', request('account_id'))
        ->whereMonth('date', $date[0])
        ->whereYear('date', $date[1])
        ->get();
        foreach ($deposits as $key => $item) {
            $acc_item = array_replace($struct, [
                'deposit_id' => $item->id,
                'date' => $item->date,
                'type' => 'cash-in',
                'trans_ref' => gen4tid('PMT-', $item->tid),
                'client_supplier' => @$item->customer->company ?: @$item->customer->name,
                'note' => $item->note,
                'amount' => $item->amount,
            ]);
            $account_items->add($acc_item); 
        }
        $sorted_items = $account_items->sortBy('date');
        
        // set beginning balance
        $account = Account::find(request('account_id'));
        $begin_balance = $account->opening_balance;
        $last_recon =  Reconciliation::where('account_id', request('id'))->orderBy('id', 'DESC')->first();
        if ($last_recon) $begin_balance = $last_recon->end_balance;

        $account_items = [];
        foreach ($sorted_items as $key => $value) {
            $value['begin_balance'] = $begin_balance;
            $account_items[] = $value;
        }

        return response()->json($account_items);
    }
}
