@extends('tenant.admin.admin-master')
@section('title')
    {{ __('Tenant Dashboard Theme Manage') }}
@endsection

@section('style')
    <link rel="stylesheet" href="{{global_asset('assets/common/css/custom-style.css')}}">

    <style>
        .selected_text {
            top: 0;
            left: 0;
            background-color: #b66dff;
            padding: 15px;
            position: absolute;
            color: white;
            transition: 0.3s;
        }

        .selected_text i {
            font-size: 30px;
        }

        .active_theme {
            background-color: #b66dff;
        }

        .modal-image {
            width: 100%;
        }
    </style>
@endsection
@section('content')
    @php
        $selected_theme = get_static_option('tenant_admin_dashboard_theme') ?? '';
    @endphp
    <div class="dashboard-recent-order">
        <div class="row">
            @foreach($all_themes as $theme)
                <div class="col-xl-3 col-sm-6 theme_status_update_button" data-slug="{{ $theme->slug }}">
                    <form action="{{ route('tenant.admin.dashboard.theme.update',$theme->slug) }}" method="post" id="ThemeActivateDeactivate">
                        @csrf
                        <div class="themePreview" style="background-image: url('{{$theme?->screenshort}}')">
                            <div class="themeLink {{$theme->slug == $selected_theme ? 'active_theme' : ''}}">
                                <h3 class="themeName">{{ $theme->name }}</h3>
                            </div>
                            @if($theme->slug == $selected_theme)
                                <h4 class="selected_text"><i class="las la-check-circle"></i></h4>
                            @endif
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

@endsection
@section('scripts')
    <x-datatable.js/>
    <script>
        $(document).ready(function ($) {
            "use strict";

            $(document).on('click', '.theme_status_update_button', function () {
                let slug = $(this).data('slug');
                let form = $(this).find('form');


                let formAction = form.attr('action').replace(/\/([^\/]+)$/, '/' + slug);
                // Update form action URL
                form.attr('action', formAction);

                // Show confirmation alert
                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to activate this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, activate it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                        Swal.fire({
                            title: "Activated!",
                            text: "Your theme has been activated.",
                            icon: "success"
                        });
                    }
                });
            });

        });
    </script>
@endsection
