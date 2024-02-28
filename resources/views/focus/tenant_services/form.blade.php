<div class="card rounded">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-1">
                <div class="col-12">
                    <h6 class="mb-2">Package Info</h6>
                    <div class="row">
                        <div class="col-12">
                            <div class='form-group'>
                                {{ Form::label('package_name', 'Package Name', ['class' => 'col control-label']) }}
                                <div class='col'>
                                    {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Package Name', 'id' => 'name', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class='form-group'>
                                {{ Form::label('cost', 'Package Cost', ['class' => 'col control-label']) }}
                                <div class='col'>
                                    {{ Form::text('cost', null, ['class' => 'form-control box-size', 'placeholder' => 'Package Cost', 'id' => 'cost', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class='form-group'>
                                {{ Form::label('maintenance_cost', 'Maintenance Cost', ['class' => 'col control-label']) }}
                                <div class='col'>
                                    {{ Form::text('maintenance_cost', null, ['class' => 'form-control box-size', 'placeholder' => 'Maintenance Cost', 'id' => 'maintenance_cost', 'required' => 'required']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
</div>  

{{-- select modules --}}
<div class="card rounded">
    <div class="card-content">
        <div class="card-body">
            <h5 class="ml-1">Active Modules</h5>
            <div class="table-responsive">
                <table class="table table-flush-spacing" id="modulesTbl">
                    <tbody>
                        <tr>
                            <td class="text-nowrap fw-bolder">
                                <div class="row">

                                    <div id="available-permissions" class="mt-2"
                                         style="height: 1000px; overflow-x: hidden; overflow-y: scroll;">
                                        <div class="row">
                                            <div class="col-12">
                                                @if ($permissions->count())
                                                    @php
                                                        $groupClassName =  null;
                                                    @endphp


                                                    @foreach ($permissions as $perm)

                                                        @if( strtolower(explode(' ', $perm->display_name)[0]) !== $groupClassName )

                                                            @php
                                                                $groupClassName = strtolower(explode(' ', $perm->display_name)[0]);
                                                            @endphp

                                                            <div class="mt-2">
                                                                <input type="checkbox"
                                                                       id="pg-master-{{strtolower(explode(' ', $perm->display_name)[0])}}"
                                                                       style="width: 20px; height: 20px;"
                                                                       class="round pg-master-{{strtolower(explode(' ', $perm->display_name)[0])}}"
                                                                >
                                                                <label for="pg-master-{{strtolower(explode(' ', $perm->display_name)[0])}}" style="font-size: 22px;">  Grant All '{{ explode(' ', $perm->display_name)[0] }}' Permissions </label>
                                                            </div>

                                                        @endif


                                                        <label class="control--checkbox">
                                                            <input class="icheckbox_square icheckbox_flat-blue pg-child-{{strtolower(explode(' ', $perm->display_name)[0]) }}"
                                                                   type="checkbox"
                                                                   name="permissions[{{ $perm->id }}]"
                                                                   value="{{ $perm->id }}"
                                                                   id="perm_{{ $perm->id }}"
                                                                    {{ is_array(old('permissions')) ? (in_array($perm->id, old('permissions')) ? 'checked' : '') : (in_array($perm->id, $packagePermissions) ? 'checked' : '') }} />
                                                            <label for="perm_{{ $perm->id }}">{{ $perm->display_name }}</label>
                                                            <div class="control__indicator"></div>
                                                        </label>
                                                        <br/>
                                                    @endforeach
                                                @else
                                                    <p>There are no available permissions.</p>
                                                @endif
                                            </div><!--col-lg-6-->
                                        </div><!--row-->
                                    </div><!--available permissions-->


                                </div>
                            </td>    
                        </tr>
                    </tbody>
                </table>        
            </div>
        </div>
    </div>
</div>  

{{-- package extras --}}
<div class="card rounded">
    <div class="card-content">
        <div class="card-body">
            @if (count($package_extras))
                <div class="row mb-2">
                    <div class="col-12">
                        <h6 class="mb-2 ml-1">Package Extras</h6>
                        <div class="row">
                            <div class="col-md-10 ml-auto mr-auto">
                                <div class="table-responsive">
                                    <table class="table table-flush-spacing" id="extrasTbl">
                                        <tbody>
                                            @foreach ($package_extras as $package)
                                                <tr>
                                                    <td class="text-nowrap fw-bolder">{{ $package->name }}</td>
                                                    <td><input type="checkbox" class="form-check-input select" name="package_id[]" value="{{ $package->id }}" {{ $package->checked }}></td>
                                                    <td><input type="text" class="form-control col-10 pb-0 pt-0 extra-cost" placeholder="Package Cost" name="extra_cost[]" value="{{ $package->extra_cost }}"></td>
                                                    <td><input type="text" class="form-control col-10 pb-0 pt-0 maint-cost" placeholder="Maintenance Cost" name="maint_cost[]" value="{{ $package->maint_cost }}"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-12">
                    <h5 class="ml-2 font-weight-bold">Total Cost: <span class="total-cost"></span></h5>
                    {{ Form::hidden('total_cost', null, ['id' => 'total-cost']) }}
                    {{ Form::hidden('extras_total', null, ['id' => 'extras-cost']) }}
                </div>
            </div>
        </div>
    </div>
</div>  
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    $.ajaxSetup(config.ajax);
    $('form').on('keyup', '#cost, #maintenance_cost', function() {
        calcTotals();
    });
    $('#extrasTbl').on('change', '.select', function() {
        calcTotals();
    });

    function calcTotals() {
        const pkgCost = accounting.unformat($('#cost').val()); 
        const maintCost = accounting.unformat($('#maintenance_cost').val()); 
        let extraCost = 0;
        let lineMaintCost = 0;
        $('#extrasTbl .select').each(function() {
            const row = $(this).parents('tr');
            if ($(this).prop('checked')) {
                extraCost += accounting.unformat(row.find('.extra-cost').val()); 
                extraCost += accounting.unformat(row.find('.maint-cost').val()); 
            }
        });
        const total = pkgCost + maintCost + extraCost + lineMaintCost;
        $('.total-cost').text(accounting.formatNumber(total));
        $('#total-cost').val(accounting.formatNumber(total));
        $('#extras-cost').val(accounting.formatNumber(extraCost));
    }
    
    $('form').submit(function(e) {
        $('#extrasTbl .select').each(function() {
            const row = $(this).parents('tr');
            if (!$(this).prop('checked')) row.remove();
        });
    });

    const service = @json(@$tenant_service);
    if (service && service.id) {
        $('#cost').keyup();
        const module_ids = service.module_id? service.module_id.split(',') : [];
        $('#modulesTbl .select').each(function() {
            const id = $(this).attr('id').split('-')[1];
            if (module_ids.includes(id+'')) $(this).prop('checked', true);
        });
    }
</script>
@endsection