@extends('tenant.admin.admin-master')
@section('title')
    {{ __('WooCommerce Import Settings') }}
@endsection

@section('style')
    <style>
        .upcoming-overlay {
            position: relative;
        }
        .upcoming-overlay:before {
            content: "";
            position: absolute;
            height: 100%;
            width: 100%;
            left: 0;
            top: 0;
            z-index: 1;
            background: rgba(255, 255, 255, .5);
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-recent-order">
        <div class="row">
            <x-flash-msg/>
            <div class="col-md-12">
                <div class="recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="header-wrap">
                        <h4 class="header-title mb-2">{{__("WooCommerce Import Settings")}}</h4>
                        <p>{{__("These fields will automatically add the default value while importing the data from the WooCommerce")}}</p>
                    </div>

                    <div class="plugin-grid">
                        <x-error-msg/>
                        <div class="card">
                            <div class="card-body">
                                <form action="{{route('tenant.admin.woocommerce.settings.import')}}" method="POST">
                                    @csrf

                                    @php
                                        $units = [
                                            1 => __('Kg'),
                                            2 => __('Lb'),
                                            3 => __('Dozen'),
                                            4 => __('Ltr'),
                                            5 => __('g'),
                                            6 => __('Piece')
                                        ];
                                    @endphp
                                    <div class="form-group">
                                        <label for="woocommerce-default-unit">{{__('Product Unit')}}</label>
                                        <select name="woocommerce_default_unit"
                                                id="woocommerce-default-unit" class="form-control">
                                            <option value="">Select Unit</option>
                                            @foreach($units as $index => $value)
                                                <option
                                                    value="{{$index}}" {{$index == get_static_option('woocommerce_default_unit') ? 'selected' : ''}}>{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label
                                            for="woocommerce-default-uom">{{__('Product Unit of measurement (UOM)')}}</label>
                                        <input type="text" class="form-control" id="woocommerce-default-uom"
                                               name="woocommerce_default_uom"
                                               value="{{get_static_option('woocommerce_default_uom')}}"
                                               placeholder="{{__('Ep, 1')}}">
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

    <div class="dashboard-recent-order mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="recent-order-wrapper upcoming-overlay dashboard-table bg-white padding-30">
                    <div class="header-wrap">
                        <h2 class="text-primary">{{__('Coming Soon')}}</h2>
                        <h4 class="header-title mb-2">{{__("CSV Import/Export Settings")}}</h4>
                        <p>{{__("These fields will automatically add the default value while importing the data from the CSV")}}</p>
                    </div>

                    <div class="plugin-grid">
                        <x-error-msg/>
                        <div class="card">
                            <div class="card-body">
                                <form>
                                    @csrf
                                    <div class="form-group">
                                        <label for="woocommerce-default-unit">{{__('Product Unit')}}</label>
                                        <select id="woocommerce-default-unit" class="form-control" disabled>
                                            <option value="">Select Unit</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label
                                            for="woocommerce-default-uom">{{__('Product Unit of measurement (UOM)')}}</label>
                                        <input type="text" class="form-control" id="woocommerce-default-uom"
                                               placeholder="{{__('Ep, 1')}}" disabled>
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
