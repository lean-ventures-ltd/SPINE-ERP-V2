<div class="row">
    {{-- <div class="col-sm-6 cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">
            <h3 class="title">Bill </h3>                                                                
            <div class="form-group row">
                <div class="col-12">
                    <label for="payer" class="caption">Search Supplier</label>                                       
                    <select class="form-control" id="supplierbox" data-placeholder="Search Supplier"></select>
                    <input type="hidden" name="supplier_id" value="0" id="supplierid">
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-sm-8">
                    <label for="payer" class="caption">Supplier Name*</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>                                            
                        {{ Form::text('suppliername', null, ['class' => 'form-control round', 'placeholder' => 'Supplier Name', 'id' => 'supplier', 'disabled']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="taxid" class="caption">Tax ID</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('supplier_taxid', null, ['class' => 'form-control round', 'placeholder' => 'Tax Id', 'id' => 'taxid', 'disabled']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <table class="table-responsive tfr" id="transxnTbl">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            @foreach (['Item', 'Inventory Item', 'Expenses', 'Asset & Equipments', 'Total'] as $val)
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
                            <td align="right" colspan="3">
                                @foreach (['paidttl', 'grandtax', 'grandttl'] as $val)
                                    <input type="hidden" name="{{ $val }}" id="{{ $val }}" value="0"> 
                                @endforeach 
                                {{ Form::submit('Generate Purchase Order', ['class' => 'btn btn-success sub-btn btn-lg']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div> --}}

    <div class="col-sm-12 cmp-pnl">
        <div class="inner-cmp-pnl">
            <h3 class="title">Properties</h3>
            <div class="form-group row">
                <div class="col-sm-4">
                    <label for="tid" class="caption">RFQ No.</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('tid', gen4tid("{$prefixes[0]}-", @$po? $po->tid : $last_tid+1), ['class' => 'form-control round', 'disabled']) }}
                        {{ Form::hidden('tid', @$po? $po->tid : $last_tid+1) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="transaction_date" class="caption">RFQ Date*</label>
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
                <div class="col-4">
                    <label for="taxFormat" class="caption">Tax</label>
                    <select class="custom-select" name="tax" id="tax">
                        <option value="inclusive" {{ "inclusive" == @$po->tax ? 'selected' : ''}}>Inclusive</option>
                        <option value="exclusive" {{ "exclusive" == @$po->tax ? 'selected' : ''}}>Exclusive</option>
                    </select>
                </div>

                {{-- <div class="col-4">
                    <label for="pricing" >Pricing</label>                    
                    <select id="pricegroup_id" name="pricegroup_id" class="custom-select">
                        <option value="0" selected>Default </option>
                        @foreach($price_supplier as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            
                        @endforeach
                    </select>                    
                </div> --}}

                <div class="col-4">
                    <label for="terms">Terms</label>
                    <select name="term_id" class="form-control">
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ $term->id == @$po->term_id ? 'selected' : ''}}>
                                {{ $term->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="toAddInfo" class="caption">Subject*</label>
                    {{ Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => trans('general.note'), 'rows'=>'2', 'required']) }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="project" class="caption">Projects</label>
                        <select class="form-control" name="project_id" id="project" data-placeholder="Search Project by Name, Customer, Branch">
                        </select>
                    </div>
                </div>
            </div>                                                                
            {{-- <div class="form-group row">
                <div class="col-12">
                    <label for="payer" class="caption">Requisition Items</label>                                       
                    <select class="form-control" id="quoteselect" data-placeholder="Search Quote">
                        <option value="">-----Select Requisition Items-----</option>
                        <option value="all">All Items</option>
                    </select>
                    <input type="hidden" name="quote_id" value="0" id="quoteid">
                </div>
            </div> --}}
        </div>
    </div>
</div>

<!-- Tab Menus -->
<ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
    <li class="nav-item bg-gradient-directional-blue">
        <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Inventory/Stock Items</a>
    </li>
    <li class="nav-item bg-danger">
        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Expenses</a>
    </li>
    <li class="nav-item bg-success">
        <a class="nav-link text-danger" id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Assets & Equipments</a>
    </li>
    {{-- <li class="nav-item bg-secondary">
        <a class="nav-link" id="active-tab4" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">Queued Requisition Items</a>
    </li> --}}
</ul>
<div class="tab-content px-1 pt-1">
    <!-- tab1 -->
    @include('focus.rfq.partials.stock_tab')
    <!-- tab2 -->
    @include('focus.rfq.partials.expense_tab')
    <!-- tab3 -->
    @include('focus.rfq.partials.asset_tab')
    <!-- tab4 -->
    {{-- @include('focus.purchaseorders.partials.queue_stock') --}}
</div>
<input type="hidden" name="supplier_type" value="supplier">