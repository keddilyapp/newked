<div class="topbar-area">
    <div class="container-max">
        <div class="row">
            <div class="col-lg-12">
                <div class="topbar-inner">
                    <div class="left-content">
                      <div class="language_dropdown @if(get_user_lang_direction() == 'rtl') ml-1 @else mr-1 @endif d-none" id="languages_selector">
                                @if (auth()->check())
                                    @php
                                        $route = auth()->guest() == 'admin' ? route('tenant.admin.dashboard') : route('tenant.user.home');
                                    @endphp
                                    <div class="selected-language">{{ __('Account') }}<i class="fas fa-caret-down"></i></div>
                                    <ul>
                                        <li class="listItem"><a href="{{ $route }}">{{ __('Dashboard') }}</a>
                                        <li class="listItem"><a href="{{ route('tenant.user.logout') }}">{{ __('Logout') }}</a></li>
                                    </ul>
                                @else
                                    <div class="selected-language">{{ __('Login') }}<i class="fas fa-caret-down"></i></div>
                                    <ul>
                                        <li class="listItem"><a class="listItem" href="{{ route('tenant.user.login') }}">{{ __('Login') }}</a>
                                        <li class="listItem"><a class="listItem" href="{{ route('tenant.user.register') }}">{{ __('Register') }}</a>
                                    </ul>
                                @endif
                            </div>
                            @if(get_static_option('landlord_frontend_language_show_hide'))
                                <!-- Select  -->
                                <div class="select-language">
                                    <select class="niceSelect tenant_languages_selector">
                                        @foreach(\App\Facades\GlobalLanguage::all_languages(\App\Enums\StatusEnums::PUBLISH) as $lang)
                                            @php
                                                $exploded = explode('(',$lang->name);
                                            @endphp
                                            <option class="lang_item" value="{{$lang->slug}}" >{{current($exploded)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                        <ul class="social-icon">
                            <li><a href="{{get_static_option('topbar_twitter_url')}}"><i class="lab la-twitter"></i></a></li>
                            <li><a href="{{get_static_option('topbar_linkedin_url')}}"><i class="lab la-linkedin-in"></i></a></li>
                            <li><a href="{{get_static_option('topbar_facebook_url')}}"><i class="lab la-facebook-f"></i></a></li>
                            <li><a href="{{get_static_option('topbar_youtube_url')}}"><i class="lab la-youtube"></i></a></li>
                        </ul>
                    </div>

                    <div class="right-content">
                        @if(!empty(get_static_option('landlord_frontend_language_show_hide')))
                           <x-language-change/>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>