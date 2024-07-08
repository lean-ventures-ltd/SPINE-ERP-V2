@php
    $label = $quote->bank_id ? 'PI' : 'Quote';
    $prefixes = prefixesArray(['quote', 'proforma_invoice'], $quote->ins);
@endphp

<div class="card">
    <div class="card-content">
        <div class="card-body">
            <input type="hidden" name="quote_id" value="{{ $quote->id }}">
            <div class="row mb-1">
                <div class="col-2">                                        
                    <label for="serial_no" class="caption mb-0">{{ $label . ' ' . trans('general.serial_no') }}</label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>                                           
                        @php
                            $tid = gen4tid($label == 'PI'? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                        @endphp
                        {{ Form::text('tid', $tid . $quote->revision, ['class' => 'form-control', 'id' => 'tid', 'disabled']) }}
                    </div>
                </div>  
                <div class="col-4">
                    <label for="client" class="caption mb-0">Customer - Branch</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client', @$quote->customer->name . ' - ' . @$quote->branch->name, ['class' => 'form-control round', 'id' => 'client', 'disabled']) }}
                        <input type="hidden" name="customer_id" value="{{ @$quote->customer_id }}" id="client_id">
                        <input type="hidden" name="branch_id" value="{{ @$quote->branch_id }}" id="branch_id">
                    </div>
                </div>
                <div class="col-6">
                    <label for="subject" class="caption mb-0">Subject / Title</label>
                    {{ Form::text('notes', @$quote->notes, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-2">
                    <label for="date" class="caption mb-0">Date</label>
                    {{ Form::text('valuation_date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
                </div>
                <div class="col-2">
                    <label for="tax" class="caption mb-0">TAX</label>
                    <select class="custom-select" name="tax_id" id="tax-id" autocomplete="off" required>
                        <option value="">-- select tax --</option>
                        @foreach ($additionals as $item)
                            <option value="{{ +$item->value }}">{{ $item->value == 0 ? 'OFF' : +$item->value . '%' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-8">
                    <label for="note" class="caption mb-0">Note</label>
                    {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
                </div>
            </div>  
        </div>
    </div>
</div>

<!-- jobcards / dnotes -->
<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 80vh">
                <table id="jobcardsTbl" class="table pb-2 tfr text-center">
                    <thead class="bg-gradient-directional-blue white pb-1">
                        <tr class="item_header bg-gradient-directional-blue white">
                            <th>Item Type</th>
                            <th>Ref No</th>                                                    
                            <th>Date</th>
                            <th>Technician</th>
                            <th>Equipment</th>
                            <th>Location</th>
                            <th>Fault</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Jobcard/DNote Row Template -->
                        <tr>
                            <td>
                                <select class="custom-select jc_type" name="type[]">
                                    <option value="1" selected>Jobcard</option>
                                    <option value="2">DNote</option> 
                                </select>
                            </td>
                            <td><input type="text" class="form-control jc_ref" name="reference[]"></td>
                            <td><input type="text" class="form-control datepicker jc_date" name="date[]"></td>
                            <td><input type="text" class="form-control jc_tech" name="technician[]"></td>
                            <td><textarea class="form-control jc_equip" name="equipment[]" rows="1"></textarea>
                            <td><input type="text" class="form-control jc_loc" name="location[]"></td>
                            <td>
                                <select class="custom-select jc_fault" name="fault[]">
                                    <option value="none">None</option>
                                    <option value="faulty_compressor">Faulty Compressor</option>
                                    <option value="faulty_pcb">Faulty PCB</option>
                                    <option value="leakage_arrest">Leakage Arrest</option>
                                    <option value="electrical_fault">Electrical Fault</option>
                                    <option value="drainage">Drainage</option>
                                    <option value="other">Other</option>
                                </select>
                            </td>
                            <td><a href="javascript:" class="btn btn-danger btn-md remove" type="button">Remove</a></td>
                            <input type="hidden" name="equipment_id[]" class="jc_equipid">
                            <input type="hidden" name="jcitem_id[]" class="jc_itemid">
                        </tr>
                    </tbody>
                </table>
                <a href="javascript:" class="btn btn-sm btn-success" aria-label="Left Align" id="addJobcard">
                    <i class="fa fa-plus-square"></i>  Jobcard / DNote
                </a> 
            </div>
        </div>
    </div>
</div>

<!-- products -->
<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="table-responsive mb-2 pb-2" style="max-height: 80vh">                            
                <table id="productsTbl" class="table tfr my_stripe_single pb-2 text-center">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            <th>#</th>
                            <th>Item Description</th>
                            <th>UoM</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th width="15%">VAT</th>
                            <th width="5%">% Valuated</th>
                            <th width="5%">Amt Valuated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Product Row Template -->
                        <tr>
                            <td><span class="num"></span></td>
                            <td class="text-left"><span class="descr"></span></td>
                            <td><span class="unit"></span></td>
                            <td><span class="qty"></span></td>         
                            <td><span class="price"></span></td>   
                            <td><span class="amount"></span></td>   
                            <td>
                                <div class="row no-gutters">
                                    <div class="col-5">
                                        <select class="custom-select tax-rate" name="tax_rate[]">
                                            <option value="">--VAT--</option>
                                            @foreach ($additionals as $item)
                                                <option value="{{ +$item->value }}">{{ $item->value == 0 ? 'OFF' : +$item->value . '%' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-7">
                                        <input type="text" class="form-control tax" name="product_tax[]" readonly>
                                    </div>
                                </div>
                            </td>
                            <td><input type="text" class="form-control perc-val" name="perc_valuated[]"></td>                
                            <td><input type="text" class="form-control amount-val" name="total_valuated[]" readonly></td>
                            <input type="hidden" name="id[]" class="item-id">
                            <input type="hidden" name="numbering[]" class="num-inp">
                            <input type="hidden" name="row_type[]" class="type-inp">
                            <input type="hidden" name="row_index[]" class="index-inp">
                            <input type="hidden" name="product_name[]" class="descr-inp">
                            <input type="hidden" name="unit[]" class="unit-inp">
                            <input type="hidden" name="product_qty[]" class="qty-inp">
                            <input type="hidden" name="product_price[]" class="price-inp">
                            <input type="hidden" name="product_subtotal[]" class="subtotal-inp">
                            <input type="hidden" name="product_amount[]" class="amount-inp">
                            <input type="hidden" name="productvar_id[]" class="prodvar-id">
                            <input type="hidden" name="verified_item_id[]" class="verifieditem-id">
                        </tr>
                        <!-- Title Row Template -->
                        <tr>
                            <td><span class="num font-weight-bold"></span></td>
                            <td colspan="8" class="text-left font-weight-bold"><span class="descr"></span></td>
                            <input type="hidden" name="id[]" class="item-id">
                            <input type="hidden" name="verified_item_id[]" class="verifieditem-id">
                            <input type="hidden" name="numbering[]" class="num-inp">
                            <input type="hidden" name="row_type[]" class="type-inp">
                            <input type="hidden" name="row_index[]" class="index-inp">
                            <input type="hidden" name="product_name[]" class="descr-inp">
                            <input type="hidden" name="unit[]">
                            <input type="hidden" name="product_qty[]">
                            <input type="hidden" name="tax_rate[]">
                            <input type="hidden" name="product_tax[]">
                            <input type="hidden" name="product_price[]">
                            <input type="hidden" name="product_subtotal[]">
                            <input type="hidden" name="product_amount[]">
                            <input type="hidden" name="productvar_id[]">
                            <input type="hidden" name="perc_valuated[]" class="perc-val">                
                            <input type="hidden" name="total_valuated[]">
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- summary -->
            <div class="row mt-3">
                <div class="col-6 ml-auto">
                    <div class="table-responsive">
                        <table id="summaryTbl" class="table table-bordered text-center">
                            <thead>
                                <th width="30%">&nbsp;</th>
                                <th>Taxable</th>
                                <th>Tax</th>
                                <th>Subtotal</th>
                                <th>Balance</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="quote-row">Quote</td>
                                    <td>{{ numberFormat(+$quote->taxable ?: +$quote->subtotal) }}</td>
                                    <td>{{ numberFormat($quote->tax) }}</td>
                                    <td>{{ numberFormat($quote->subtotal) }}</td>
                                    <td></td>
                                </tr>
                                <tr class="valx-row">
                                    <td><b>Valuation</b></td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <input type="hidden" name="taxable" id="taxable">
                                    <input type="hidden" name="subtotal" id="subtotal">
                                    <input type="hidden" name="tax" id="tax">
                                    <input type="hidden" name="total" id="total">
                                    <input type="hidden" name="balance" id="balance">
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="edit-form-btn row mt-1">
                {{ link_to_route('biller.job_valuations.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
            </div>
        </div>
    </div>
</div>

@section('after-scripts')
@include('focus.job_valuations.form_js')
@endsection
