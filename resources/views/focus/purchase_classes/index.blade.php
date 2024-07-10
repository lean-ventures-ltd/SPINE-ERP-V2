@extends ('core.layouts.app')

@section ('title', 'Purchase Classes')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Purchase Classes </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchase_classes.partials.header-buttons')
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

                            <div class="row mt-2 mb-1">

                                <div class="col-8 col-lg-6">
                                    <label for="financial_year_id" >Filter by Financial Year</label>
                                    <select class="form-control box-size mb-2" id="financial_year_id" name="financial_year_id" required>

                                        <option value=""> Select a Financial Year </option>

                                        @foreach ($financialYears as $fY)
                                            <option value="{{ $fY['id'] }}">
                                                {{ $fY['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                                <table id="purchase-class-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Budget</th>
                                        <th>Financial Year</th>
                                        <th>Action</th>
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
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}

<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    $('#financial_year_id').select2();

    function draw_data() {
        const tableLan = {@lang('datatable.strings')};

        var dataTable = $('#purchase-class-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.purchase-classes.index") }}',
                type: 'GET',
                data: {
                    financial_year: $('#financial_year_id').val(),
                }
            },
            columns: [
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'budget',
                    name: 'budget'
                },
                {
                    data: 'financial_year_id',
                    name: 'financial_year_id'
                },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [0, "asc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    $('#financial_year_id').change( () => {
        $('#purchase-class-table').DataTable().destroy();
        draw_data();
    })

</script>
@endsection