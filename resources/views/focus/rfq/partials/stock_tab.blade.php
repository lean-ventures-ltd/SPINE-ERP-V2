<div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="stockTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white ">
                <th width="10%">#</th>
                <th width="35%" class="text-center">{{trans('general.item_name')}}</th>
                <th width="25%" class="text-center">{{trans('general.quantity')}}</th>
                <th width="20%" class="text-center">UoM</th>
                {{-- <th width="10%" class="text-center">{{trans('general.rate')}}</th>
                <th width="10%" class="text-center">{{trans('general.tax_p')}}</th>
                <th width="10%" class="text-center">Tax</th>
                <th width="12%" class="text-center">{{trans('general.amount')}}</th> --}}
                <th width="10%" class="text-center">Action</th>                   
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td><input type="text" class="form-control increment" value="1" id="increment-0" disabled></td>
                <td><input type="text" class="form-control stockname" name="name[]" placeholder="Product Name" id='stockname-0'></td>
                <td><input type="text" class="form-control qty" name="qty[]" id="qty-0" value="1"></td>  
                <td><select name="uom[]" id="uom-0" class="form-control uom" ></select></td> 
                {{-- <td><input type="text" class="form-control price" name="rate[]" id="price-0"></td> --}}
                {{-- <td>
                    <select class="form-control rowtax" name="itemtax[]" id="rowtax-0">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }}
                            </option>
                        @endforeach                                                    
                    </select>
                </td> --}}
                {{-- <td><input type="text" class="form-control taxable" value="0"></td> --}}
                {{-- <td class="text-center">{{config('currency.symbol')}} <b><span class='amount' id="result-0">0</span></b></td>  --}}
                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" id="stockitemid-0" name="item_id[]">
                <input type="hidden" class="stocktaxr" name="taxrate[]">
                <input type="hidden" class="stockamountr" name="amount[]">
                {{-- <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="0"> --}}
                <input type="hidden" name="type[]" value="Stock">
                <input type="hidden" name="id[]" value="0">
            </tr>
            <tr>
                <td colspan="2">
                    <textarea id="stockdescr-0" class="form-control descr" name="description[]" placeholder="Product Description"></textarea>
                </td>
                <td><input type="text" class="form-control product_code" name="product_code[]" id="product_code-0" readonly></td>
                <td>
                    <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid-0">
                        <option value="">-- Warehouse --</option>
                        @foreach ($warehouses as $row)
                            <option value="{{ $row->id }}">{{ $row->title }}</option>
                        @endforeach
                    </select>
                </td>
                <td colspan="3">
                    {{-- <div class="form-group">
                        <label for="project" class="caption">Projects</label>
                        <select class="form-control" name="project_id" id="project" data-placeholder="Search Project by Name, Customer, Branch">
                        </select>
                    </div> --}}
                    <input type="text" class="form-control projectstock" id="projectstocktext-0" placeholder="Search Project By Name">
                    <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" id="projectstockval-0">
                </td>
                <td colspan="6"></td>
            </tr>
            <!-- end layout -->

            <!-- fetched rows -->
            @isset ($po)
                @php ($i = 0)
                @foreach ($po->products as $item)
                    @if ($item->type == 'Stock')
                        <tr>
                            <td><input type="text" class="form-control increment" value="{{$i+1}}" id="increment-0" disabled></td>
                            <td><input type="text" class="form-control stockname" name="name[]" value="{{ $item->description }}" placeholder="Product Name" id='stockname-{{$i}}'></td>
                            <td><input type="text" class="form-control qty" name="qty[]" value="{{ number_format($item->qty, 1) }}" id="qty-{{$i}}"></td>                    
                            <td>
                                <select name="uom[]" id="uom-{{ $i }}" class="form-control uom">
                                    <option value="{{ $item->uom }}" selected>{{ $item->uom }}</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control price" name="rate[]" value="{{ (float) $item->rate }}" id="price-{{$i}}"></td>
                            <td>
                                <select class="form-control rowtax" name="itemtax[]" id="rowtax-{{$i}}">
                                    @foreach ($additionals as $tax)
                                        <option value="{{ (int) $tax->value }}" {{ $tax->value == $item->itemtax ? 'selected' : ''}}>
                                            {{ $tax->name }}
                                        </option>
                                    @endforeach                                                    
                                </select>
                            </td>
                            <td><input type="text" class="form-control taxable" value="{{ (float) $item->taxrate }}" readonly></td>
                            <td class="text-center">{{config('currency.symbol')}} <b><span class='amount' id="result-{{$i}}">{{ (float) $item->amount }}</span></b></td>              
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                            <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                            <input type="hidden" class="stocktaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                            <input type="hidden" class="stockamountr" name="amount[]" value="{{ (float) $item->amount }}">
                            <!--<input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="0">-->
                            <input type="hidden" name="type[]" value="Stock">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        <tr>
                            <td colspan=2>
                                <textarea id="stockdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Product Description">{{ $item->description }}</textarea>
                            </td>
                            <td><input type="text" class="form-control product_code" value="{{$item->product_code}}" name="product_code[]" id="product_code-{{$i}}" readonly></td>
                            <td>
                                <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid-{{$i}}">
                                    <option value="">-- Warehouse --</option>
                                    @foreach ($warehouses as $row)
                                        <option value="{{ $row->id }}" {{ $row->id == $item->warehouse_id? 'selected' : '' }}>
                                            {{ $row->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td colspan="4">
                                <input type="text" class="form-control projectstock" value="{{ $item->project_name }}" id="projectstocktext-{{$i}}" placeholder="Search Project By Name">
                                <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="{{ $item->itemproject_id ? $item->itemproject_id : '0' }}" id="projectstockval-{{$i}}">
                            </td>
                            <td colspan="6"></td>
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
            <!-- end fetched rows -->

            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="7"></td>
            </tr>


        </tbody>
    </table>
</div>
