@extends ('core.layouts.app')

@section ('title', 'Aging Reports')

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Aging Report</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.customers.partials.customers-header-buttons')
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
                                <table id="agingTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Customer Name</th>
                                            @foreach (['0 - 30', '31 - 60', '61 - 90', '91 - 120', '120+'] as $val)
                                                <th>{{ $val }}</th>
                                            @endforeach
                                            <th>Aging Total</th>  
                                            <th>Unallocated</th>
                                            <th>Outstanding</th>                     
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totals = array_fill(0, 5, 0); // Assuming there are 5 aging clusters
                                            $totalAgingTotal = 0;
                                            $totalUnallocated = 0;
                                            $totalOutstanding = 0;
                                        @endphp
                                        @foreach ($customers_data as $data)
                                            @php
                                                $customer = $data['customer'];
                                                $aging_cluster = $data['aging_cluster'];
                                                $total_aging = 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $customer->name }}</td>
                                                @for ($i = 0; $i < count($aging_cluster); $i++) 
                                                    <td>
                                                        {{ numberFormat($aging_cluster[$i]) }}
                                                        @php
                                                            $total_aging += $aging_cluster[$i];
                                                            $totals[$i] += $aging_cluster[$i];
                                                        @endphp
                                                    </td>
                                                @endfor
                                                <td>{{ numberFormat($total_aging) }}</td>
                                                <td>{{ numberFormat($customer->on_account) }}</td>
                                                <td>{{ numberFormat($total_aging - $customer->on_account) }}</td>
                                                @php
                                                    $totalAgingTotal += $total_aging;
                                                    $totalUnallocated += $customer->on_account;
                                                    $totalOutstanding += ($total_aging - $customer->on_account);
                                                @endphp
                                            </tr>
                                        @endforeach                    
                                    </tbody>  
                                    <thead>
                                        <tr>
                                            <th>Total</th>
                                            @for ($i = 0; $i < count($totals); $i++)
                                                <th>{{ numberFormat($totals[$i]) }}</th>
                                            @endfor
                                            <th>{{ numberFormat($totalAgingTotal) }}</th>
                                            <th>{{ numberFormat($totalUnallocated) }}</th>
                                            <th>{{ numberFormat($totalOutstanding) }}</th>
                                        </tr>
                                    </thead>                   
                                </table> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $('#agingTbl').dataTable({
        stateSave: true,
        processing: true,
        responsive: true,
        language: {@lang('datatable.strings')},
        
        order: [[0, "desc"]],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: ['csv', 'excel', 'print'],
        lengthMenu: [
            [25, 50, 100, 200, -1],
            [25, 50, 100, 200, "All"]
        ],
    });
</script>

@endsection