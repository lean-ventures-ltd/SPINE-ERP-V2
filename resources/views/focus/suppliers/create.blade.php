@extends ('core.layouts.app')

@section ('title', trans('labels.backend.suppliers.management') . ' | ' . trans('labels.backend.suppliers.create'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title mb-0">{{ trans('labels.backend.suppliers.create') }}</h4>
        </div>
        <div class="content-header-right col-6">
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
                            {{ Form::open(['route' => 'biller.suppliers.store', 'method' => 'post', 'files' => true, 'id' => 'create-supplier']) }}
                            <div class="form-group">
                                @include("focus.suppliers.form")
                                <div class="edit-form-btn ml-5">
                                    {{ link_to_route('biller.suppliers.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
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
@endsection