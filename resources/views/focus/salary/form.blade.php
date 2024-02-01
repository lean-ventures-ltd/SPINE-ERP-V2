
<div class='row mb-1'>
    <div class='col-md-4'>
        {{ Form::label( 'employee', 'Employee Name',['class' => 'control-label']) }}

        <label for="employee">Employee</label>
        <select name="employee_id" id="employee_id" class="form-control round" required @if(!empty($salary)) disabled @endif>
            <option value="">-- Select Employee --</option>
            @foreach ($employees as $emp)
                <option value="{{ $emp['id'] }}"
                        @if(!empty($salary))
                            @if($salary->employee_id === $emp['id']) selected @endif
                        @endif
                >{{ $emp['full_name'] }}</option>
            @endforeach
        </select>

    </div>
    <div class='col-md-4'>
        <div class="row">
            <div class="col-4">
                {{ Form::label( 'basic_salary', 'Basic Pay',['class' => 'control-label']) }}
                {{ Form::number('basic_salary', null, ['class' => 'form-control round', 'placeholder' => '0.00', 'required']) }}
            </div>
            <div class="col-8">
                {{ Form::label( 'hourly_salary', 'Hourly Pay',['class' => 'control-label']) }}
                <select class="form-control round" name="hourly_salary" id="hourly_salary">
                    <option value="0">Net Retainer</option>
                    <option value="0.5">50 Percent</option>
                    <option value="0.4">40 Percent</option>
                </select>

            </div>
        </div>
    </div>
    {{--        <div class="col-md-4">--}}
    {{--            {{ Form::label( 'contract_type', 'Contract Type',['class' => 'control-label']) }}--}}
    {{--            <select class="form-control round" name="contract_type" id="employeebox" data-placeholder="Search Contract">--}}
    {{--                <option value="permanent">Permanent</option>--}}
    {{--                <option value="contract">Contract</option>--}}
    {{--                <option value="casual">Casual</option>--}}
    {{--                <option value="intern">Intern</option>--}}
    {{--            </select>--}}
    {{--        </div>--}}
</div>
<div class='row mb-1'>
    {{-- <div class='col-md-4'>
        {{ Form::label( 'transport_allowance', 'Transport Allowance',['class' => 'control-label']) }}
        {{ Form::number('transport_allowance', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'directors_fee', 'Directors Fees',['class' => 'control-label']) }}
        {{ Form::number('directors_fee', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
    </div> --}}

</div>
<div class="row mb-1">
    <div class="col-md-4">
        {{ Form::label('workshift', 'Select Workshift',['class' => 'control-label']) }}
        <select class="form-control round" name="workshift_id"  data-placeholder="Search Workshift">
            @foreach ($workshifts as $work)
                <option value="{{$work->id}}" {{ $work->id == @$salary->workshift_id ? 'selected' : '' }}>{{$work->name}}</option>
            @endforeach
        </select>
    </div>

    {{--        <div class="col-md-4">--}}
    {{--            {{ Form::label( 'start_date', 'Start Date',['class' => 'control-label']) }}--}}
    {{--            {{ Form::date('start_date', null, ['class' => 'form-control round datepicker', 'placeholder' => '', 'required']) }}--}}
    {{--        </div>--}}
    {{--        <div class="col-md-4">--}}
    {{--            {{ Form::label( 'duration', 'Duration (in Months)',['class' => 'control-label']) }}--}}
    {{--            {{ Form::number('duration', null, ['class' => 'form-control round', 'placeholder' => '1', 'required']) }}--}}
    {{--        </div>--}}


    {{--        <div class="col-md-4">--}}
    {{--            {{ Form::label('pay_per_hr', 'Pay Rate/Hr',['class' => 'control-label']) }}--}}
    {{--            {{ Form::number('pay_per_hr', null, ['class' => 'form-control round', 'step' => '0.01', 'placeholder' => '0.00']) }}--}}
    {{--        </div>--}}

    <div class="col-md-2">
        <label for="nhif">NHIF Status</label>
        <select name="nhif" id="nhif" class="form-control round" required >
            <option value="">-- Select NHIF Status --</option>
            @php
                $nhifOptions = [
                    'Make Deduction' => 1,
                    'Exempt' => 0
                ];

            @endphp

            @foreach ($nhifOptions as $option => $value)
                <option value="{{ $value }}"
                        @if(!empty($salary))
                            @if($salary->nhif === $value) selected @endif
                        @endif
                >{{ $option }}</option>
            @endforeach
        </select>
    </div>


    <div class="col-md-2">
        <label for="deduction_exempt"> Deduct PAYE, NSSF & Housing Levy </label>
        <select name="deduction_exempt" id="deduction_exempt" class="form-control round" required >
            <option value="">-- Select Deduction Status --</option>
            @php
                $deductionOptions = [
                    'Make All Deductions' => 0,
                    'No Deductions' => 1
                ];

            @endphp

            @foreach ($deductionOptions as $option => $value)
                <option value="{{ $value }}"
                        @if(!empty($salary))
                            @if($salary->deduction_exempt === $value) selected @endif
                        @endif
                >{{ $option }}</option>
            @endforeach
        </select>
    </div>


</div>



<p>Add Allowances</p>
@include('focus.salary.partials.allowance_form')


<div class="edit-form-btn text-right mt-3 mb-2">
    {{ link_to_route('biller.salary.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md px-5']) }}
    {{ Form::submit( empty($salary) ? 'Create' : 'Update' , ['class' => 'btn btn-primary btn-md px-5']) }}
    <div class="clearfix"></div>
</div>


@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>

        $('#employee_id').select2();

    </script>

@endsection
