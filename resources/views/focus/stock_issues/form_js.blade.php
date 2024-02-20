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
                        data: {keyword: request.term},
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
                    if (data.units && data.units.length) {
                        const unit = data.units[0];
                        row.find('.unit').text(unit.code);
                    }
                    if (data.warehouses && data.warehouses.length) {
                        row.find('.source option:not(:first)').remove();
                        data.warehouses.forEach(v => {
                            const productsQty = accounting.unformat(v.products_qty);
                            row.find('.source').append(`<option value="${v.id}" products_qty="${productsQty}">${v.title} (${productsQty})</option>`)
                        });
                    }
                    if ($('#issue_to').val() == 'Employee') row.find('.assignee').attr('disabled', true);
                    else row.find('.assignee').attr('disabled', false);
                    // on edit
                    row.find('.issue-qty').attr('readonly', false);
                    row.find('.source-inp').remove();
                    row.find('.source').attr('disabled', false);
                }
            };
        }
    };

    const Index = {
        currRow: '',
        sourceTd: '',
        assigneeTd: '',

        init() {
            $('#productsTbl tbody td').css({paddingLeft: '5px', paddingRight: '5px', paddingBottom: 0});
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            ['#employee', '#customer', '#project', '#quote'].forEach(v => $(v).select2({allowClear: true}));

            Index.sourceTd = $('.td-source:first').clone();
            Index.assigneeTd = $('.td-assignee:first').clone();
            $('#name-1').autocomplete(config.autoCompleteCb());
            ['#source-1', '#assignee-1'].forEach(v => $(v).select2({allowClear: true}));

            $('#add-item').click(Index.addItemClick);
            $('#issue_to').change(Index.issueToChange);
            $('#quote').change(Index.quoteChange);
            $('#productsTbl').on('keyup', '.issue-qty', Index.qtyCostKeyUp);
            $('#productsTbl').on('change', '.source', Index.qtyCostKeyUp);
            $('#productsTbl').on('keyup', '.name', function() { Index.currRow = $(this).parents('tr') });
            $('#productsTbl').on('click', '.remove', Index.removeRowClick);
                
            const data = @json(@$stock_issue);
            if (data && data.id) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                $('#issue_to').val(data.issue_to);
                $('#productsTbl tbody tr').each(function(i) {
                    if (i == 0) return;
                    $(this).find('.name').autocomplete(config.autoCompleteCb());
                    $(this).find('.source').select2({allowClear: true});
                    $(this).find('.assignee').select2({allowClear: true});
                });
                Index.calcTotals();
            }
        },

        quoteChange() {
            $('#productsTbl tbody tr:not(:first)').remove();
            $('#productsTbl .remove').click();
            $('#total').val('');
            if (this.value) {
                let quote_id = this.value;
                $.post("{{ route('biller.stock_issues.quote_pi_products') }}", {quote_id}, data => {
                    data.forEach((v,i) => {
                        if (i > 0) $('#add-item').click();
                        let row = $('#productsTbl tbody tr:last');
                        row.find('.prodvar-id').val(v.id); 
                        row.find('.name').val(v.name); 
                        row.find('.qty-onhand').text(accounting.unformat(v.qty));
                        row.find('.qty-onhand-inp').val(accounting.unformat(v.qty));
                        row.find('.qty-rem').text(accounting.unformat(v.qty));
                        row.find('.qty-rem-inp').val(accounting.unformat(v.qty));
                        row.find('.cost').val(accounting.unformat(v.purchase_price));
                        if (v.unit && v.unit.id) row.find('.unit').text(v.unit.code);
                        if (v.warehouses && v.warehouses.length) {
                            row.find('.source option:not(:first)').remove();
                            v.warehouses.forEach(v => {
                                const productsQty = accounting.unformat(v.products_qty);
                                row.find('.source').append(`<option value="${v.id}" products_qty="${productsQty}">${v.title} (${productsQty})</option>`)
                            });
                        }
                        if ($('#issue_to').val() == 'Employee') row.find('.assignee').attr('disabled', true);
                        else row.find('.assignee').attr('disabled', false);
                    });
                });
            }
        },

        issueToChange() {
            $('.select-col').addClass('d-none');
            $('#productsTbl .assignee').attr('disabled', false);
            if (this.value == 'Employee') {
                $('#employee').parents(".select-col").removeClass('d-none');
                $('#productsTbl .assignee').attr('disabled', true);
            } else if (this.value == 'Customer') {
                $('#customer').parents(".select-col").removeClass('d-none');
            } else {
                $('#project').parents(".select-col").removeClass('d-none');
            }
        },

        addItemClick() {
            let row = $('#productsTbl tbody tr:last').clone();
            let indx = accounting.unformat(row.find('.name').attr('id').split('-')[1]);
            row.find('input').attr('value', '');
            row.find('textarea').text('');
            row.find('.unit, .qty-onhand, .qty-rem').text('');
            row.find('.name').attr('id', `name-${indx+1}`);

            let sourceTd = Index.sourceTd.clone();
            let assigneeTd = Index.assigneeTd.clone();
            row.find('.td-source').children().remove();
            row.find('.td-assignee').children().remove();
            row.find('.td-source').append(sourceTd.children());
            row.find('.td-assignee').append(assigneeTd.children());
            row.find('.source').attr('id', `source-${indx+1}`);
            row.find('.assignee').attr('id', `assignee-${indx+1}`);

            $('#productsTbl tbody').append(`<tr>${row.html()}</tr>`);
            row = $('#productsTbl tbody tr:last');
            row.find('.name').autocomplete(config.autoCompleteCb());
            row.find('.source').select2({allowClear: true});
            row.find('.assignee').select2({allowClear: true});
            if ($('#issue_to').val() == 'Employee') row.find('.assignee').attr('disabled', true);
            else row.find('.assignee').attr('disabled', false);
        },

        removeRowClick() {
            let row = $(this).parents('tr');
            if (!row.siblings().length) {
                row.find('input, textarea, select').each(function() { $(this).val('').change() });
                row.find('.unit, .qty-onhand, .qty-rem').text('');  
                row.find('.source option:not(:first)').remove();
            } else row.remove();
            Index.calcTotals();
        },

        qtyCostKeyUp() {
            const row = $(this).parents('tr');
            const cost = accounting.unformat(row.find('.cost').val());
            const sourceQty = accounting.unformat(row.find('.source option:selected').attr('products_qty'));
            let qtyOnhand = accounting.unformat(row.find('.qty-onhand').text());
            let issueQty = accounting.unformat(row.find('.issue-qty').val());
            if (issueQty < 0) issueQty = 0;
            let qtyRem = 0;

            if (row.find('.source').val()) {
                qtyOnhand = sourceQty;
                qtyRem = sourceQty;
            }
            if (issueQty > qtyOnhand) issueQty = qtyOnhand;            
            const amount = issueQty * cost;
            qtyRem = qtyOnhand - issueQty;

            row.find('.issue-qty').val(issueQty);
            row.find('.qty-onhand').text(qtyOnhand);
            row.find('.qty-onhand-inp').val(qtyOnhand);
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
