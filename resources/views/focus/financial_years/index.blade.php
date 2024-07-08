@extends ('core.layouts.app')

@section ('title', 'Financial Years')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h2 class=" mb-0">Financial Years </h2>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.financial_years.header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">

                <div class="col-12">

                    <div class="card" style="border-radius: 8px;">

                        <div class="card" style="border-radius: 8px;">

                            <div class="card-content">

                                <div class="card-body">

                                    <table id="financial-years-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    <!-- The Modal -->
                                    <div id="deleteModal" class="modal">
                                        <div class="modal-content">
                                            <p>Are you sure you want to delete this item?</p>
                                            <button id="confirmDelete">Yes, Delete</button>
                                            <button id="cancelDelete">Cancel</button>
                                        </div>
                                    </div>

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
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>
        setTimeout(() => draw_data(), "{{ config('master.delay') }}");

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

        function draw_data() {
            const tableLan = {@lang('datatable.strings')};
            var dataTable = $('#financial-years-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: tableLan,
                ajax: {
                    url: '{{ route("biller.financial_years.index") }}',
                    type: 'GET',
                },
                columns: [
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        sortable: false
                    }
                ],
                order: [
                    [1, "desc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }


    </script>
@endsection

<style>
    /* Styling for the modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.75);
    }

    .radius-8-right {
        border-radius: 0 8px 8px 0;
    }
    .radius-8-left {
        border-radius: 8px 0 0 8px;
    }
    .radius-8 {
        border-radius: 8px;
    }


</style>

