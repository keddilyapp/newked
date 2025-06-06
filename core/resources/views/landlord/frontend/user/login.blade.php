@extends('landlord.frontend.frontend-page-master')

@section('title')
    {{__('User Login')}}
@endsection

@section('page-title')
    {{__('User Login')}}
@endsection

@section('content')
    <!-- sign-in area start -->
    <div class="sign-in-area-wrapper padding-top-100 padding-bottom-100 bg-black">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-9 col-lg-7 col-xl-6 col-xxl-5">
                    <div class="sign-in register signIn-signUp-wrapper bg-black">
                        <h4 class="title signin-contents-title">{{__('Sign In')}}</h4>
                        <div class="form-wrapper custom--form mt-5">
                            <x-error-msg/>
                            <x-flash-msg/>
                            <form action="" method="post" enctype="multipart/form-data" class="account-form" id="login_form_order_page">
                                <div class="error-wrap"></div>
                                <div class="form-group single-input">
                                    <label for="exampleInputEmail1" class="label-title mb-3">{{__('Username')}}<span class="required">*</span></label>
                                    <input type="text" name="username" class="form-control form--control" id="exampleInputEmail1" placeholder="{{__('Type your username')}}">
                                </div>
                                <div class="form-group single-input mt-4">
                                    <label for="exampleInputEmail1" class="label-title mb-3">{{__('Password')}}<span class="required">*</span></label>
                                    <input type="password" name="password" class="form-control form--control" id="exampleInputPassword1" placeholder="{{__('Password')}}">
                                </div>

                                <div class="form-group single-input form-check mt-4">
                                    <div class="box-wrap">
                                        <div class="left">
                                            <div class="checkbox-inlines">
                                                <input type="checkbox" name="remember" class="form-check-input check-input" id="exampleCheck1">
                                                <label class="form-check-label checkbox-label" for="exampleCheck1">{{__('Remember me')}}</label>
                                            </div>
                                        </div>
                                        <div class="right forgot-password">
                                            <a href="{{route('tenant.user.forget.password')}}" class="forgot-btn">{{__('Forgot Password?')}}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="btn-wrapper mt-4">
                                    <button type="submit" id="login_btn" class="cmn-btn cmn-btn-bg-1 w-100">{{__('Sign In')}}</button>

                                    @if(moduleExists('SmsGateway') && get_static_option('otp_login_status'))
                                        <p class="font-weight-bold text-center my-2">{{__('Or')}}</p>
                                        <a href="{{route(route_prefix().'user.login.otp')}}" class="cmn-btn cmn-btn-outline-one color-one w-100" style="padding: 10px 25px">{{__('Sign In with OTP')}}</a>
                                    @endif
                                </div>
                            </form>
                            <p class="info mt-3">{{__("Do not have an account")}} <a href="{{route(route_prefix().'user.register')}}" class="active"> <strong>{{__('Sign up')}}</strong> </a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- sign-in area end -->
@endsection
@section('scripts')
   <x-custom-js.ajax-login/>
@endsection