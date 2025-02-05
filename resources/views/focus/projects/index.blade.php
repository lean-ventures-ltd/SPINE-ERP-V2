@extends ('core.layouts.app')

@section ('title', trans('labels.backend.projects.management'))

@section('content')
    <div class="content-wrapper">
        <!-- Header -->
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Project Management</h4>
            </div>
            <div class="col-6">
                <div class="media-body media-right text-right">
                    @include('focus.projects.partials.projects-header-buttons')
                </div>
            </div>
        </div>
        <!-- End Header -->

        <!-- Left sidebar -->
        @include('focus.projects.partials.sidebar')
        <!-- End Left sidebar -->

        <!-- Content -->
        <div class="content-right" style="width: calc(100% - 270px)">
            <div class="content-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-control select2" id="customerFilter" data-placeholder="Search Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control select2" id="branchFilter" data-placeholder="Search Branch">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="custom-select" id="projectStatus">
                                    <option value="">-- Select Status --</option>
                                    @foreach ($mics as $row)
                                        @if ($row->section == 2)
                                            <option value="{{ $row->id }}">{{ $row->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div> 
                        <br>
                        <div class="row">
                            <div class="ml-2">{{ trans('general.search_date')}} </div>
                            <div class="col-md-2">
                                <input type="text" name="start_date" id="start_date" class="form-control datepicker date30  form-control-sm" autocomplete="off" />
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="end_date" id="end_date" class="form-control datepicker form-control-sm" autocomplete="off" />
                            </div>
                            <div class="col-md-2">
                                <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                            </div>
                        </div>  
                        <hr>
                        <table id="projectsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>#Project No.</th>
                                    <th>#Quote/PI</th>
                                    <th>Name</th>
                                    <th>Exp G.P(%)</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Job Hrs</th>
                                    <th>Start</th>
                                    <th>Deadline</th>
                                    <th>{{ trans('general.action') }}</th>
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
        <!-- End Content -->
    </div>

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    {{-- <input type="hidden" id="loader_url" value="{{route('biller.projects.load')}}"> --}}
    @include('focus.projects.modal.project_new')
    @include('focus.projects.modal.status_modal')
    @include('focus.projects.modal.project_view')
@endsection
@section('after-styles')
    {{ Html::style('core/app-assets/css-'.visual().'/pages/app-todo.css') }}
    {{ Html::style('core/app-assets/css-'.visual().'/plugins/forms/checkboxes-radios.css') }}
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection
@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
{{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customerFilter").val()}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        },
        quoteSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.projects.quotes_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#person").val(), branch_id: $("#branch_id").val() }),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        }
    };

    // form submit callback
    function trigger(res) {
        $('#projectsTbl').DataTable().destroy();
        Index.drawDataTable();
    }

    const Index = {
        startDate: '',
        endDate: '',
        
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            
            
            $("#submit-data_project").on("click", Index.onSubmitProject);
            $("#projectStatus").change(Index.onChangeStatus);
            $("#customerFilter").select2({allowClear: true}).change(Index.onChangeCustomer);
            $("#branchFilter").select2(config.branchSelect).change(Index.onChangeBranch);
            $('#AddProjectModal').on('shown.bs.modal', Index.onShownModal);
            $(document).on('click', '.status', Index.onStatusClick);
            $('#search').click(Index.onClickSearch);
            Index.drawDataTable();
        },
        
        onClickSearch() {
            Index.startDate = $('#start_date').val();
            Index.endDate = $('#end_date').val();
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },

        onStatusClick() {
            $('#status_project_id').val($(this).attr('project-id'));
            $('#status').val($(this).attr('data-id'));
            $('#end_note').val($(this).attr('end-note'));
        },
        
        onSubmitProject() {
            e.preventDefault();
            let form_data = {};
            form_data['form'] = $("#data_form_project").serialize();
            form_data['url'] = $('#action-url').val();
            $('#AddProjectModal').modal('toggle');
            addObject(form_data, true);
        },

        onChangeCustomer() {
            $("#branchFilter option:not(:eq(0))").remove();
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        onChangeBranch() {
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },
        
        onChangeStatus() {
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },

        onShownModal() {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: "{{ config('core.user_date_format') }}"
            });
            $('.from_date').datepicker(config.date).datepicker('setDate', new Date());
            $('.to_date').datepicker(config.date).datepicker('setDate', '{{dateFormat(date('Y-m-d', strtotime('+30 days', strtotime(date('Y-m-d')))))}}');
            $('#color').colorpicker();
            $("#tags").select2();
            $("#employee").select2();

            const branchConfig = {...config.branchSelect};
            branchConfig.ajax.data = ({term}) => ({search:term, customer_id: $('#person').val()});
            $("#branch_id").select2(branchConfig);

            $("#person").select2({allowClear: true, dropdownParent: $('#AddProjectModal .modal-body')})
            .change(function() { $("#branch_id").val('') });
            
            // attach primary quote
            $("#quotes").select2(config.quoteSelect).change(function() {
                $('.proj_title').val('');
                $('.proj_short_descr').val('');
                let text = $("#quotes option:eq(1)").text();
                if (text) {
                    text = text.split('-')[2];
                    $('.proj_title').val(text);
                    $('.proj_short_descr').val(text);
                }
            });
        },

        drawDataTable() {
            $('#projectsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.projects.get') }}",
                    type: 'POST',
                    data: {
                        customer_id: $("#customerFilter").val(),
                        branch_id: $("#branchFilter").val(),
                        status: $("#projectStatus").val(),
                        start_date: Index.startDate,
                        end_date: Index.endDate,
                    }
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...['tid', 'main_quote_id', 'name', 'exp_profit_margin', 'priority', 'status', 'job_hrs', 'start_date', 'end_date'].map(v => ({data: v, name: v})),
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [4] },
                    // { type: "custom-date-sort", targets: [1,6] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(Index.init);
</script>
@endsection
