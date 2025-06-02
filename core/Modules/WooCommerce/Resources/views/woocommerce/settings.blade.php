@extends('tenant.admin.admin-master')
@section('title')
    {{ __('WooCommerce Settings') }}
@endsection

@section('style')

@endsection

@section('content')
    <div class="dashboard-recent-order">
        <div class="row">
            <x-flash-msg/>
            <div class="col-md-12">
                <div class="recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="header-wrap">
                        <h4 class="header-title mb-2">{{__("WooCommerce Credential Settings")}}</h4>
                        <p>{{__("Setup your woocommerce store credentials from Wordpress")}}</p>
                    </div>

                    <div class="plugin-grid">
                        <x-error-msg/>
                        <div class="card">
                            <div class="card-body">
                                <form action="{{route('tenant.admin.woocommerce.settings')}}" method="POST">
                                    @csrf

                                    <div class="form-group">
                                        <label for="site-url">{{__('WordPress Site URL')}}</label>
                                        <input type="text" class="form-control" id="site-url" name="woocommerce_site_url" value="{{get_static_option('woocommerce_site_url')}}" placeholder="{{__('WordPress Site URL')}}">
                                    </div>

                                    <div class="form-group">
                                        <label for="consumer-key">{{__('Consumer Key')}}</label>
                                        <input type="text" class="form-control" id="consumer-key" name="woocommerce_consumer_key" value="{{get_static_option('woocommerce_consumer_key')}}" placeholder="{{__('Consumer Key')}}">
                                    </div>

                                    <div class="form-group">
                                        <label for="consumer-secret">{{__('Consumer Secret')}}</label>
                                        <input type="text" class="form-control" id="consumer-secret" name="woocommerce_consumer_secret" value="{{get_static_option('woocommerce_consumer_secret')}}" placeholder="{{__('Consumer Secret')}}">
                                    </div>

                                    <div class="form-group text-end">
                                        <button type="submit" class="btn btn-behance">{{__('Update')}}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
