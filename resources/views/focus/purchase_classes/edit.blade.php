<!DOCTYPE html>

@extends ('core.layouts.app')

<head>
    <script src="https://cdn.tiny.cloud/1/ewcb9ttdxkr6mv3uyc8ueykuqz06aja4t3e7wuqyfqfwq17z/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
</head>


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

                                <div>

                                    <div class="row mb-2">
                                        <div class="col-8 col-lg-4">
                                            <label for="name" class="mt-2">Name</label>
                                            <input type="text" id="name" name="name" required class="form-control box-size mb-2"
                                                   @if(!empty($purchaseClass)) value="{{$purchaseClass['name']}}" @endif
                                            >
                                        </div>

                                        <div class="col-8 col-lg-4">
                                            <label for="budget" class="mt-2">Budget</label>
                                            <input type="number" step="0.01" id="budget" name="budget" required class="form-control box-size mb-2"
                                                   @if(!empty($purchaseClass)) value="{{$purchaseClass['budget']}}" @endif
                                            >
                                        </div>

                                        <div class="col-12 col-lg-8">
                                            <label for="financial_year_id" >Financial Year</label>
                                            <select class="form-control box-size mb-2" id="financial_year_id" name="financial_year_id" required>

                                                <option value=""> Select a Financial Year </option>

                                                @foreach ($financialYears as $fY)
                                                    <option value="{{ ($fY['id']) }}" @if($purchaseClass['financial_year_id'] === $fY['id']) selected @endif>
                                                        {{ $fY['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 col-lg-8">
                                            <label for="description">Description</label>
                                            <textarea name="description" id="description" class="col-8 col-lg-8 tinyinput" cols="30" rows="10">
                                                @if(!empty($purchaseClass)) {{$purchaseClass['description']}} @endif
                                            </textarea>
                                        </div>
                                    </div>


                                    <div class="edit-form-btn">
                                        {{ link_to_route('biller.purchase-classes.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-1']) }}
                                        {{ Form::submit('Update', ['class' => 'btn btn-primary btn-md']) }}
                                        <div class="clearfix"></div>
                                    </div>

                                </div>



{{--                                <div>--}}

{{--                                    <div class="row mb-2">--}}
{{--                                        <div class="col-8">--}}
{{--                                            <label for="name" class="mt-2">Name</label>--}}
{{--                                            <input type="text" id="name" name="name" required class="form-control box-size mb-2"--}}
{{--                                                   @if(!empty($purchaseClass)) value="{{$purchaseClass['name']}}" @endif--}}
{{--                                            >--}}
{{--                                        </div>--}}

{{--                                        <div class="col-8 col-lg-8">--}}
{{--                                            <textarea name="description" id="description" class="tinyinput" cols="30" rows="10">--}}
{{--                                                @if(!empty($purchaseClass)) value="{{$purchaseClass['description']}}" @endif--}}
{{--                                            </textarea>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

{{--                                    <div class="row mb-2">--}}

{{--                                        <div class="col-4">--}}
{{--                                            <label for="from_date">Start Date</label>--}}
{{--                                            <input type="text" id="start_date" name="start_date" required placeholder="Start From..." class="datepicker form-control box-size mb-2"--}}
{{--                                                   @if(!empty($purchaseClass)) value="{{$purchaseClass['start_date']}}" @endif--}}
{{--                                            >--}}
{{--                                        </div>--}}

{{--                                        <div class="col-4">--}}
{{--                                            <label for="to_date">End Date</label>--}}
{{--                                            <input type="text" id="end_date" name="end_date" required placeholder="End On..." class="datepicker form-control box-size mb-2"--}}
{{--                                                   @if(!empty($purchaseClass)) value="{{$purchaseClass['end_date']}}" @endif--}}
{{--                                            >--}}
{{--                                        </div>--}}

{{--                                    </div>--}}


{{--                                </div>--}}


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


@section('after-scripts')

    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });
        $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})


        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 300,
        });


    </script>

@endsection

<style>
    .radius-8 {
        border-radius: 8px;
    }
</style>