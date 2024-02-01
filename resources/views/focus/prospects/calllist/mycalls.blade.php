@extends ('core.layouts.app')

@section('title', 'My Today Calls')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">My Today Calls</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-auto float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.prospects.partials.prospects-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">


                    <div class="card">
                        <div class="card-body">
                            <input type="hidden" name="list_id" id="list_id" value="">
                            <div class="col-4">
                                <label for="client">All CallLists</label>
                                <select name="calllist_id" class="custom-select" id="calllist_id"
                                    data-placeholder="Choose CallList">
                                    <option value="0">Choose Call List</option>
                                    @foreach ($calllists as $calllist)
                                        <option value="{{ $calllist->id }}">{{ $calllist->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- <div class="row no-gutters">
                                <div class="col-sm-3 col-md-2 h4">Called</div>
                                <div class="col-sm-2 col-md-1 h4 text-primary font-weight-bold">{{ $called }}</div>
                                <div class="col-sm-12 col-md-1 h4 text-primary font-weight-bold">
                                    {{ numberFormat(div_num($called, $total_prospect) * 100) }}%</div>
                            </div>
                            <div class="row no-gutters">
                                <div class="col-sm-3 col-md-2 h4">Not Called</div>
                                <div class="col-sm-2 col-md-1 h4 text-success font-weight-bold">{{ $not_called }}</div>
                                <div class="col-sm-12 col-md-1 h4 text-success font-weight-bold">
                                    {{ numberFormat(div_num($not_called, $total_prospect) * 100) }}%</div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="mytodaycalllist-table"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Company/Name</th>
                                            <th>Industry</th>
                                            <th>Call</th>
                                            {{-- <th>Email</th> --}}
                                            <th>Phone</th>
                                            <th>Region</th>
                                            <th>Call Status</th>
                                            <th>Call Date</th>

                                            {{-- <th>{{ trans('labels.general.actions') }}</th> --}}
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
    @include('focus.prospects.partials.call_modal')
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };

        const Index = {
            callListId: @json(request('id')),
            init() {
                $.ajaxSetup(config.ajax);
                this.draw_data();
                this.showModal();
                this.dismissModal();
                this.disableSubmitButtons();
                $('#callModal').find('.erp-status').change(this.erpChange);
                $('#callModal').find('.challenges-status').change(this.challengesChange);
                $('#callModal').find('.demo-status').change(this.demoChange);
                $('#callModal').find('.call-status').change(this.callTypeChange);
                $('#calllist_id').change(this.callListChange);

            },

            showModal() {
                $('#mytodaycalllist-table tbody').on('click', '#call', function(e) {
                    var id = $(this).attr('data-id');
                    
                    //show modal
                    $('#callModal').modal('show');


                    //picked
                    $('#picked_prospect_id').val(id);

                    //notpicked

                    $('#notpicked_prospect_id').val(id);

                    //pickedbusy

                    $('#busyprospect_id').val(id);

                    //notavailable
                    $('#notavailable_prospect').val(id);
                    

                    //append response to call history
                    $.ajax({
                        url: "{{ route('biller.prospects.followup') }}",
                        type: 'post',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            $('#remarksTableModal').append(response);
                        }
                    });

                    //append prospect details
                    $.ajax({
                        url: "{{ route('biller.prospects.fetchprospect') }}",
                        type: 'post',
                        data: {
                            id: id
                        },
                        success: function(response) {
                           
                            $('#prospectTableDetails').append(response);
                        }
                    });

                    $('#callModal').on('hidden.bs.modal', function(e) {
                        $("#notes").val('');
                        $("#current_erp_challenges").val('');
                        $('#picked_prospect_id').val('');
                        $('#notpicked_prospect_id').val('');
                        $('#busyprospect_id').val('');
                        $('#notavailable_prospect').val('');
                        $("#save_call_chat").attr("disabled", false);
                        $("#save_reshedule").attr("disabled", false);
                        $("#save_reminder").attr("disabled", false);
                        $("#notavailable").attr("disabled", false);
                        $('#remarks_table').remove();
                        $('#prospect_prospect_table').remove();
                        id = "";
                        saved ? window.location.reload() : null;
                    });
                });
            },

            disableSubmitButtons() {
                $("#picked").submit(function() {
                    $("#save_call_chat").attr("disabled", true);
                    return true;
                });
                $("#pickedbusy").submit(function() {
                    $("#save_reshedule").attr("disabled", true);
                    return true;
                });
                $("#notpicked").submit(function() {
                    $("#save_reminder").attr("disabled", true);
                    return true;
                });
                $("#notavailable").submit(function() {
                    $("#notavailable").attr("disabled", true);
                    return true;
                });
            },
            callListChange() {
                Index.callListId = $(this).val();
                $('#mytodaycalllist-table').DataTable().destroy();
                return Index.draw_data();
            },
            erpChange() {
                if ($(this).val() == 0) {
                    $("#erp_div").css("display", "none");
                } else {
                    $("#erp_div").css("display", "block");
                }
            },
            challengesChange() {
                if ($(this).val() == "0") {
                    $("#erpchallenges").css("display", "none");
                } else {
                    $("#erpchallenges").css("display", "block");
                }
            },
            demoChange() {

                if ($(this).val() == "0") {
                    $("#demo").css("display", "none");
                    $("#notes").val('');
                    $("#demo_date").val('');
                } else {
                    $("#demo").css("display", "");
                }
            },
            callTypeChange() {

                if ($(this).val() == 'picked') {
                    $("#div_picked").css("display", "block");
                    $("#div_notpicked").css("display", "none");
                    $("#div_picked_busy").css("display", "none");
                    $("#div_notpicked_available").css("display", "none");
                } else if ($(this).val() == 'pickedbusy') {
                    $("#div_picked_busy").css("display", "block");
                    $("#div_picked").css("display", "none");
                    $("#div_notpicked").css("display", "none");
                    $("#div_notpicked_available").css("display", "none");
                } else if ($(this).val() == 'notpicked') {
                    $("#div_notpicked").css("display", "block");
                    $("#div_picked").css("display", "none");
                    $("#div_picked_busy").css("display", "none");
                    $("#div_notpicked_available").css("display", "none");
                } else if ($(this).val() == 'notavailable') {
                    $("#div_notpicked_available").css("display", "block");
                    $("#div_notpicked").css("display", "none");
                    $("#div_picked").css("display", "none");
                    $("#div_picked_busy").css("display", "none");

                }


            },

            dismissModal() {

                $('#callModal').on('hidden.bs.modal', function() {
                    $("#notes").val('');
                    $("#current_erp_challenges").val('');
                    $('#picked_prospect_id').val('');
                    $('#notpicked_prospect_id').val('');
                    $('#busyprospect_id').val('');
                    $('#notavailable_prospect').val('');
                    $("#save_call_chat").attr("disabled", false);
                    $("#save_reshedule").attr("disabled", false);
                    $("#save_reminder").attr("disabled", false);
                    $("#notavailable").attr("disabled", false);
                    $('#remarks_table').remove();
                    $('#prospect_prospect_table').remove();
                    id= "";
                    //saved?window.location.reload():null;
                });
            },



            draw_data() {

                $('#mytodaycalllist-table').dataTable({
                    stateSave: true,
                    processing: true,
                    responsive: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.calllists.fetchtodaycalls') }}',
                        type: 'post',
                        data: {
                            id: this.callListId
                        }

                    },
                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },

                        {
                            data: 'company',
                            name: 'company'
                        },
                        {
                            data: 'industry',
                            name: 'industry'
                        },
                        {
                            data: 'call_prospect',
                            name: 'call_prospect'
                        },
                        {
                            data: 'phone',
                            name: 'phone'
                        },

                        {
                            data: 'region',
                            name: 'region'
                        },
                        {
                            data: 'call_status',
                            name: 'call_status'
                        },
                        {
                            data: 'call_date',
                            name: 'call_date'
                        },

                    ],
                    columnDefs: [{
                        type: "custom-date-sort",
                        targets: [8]
                    }],
                    order: [
                        [0, "desc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                });
            },


        };
        $(() => Index.init());
    </script>


@endsection
