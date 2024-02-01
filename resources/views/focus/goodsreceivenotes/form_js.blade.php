{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        select2: {allowClear: true},
        fetchLpo: (supplier_id) => {
            return $.ajax({
                url: "{{ route('biller.suppliers.purchaseorders') }}",
                type: 'POST',
                quietMillis: 50,
                data: {supplier_id, type: 'grn'},
            });
        },
        fetchLpoGoods: (purchaseorder_id) => {
            return $.ajax({
                url: "{{ route('biller.purchaseorders.goods') }}",
                type: 'POST',
                quietMillis: 50,
                data: {purchaseorder_id},
            });
        }
    };

    const Form = {
        grn: @json(@$goodsreceivenote),

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#supplier').select2(config.select2);
            $('#purchaseorder').select2(config.select2);

            // edit mode
            if (this.grn) {
                if (this.grn.date) $('#date').datepicker('setDate', new Date(this.grn.date));
                if (this.grn.invoice_date) $('#invoice_date').datepicker('setDate', new Date(this.grn.invoice_date));
                $('#supplier').attr('disabled', true);
                $('#purchaseorder').attr('disabled', true);
                if (this.grn.invoice_no) {
                    $('#invoice_status option:eq(0)').remove();
                    $('#invoice_no').attr('disabled', false);
                    $('#invoice_date').attr('disabled', false);
                } else {
                    $('#invoice_status option:eq(1)').remove();
                }
            } 

            $('#supplier').change(this.supplierChange);
            $('#purchaseorder').change(this.purchaseorderChange);
            $('#tax_rate').change(() => Form.columnTotals());
            $('#invoice_status').change(this.invoiceStatusChange);
            $('#productTbl').on('change', '.qty', this.onQtyChange);
            this.columnTotals();
        },

        invoiceStatusChange() {
            const el = $(this);
            if (el.val() == 'with_invoice') {
                $('#invoice_no').val('').attr({'disabled': false, 'required': true});
                $('#invoice_date').val('').attr({'disabled': false, 'required': true});
            } else {
                $('#invoice_no').val('').attr({'disabled': true, 'required': false});
                $('#invoice_date').val('').attr({'disabled': true, 'required': false});
            }
        },

        supplierChange() {
            $('#purchaseorder option:not(:eq(0))').remove();
            $('#productTbl tbody').html('');
            const el = $(this);
            if (el.val()) {
                config
                .fetchLpo(el.val())
                .done(data => {
                    data.forEach(v => {
                        let tid = `${v.tid}`.length < 4? `000${v.tid}`.slice(-4) : v.tid;
                        $('#purchaseorder').append(`<option value="${v.id}">PO-${tid} - ${v.note}</option>`);
                    });
                    $('#purchaseorder').change();
                });
            }
        },

        purchaseorderChange() {
            const el = $(this);
            $('#productTbl tbody').html('');
            if (!el.val()) return;
            config.fetchLpoGoods(el.val()).done(data => {
                data.forEach((v,i) => $('#productTbl tbody').append(Form.productRow(v,i)));
            });
        },

        productRow(v,i) {
            let received = accounting.formatNumber(v.qty_received);
            let due = v.qty - v.qty_received;
            let balance = accounting.formatNumber(due > 0? due : 0);
            return `
                <tr>
                    <td>${i+1}</td>    
                    <td>${v.description}</td>    
                    <td>${v.uom}</td>    
                    <td class="qty_ordered">${accounting.formatNumber(v.qty)}</td>    
                    <td class="qty_received">${received}</td>    
                    <td class="qty_due">${balance}</td>    
                    <td><input name="qty[]" id="qty" class="form-control qty"></td>    
                    <input type="hidden" name="purchaseorder_item_id[]" value="${v.id}">
                    <input type="hidden" name="rate[]" value="${parseFloat(v.rate)}" class="rate">
                    <input type="hidden" name="item_id[]" value="${v.item_id}">
                </tr>
            `;
        },

        onQtyChange() {
            let qty = accounting.unformat(this.value);

            // limit qty on goods received
            // let row = $(this).parents('tr');
            // let qtyDue = accounting.unformat(row.find('.qty_due').text());
            // let qtyOrdered = accounting.unformat(row.find('.qty_ordered').text());
            // let qtyReceived = accounting.unformat(row.find('.qty_received').text());
            // if (!Form.grn) {
            //     if (qty > qtyDue) qty = qtyDue;
            // } else {
            //     let limit = qty;
            //     let originQty = accounting.unformat($(this).attr('origin'));
            //     if (qtyDue && qtyReceived < qtyOrdered) {
            //         limit = originQty + qtyDue;
            //     } else {
            //         if (qtyReceived > qtyOrdered) {
            //             if (originQty < qtyReceived) limit = originQty - (qtyOrdered - qtyReceived);
            //             if (originQty == qtyReceived) limit = qtyOrdered;
            //         } else {
            //             limit = qtyOrdered;
            //         }
            //     }
            //     if (qty > limit) qty = limit;
            // }

            this.value = accounting.formatNumber(qty);
            Form.columnTotals();
        },

        columnTotals() {
            subtotal = 0;
            total = 0;
            const tax_rate = 1 + $('#tax_rate').val() / 100;
            $('#productTbl tbody tr').each(function() {
                const row = $(this);
                const qty = accounting.unformat(row.find('.qty').val());
                const rate = accounting.unformat(row.find('.rate').val());
                subtotal += qty * rate;
                total += qty * rate * tax_rate;
            });
            $('#subtotal').val(accounting.formatNumber(subtotal));
            $('#tax').val(accounting.formatNumber(total - subtotal));
            $('#total').val(accounting.formatNumber(total));
        },
    }

    $(() => Form.init());
</script>
