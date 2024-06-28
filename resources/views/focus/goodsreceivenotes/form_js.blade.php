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
        },
        prediction: (url, callback) => {
            return {
                source: function(request, response) {
                    $.ajax({
                        url,
                        dataType: "json",
                        method: "POST",
                        data: {keyword: request.term, projectstock: $('#projectstock').val()},
                        success: function(data) {
                            response(data.map(v => ({
                                label: v.name,
                                value: v.name,
                                data: v
                            })));
                        }
                    });
                },
                autoFocus: true,
                minLength: 0,
                select: callback
            };
        }
    }
    

    const Form = {
        grn: @json(@$goodsreceivenote),
        projectStockRowId: 1,
        projectstockUrl: "{{ route('biller.projects.project_search') }}",

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#supplier').select2(config.select2);
            $('#purchaseorder').select2(config.select2);

            if (this.grn) {
                if (this.grn.date) $('#date').datepicker('setDate', new Date(this.grn.date));
                if (this.grn.invoice_date) $('#invoice_date').datepicker('setDate', new Date(this.grn.invoice_date));
                $('#supplier').attr('disabled', true);
                $('#purchaseorder').attr('disabled', true);
                $('.projectstock').autocomplete(config.prediction(this.projectstockUrl, Form.projectstockSelect));
                if (this.grn.invoice_no) {
                    $('#invoice_no').attr('disabled', false);
                    $('#invoice_date').attr('disabled', false);
                    $('#invoice_status option:eq(0)').remove();
                } else {
                    $('#invoice_status option:eq(1)').remove();
                }
            } 

            $('#supplier').change(this.supplierChange);
            $('#purchaseorder').change(this.purchaseorderChange);
            $('#tax_rate').change(() => Form.columnTotals());
            $('#invoice_status').change(this.invoiceStatusChange);
            $('#productTbl').on('change', '.qty', this.onQtyChange);
            $('#productTbl').on('mouseup', '.projectstock', Form.projectMouse);
            $('#productTbl').on('change', '.warehouse', this.warehouseChange);
            $('#productTbl').on('keyup', '.projectstock', this.projectChange);
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
                $('#credit_limit').html('');
                $.ajax({
                type: "POST",
                url: "{{route('biller.suppliers.check_limit')}}",
                data: {
                    supplier_id: el.val(),
                },
                success: function (result) {
                    let total = $('#total').val();
                    let number = total.replace(/,/g, '');
                    let newTotal = parseFloat(number);
                    // console.log(result, parseFloat(result.total_aging), newTotal);
                     let outstandingTotal = parseFloat(result.outstanding_balance);
                     let total_aging = parseFloat(result.total_aging);
                     let credit_limit = parseFloat(result.credit_limit);
                     let total_age_grandtotal = total_aging+newTotal;
                    let balance = total_age_grandtotal - outstandingTotal;
                    $('#total_aging').val(result.total_aging.toLocaleString());
                    $('#credit').val(result.credit_limit.toLocaleString());
                    $('#outstanding_balance').val(result.outstanding_balance);
                    // console.log(credit_limit, total_aging,total_age_grandtotal,typeof result.credit_limit , balance, newTotal);
                    if(balance > credit_limit){
                        let exceeded = balance-result.credit_limit;
                        $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by: ${parseFloat(exceeded).toFixed(2)}</h4>`);
                        
                    }else{
                        $('#credit_limit').html('')
                    }
                }
            });
            }
        },

        purchaseorderChange() {
            const el = $(this);
            const projectstockUrl = "{{ route('biller.projects.project_search') }}"
            $('#productTbl tbody').html('');
            if (!el.val()) return;
            config.fetchLpoGoods(el.val()).done(data => {
                data.forEach((v,i) => {
                    $('#productTbl tbody').append(Form.productRow(v,i));
                    $('#productTbl tbody tr').find(`#warehouseid-${i+1} option`).each(function() {
                        if ($(this).val() == v.warehouse_id) {
                        $(this).prop("selected", true);
                        return false; // break out of the loop
                        }
                    });
                    // $('.projectstock').autocomplete(config.prediction(projectstockUrl, Form.projectstockSelect));
                });

            });
        },

        // stock select autocomplete
    
        projectstockSelect(event, ui) {
            const {data} = ui.item;
            const i = Form.projectStockRowId;
            const el = $(this);
            const row = el.parents('tr:first');
            if(el.is('.projectstock')){
                row.find('.warehouse').attr('readonly', true);
                row.find('.warehouse option:selected').val(0);
                row.find('.stockitemprojectid').val(data.id).change();
            }
            
        },

        
        projectMouse() {
            const id = $(this).attr('id').split('-')[1];
            if ($(this).is('.projectstock')) Form.projectStockRowId = id;
        },
        productRow(v,i) {
            const qty = accounting.formatNumber(v.qty);
            const received = accounting.formatNumber(v.qty_received);
            const due = v.qty - v.qty_received;
            const balance = accounting.formatNumber(due > 0? due : 0);
            return `
                <tr>
                    <td width="5%">${i+1}</td>    
                    <td width="20%" class="text-left">
                        ${v.description}
                        <input type="hidden" class="product_code" name="product_code[]" value="${v.product_code}">
                    </td>
                    <td width="15%">
                        <input class="form-control projectstock" value="${v.project_tid}" id="projectstocktext-${i+1}" placeholder="Search Project By Name"></input>
                        <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="${v.itemproject_id}" id="projectstockval-${i+1}" >
                    </td>  
                    <td width="10%"><select name="warehouse_id[]" class="form-control warehouse" id="warehouseid-${i+1}">
                        <option value="">Select Warehouse</option>
                        @foreach ($warehouses as $row)
                            <option value="{{ $row->id }}">{{ $row->title }}</option>
                        @endforeach
                    </select>
                    </td>     
                    <td width="10%">${v.uom}</td>    
                    <td width="5%" class="qty_ordered">${qty}</td>    
                    <td width="5%" class="qty_received">${received}</td>    
                    <td width="5%" class="qty_due">${balance}</td>    
                    <td width="15%"><input name="qty[]" id="qty" class="form-control qty"></td>    
                    <input type="hidden" name="purchaseorder_item_id[]" value="${v.id}">
                    <input type="hidden" name="rate[]" value="${parseFloat(v.rate)}" class="rate">
                    <input type="hidden" name="item_id[]" value="${v.product_id}">
                </tr>
            `;
        },

        
        warehouseChange() {
            const el = $(this);
            const row = el.parents('tr:first');
            if(el.is('.warehouse')){
                row.find('.projectstock').val('').attr('disabled', true);
                row.find('.stockitemprojectid').val(0);
            }

            if(el.val() == ''){
                row.find('.projectstock').val('').attr('disabled', false);
            }
        },
        projectChange() {
            const projectstockUrl = "{{ route('biller.projects.project_search') }}";
            const el = $(this);
            const row = el.parents('tr:first');
            if(el.is('.projectstock')){
                row.find('.projectstock').autocomplete(config.prediction(projectstockUrl, Form.projectstockSelect));
                // row.find('.projectstock').val('').attr('disabled', true);
                // row.find('.stockitemprojectid').val(0);
            }

            if(el.val() == ''){
                row.find('.projectstock').val('').attr('disabled', false);
            }
        },
        
        onQtyChange() {
            let qty = accounting.unformat(this.value);
            
            // limit qty on goods received
            let row = $(this).parents('tr');
            let qtyDue = accounting.unformat(row.find('.qty_due').text());
            let qtyOrdered = accounting.unformat(row.find('.qty_ordered').text());
            let qtyReceived = accounting.unformat(row.find('.qty_received').text());
            if (!Form.grn) {
                if (qty > qtyDue) qty = qtyDue;
            } else {
                let limit = qty;
                let originQty = accounting.unformat($(this).attr('origin'));
                if (qtyDue && qtyReceived < qtyOrdered) {
                    limit = originQty + qtyDue;
                } else {
                    if (qtyReceived > qtyOrdered) {
                        if (originQty < qtyReceived) limit = originQty - (qtyOrdered - qtyReceived);
                        if (originQty == qtyReceived) limit = qtyOrdered;
                    } else {
                        limit = qtyOrdered;
                    }
                }
                if (qty > limit) qty = limit;
            }
            
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
            $("#credit_limit").html('');
            let credit_limit = $('#credit').val().replace(/,/g, '');
            let total_aging = $('#total_aging').val().replace(/,/g, '');
            let outstanding_balance = $('#outstanding_balance').val().replace(/,/g, '');
            let balance = total_aging.toLocaleString() - outstanding_balance.toLocaleString() + total;
            if (balance > credit_limit) {
                let exceeded = balance -credit_limit;
                $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by:  ${parseFloat(exceeded).toFixed(2)}</h4>`);
            }else{
                $("#credit_limit").html('');
            }
        },
    }

    $(() => Form.init());
</script>

