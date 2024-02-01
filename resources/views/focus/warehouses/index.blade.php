@extends ('core.layouts.app')

@section ('title', 'Product Location')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-6">
            <h4 class="content-header-title mb-0">
                {{ 'Product Location' }}
            </h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.warehouses.partials.warehouses-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 h4">Product Count</div>                            
                            <div class="col-2 h4">{{ $product_count }}</div>
                        </div>
                        <div class="row">
                            <div class="col-2 h4">Total Unit Cost</div>                           
                            <div class="col-4 h4">{{ amountFormat($product_worth) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="warehouses-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Location</th>
                                        <th>Product Count</th>
                                        <th>{{ trans('productcategories.total_worth') }}</th>
                                        <th>{{ trans('general.createdat') }}</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1">
                                            <i class="fa fa-spinner spinner"></i>
                                        </td>                                                
                                    </tr>
                                </tbody>
                            </table>
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
<script>
    const config = {
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }
        }
    };

    const Index = {
        init() {
            this.drawDataTable();
        },

        drawDataTable() {
            $('#warehouses-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.warehouses.get") }}',
                    type: 'post'
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'total', name: 'total'},
                    {data: 'worth', name: 'worth'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(() => Index.init());
</script>
@endsection
