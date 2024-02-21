{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        autoCompleteCb: () => {
            return {
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('biller.products.quote_product_search') }}",
                        data: {keyword: request.term, warehouse_id: $('#source').val()},
                        method: 'POST',
                        success: result => response(result.map(v => ({
                            label: `${v.name}`,
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
                    row.find('.qty-rem').text(accounting.unformat(data.qty));
                    row.find('.qty-rem-inp').val(accounting.unformat(data.qty));
                    row.find('.cost').val(accounting.unformat(data.purchase_price));
                    row.find('.qty-transf').val('');
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
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#name-' + 1).autocomplete(config.autoCompleteCb());
            ['#source', '#dest'].forEach(v => $(v).select2({allowClear: true}));

            $('#add-item').click(Index.addItemClick);
            $('#source').change(Index.sourceChange);
            $('#productsTbl').on('keyup', '.qty-transf', Index.qtyKeyUp);
            $('#productsTbl').on('keyup', '.name', function() { Index.currRow = $(this).parents('tr') });
            $('#productsTbl').on('click', '.remove', Index.removeRowClick);

            const data = @json(@$stock_transfer);
            const data_items = @json(@$stock_transfer->items);
            if (data && data_items.length) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                $('#productsTbl tbody tr').each(function(i) {
                    const v = data_items[i];
                    const row = $(this);
                    if (i > 0) row.find('.name').autocomplete(config.autoCompleteCb());
                    row.find('.amount').val(v.amount*1);
                    row.find('.cost').val(v.cost*1);
                    row.find('.qty-rem-inp').val(v.qty_rem*1);
                    row.find('.qty-onhand-inp').val(v.qty_onhand*1);
                    row.find('.prodvar-id').val(v.productvar_id);
                });
                Index.calcTotals();
            }
        },

        sourceChange() {
            $('#productsTbl tbody tr:not(:first)').remove();
            $('#productsTbl .remove:first').click();
        },

        addItemClick() {
            let row = $('#productsTbl tbody tr:last').clone();
            let indx = accounting.unformat(row.find('.name').attr('id').split('-')[1]);
            row.find('input, textarea').val('').attr('value', '');
            row.find('textarea').text('');
            row.find('.unit, .qty-onhand, .qty-rem').text('');
            row.find('.name').attr('id', `name-${indx+1}`);
            $('#productsTbl tbody').append(`<tr>${row.html()}</tr>`);
            $(`#name-${indx+1}`).autocomplete(config.autoCompleteCb());
        },

        removeRowClick() {
            let row = $(this).parents('tr');
            if (!row.siblings().length) {
                row.find('input, textarea').each(function() { $(this).val(''); });
                row.find('textarea').text('');
                row.find('.unit, .qty-onhand, .qty-rem').text('');
            } else row.remove();
            Index.calcTotals();
        },

        qtyKeyUp() {
            const row = $(this).parents('tr');
            const cost = accounting.unformat(row.find('.cost').val());
            const qtyOnhand = accounting.unformat(row.find('.qty-onhand').text());
            let qtyTransf = accounting.unformat(row.find('.qty-transf').val());
            if (qtyTransf < 0) qtyTransf = 0;
            if (qtyTransf > qtyOnhand) {
                qtyTransf = qtyOnhand;
                row.find('.qty-transf').val(qtyTransf);
            }
            const amount = qtyTransf * cost;
            qtyRem = qtyOnhand - qtyTransf;
            row.find('.qty-rem').text(qtyRem);
            row.find('.qty-rem-inp').val(qtyRem);
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
