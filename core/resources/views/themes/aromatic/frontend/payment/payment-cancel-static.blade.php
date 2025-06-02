@extends('tenant.frontend.frontend-page-master')
@section('title')
    {{__('Order Cancelled')}}
@endsection
@section('content')
    <div class="error-page-content padding-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="order-cancel-area">
                         <div class="alert alert-warning">
                              <h5 class="title text-capitalize">{{ __('your order has been canceled') }}</h5>
                         </div>
                        <div class="btn-wrapper text-center mt-5">
                            <a href="{{url('/')}}" class="boxed-btn btn btn-primary text-capitalize">{{__('back to home')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
