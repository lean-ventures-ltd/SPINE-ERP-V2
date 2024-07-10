<div class="row">
    <div class="col-4 ml-auto mr-auto">
        <!-- Edit -->
        <a href="{{ route('biller.invoices.edit_project_invoice', $invoice) }}" class="btn btn-warning mb-1"><i class="fa fa-pencil"></i> Edit</a>

        <!-- Partial payment -->
        {{-- <a href="#modal_bill_payment_1" data-toggle="modal" data-remote="false" data-type="reminder" class="btn btn-large btn-info mb-1" title="Partial Payment"><span class="fa fa-money"></span> {{trans('general.make_payment')}} </a> --}}

        <!-- Email & Payment reminders-->
        {{-- <div class="btn-group">
            <button type="button" class="btn btn-facebook dropdown-toggle mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="fa fa-envelope-o"></span> {{trans('customers.email')}}
            </button>
            <div class="dropdown-menu"><a href="#sendEmail" data-toggle="modal" data-remote="false" class="dropdown-item send_bill" data-type="1" data-type1="notification">{{trans('general.invoice_notification')}}</a>
                <div class="dropdown-divider"></div>
                <a href="#sendEmail" data-toggle="modal" data-remote="false" class="dropdown-item send_bill" data-type="2" data-type1="reminder">{{trans('general.payment_reminder')}}</a>
                <a href="#sendEmail" data-toggle="modal" data-remote="false" class="dropdown-item send_bill" data-type="3" data-type1="received">{{trans('general.payment_received')}}</a>
                <div class="dropdown-divider"></div>
                <a href="#sendEmail" data-toggle="modal" data-remote="false" class="dropdown-item send_bill" href="#" data-type="4" data-type1="overdue"> {{trans('general.payment_overdue')}}</a><a href="#sendEmail" data-toggle="modal" data-remote="false" class="dropdown-item send_bill" data-type="5" data-type1="refund">{{trans('general.refund_generated')}}</a>
            </div>
        </div> --}}

        <!-- SMS  & Payment reminders-->
        {{-- <div class="btn-group">
            <button type="button" class="btn btn-blue dropdown-toggle mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="fa fa-mobile"></span> {{trans('general.sms')}}
            </button>
            <div class="dropdown-menu"><a href="#sendSMS" data-toggle="modal" data-remote="false" class="dropdown-item send_sms" data-type="11" data-type1="notification">{{trans('general.invoice_notification')}}</a>
                <div class="dropdown-divider"></div>
                <a href="#sendSMS" data-toggle="modal" data-remote="false" class="dropdown-item send_sms" data-type="12" data-type1="reminder">{{trans('general.payment_reminder')}}</a>
                <a href="#sendSMS" data-toggle="modal" data-remote="false" class="dropdown-item send_sms" data-type="13" data-type1="received">{{trans('general.payment_received')}}</a>
                <div class="dropdown-divider"></div>
                <a href="#sendSMS" data-toggle="modal" data-remote="false" class="dropdown-item send_sms" href="#" data-type="14" data-type1="overdue">{{trans('general.payment_overdue')}}</a><a href="#sendSMS" data-toggle="modal" data-remote="false" class="dropdown-item send_sms" data-type="15" data-type1="refund">{{trans('general.refund_generated')}}</a>
            </div>
        </div> --}}
                    
        <a href="#cancel-invoice-modal" class="btn btn-danger mb-1" data-toggle="modal" data-remote="false"><i class="fa fa-minus-circle"> </i> {{trans('general.cancel')}}</a>
        
        <div class="btn-group">
            <button type="button" class="btn btn-vimeo mb-1 btn-md dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-print"></i> {{trans('general.print')}}</button>
            <div class="dropdown-menu">
                <a class="dropdown-item" target="_blank" href="{{$link}}">{{trans('general.pdf_print')}}</a>
                {{-- <a class="dropdown-item" href="{{route('biller.print_compact',[$invoice['id'],1,$valid_token,1])}}">{{trans('general.pos_print')}}</a> --}}
            </div>
        </div>
        @if($invoice['i_class'] > 1)
            <a href="#pop_model_4" data-toggle="modal" data-remote="false" class="btn btn-large btn-blue-grey mb-1" title="Change Status"><span class="fa fa-superscript"></span> {{trans('invoices.subscription')}}</a>
        @endif
    </div>
</div>
@if ($invoice->is_cancelled)
    <div class="row">
        <div class="col-1 ml-auto mr-2">
            <div class="badge text-center white d-block m-1">
                <span class="bg-danger round p-1">Cancelled</span>
            </div>
        </div>
    </div>
@endif
