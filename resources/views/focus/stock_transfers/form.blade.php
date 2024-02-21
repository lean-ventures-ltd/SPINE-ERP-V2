<div class="row mb-1">
    <div class="col-md-2 col-12">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div>
    <div class="col-md-2 col-12">
        <label for="ref_no">Reference No.</label>
        {{ Form::text('ref_no', null, ['class' => 'form-control', 'id' => 'ref_no']) }}
    </div>
    <div class="col-md-4 col-12">
        <label for="source">Transfer From</label>
        <select name="source_id" id="source" class="form-control" data-placeholder="Search Source" autocomplete="off" required>
            <option value=""></option>
            @foreach ($source_warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" {{ @$stock_transfer->source_id == $warehouse->id? 'selected' : '' }}>
                    {{ $warehouse->title }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 col-12">
        <label for="destination">Transfer To</label>
        <select name="dest_id" id="dest" class="form-control" data-placeholder="Search Destination" autocomplete="off" required>
            <option value=""></option>
            @foreach ($dest_warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" {{ @$stock_transfer->dest_id == $warehouse->id? 'selected' : '' }}>
                    {{ $warehouse->title }}
                </option>
            @endforeach
        </select>
    </div>  
</div>
<div class="row mb-1">
    <div class="col-md-12">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div>
</div>
<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="productsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="30%">Stock Item</th>
                <th>Unit</th>
                <th>Qty On-Hand</th>
                <th>Qty Rem</th>
                <th width="10%">Transf. Qty</th>
            </tr>
        </thead>
        <tbody>
            @if (@$stock_transfer)
                @foreach ($stock_transfer->items as $i => $item)
                    <tr>
                        <td><textarea id="name-{{$i+1}}" class="form-control name" cols="30" rows="1" autocomplete="off">{{ @$item->productvar->name }}</textarea></td>
                        <td><span class="unit">{{ @$item->productvar->product->unit->code }}</span></td>                
                        <td><span class="qty-onhand">{{ +$item->qty_onhand }}</span></td>
                        <td><span class="qty-rem">{{ +$item->qty_rem }}</span></td>
                        <td>
                            <span class="badge badge-danger float-right mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                            <input type="text"  name="qty_transf[]" value="{{ +$item->qty_transf }}" class="form-control col-8 pr-0 pl-0 qty-transf" autocomplete="off">
                        </td>
                        <input type="hidden" name="amount[]" value="{{ $item->amount }}" class="amount">
                        <input type="hidden" name="cost[]" value="{{ $item->cost }}" class="cost">
                        <input type="hidden" name="qty_rem[]" value="{{ +$item->qty_rem }}" class="qty-rem-inp">
                        <input type="hidden" name="qty_onhand[]" value="{{ +$item->qty_onhand }}" class="qty-onhand-inp">
                        <input type="hidden" name="productvar_id[]" value="{{ $item->productvar_id }}" class="prodvar-id">
                    </tr>
                @endforeach
            @else
                <tr>
                    <td><textarea id="name-1" class="form-control name" cols="30" rows="1" autocomplete="off"></textarea></td>
                    <td><span class="unit"></span></td>                
                    <td><span class="qty-onhand"></span></td>
                    <td><span class="qty-rem"></span></td>
                    <td>
                        <span class="badge badge-danger float-right mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                        <input type="text"  name="qty_transf[]" class="form-control col-8 pr-0 pl-0 qty-transf" autocomplete="off">
                    </td>
                    <input type="hidden" name="amount[]" class="amount">
                    <input type="hidden" name="cost[]" class="cost">
                    <input type="hidden" name="qty_rem[]" class="qty-rem-inp">
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
{{ Form::hidden('total', null, ['id' => 'total']) }}


@section('extra-scripts')
@include('focus.stock_transfers.form_js')
@endsection