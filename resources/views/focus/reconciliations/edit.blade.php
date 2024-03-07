@extends ('core.layouts.app')

@section('title', 'Edit | Reconciliation Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Reconciliations Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.reconciliations.partials.reconciliations-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::model($reconciliation, ['route' => array('biller.reconciliations.update', $reconciliation), 'method' => 'PATCH', 'id' => 'recon-form']) }}
            @include('focus.reconciliations.form')
            <div class="edit-form-btn row">
                {{ link_to_route('biller.reconciliations.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md col-1 mr-2']) }}                                           
            </div>
        {{ Form::close() }}
    </div>
</div>
@endsection
