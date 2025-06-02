@extends('tenant.admin.admin-master')

@section("title", __("Manage POS"))

@section('pwa-header')
    <meta name="theme-color" content="#6777ef"/>
    <link rel="apple-touch-icon" href="{{ global_asset('assets/pwa/logo.png') }}">
    <link rel="manifest" href="{{ global_asset('assets/pwa/manifest.json') }}">
@endsection

@section('style')
    @if(env("APP_ENV") == 'production')
        {{
            \Illuminate\Support\Facades\Vite::useHotFile(storage_path('vite.hot')) // Customize the "hot" file...
            ->useBuildDirectory('../../_build') // Customize the build directory...
            ->useManifestFilename('manifest.json') // Customize the manifest filename...
            ->withEntryPoints(['resources/css/app.css'])
        }}
    @else
        @vite('resources/css/app.css')
    @endif
@endsection
@section('content')
    <div id="app"></div>
@endsection
@section('scripts')
    @if(env("APP_ENV") == 'production')
        {{
            \Illuminate\Support\Facades\Vite::useHotFile(storage_path('vite.hot')) // Customize the "hot" file...
                ->useBuildDirectory('../../_build') // Customize the build directory...
                ->useManifestFilename('manifest.json') // Customize the manifest filename...
                ->withEntryPoints(['resources/js/app.js'])
        }}
    @else
        @vite('resources/js/app.js')
    @endif
@endsection

@section('pwa-footer')
    <script src="{{ global_asset('assets/pwa/sw.js') }}"></script>
    <script>
        if (!navigator.serviceWorker.controller) {
            navigator.serviceWorker.register(window.appUrl+"/assets/pwa/sw.js").
            then(function (reg) {
                console.log("Service worker has been registered for scope: " + reg.scope);

            });

        }
    </script>
@endsection
