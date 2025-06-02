@include('tenant.admin.partials.header')

@if(get_static_option('tenant_admin_dashboard_theme') == 'metronic')
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid">
            <div class="main-panel main-panel-metronic">
                <div class="content-wrapper content-wrapper-metronic">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
@else
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"> @yield('title') </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                </ol>
            </nav>
        </div>
        @yield('content')
    </div>
@endif

 @include('tenant.admin.partials.footer')
