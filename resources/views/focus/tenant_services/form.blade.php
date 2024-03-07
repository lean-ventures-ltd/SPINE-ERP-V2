<div class="card rounded">
    <div class="card-content">
        <div class="card-body">
            <div class="row mb-1">
                <div class="col-12">
                    <h2 class="mb-2">Package Info</h2>
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
                                    {{ Form::number('cost', null, ['class' => 'form-control box-size', 'placeholder' => 'Package Cost', 'id' => 'cost', 'required' => 'required', 'step' => '0.001']) }}
                                </div>
                            </div>
                            <div class='form-group'>
                                {{ Form::label('maintenance_cost', 'Maintenance Cost', ['class' => 'col control-label']) }}
                                <div class='col'>
                                    {{ Form::number('maintenance_cost', null, ['class' => 'form-control box-size', 'placeholder' => 'Maintenance Cost', 'id' => 'maintenance_cost', 'required' => 'required', 'step' => '0.001']) }}
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
        <div class="card-body p-3">
            <h2 class="mb-3">Subscription Packs</h2>
                <div class="row">

                    @foreach($subscriptionTiers as $sT)

                        <div class="form-group">
                            <input type="checkbox"
                                   id="subscription_packs"
                                   style="width: 20px; height: 20px;"
                                   class="round ml-1 mr-1"
                                   name="subscription_packs[]"
                                   value="{{$sT->st_number}}"
                                   @if(!empty(@$tenant_service) && in_array($sT->st_number, @$tenant_service->subscription_packs))
                                       checked
                                   @endif
                            >
                            <label for="subscription_packs" style="font-size: 22px;"> {{ explode('>>>Subscription-Pack<<< ', $sT->related_role->name)[1] }} </label>
                        </div>

                    @endforeach

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