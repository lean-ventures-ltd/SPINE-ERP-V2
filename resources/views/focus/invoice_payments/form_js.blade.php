{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    const config = {
        ajax: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} },
        date: {format: "{{config('core.user_date_format')}}", autoHide: true}, 
        select2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.customers.select') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term}),
                processResults: result => {
                    return { results: result.map(v => ({text: `${v.name} - ${v.company}`, id: v.id }))};
                }      
            },
        },
    };

    const Form = {
        invoicePayment: @json(@$invoice_payment),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#person').select2(config.select2);

            $('#person').change(Form.customerChange);
            $('#payment_type').change(Form.paymentTypeChange);
            $('#rel_payment').change(Form.relatedPaymentChange);
            $('#currency').change(Form.currencyChange);
            
            $('#invoiceTbl').on('change', '.paid', Form.allocationChange);
            $('#amount').keyup(Form.allocateAmount)
                .focusout(Form.amountFocusOut)
                .focus(Form.amountFocus);

            $('form').submit(Form.formSubmit);
            
            // edit mode
            const pmt = @json(@$invoice_payment);
            if (pmt && pmt.id) {
                ['person', 'payment_type', 'rel_payment', 'currency'].forEach(v => $(`#${v}`).attr('disabled', true));
                if (pmt.date) $('#date').datepicker('setDate', new Date(pmt.date));
                if (pmt.note) $('#note').val(pmt.note);
                $('#amount').val(accounting.formatNumber(pmt.amount*1));
                $('#account').val(pmt.account_id);
                $('#payment_mode').val(pmt.payment_mode);
                $('#reference').val(pmt.reference);
                $('#fx_curr_rate').val(+pmt.fx_curr_rate);
                Form.calcTotal();
                // allocation
                if (pmt.rel_payment_id) {
                    ['account', 'payment_mode', 'reference'].forEach(v => $(`#${v}`).attr('disabled', true));
                }
            } else {
                $('#currency').change();
                Form.loadUnallocatedPayments();
            }
        },

        formSubmit() {
            // filter unallocated inputs
            $('#invoiceTbl tbody tr').each(function() {
                let allocatedAmount = $(this).find('.paid').val();
                if (accounting.unformat(allocatedAmount) == 0) {
                    $(this).remove();
                } 
            });
            if (Form.invoicePayment && $('#payment_type').val() == 'per_invoice' && !$('#invoiceTbl tbody tr').length) {
                if (!confirm('Allocating zero on line items will reset this payment! Are you sure?')) {
                    event.preventDefault();
                    location.reload();
                }
            }
            // check if payment amount = allocated amount
            let amount = accounting.unformat($('#amount').val());
            let allocatedTotal = accounting.unformat($('#allocate_ttl').val());
            if (amount != allocatedTotal && $('#payment_type').val() == 'per_invoice') {
                event.preventDefault();
                alert('Total Allocated Amount must be equal to Payment Amount!');
            }
            // clear disabled attributes
            $(this).find(':disabled').attr('disabled', false);
        },

        invoiceRow(v, i) {
            return `
                <tr>
                    <td class="text-center">${v.invoiceduedate.split('-').reverse().join('-')}</td>
                    <td>${v.tid}</td>
                    <td class="text-left">${v.notes}</td>
                    <td>${v.status}</td>
                    <td>${accounting.formatNumber(v.total)}</td>
                    <td>${accounting.formatNumber(v.amountpaid)}</td>
                    <td class="text-center due"><b>${accounting.formatNumber(v.total - v.amountpaid)}</b></td>
                    <td><input type="text" class="form-control paid" name="paid[]"></td>
                    <input type="hidden" name="invoice_id[]" value="${v.id}">
                </tr>
            `;
        },

        currencyChange() {
            const rate = $(this).find('option:selected').attr('rate');
            $('#fx_curr_rate').val(accounting.unformat(rate));
            $('#person').change();
        },

        customerChange() {
            $('#amount').val('');
            $('#allocate_ttl').val('');
            $('#balance').val('');
            $('#invoiceTbl tbody tr').remove();
            $('#rel_payment option:not(:eq(0))').remove();
            Form.loadUnallocatedPayments();
            
            customer_id = this.value;
            currency_id = $('#currency').val();
            if (customer_id && $('#payment_type').val() == 'per_invoice') {
                // fetch invoices
                const url = "{{ route('biller.invoices.client_invoices') }}?customer_id="+customer_id+'&currency_id='+currency_id;
                $.get(url, data => {
                    data.forEach((v, i) => {
                        $('#invoiceTbl tbody').append(Form.invoiceRow(v, i));
                    });
                });
                
                $('#rel_payment').val('');
                $('#rel_payment option').each(function() {
                    $(this).addClass('d-none');
                    if ($(this).attr('customer_id') == customer_id) $(this).removeClass('d-none');
                });
            }
        },

        loadUnallocatedPayments() {
            if ($('#person').val() && $('#payment_type').val() == 'per_invoice') {
                $('#rel_payment').attr('disabled', false).val('').change();
            } else {
                $('#rel_payment').attr('disabled', true).val('').change();
            }
            const payments = @json($unallocated_pmts);
            payments.forEach(v => {
                let balance = accounting.unformat(v.amount - v.allocate_ttl);
                let paymentType = v.payment_type.split('_').join(' ');
                let date = v.date.split('-').reverse().join('-');
                
                paymentType = paymentType.charAt(0).toUpperCase() + paymentType.slice(1);
                balance = accounting.formatNumber(balance);
                let text = `(${balance} - ${paymentType}: ${date}) - ${v.payment_mode.toUpperCase()} ${v.reference}`;
                
                $('#rel_payment').append(`
                    <option
                        value=${v.id}
                        amount=${v.amount}
                        allocateTotal=${v.allocate_ttl}
                        accountId=${v.account_id}
                        paymentMode=${v.payment_mode}
                        reference=${v.reference}
                        date=${v.date}
                    >
                        ${text}
                    </option>
                `);
            });
        },

        paymentTypeChange() {
            $('#amount').val('');
            $('#allocate_ttl').val('');
            $('#balance').val('');
            if ($(this).val() == 'per_invoice') {
                $('#rel_payment').val('').attr('disabled', false).change();
                $('#person').change();
                const payments = @json($unallocated_pmts);
                if (payments.length) Form.loadUnallocatedPayments();
            } else {
                $('#invoiceTbl tbody tr').remove();
                $('#rel_payment').val('').attr('disabled', true);
                $('#rel_payment option:not(:first)').remove();
                $('#amount').val('').attr('readonly', false);
                $('#account').val('').attr('disabled', false);
                $('#payment_mode').val('').attr('disabled', false);
                $('#reference').val('').attr('readonly', false);
                $('#allocate_ttl').val('');
                $('#balance').val('');
            }
        },

        allocationChange() {
            const paid = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(paid));
            Form.calcTotal();
        },

        allocateAmount() {
            let dueTotal = 0;
            let allocateTotal = 0;
            let decrAmount = accounting.unformat($(this).val());
            const pmt = @json(@$invoice_payment);
            if (pmt && pmt.id) {
                $('#invoiceTbl tbody tr').each(function(i) {
                    const paid = accounting.unformat($(this).find('.paid').val());
                    decrAmount -= paid;
                    allocateTotal += paid;
                });
                const amount = accounting.unformat($(this).val());
                $('#unallocate_ttl').val(accounting.formatNumber(amount - allocateTotal));
                $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            } else {
                $('#invoiceTbl tbody tr').each(function(i) {
                    const due = accounting.unformat($(this).find('.due').html());
                    if (due > decrAmount) $(this).find('.paid').val(accounting.formatNumber(decrAmount));
                    else if (decrAmount >= due) $(this).find('.paid').val(accounting.formatNumber(due));
                    else $(this).find('.paid').val(0);
                    const paid = accounting.unformat($(this).find('.paid').val());
                    decrAmount -= paid;
                    dueTotal += due;
                    allocateTotal += paid;
                });
                $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
                $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
                const amount = accounting.unformat($(this).val());
                $('#unallocate_ttl').val(accounting.formatNumber(amount - allocateTotal));
            }
        },

        amountFocus() {
            if (!$('#person').val()) $(this).blur();
        },

        amountFocusOut() {
            const amount = accounting.unformat($(this).val());
            if (amount) $(this).val(accounting.formatNumber(amount));
        },

        relatedPaymentChange() {
            if (+this.value) {
                const opt = $(this).find(':selected');
                $('#date').datepicker('setDate', new Date(opt.attr('date'))).attr('readonly', true);
                $('#reference').val(opt.attr('reference')).attr('readonly', true);
                $('#account').val(opt.attr('accountId')).attr('disabled', true);
                $('#payment_mode').val(opt.attr('paymentMode')).attr('disabled', true);

                let balance = parseFloat(opt.attr('amount')) - parseFloat(opt.attr('allocateTotal'));
                const unallocated = accounting.unformat(balance);
                $('#amount').prop('readonly', true).val(unallocated).keyup().focusout();
            } else {
                ['amount', 'reference'].forEach(v => $('#'+v).val('').attr('readonly', false).keyup());
                ['account', 'payment_mode'].forEach(v => $('#'+v).val('').attr('disabled', false));
                // $('#date').datepicker('setDate', new Date()).attr('readonly', false);
            }
        },

        calcTotal() {
            let dueTotal = 0;
            let allocateTotal = 0;
            $('#invoiceTbl tbody tr').each(function(i) {
                const due = accounting.unformat($(this).find('.due').text());
                const paid = accounting.unformat($(this).find('.paid').val());
                dueTotal += due;
                allocateTotal += paid;
            });
            const amount = accounting.unformat($('#amount').val());
            $('#unallocate_ttl').val(accounting.formatNumber(amount - allocateTotal));
            $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
        },
    };    

    $(Form.init);
</script>
