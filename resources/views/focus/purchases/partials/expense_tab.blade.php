<div class="tab-pane active" id="active2" aria-labelledby="link-tab2" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="expTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-danger white">
                <th width="5%">#</th>
                <th width="15%" class="text-center">Ledger Name</th>
                <th width="6%" class="text-center">{{trans('general.quantity')}}</th>
                <th width="6%" class="text-center">UoM</th>
                <th width="6%" class="text-center">{{trans('general.rate')}}</th>
                <th width="8%" class="text-center">{{trans('general.tax_p')}}</th>
                <th width="4%" class="text-center">Tax</th>
                <th width="8%" class="text-center">{{trans('general.amount')}} ({{config('currency.symbol')}})</th>
                <th width="5%" class="text-center">{{trans('general.action')}}</th>
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td><input type="text" class="form-control" value="1" id="expenseinc-0" disabled></td>
                <td>
                    <input type="text" class="form-control accountname" name="name[]" id="accountname-0" placeholder="Enter Ledger Account" autocomplete="off">
                    <input type="hidden" id="expitemid-0" name="item_id[]">
                </td>
                <td><input type="text" class="form-control exp_qty" name="qty[]" id="expqty-0" value="1"></td>
                <td><input type="text" class="form-control exp_uom" name="uom[]" id="expuom-0"></td>
                <td><input type="text" class="form-control exp_price" name="rate[]" id="expprice-0"></td>
                <td>
                    <select class="form-control exp_vat" name="itemtax[]" id="expvat-0">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }}
                            </option>
                        @endforeach                                                    
                    </select>
                </td>  
                <td class="text-center"><span class="exp_tax" id="exptax-0">0</span></td>
                <td>{{config('currency.symbol')}} <b><span class="exp_amount" id="expamount-0">0</span></b></td>
                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square"></i></button></td>
                <input type="hidden" class="exptaxr" name="taxrate[]">
                <input type="hidden" class="expamountr" name="amount[]">
                <input type="hidden" name="type[]" value="Expense">
                <input type="hidden" name="id[]" value="0">
                <input type="hidden" name="warehouse_id[]">
            </tr>
            <tr>
                <td colspan="2">
                    <textarea id="expdescr-0" class="form-control descr" name="description[]" placeholder="Enter Description"></textarea>
                </td>
                <td colspan="2">
                    <select id="item_purchase_class-0" name="item_purchase_class[]" class="custom-select item-purchase-class">
                        <option value="">-- Select Purchase Class --</option>
                        @foreach ($purchaseClasses as $pc)
                            <option value="{{ $pc->id }}" @if(@$purchase->purchase_class == $pc->id) selected @endif>
                                {{ $pc->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td colspan="3">
                    <input type="text" class="form-control projectexp" id="projectexptext-0" placeholder="Search Project By Name">
                    <input type="hidden" name="itemproject_id[]" id="projectexpval-0">
                </td>
                <td colspan="2">
                    <select id="item_milestone-0" name="item_milestone[]" class="form-control item-milestone item-milestone-0">
                        <option value="">Select a Budget Line</option>
                    </select>
                </td>
            </tr>
            <!-- end layout -->

            <!-- fetched rows -->
            @isset ($purchase)
                @php ($i = 0)
                @foreach ($purchase->products as $item)
                    @if ($item->type == 'Expense')
                        <tr>
                            <td><input type="text" class="form-control" value="{{$i+1}}" id="expenseinc-{{$i}}" disabled></td>
                            <td><input type="text" class="form-control accountname" name="name[]" value="{{ @$item->account->holder }}" id="accountname-{{$i}}" placeholder="Enter Ledger"></td>
                            <td><input type="text" class="form-control exp_qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="expqty-{{$i}}"></td>
                            <td><input type="text" class="form-control exp_uom" name="uom[]" value="{{ $item->uom }}" id="expuom-{{$i}}"></td>
                            <td><input type="text" class="form-control exp_price" name="rate[]" value="{{ (float) $item->rate }}" id="expprice-{{$i}}"></td>
                            <td>
                                <select class="form-control exp_vat" name="itemtax[]" id="expvat-{{$i}}">
                                    @foreach ($additionals as $tax)
                                        <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->itemtax ? 'selected' : ''}}>
                                            {{ $tax->name }}
                                        </option>
                                    @endforeach                  
                                </select>
                            </td>                          
                            <td class="text-center"><span class="exp_tax" id="exptax-{{$i}}">{{ (float) $item->taxrate }}</span></td>
                            <td>{{config('currency.symbol')}} <b><span class="exp_amount" id="expamount-{{$i}}">{{ (float) $item->amount }}</span></b></td>
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square"></i></button></td>
                            <input type="hidden" id="expitemid-{{$i}}" name="item_id[]"value="{{ $item->item_id }}" >
                            <input type="hidden" class="exptaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                            <input type="hidden" class="expamountr" name="amount[]" value="{{ (float) $item->amount }}">
                            <input type="hidden" name="type[]" value="Expense">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="warehouse_id[]">
                        </tr>
                        <tr>
                            <td colspan="2">
                                <textarea id="expdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Enter Description">{{ $item->description }}</textarea>
                            </td>
                            <td colspan="2">
                                <select id="item_purchase_class-0" name="item_purchase_class[]" class="custom-select item-purchase-class">
                                    <option value="">-- Select Purchase Class --</option>
                                    @foreach ($purchaseClasses as $pc)
                                        <option value="{{ $pc->id }}" @if(@$purchase->purchase_class == $pc->id) selected @endif>
                                            {{ $pc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td colspan="3">
                                <input type="text" class="form-control projectexp" value="{{ $item->project ? $item->project->name : '' }}" id="projectexptext-{{$i}}" placeholder="Enter Project">
                                <input type="hidden" name="itemproject_id[]" value="{{ $item->itemproject_id }}" id="projectexpval-{{$i}}">
                            </td>
                            <td colspan="2">
                                <select id="item_milestone-0" name="item_milestone[]" class="form-control item-milestone">
                                    <option value="">Select a Budget Line</option>
                                </select>
                            </td>

                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
            <!-- end fetched rows -->

            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addexp">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="6"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="5" align="right"><b>{{trans('general.total_tax')}}</b></td>
                <td align="left" colspan="2">{{config('currency.symbol')}} <span id="exprow_taxttl" class="lightMode">0</span></td>
            </tr>
            <tr class="bg-white">
                <td colspan="5" align="right"><b>Expense Total ({{config('currency.symbol')}})</b></td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="expense_grandttl" value="0.00" id="exp_grandttl" readonly>
                    <input type="hidden" name="expense_subttl" value="0.00" id="exp_subttl">
                    <input type="hidden" name="expense_tax" value="0.00" id="exp_tax">
                </td>
            </tr>
        </tbody>
    </table>
</div>
