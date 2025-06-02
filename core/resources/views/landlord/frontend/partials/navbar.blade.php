<header class="header-style-01">
  
    <!-- Menu area Starts -->
    <nav class="navbar navbar-area nav-absolute navbar-expand-lg">
        <div class="container nav-container">
            <div class="responsive-mobile-menu">
                <div class="logo-wrapper">
                    <a href="{{url('/')}}" class="logo">
                        @if(!empty(get_static_option('site_logo')))
                            {!! render_image_markup_by_attachment_id(get_static_option('site_logo')) !!}
                        @else
                            <h2 class="site-title">{{get_static_option('site_'.get_user_lang().'_title')}}</h2>
                        @endif
                    </a>
                </div>
                <a href="javascript:void(0)" class="click-nav-right-icon">
                    <i class="las la-user-circle"></i>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#multi_tenancy_menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="navbar-inner-all">
                <div class="collapse navbar-collapse" id="multi_tenancy_menu">
                    <ul class="navbar-nav">
                        {!! render_frontend_menu($primary_menu) !!}
                    </ul>
                </div>
                <div class="navbar-right-content show-nav-content">
                    <div class="single-right-content">
                        @if( Auth::guard('web')->check())
                            <div class="btn-wrapper">
                                @php
                                    $route = auth()->guest() == 'admin' ? route('landlord.admin.dashboard') : route('landlord.user.home');
                                @endphp
                                    <a class="cmn-btn cmn-btn-bg-1" href="{{$route}}">{{ get_static_option('default_dashboard_text') ?? __('Dashboard') }}  </a>
                                    <a class="cmn-btn cmn-btn-bg-1" href="{{route('landlord.user.logout') }}">{{ get_static_option('default_logout_text') ?? __('Logout') }}</a>
                            </div>
                                        <div class="language-switcher-wrapper ms-2">
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle border-dark" 
                style=" background-color:var(--main-color-one); color: #fff; line-height: 22px; padding: 10px 25px;font-size: 16px;font-weight: 500; font-family: var(--body-font); display: inline-block; border-radius: 5px; text-align: center; cursor: pointer; line-height: 34px; padding: 10px 10px;"
                 type="button"
                id="languageDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"
        >
            <i class="las la-globe text-black"></i>
            <span class="visually-hidden">{{ \App\Facades\GlobalLanguage::user_lang_slug() ?? __('Language') }}</span>
            {{ \App\Facades\GlobalLanguage::user_lang_slug() ?? 'Language' }}
        </button>
        <ul class="dropdown-menu bg-black" aria-labelledby="languageDropdown"
            style= "display; block; text-align: left; line-height: 30px; padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.2); font-size: 16px; font-family: var(--body-font);
text-transform: capitalize; color: var(--heading-color); font-weight: 400; -webkit-text-size-adjust: 100%;
    -webkit-tap-highlight-color: transparent; box-sizing: border-box;"> 
            @php
                $available_languages = \Cache::remember('all_active_languages', 60 * 60, function () {
                    return \App\Models\Language::where('status', 1)->get();
                });
                $current_lang_slug = \App\Facades\GlobalLanguage::user_lang_slug();
            @endphp
            @foreach($available_languages as $language)
                <li>
                    <a class="dropdown-item text-warning @if($current_lang_slug == $language->slug) active @endif" href="{{ route('change.language', $language->slug) }}">
                        {{ $language->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
                        @else
                            <div class="btn-wrapper">
                                @if(get_static_option('default_menu_item') == get_static_option('default_login_text'))
                                    <a href="{{route('landlord.user.login')}}" class="cmn-btn cmn-btn-bg-1">{{get_static_option('default_login_text') ?? __("Login")}}</a>
                                @elseif(get_static_option('default_menu_item') == get_static_option('default_register_text'))
                                    <a href="{{route('landlord.user.register')}}" class="cmn-btn cmn-btn-bg-1">{{get_static_option('default_register_text') ?? __("Get Started")}}</a>
                                @else
                                    <a href="{{route('landlord.user.register')}}" class="cmn-btn cmn-btn-bg-1">{{get_static_option('default_register_text') ?? __("Get Started")}}</a>
                                @endif
                            </div>
                                        <div class="language-switcher-wrapper ms-2">
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle border-dark" 
                style=" background-color:var(--main-color-one); color: #fff; line-height: 22px; padding: 10px 25px;font-size: 16px;font-weight: 500; font-family: var(--body-font); display: inline-block; border-radius: 5px; text-align: center; cursor: pointer; line-height: 34px; padding: 10px 10px;"
                 type="button"
                id="languageDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"
        >
            <i class="las la-globe text-black"></i>
            <span class="visually-hidden">{{ \App\Facades\GlobalLanguage::user_lang_slug() ?? __('Language') }}</span>
            {{ \App\Facades\GlobalLanguage::user_lang_slug() ?? 'Language' }}
        </button>
        <ul class="dropdown-menu bg-black" aria-labelledby="languageDropdown"
            style= "display; block; text-align: left; line-height: 30px; padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.2); font-size: 16px; font-family: var(--body-font);
text-transform: capitalize; color: var(--heading-color); font-weight: 400; -webkit-text-size-adjust: 100%;
    -webkit-tap-highlight-color: transparent; box-sizing: border-box;"> 
            @php
                $available_languages = \Cache::remember('all_active_languages', 60 * 60, function () {
                    return \App\Models\Language::where('status', 1)->get();
                });
                $current_lang_slug = \App\Facades\GlobalLanguage::user_lang_slug();
            @endphp
            @foreach($available_languages as $language)
                <li>
                    <a class="dropdown-item text-warning @if($current_lang_slug == $language->slug) active @endif" href="{{ route('change.language', $language->slug) }}">
                        {{ $language->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- Menu area end -->
</header>