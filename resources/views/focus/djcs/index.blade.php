@extends ('core.layouts.app')

@section ('title', 'Djc Report Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Djc Report Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.djcs.partials.djcs-header-buttons')
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
                            <table id="djc-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Report No</th>
                                        <th>Client & Branch</th>                                            
                                        <th>Subject</th>                                            
                                        <th>Job Card</th>
                                        <th>Client Ref</th>
                                        <th>Ticket No</th>
                                        <th>Report Date</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
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
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}

<script>
    setTimeout(() => draw_data(), "{{config('master.delay')}}");

    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    function draw_data() {
        var dataTable = $('#djc-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: '{{ route("biller.djcs.get") }}',
                type: 'post',
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...[
                    'tid', 'customer', 'subject', 'job_card', 'client_ref', 'lead_id', 'report_date'
                ].map(v => ({data:v, name:v})),
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [0, "desc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection