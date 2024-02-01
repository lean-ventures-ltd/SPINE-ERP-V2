<div class="tab-pane fade in" id="active2" aria-labelledby="link-tab2" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="expTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-danger white">
                <th width="30%" class="text-center">Ledger Name</th>
                <th width="7%" class="text-center">{{trans('general.quantity')}}</th>
                <th width="7%" class="text-center">UoM</th>
                <th width="10%" class="text-center">{{trans('general.rate')}}</th>
                <th width="10%" class="text-center">{{trans('general.tax_p')}}</th>
                <th width="8%" class="text-center">Tax</th>
                <th width="10%" class="text-center">{{trans('general.amount')}}</th>
                <th width="5%" class="text-center">{{trans('general.action')}}</th>
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td><input type="text" class="form-control accountname" name="name[]" placeholder="Enter Ledger" id="accountname-0"></td>
                <td><input type="text" class="form-control exp_qty" name="qty[]" id="expqty-0" value="1"></td>
                <td><input type="text" class="form-control uom" name="uom[]" id="uom-0"></td>                    
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
                <td><button type="button" class="btn btn-danger remove d-none"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" id="expitemid-0" name="item_id[]">
                <input type="hidden" class="exptaxr" name="taxrate[]">
                <input type="hidden" class="expamountr" name="amount[]">
                <input type="hidden" name="type[]" value="Expense">
                <input type="hidden" name="id[]" value="0">
            </tr>
            <tr>
                <td colspan="3">
                    <textarea id="expdescr-0" class="form-control descr" name="description[]" placeholder="Enter Description"></textarea>
                </td>
                <td colspan="5">
                    <input type="text" class="form-control projectexp" id="projectexptext-0" placeholder="Search Project by Name, Customer, Branch">
                    <input type="hidden" name="itemproject_id[]" id="projectexpval-0">
                </td>
            </tr>
            <!-- end layout -->

            <!-- fetched rows -->
            @isset ($po)
                @php ($i = 0)
                @foreach ($po->products as $item)
                    @if ($item->type == 'Expense')
                        <tr>
                            <td><input type="text" class="form-control accountname" name="name[]" value="{{ @$item->account->holder }}" placeholder="Enter Ledger" id="accountname-{{$i}}"></td>
                            <td><input type="text" class="form-control exp_qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="expqty-{{$i}}"></td>
                            <td><input type="text" class="form-control uom" name="uom[]" value="{{ $item->uom }}" id="uom-0"></td>                    
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
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                            <input type="hidden" id="expitemid-{{$i}}" name="item_id[]"value="{{ $item->item_id }}" >
                            <input type="hidden" class="exptaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                            <input type="hidden" class="expamountr" name="amount[]" value="{{ (float) $item->amount }}">
                            <input type="hidden" name="type[]" value="Expense">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        <tr>
                            <td colspan="3">
                                <textarea id="expdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Enter Description">{{ $item->description }}</textarea>
                            </td>
                            <td colspan="5">
                                <input type="text" class="form-control projectexp" value="{{ $item->project ? $item->project->name : '' }}" id="projectexptext-{{$i}}" placeholder="Enter Project">
                                <input type="hidden" name="itemproject_id[]" value="{{ $item->itemproject_id }}" id="projectexpval-{{$i}}">
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
                <td colspan="5" align="right"><b>{{trans('general.grand_total')}} ({{config('currency.symbol')}})</b></td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="expense_grandttl" value="0.00" id="exp_grandttl" readonly>
                    <input type="hidden" name="expense_subttl" value="0.00" id="exp_subttl">
                    <input type="hidden" name="expense_tax" value="0.00" id="exp_tax">
                </td>
            </tr>
        </tbody>
    </table>
</div>
