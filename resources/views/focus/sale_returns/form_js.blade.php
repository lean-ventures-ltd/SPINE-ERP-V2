{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        invoiceSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.invoices.client_invoices') }}",
                dataType: 'json',
                type: 'GET',
                data: ({term}) => ({search: term, customer_id: $("#customer").val()}),
                processResults: data => {
                    return { 
                        results: data.map(v => ({
                            text: `${v.tid}`.length < 4? 'INV-' + `000${v.tid}`.slice(-4) + ` ${v.notes}` : `INV-${v.tid} ${v.notes}`, 
                            id: v.id
                        }))
                    }
                },
            }
        },
    };

    const Index = {
        initRow: '',

        init() {
            $('#productsTbl tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customer').select2({allowClear: true});
            $('#invoice').select2(config.invoiceSelect);
            Index.initRow = $('#productsTbl tbody tr:first');

            $('#customer').change(() => $('#invoice').val('').change());
            $('#invoice').change(Index.invoiceChange);
            
            $('#productsTbl').on('keyup', '.return-qty', Index.returnQtyChange);
                
            const data = @json(@$sale_return);
            if (data && data.id) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                $('#customer').attr('disabled', true);
                $('#invoice').attr('disabled', true);
                Index.calcTotals();
            }
        },

        invoiceChange() {
            $('#productsTbl tbody').html('');
            const url = "{{ route('biller.sale_returns.invoice_stock_items') }}";
            const params = {invoice_id: $(this).val()};
            $.post(url, params, data => {
                data.forEach((v,i) => {
                    const row = Index.initRow.clone();
                    row.find('.serial').text(i+1);
                    row.find('.name').text(v.name);
                    row.find('.reference').text(v.reference);
                    row.find('.product-code').text(v.code);
                    row.find('.unit').text(v.uom);
                    row.find('.qty-onhand').text(+v.qty);
                    row.find('.qty-onhand-inp').val(+v.qty);
                    row.find('.cost').val(+v.purchase_price);
                    row.find('.prodvar-id').val(v.id);
                    row.find('.verified-item-id').val(v.verified_item_id);
                    $('#productsTbl tbody').append(row);
                });                
            });
        },

        returnQtyChange() {
            this.value = accounting.unformat(this.value);
            if (this.value < 0) this.value *= -1;
            const row = $(this).parents('tr');
            const cost = accounting.unformat(row.find('.cost').val());
            const qtyOnHand = accounting.unformat(row.find('.qty-onhand').text());
            const returnQty = accounting.unformat(this.value);
            const newQty = qtyOnHand + returnQty;
            row.find('.new-qty').text(newQty);
            row.find('.new-qty-inp').val(newQty);
            row.find('.amount').val(cost * returnQty);

            // edit mode
            const data = @json(@$sale_return);
            if (data && data.id) {
                const originValue = accounting.unformat(row.find('.return-qty').attr('origin-value'));
                if (returnQty == originValue) {
                    row.find('.new-qty').text(qtyOnHand);
                    row.find('.new-qty-inp').val(qtyOnHand);
                } 
                if (returnQty < originValue) {
                    row.find('.new-qty').text(qtyOnHand-returnQty);
                    row.find('.new-qty-inp').val(qtyOnHand-returnQty);
                } 
                if (returnQty > originValue) {
                    row.find('.new-qty').text(qtyOnHand - originValue + returnQty);
                    row.find('.new-qty-inp').val(qtyOnHand - originValue + returnQty);
                } 
            }

            Index.calcTotals();
        },

        calcTotals() {
            let total = 0;
            $('#productsTbl tbody tr').each(function() {
                const row = $(this);
                const qty = accounting.unformat(row.find('.return-qty').val());
                if (qty > 0) {
                    const cost = accounting.unformat(row.find('.cost').val());
                    total += qty * cost;
                }
            });
            $('#total').val(accounting.formatNumber(total));
        },
    };

    $(Index.init);
</script>
