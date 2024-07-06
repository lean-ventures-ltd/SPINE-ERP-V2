@extends ('core.layouts.app')

@section('title', 'Edit | Sale Return')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Sale Return</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.sale_returns.partials.salereturn-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($sale_return, ['route' => array('biller.sale_returns.update', $sale_return), 'method' => 'PATCH']) }}
                        @include('focus.sale_returns.form')
                        <div class="edit-form-btn row">
                            {{ link_to_route('biller.sale_returns.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                            {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
