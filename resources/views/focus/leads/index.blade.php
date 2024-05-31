@extends ('core.layouts.app')

@section ('title', 'Tickets Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tickets Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.leads.partials.leads-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row no-gutters">
                            <div class="col-sm-3 col-md-2 h4">Open Tickets</div>
                            <div class="col-sm-2 col-md-1 h4 text-primary font-weight-bold">{{ $open_lead }}</div>
                            <div class="col-sm-12 col-md-1 h4 text-primary font-weight-bold">{{ numberFormat(div_num($open_lead, $total_lead) * 100) }}%</div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-sm-3 col-md-2 h4">Closed Tickets</div>
                            <div class="col-sm-2 col-md-1 h4 text-success font-weight-bold">{{ $closed_lead }}</div>
                            <div class="col-sm-12 col-md-1 h4 text-success font-weight-bold">{{ numberFormat(div_num($closed_lead, $total_lead) * 100) }}%</div>
                        </div>

                        <div class="row mt-2" id="filters">

                            <div class="col-3">
                                <label for="status">Status</label>
                                <select name="status" class="custom-select" id="status" data-placeholder="Filter by status">
                                    <option value=""> Filter by Status </option>
                                    @foreach (['Open' => 'OPEN', 'Closed' => 'CLOSED'] as $key => $value)
                                        <option value="{{ $value }}">{{ $key }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-3">
                                <label for="category" class="caption">Category</label>
                                <select class="custom-select" name="account_id" id="category" data-placeholder="Filter by Income Category">
                                    @foreach ($income_accounts as $row)

                                        @if($row->holder !== 'Stock Gain' && $row->holder !== 'Others' && $row->holder !== 'Point of Sale' && $row->holder !== 'Loan Penalty Receivable' && $row->holder !== 'Loan Interest Receivable')
                                            <option value="{{ $row->id }}"  @if($row->id == @$quote->account_id) selected @endif>
                                                {{ $row->holder }}
                                            </option>
                                        @endif

                                    @endforeach
                                </select>
                            </div>

                            <div class="col-2">
                                <label for="from_date">From Date</label>
                                <input type="text" id="from_date" name="from_date" required placeholder="Filter From..." class="datepicker form-control box-size mb-2">
                            </div>

                            <div class="col-2">
                                <label for="to_date">To Date</label>
                                <input type="text" id="to_date" name="to_date" required placeholder="Filter To..." class="datepicker form-control box-size mb-2">
                            </div>

                            <div class="col-2">

                                <button id="clear_filters" class="btn btn-secondary round mt-2" > Clear Filters </button>

                            </div>

                        </div>



                    </div>
                </div>
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="leads-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ticket No</th>
                                        <th>Client & Branch</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>New/Existing</th>
                                        <th>Category</th>
                                        <th>Source</th>
                                        <th>{{ trans('general.createdat') }}</th>
                                        <th>Client Ref</th>
                                        <th>Days to Event</th>
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
{{ Html::script('focus/js/select2.min.js') }}
<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });

    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})

    $('#status').select2({allowClear: true}).val('').trigger('change');
    $('#category').select2({allowClear: true}).val('').trigger('change');

    // $('#status, #category, #from_date, #to_date').change(redrawTable());
    $('#filters').on('change', '#status, #category, #from_date, #to_date', () => {
        try {
            $('#leads-table').DataTable().destroy();
            draw_data();
        } catch (error) {
            console.error('An error occurred:', error);
        }
    });



    $('#clear_filters').click( () => {

        $('#status, #category').val('').trigger('change');
        $('#from_date, #to_date').val('');
        $('#leads-table').DataTable().destroy();
        draw_data();
    });



    function draw_data(filters = {}) {
        $('#leads-table').dataTable({
            stateSave: true,
            processing: true,
            responsive: true,
            language: {@lang("datatable.strings")},
            ajax: {
                url: '{{ route("biller.leads.get") }}',
                type: 'post',
                data: {
                    status: $('#status').val(),
                    category: $('#category').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                },
            },
            columns: [
                {
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'reference',
                    name: 'reference'
                },
                {
                    data: 'client_name',
                    name: 'client_name'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'client_status',
                    name: 'client_status'
                },
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'source',
                    name: 'source'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                },
                {
                    data: 'client_ref',
                    name: 'client_ref'
                },
                {
                    data: 'exact_date',
                    name: 'exact_date'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            columnDefs: [
                { type: "custom-date-sort", targets: [6] }
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection