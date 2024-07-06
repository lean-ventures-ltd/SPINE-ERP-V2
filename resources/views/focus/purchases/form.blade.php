<div class="row">
    <div class="col-sm-6 cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">
            <h3 class="title">Bill </h3>                                                                
            <div class="form-group row">
                <div class="col-5">
                    <input type="hidden" value="0" id="credit">
                    <input type="hidden" value="0" id="total_aging">
                    <input type="hidden" value="0" id="outstanding_balance">
                    <div><label for="supplier-type">Select Supplier Type</label></div>
                    <div class="d-inline-block custom-control custom-checkbox mr-1">
                        <input type="radio" class="custom-control-input bg-primary" name="supplier_type" id="colorCheck1" value="walk-in" checked>
                        <label class="custom-control-label" for="colorCheck1">Walkin</label>
                    </div>
                    <div class="d-inline-block custom-control custom-checkbox mr-1">
                        <input type="radio" class="custom-control-input bg-purple" name="supplier_type" value="supplier" id="colorCheck3">
                        <label class="custom-control-label" for="colorCheck3">{{trans('suppliers.supplier')}}</label>
                    </div>
                </div>
                <div class="col-7">
                    <label for="payer" class="caption">Search Supplier</label> 
                    <a href="{{ route('biller.suppliers.create') }}" class="btn btn-blue btn-sm round float-right add-supplier">
                        <i class="fa fa-plus-circle"></i> supplier
                    </a>                                     
                    <select class="form-control" id="supplierbox" data-placeholder="Search Supplier" disabled></select>
                    <input type="hidden" name="supplier_id" value="{{ @$purchase->supplier_id ?: 1 }}" id="supplierid">
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-sm-8">
                    <label for="payer" class="caption">Supplier Name*</label>
                    <div class="input-group ">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>                                            
                        {{ Form::text('suppliername', null, ['class' => 'form-control round', 'placeholder' => 'Supplier Name', 'id' => 'supplier', 'required']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="taxid" class="caption">Tax ID</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('supplier_taxid', null, ['class' => 'form-control round', 'placeholder' => 'Tax Id', 'id'=>'taxid']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-4">
                    <label for="pricing">Pricing</label>            
                    <select id="pricegroup_id" name="pricegroup_id"  class="form-control">
                        <option value="0" selected>Default </option>
                        @foreach($price_supplier as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>                    
                </div>
                <div class="col-3">
                    <label for="taxFormat" class="caption">Tax</label>F
                    <select class="form-control" name="tax" id="tax">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }} 
                            </option>
                        @endforeach                                                    
                    </select>
                </div>
                <div class="col-5">
                    <div><label for="vat_on_amount">Tax on Amount</label></div>
                    <div class="d-inline form-check mr-1">
                        <input type="radio" class="form-check-input bg-primary is_tax_exc" name="is_tax_exc" value="1" id="tax_exc" checked>
                        <label for="exclusive">Exclusive</label>
                    </div>
                    <div class="d-inline form-check">
                        <input type="radio" class="form-check-input bg-purple is_tax_exc" name="is_tax_exc" value="0" id="tax_inc">
                        <label for="inclusive">Inclusive</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <table class="table-responsive tfr" id="transxnTbl">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            @foreach (['Item', 'Inventory / Stock', 'Expense', 'Asset & Equipment', 'Total'] as $val)
                                <th width="20%" class="text-center">{{ $val }}</th>
                            @endforeach                                                  
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">Line Total</td>
                            @for ($i = 0; $i < 4; $i++)
                                <td class="text-center">0.00</td>
                            @endfor                                                
                        </tr>                                                  
                        <tr>
                            <td class="text-center">Tax</td>
                            @for ($i = 0; $i < 4; $i++)
                                <td class="text-center">0.00</td>
                            @endfor                                                
                        </tr>
                        <tr>
                            <td class="text-center">Grand Total</td>
                            @for ($i = 0; $i < 4; $i++)
                                <td class="text-center">0.00</td>
                            @endfor                                                                                                      
                        </tr>

                        <tr class="sub_c" style="display: table-row;">
                            <td align="right" colspan="4">
                                <p id="milestone_warning" class="text-red ml-2" style="display: inline-block; color: red; font-size: 16px; "> </p>
                                <p id="budget_warning" class="text-red ml-2" style="display: inline-block; color: red; font-size: 16px; "> </p>
                            </td>
                        </tr>

                        <tr class="sub_c" style="display: table-row;">
                            <td align="right" colspan="3">
                                @foreach (['paidttl', 'grandtax', 'grandttl'] as $val)
                                    <input type="hidden" name="{{ $val }}" id="{{ $val }}" value="0"> 
                                @endforeach 
                                {{ Form::submit('Post Transaction', ['class' => 'btn btn-success sub-btn btn-lg']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-sm-6 cmp-pnl">
        <div class="inner-cmp-pnl">
            <h3 class="title">{{trans('purchaseorders.properties')}}</h3>
            <div class="form-group row">
                <div class="col-sm-4">
                    <label for="tid" class="caption">Transaction ID*</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::number('tid', @$purchase? $purchase->tid : $last_tid+1, ['class' => 'form-control round', 'readonly']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="transaction_date" class="caption">Purchase Date*</label>
                    <div class="input-group">                                            
                        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="due_date" class="caption">Due Date*</label>
                    <div class="input-group">                                            
                        {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'due_date']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-4"><label for="ref_type" class="caption">Document Type*</label>
                    <div class="input-group">                                            
                        <select class="form-control" name="doc_ref_type" id="ref_type" required>
                            <option value="">-- Select Type --</option>
                            @foreach (['Receipt', 'DNote', 'Voucher', 'Invoice'] as $val)
                                <option value="{{ $val }}">{{ $val }}</option>
                            @endforeach                                                        
                        </select>
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="refer_no" class="caption">{{trans('general.reference')}} No.</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>                                            
                        <input id="reference_no" type="text" name="doc_ref" class="form-control round" placeholder="{{ trans('general.reference') }}" value="{{ @$purchase->doc_ref }}" required>
                    </div>
                </div>
                <div class="col-sm-4">
                        <label for="refer_no" class="caption"> CU Invoice no. </label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        <input type="text" id="cu_invoice_no" name="cu_invoice_no" class="form-control round" placeholder="CU Invoice No." value="{{ @$purchase->cu_invoice_no }}" @if(empty(@$purchase->cu_invoice_no))  readonly @endif>


                    </div>
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-12">
                    <label for="toAddInfo" class="caption">{{trans('general.note')}}*</label>
                    {{ Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => trans('general.note'), 'rows'=>'2', 'required']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="project" class="caption">Project</label>
                        <select class="form-control" name="project_id" id="project" data-placeholder="Search Project by Name, Customer, Branch">
                        </select>
                    </div>
                </div>



                <div class="col-6">
                    <label for="project_milestone" class="caption" style="display: inline-block;">Project Budget Line</label>

{{--                    <p id="milestone_warning" class="text-red ml-2" style="display: inline-block; color: red; font-size: 16px; "> </p>--}}
                    <select id="project_milestone" name="project_milestone" class="form-control">
                        <option value="">Select a Budget Line</option>
                    </select>
                </div>

                <div class="col-6">
                    <label for="purchase_class" class="caption" style="display: inline-block;">Purchase Class</label>
                    <select id="purchase_class" name="purchase_class" class="custom-select round" >
                        <option value="">-- Select Purchase Class --</option>
                        @foreach ($purchaseClasses as $pc)
                            <option value="{{ $pc->id }}" @if(@$purchase->purchase_class == $pc->id) selected @endif>
                                {{ $pc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label for="payer" class="caption">Requisition Items</label>
                    <select class="form-control" id="quoteselect" data-placeholder="Search Quote">
                        <option value="">-----Select Requisition Items-----</option>
                        <option value="all">All Items</option>
                    </select>
                    <input type="hidden" name="quote_id" value="0" id="quoteid">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Menus -->
<ul class="nav nav-tabs nav-top-border nav-justified mt-3" role="tablist">
    <li class="nav-item bg-blue">
        <a class="nav-link text-black" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">
            <h4 class="mt-1" style="color: #0b0b0b">Inventory / Stock</h4>
        </a>
    </li>
    <li class="nav-item bg-danger">
        <a class="nav-link active text-black" id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">
            <h4 class="mt-1" style="color: #0b0b0b">Expense</h4>
        </a>
    </li>
    <li class="nav-item bg-success">
        <a class="nav-link text-black" id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">
            <h4 class="mt-1" style="color: #0b0b0b">Assets & Equipment</h4>
        </a>
    </li>
</ul>
<div class="tab-content px-1 pt-1">
    <!-- stock tab -->
    @include('focus.purchases.partials.stock_tab')
    <!-- expense tab -->
    @include('focus.purchases.partials.expense_tab')
    <!-- asset tab -->
    @include('focus.purchases.partials.asset_tab')
</div>
