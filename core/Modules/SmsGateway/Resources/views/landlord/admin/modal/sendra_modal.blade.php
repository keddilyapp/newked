<div class="modal fade" tabindex="-1" id="sendra_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize">{{__("Sendra")}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route(route_prefix().'admin.sms.settings')}}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="sms_gateway_name" value="sendra">
                <div class="card-body">
                    <!--otp env settings -->
                    <h5 class="mb-4">{{ __('Configure Sendra credentials') }}</h5>

                    <div class="form-group">
                        <label for="SENDRA_API_TOKEN"><strong>{{__('Sendra API Token')}} <span
                                        class="text-danger">*</span></strong></label>
                        <input type="text" class="form-control" name="sendra_api_token" value=""
                               placeholder="{{ __('Sendra API Token')}}">
                    </div>

                    @php
                        $saved_gateway = \Modules\SmsGateway\Entities\SmsGateway::active()->where('name', 'sendra')->first();
                        $credentials = json_decode($saved_gateway->credentials ?? '') ?? '';

                        $sendra_otp_template_id = $credentials->sendra_otp_template_id ?? '';
                        $sendra_notify_user_register_template_id = $credentials->sendra_notify_user_register_template_id ?? '';
                        $sendra_notify_admin_register_template_id = $credentials->sendra_notify_admin_register_template_id ?? '';
                        $sendra_notify_user_order_template_id = $credentials->sendra_notify_user_order_template_id ?? '';
                        $sendra_notify_admin_order_template_id = $credentials->sendra_notify_admin_order_template_id ?? '';
                    @endphp

                    <div class="form-group">
                        <label for="SENDRA_OTP_TEMPLATE_ID"><strong>{{__('OTP Template ID')}} <span class="text-danger">*</span>
                            </strong></label>
                        <select class="form-control" name="sendra_otp_template_id" id="sendra_otp_template_id">
                            <option value="">Select any template</option>
                        @foreach($templates['templates'] ?? [] as $template)
                                <option value="{{$template['name']}}"
                                        data-lang="{{$template['language']}}" {{$sendra_otp_template_id === $template['name'] ? 'selected' : ''}}>{{$template['name']}}</option>
                            @endforeach
                        </select>

                        <input type="hidden" name="sendra_otp_template_language" value="">
                    </div>


                    <div class="form-group">
                        <label for="SENDRA_NOTIFY_TEMPLATE_ID"><strong>{{__('Notify User Register Template ID')}} <span
                                        class="text-danger">*</span> </strong></label>
                        <select class="form-control" name="sendra_notify_user_register_template_id"
                                id="sendra_notify_user_register_template_id">
                            <option value="">Select any template</option>
                        @foreach($templates['templates'] ?? [] as $template)
                                <option value="{{$template['name']}}"
                                        data-lang="{{$template['language']}}" {{$sendra_notify_user_register_template_id === $template['name'] ? 'selected' : ''}}>{{$template['name']}}</option>
                            @endforeach
                        </select>

                        <input type="hidden" name="sendra_notify_user_register_template_language" value="">
                    </div>


                    <div class="form-group">
                        <label for="SENDRA_NOTIFY_TEMPLATE_ID"><strong>{{__('Notify Admin Register Template ID')}} <span
                                        class="text-danger">*</span> </strong></label>
                        <select class="form-control" name="sendra_notify_admin_register_template_id"
                                id="sendra_notify_admin_register_template_id">
                            <option value="">Select any template</option>
                        @foreach($templates['templates'] ?? [] as $template)
                                <option value="{{$template['name']}}"
                                        data-lang="{{$template['language']}}" {{$sendra_notify_admin_register_template_id === $template['name'] ? 'selected' : ''}}>{{$template['name']}}</option>
                            @endforeach
                        </select>

                        <input type="hidden" name="sendra_notify_admin_register_template_language" value="">
                    </div>


                    <div class="form-group">
                        <label for="SENDRA_NOTIFY_TEMPLATE_ID"><strong>{{__('Notify User Order Template ID')}} <span
                                        class="text-danger">*</span> </strong></label>
                        <select class="form-control" name="sendra_notify_user_order_template_id"
                                id="sendra_notify_user_order_template_id">
                            <option value="">Select any template</option>
                        @foreach($templates['templates'] ?? [] as $template)
                                <option value="{{$template['name']}}"
                                        data-lang="{{$template['language']}}" {{$sendra_notify_user_order_template_id === $template['name'] ? 'selected' : ''}}>{{$template['name']}}</option>
                            @endforeach
                        </select>

                        <input type="hidden" name="sendra_notify_user_order_template_language" value="">
                    </div>


                    <div class="form-group">
                        <label for="SENDRA_NOTIFY_TEMPLATE_ID"><strong>{{__('Notify Admin Order Template ID')}} <span
                                        class="text-danger">*</span> </strong></label>
                        <select class="form-control" name="sendra_notify_admin_order_template_id"
                                id="sendra_notify_admin_order_template_id">
                            <option value="">Select any template</option>
                        @foreach($templates['templates'] ?? [] as $template)
                                <option value="{{$template['name']}}"
                                        data-lang="{{$template['language']}}" {{$sendra_notify_admin_order_template_id === $template['name'] ? 'selected' : ''}}>{{$template['name']}}</option>
                            @endforeach
                        </select>

                        <input type="hidden" name="sendra_notify_admin_order_template_language" value="">
                    </div>


                    <div class="form-group">
                        <label for="disable_user_otp_verify"><strong>{{__('OTP Expire Time Add')}}</strong></label>
                        <select name="user_otp_expire_time" class="form-control">
                            <option value="30">{{__('30 Second')}}</option>
                            @for($i=1; $i<=5; $i=$i+0.5)
                                <option value="{{$i}}">{{__($i . ($i > 1 ? ' Minutes' : ' Minute'))}}</option>
                            @endfor
                        </select>
                        <p class="form-text text-muted mt-2">{{__('User OTP verify Expire Time Add.')}}</p>
                    </div>

                    <button type="submit" id="update"
                            class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
