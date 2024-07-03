@extends ('core.layouts.app')

@section('title', 'Edit Project Invoice')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="col-12">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.invoices-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header">
                    <div id="credit_limit" class="align-center"></div>
                </div>
                <div class="card-body">
                    {{ Form::model($invoice, ['route' => ['biller.invoices.update_project_invoice', $invoice], 'method' => 'POST']) }}
                        @php $customer = $invoice->customer; @endphp
                        @include('focus.invoices.project_invoice_form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script type="text/javascript">
    // default values
    const invoice = @json($invoice);

    // Initialize datepicker
    $('.datepicker').datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});
    if (invoice.invoicedate) 
        $('#invoicedate').datepicker('setDate', new Date(invoice.invoicedate));

    // On selecting Tax
    $('#tax_id').change(function() {
        let tax = 0;
        let subtotal = 0; 
        let total = 0;
        $('#quoteTbl tbody tr').each(function(i) {
            let lineSubtotal = accounting.unformat($(this).find('.subtotal').val());
            let lineQty = accounting.unformat($(this).find('.qty').val());
            let lineTotal = lineSubtotal * lineQty;
            const taxRate = $('#tax_id').val() / 100;
           
            tax += lineTotal * taxRate;
            subtotal += lineTotal;
            total += lineTotal * (1+taxRate);
             
            $(this).find('.rate').val(accounting.formatNumber(lineSubtotal, 4));
            $(this).find('.amount').text(accounting.formatNumber(lineTotal, 4));
        });
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber(tax));
        $('#total').val(accounting.formatNumber(total));
        checkLimits();
    });
    $('#tax_id').change();

    function checkLimits() {
        $('#credit_limit').html('')
            $.ajax({
                type: "POST",
                url: "{{route('biller.customers.check_limit')}}",
                data: {
                    customer_id: $('#customer_id').val(),
                },
                success: function (result) {
                    let total = $('#total').val();
                    let number = 0;
                    if(!isNaN(total)){
                        total = 0;
                        number = total;
                    }else{
                        number = total.replace(/,/g, '');
                    }
                    
                    let newTotal = parseFloat(number);
                     let outstandingTotal = parseFloat(result.outstanding_balance);
                     let total_aging = parseFloat(result.total_aging);
                     let credit_limit = parseFloat(result.credit_limit);
                     let total_age_grandtotal = total_aging+newTotal;
                    let balance = total_age_grandtotal - outstandingTotal;
                    $('#total_aging').val(result.total_aging.toLocaleString());
                    $('#credit').val(result.credit_limit.toLocaleString());
                    $('#outstanding_balance').val(result.outstanding_balance);
                    if(balance > credit_limit){
                        let exceeded = balance-result.credit_limit;
                        $("#credit_limit").append(`<h4 class="text-danger">Credit Limit Violated by: ${exceeded.toLocaleString()}</h4>`);
                        
                    }else{
                        $('#credit_limit').html('')
                    }
                }
            });
    }


    /**
     * Standard Invoice
     * */
    const isStdInvoice = @json(@$invoice->is_standard);
    if (isStdInvoice) {
        $('#quoteTbl thead tr:first th:eq(1)').remove();
        $('#quoteTbl tbody tr').each(function() {
            $(this).find('td:eq(1)').remove();
        });
    }
</script>
@endsection