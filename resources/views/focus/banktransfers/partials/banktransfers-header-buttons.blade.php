<div class="btn-group" role="group" aria-label="Basic example">
    <a href="{{ route( 'biller.banktransfers.index' ) }}" class="btn btn-info  btn-lighten-2 round">
        <i class="fa fa-list-alt"></i> {{trans( 'general.list' )}}
    </a>
    @permission('create-money-transfer')
    <a href="{{ route( 'biller.banktransfers.create' ) }}" class="btn btn-pink  btn-lighten-3 round">
        <i class="fa fa-plus-circle"></i> {{trans( 'general.create' )}}
    </a>
    @endauth
</div>
