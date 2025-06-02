@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('WooCommerce Product Import')}}
@endsection
@section('style')
    <link rel="stylesheet" href="{{global_asset('assets/common/css/loader.css')}}">
    <x-media-upload.css/>
    <x-datatable.css/>
    <x-bulk-action.css/>

    <style>
        .img-wrap img{
            width: 100%;
        }
        .import_btn i{
            font-size: 20px;
        }
        .loader-02 {
            left: 50dvw;
            top: 50dvh;
            position: absolute;
            z-index: 999;
        }
        .loader-text{
            left: 0;
            top: 15dvh;
            position: absolute;
        }
    </style>
@endsection

@section('content')
    <div class="domain-reseller-loader-wrapper">
        <x-loaders.loader-02/>
    </div>

    <div class="col-lg-12 col-ml-12">
        <x-error-msg/>
        <x-flash-msg/>
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="header-wrap d-flex justify-content-between">
                            <div>
                                <h4 class="header-title">{{__('All Available Products')}}</h4>
                                <p class="my-2">{{__('The list shows all the products listed in your wordpress site through woocommerce. Only simple products are available right now. Variable product support will be available very soon.')}}</p>
                            </div>
                            <a class="btn btn-primary btn-sm import_all_btn" data-route="{{route('tenant.admin.woocommerce.import.all')}}">{{__('Import All')}}</a>
                        </div>
                        @can('product-category-delete')
                            <x-woocommerce::bulk-action.dropdown/>
                        @endcan

                        <div class="table-wrap table-responsive">
                            <table class="table table-default">
                                <thead>
                                <x-woocommerce::bulk-action.th/>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Summary')}}</th>
                                <th>{{__('Image')}}</th>
                                </thead>
                                <tbody>

                                @foreach($all_products ?? [] as $key => $product)
                                    @continue(count($product->variations) > 0)
                                    <tr>
                                        <x-woocommerce::bulk-action.td :id="$product->id"/>
                                        <td>{{$product->id}}</td>
                                        <td>
                                            <p>{{$product->name}}</p>
                                            <small>
                                                <a href="{{$product->permalink}}" target="_blank">{{$product->permalink}}</a>
                                            </small>
                                        </td>
                                        <td>
                                            {!! preg_replace("/\r|\n/", "", $product->short_description) !!}
                                        </td>
                                        <td>
                                            @php
                                                $image = current($product->images);
                                                $image_url = !empty($image) ? $image->src : '';
                                            @endphp
                                            <div class="attachment-preview">
                                                <div class="img-wrap">
                                                    <img src="{{ $image_url }}" alt="" loading="lazy">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <x-datatable.js/>
    <x-table.btn.swal.js/>
    <x-woocommerce::bulk-action.js :route="route('tenant.admin.woocommerce.import.selective')"/>

    <script>
        (function ($) {
            "use strict"

            const pageLoader = (type = 'hide') => {
                $('.domain-reseller-loader-wrapper').css('display', type === 'show' ? 'block' : 'none');
            }

            $(document).ready(function () {
                pageLoader('hide');

                $(document).on('click', '.import_all_btn', function () {
                    Swal.fire({
                        title: "{{ __('Do you want to import all products from WooCommerce?') }}",
                        text: '{{__("You would not be able to revert this item!")}}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '{{__('Import')}}',
                        confirmButtonColor: '#00ce90',
                        cancelButtonText: "{{__('Cancel')}}",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            pageLoader('show');
                            $('a').attr('disabled', true);
                            $('a').css('opacity', '0.5');
                            $('button').attr('disabled', true);
                            $('.import_all_btn').removeClass('import_all_btn');

                            let route = $(this).data('route');
                            $.get(route).then(function (data) {
                                if (data) {
                                    pageLoader('hide');
                                    Swal.fire(`{{__('Imported Successfully!')}}`, '', 'success');
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1000);
                                }
                            });
                        }
                    });
                });
            });
        })(jQuery)
    </script>
@endsection
