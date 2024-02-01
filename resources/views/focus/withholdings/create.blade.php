@extends ('core.layouts.app')

@section('title', 'Withholding Certificate | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Withholding Certificates Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.withholdings.partials.withholdings-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.withholdings.store', 'method' => 'POST', 'id' => 'withholding']) }}
                        @include('focus.withholdings.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    $('.datepicker').datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());

    $('form').submit(function() {
        // filter unallocated inputs
        $('#invoiceTbl tbody tr').each(function() {
            let paidInp = $(this).find('.paid');
            if (paidInp.length) {
                if (accounting.unformat(paidInp.val()) == 0)
                    $(this).remove();   
            } 
        });

        // if (Form.billPayment && $('#payment_type').val() == 'per_invoice' && !$('#billsTbl tbody tr').length) {
        //     if (!confirm('Allocating zero on line items will reset this payment! Are you sure?')) {
        //         event.preventDefault();
        //         location.reload();
        //     }
        // }

        // check if payment amount = allocated amount
        const pmtAmount = accounting.unformat($('#amount').val());
        const allocAmount = accounting.unformat($('#allocate_ttl').val());
        if (pmtAmount != allocAmount && $('#invoiceTbl tbody tr').length > 1) {
            event.preventDefault();
            alert('Total Allocated Amount must be equal to Payment Amount!');
        }
    });

    // customer select2 config
    $('#person').select2({
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({search: term}),
            processResults: result => {
                return { results: result.map(v => ({text: `${v.company} - ${v.taxid}`, id: v.id }))};
            }      
        },
        allowClear: true
    }).change(function() {
        $.ajax({
            url: "{{ route('biller.invoices.client_invoices') }}?customer_id=" + $(this).val(),
            success: data => loadInvoice(data) 
        });
    });

    const withholdings = @json($withholdings);
    // on change certificate
    $('#certificate').change(function() {
        if ($(this).val() == 'tax') {
            $('#withholding_cert').attr('disabled', false);
            loadInvoice();
        } else {
            $('#withholding_cert').attr('disabled', true);
            $('#withholding_cert').val('0').change();
            $('#person').change();
        }
        
        // load tax certificate withholdings
        $('#withholding_cert option:not(:eq(0))').remove();
        withholdings.forEach(v => {
            if ($('#person').val() == v.customer_id) {
                const option = $(document.createElement('option'));
                option.val(v.id)
                .text(`${v.reference} - ${parseFloat(v.amount).toFixed(2)} - ${v.note}`)
                .attr('certDate', v.cert_date)
                .attr('amount', v.amount)
                .attr('allocateTotal', v.allocate_ttl)
                .attr('reference', v.reference)
                .attr('trDate', v.tr_date)
                .attr('note', v.note);

                $('#withholding_cert').append(option);
            }
        });
    });    

    // On allocating amount on invoices
    $('#invoiceTbl').on('change', '.paid', function() {
        const due = parseFloat($(this).parents('tr').find('.due').text().replace(/,/g, ''));
        const paid = parseFloat($(this).val().replace(/,/g, ''));
        if (paid > due) $(this).val(due.toLocaleString());
        else $(this).val(paid.toLocaleString());
        calcTotal();
        // check if amount is less than allocated
        const amount = parseFloat($('#amount').val().replace(/,/g, ''));
        const allocatedTotal = parseFloat($('#allocate_ttl').val().replace(/,/g, ''));
        if (amount < allocatedTotal) {
            alert('Cannot allocate more than withheld amount!');
            $(this).val(0).change();
        } 
    });

    // invoice row
    function invoiceRow(v, i) {
        const amount = parseFloat(v.total).toLocaleString();
        const amountpaid = parseFloat(v.amountpaid).toLocaleString();
        const outstanding = parseFloat(v.total - v.amountpaid).toLocaleString();
        return `
            <tr>
                <td class="text-center">${new Date(v.invoicedate).toDateString()}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.notes}</td>
                <td>${v.status}</td>
                <td>${amount}</td>
                <td>${amountpaid}</td>
                <td class="text-center due"><b>${outstanding}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]"></td>
                <input type="hidden" name="invoice_id[]" value="${v.id}">
            </tr>
        `;
    }
    // load client invoices
    function loadInvoice(data = []) {
        $('#amount').val('');
        $('#balance').val('');
        $('#allocate_ttl').val('');
        $('#invoiceTbl tbody tr:not(:eq(-1))').remove();
        if (!data.length) return;
        data.forEach((v, i) => {
            $('#invoiceTbl tbody tr:eq(-1)').before(invoiceRow(v, i));
        });
        calcTotal();
    }

    // On amount change
    $('#amount').keyup(function() {
        let dueTotal = 0;
        let allocatedTotal = 0;
        let amount = parseFloat($(this).val().replace(/,/g, ''));
        const rows = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function() {
            if ($(this).index() == rows-1) return;
            const due = parseFloat($(this).find('.due').text().replace(/,/g, ''));
            if (amount > due) $(this).find('.paid').val(due.toLocaleString());
            else if (amount > 0) $(this).find('.paid').val(amount.toLocaleString());
            else $(this).find('.paid').val(0);
            const paid = parseFloat($(this).find('.paid').val().replace(/,/g, ''));
            amount -= due;
            dueTotal += due;
            allocatedTotal += paid;
        });
        $('#balance').val(parseFloat(dueTotal - allocatedTotal).toLocaleString());
        $('#allocate_ttl').val(parseFloat(allocatedTotal.toFixed(2)).toLocaleString());
    }).focusout(function() {
        if (!$(this).val()) return;
        const val = $(this).val().replace(/,/g, '') * 1;
        $(this).val(parseFloat(val.toFixed(2)).toLocaleString());
    }).focus(function() {
        if (!$('#person').val()) $(this).blur();
    });

    // Allocate Withholding Tax 
    $('#withholding_cert').select2({
        data: [{id: 0, text: 'None'}], 
    }).change(function() {
        if ($(this).val() == 0) {
            ['amount', 'reference', 'note', 'cert_date', 'tr_date'].forEach(v => $('#'+v).attr('readonly', false).val(''));
            ['cert_date', 'tr_date'].forEach(v => $('#'+v).datepicker('setDate', new Date()));
            loadInvoice();
        } else {
            $('#person').change();
            ['cert_date', 'reference', 'tr_date', 'note'].forEach(v => $('#'+v).attr('readonly', true));
            const opt = $(this).find(':selected');
            $('#cert_date').datepicker('setDate', new Date(opt.attr('certDate')));
            $('#reference').val(opt.attr('reference'));
            $('#tr_date').datepicker('setDate', new Date(opt.attr('trDate')));
            $('#note').val(opt.attr('note'));
            // execute after ajax async call
            const balance = parseFloat(opt.attr('amount') - opt.attr('allocateTotal'));
            setTimeout(() => $('#amount').val(balance).keyup().focusout(), 500);
        }
    });   

    function calcTotal() {
        let dueTotal = 0;
        let allocatedTotal = 0;
        const rows = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function() {
            if ($(this).index() == rows-1) return;
            const due = parseFloat($(this).find('.due').text().replace(/,/g, '')) || 0;
            const paid = parseFloat($(this).find('.paid').val().replace(/,/g, '')) || 0;
            dueTotal += due;
            allocatedTotal += paid;
        });
        $('#balance').val(parseFloat(dueTotal - allocatedTotal).toLocaleString());
        $('#allocate_ttl').val(parseFloat(allocatedTotal.toFixed(2)).toLocaleString());
    }
</script>
@endsection