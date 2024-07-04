@extends ('core.layouts.app')

@section ('title', 'View | Reconciliations Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Reconciliations Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.reconciliations.partials.reconciliations-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table id="journalsTbl" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $recon = $reconciliation;
                        $recon_details = [
                            'Account' => @$recon->account->holder,
                            'Statement Ending' => $recon->end_date,
                            'Ending Balance' => numberFormat($recon->end_balance),
                            'Reconciled On' => dateFormat($recon->created_at),
                            'Beginning Balance' => numberFormat($recon->begin_balance),   
                            'Cash Out' => numberFormat($recon->cash_out),                     
                            'Cash In' => numberFormat($recon->cash_in),   
                            'Cleared Balance' => numberFormat($recon->cleared_balance),
                        ];
                    @endphp
                    @foreach ($recon_details as $key => $val)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>

            <div class="table-responsive">        
                <table id="ledgerTbl" class="table">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th>Date</th>
                            <th>Type</th>
                            <th>Trans. Ref</th>
                            <th>Payer / Payee</th>
                            <th>Note</th>
                            <th class="mr-0 pr-0" width="15%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reconciliation->items()->whereNotNull('checked')->get() as $item)
                            @if ($item->journal && $item->journal_item)
                                @php
                                    $journal = $item->journal;
                                    $journal_item = $item->journal_item;
                                @endphp
                                <tr>
                                    <td>{{ dateFormat($journal->date) }}</td>
                                    <td>{{ $journal_item->debit == 0? 'cash-out' : 'cash-in' }}</td>
                                    <td>{{ gen4tid('JNL-', $journal->tid) }}</td>
                                    <td></td>
                                    <td>{{ $journal->note }}</td>
                                    <td>{{ $journal_item->debit == 0? numberFormat($journal_item->credit) : numberFormat($journal_item->debit) }}</td>
                                </tr>
                            @elseif ($item->payment)
                                @php
                                    $payment = $item->payment;
                                @endphp
                                <tr>
                                    <td>{{ dateFormat($payment->date) }}</td>
                                    <td>{{ 'cash-out' }}</td>
                                    <td>{{ gen4tid('RMT-', $payment->tid) }}</td>
                                    <td>{{ @$payment->supplier->name }}</td>
                                    <td>{{ $payment->note }}</td>
                                    <td>{{ numberFormat($payment->amount) }}</td>
                                </tr>
                            @elseif ($item->deposit)
                                @php
                                    $deposit = $item->deposit;
                                @endphp
                                <tr>
                                    <td>{{ dateFormat($deposit->date) }}</td>
                                    <td>{{ 'cash-in' }}</td>
                                    <td>{{ gen4tid('PMT-', $deposit->tid) }}</td>
                                    <td>{{ @$deposit->customer->company }}</td>
                                    <td>{{ $deposit->note }}</td>
                                    <td>{{ numberFormat($deposit->amount) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection