<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {
            autoHide: true,
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            format: 'MM-yyyy',
            onClose: function(dateText, inst) { 
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
        },
    };

    const Index = {
        initRow: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('#end_date').datepicker(config.date).datepicker('setDate', "{{ date('m-Y') }}");
        
            Index.initRow = $('#transactions tbody tr:first');

            $('#recon-form').submit(Index.onFormSubmit);
            $('#account').change(Index.onAccountChange);
            $('#end_date').change(() => $('#account').change());
            $('#end_balance').keyup(Index.onEndBalKeyUp);
            $('#end_balance').change(Index.onEndBalChange);
            $('#check-all').change(Index.onCheckAllChange);
            $('#transactions').on('change', '.check', Index.onCheckBoxChange);
            
            // editing
            const data = @json(@$reconciliation);
            if (data && data.id) {
                $('#account').attr('disabled', true);
                $('#end_date').val(data.end_date).attr('disabled', true);                
                $('#end_balance').keyup().change();
                $('#check-all').prop('checked', true).change();
            } 
        },

        onFormSubmit(e) {
            const balanceDiff = accounting.unformat($('#balance_diff').val());
            const msg = 'Balance Difference is Not Zero! Your Transactions Do Not Match Your Statement. Are you sure to proceed?';
            if (balanceDiff != 0 && !confirm(msg)) e.preventDefault();
        },

        onAccountChange() {
            $('#transactions tbody tr').remove();
            $('#begin_balance').val('0.00');
            $('.begin-bal').text('0.00');
            if (!this.value) return;

            const url = "{{ route('biller.reconciliations.account_items') }}";
            const params = {account_id: $(this).val(), end_date: $('#end_date').val()};
            $.post(url, params, data => {
                data.forEach((v,i) => {
                    if(i == 0) {
                        $('#begin_balance').val(accounting.formatNumber(v.begin_balance*1));
                        $('.begin-bal').text(accounting.formatNumber(v.begin_balance*1));
                    }
                    const row = Index.initRow.clone();
                    row.removeClass('d-none');
                    row.find('.journalitem-id').val(v.journal_item_id);
                    row.find('.journal-id').val(v.man_journal_id);
                    row.find('.pmt-id').val(v.payment_id);
                    row.find('.dep-id').val(v.deposit_id);
                    row.find('.date').text(v.date.split('-').reverse().join('-') || '');
                    row.find('.type').text(v.type);
                    row.find('.trans-ref').text(v.trans_ref);
                    row.find('.client-supplier').text(v.client_supplier);
                    row.find('.note').text(v.note);
                    row.find('.cash').text(accounting.formatNumber(+v.amount));
                    if (v.payment_id) row.find('.credit').text(accounting.formatNumber(+v.amount));
                    if (v.deposit_id) row.find('.debit').text(accounting.formatNumber(+v.amount));
                    $('#transactions tbody').append(row);
                });
                
                const data_items = @json(@$reconciliation->items);
                if (data_items && data_items.length) {
                    $('#transactions tbody tr').each(function() {
                        const el = $(this);
                        const checkinp = el.find('.check-inp');
                        if (checkinp.val() == 1) el.find('.check').prop('checked', true).change();
                    });
                } 
            });
        },

        onEndBalChange() {
            const value = accounting.unformat(this.value);
            $(this).val(accounting.formatNumber(value));
        },

        onEndBalKeyUp() {
            const endBal = accounting.unformat(this.value);
            const clearedBal = accounting.unformat($('.cleared-bal').html());
            const balanceDiff = endBal - clearedBal;

            $('.endin-bal').text(accounting.formatNumber(endBal));
            $('.bal-diff').text(accounting.formatNumber(balanceDiff));
            $('#balance_diff').val(accounting.formatNumber(balanceDiff));
        },

        onCheckBoxChange() {
            const row = $(this).parents('tr');
            const type = row.find('.type').html();
            const endBal = accounting.unformat($('.endin-bal').html());
            const beginBal = accounting.unformat($('.begin-bal').html());
            const cash = accounting.unformat(row.find('.cash').html());
            let cashIn = accounting.unformat($('.cash-in').html());
            let cashOut = accounting.unformat($('.cash-out').html());
            if (type == 'cash-in') {
                if ($(this).is(':checked')) cashIn += cash;
                else cashIn -= cash;
            }
            if (type == 'cash-out') {
                if ($(this).is(':checked')) cashOut += cash;
                else cashOut -= cash;
            }

            if ($(this).is(':checked')) row.find('.check-inp').val(1);
            else row.find('.check-inp').val('');

            $('.cash-in').text(accounting.formatNumber(cashIn));
            $('.cash-out').text(accounting.formatNumber(cashOut));
            $('#cash_in').val(accounting.formatNumber(cashIn));
            $('#cash_out').val(accounting.formatNumber(cashOut));

            const clearedBal = beginBal - cashOut + cashIn
            $('.cleared-bal').text(accounting.formatNumber(clearedBal));
            $('#cleared_balance').val(accounting.formatNumber(clearedBal));

            const balDiff = endBal - clearedBal;
            $('.bal-diff').text(accounting.formatNumber(balDiff));
            $('#balance_diff').val(accounting.formatNumber(balDiff));
        },

        onCheckAllChange() {
            let cashIn = 0;
            let cashOut = 0;
            if ($(this).is(':checked')) {
                $('#transactions tbody tr').each(function() {
                    const row = $(this);
                    row.find('.check').prop('checked', true);
                    row.find('.check-inp').val(1);
                    const type = row.find('.type').html();
                    const cash = accounting.unformat(row.find('.cash').html());
                    if (type == 'cash-in') cashIn += cash;
                    if (type == 'cash-out') cashOut += cash;
                });
            } else {
                $('#transactions tbody tr').each(function() {
                    const row = $(this);
                    row.find('.check').prop('checked', false);
                    row.find('.check-inp').val('');
                });
            }
            $('.cash-in').text(accounting.formatNumber(cashIn));
            $('.cash-out').text(accounting.formatNumber(cashOut));
            $('#cash_in').val(accounting.formatNumber(cashIn));
            $('#cash_out').val(accounting.formatNumber(cashOut));

            const endBal = accounting.unformat($('.endin-bal').html());
            const beginBal = accounting.unformat($('.begin-bal').html());

            const clearedBal = beginBal - cashOut + cashIn;
            $('.cleared-bal').text(accounting.formatNumber(clearedBal));
            $('#cleared_balance').val(accounting.formatNumber(clearedBal));

            const balDiff = endBal - clearedBal;
            $('.bal-diff').text(accounting.formatNumber(balDiff));
            $('#balance_diff').val(accounting.formatNumber(balDiff));
        }
    }

    $(Index.init);
</script>