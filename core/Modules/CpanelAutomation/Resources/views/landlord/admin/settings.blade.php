@extends(route_prefix().'admin.admin-master')

@section('title')
    {{ __('Cpanel Automation') }}
@endsection

@section('style')

@endsection

@section('content')
    <div class="dashboard-recent-order">
        <div class="row">
            <x-flash-msg/>
            <x-error-msg/>
            <div class="col-md-12">
                <div class="p-4 recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="wrapper d-flex justify-content-between">
                        <div class="header-wrap">
                            <h4 class="header-title mb-2">{{__("Cpanel Automation Settings")}}</h4>
                            <p class="text-sm">{{__("If you are using shared hosting and Cpanel, you can automate user website database creation for you. no more manual database creation.")}}</p>
                        </div>
                        <div class="settings-options justify-content-end">
                            <form action="{{route('landlord.admin.cpanel.automation.database.create.test')}}" method="post">
                                @csrf
                                <button type="submit"  class="btn btn-success btn-small">
                                    {{__('Create Test Database')}}
                                </button>
                            </form>
                        </div>
                    </div>

                    <form action="{{route('landlord.admin.cpanel.automation.settings')}}" method="post">
                        @csrf
                        <x-fields.switcher label="Enable or disable" name="_cpanel_automation_status" value="{{!empty($settings['_cpanel_automation_status']) && $settings['_cpanel_automation_status'] === 'on'}}"/>
                        <x-fields.input label="Cpanel Url" name="_cpanel_url" info="example: https://yourdomain.com:2083" value="{{$settings['_cpanel_url'] ?? ''}}"/>
                        <x-fields.input label="Cpanel Username" name="_cpanel_username" value="{{$settings['_cpanel_username'] ?? ''}}" />
                        <x-fields.input label="Cpanel Token" type="text" name="_cpanel_access_token" value="{{!empty($settings['_cpanel_access_token']) ? $settings['_cpanel_access_token'] : ''}}" info="Do not share this token with anyone"/>
                        <x-button type="submit">{{__('Save Changes')}}</x-button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        (function ($) {
            "use strict";


        })(jQuery);
    </script>

@endsection
