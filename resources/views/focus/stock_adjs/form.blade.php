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
                <option value="{{ $account->id }}" account_type="{{ $account->account_type }}">
                    {{ $account->number }} - {{ $account->holder }}
                </option>
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
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        autoCompleteCb: () => {
            return {
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('biller.products.quote_product_search') }}",
                        data: {keyword: request.term},
                        method: 'POST',
                        success: result => response(result.map(v => ({
                            label: `${v.name} (${v.warehouse? v.warehouse.title : ''})`,
                            value: v.name,
                            data: v
                        }))),
                    });
                },
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    const {data} = ui.item;
                    let row = Index.currRow;
                    row.find('.prodvar-id').val(data.id); 
                    row.find('.qty-onhand').text(accounting.unformat(data.qty));
                    row.find('.qty-onhand-inp').val(accounting.unformat(data.qty));
                    row.find('.cost').val(accounting.unformat(data.purchase_price));
                    if (data.units && data.units.length) {
                        const unit = data.units[0];
                        row.find('.unit').text(unit.code);
                    }
                }
            };
        }
    };

    const Index = {
        currRow: '',
        init() {
            $('#productsTbl tbody td').css({paddingLeft: '5px', paddingRight: '5px', paddingBottom: 0});
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#name-' + 1).autocomplete(config.autoCompleteCb());

            $('#adj_type').change(Index.adjTypeChange).change();
            $('#add-item').click(Index.addItemClick);
            $('#account').change(Index.accountChange);
            $('#productsTbl').on('keyup', '.new-qty, .qty-diff, .cost', Index.qtyCostKeyUp);
            $('#productsTbl').on('keyup', '.name', function() { Index.currRow = $(this).parents('tr') });
            $('#productsTbl').on('click', '.remove', Index.removeRowClick);
                
            const stockAdj = @json(@$stock_adj);
            if (stockAdj && stockAdj.id) {
                $('.datepicker').datepicker('setDate', new Date(stockAdj.date));
                $('#adj_type').val(stockAdj.adj_type);
                $('#account').val(stockAdj.account_id);
                Index.calcTotals();
            }
        },

        addItemClick() {
            let row = $('#productsTbl tbody tr:last').clone();
            let indx = accounting.unformat(row.find('.name').attr('id').split('-')[1]);
            row.find('input').attr('value', '');
            row.find('textarea').text('');
            row.find('.unit, .qty-onhand').text('');
            row.find('.name').attr('id', `name-${indx+1}`);
            $('#productsTbl tbody').append(`<tr>${row.html()}</tr>`);
            $(`#name-${indx+1}`).autocomplete(config.autoCompleteCb());
        },

        removeRowClick() {
            let row = $(this).parents('tr');
            if (row.siblings().length) {
                row.remove();
            } else {
                row.find('input, textarea').each(function() { $(this).val(''); });
                row.find('.unit, .qty-onhand').text('');            
            }
            Index.calcTotals();
        },

        adjTypeChange() {
            ['.h-cost', '.h-qty'].forEach(v => $(v).removeClass('d-none'));
            ['.cost', '.new-qty','.qty-diff'].forEach(v => $(v).parents('td').removeClass('d-none'));
            if (this.value == 'Qty') {
                $('.h-cost').addClass('d-none');
                $('.cost').parents('td').addClass('d-none');
            } else if (this.value == 'Cost') {
                $('.h-qty').addClass('d-none');
                ['.new-qty','.qty-diff'].forEach(v => $(v).parents('td').addClass('d-none'));
            } 
            window.scrollTo(0, document.body.scrollHeight);
            window.scrollTo(document.body.scrollHeight, 0);
        },

        accountChange() {
            $('#productsTbl').find('.new-qty, .qty-diff, .amount').val('');
            $('#total').val('');
        },

        qtyCostKeyUp() {
            const row = $(this).parents('tr');
            const qtyOnhand = accounting.unformat(row.find('.qty-onhand').text());
            const cost = accounting.unformat(row.find('.cost').val());
            let qtyDiff = accounting.unformat(row.find('.qty-diff').val());
            let newQty = accounting.unformat(row.find('.new-qty').val());
            let amount = 0;
            const accountType = $('#account option:selected').attr('account_type');
            if ($(this).is('.qty-diff')) {
                let newQty = qtyOnhand + qtyDiff;
                if ((qtyDiff < 0 && accountType == 'Income') || (qtyDiff > 0 && accountType == 'Expense')) {
                    qtyDiff = 0;
                }
                if (qtyDiff == 0) amount = 0;
                else amount = qtyDiff * cost;
                row.find('.new-qty').val(accounting.formatNumber(newQty));
            }
            if ($(this).is('.new-qty')) {
                let qtyDiff = newQty - qtyOnhand;
                if ((qtyDiff < 0 && accountType == 'Income') || (qtyDiff > 0 && accountType == 'Expense')) {
                    qtyDiff = 0;
                }
                if (qtyDiff == 0) amount = 0;
                else amount = qtyDiff * cost;
                row.find('.qty-diff').val(qtyDiff);
            }
            row.find('.amount').val(accounting.formatNumber(amount));
            Index.calcTotals();
        },  

        calcTotals() {
            let total = 0;
            $('#productsTbl tbody tr').each(function() {
                const amount = accounting.unformat($(this).find('.amount').val());
                total += amount;
            });
            $('#total').val(accounting.formatNumber(total));
        },
    };

    $(Index.init);
</script>
@endsection