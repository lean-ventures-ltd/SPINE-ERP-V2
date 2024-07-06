<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-7 col-12">
                    <label for="payer" class="caption">Bank Account</label>                                       
                    <select class="custom-select" id="account" name="account_id" autocomplete="off" required>
                        <option value="">-- Select Bank --</option>
                        @foreach ($accounts as $bank)
                            <option value="{{ $bank->id }}" {{ $bank->id == @$reconciliation->account_id? 'selected' : ''}}>
                                {{ $bank->holder }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-2">
                    <label for="end_date" class="caption">Ending Month</label>
                    {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'id' => 'end_date', 'required' => 'required']) }}
                </div>   
                <div class="col-2">
                    <label for="end_balance" class="caption">Ending Balance</label>
                    {{ Form::text('end_balance', null, ['class' => 'form-control', 'id' => 'end_balance', 'autocomplete' => "off", 'required' => 'required']) }}
                    {{ Form::hidden('begin_balance', null, ['id' => 'begin_balance']) }}
                    {{ Form::hidden('cash_in', null, ['id' => 'cash_in']) }}
                    {{ Form::hidden('cash_out', null, ['id' => 'cash_out']) }}
                    {{ Form::hidden('cleared_balance', null, ['id' => 'cleared_balance']) }}
                    {{ Form::hidden('balance_diff', null, ['id' => 'balance_diff']) }}
                </div>  
            </div> 
            <div class="row">
                <div class="col-md-7 col-12">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Beginning Balance</th>
                                    <th>- Cash Out</th>
                                    <th>+ Cash In</th>
                                    <th>Cleared Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="begin-bal">0.00</td>
                                    <td class="cash-out">0.00</td>
                                    <td class="cash-in">0.00</td>
                                    <td class="cleared-bal">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-5 col-12">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Ending Balance</th>
                                    <th>- Cleared Balance</th>
                                    <th>Difference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="endin-bal">0.00</td>
                                    <td class="cleared-bal">0.00</td>
                                    <td class="bal-diff">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- reconciliations table -->
<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 80vh">
                <table class="table tfr text-center" id="transactions">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th>Date</th>
                            <th>Type</th>
                            <th>Trans. Ref</th>
                            <th>Payer / Payee</th>
                            <th>Note</th>
                            <th class="d-none" width="15%">Amount</th>
                            <th width="15%">Debit</th>
                            <th width="15%">Credit</th>
                            <th><input id="check-all" type="checkbox" autocomplete="off"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($reconciliation->items))
                            @foreach ($reconciliation->items as $item)
                                @if ($item->journal && $item->journal_item)
                                    @php
                                        $journal = $item->journal;
                                        $journal_item = $item->journal_item;
                                    @endphp
                                    <tr>
                                        <td class="date">{{ dateFormat($journal->date) }}</td>
                                        <td class="type">{{ $journal_item->debit == 0? 'cash-out' : 'cash-in' }}</td>
                                        <td class="trans-ref">{{ gen4tid('JNL-', $journal->tid) }}</td>
                                        <td class="client-suppler"></td>
                                        <td class="note">{{ $journal->note }}</td>
                                        <td class="d-none"><span class="cash">{{ $journal_item->debit == 0? numberFormat($journal_item->credit) : numberFormat($journal_item->debit) }}</span></td>
                                        @if ($journal_item->debit > 0)
                                            <td><span class="debit">{{ numberFormat($journal_item->debit) }}</span></td>
                                            <td><span class="credit"></span></td>
                                        @else
                                            <td><span class="debit"></span></td>
                                            <td><span class="credit">{{ numberFormat($journal_item->credit) }}</span></td>
                                        @endif
                                        <td><input class="check" type="checkbox" autocomplete="off"></td>
                                        <input type="hidden" name="checked[]" value="{{ $item->checked }}" class="check-inp">
                                        <input type="hidden" name="man_journal_id[]" value="{{ $journal->id }}" class="journal-id">
                                        <input type="hidden" name="journal_item_id[]" value="{{ $journal_item->id }}" class="journalitem-id">
                                        <input type="hidden" name="payment_id[]" class="pmt-id">
                                        <input type="hidden" name="deposit_id[]" class="dep-id">
                                    </tr> 
                                @elseif ($item->payment)
                                    @php
                                        $payment = $item->payment;
                                    @endphp
                                    <tr>
                                        <td class="date">{{ dateFormat($payment->date) }}</td>
                                        <td class="type">{{ 'cash-out' }}</td>
                                        <td class="trans-ref">{{ gen4tid('RMT-', $payment->tid) }}</td>
                                        <td class="client-suppler">{{ @$payment->supplier->name }}</td>
                                        <td class="note">{{ $payment->note }}</td>
                                        <td class="d-none"><span class="cash">{{ numberFormat($payment->amount) }}</span></td>
                                        <td><span class="debit"></span></td>
                                        <td><span class="credit">{{ numberFormat($deposit->amount) }}</span></td>
                                        <td><input class="check" type="checkbox" autocomplete="off"></td>
                                        <input type="hidden" name="checked[]" value="{{ $item->checked }}" class="check-inp">
                                        <input type="hidden" name="man_journal_id[]" class="journal-id">
                                        <input type="hidden" name="journal_item_id[]" class="journalitem-id">
                                        <input type="hidden" name="payment_id[]" value="{{ $payment->id }}" class="pmt-id">
                                        <input type="hidden" name="deposit_id[]" class="dep-id">
                                    </tr> 
                                @elseif ($item->deposit)
                                    @php
                                        $deposit = $item->deposit;
                                    @endphp
                                    <tr>
                                        <td class="date">{{ dateFormat($deposit->date) }}</td>
                                        <td class="type">{{ 'cash-in' }}</td>
                                        <td class="trans-ref">{{ gen4tid('PMT-', $deposit->tid) }}</td>
                                        <td class="client-supplier">{{ @$deposit->customer->company }}</td>
                                        <td class="note">{{ $deposit->note }}</td>
                                        <td class="d-none"><span class="cash">{{ numberFormat($deposit->amount) }}</span></td>
                                        <td><span class="debit">{{ numberFormat($deposit->amount) }}</span></td>
                                        <td><span class="credit"></span></td>
                                        <td><input class="check" type="checkbox" autocomplete="off"></td>
                                        <input type="hidden" name="checked[]" value="{{ $item->checked }}" class="check-inp">
                                        <input type="hidden" name="man_journal_id[]" class="journal-id">
                                        <input type="hidden" name="journal_item_id[]" class="journalitem-id">
                                        <input type="hidden" name="payment_id[]" class="pmt-id">
                                        <input type="hidden" name="deposit_id[]" value="{{ $deposit->id }}" class="dep-id">
                                    </tr> 
                                @endif
                            @endforeach
                        @else
                            <tr class="d-none">
                                <td class="date"></td>
                                <td class="type"></td>
                                <td class="trans-ref"></td>
                                <td class="client-supplier"></td>
                                <td class="note"></td>
                                <td class="d-none"><span class="cash"></span></td>
                                <td><span class="debit"></span></td>
                                <td><span class="credit"></span></td>
                                <td><input class="check" type="checkbox" autocomplete="off"></td>
                                <input type="hidden" name="checked[]" class="check-inp">
                                <input type="hidden" name="man_journal_id[]" class="journal-id">
                                <input type="hidden" name="journal_item_id[]" class="journalitem-id">
                                <input type="hidden" name="payment_id[]" class="pmt-id">
                                <input type="hidden" name="deposit_id[]" class="dep-id">
                            </tr> 
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('after-scripts')
@include('focus.reconciliations.form_js')
@endsection