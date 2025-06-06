<!doctype html>
<html lang="{{ \App\Facades\GlobalLanguage::default_slug() }}" dir="{{ \App\Facades\GlobalLanguage::custom_default_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- header for PWA -->
    @yield('pwa-header')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @if(!request()->routeIs('tenant.home'))
            @yield('title')  -
        @endif
        {{get_static_option('site_title',__('Xgenious'))}}
        @if(!empty(get_static_option('site_tag_line')))
            - {{get_static_option('site_tag_line')}}
        @endif
    </title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    {!! render_favicon_by_id(get_static_option('site_favicon')) !!}

    {!! \App\Services\AdminTheme\MetaDataHelpers::Init()->getRenderableCoreStyles() !!}


{{--    <link href="{{ global_asset('assets/landlord/admin/css/materialdesignicons.min.css') }}" rel="stylesheet">--}}
{{--    <link href="{{ global_asset('assets/landlord/admin/css/vendor.bundle.base.css') }}" rel="stylesheet">--}}
{{--    <link href="{{ global_asset('assets/landlord/admin/css/style.css') }}" rel="stylesheet">--}}
{{--    <link href="{{ global_asset('assets/common/css/select2.min.css') }}" rel="stylesheet">--}}
{{--    <link href="{{ global_asset('assets/common/css/flatpickr.min.css') }}" rel="stylesheet">--}}
{{--    <link href="{{ global_asset('assets/common/css/toastr.css') }}" rel="stylesheet">--}}
{{--    <link rel="stylesheet" href="{{global_asset('assets/tenant/frontend/css/flaticon.css')}}">--}}
{{--    <link rel="stylesheet" href="{{global_asset('assets/landlord/frontend/css/line-awesome.min.css')}}">--}}

{{--    <link rel="stylesheet" href="{{global_asset('assets/tenant/backend/css/module-fix-style.css')}}">--}}

    <!-- dark mode css  -->
{{--    @if(!empty(get_static_option('dark_mode_for_admin_panel')))--}}
{{--        <link href="{{ global_asset('assets/landlord/admin/css/dark-mode.css') }}" rel="stylesheet">--}}
{{--    @endif--}}

{{--    @if(\App\Enums\LanguageEnums::getdirection(get_user_lang_direction()) === 'rtl')--}}
{{--        <link href="{{ global_asset('assets/landlord/admin/css/rtl.css') }}" rel="stylesheet">--}}
{{--    @endif--}}

    @yield('style')

    {!! \App\Services\AdminTheme\MetaDataHelpers::Init()->getThemesStyles(get_static_option('tenant_admin_dashboard_theme'),'header') !!}
    {{-- /* Load stylesheet from admin theme */  --}}

</head>
<body>
<div class="container-scroller">

    @if(get_static_option('tenant_admin_dashboard_theme') == 'metronic')
        @include('admin-themes.metronic.views.partials.topbar')
        <div class="page-body-wrapper page-body-wrapper-metronic">
        @include('admin-themes.metronic.views.partials.sidebar')

    @else
        {{-- /* enable/disable topbar */  --}}
        @include('tenant.admin.partials.topbar')
        <div class="container-fluid page-body-wrapper">
        {{-- /* enable/disable partial */  --}}
        @include('tenant.admin.partials.sidebar')
    @endif

