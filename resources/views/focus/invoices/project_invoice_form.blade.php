<div class="card" style="margin-bottom: 1em;">
    <div class="card-header mb-0 pb-0" style="padding-top: 1em;">
        <div id="credit_limit" class="align-center"></div>
    </div>
    <div class="card-content">
        <div class="card-body mt-0 pt-0 pb-0">
            <div class="row mb-1">
                <div class="col-md-6"><label for="payer" class="caption">Customer Name</label>
                    <div class="input-group">
                        @php
                            $customer_name = '';
                            if (!$customer->company && $quotes->count() == 1) {
                                $quote = $quotes->first();
                                if ($quote->customer) $customer_name = $quote->customer->company;
                                elseif ($quote->lead) $customer_name = $quote->lead->client_name;
                            } else $customer_name = $customer->company;
                        @endphp
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('customer_name', $customer_name, ['class' => 'form-control round', 'id' => 'customername', 'readonly']) }}
                        <input type="hidden" name="customer_id" value="{{ $customer->id ?: 0 }}" id="customer_id">
                        {{ Form::hidden('taxid', $customer->taxid) }}
                        <input type="hidden" value="0" id="credit">
                        <input type="hidden" value="0" id="total_aging">
                        <input type="hidden" value="0" id="outstanding_balance">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="tid" class="caption">Invoice No.</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        @php
                            $label = gen4tid("{$prefixes[0]}-", @$last_tid+1);
                            $tid = @$last_tid+1; 
                            if (isset($invoice)) {
                                $label = gen4tid("{$prefixes[0]}-", $invoice->tid);
                                $tid = $invoice->tid;
                            }
                        @endphp
                        {{ Form::text('tid', $label, ['class' => 'form-control round', 'disabled']) }}
                        <input type="hidden" name="tid" value={{ $tid }}>
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="invoicedate" class="caption">Invoice Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'id' => 'invoicedate']) }}
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="tid" class="caption">VAT Rate*</label>
                    <div class="input-group">
                        <select class="custom-select" name='tax_id' id="tax_id" required>
                            <option value="">-- Select VAT --</option>
                            @foreach ($additionals as $row)
                                <option value="{{ +$row->value }}" {{ @$invoice && $invoice->tax_id == $row->value? 'selected' : '' }}>
                                    {{ $row->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>   
            </div>
            <div class="form-group row">
                <div class="col-md-2"> 
                    <label for="refer_no" class="caption">Payment Account*</label>                                   
                    <div class="input-group">
                        <select class="custom-select" name="bank_id" id="bank_id" required>
                            <option value="">-- Select Bank --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->id }}" {{ $bank->id == @$invoice->bank_id ? 'selected' : '' }}>
                                    {{ $bank->bank }}
                                </option>
                            @endforeach
                        </select>
                    </div>                                
                </div>
                <div class="col-md-2">
                    <label for="validity" class="caption">Credit Period</label>
                    <div class="input-group">
                        <select class="custom-select" name="validity" id="validity">
                            @foreach ([0, 14, 30, 45, 60, 90] as $val)
                            <option value="{{ $val }}" {{ !$val ? 'selected' : ''}} {{ @$invoice->validity == $val ? 'selected' : '' }}>
                                {{ $val ? 'Valid For ' . $val . ' Days' : 'On Receipt' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="income_category" class="caption">Income Category*</label>
                    <select class="custom-select" name="account_id" required>
                        <option value="">-- Select Category --</option>                                        
                        @foreach ($accounts as $row)
                            @php
                                $account_type = $row->accountType;
                                if ($account_type->name != 'Income') continue;
                            @endphp

                            @if($row->holder !== 'Stock Gain' && $row->holder !== 'Others' && $row->holder !== 'Point of Sale' && $row->holder !== 'Loan Penalty Receivable' && $row->holder !== 'Loan Interest Receivable')
                                <option value="{{ $row->id }}" {{ $row->id == @$invoice->account_id ? 'selected' : '' }}>
                                    {{ $row->holder }}
                                </option>
                            @endif

                        @endforeach                                        
                    </select>
                </div>

                @if ($currency->rate == 1)
                    <div class="col-md-4">
                        <label for="terms">Terms</label>
                        <select name="term_id" class="custom-select">
                            @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ $term->id == @$invoice->term_id ? 'selected' : ''}}>
                                {{ $term->title }}
                            </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="fx_curr_rate" value="{{ +$currency->rate }}">
                    </div>
                @else
                    <div class="col-md-2">
                        <label for="terms">Terms</label>
                        <select name="term_id" class="custom-select">
                            @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ $term->id == @$invoice->term_id ? 'selected' : ''}}>
                                {{ $term->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="currency_code">Forex Rate</label>
                        <div class="row no-gutters">
                            <div class="col-6"> 
                                {{ Form::text('currency_code', $currency->code, ['class' => 'form-control', 'id' => 'currency_code', 'disabled' => 'disabled']) }}
                            </div>
                            <div class="col-6">
                                {{ Form::text('fx_curr_rate', +$currency->rate, ['class' => 'form-control', 'id' => 'fx_curr_rate']) }}
                            </div>
                        </div>
                    </div>
                @endif

                @if (@$quote_ids && count($quote_ids) == 1)
                    <div class="col-md-2">
                        <label for="invoice_category">Invoice Type</label>
                        <select name="invoice_type" class="custom-select" id="invoice_type" required>
                            @foreach (['standard' => 'Standard', 'collective' => 'Consolidated'] as $key => $val)
                                <option value="{{ $key }}" {{ $key == @$invoice->invoice_type? 'selected' : ''}}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="col-md-2">
                        <label for="cu_invoice_no">CU Invoice No.</label>
                        <input type="text" id="cu_invoice_no" name="cu_invoice_no" class="form-control box-size"
                            @if(!empty($invoice->cu_invoice_no))
                                value="{{$invoice->cu_invoice_no}}"
                            @endif
                        >
                    </div>
                @endif
            </div>
            <div class="row mb-1">
                <div class="col-md-10">
                    <div class="input-group"><label for="title" class="caption">Note</label></div>
                    {{ Form::text('notes', null, ['class' => 'form-control']) }}
                </div>
                @if (@$quote_ids && count($quote_ids) == 1)
                    <div class="col-md-2">
                        <label for="cu_invoice_no">CU Invoice No.</label>
                        <input type="text" id="cu_invoice_no" name="cu_invoice_no" class="form-control box-size"
                            @if(!empty($invoice->cu_invoice_no))
                                value="{{$invoice->cu_invoice_no}}"
                            @endif
                        >
                    </div>
                @endif
            </div>
            <!-- estimate id -->
            @if (@$quotes && @$quotes[0]['estimate_id'] > 0)
                <input type="hidden" name="estimate_id" value="{{ $quotes[0]['estimate_id'] }}">
            @endif
            <!-- end estimate id -->
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 80vh">
                <table id="quoteTbl" class="table tfr my_stripe_single pb-1">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            <th width="5%">#</th>
                            <th width="25%">Reference</th>
                            <th width="35%">Item Description</th>
                            <th width="10%">UoM</th>
                            <th width="10%">Qty</th>
                            <th width="10%">Rate (VAT Exc)</th>
                            <th width="10%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($quotes))
                            @foreach($quotes as $k => $val)
                                @php
                                    // Reference details
                                    $tid = gen4tid($val->bank_id? "{$prefixes[2]}-" : "{$prefixes[1]}-", $val->tid);
                                    if ($val->revision) $tid .= $val->revision;
                                    $lpo_no = $val->lpo ? "{$prefixes[3]}-{$val->lpo->lpo_no}" : '';
                                    $client_ref = $val->client_ref;
                                    $branch_name = $val->branch? "{$val->branch->name} ({$val->branch->branch_code})" : '';
                                    $djc_ref = $val->reference? "Djc-{$val->reference}" : '';
                                    
                                    // Description details
                                    $jcs = [];
                                    foreach($val->verified_jcs as $jc) {
                                        if ($jc->type == 2) $jcs[] = "{$prefixes[4]}-{$jc->reference}";
                                        else $jcs[] = "{$prefixes[5]}-{$jc->reference}";
                                    }
                
                                    // Table values
                                    $title = $val->notes;
                                    $jcs = implode(', ', $jcs);
                                    $description = implode(';', [$title, $djc_ref, $jcs]);
                                    $reference = '' . implode('; ', [$branch_name, $tid, $lpo_no, $client_ref]); 
                                    $project_id = $val->project_quote ? $val->project_quote->project_id : '';
                                    $taxable = $val->verified_products()->where('product_tax', '>', 0)->sum(DB::raw('product_qty * product_subtotal'));
                                    $subtotal = $val->verified_products()->sum(DB::raw('product_qty * product_subtotal')); 
                                    // browserLog($subtotal . ' ' . $taxable);
                                @endphp
                                <tr>
                                    <td class="num pl-2">{{ $k+1 }}</td>                                            
                                    <td><textarea class="form-control ref" name="reference[]" id="reference-{{ $k }}" rows="5" readonly>{{ $reference }}</textarea></td>
                                    <td><textarea class="form-control descr" name="description[]" id="description-{{ $k }}" rows="5">{{ $description }}</textarea></td>
                                    <td><input type="text" class="form-control unit" name="unit[]" id="unit-{{ $k }}" value="Lot" readonly></td>
                                    <td><input type="text" class="form-control qty" name="product_qty[]" id="product_qty-{{ $k }}" value="1" readonly></td>
                                    <td><input type="text" class="form-control rate" name="product_price[]" value="{{ number_format($subtotal, 4) }}" id="product_price-{{ $k }}" readonly></td>
                                    <td class="text-center"><strong><span class='ttlText amount' id="result-{{ $k }}">{{ number_format($subtotal + $val->verified_tax, 4) }}</span></strong></td>
                                    
                                    <input type="hidden" class="subtotal" value="{{ round($subtotal, 4) }}" id="initprice-{{ $k }}" disabled>
                                    <input type="hidden" class="num-val" name="numbering[]" id="num-{{ $k }}">
                                    <input type="hidden" class="row-index" name="row_index[]" id="rowindex-{{ $k }}">
                                    <input type="hidden" class="quote-id" name="quote_id[]" value="{{ $val->id }}" id="quoteid-{{ $k }}">
                                    <input type="hidden" class="branch-id" name="branch_id[]" value="{{ $val->branch_id }}" id="branchid-{{ $k }}">
                                    <input type="hidden" class="project-id" name="project_id[]" value="{{ $project_id }}" id="projectid-{{ $k }}">
                                    
                                    <input type="hidden" class="taxable" value="{{ round($taxable, 4) }}">
                                    <input type="hidden" class="producttax" name="product_tax[]" value="{{ $val->verified_tax }}" id="producttax-{{ $k }}">
                                    <input type="hidden" class="taxrate" name="tax_rate[]" value="{{ +$val->tax_id }}" id="taxrate-{{ $k }}">
                                    <input type="hidden" class="productsubtotal" name="product_subtotal[]" value="{{ round($subtotal, 4) }}" id="productsubtotal-{{ $k }}">
                                    <input type="hidden" class="productamount" name="product_amount[]" value="{{ number_format($subtotal + $val->verified_tax, 4) }}">
                                    <input type="hidden" class="price" value="0" id="price-{{ $k }}">
                                </tr>
                            @endforeach
                        @else        
                            {{-- edit invoice items --}}
                            @foreach ($invoice->products as $k => $item)
                                <tr>
                                    <td class="num pl-2">{{ $k+1 }}</td>                                            
                                    <td><textarea class="form-control ref" name="reference[]" id="reference-{{ $k }}" rows="5">{{ $item->reference }}</textarea></td>
                                    <td><textarea class="form-control descr" name="description[]" id="description-{{ $k }}" rows="5">{{ $item->description }}</textarea></td>
                                    <td><input type="text" class="form-control unit" name="unit[]" id="unit-{{ $k }}" value="{{ $item->unit }}" readonly></td>
                                    <td><input type="text" class="form-control qty" name="product_qty[]" id="product_qty-{{ $k }}" value="{{ +$item->product_qty }}" readonly></td>
            
                                    @php
                                        $unit_cost = number_format($item->product_price, 4);
                                        $net_cost = number_format($item->product_price * $item->product_qty, 4);
                                    @endphp
                                    <td><input type="text" class="form-control rate" name="product_price[]" value="{{ $unit_cost }}" id="product_price-{{ $k }}" readonly></td>
                                    <td class="text-center"><strong><span class='ttlText amount' id="result-{{ $k }}">{{ $net_cost }}</span></strong></td>
                
                                    <input type="hidden"  class="subtotal" value="{{ +$item->product_price }}" id="initprice-{{ $k }}" disabled>
                                    <input type="hidden" class="num-val" name="numbering[]" value="{{ $item->numbering }}" id="num-{{ $k }}">
                                    <input type="hidden" class="row-index" name="row_index[]" value="{{ $item->row_index }}" id="rowindex-{{ $k }}">
                                    <input type="hidden" class="quote-id" name="quote_id[]" value="{{ $item->quote_id }}" id="quoteid-{{ $k }}">
                                    <input type="hidden" class="branch-id" name="branch_id[]" value="{{ $item->branch_id }}" id="branchid-{{ $k }}">
                                    <input type="hidden" class="project-id" name="project_id[]" value="{{ $item->project_id }}" id="projectid-{{ $k }}">
                                    <input type="hidden" name="id[]" value="{{ $item->id }}">
            
                                    <input type="hidden" class="taxable" value="{{ +$item->product_price }}">
                                    <input type="hidden" class="producttax" name="product_tax[]" value="{{ $item->product_tax }}" id="producttax-{{ $k }}">
                                    <input type="hidden" class="taxrate" name="tax_rate[]" value="{{ +$item->tax_rate }}" id="taxrate-{{ $k }}">
                                    <input type="hidden" class="productsubtotal" name="product_subtotal[]" value="{{ +$item->product_price }}" id="productsubtotal-{{ $k }}">
                                    <input type="hidden" class="productamount" name="product_amount[]" value="{{ +$item->product_amount }}">
                                    <input type="hidden" class="price" value="0" id="price-{{ $k }}">
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>    
            </div>
            <div class="form-group">
                <div class="col-2 ml-auto">
                    <label for="taxable">Taxable</label>
                    {{ Form::text('taxable', null, ['class' => 'form-control', 'id' => 'taxable', 'readonly']) }}
                </div>
                <div class="col-2 ml-auto">
                    <label for="subtotal">Subtotal</label>
                    {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
                </div>
                <div class="col-2 ml-auto">
                    <label for="totaltax">Total VAT</label>
                    {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
                </div>
                <div class="col-2 ml-auto">
                    <label for="grandtotal">Grand Total</label>
                    {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
                </div>
                <div class="row no-gutters mt-1">
                    <div class="col-1 ml-auto pl-1">
                        <a href="{{ route('biller.invoices.uninvoiced_quote') }}" class="btn btn-danger block">Cancel</a>    
                    </div>
                    <div class="col-1 ml-1">
                        {{ Form::submit(@$invoice? 'Update' : 'Generate', ['class' => 'btn btn-primary block text-white mr-1']) }}    
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
