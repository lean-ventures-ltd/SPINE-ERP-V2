@extends ('core.layouts.app')

@section ('title', 'Edit Purchase Class')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Edit Purchase Class</h3>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchase_classes.partials.header-buttons')
                </div>
            </div>
        </div>

    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::model($purchaseClass, ['route' => ['biller.purchase-classes.update', $purchaseClass->id], 'method' => 'PATCH']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}

                                <div class="row mb-2">

                                    <div class="col-10 col-lg-7">
                                        <label for="name" class="mt-2">Name</label>
                                        <input type="text" id="name" name="name" required class="form-control box-size mb-2" value="{{ $purchaseClass->name }}">
                                    </div>

                                </div>

                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.purchase-classes.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-1']) }}
                                    {{ Form::submit('Update', ['class' => 'btn btn-primary btn-md']) }}
                                    <div class="clearfix"></div>
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

<style>
    .radius-8 {
        border-radius: 8px;
    }
</style>