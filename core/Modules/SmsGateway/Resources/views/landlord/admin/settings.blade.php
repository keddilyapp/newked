@extends(route_prefix().'admin.admin-master')

@section('title')
    {{ __('SMS Gateway') }}
@endsection

@section('style')
    <style>
        .plugin-grid {
            display: flex;
            flex-wrap: wrap;
            /*justify-content: space-between;*/
            /*padding: 1em;*/
            gap: 1em;  /* space between grid items */
        }

        .plugin-card {
            width: calc((100% - 2em) / 3);  /* for a three column layout */
            box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2);
            /*padding: 1em;*/
            text-align: center;
        }
        .plugin-card .thumb-bg-color {
            background: #5433FF;  /* fallback for old browsers */
            padding: 40px;
            color: #fff;
        }

        .plugin-card .thumb-bg-color.twilio {
            background: #ED213A;  /* fallback for old browsers */
            background: -webkit-linear-gradient(to right, #93291E, #ED213A);  /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to right, #93291E, #ED213A); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
        }
        .plugin-card .thumb-bg-color.msg91 {
            background: #1488CC;  /* fallback for old browsers */
            background: -webkit-linear-gradient(to right, #2B32B2, #1488CC);  /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to right, #2B32B2, #1488CC); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
        }

        .plugin-card .thumb-bg-color strong {
            font-size: 20px;
            line-height: 26px;
        }

        .plugin-card .thumb-bg-color strong .version {
            font-size: 14px;
            line-height: 18px;
            background-color: #fff;
            padding: 5px 10px;
            display: inline-block;
            color: #333;
            border-radius: 3px;
            margin-top: 15px;
        }

        .plugin-title {
            font-size: 16px;
            font-weight: 500;
            background-color: #03A9F4;
            box-shadow: 0 0 30px 0 rgba(0,0,0,0.2);
            display: inline-block;
            padding: 12px 30px;
            border-radius: 25px;
            color: #fff;
            position: relative;
            margin-top: -20px;
        }
        .plugin-title.externalplugin {
            background-color: #3F51B5;
        }
        .plugin-meta {
            font-size: 0.9em;
            color: #666;
            padding: 20px;
        }
        .padding-30{
            padding: 30px;
        }
        .plugin-card .thumb-bg-color.externalplugin {
            background-color: #FF9800;
        }

        .plugin-card .plugin-meta {
            min-height: 50px;
        }
        .plugin-card .btn-group-wrap {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .plugin-card .btn-group-wrap a {
            display: inline-block;
            padding: 8px 25px;
            background-color: #4b4e5b;
            border-radius: 25px;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            transition: all 300ms;
        }

        .plugin-card .btn-group-wrap a.pl_delete {
            background-color: #e13a3a;
        }
        .plugin-card .btn-group-wrap a:hover{
            opacity: .8;
        }

        #sendra_modal .modal-dialog{
            max-width: 600px;
        }

        #sendra_modal label {
            font-size: 13px;
        }

        /* For large screens and above */
        @media (min-width: 900px) {
            .plugin-card {
                width: calc((100% - 3em) / 3);  /* three columns for large screens */
            }
        }

        /* For medium screens and above */
        @media (max-width: 600px) {
            .plugin-card {
                width: calc((100% - 2em) / 2);  /* two columns for medium screens */
            }
            .plugin-card .btn-group-wrap {
                gap: 5px;
            }
            .plugin-card .btn-group-wrap a {
                padding: 7px 15px;
            }
            .plugin-title {
                font-size: 12px;
                line-height: 16px;
            }
        }
        @media (max-width: 500px) {
            .plugin-card {
                width: calc((100% - 2em) / 1);  /* two columns for medium screens */
            }
            .plugin-title {
                font-size: 16px;
                line-height: 20px;
            }
        }
        .iti{
            width: 100%;
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-recent-order">
        <div class="row">
            <x-flash-msg/>
            <x-error-msg/>
            <div class="col-md-12">
                <div class="p-4 recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="wrapper d-flex justify-content-between">
                        <div class="header-wrap">
                            <h4 class="header-title mb-2">{{__("SMS Gateway Settings")}}</h4>
                            <p>{{__("Manage all sms gateway from here, you can active/deactivate any sms gateway from here.")}}</p>
                        </div>
                        <div class="settings-options justify-content-end">
                            <span data-bs-toggle="modal" data-bs-target="#settings_option_modal">
                                <a href="#" data-bs-toggle="tooltip"  data-bs-placement="top" title="{{__('Configure when SMS will be send')}}" class="modal-btn btn btn-info btn-small settings-option-modal">
                                    {{__('SMS Settings')}}
                                </a>
                            </span>
                            <span data-bs-target="#test_sms_modal" data-bs-toggle="modal">
                                <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Send a test SMS')}}" class="modal-btn btn btn-success btn-small">
                                    {{__('Test SMS')}}
                                </a>
                            </span>
                        </div>
                    </div>

                    <x-fields.switcher label="Enable or disable OTP" name="otp_login_status" value="{{get_static_option('otp_login_status')}}"/>

                    <div class="my-5 plugin-grid" @style(['display: none' => empty(get_static_option('otp_login_status'))])>
                        @foreach(\Modules\SmsGateway\Http\Services\OtpTraitService::gateways() as $key => $item)
                            @php
                                $sms_gateway = \Modules\SmsGateway\Entities\SmsGateway::where('name', $key)->first();
                                $status = $sms_gateway->status ?? 0;
                                $otp_time = $sms_gateway->otp_expire_time ?? 0;
                                $credentials = $sms_gateway->credentials ?? '{}';

                                $link = \Modules\SmsGateway\Http\Services\OtpTraitService::getLink($key);
                            @endphp

                            <div class="plugin-card">
                                <div class="thumb-bg-color {{$key}} google_analytics">
                                    <strong class="google_analytics text-capitalize">{{$item}}</strong>
                                </div>
                                <p class="plugin-meta">
                                    {{__("You can learn more about it from here,")}}
                                    <a href="{{$link}}" target="_blank">{{__('Link')}}</a>
                                </p>
                                <div class="btn-group-wrap">
                                    <a href="#"
                                       data-option="{{$key}}"
                                       data-status="{{$status}}"
                                       class="pl-btn pl_active_deactive {{$status ? 'bg-success' : ''}}">{{$status ? __('Activated') : __('Deactivated')}}</a>

                                    <a href="#" data-bs-target="#{{$key}}_modal" data-bs-toggle="modal"
                                       data-option="{{$key}}"
                                       data-otp-time="{{$otp_time}}"
                                       data-credentials="{{$credentials}}"
                                       class="pl-btn pl_delete pl_settings">{{__("Settings") }}</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('smsgateway::landlord.admin.modal.twilio_modal')
    @include('smsgateway::landlord.admin.modal.msg91_modal')
    @include('smsgateway::landlord.admin.modal.sendra_modal')

    <div class="modal fade" tabindex="-1" id="settings_option_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-capitalize">{{__("SMS Settings")}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{route(route_prefix().'admin.sms.options')}}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        <h5 class="mb-4">{{ __('Receive sms when the actions are triggered') }}</h5>

                        <x-fields.switcher label="When new user is registered - for admin" name="new_user_admin" value="{{get_static_option('new_user_admin')}}"/>
                        <x-fields.switcher label="When new user is registered - for user" name="new_user_user" value="{{get_static_option('new_user_user')}}"/>

                        @if(!tenant())
                            <x-fields.switcher label="When new tenant/shop is created - for admin" name="new_tenant_admin" value="{{get_static_option('new_tenant_admin')}}"/>
                            <x-fields.switcher label="When new tenant/shop is created - for user" name="new_tenant_user" value="{{get_static_option('new_tenant_user')}}"/>
                        @endif

                        @tenant
                            <x-fields.switcher label="When new order is placed - for admin" name="new_order_admin" value="{{get_static_option('new_order_admin')}}"/>
                            <x-fields.switcher label="When new order is placed - for user" name="new_order_user" value="{{get_static_option('new_order_user')}}"/>
                        @endtenant

                        <div class="form-group">
                            <label for="TWILIO_AUTH_TOKEN"><strong>{{__('Set a receiving phone number')}} <span class="text-danger">*</span></strong></label>
                            <input type="tel"  class="form-control" name="receiving_phone_number" value="{{get_static_option('receiving_phone_number')}}"
                                   placeholder="{{ __('Send test sms')}}" id="set-telephone">
                        </div>

                        <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="test_sms_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-capitalize">{{__("Send Test SMS")}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{route(route_prefix().'admin.sms.test')}}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        <div class="form-group">
                            <label for="TWILIO_AUTH_TOKEN"><strong>{{__('Phone number')}} <span class="text-danger">*</span></strong></label>
                            <input type="tel"  class="form-control" name="test_phone_number" value=""
                                   placeholder="{{ __('Send test sms')}}" id="telephone">
                        </div>

                        <button type="submit" id="test-sms-btn" class="btn btn-primary mt-4 pr-4 pl-4" disabled>{{__('Send')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function ($) {
            "use strict";

            $(document).on('change', 'input[name=otp_login_status]', function (e) {
                Swal.fire({
                    title: '{{__("Are you sure?")}}',
                    text: '{{__("You will able revert your decision anytime")}}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1F51FF',
                    cancelButtonColor: '#D2042D',
                    confirmButtonText: "{{__('Yes!')}}",
                    cancelButtonText: "{{__('Cancel')}}",

                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.get("{{route(route_prefix()."admin.sms.login.otp.status")}}")
                            .then((response) => {
                                if (response.data.type === 'success') {
                                    toastr.success(`{{__('Settings updated')}}`);
                                    let plugin_grid = $('.plugin-grid');
                                    plugin_grid.toggle();
                                }
                            });
                    } else {
                        location.reload();
                    }
                });
            });

            $(document).on('click', '.pl_settings', function (e) {
                e.preventDefault();

                let el = $(this);
                let option = el.attr('data-option');
                let otp_expire_time = el.attr('data-otp-time');
                let credentials = el.attr('data-credentials');
                credentials = jQuery.parseJSON(credentials);

                let modal = $(`#${option}_modal`);
                for (let item in credentials)
                {
                    modal.find(`input[name=${item}]`).val(credentials[item]);
                }
                modal.find(`select[name=user_otp_expire_time] option[value=${otp_expire_time}]`).attr('selected', true)
            });

            $(document).on("click", '.pl_active_deactive', function (e) {
                e.preventDefault();
                var el = $(this);
                Swal.fire({
                    title: '{{__("Are you sure?")}}',
                    text: '{{__("you will able revert your decision anytime")}}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1F51FF',
                    cancelButtonColor: '#D2042D',
                    confirmButtonText: "{{__('Yes!')}}",
                    cancelButtonText: "{{__('Cancel')}}",

                }).then((result) => {
                    if (result.isConfirmed) {
                        //todo: ajax call
                        let optionName = el.data('option');
                        let status = el.data('status');

                        axios.post("{{route(route_prefix()."admin.sms.status")}}", {
                            option_name: optionName,
                            status: status
                        })
                            .then((response) => {
                                if (response.data.type === 'success') {
                                    location.reload();
                                }
                            });
                    }
                });
            })

            $(document).ready(function () {
                axios.get("{{route(route_prefix()."admin.sms.templates")}}")
                    .then((response) => {
                        console.log(response)

                        if (response.data.type === 'success') {
                            location.reload();
                        }
                    });
            });

            const template_id_lang = [
                'sendra_otp_template',
                'sendra_notify_user_register_template',
                'sendra_notify_admin_register_template',
                'sendra_notify_user_order_template',
                'sendra_notify_user_order_template',
                'sendra_notify_user_order_template',
                'sendra_notify_admin_order_template'
            ];

            template_id_lang.forEach(function(template) {
                $(document).on('change', `#${template}_id`, function () {
                    let lang = $(this).find('option:selected').attr('data-lang');
                    $(`input[name=${template}_language]`).val(lang);
                });
            });
        })(jQuery);
    </script>

    <x-custom-js.phone-number-config selector="#telephone" submit-button-id="test-sms-btn" key="1"/>
    <x-custom-js.phone-number-config selector="#set-telephone" submit-button-id="test-sms-btn" key="2"/>

    <script>
        $(document).ready(function () {
            setTimeout(() => {
                $('#set-telephone').val(`{{get_static_option('receiving_phone_number')}}`);
            }, 1000);
        });
    </script>
@endsection
