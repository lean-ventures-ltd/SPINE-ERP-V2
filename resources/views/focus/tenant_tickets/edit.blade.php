@extends ('core.layouts.app')

@section('title', 'Tenant Tickets | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tenant Ticket Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tenant_tickets.partials.tenant-tickets-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($tenant_ticket, ['route' => array('biller.tenant_tickets.update', $tenant_ticket), 'method' => 'PATCH']) }}
                        @include('focus.tenant_tickets.form')
                        <div class="edit-form-btn ml-2">
                            {{ link_to_route('biller.tenant_tickets.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                        </div> 
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
