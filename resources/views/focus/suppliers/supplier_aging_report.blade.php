@extends ('core.layouts.app')

@section ('title', 'Aging Reports')

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Supplier Aging Report</h4>
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
                                <table id="agingTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Supplier Name</th>
                                            @foreach (['0 - 30', '31 - 60', '61 - 90', '91 - 120', '120+'] as $val)
                                                <th>{{ $val }}</th>
                                            @endforeach
                                            <th>Aging Total</th>  
                                            <th>Unallocated</th>
                                            <th>Outstanding</th>                     
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($suppliers_data as $data)
                                            @php
                                                $supplier = $data['supplier'];
                                                $aging_cluster = $data['aging_cluster'];
                                                $total_aging = 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $supplier->name }}</td>
                                                @for ($i = 0; $i < count($aging_cluster); $i++) 
                                                    <td>
                                                        {{ numberFormat($aging_cluster[$i]) }}
                                                        @php
                                                            $total_aging += $aging_cluster[$i];
                                                        @endphp
                                                    </td>
                                                @endfor
                                                <td>{{ numberFormat($total_aging) }}</td>
                                                <td>{{ numberFormat($supplier->on_account) }}</td>
                                                <td>{{ numberFormat($total_aging - $supplier->on_account) }}</td>
                                            </tr>
                                        @endforeach                    
                                    </tbody>                     
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