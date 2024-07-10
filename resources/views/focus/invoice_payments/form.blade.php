<div class="card">
    <div class="card-content">
        <div class="card-body mb-0 pb-0">
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="customer" class="caption">Search Customer</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select id="person" name="customer_id" class="form-control select-box" data-placeholder="Search Customer" required>
                            @isset ($invoice_payment)
                                <option value="{{ $invoice_payment->customer_id }}">{{ @$invoice_payment->customer->company }}</option>
                            @endisset
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="type">Payment Type</label>
                    <select name="payment_type" id="payment_type" class="custom-select">
                        @foreach (['per_invoice', 'on_account', 'advance_payment'] as $val)
                            <option value="{{ $val }}" {{ $val == @$invoice_payment->payment_type? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $val)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="reference" class="caption">Payment No.</label>
                    <div class="input-group">
                        {{ Form::text('tid', @$tid, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
                    </div>
                </div> 
                <div class="col-md-2">
                    <label for="date" class="caption">Date</label>
                    <div class="input-group">
                        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                    </div>
                </div>     
                <div class="col-md-2">            
                    <label for="currency_code">Forex Rate</label>
                    <div class="row no-gutters">
                        <div class="col-md-6"> 
                            <select name="currency_id" id="currency" class="custom-select" required>
                                @foreach ($currencies as $row)
                                    @if (@$invoice_payment)
                                        @if ($row->id == $invoice_payment->currency_id)
                                            <option value="{{ $row->id }}" rate="{{+$invoice_payment->fx_curr_rate}}" selected>{{ $row->code }}</option>
                                        @else    
                                            <option value="{{ $row->id }}" rate="{{+$row->rate}}">{{ $row->code }}</option>
                                        @endif
                                    @else
                                        <option value="{{ $row->id }}" rate="{{+$row->rate}}" {{$row->rate == 1? 'selected' : ''}}>{{ $row->code }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            {{ Form::text('fx_curr_rate', null, ['class' => 'form-control', 'id' => 'fx_curr_rate', 'required' => 'required']) }}
                        </div>
                    </div>
                </div>    
            </div> 
            <div class="form-group row">  
                <div class="col-md-4">
                    <label for="account">Receive On Bank (Ledger Account)</label>
                    <select name="account_id" id="account" class="custom-select" required>
                        <option value="">-- Select Account --</option>
                            @foreach ($accounts as $row)
                                <option value="{{ $row->id }}">{{ $row->holder }}</option>
                            @endforeach
                    </select>
                </div>     
                <div class="col-md-2">
                    <label for="payment_mode">Payment Mode</label>
                    <select name="payment_mode" id="payment_mode" class="custom-select" required>
                        <option value="">-- Select Mode --</option>
                        @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                            <option value="{{ $val }}">{{ strtoupper($val) }}</option>
                        @endforeach
                    </select>
                </div>  
                <div class="col-md-2">
                    <label for="reference" class="caption">Reference No.</label>
                    {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
                </div>       
                <div class="col-md-2">
                    <label for="amount" class="caption">Amount</label>
                    {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
                </div>                                         
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <label for="note">Note</label>
                {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
                </div>  
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6">
                    <label for="payment">Allocate Lumpsome Payment</label>
                    <select id="rel_payment" name="rel_payment_id" class="form-control custom-select" style="height:2em;" disabled>
                        <option value="">None</option>
                    </select>
                </div>   
            </div>

            <div class="table-responsive" style="max-height: 80vh">
                <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th>Due Date</th>
                            <th>Invoice No</th>
                            <th>Note</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Outstanding</th>
                            <th>Allocate</th>
                        </tr>
                    </thead>
                    <tbody>   
                        @isset ($invoice_payment)
                            @foreach ($invoice_payment->items as $row)
                                @php
                                    $invoice = $row->invoice;
                                    if (!$invoice) continue;
                                @endphp
                                <tr>
                                    <td>{{ dateFormat($invoice->invoiceduedate) }}</td>
                                    <td>{{ gen4tid('Inv-', $invoice->tid) }}</td>
                                    <td style="text-align: left;">{{ $invoice->notes }}</td>
                                    <td>{{ $invoice->status }}</td>
                                    <td class="inv-amount">{{ numberFormat($invoice->total) }}</td>
                                    <td>{{ numberFormat($invoice->amountpaid) }}</td>
                                    <td class="due"><b>{{ numberFormat($invoice->total - $invoice->amountpaid) }}<b></td>
                                    <td><input type="text" class="form-control paid" name="paid[]" value="{{ numberFormat($row->paid) }}"></td>
                                    <input type="hidden" name="invoice_id[]" value="{{ $row->invoice_id }}">
                                    <input type="hidden" name="id[]" value="{{ $row->id }}">
                                </tr>
                            @endforeach
                        @endisset
                    </tbody>                
                </table>
            </div>

            @if (!@$invoice_payment)
                <div class="row mt-2">
                    <div class="col-md-2 ml-auto">
                        <label for="total_balance" class="mb-0">Total Balance</label>
                        {{ Form::text('balance', null, ['class' => 'form-control', 'id' => 'balance', 'disabled']) }}
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-2 ml-auto">
                    <label for="total_paid" class="mb-0">Total Allocated</label>
                    {{ Form::text('allocate_ttl', null, ['class' => 'form-control', 'id' => 'allocate_ttl', 'readonly']) }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 ml-auto">
                    <label for="total_paid" class="mb-0">Total Unallocated</label>
                    {{ Form::text('unallocate_ttl', null, ['class' => 'form-control', 'id' => 'unallocate_ttl', 'disabled']) }}
                </div>
            </div>

            <div class="edit-form-btn row mt-2">
                {{ link_to_route('biller.invoice_payments.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                {{ Form::submit(@$invoice_payment? 'Update Payment' : 'Receive Payment', ['class' => 'btn btn-primary btn-md mr-2']) }}                                           
            </div>
        </div>
    </div>
</div>
