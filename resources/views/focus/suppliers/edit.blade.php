@extends ('core.layouts.app')
@section ('title', trans('labels.backend.suppliers.management') . ' | ' . trans('labels.backend.suppliers.edit'))

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">{{ trans('labels.backend.suppliers.edit') }}</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.suppliers.partials.suppliers-header-buttons')
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                {{ Form::model($supplier, ['route' => ['biller.suppliers.update', $supplier], 'files' => true, 'method' => 'PATCH', 'id' => 'edit-supplier']) }}
                                    <div class="form-group">
                                        @include("focus.suppliers.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.suppliers.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                        </div>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection