@extends ('core.layouts.app')

@section ('title',  'Creat | Invoice Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.standard_invoices.partials.invoices-header-buttons')
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card round">
                    <div class="card-content">
                        <div class="card-header">
                            <div id="credit_limit" class="align-center"></div>
                        </div>
                        <div class="card-body ">
                            {{ Form::open(['route' => 'biller.standard_invoices.store', 'method' => 'POST', 'id' => 'standardInvoiceForm']) }}
                                @include("focus.standard_invoices.form")
                            {{ Form::close() }}
                            @include('focus.standard_invoices.partials.add_customer_modal')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
    <script>



    </script>
@endsection
