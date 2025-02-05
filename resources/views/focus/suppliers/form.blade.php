<div class="card-content">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                   aria-selected="true">{{trans('customers.billing_address')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab"
                   aria-selected="false">Payment Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3" role="tab"
                   aria-selected="false">Opening Balance</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4" role="tab"
                   aria-selected="false">{{trans('general.other')}}</a>
            </li>
        </ul>

        <div class="tab-content px-1 pt-1">
            <!-- billing address -->
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                <div class='form-group'>
                    {{ Form::label( 'company', trans('customers.company'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('company', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.company')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'name', trans('customers.name'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.name').'*','required'=>'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'phone', trans('customers.phone'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('phone', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.phone').'*','required'=>'required']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'email', trans('customers.email'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::email('email', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.email').'*','required'=>'required']) }}
                    </div>
                </div>
              
                <div class='form-group'>
                    {{ Form::label( 'address', trans('customers.address'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('address', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.address')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'city', trans('customers.city'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('city', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.city')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'region', trans('customers.region'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('region', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.region')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'country', trans('customers.country'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('country', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.country')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'postbox', trans('customers.postbox'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('postbox', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.postbox')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'taxid','VAT NUMBER',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('taxid', null, ['class' => 'form-control box-size', 'placeholder' => 'VAT NUMBER']) }}
                    </div>
                </div>
            </div>

            <!-- payment setting -->
            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">       
                <div class='form-group'>
                    {{ Form::label( 'account_no', 'ACCOUNT NUMBER',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('account_no', null, ['class' => 'form-control box-size', 'placeholder' => 'ACCOUNT NUMBER']) }}
                    </div>
                </div>              
                <div class='form-group'>
                    {{ Form::label( 'account_name', 'PRINT NAME ON CHEQUE AS',['class' => 'col-lg-3 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('account_name', null, ['class' => 'form-control box-size', 'placeholder' => 'CHEQUE NAME (ACCOUNT NAME)']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'bank', 'BANK',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('bank', null, ['class' => 'form-control box-size', 'placeholder' => 'BANK']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'bank_code', 'BANK CODE',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('bank_code', null, ['class' => 'form-control box-size', 'placeholder' => 'BANK CODE']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'payment_terms', 'PAYMENT TERMS',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>                     
                        {{ Form::select('payment_terms',['0'=>'ON RECEIPT','30'=>'AFTER 30 DAYS','45'=>'AFTER 45 DAY','60'=>'AFTER 60 DAY','90'=>'AFTER 90 DAY'], null, ['class' => 'form-control box-size', 'placeholder' => 'PAYMENT TERMS']) }}
                    </div>
                </div>             
                <div class='form-group'>
                    {{ Form::label( 'credit_limit', 'CREDIT LIMIT',['class' => 'col-lg-3 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('credit_limit', null, ['class' => 'form-control box-size', 'id'=>'credit_limit', 'placeholder' => 'CREDIT LIMIT']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'mpesa_payment', 'MPESA PAYMENT OPTIONS',['class' => 'col-lg-3 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('mpesa_payment', null, ['class' => 'form-control box-size', 'placeholder' => 'PAYBILL OR BUYGOODS NUMBER & ACCOUNT NUMBER']) }}
                    </div>
                </div>              
            </div>

            <!-- opening balance -->
            <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                <div class='form-group'>
                    {{ Form::label('balance', 'Opening Balance',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('open_balance', numberFormat(@$supplier->open_balance), ['class' => 'form-control', 'id'=>'open_balance']) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label('date', 'As At Date',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('open_balance_date', null, ['class' => 'form-control datepicker','id'=>'open_balance_date']) }}
                    </div>
                </div>       
                <div class='form-group'>
                    {{ Form::label('note', 'Note',['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('open_balance_note', null, ['class' => 'form-control', 'id' => 'open_balance_note']) }}
                    </div>
                </div>       
                <div class='form-group'>
                    {{ Form::label('expense_account', 'Recognise Expense on Account',['class' => 'col-lg-3 control-label']) }}
                    <div class='col-lg-10'>
                        <select name="expense_account_id" class="custom-select" id="expense_account">
                            <option value="">-- Select Expense Account --</option>
                            @foreach ($accounts as $row) 
                                <option value="{{ $row->id }}" {{ $row->id == @$supplier->expense_account_id? 'selected' : '' }}>
                                    {{ $row->holder }}
                                </option>
                            @endforeach
                        </select>                        
                    </div>
                </div>          
            </div>

            <!-- other details -->
            <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                <div class='form-group'>
                    <label for="contact_person_info" class="col-2">Contact Person Info</label>
                    <div class='col-10'>
                        {{ Form::textarea('contact_person_info', null, ['class' => 'form-control', 'placeholder' => 'Contact Person Info', 'rows' => '5']) }}
                    </div>
                </div>    
                <div class='form-group'>
                    {{ Form::label( 'docid', trans('customers.docid'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('docid', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.docid')]) }}
                    </div>
                </div>
                <div class='form-group'>
                    {{ Form::label( 'custom1', trans('customers.custom1'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-10'>
                        {{ Form::text('custom1', null, ['class' => 'form-control box-size', 'placeholder' => trans('customers.custom1')]) }}
                    </div>
                </div>
                <div class='form-group hide_picture'>
                    {{ Form::label( 'picture', trans('customers.picture'),['class' => 'col-lg-2 control-label']) }}
                    <div class='col-lg-6'>
                        {!! Form::file('picture', array('class'=>'input' )) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    // datepicker
    $('.datepicker').datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());
    
    // default supplier
    const supplier = @json(@$supplier);
    if (supplier) {
        if (supplier.open_balance_date) {
            $('#open_balance_date').datepicker('setDate', new Date(supplier.open_balance_date));
        }
        const balance = parseFloat(supplier.open_balance);
        $('#open_balance').val(accounting.formatNumber(balance));
        $('#expense_account').val(supplier.expense_account_id);
    }

    // on selecting groups
    $("#groups").select2({multiple: true});
    $("#groups").on("select2:select", function (evt) {
        var element = evt.params.data.element;
        var $element = $(element);
        $element.detach();
        $(this).append($element);
        $(this).trigger("change");
    });

    // on changing opening balance
    $("#open_balance").change(function() {
        const value = accounting.unformat($(this).val());
        if (value > 0) $('#expense_account').attr('required', true);
        else $('#expense_account').attr('required', false);
        $(this).val(accounting.formatNumber(value));
    });

    // on chnaging credit limit 
    $("#credit_limit").change(function() {
        const input_val = $(this).val();
        $("#credit_limit").val(accounting.formatNumber(input_val));
    });
</script>
@endsection
