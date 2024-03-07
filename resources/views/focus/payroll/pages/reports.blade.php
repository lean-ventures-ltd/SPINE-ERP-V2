@extends('core.layouts.app')

@section('title', 'Payroll Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">Payroll Reports</h4>

            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">

                    <div class="media-body media-right text-right">
                        @include('focus.payroll.partials.payroll-header-buttons')
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-1 h2">{{ (new DateTime($payrollTallies[0]['payroll_month']))->format('M Y') }}</div>
                    <div class="col-4 h3">| {{ $payrollTallies[0]['working_days'] }} Working Days</div>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                           role="tab" aria-selected="true"><span class="">NSSF REPORTS </span>

                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2"
                           role="tab" aria-selected="false"><span>PAYE REPORTS</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3"
                           role="tab" aria-selected="false">
                            <span>NHIF REPORTS</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9"
                           role="tab" aria-selected="false">
                            <span>HOUSING LEVY REPORTS</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab8" data-toggle="tab" aria-controls="tab8" href="#tab8" role="tab"
                           aria-selected="false">
                            <span>PAYROLL REPORTS</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content px-1 pt-1">
                    <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                        @include('focus.payroll.reports.nssf-report')
                    </div>
                    <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                        @include('focus.payroll.reports.paye-report')
                    </div>
                    <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                        @include('focus.payroll.reports.nhif-report')
                    </div>
                    <div class="tab-pane" id="tab9" role="tabpanel" aria-labelledby="base-tab9">
                        @include('focus.payroll.reports.housing-levy-report')
                    </div>
                    <div class="tab-pane" id="tab8" role="tabpanel" aria-labelledby="base-tab8">
                        @include('focus.payroll.reports.payroll-report')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        $(function () {
            setTimeout(function () {
                draw_data()
            }, {{config('master.delay')}});
        });

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#payrollTable').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.payroll.get_reports", $payroll) }}',
                    data: { payroll_id: @json(@$payroll) },
                    type: 'post',
                },
                columns: [
                    {data: 'payroll_id', name: 'payroll_id'},
                    {data: 'employee_id', name: 'employee_id'},
                    {data: 'name', name: 'name'},
                    {data: 'id_number', name: 'id_number'},
                    {data: 'fixed_salary', name: 'fixed_salary'},
                    {data: 'max_hourly_salary', name: 'max_hourly_salary'},
                    {data: 'pay_per_hr', name: 'pay_per_hr'},
                    {data: 'man_hours', name: 'man_hours'},
                    {data: 'basic_hourly_salary', name: 'basic_hourly_salary'},
                    {data: 'absent_days', name: 'absent_days'},
                    {data: 'absent_daily_deduction', name: 'absent_daily_deduction'},
                    {data: 'absent_total_deduction', name: 'absent_total_deduction'},
                    {data: 'basic_salary', name: 'basic_salary'},
                    {data: 'house_allowance', name: 'house_allowance'},
                    {data: 'transport_allowance', name: 'transport_allowance'},
                    {data: 'other_allowance', name: 'other_allowance'},
                    {data: 'total_allowance', name: 'total_allowance'},
                    {data: 'basic_plus_allowance', name: 'basic_plus_allowance'},
                    {data: 'taxable_deductions', name: 'taxable_deductions'},
                    {data: 'deduction_narration', name: 'deduction_narration'},
                    {data: 'nssf', name: 'nssf'},
                    {data: 'taxable_gross', name: 'taxable_gross'},
                    {data: 'nhif', name: 'nhif'},
                    {data: 'housing_levy', name: 'housing_levy'},
                    {data: 'income_tax', name: 'income_tax'},
                    {data: 'nhif_relief', name: 'nhif_relief'},
                    {data: 'personal_relief', name: 'personal_relief'},
                    {data: 'paye', name: 'paye'},
                    {data: 'netpay', name: 'netpay'},
                    {data: 'loan', name: 'loan'},
                    {data: 'advance', name: 'advance'},
                    {data: 'benefits', name: 'benefits'},
                    {data: 'other_deductions', name: 'other_deductions'},
                    {data: 'other_allowances', name: 'other_allowances'},
                    {data: 'net_after_bnd', name: 'net_after_bnd'},
                    {data: 'primary_contact', name: 'primary_contact'},
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });

            $('#nssfTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.payroll.getNssfReport", $payroll) }}',
                    data: { payroll_id: @json(@$payroll) },
                    type: 'post',
                },
                columns: [
                    { data: 'payroll_id', name: 'payroll_id' },
                    { data: 'surname', name: 'surname' },
                    { data: 'other_names', name: 'other_names' },
                    { data: 'id_number', name: 'id_number' },
                    { data: 'kra_pin', name: 'kra_pin' },
                    { data: 'nssf_number', name: 'nssf_number' },
                    { data: 'basic_plus_allowance', name: 'basic_plus_allowance' },
                    // Add other columns as needed
                ],
                order: [[1, 'asc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });

            $('#payeTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.payroll.getPayeReport", $payroll) }}',
                    data: { payroll_id: @json(@$payroll) },
                    type: 'post',
                },
                columns: [
                    {data: 'kra_pin', name: 'kra_pin'},
                    {data: 'name', name: 'name'},
                    {data: 'tax_obligation', name: 'tax_obligation'},
                    {data: 'employee_type', name: 'employee_type'},
                    {data: 'basic_plus_allowance', name: 'basic_plus_allowance'},
                    {data: 'zero_col', name: 'zero_col1'},
                    {data: 'zero_col', name: 'zero_col2'},
                    {data: 'zero_col', name: 'zero_col3'},
                    {data: 'zero_col', name: 'zero_col4'},
                    {data: 'zero_col', name: 'zero_col5'},
                    {data: 'zero_col', name: 'zero_col6'},
                    {data: 'zero_col', name: 'zero_col7'},
                    {data: 'blank_col', name: 'blank_col1'},
                    {data: 'zero_col', name: 'zero_col8'},
                    {data: 'zero_col', name: 'zero_col9'},
                    {data: 'blank_col', name: 'blank_col2'},
                    {data: 'zero_col', name: 'zero_col10'},
                    {data: 'benefit_not_given', name: 'benefit_not_given'},
                    {data: 'blank_col', name: 'blank_col3'},
                    {data: 'blank_col', name: 'blank_col4'},
                    {data: 'blank_col', name: 'blank_col5'},
                    {data: 'blank_col', name: 'blank_col6'},
                    {data: 'blank_col', name: 'blank_col7'},
                    {data: 'blank_col', name: 'blank_col8'},
                    {data: 'nssf', name: 'nssf'},
                    {data: 'blank_col', name: 'blank_col9'},
                    {data: 'zero_col', name: 'zero_col11'},
                    {data: 'blank_col', name: 'blank_col10'},
                    {data: 'blank_col', name: 'blank_col11'},
                    {data: 'blank_col', name: 'blank_col12'},
                    {data: 'blank_col', name: 'blank_col13'},
                    {data: 'personal_relief', name: 'personal_relief'},
                    {data: 'nhif_relief', name: 'nhif_relief'},
                    {data: 'blank_col', name: 'blank_col14'},
                    {data: 'paye', name: 'paye'},
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });

            $('#nhifTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.payroll.getNhifReport", $payroll) }}',
                    data: { payroll_id: @json(@$payroll) },
                    type: 'post',
                },
                columns: [
                    { data: 'payroll_id', name: 'payroll_id' },
                    { data: 'surname', name: 'surname' },
                    { data: 'other_names', name: 'other_names' },
                    { data: 'id_number', name: 'id_number' },
                    { data: 'nhif_number', name: 'nhif_number' },
                    { data: 'nhif', name: 'nhif' },
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });

            $('#housingLevyTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.payroll.getHousingLevyReport", $payroll) }}',
                    data: { payroll_id: @json(@$payroll) },
                    type: 'post',
                },
                columns: [
                    { data: 'id_number', name: 'id_number' },
                    { data: 'name', name: 'name' },
                    { data: 'kra_pin', name: 'kra_pin' },
                    { data: 'basic_plus_allowance', name: 'basic_plus_allowance'}
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,

            });
        }
    </script>
@endsection
