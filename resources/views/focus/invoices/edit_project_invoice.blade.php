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
    if (invoice.invoicedate) {
        $('#invoicedate').datepicker('setDate', new Date(invoice.invoicedate));
    }

    // On tax change
    $('#tax_id').change(function() {
        const mainTax = accounting.unformat(this.value);
        const rowTaxeRates = [];
        $('#quoteTbl tbody tr').each(function(i) {
            const taxRate = accounting.unformat($(this).find('.taxrate').val());
            rowTaxeRates.push(taxRate);
        });
        const disjoint = [mainTax, 0].filter(v => !rowTaxeRates.includes(v));
        let isError = false;
        // mixed vat
        if (rowTaxeRates.includes(0)) {
            if (mainTax > 0) {
                if (disjoint.length && !rowTaxeRates.includes(disjoint[0])) isError = true;
            } else {
                if (disjoint.length) isError = true;
            }
        } else {
            // single vat
            if (disjoint.length > 1 && disjoint[0] != 0) isError = true;
        }
        if (isError) {
            alert(`${disjoint[0]}% rate not applicable!`);
            $('#tax_id').val(0);
        }
        computeTotals();
    });
    $('#tax_id').change();


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

    // compute totals
    function computeTotals() {
        let taxable = 0;
        let subtotal = 0; 
        let total = 0;
        const mainTax = accounting.unformat($('#tax_id').val());
        $('#quoteTbl tbody tr').each(function(i) {
            $(this).find('.row-index').val(i);
            const qty = accounting.unformat($(this).find('.qty').val());
            const rowSubtotal = accounting.unformat($(this).find('.rate').val());
            const rowTaxable = accounting.unformat($(this).find('.taxable').val());
            const rowTotal = accounting.unformat($(this).find('.amount').text());
            const rowTaxRate = accounting.unformat($(this).find('.taxrate').val());
            if (+rowTaxRate > 0) taxable += qty * rowTaxable;
            subtotal += qty * rowSubtotal;  
            total += rowTotal;
        });
        const tax = taxable * mainTax/100;
        total = subtotal + tax;
        $('#tax').val(accounting.formatNumber(tax));
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#total').val(accounting.formatNumber(total));
    }
</script>
@endsection