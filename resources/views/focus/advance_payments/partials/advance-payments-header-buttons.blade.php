<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route('biller.advance_payments.index') }}" class="btn btn-info  btn-lighten-2">
        <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
    </a>            
    <a href="{{ route('biller.advance_payments.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> {{ trans('general.create') }}
    </a>
</div>