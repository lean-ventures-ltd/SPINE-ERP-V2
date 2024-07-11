@extends ('core.layouts.app')

@section ('title',  'Edit Document Track')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">

            <div class="content-header-left col-6">
                <h3 class="mb-0">Edit Document Track</h3>
            </div>

        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-radius: 8px;">
                        <div class="card-content">
                            <div class="card-body">
                                {{--                            {{ Form::open(['route' => 'biller.lead-sources.store', 'method' => 'POST', 'id' => 'create-employee-daily-log']) }}--}}
                                <div class="form-group">

                                    @include('focus.documentManager.form')


                                </div>
                                {{--                            {{ Form::close() }}--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


