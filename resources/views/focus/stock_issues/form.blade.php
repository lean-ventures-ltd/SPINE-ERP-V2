<div class="row mb-1">
    <div class="col-md-2 col-12">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
    </div>
    <div class="col-md-2 col-12">
        <label for="ref_no">Reference No.</label>
        {{ Form::text('ref_no', null, ['class' => 'form-control', 'id' => 'ref_no']) }}
    </div>
    <div class="col-md-2 col-12">
        <label for="issue_to">Issue To</label>
        <select name="issue_to" id="issue_to" class="custom-select">
            @foreach (['Employee', 'Customer', 'Project'] as $value)
                <option value="{{ $value }}">
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-12 select-col">
        <label for="employee">Employee</label>
        <select name="employee_id" id="employee" class="form-control" data-placeholder="Search Employee">
            <option value=""></option>
            @foreach ($employees as $row)
                <option value="{{ $row->id }}" {{ @$stock_issue->employee_id == $row->id? 'selected' : ''}}>
                    {{ $row->first_name }} {{ $row->last_name }}
                </option>    
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-12 select-col d-none">
        <label for="customer">Customer</label>
        <select name="customer_id" id="customer" class="form-control d-none" data-placeholder="Search Customer">
            <option value=""></option>
            @foreach ($customers as $row)
                <option value="{{ $row->id }}" {{ @$stock_issue->customer_id == $row->id? 'selected' : ''}}>
                    {{ $row->company ?: $row->name }}
                </option>    
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-12 select-col d-none">
        <label for="project">Project</label>
        <select name="project_id" id="project" class="form-control d-none" data-placeholder="Search Project">
            <option value=""></option>
            @foreach ($projects as $row)
                <option value="{{ $row->id }}" {{ @$stock_issue->project_id == $row->id? 'selected' : ''}}>
                    {{ gen4tid('PRJ-', $row->tid) }} - {{ $row->name }}
                </option>    
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-12">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required' => 'required']) }}
    </div>
</div>
<hr>
<div class="row mb-1">
    <div class="col-md-3 col-12">
        <label for="quote">Load Items From Quote / PI</label>
        <select name="quote_id" id="quote" class="form-control" data-placeholder="Search Quote / PI Number" autocomplete="off">
            <option value=""></option>
            @foreach ($quotes as $row)
                <option value="{{ $row->id }}" {{ @$stock_issue->quote_id == $row->id? 'selected' : ''}}>
                    {{ gen4tid($row->bank_id? 'PI-' : 'QT-', $row->tid) }}
                </option>    
            @endforeach
        </select>
    </div>
</div>

<div class="table-responsive">
    <table id="productsTbl" class="table table-sm tfr my_stripe_single text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="25%">Stock Item</th>
                <th>Unit</th>
                <th>Qty On-Hand</th>
                <th>Qty Rem</th>
                <th>Issue Qty</th>
                <th>Location</th>
                <th>Assigned To</th>
            </tr>
        </thead>
        <tbody>
            @if (@$stock_issue)
                @foreach ($stock_issue->items as $i => $item)
                    <tr>
                        <td><textarea id="name-{{$i+1}}" class="form-control name" cols="30" rows="1" autocomplete="off" required>{{ @$item->productvar->name }}</textarea></td>
                        <td><span class="unit">{{ @$item->productvar->product->unit->code }}</span></td>                
                        <td><span class="qty-onhand">{{ +$item->qty_onhand }}</span></td>
                        <td><span class="qty-rem">{{ +$item->qty_rem }}</span></td>
                        <td><input type="text" name="issue_qty[]" value="{{ +$item->issue_qty }}" class="form-control issue-qty" autocomplete="off" required readonly></td>
                        <td class="td-source">
                            <input type="hidden" name="warehouse_id[]" value="{{ $item->warehouse_id }}" class="source-inp">
                            <select name="warehouse_id[]" id="source-{{$i+1}}" class="form-control source" data-placeholder="Search Location" required disabled>
                                <option value=""></option>
                                <option value="{{ $item->warehouse_id }}" products_qty="{{ +$item->qty_onhand }}" selected>
                                    {{ @$item->warehouse->title }} ({{ +$item->qty_onhand }})
                                </option>
                            </select>
                        </td>
                        <td class="td-assignee">
                            <div class="row no-gutters">
                                <div class="col-md-10">
                                    <select name="assignee_id[]" id="assignee-{{$i+1}}" class="form-control assignee" data-placeholder="Search Employee" disabled>
                                        <option value=""></option>
                                        @foreach ($employees as $row)
                                            <option value="{{ $row->id }}" {{ $item->assignee_id == $row->id? 'selected' : '' }}>
                                                {{ $row->first_name }} {{ $row->last_name }}
                                            </option>    
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge badge-danger float-right mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                                </div>
                            </div>
                        </td>
                        <input type="hidden" name="qty_onhand[]" value="{{ +$item->qty_onhand }}" class="qty-onhand-inp">
                        <input type="hidden" name="qty_rem[]" value="{{ +$item->qty_rem }}" class="qty-rem-inp">
                        <input type="hidden" name="cost[]" value="{{ +$item->cost }}" class="cost">
                        <input type="hidden" name="amount[]" value="{{ +$item->amount }}" class="amount">
                        <input type="hidden" name="productvar_id[]" value="{{ $item->productvar_id }}" class="prodvar-id">
                    </tr>
                @endforeach
            @else
                <tr>
                    <td><textarea id="name-1" class="form-control name" cols="30" rows="1" autocomplete="off" required></textarea></td>
                    <td><span class="unit"></span></td>                
                    <td><span class="qty-onhand"></span></td>
                    <td><span class="qty-rem"></span></td>
                    <td><input type="text" name="issue_qty[]" class="form-control issue-qty" autocomplete="off" required></td>
                    <td class="td-source">
                        <select name="warehouse_id[]" id="source-1" class="form-control source" data-placeholder="Search Location" required>
                            <option value=""></option>
                        </select>
                    </td>
                    <td class="td-assignee">
                        <div class="row no-gutters">
                            <div class="col-md-10">
                                <select name="assignee_id[]" id="assignee-1" class="form-control assignee" data-placeholder="Search Employee" disabled>
                                    <option value=""></option>
                                    @foreach ($employees as $row)
                                        <option value="{{ $row->id }}">
                                            {{ $row->first_name }} {{ $row->last_name }}
                                        </option>    
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <span class="badge badge-danger float-right mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                            </div>
                        </div>
                    </td>
                    <input type="hidden" name="qty_onhand[]" class="qty-onhand-inp">
                    <input type="hidden" name="qty_rem[]" class="qty-rem-inp">
                    <input type="hidden" name="cost[]" class="cost">
                    <input type="hidden" name="amount[]" class="amount">
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
@include('focus.stock_issues.form_js')
@endsection
