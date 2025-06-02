@php
    $route_name ='landlord';
@endphp

@extends($route_name.'.admin.admin-master')
@section('title')
    {{__('All Shops')}}
@endsection

@section('style')
    <x-datatable.css/>
    <x-summernote.css/>

    <style>
        a {
            text-decoration: none;
        }
    </style>
@endsection

@section('content')
    <div class="col-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">{{__('All Shops')}} {{$all_tenants ? '('.count($all_tenants).')' : ''}}</h4>

                <x-error-msg/>
                <x-flash-msg/>

                <x-datatable.table>
                    <x-slot name="th">
                        <th>{{__('Shop ID')}}</th>
                        <th>{{__('Details')}}</th>
                        <th>{{__('Shop Address')}}</th>
                        <th>{{__('Browse')}}</th>
                        <th>{{__('Action')}}</th>
                    </x-slot>
                    <x-slot name="tr">
                        @foreach($all_tenants as $tenant)
                            @php
                                $url = '';
                                $central = '.'.env('CENTRAL_DOMAIN');

                                if(!empty($tenant->custom_domain?->custom_domain) && $tenant->custom_domain?->custom_domain_status == 'connected'){
                                    $custom_url = $tenant->custom_domain?->custom_domain ;
                                    $url = tenant_url_with_protocol($custom_url);
                                }else{
                                    $local_url = $tenant->id .$central ;
                                    $url = tenant_url_with_protocol($local_url);
                                }

                                $hash_token = hash_hmac('sha512',$tenant?->user?->username.'_'.$tenant->id, $tenant->unique_key);
                            @endphp

                            <tr>
                                <td>{{$tenant->id}}
                                </td>
                                <td>
                                    <p>{{__('User:').' '}} {{$tenant?->user?->name}}</p>
                                    <p>{{__('Package:').' '}} {{$tenant?->payment_log?->package_name}}</p>

                                    @php
                                        $custom_theme_name = get_static_option_central($tenant->theme_slug."_theme_name") ?? getIndividualThemeDetails($tenant->theme_slug)['name'];
                                    @endphp
                                    <p>{{__('Theme:').' '}} {{$custom_theme_name}}</p>
                                </td>
                                <td>
                                    <a href="{{$url}}" target="_blank">{{str_replace(['https://', 'http://'],'', $url)}}</a>
                                </td>

                                <td>
                                    <a class="badge rounded bg-primary px-4" href="{{$url}}" target="_blank">{{$tenant->id . '.'. env('CENTRAL_DOMAIN')}}</a>
                                    @can('users-direct-login')
                                        <a class="badge rounded bg-danger px-4" href="{{$url.'/token-login/'.$hash_token}}" target="_blank">{{__('Login as Super Admin')}}</a>
                                    @endcan
                                </td>
                                <td>
                                    <x-delete-popover permissions="domain-delete" url="{{route(route_prefix().'admin.tenant.domain.delete', $tenant->id)}}"/>
{{--                                    <x-delete-popover customStrictClass="swal_tenant_delete_button" permissions="domain-delete" url="{{route(route_prefix().'admin.tenant.domain.delete', $tenant->id)}}"/>--}}
                                </td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-datatable.table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <x-datatable.js/>
    <x-summernote.js/>
    <x-custom-js.landloard-unique-subdomain-check :name="'custom_subdomain'"/>
    {{--subdomain check--}}

    <script>
        $('.table-wrap > table').DataTable( {
            "order": [[0, "asc" ]]
        });

        $(document).ready(function () {
            let theme_selected_first = false; // if theme selected first then after domain selection do not change theme again

            $(document).on('change', '#subdomain', function () {
                let el = $(this).parent().parent().find(".form-group #custom-theme");
                let subdomain = $(this).val();

                if (!theme_selected_first) {
                    $.ajax({
                        url: '{{route('landlord.admin.tenant.check.subdomain.theme')}}',
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}',
                            subdomain: subdomain
                        },
                        beforeSend: function () {
                            el.find('option').attr('selected', false);
                        },
                        success: function (res) {
                            el.find("option[value=" + res.theme_slug + "]").attr('selected', true);
                        }
                    });
                }
            });

            {{--$(document).on('click','.swal_tenant_delete_button',function(e){--}}
            {{--    e.preventDefault();--}}
            {{--    Swal.fire({--}}
            {{--        title: '{{__("Are you sure?")}}',--}}
            {{--        text: '{{__("You would not be able to revert this item!")}}',--}}
            {{--        icon: 'warning',--}}
            {{--        showCancelButton: true,--}}
            {{--        confirmButtonColor: '#1F51FF',--}}
            {{--        cancelButtonColor: '#d33',--}}
            {{--        confirmButtonText: "{{__('Yes, Accept it!')}}",--}}
            {{--        cancelButtonText: "{{__('Cancel')}}",--}}

            {{--    }).then((result) => {--}}
            {{--        if (result.isConfirmed) {--}}
            {{--            let el = $(this);--}}
            {{--            el.removeClass('swal_tenant_delete_button btn-danger');--}}
            {{--            el.addClass('btn-secondary');--}}

            {{--            $(this).next().find('.swal_form_submit_btn').trigger('click');--}}
            {{--        }--}}
            {{--    });--}}
            {{--});--}}
        });
    </script>

    <script>
        (function ($) {
            "use strict";

            $(document).ready(function () {
                $('.summernote').summernote({
                    height: 300,   //set editable area's height
                    codemirror: { // codemirror options
                        theme: 'monokai'
                    },
                    callbacks: {
                        onChange: function (contents, $editable) {
                            $(this).prev('input').val(contents);
                        }
                    }
                });
            });
        })(jQuery)
    </script>
@endsection

