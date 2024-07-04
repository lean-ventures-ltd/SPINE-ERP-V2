{{ Html::script(mix('js/dataTable.js')) }}
<script>    
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});

    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };
    // ajax setup
    $.ajaxSetup(config.ajax);
    // Intialize datepicker
    $('.datepicker').each(function() {
        let d = $(this).attr('value') ? $(this).attr('value') : new Date();
        $(this).datepicker(config.date).datepicker('setDate', new Date(d));
    });

    // on keyup percentage valuated
    $('#productsTbl').on('keyup', '.perc-val', function() {
        const row = $(this).parents('tr');
        const percValuated = accounting.unformat(row.find('.perc-val').val());
        const amount = accounting.unformat(row.find('.amount').text());
        const taxRate = accounting.unformat(row.find('.tax-rate').val());
        const tax = amount * taxRate * 0.01
        const amountValuated = (tax + amount) * percValuated * 0.01;
        row.find('.tax').val(accounting.formatNumber(tax));
        row.find('.amount-val').val(accounting.formatNumber(amountValuated));
        calcTotals();
    });
    // set percentage valuated for title row
    $('#productsTbl').on('blur', '.perc-val', function() {
        const percValuated = +this.value;
        const selectedRow = $(this).parents('tr');
        selectedRow.prevAll().each(function() {
            const row = $(this);
            if (row.find('.type-inp').val() == 2) {
                row.find('.perc-val').val(percValuated);
                row.find('.perc-val')[0].dispatchEvent(new Event('input'));
                return false;
            }
        });
    });
    $('#productsTbl').on('change', '.tax-rate', function() {
        $(this).parents('tr').find('.perc-val').keyup();
    });
    $('#tax-id').change(function() {
        const taxRate = this.value;
        $('#productsTbl .tax-rate option').each(function() {
            $(this).removeClass('d-none');
            if (taxRate) {
                const optionVal = +$(this).attr('value');
                if (+taxRate === 0 && optionVal !== 0) {
                    $(this).addClass('d-none');
                } else if (+taxRate && ![+taxRate, 0].includes(optionVal)) {
                    $(this).addClass('d-none');
                }
            }
        });
        $('#productsTbl .tax-rate').each(function() {
            $(this).val(taxRate).change();
        });
    });

    // set product rows
    const initProductRow = $('#productsTbl tbody tr:first').clone();
    const initTitleRow = $('#productsTbl tbody tr:last').clone();
    $('#productsTbl tbody').html('');
    const verifiedItems = @json($quote->verified_products);
    verifiedItems.forEach((v,i) => {
        // product type
        if (v.a_type == 1) {
            $('#productsTbl tbody').append(`<tr>${initProductRow.html()}</tr>`);
            const row =  $('#productsTbl tbody tr:last');
            const subtotal = +v.product_subtotal;
            const amount = v.product_qty * subtotal; 
            row.find('.index-inp').val(i);
            row.find('.type-inp').val(v.a_type);
            row.find('.num').text(v.numbering);
            row.find('.num-inp').val(v.numbering);
            row.find('.descr').text(v.product_name);
            row.find('.descr-inp').val(v.product_name);
            row.find('.unit').text(v.unit);
            row.find('.unit-inp').val(v.unit);
            row.find('.qty').text(+v.product_qty);
            row.find('.qty-inp').val(+v.product_qty);
            row.find('.price').text(accounting.formatNumber(subtotal,2));
            row.find('.price-inp').val(accounting.formatNumber(subtotal,2));
            row.find('.subtotal-inp').val(accounting.formatNumber(subtotal,2));
            row.find('.amount').text(accounting.formatNumber(amount,2));
            row.find('.amount-inp').val(accounting.formatNumber(subtotal,2));
            row.find('.prodvar-id').val(v.productvar_id);
            row.find('.verifieditem-id').val(v.id);
        } else {
            $('#productsTbl tbody').append(`<tr>${initTitleRow.html()}</tr>`);
            const row =  $('#productsTbl tbody tr:last');
            row.find('.index-inp').val(i);
            row.find('.type-inp').val(v.a_type);
            row.find('.num').text(v.numbering);
            row.find('.num-inp').val(v.numbering);
            row.find('.descr').text(v.product_name);
            row.find('.descr-inp').val(v.product_name);
            row.find('.verifieditem-id').val(v.id);
        }
    });

    // summary totals
    function calcTotals() {
        let taxable = 0;
        let subtotal = 0;
        let tax = 0;
        $('#productsTbl tbody tr').each(function() {
            const qty = accounting.unformat($(this).find('.qty').val());
            const price = accounting.unformat($(this).find('.price').val());
            const rowTax = accounting.unformat($(this).find('.tax').val());
            const amountValuated = accounting.unformat($(this).find('.amount-val').val());
            const percentageValuated = accounting.unformat($(this).find('.perc-val').val());
            if (percentageValuated) {
                const amount = accounting.unformat($(this).find('.amount').text());
                const taxRate = accounting.unformat($(this).find('.tax-rate').val());
                if (taxRate > 0) taxable += amount * percentageValuated * 0.01;
                subtotal += amount * percentageValuated * 0.01;
                tax += rowTax * percentageValuated * 0.01;
            }
        });
        const total = subtotal+tax;
        const quoteSubtotal = @json(+$quote->subtotal);
        const valuationBalance = quoteSubtotal-subtotal;

        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber(tax));     
        $('#total').val(accounting.formatNumber(total));
        $('#balance').val(accounting.formatNumber(valuationBalance));
        $('.valx-row td').eq(1).text($('#taxable').val());
        $('.valx-row td').eq(2).text($('#tax').val());
        $('.valx-row td').eq(3).text($('#subtotal').val());
        $('.valx-row td').eq(4).text($('#balance').val());
    }
    

    /**
     * Equipments
     **/
    // on change row type
    $('#jobcardsTbl').on('change', '.jc_type', function() {
        const row = $(this).parents('tr');
        // value 2 is dnote, else jobcard
        if ($(this).val() == 2) ['.jc_fault', '.jc_equip', '.jc_loc'].forEach((v) => row.find(v).addClass('d-none'));
        else ['.jc_fault', '.jc_equip', '.jc_loc'].forEach((v) => row.find(v).removeClass('d-none'));
    });
    
    // add job card row
    const initJobcardRow = $('#jobcardsTbl tbody tr:first').clone();
    $('.jc_equip:first').autocomplete(equipmentAutocomplete());
    $('#addJobcard').click(function() {
        $('#jobcardsTbl tbody').append(`<tr>${initJobcardRow.html()}</tr>`);
        const el = $('#jobcardsTbl tbody tr:last');
        el.find('.jc_equip').autocomplete(equipmentAutocomplete());
        el.find('.jc_date').datepicker(config.date).datepicker('setDate', new Date());
    });
    // remove job card row
    $('#jobcardsTbl').on('click', '.remove', function() {
        const row = $(this).parents('tr');
        if (confirm('Are you sure ?')) {
            if (!row.siblings().length) $('#addJobcard').click();
            row.remove();
        }
    });

    // equipment autocomplete
    let focusEquipmentRow;
    $('#jobcardsTbl').on('keyup', '.jc_equip', function() {
        focusEquipmentRow = $(this).parents('tr');
    });
    function equipmentAutocomplete() {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    method: 'POST',
                    data: {
                        keyword: request.term, 
                        customer_id: "{{ $quote->customer_id }}",
                        branch_id: "{{ $quote->branch_id }}",
                    },
                    success: data => {
                        data = data.map(v => {
                            for (const key in v) {
                                if (!v[key]) v[key] = '';
                            }
                            const label = `${v.unique_id} ${v.equip_serial} ${v.make_type} ${v.model} ${v.machine_gas}
                                ${v.capacity} ${v.location} ${v.building} ${v.floor}`;
                            return {label, value: v.unique_id, data: v};
                        });
                        response(data);
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: (event, ui) => {
                const {data} = ui.item;
                const el = focusEquipmentRow;
                el.find('.jc_equipid').val(data.id);
                el.find('.jc_equip').val(data.make_type);
                el.find('.jc_loc').val(data.location);
            }
        };
    }    
</script>
