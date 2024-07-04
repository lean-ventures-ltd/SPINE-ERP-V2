@extends ('core.layouts.app')

@section ('title', 'Valuation Quotes/PI')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Valuation Quotes/PI</h4>
        </div>                      
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3">
                                    <select id="valuation_status" class="custom-select">
                                        <option value="">-- Valuation Status--</option>
                                        @foreach (['partially valuated', 'unvaluated'] as $val)
                                            <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-2">{{ trans('general.search_date')}} </div>
                                <div class="col-2">
                                    <input type="text" name="start_date" id="start_date" class="form-control datepicker date30  form-control-sm" autocomplete="off" />
                                </div>
                                <div class="col-2">
                                    <input type="text" name="end_date" id="end_date" class="form-control datepicker form-control-sm" autocomplete="off" />
                                </div>
                                <div class="col-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                </div>
                            </div>
                            <hr>
                            <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#Quote/PI</th>
                                        <th>Customer - Branch</th>
                                        <th>Title</th>                                            
                                        <th>Amount</th>
                                        <th>Verified</th>
                                        <th>Valuated</th>
                                        <th>Balance</th>
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
                            {{-- form redirect to valuation creation --}}
                            <form action="{{ route('biller.job_valuations.create') }}">
                                <input type="hidden" name="quote_id" id="quote">
                            </form>  
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
        ajaxSetup: {
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
        },
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajaxSetup);
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());

            $('#quotesTbl').on('change', '.select-row', this.selectRow);
            $('#verify_state').change(this.verifyStateChange);
            $('#search').click(this.searchDateClick);
            this.drawDataTable();
        },

        verifyStateChange() {
            const el = $(this);
            $('#quotesTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        searchDateClick() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const verifyState = $('#verify_state').val();
            if (!startDate || !endDate) return alert("Date range required!"); 

            $('#quotesTbl').DataTable().destroy();
            return Index.drawDataTable({
                start_date: startDate, 
                end_date: endDate,
                verify_state: verifyState
            });
        },

        selectRow() {
            const el = $(this);
            if (el.prop('checked')) {
                $('#quote').val(el.val());
                $('#quotesTbl tbody tr').each(function() {
                    if ($(this).find('.select-row').val() != el.val()) {
                        $(this).find('.select-row').prop('checked', false);
                    }
                });
            } else {
                $('#quote').val('');
                $('#quotesTbl tbody tr').each(function() {
                    $(this).find('.select-row').prop('checked', false);
                });
            }
            if ($('#quote').val()) {
                swal({
                    title: 'Valuate this item?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }, () => $('form').submit()); 
            }
        },

        drawDataTable() {
            $('#quotesTbl').dataTable({
                processing: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.job_valuations.get_quotes") }}',
                    type: 'post',
                    data: {valuation_status: $('#valuation_status').val()},
                },
                columns: [
                    {data: 'checkbox',  searchable: false,  sortable: false},
                    ...[
                        'tid', 'customer', 'notes', 'total', 'verified_total', 'verified_total', 'verified_total', 
                    ].map(v => ({data: v, name: v})),
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [4, 5] },
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: [ 'csv', 'excel', 'print']
            });
        }
    };

    $(Index.init());
</script>
@endsection