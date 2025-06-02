@extends('tenant.frontend.frontend-page-master')

@section('title')
    {{__('Order Cancelled for:'.' '.$order_details->name ?? '')}}
@endsection

@section('page-title')
    {{__('Order Cancelled for:'.' '.$order_details->name ?? '')}}
@endsection

@section('content')
    <div class="error-page-content padding-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="order-cancel-area">
                        <h1 class="title text-capitalize">{{get_static_option('site_order_cancel_page_title') ?? __('your order has been canceled')}}</h1>
                        <h3 class="sub-title">
                            @php
                                $subtitle = get_static_option('site_order_cancel_page_subtitle');
                                $subtitle = str_replace('{oid}',$order_details->id, $subtitle);
                            @endphp
                            {{$subtitle}}
                        </h3>
                        <p>
                            {{get_static_option('site_order_cancel_page_description')}}
                        </p>
                        <div class="btn-wrapper text-center my-4">
                            <a href="{{url('/')}}" class="boxed-btn btn btn-primary rounded-0 text-capitalize">{{__('back to home')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
