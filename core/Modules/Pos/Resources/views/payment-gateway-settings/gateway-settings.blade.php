@extends('tenant.admin.admin-master')
@section('title')
    {{__('Payment Settings')}}
@endsection
@section('style')
    <x-media-upload.css/>
@endsection

@php
    $e_wallet_credential = json_decode(get_static_option("pos_e_wallet_credential"));
    $e_wallet_images = \App\Models\MediaUploader::whereIn("id",$e_wallet_credential?->e_wallet_image ?? [])->get();
@endphp

@section('content')
    <div class="col-lg-12 col-ml-12">
        <div class="row">
            <div class="col-12">
                <x-flash-msg/>
                <x-error-msg/>

                <div class="dashboard__card card mb-4">
                    <div class="dashboard__card__header card-header">
                        <h4 class="dashboard__card__title card-title">{{__("Default Tax")}}</h4>
                        <div class="dashboard__card__body card-body custom__form mt-4">
                            <form action="{{  route('tenant.admin.pos.tax-settings') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12 d-flex gap-5">
                                        <div class="form-group w-25">
                                            <label for="country">{{__('Country')}}</label>
                                            <select class="form-control" name="country" id="country">
                                                <option value="">{{__('Select country')}}</option>
                                                @foreach($countries ?? [] as $country)
                                                    <option value="{{$country->id}}" @selected($country->id == get_static_option('pos_tax_country'))>{{$country->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group w-25">
                                            <label for="state">{{__('State')}}</label>
                                            <select class="form-control" name="state" id="state">
                                                @foreach($states ?? [] as $country)
                                                    <option value="{{$country->id}}" @selected($country->id == get_static_option('pos_tax_state'))>{{$country->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group w-25">
                                            <label for="city">{{__('City')}}</label>
                                            <select class="form-control" name="city" id="city">
                                                @foreach($cities ?? [] as $country)
                                                    <option value="{{$country->id}}" @selected($country->id == get_static_option('pos_tax_city'))>{{$country->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group w-25">
                                            <label for="postal_code">{{__('Postal Code')}}</label>
                                            <input class="form-control" name="postal_code" id="postal_code" placeholder="Postal Code"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-none tax_class_row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="tax_class">{{__('Tax Class')}}</label>
                                            <select class="form-control" name="tax_class" id="tax_class">

                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="btn-wrapper mt-4">
                                    <button type="submit" class="btn btn-primary cmn_btn btn_bg_profile">{{__('Update Changes')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="dashboard__card card">
                    <div class="dashboard__card__header card-header">
                        <h4 class="dashboard__card__title card-title">{{__("Payment Gateway Settings")}}</h4>
                    </div>
                    <div class="dashboard__card__body card-body custom__form mt-4">
                        <form action="{{  route('tenant.admin.pos.payment-gateway-settings') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                        <div class="card p-0">
                                            <div class="card-header p-0 border-0">
                                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="cash-tab" data-bs-toggle="tab" data-bs-target="#cash" type="button" role="tab" aria-controls="cash" aria-selected="true">Cash</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="card-tab" data-bs-toggle="tab" data-bs-target="#card" type="button" role="tab" aria-controls="card" aria-selected="false">Card</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="tab-content" id="myTabContent">
                                            <div class="tab-pane fade show active mt-4" id="cash" role="tabpanel" aria-labelledby="home-tab">
                                                <div class="dashboard__card">
                                                    <div class="dashboard__card__body">
                                                        <div class="form-group">
                                                            <label for="pos_payment_gateway_enable"><strong>{{__('Enable/Disable') }} Cash payment</strong></label>
                                                            <input type="hidden" name="pos_payment_gateway_enable">
                                                            <label class="switch">
                                                                <input type="checkbox" name="pos_payment_gateway_enable"  @if(get_static_option('pos_payment_gateway_enable') == 1 ) checked @endif >
                                                                <span class="slider onff"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade mt-4" id="card" role="tabpanel" aria-labelledby="card-tab">
                                                <div class="dashboard__card">
                                                    <div class="dashboard__card__body">
                                                        <div class="form-group">
                                                            <label for="pos_card_payment_gateway_enable"><strong>{{__('Enable/Disable') }} Card payment</strong></label>
                                                            <input type="hidden" name="pos_card_payment_gateway_enable">
                                                            <label class="switch">
                                                                <input type="checkbox" name="pos_card_payment_gateway_enable"  @if(get_static_option('pos_card_payment_gateway_enable') == 1 ) checked @endif >
                                                                <span class="slider onff"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>

                            <div class="btn-wrapper mt-4">
                                <button type="submit" class="btn btn-primary cmn_btn btn_bg_profile">{{__('Update Changes')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-media-upload.markup/>
@endsection
@section('scripts')
    <x-summernote.js/>
    <x-media-upload.js/>
    <script>
        $(document).ready(function () {
            $(document).on('change', 'select[name=country]', function () {
                let country_id = $(this).val();

                if (country_id === "")
                {
                    $("#state").html("");
                    $("#city").html("");
                    toastr.error("Country field is required.");
                    return;
                }

                send_ajax_request("get", '',"{{ route('tenant.admin.tax-module.country.state.info.ajax') }}?id=" + country_id, () => {}, (data) => {
                    $("#city").html("");
                    $("#state").html(data);
                }, (errors) => prepare_errors(errors))
            });

            $(document).on('change', 'select[name=state]', function () {
                let state_id = $(this).val();

                send_ajax_request("get", '',"{{ route('tenant.admin.tax-module.state.city.info.ajax') }}?id=" + state_id, () => {}, (data) => {
                    $("#city").html(data);
                }, (errors) => prepare_errors(errors))
            });
        });

        $(document).on("click", ".add", function (){
            let tr = $(this).closest("tr");


            $(`<tr>` + tr.html() + `</tr>`).insertAfter($(this).closest("tr"));
        });

        $(document).on("click", ".remove", function () {
            $(this).closest("tr").remove()
        });

        (function($){
            "use strict";
            $(document).ready(function ($) {
                $('.summernote').summernote({
                    height: 200,   //set editable area's height
                    codemirror: { // codemirror options
                        theme: 'monokai'
                    },
                    callbacks: {
                        onChange: function(contents, $editable) {
                            $(this).prev('input').val(contents);
                        }
                    }
                });
                if($('.summernote').length > 0){
                    $('.summernote').each(function(index,value){
                        $(this).summernote('code', $(this).data('content'));
                    });
                }
            });
        })(jQuery);
    </script>
@endsection
