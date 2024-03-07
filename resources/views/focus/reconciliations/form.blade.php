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
                    <label for="end_date" class="caption">Ending Date</label>
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

<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="table-responsive" style="height: 50vh">
                <table class="table tfr" id="transactions">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th>Date</th>
                            <th>Type</th>
                            <th>Trans. Ref</th>
                            <th>Payer / Payee</th>
                            <th>Note</th>
                            <th class="mr-0 pr-0" width="15%">Amount</th>
                            <th class="ml-0 pl-0"><input id="check-all" type="checkbox" autocomplete="off"></th>
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
                                        <td class="mr-0 pr-0"><span class="cash">{{ $journal_item->debit == 0? numberFormat($journal_item->credit) : numberFormat($journal_item->debit) }}</span></td>
                                        <td class="ml-0 pl-0"><input class="check" type="checkbox" autocomplete="off"></td>
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
                                        <td class="mr-0 pr-0"><span class="cash">{{ numberFormat($payment->amount) }}</span></td>
                                        <td class="ml-0 pl-0"><input class="check" type="checkbox" autocomplete="off"></td>
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
                                        <td class="client-suppler">{{ @$deposit->customer->company }}</td>
                                        <td class="note">{{ $deposit->note }}</td>
                                        <td class="mr-0 pr-0"><span class="cash">{{ numberFormat($deposit->amount) }}</span></td>
                                        <td class="ml-0 pl-0"><input class="check" type="checkbox" autocomplete="off"></td>
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
                                <td class="client-suppler"></td>
                                <td class="note"></td>
                                <td class="mr-0 pr-0"><span class="cash"></span></td>
                                <td class="ml-0 pl-0"><input class="check" type="checkbox" autocomplete="off"></td>
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
<script>
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {format: "{{config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        initRow: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', "{{ dateFormat($last_day) }}");
            Index.initRow = $('#transactions tbody tr:first');

            $('#recon-form').submit(Index.onFormSubmit);
            $('#account').change(Index.onAccountChange);
            $('#end_date').change(() => $('#account').change());
            $('#end_balance').keyup(Index.onEndBalKeyUp);
            $('#end_balance').change(Index.onEndBalChange);
            $('#check-all').change(Index.onCheckAllChange);
            $('#transactions').on('change', '.check', Index.onCheckBoxChange);
            
            // editing
            const data = @json(@$reconciliation);
            const data_items = @json(@$reconciliation->items);
            if (data && data_items.length) {
                $('#account').attr('disabled', true);
                $('#end_date').attr('disabled', true);
                $('#end_balance').keyup().change();
                $('#transactions tbody tr').each(function() {
                    const row = $(this);
                    const checkinp = row.find('.check-inp');
                    if (checkinp.val() == 1) row.find('.check').prop('checked', true).change();
                });
            } 
        },

        onFormSubmit(e) {
            const balanceDiff = accounting.unformat($('#balance_diff').val());
            const msg = 'Balance Difference is Not Zero! Your Transactions Do Not Match Your Statement. Are you sure to proceed?';
            if (balanceDiff != 0 && !confirm(msg)) e.preventDefault();
        },

        onAccountChange() {
            $('#transactions tbody tr').remove();
            if (!this.value) return;
            const url = "{{ route('biller.reconciliations.account_items') }}";
            const params = {account_id: $(this).val(), end_date: $('#end_date').val()};
            $.post(url, params, data => {
                data.forEach((v,i) => {
                    if(i == 0) {
                        $('#begin_balance').val(accounting.formatNumber(v.begin_balance*1));
                        $('.begin-bal').text(accounting.formatNumber(v.begin_balance*1));
                    }
                    const row = Index.initRow.clone();
                    row.removeClass('d-none');
                    row.find('.journalitem-id').val(v.journal_item_id);
                    row.find('.journal-id').val(v.man_journal_id);
                    row.find('.pmt-id').val(v.payment_id);
                    row.find('.dep-id').val(v.deposit_id);
                    row.find('.date').text(v.date.split('-').reverse().join('-') || '');
                    row.find('.type').text(v.type);
                    row.find('.trans-ref').text(v.trans_ref);
                    row.find('.client-supplier').text(v.client_supplier);
                    row.find('.note').text(v.note);
                    row.find('.cash').text(accounting.formatNumber(v.amount*1));
                    $('#transactions tbody').append(row);
                });
            });
        },

        onEndBalChange() {
            const value = accounting.unformat(this.value);
            $(this).val(accounting.formatNumber(value));
        },

        onEndBalKeyUp() {
            const endBal = accounting.unformat(this.value);
            const clearedBal = accounting.unformat($('.cleared-bal').text());
            const balanceDiff = endBal - clearedBal;

            $('.endin-bal').text(accounting.formatNumber(endBal));
            $('.bal-diff').text(accounting.formatNumber(balanceDiff));
            $('#balance_diff').val(accounting.formatNumber(balanceDiff));
        },

        onCheckBoxChange() {
            const row = $(this).parents('tr');
            const type = row.find('.type').text();
            const endBal = accounting.unformat($('.endin-bal').text());
            const beginBal = accounting.unformat($('.begin-bal').text());
            const cash = accounting.unformat(row.find('.cash').text());
            let cashIn = accounting.unformat($('.cash-in').text());
            let cashOut = accounting.unformat($('.cash-out').text());
            if (type == 'cash-in') {
                if ($(this).is(':checked')) cashIn += cash;
                else cashIn -= cash;
            }
            if (type == 'cash-out') {
                if ($(this).is(':checked')) cashOut += cash;
                else cashOut -= cash;
            }

            if ($(this).is(':checked')) row.find('.check-inp').val(1);
            else row.find('.check-inp').val('');

            $('.cash-in').text(accounting.formatNumber(cashIn));
            $('.cash-out').text(accounting.formatNumber(cashOut));
            $('#cash_in').val(accounting.formatNumber(cashIn));
            $('#cash_out').val(accounting.formatNumber(cashOut));

            const clearedBal = beginBal - cashOut + cashIn;
            $('.cleared-bal').text(accounting.formatNumber(clearedBal));
            $('#cleared_balance').val(accounting.formatNumber(clearedBal));

            const balDiff = endBal - clearedBal;
            $('.bal-diff').text(accounting.formatNumber(balDiff));
            $('#balance_diff').val(accounting.formatNumber(balDiff));
        },

        onCheckAllChange() {
            let cashIn = 0;
            let cashOut = 0;
            if ($(this).is(':checked')) {
                $('#transactions tbody tr').each(function() {
                    const row = $(this);
                    row.find('.check').prop('checked', true);
                    row.find('.check-inp').val(1);
                    const type = row.find('.type').text();
                    const cash = accounting.unformat(row.find('.cash').text());
                    if (type == 'cash-in') cashIn += cash;
                    if (type == 'cash-out') cashOut += cash;
                });
            } else {
                $('#transactions tbody tr').each(function() {
                    const row = $(this);
                    row.find('.check').prop('checked', false);
                    row.find('.check-inp').val('');
                });
            }
            $('.cash-in').text(accounting.formatNumber(cashIn));
            $('.cash-out').text(accounting.formatNumber(cashOut));
            $('#cash_in').val(accounting.formatNumber(cashIn));
            $('#cash_out').val(accounting.formatNumber(cashOut));

            const endBal = accounting.unformat($('.endin-bal').text());
            const beginBal = accounting.unformat($('.begin-bal').text());

            const clearedBal = beginBal - cashOut + cashIn;
            $('.cleared-bal').text(accounting.formatNumber(clearedBal));
            $('#cleared_balance').val(accounting.formatNumber(clearedBal));

            const balDiff = endBal - clearedBal;
            $('.bal-diff').text(accounting.formatNumber(balDiff));
            $('#balance_diff').val(accounting.formatNumber(balDiff));
        }
    }

    $(Index.init);
</script>
@endsection