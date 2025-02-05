@extends ('core.layouts.app')
@section ('title', trans('labels.backend.productcategories.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12">
            <h4 class=" mb-0">{{ trans('labels.backend.productcategories.management') }}</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.refill_product_categories.partials.productcategories-header-buttons')
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
                            <table id="categoriesTbl"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Description</th>
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
    $(function () {
        setTimeout(function () {
            draw_data()
        }, {{config('master.delay')}});
    });

    function draw_data() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        var dataTable = $('#categoriesTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: '{{ route("biller.refill_product_categories.get") }}',
                type: 'post',
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['title', 'extra'].map(v => ({data:v, name: v})),
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection
