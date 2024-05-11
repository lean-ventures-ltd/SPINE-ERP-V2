<div class="form-group row">
    <div class="col-md-2 col-12">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div>
    <div class="col-md-4 col-12">
        <label for="adj_type">Adjustment Type</label>
        <select name="adj_type" id="adj_type" class="custom-select">
            @foreach (['Qty' => 'Qty' , 'Cost' => 'Cost', 'Qty-Cost' => 'Cost And Qty'] as $key => $value)
                <option value="{{ $key }}">
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-12">
        <label for="account_id">Adjustment Account</label>
        <select name="account_id" id="account" class="custom-select" required>
            @foreach ($accounts as $key => $account)
                @if($row->holder !== 'Stock Gain' && $row->holder !== 'Others' && $row->holder !== 'Point of Sale' && $row->holder !== 'Loan Penalty Receivable' && $row->holder !== 'Loan Interest Receivable')
                    <option value="{{ $account->id }}" account_type="{{ $account->account_type }}">
                        {{ $account->number }} - {{ $account->holder }}
                    </option>
                @endif
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <div class="col-md-12 col-12">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required' => 'required']) }}
    </div>
</div>

<div class="table-responsive">
    <table id="productsTbl" class="table table-sm tfr my_stripe_single text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="30%">Stock Item</th>
                <th>Unit</th>
                <th>Qty On-Hand</th>
                <th class="h-qty">New Qty</th>
                <th class="h-qty">Qty Diff</th>
                <th class="h-cost" width="15%">Cost</th>
                <th width="25%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if (@$stock_adj)
                @foreach ($stock_adj->items as $i => $item)
                    <tr>
                        <td><textarea id="name-{{$i+1}}" class="form-control name" cols="30" rows="1" autocomplete="off">{{ @$item->productvar->name }}</textarea></td>
                        <td><span class="unit">{{ @$item->productvar->product->unit->code }}</span></td>                
                        <td><span class="qty-onhand">{{ +$item->qty_onhand }}</span></td>
                        <td><input type="text" name="new_qty[]" value="{{ +$item->new_qty }}" class="form-control new-qty" autocomplete="off"></td>
                        <td><input type="text" name="qty_diff[]" value="{{ +$item->qty_diff }}" class="form-control qty-diff" autocomplete="off"></td>
                        <td><input type="text" name="cost[]" value="{{ numberFormat($item->cost) }}" class="form-control cost" autocomplete="off"></td>
                        <td>
                            <span class="badge badge-danger float-right mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                            <input type="text"  name="amount[]" value="{{ numberFormat($item->amount) }}" class="form-control col-10 pr-0 pl-0 amount" autocomplete="off" readonly>
                        </td>
                        <input type="hidden" name="qty_onhand[]" value="{{ +$item->qty_onhand }}" class="qty-onhand-inp">
                        <input type="hidden" name="productvar_id[]" value="{{ $item->productvar_id }}" class="prodvar-id">
                    </tr>
                @endforeach
            @else
                <tr>
                    <td><textarea id="name-1" class="form-control name" cols="30" rows="1" autocomplete="off"></textarea></td>
                    <td><span class="unit"></span></td>                
                    <td><span class="qty-onhand"></span></td>
                    <td><input type="text" name="new_qty[]" class="form-control new-qty" autocomplete="off"></td>
                    <td><input type="text" name="qty_diff[]" class="form-control qty-diff" autocomplete="off"></td>
                    <td><input type="text" name="cost[]" class="form-control cost" autocomplete="off"></td>
                    <td>
                        <span class="badge badge-danger float-right mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                        <input type="text"  name="amount[]" class="form-control col-10 pr-0 pl-0 amount" autocomplete="off" readonly>
                    </td>
                    <input type="hidden" name="qty_onhand[]" class="qty-onhand-inp">
                    <input type="hidden" name="productvar_id[]" class="prodvar-id">
                </tr>
            @endif
        </tbody>
    </table>
</div>   
<div class="row mt-1">
    <div class="col-6">
        <button type="button" class="btn btn-success" id="add-item">
            <i class="fa fa-plus-square"></i> Item
        </button>
    </div>
</div>             

<div class="form-group row">
    <div class="col-2 ml-auto">
        <label for="total" class="mb-0">Total Amount</label>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total','readonly' => 'readonly', 'autocomplete' => "off"]) }}
    </div>
</div>

@section('extra-scripts')
@include('focus.stock_adjs.form_js')
@endsection
