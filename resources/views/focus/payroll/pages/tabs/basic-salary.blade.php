<form id="basicSalary" action="{{ route('biller.payroll.store_basic') }}" method="post">
    @csrf
    <input type="hidden" name="payroll_id" value="{{ $payroll->id }}" id="">
    <div class="card-body">
        <table class="table">
            <thead>
                <th>Payroll Reference</th>
                <th>Payroll Date</th>
                <th>Month Days</th>
                <th>Working Days</th>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" class="form-control" value="{{ $payroll->reference }}"
                            readonly></td>
                    <td><input type="text" name="processing_date" class="form-control datepicker"
                            value=""></td>
                    <td><input type="text" class="form-control month_days"
                            value="{{ $payroll->total_month_days }}" readonly></td>
                    <td><input type="number" step="0.01" class="form-control working_days"
                            value="{{ $payroll->working_days }}" readonly></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-body">
        <table id="employeeTbl" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Employee Id</th>
                    <th>Employee Name</th>
                    <th>Fixed Salary</th>
                    <th>Max Hourly Salary</th>
                    <th>Hourly Wage</th>
                    <th>Man Hours</th>
                    <th>Additional Hours</th>
                    <th>Basic Hourly Salary</th>
                    <th>Additional Hourly Salary</th>
                    <th>Absent Days</th>
                    <th>Daily Deduction</th>
                    <th>Absenteeism Deduction</th>
                    <th>Final Basic Pay</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 1;
                @endphp
                @foreach ($payDetails as $pd)
{{--                    @if ($pd->employees_salary)--}}
                    <tr>
                        <td>{{ gen4tid('EMP-', $pd['employee_id']) }}</td>
                        <td>{{ $pd['name'] }}</td>
                        <input type="hidden" id="employee_id-{{$i}}" name="employee_id[]" value="{{ $pd['employee_id'] }}">

                        @php

                            $fixedSalary = $pd['basic_salary'] - bcmul($pd['basic_salary'], $pd['hourly_salary'], 2)

                        @endphp

                        <td>{{ amountFormat($fixedSalary) }}</td>
                        <input type="hidden" class="fixed_salary" name="fixed_salary[]" id="fixed_salary-{{$i}}" value="{{ $fixedSalary }}">


                        @php

                            $maxHourlySalary = bcmul(doubleval($pd['hourly_salary']), $pd['basic_salary'], 2);

                        @endphp
                        <td>{{ amountFormat($maxHourlySalary) }}</td>
                        <input type="hidden" class="max_hourly_salary" name="max_hourly_salary[]" id="max_hourly_salary-{{$i}}" value="{{ $maxHourlySalary  }}">

                        @php

//                            $hourlyRate = bcdiv(bcdiv($maxHourlySalary, $payroll->total_month_days, 4), 6, 2);
                            $hourlyRate = bcdiv(bcdiv($maxHourlySalary, 26, 4), 6, 2);

                        @endphp
                        <td>{{ $hourlyRate }}</td>
                        <input type="hidden" class="pay_per_hr" name="pay_per_hr[]" id="pay_per_hr-{{$i}}" value="{{ $hourlyRate }}">

                        <td>{{ $pd['man_hours'] }}</td>
                        <input type="hidden" class="man_hours" name="man_hours[]" id="man_hours-{{$i}}" value="{{ $pd['man_hours'] }}">

                        <td class="editable-cell"><input type="number" step="0.01" name="additional_hours[]" class="form-control additional_hours" value="0"  id="additional_hours-{{$i}}"></td>

                        @php

                            $basicHourlySalary = 0.00;
                            if($maxHourlySalary > 0) $basicHourlySalary = bcmul($hourlyRate, $pd['man_hours'], 2);

                        @endphp
                        {{--                        <td>{{ amountFormat($basicHourlySalary) }}</td>--}}
                        <td><input type="number" step="0.001" class="form-control basic_hourly_salary" name="basic_hourly_salary[]" id="basic_hourly_salary-{{$i}}" value="{{ $basicHourlySalary }}" readonly></td>

                        <td><input type="number" step="0.001" class="form-control additional_hourly_salary" name="additional_hourly_salary[]" id="additional_hourly_salary-{{$i}}" value="0" readonly></td>
                        <td class="editable-cell"><input type="number" step="0.01" name="absent_days[]" class="form-control absent" value="0"  id="absent_days-{{$i}}"></td>
                        <td>
                            <input type="number" step="0.01" name="absent_daily_deduction[]" class="form-control rate"  id="rate-days-{{$i}}" readonly>
                            <input type="hidden" class="form-control rate-month"  id="rate-month-{{$i}}">
                        </td>
                        {{-- <input type="hidden" name="absent_deduction[]" class="form-control absent_deduction"  id="absent_deduction-{{$i}}"> --}}
                        <td><input type="number" step="0.01" name="absent_total_deduction[]" class="form-control absent_deduction"  id="absent_deduction-{{$i}}" readonly></td>
                        <td><input type="number" step="0.01" name="basic_salary[]" class="form-control total"  id="total_basic_salary-{{$i}}" readonly></td>
                    </tr>
{{--                    @endif--}}
                @endforeach

            </tbody>
        </table>
    </div>
    <div class="form-group">
        <div class="col-3">
            <label for="grand_total">Total Salary</label>
            <input type="text" name="salary_total" class="form-control" id="salary_total"
                readonly>
        </div>
    </div>
    <div class="float-right">
        <button type="submit" class="btn btn-primary submit-salary">Save Basic Pay</button>
    </div>
    
</form>