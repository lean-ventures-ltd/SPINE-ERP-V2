@extends ('core.layouts.app')
@section('title', trans('general.dashboard_title') . ' | ' . config('core.cname'))
@section('content')

    <head>
        <!-- Latest CSS -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    </head>

    <!-- BEGIN: Content-->
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <div class="row match-height height-70-per">
                    <div class="col-12 col-lg-6">
                        <div class="card radius-8">
                            <div class="card-header">
                                <h4 class="card-title">{{ trans('dashboard.recent') }} Projects</h4>
                                <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                                <div class="heading-elements">
                                    <ul class="list-inline mb-0">
                                        <li><a href="{{ route('biller.projects.index') }}" class="btn btn-success btn-sm rounded">Manage Projects</a></li>
                                        <li><a data-action="reload"><i class="icon-reload"></i></a></li>
                                        <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive height-300">
                                    <table class="table table-hover mb-1">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>#Project No.</th>
                                            <th width="5em">Project Name</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Deadline</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($projects as $i => $project)
                                            <tr>
                                                <td class="text-truncate">{{ $i+1 }}</td>
                                                <td class="text-truncate">
                                                    <a href="{{ route('biller.projects.show', $project) }}">{{ gen4tid('PRJ-', $project->tid) }}</a>
                                                </td>
                                                <td class="text-truncate">{{ $project->name }}</td>
                                                <td class="text-truncate">{{ $project->priority }} </td>
                                                <td class="text-truncate">{{ @$project->misc->name }}</td>
                                                <td class="text-truncate">{{ dateFormat($project->end_date) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Labour Hours Graph -->
                    <div class="col-12 col-lg-6">
                        <div class="card radius-8">

                            <div class="card-content">
                                <div class="card-body">
                                    <div class="bar-chart-container">
                                        <p class="ml-6 card-title"> {{ $sevenDayLabourHours['chartTitle'] }} </p>
                                        <canvas id="key-quantities-chart"></canvas>
                                    </div>
                                                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                

                </div>





                <div class="row ">
                    <!-- Sale Invoices Graph -->
                    <div class="col-12 col-xl-8 col-lg-12">
                        <div class="card radius-8">

                            <div class="card-content">
                                <div class="card-body">
                                        <div class="bar-chart-container">
                                            <p class="ml-6 card-title">{{ $sevenDaySalesExpenses['chartTitle'] }}</p>
                                            <canvas id="invoice-totals-chart" ></canvas>
                                        </div>
{{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-12">

                        <div>

                            <!-- Yesterday's Man Hours -->
                            <div class="card mb-1 radius-8">
                                <div class="card-content">
                                    <div class="media align-items-stretch">
                                        <div class="p-2 text-center bg-warning bg-darken-2 radius-8-left">
                                            <i class="icon-clock font-large-1 white"></i>
                                        </div>
                                        <div class="p-2 bg-gradient-x-warning white media-body radius-8-right">
                                            <h6>Yesterday's Labour Hours XXXX</h6>
                                            <h5 class="text-bold-400 mb-0">
                                                {{ $labourAllocationData['yesterday']['ylaTotalManHours'] }} hrs
                                                <small class="float-right mr-4">Target: 72</small>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Yesterday Invoices -->
                            <div class="card mb-1 radius-8">
                                <div class="card-content">
                                    <div class="media align-items-stretch">
                                        <div class="p-2 text-center bg-primary bg-darken-2 radius-8-left">
                                            <i class="fa fa-file-text-o font-large-1 white"></i>
                                        </div>
                                        <div class="p-2 bg-gradient-x-primary white media-body radius-8-right">
                                            <h6>Yesterday Invoices</h6>
                                            <h5 class="text-bold-400 mb-0">
                                                <!--<i class="ft-plus"></i> -->
                                                <!--<span id="dash_1"><i class="fa fa-spinner spinner"></i></span>-->
                                                {{ $data['invoices']->count() }} invoice(s)
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-1 radius-8">
                                <div class="card-content">
                                    <div class="media align-items-stretch">
                                        <div class="p-2 text-center bg-primary bg-darken-2 radius-8-left">
                                            <i class="fa icon-credit-card font-large-1 white"></i>
                                        </div>
                                        <div class="p-2 bg-gradient-x-primary white media-body radius-8-right">
                                            <h6>Yesterday Purchases</h6>
                                            <h5 class="text-bold-400 mb-0">
                                                <!--<i class="ft-plus"></i> -->
                                                <!--<span id="dash_1"><i class="fa fa-spinner spinner"></i></span>-->
                                                {{ $data['purchases']->count() + $data['purchase_orders']->count() }} purchase(s)
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- This Month's Man Hours -->
                            <div class="card mb-1 radius-8">
                                <div class="card-content">
                                    <div class="media align-items-stretch">
                                        <div class="p-2 text-center bg-success bg-darken-2 radius-8-left">
                                            <i class="icon-clock font-large-1 white"></i>
                                        </div>
                                        <div class="p-2 bg-gradient-x-success white media-body radius-8-right">
                                            <h6>This Month's Labour Hours</h6>
                                            <h5 class="text-bold-400 mb-0">
                                                <!--<i class="ft-arrow-up"></i> <span id="dash_4"><i class="fa fa-spinner spinner"></i></span>-->
                                                {{ $labourAllocationData['thisMonth']['tmlaTotalManHours'] }} hrs
                                                <small class="float-right mr-4">Target: {{  $labourAllocationData['thisMonth']['monthHoursTarget'] }}</small>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- This Month's Sales Total-->
                            <div class="card mb-1 radius-8">
                                <div class="card-content">
                                    <div class="media align-items-stretch">
                                        <div class="p-2 text-center bg-success bg-darken-2 radius-8-left">
                                            <i class="icon-note font-large-1 white"></i>
                                        </div>
                                        <div class="p-2 bg-gradient-x-success white media-body radius-8-right">
                                            <h6>This Month Sales Total</h6>
                                            <h5 class="text-bold-400 mb-0">
                                                <!--<i class="ft-arrow-up"></i> <span id="dash_4"><i class="fa fa-spinner spinner"></i></span>-->
                                                KES {{ number_format($keyMetrics['thisMonth']['totals']['sales'], 2, '.', ',') }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- This Month's Purchases Total-->
                            <div class="card mb-1 radius-8">
                                <div class="card-content">
                                    <div class="media align-items-stretch">
                                        <div class="p-2 text-center bg-success bg-darken-2 radius-8-left">
                                            <i class="icon-clock font-large-1 white"></i>
                                        </div>
                                        <div class="p-2 bg-gradient-x-success white media-body radius-8-right">
                                            <h6>This Month Purchases Total</h6>
                                            <h5 class="text-bold-400 mb-0">
                                                <!--<i class="ft-arrow-up"></i> <span id="dash_4"><i class="fa fa-spinner spinner"></i></span>-->
                                               KES {{ number_format($keyMetrics['thisMonth']['totals']['expenses'], 2, '.', ',') }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
                
                <!-- Recent & Monthly Sales -->
                <div class="row match-height">
                    <div class="col-xl-8 col-lg-12">
                        <div class="card radius-8">
                            <div class="card-header">
                                <h4 class="card-title">{{ trans('dashboard.recent_invoices') }}</h4>
                                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                <div class="heading-elements">
                                    <ul class="list-inline mb-0">
                                        <li><a href="{{ route('biller.invoices.create') }}"
                                                class="btn btn-primary btn-sm rounded">{{ trans('invoices.add_sale') }}</a>
                                        </li>
                                        <li><a href="{{ route('biller.invoices.index') }}"
                                                class="btn btn-success btn-sm rounded">{{ trans('invoices.manage_invoices') }}</a>
                                        </li>
                                        <li></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Recent Invoices -->
                            <div class="card-content">
                                <div class="table-responsive">
                                    <table id="recent-orders"
                                        class="table table-hover mb-0 ps-container ps-theme-default">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('invoices.invoice') }}</th>
                                                <th>{{ trans('customers.customer') }}</th>
                                                <th>{{ trans('invoices.invoice_due_date') }}</th>
                                                <th>{{ trans('general.amount') }}</th>
                                                <th>{{ trans('general.status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $prefixes = prefixes();
                                            @endphp
                                            @foreach ($data['invoices'] as $invoice)
                                                <tr>
                                                    <td class="text-truncate"><a
                                                            href="{{ route('biller.invoices.show', [$invoice['id']]) }}">
                                                            @switch($invoice['i_class'])
                                                                @case(0)
                                                                    {{ $prefixes->where('class', '=', 1)->first()->value }}
                                                                @break
                                                                @case(1)
                                                                    {{ $prefixes->where('class', '=', 10)->first()->value }}
                                                                @break
                                                                @case($invoice['i_class'] > 1)
                                                                    {{ $prefixes->where('class', '=', 6)->first()->value }}
                                                                @break
                                                            @endswitch #{{ $invoice['tid'] }}
                                                        </a></td>
                                                    <td class="text-truncate">{{ @$invoice->customer->name }}</td>
                                                    <td class="text-truncate">{{ dateFormat($invoice['invoiceduedate']) }}
                                                    </td>
                                                    <td class="text-truncate">{{ amountFormat($invoice['total']) }}</td>
                                                    <td class="text-truncate"><span
                                                            class="st-{{ $invoice['status'] }}">{{ trans('payments.' . $invoice['status']) }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Buyers -->
                    <div class="col-12 col-lg-4 card radius-8" >
                        <div class="card-header">
                            <h4 class="card-title">{{ trans('dashboard.recent_buyers') }}</h4>
                            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                            <div class="heading-elements">
                                <ul class="list-inline mb-0">
                                    <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-content px-1">
                            <div id="recent-buyers_p" class="media-list height-450 position-relative">
                                @foreach ($data['customers'] as $customer)
                                    <a href="#" class="media border-0">
                                        <div class="media-left pr-1">
                                                <span class="avatar avatar-md avatar-online"><img
                                                            class="media-object rounded-circle"
                                                            src="{{ Storage::disk('public')->url('app/public/img/customer/' . $customer->picture) }}">
                                                    <i></i>
                                                </span>
                                        </div>
                                        <div class="media-body w-100">
                                            <h6 class="list-group-item-heading">
                                                {{ $customer->company ?: $customer->name }}
                                            </h6>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="loader_url" value="{{ route('biller.tasks.load') }}">
    <input type="hidden" id="mini_dash" value="{{ route('biller.mini_dash') }}">
    <!-- END: Content-->
    {{-- @include('focus.projects.modal.task_view') --}}
@endsection

@section('after-styles')
    {!! Html::style('core/app-assets/vendors/css/charts/morris.css') !!}
@endsection

@section('extra-scripts')
{{ Html::script('core/app-assets/vendors/js/charts/raphael-min.js') }}
{{ Html::script('core/app-assets/vendors/js/charts/morris.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script type="text/javascript">

    Chart.register(ChartDataLabels);


    // Daily Sales and Expense Totals
    $(function(){

        let sevenDaySalesExpenses = @json($sevenDaySalesExpenses);

        var ctx = $("#invoice-totals-chart");

        var chart1 = new Chart(ctx, {
            type: 'line',

            data: {
                labels: sevenDaySalesExpenses.salesDates,
                datasets: [
                    {
                        label: "Sales",
                        data: sevenDaySalesExpenses.salesTotals,
                        tension: 0.1,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192)',
                    },
                    {
                        label: "Expenses",
                        data: sevenDaySalesExpenses.expensesTotals,
                        tension: 0.1,
                        backgroundColor: 'rgba(255, 205, 86, 0.2)',
                        borderColor: 'rgba(255, 205, 86)',
                    },
                ]
            },
            options: {
                datasets : {
                    bar : {
                        borderRadius : 6,
                        borderSkipped : 'bottom',
                    }
                },
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Day of the month'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Invoice Totals'
                        },
                        ticks: {
                            beginAtZero:true,
                            userCallback: function(value, index, values) {
                                value = value.toString();
                                value = value.split(/(?=(?:...)*$)/);
                                value = value.join(',');
                                return value;
                            }
                        }
                    }]
                },
                tooltips: {
                    enabled: true,
                    mode: 'single',
                    callbacks: {
                        title: function (tooltipItems, data) {
                            //Return value for title
                            return dailySalesExpensesData.month + ' ' + tooltipItems[0].xLabel;
                        },
                        label: function (tooltipItems, data) { // Solution found on https://stackoverflow.com/a/34855201/6660135
                            //Return value for label
                            return 'KES ' + tooltipItems.yLabel;
                        }
                    }
                },
                responsive: true,
                transitions: {
                    show: {
                        animations: {
                            x: {
                                from: 0
                            },
                            y: {
                                from: 0
                            }
                        }
                    },
                    hide: {
                        animations: {
                            x: {
                                to: 0
                            },
                            y: {
                                to: 0
                            }
                        }
                    }
                },
                plugins:{
                    datalabels: {
                        display: true,
                        color: 'black',
                        anchor: 'top',
                        align: 'right',
                        labels: {
                            title: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        formatter: (value, context) => {
                            // Use Intl.NumberFormat to format the value with commas
                            return new Intl.NumberFormat('en-US').format(value);
                        },
                    }
                }
            }
        });


    });


    // Key Metrics Quantities
    $(function(){

        let sevenDayLabourHours = @json($sevenDayLabourHours);


        //get the pie chart canvas
        var ctx = $("#key-quantities-chart");

        //create Pie Chart class object
        var chart1 = new Chart(ctx, {
            type: 'bar',

            data: {
                labels: (sevenDayLabourHours.labourDates),
                datasets: [

                    {
                        label: "Labour Hours",
                        data: sevenDayLabourHours.hoursTotals,
                        backgroundColor: 'rgba(75,192,77,0.2)',
                        borderColor: 'rgba(75,192,77)',
                        borderWidth: 1,
                    },

                ]
            },
            options: {
                datasets : {
                    bar : {
                        borderRadius : 6,
                        borderSkipped : 'bottom',
                    }
                },
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Day of the month'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Invoice Totals'
                        },
                        ticks: {
                            beginAtZero:true,
                            userCallback: function(value, index, values) {
                                value = value.toString();
                                value = value.split(/(?=(?:...)*$)/);
                                value = value.join(',');
                                return value;
                            }
                        }
                    }]
                },
                responsive: true,
                transitions: {
                    show: {
                        animations: {
                            x: {
                                from: 0
                            },
                            y: {
                                from: 0
                            }
                        }
                    },
                    hide: {
                        animations: {
                            x: {
                                to: 0
                            },
                            y: {
                                to: 0
                            }
                        }
                    }
                },
                plugins:{
                    datalabels: {
                        color: 'black',
                        anchor: 'top',
                        labels: {
                            title: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        padding:{
                            bottom: 20,
                        }
                    }
                }
            }
        });


    });


    //Key Metrics TOTALS
    $(function(){

        let keyMetrics = @json($keyMetrics);
        let yesterdayTotals = keyMetrics.yesterday.totals;
        let monthTotals = keyMetrics.thisMonth.totals;

        //get the pie chart canvas
        var ctx = $("#key-totals-chart");

        //create Pie Chart class object
        var chart1 = new Chart(ctx, {
            type: 'bar',

            data: {
                labels: ["Yesterday", "This Month"],
                datasets: [
                    {
                        label: "Sales",
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(255, 205, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(201, 203, 207, 0.2)'
                        ],
                        borderWidth: 1,
                        borderColor: 'rgba(255, 205, 86)',
                        data: [yesterdayTotals.sales, monthTotals.sales]
                    },
                    {
                        label: "Expense",
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderWidth: 1,
                        borderColor: 'rgba(153, 102, 255)',
                        data: [yesterdayTotals.expenses, monthTotals.expenses]
                    },
                ]
            },
            options: {
                datasets : {
                    bar : {
                        borderRadius : 6,
                        borderSkipped : 'bottom',
                    }
                },
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Day of the month'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Invoice Totals'
                        },
                        ticks: {
                            beginAtZero:true,
                            userCallback: function(value, index, values) {
                                value = value.toString();
                                value = value.split(/(?=(?:...)*$)/);
                                value = value.join(',');
                                return value;
                            }
                        }
                    }]
                },
                responsive: true,
                transitions: {
                    show: {
                        animations: {
                            x: {
                                from: 0
                            },
                            y: {
                                from: 0
                            }
                        }
                    },
                    hide: {
                        animations: {
                            x: {
                                to: 0
                            },
                            y: {
                                to: 0
                            }
                        }
                    }
                },
                plugins:{
                    datalabels: {
                        color: 'black',
                        anchor: 'top',
                        labels: {
                            title: {
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        padding:{
                            bottom: 20,
                        }
                    }
                }
            }
        });
    });

    function loadDash() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var action_url = $('#mini_dash').val();
        $.ajax({
            url: action_url,
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                var i = 1;
                $.each(data.dash, function(key, value) {
                    $('#dash_' + i).text(value);
                    i++;
                });
                drawCompareChart(data.inv_exp);
                sales(data.sales);
            }
        });
        window.dispatchEvent(new Event('resize'));
    }

    function drawCompareChart(inv_exp) {
        $('#dashboard-sales-breakdown-chart').empty();
        Morris.Donut({
            element: 'income-compare-chart',
            data: [{
                    label: "{{ trans('accounts.Income') }}",
                    value: inv_exp.income
                },
                {
                    label: "{{ trans('accounts.Expenses') }}",
                    value: inv_exp.expense
                }
            ],
            resize: true,
            colors: ['#34cea7', '#ff6e40'],
            gridTextSize: 6,
            gridTextWeight: 400
        });
    }

    function drawIncomeChart(dataIncome) {
        $('#dashboard-income-chart').empty();
        Morris.Area({
            element: 'dashboard-income-chart',
            data: dataIncome,
            xkey: 'x',
            ykeys: ['y'],
            ymin: 'auto 40',
            labels: ['{{ trans('general.amount') }}'],
            xLabels: "day",
            hideHover: 'auto',
            yLabelFormat: function(y) {
                // Only integers
                if (y === parseInt(y, 10)) return y;
                return '';
            },
            resize: true,
            lineColors: ['#00A5A8'],
            pointFillColors: ['#00A5A8'],
            fillOpacity: 0.4,
        });
    }

    function drawExpenseChart(dataExpenses) {
        $('#dashboard-expense-chart').empty();
        Morris.Area({
            element: 'dashboard-expense-chart',
            data: dataExpenses,
            xkey: 'x',
            ykeys: ['y'],
            ymin: 'auto 0',
            labels: ['{{ trans('general.amount') }}'],
            xLabels: "day",
            hideHover: 'auto',
            yLabelFormat: function(y) {
                // Only integers
                if (y === parseInt(y, 10)) return y;
                return '';
            },
            resize: true,
            lineColors: ['#ff6e40'],
            pointFillColors: ['#34cea7']
        });
    }

    function sales(sales_data) {
        $('#products-sales').empty();
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        Morris.Area({
            element: 'products-sales',
            data: sales_data,
            xkey: 'y',
            ykeys: ['sales', 'invoices'],
            labels: ['sales', 'invoices'],
            behaveLikeLine: true,
            xLabelFormat: function(x) {
                var day = x.getDate();
                var month = months[x.getMonth()];
                return day + ' ' + month;
            },
            resize: true,
            pointSize: 0,
            pointStrokeColors: ['#00B5B8', '#FA8E57', '#F25E75'],
            smooth: true,
            gridLineColor: '#E4E7ED',
            numLines: 6,
            gridtextSize: 14,
            lineWidth: 0,
            fillOpacity: 0.9,
            hideHover: 'auto',
            lineColors: ['#00B5B8', '#F25E75'],
        });
    }

    $('a[data-toggle=tab').on('shown.bs.tab', function(e) {
        window.dispatchEvent(new Event('resize'));
    });
</script>

<style>
    div.scroll {
        background-color: #fed9ff;
        width: 600px;
        height: 150px;
        overflow-x: hidden;
        overflow-y: auto;
        text-align: center;
        padding: 20px;
    }
    .radius-8-right {
        border-radius: 0 8px 8px 0;
    }
    .radius-8-left {
        border-radius: 8px 0 0 8px;
    }
    .radius-8 {
        border-radius: 8px;
    }
    .grid-container-2 {
        display: grid;
        gap: 20px;
        grid-template-columns: auto auto;
    }
</style>
@endsection
