@extends ('core.layouts.app')

@section ('title',  'Journals Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Journals Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.journals.partials.journals-header-buttons')
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
                            <table id="journalsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#Entry No</th>
                                        <th>Date</th>
                                        <th>Note</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Action</th>
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    $('#journalsTbl').dataTable({
        stateSave: true,
        serverside: true,
        processing: true,
        responsive: true,
        language: {@lang('datatable.strings')},
        ajax: {
            url: '{{ route("biller.journals.get") }}',
            type: 'post'
        },
        columns: [{
                data: 'DT_Row_Index',
                name: 'id'
            },
            {
                data: 'tid',
                name: 'tid'
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'note',
                name: 'note'
            },
            {
                data: 'debit_ttl',
                name: 'debit_ttl'
            },
            {
                data: 'credit_ttl',
                name: 'credit_ttl'
            },
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            },            
        ],
        columnDefs: [
            { type: "custom-number-sort", targets: [4, 5] },
            { type: "custom-date-sort", targets: [2] }
        ],
        order: [[0, "desc"]],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: ['csv', 'excel', 'print'],
    });
</script>
@endsection