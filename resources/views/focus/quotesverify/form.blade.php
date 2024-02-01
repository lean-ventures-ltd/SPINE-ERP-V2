@php
    $label = $quote->bank_id ? 'PI' : 'Quote';
    $prefixes = prefixesArray(['quote', 'proforma_invoice'], $quote->ins);
@endphp
<div class="row">
    {{ Form::hidden('id', $quote->id) }}
    <div class="col-6 cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="fcol-sm-12">
                    <h3 class="title pl-1">Verify {{ $label }}</h3>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-6">                                        
                    <label for="serial_no" class="caption">{{ $label . ' ' . trans('general.serial_no') }}</label>
                    <div class="input-group">
                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>                                           
                        @php
                            $tid = gen4tid($label == 'PI'? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                        @endphp
                        {{ Form::text('tid', $tid . $quote->revision, ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                    </div>
                </div>    
                <div class="col-6">
                    <label for="date" class="caption">{{ $label }} Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('date', null, ['class' => 'form-control round datepicker', 'id'=>'date', 'disabled']) }}
                    </div>
                </div>                                
            </div>

            <div class="form-group row">                                    
                <div class="col-7">
                    <label for="client" class="caption">Client</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client', @$quote->customer->name, ['class' => 'form-control round', 'id' => 'client', 'disabled']) }}
                        <input type="hidden" name="client_id" value="{{ @$quote->customer_id }}" id="client_id">
                    </div>
                </div>
                <div class="col-5">
                    <label for="branch" class="caption">Branch</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('branch', @$quote->branch->name, ['class' => 'form-control round', 'id' => 'branch', 'disabled']) }}
                        <input type="hidden" name="branch_id" value="{{ @$quote->branch_id }}" id="branch_id">
                    </div>
                </div>
            </div> 
            <div class="form-group row">
                <div class="col-12">
                    <label for="subject" class="caption">Subject / Title</label>
                    {{ Form::text('notes', null, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
                </div>
            </div>  
        </div>
    </div>

    <div class="col-6 cmp-pnl">
        <div class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="col-sm-12">
                    <h3 class="title">Properties</h3>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-4">
                    <label for="client_ref" class="caption">Client Ref / Callout ID</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('client_ref', null, ['class' => 'form-control round', 'placeholder' => 'Client Reference', 'id' => 'client_ref', 'disabled']) }}
                    </div>
                </div>   
                <div class="col-4">
                    <label for="invocieno" class="caption">Djc Reference</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('reference', null, ['class' => 'form-control round', 'disabled']) }}
                    </div>
                </div>
                <div class="col-4">
                    <label for="reference_date" class="caption">Reference Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('reference_date', null, ['class' => 'form-control round datepicker', 'id'=>'reference-date', 'disabled']) }}
                    </div>
                </div>             
            </div>
            <div class="form-group row">
                <div class="col-4">
                    <label for="quote_subtotal">{{ $label }} Subtotal</label>
                    {{ Form::text('quote_subtotal', numberFormat($quote->subtotal), ['class' => 'form-control', 'id' => 'quote_subtotal',  'disabled']) }}
                </div>
                <div class="col-4">
                    <label for="quote_subtotal">{{ $label }} Total</label>
                    {{ Form::text('quote_total', numberFormat($quote->total), ['class' => 'form-control', 'id' => 'quote_total', 'disabled']) }}
                </div>    
                <div class="col-4">
                    <label for="currency">Currency</label>
                    {{ Form::text('currency', $quote->currency? $quote->currency->code : '', ['class' => 'form-control', 'id' => 'currency', 'disabled']) }}
                </div>              
            </div>   
            <div class="form-group row">
                <div class="col-12">
                    <label for="gen_remark" class="caption">General Remark</label>
                    {{ Form::text('gen_remark', null, ['class' => 'form-control', 'id' => 'gen_remark']) }}
                </div>
            </div>   
        </div>
    </div>                        
</div>                  

<div>                            
    <table id="productsTbl" class="table-responsive tfr my_stripe_single pb-2 text-center">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white">
                <th width="5%">#</th>
                <th width="35%">Item Name</th>
                <th width="7%">UoM</th>
                <th width="7%">Qty</th>
                <th width="10%">{{trans('general.rate')}}</th>
                <th width="10%">{{trans('general.rate')}} (VAT Inc)</th>
                <th width="10%">{{trans('general.amount')}} </th>
                <th width="12%">Remark</th>
                <th width="5%">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="row">
        <div class="col-10 col-xs-7">
            <a href="javascript:" class="btn btn-success mr-1" aria-label="Left Align" id="add-product">
                <i class="fa fa-plus-square"></i> Product
            </a>
            <a href="javascript:" class="btn btn-primary" aria-label="Left Align" id="add-title">
                <i class="fa fa-plus-square"></i> Title
            </a>

            <div class="form-group row pt-2">
                <div class="col-sm-12">
                    <table id="jobcardTbl" class="table-responsive pb-2 tfr text-center">
                        <thead class="bg-gradient-directional-blue white pb-1">
                            <tr>
                                <th width="10%">Type</th>
                                <th width="12%">Ref No</th>                                                    
                                <th width="12%">Date</th>
                                <th width="15%">Technician</th>
                                <th width="15%">Equipment</th>
                                <th width="12%">Location</th>
                                <th width="16%">Fault</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <a href="javascript:" class="btn btn-success" aria-label="Left Align" id="add-jobcard">
                        <i class="fa fa-plus-square"></i>  Jobcard / DNote
                    </a>                                            
                </div>
            </div>     
        </div>

        <div class="col-2 col-xs-5 invoice-block pull-right">
            <div>
                <label class="m-0">Taxable</label>
                <div class="input-group m-bot15">
                    <input type="text" name="taxable" id="taxable" class="form-control" readonly>
                </div>
            </div>
            <div>
                <label class="m-0">Subtotal</label>
                <div class="input-group m-bot15">
                    <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>
                </div>
            </div>
            <div>
                <label class="m-0">{{trans('general.total_tax')}}</label>
                <div class="input-group m-bot15">
                    <input type="text" name="tax" id="tax" class="form-control" readonly>
                </div>
            </div>
            <div>
                <label class="m-0">{{trans('general.grand_total')}}</label>
                <div class="input-group m-bot15">
                    <input type="text" name="total" class="form-control" id="total" placeholder="Total" readonly>
                </div>
            </div>
            <div class="form-group mt-1">
                <button type="button" class="btn btn-danger" aria-label="Left Align" id="reset-items">
                    <i class="fa fa-trash"></i> Undo
                </button>
                {{ Form::submit('Submit', ['class' => 'btn btn-success']) }}
            </div>
            
        </div>
    </div>
</div>