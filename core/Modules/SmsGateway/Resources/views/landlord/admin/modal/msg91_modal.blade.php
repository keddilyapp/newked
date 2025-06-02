<div class="modal fade" tabindex="-1" id="msg91_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize">{{__("MSG91")}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route(route_prefix().'admin.sms.settings')}}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="sms_gateway_name" value="msg91">
                <div class="card-body">
                    <!--otp env settings -->
                    <h5 class="mb-4">{{ __('Configure MSG91 credentials') }}</h5>

                    <div class="form-group">
                        <label for="MSG91_AUTH_TOKEN"><strong>{{__('MSG91 Auth Key')}} <span class="text-danger">*</span></strong></label>
                        <input type="text"  class="form-control" name="msg91_auth_key" value=""
                               placeholder="{{ __('MSG91 Auth Key')}}">
                    </div>

                    <div class="form-group">
                        <label for="MSG91_OTP_TEMPLATE_ID"><strong>{{__('OTP Template ID')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text" class="form-control" name="msg91_otp_template_id" value=""
                               placeholder="{{ __('OTP Template ID')}}">
                    </div>

                    <div class="form-group">
                        <label for="MSG91_NOTIFY_TEMPLATE_ID"><strong>{{__('Notify User Register Template ID')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text" class="form-control" name="msg91_notify_user_register_template_id" value=""
                               placeholder="{{ __('Notify User Register Template ID')}}">
                    </div>

                    <div class="form-group">
                        <label for="MSG91_NOTIFY_TEMPLATE_ID"><strong>{{__('Notify Admin Register Template ID')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text" class="form-control" name="msg91_notify_admin_register_template_id" value=""
                               placeholder="{{ __('Notify Admin Register Template ID')}}">
                    </div>

                    <div class="form-group">
                        <label for="MSG91_NOTIFY_TEMPLATE_ID"><strong>{{__('Notify User Order Template ID')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text" class="form-control" name="msg91_notify_user_order_template_id" value=""
                               placeholder="{{ __('Notify User Order Template ID')}}">
                    </div>

                    <div class="form-group">
                        <label for="MSG91_NOTIFY_TEMPLATE_ID"><strong>{{__('Notify Admin Order Template ID')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text" class="form-control" name="msg91_notify_admin_order_template_id" value=""
                               placeholder="{{ __('Notify Admin Order Template ID')}}">
                    </div>

                    <div class="form-group">
                        <label for="disable_user_otp_verify"><strong>{{__('OTP Expire Time Add')}}</strong></label>
                        <select name="user_otp_expire_time" class="form-control">
                            <option  value="30">{{__('30 Second')}}</option>
                            @for($i=1; $i<=5; $i=$i+0.5)
                                <option value="{{$i}}">{{__($i . ($i > 1 ? ' Minutes' : ' Minute'))}}</option>
                            @endfor
                        </select>
                        <p class="form-text text-muted mt-2">{{__('User OTP verify Expire Time Add.')}}</p>
                    </div>

                    <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
