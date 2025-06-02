@extends('tenant.admin.admin-master')

@section('title')
    {{ __('Shipping Plugin Settings') }}
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
            background-color: #007cbd;
            padding: 40px;
            color: #fff;
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

        .plugin-grid .plugin-card .plugin-card .btn-group-wrap a.pl_delete {
            background-color: #e13a3a;
        }
        .plugin-card .btn-group-wrap a:hover{
            opacity: .8;
        }
        .google_analytics{
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            position: relative;

        }
        .logo{
            width: 150px;
            height: auto;
            border: 1px solid #e5b600;
            border-radius: 5px;
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
        .token-alert {
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            padding: 1px;
            background-color: red;
            border-color: red;
            color: #ffffff;
            font-weight: 800;
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
                            <h4 class="header-title mb-2">{{__("Shipping Plugin Settings")}}</h4>
                            <p>{{__("Manage all shipping gateway from here, you can active/deactivate shipping sms gateway from here.")}}</p>
                        </div>
                    </div>

                    <div class="plugin-grid">
                        @foreach($gateways ?? [] as $gateway)
                            @php
                                $status = get_static_option('active_shipping_gateway');

                                if ($gateway['slug'] === 'shiprocket')
                                {
                                        $credentials = [
                                            $gateway['slug']."_api_user_email" => get_static_option($gateway['slug']."_api_user_email"),
                                            $gateway['slug']."_api_user_password" => get_static_option($gateway['slug']."_api_user_password"),
                                            $gateway['slug']."_api_authorization_token" => get_static_option($gateway['slug']."_api_authorization_token"),
                                            $gateway['slug']."_auto_create_order_option" => get_static_option($gateway['slug']."_auto_create_order_option"),
                                            $gateway['slug']."_order_tracking_option" => get_static_option($gateway['slug']."_order_tracking_option")
                                        ];

                                        try {
                                            $list = (new \Modules\ShippingPlugin\Http\Services\Gateways\ShipRocket())->getPickupLocations() ?? [];
                                            $data = ['status' => true,'list' => $list];
                                        } catch (Exception $exception)
                                        {
                                            if ($exception->getCode() === 401)
                                            {
                                                $data = ['status' => false,'message' => __('Token has expired'),'list' => []];
                                            }
                                        }
                                } else {
                                        $credentials = [
                                            $gateway['slug']."_api_key" => get_static_option($gateway['slug']."_api_key"),
                                            $gateway['slug']."_api_secret" => get_static_option($gateway['slug']."_api_secret")
                                        ];
                                }

                                $credentials = json_encode($credentials);
                            @endphp

                            <div class="plugin-card">
                                <div class="thumb-bg-color google_analytics" style="background: url('{{route('tenant.admin.shipping.plugin.logo', $gateway['logo'])}}')">
                                    <strong class="google_analytics font-weight-bolder text-uppercase">{{$gateway['name']}}</strong>

                                    @if(isset($data) && !$data['status'])
                                        <p class="token-alert alert alert-danger">
                                            {{$data['message']}}
                                        </p>
                                    @endif
                                </div>
                                <p class="plugin-meta">
                                    {{__("You can learn more about it from here,")}}
                                    <a href="{{$gateway['reference']}}" target="_blank">{{__('Link')}}</a>
                                </p>

                                <div class="btn-group-wrap">
                                    <a href="#"
                                       data-option="{{$gateway['slug']}}"
                                       class="pl-btn pl_active_deactive {{$status == $gateway['slug'] ? 'bg-success' : 'bg-danger'}}">{{$status == $gateway['slug'] ? __('Activated') : __('Deactivated')}} <x-btn.button-loader class="d-none"/></a>

                                    <a href="#" data-bs-target="#{{$gateway['slug']}}_modal" data-bs-toggle="modal"
                                       data-option="{{$gateway['slug']}}"
                                       data-credentials="{{$credentials}}"
                                       class="pl-btn pl_delete pl_settings">{{__("Settings") }}</a>

                                    @if($gateway['configuration'])
                                        <a href="#" data-bs-target="#{{$gateway['slug']}}_configuration_modal" data-bs-toggle="modal"
                                           class="pl-btn pl_delete pl_settings">{{__("Config") }}</a>
                                    @endif

                                    @if($gateway['info'])
                                        <a href="" class="pl-btn pl_active_deactive bg-primary">{{__('Info')}}</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SETTINGS MODALS -->
    @include('shippingplugin::backend.modals.dhl')
    @include('shippingplugin::backend.modals.shiprocket')
    @include('shippingplugin::backend.modals.shiprocket-config')
@endsection

@section('scripts')
    <script>
        (function ($) {
            "use strict";

            $(document).on('click', '.pl_settings', function (e) {
                e.preventDefault();

                let el = $(this);
                let option = el.attr('data-option');
                let credentials = el.attr('data-credentials');
                credentials = jQuery.parseJSON(credentials);

                let modal = $(`#${option}_modal`);
                for (let item in credentials)
                {
                    modal.find(`input[name=${item}][type=text]`).val(credentials[item]);
                }
            });

            $(document).on('click', '.pl_active_deactive', function (e) {
                e.preventDefault();

                let el = $(this);
                let option = el.attr('data-option');
                el.find('span').toggleClass('d-none');
                el.attr('disabled', true);

                axios.get(`{{route('tenant.admin.shipping.plugin.status.change')}}?option=${option.toLowerCase()}`)
                    .then((response) => {
                        if (response.data.type === 'success') {
                            location.reload();
                        }
                    });
            })

            $(document).on('click', '.modal form button[type=submit]', function () {
                $(this).find('span').toggleClass('d-none');
            });
        })(jQuery)
    </script>
@endsection
