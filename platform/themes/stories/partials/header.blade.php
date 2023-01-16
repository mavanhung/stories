<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=1" name="viewport"/>

        <!-- Fonts-->
        <link href="https://fonts.googleapis.com/css2?family={{ urlencode(theme_option('primary_font', 'Noto Sans JP')) }}:wght@400;500;700;900&display=swap" rel="stylesheet" type="text/css">
        <!-- CSS Library-->

        <style>
            :root {
                --color-primary: {{ theme_option('primary_color', '#3ba956') }};
                --color-secondary: {{ theme_option('secondary_color', '#2d3d8b') }};
                --color-danger: {{ theme_option('danger_color', '#e3363e') }};
                --primary-font: '{{ theme_option('primary_font', 'Noto Sans JP') }}', sans-serif;
            }
        </style>

        {!! Theme::header() !!}
    </head>
    <body @if (BaseHelper::siteLanguageDirection() == 'rtl') dir="rtl" @endif>
        <div id="alert-container"></div>
        <div class="scroll-progress primary-bg"></div>
        @if (theme_option('preloader_enabled', 'no') == 'yes')
            <!-- Start Preloader -->
            <div class="preloader text-center">
                <div class="circle"></div>
            </div>
        @endif
        <!--Offcanvas sidebar-->
        <aside id="sidebar-wrapper" class="custom-scrollbar offcanvas-sidebar" data-load-url="{{ route('theme.ajax-get-panel-inner') }}">
            <button class="off-canvas-close"><i class="elegant-icon icon_close"></i></button>
            <div class="sidebar-inner">
                <div class="sidebar-inner-loading">
                    <div class="half-circle-spinner">
                        <div class="circle circle-1"></div>
                        <div class="circle circle-2"></div>
                    </div>
                </div>
            </div>
        </aside>
        <!-- Start Header -->
        <header class="main-header header-style-1 font-heading">
            {{-- @if (is_plugin_active('language'))
                <div class="header-select-language d-block d-sm-none">
                    <div class="container">
                        <div class="language-wrapper d-block d-sm-none">
                            <span>{{ __('Language') }}:</span> {!! apply_filters('language_switcher') !!}
                        </div>
                    </div>
                </div>
            @endif --}}
            <div class="header-top">
                <div class="container">
                    <div class="row pt-20 pb-20 align-items-center">
                        <div class="col-md-3 col-6">
                            {{-- @if (theme_option('logo'))
                                <a href="{{ url('') }}"><img class="logo" src="{{ RvMedia::getImageUrl(theme_option('logo')) }}" alt="{{ setting('site_title') }}"></a>
                            @endif --}}
                            <a class="logo__custom" href="{{ url('') }}">
                                <img src="{{ Theme::asset()->url('images/logo.png') }}" alt="logo">
                                <div class="logo__custom-text">
                                    <span>xoaichua</span>
                                    <small>review</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-9 col-6 text-right header-top-right">
                            {!! Menu::renderMenuLocation('header-menu', [
                                'view'    => 'top-menu',
                                'options' => ['class' => 'list-inline nav-topbar d-none d-md-inline'],
                            ]) !!}
                            <div class="language-wrapper d-none d-md-inline">
                                {!! apply_filters('language_switcher') !!}
                            </div>
                            @if(apply_filters('language_switcher'))
                                <span class="vertical-divider mr-20 ml-20 d-none d-md-inline"></span>
                            @endif
                            <div class="form-search-wrapper">
                                <div class="row align-items-center">
                                    <div class="col-md-8 col-8 p-0">
                                        <form class="search-style-2" action="{{ is_plugin_active('blog') ? route('public.search') : '#' }}">
                                            <input type="text" name="q" id="" value="{{ app('request')->input('q') }}" placeholder="{{ __('Enter search text') }}">
                                            <button type="submit">
                                                <i class="elegant-icon icon_search"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <div class="float-right header-tools text-muted font-small d-flex align-items-center">
                                            <ul class="header-social-network d-inline-block list-inline mr-15">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if (theme_option('social_' . $i . '_url') && theme_option('social_' . $i . '_name'))
                                                        <li class="list-inline-item"><a class="social-icon text-xs-center" style="background: {{ theme_option('social_' . $i . '_color') }}" href="{{ theme_option('social_' . $i . '_url') }}" target="_blank" title="{{ theme_option('social_' . $i . '_name') }}" rel="nofollow"><i class="elegant-icon {{ theme_option('social_' . $i . '_icon') }}"></i></a></li>
                                                    @endif
                                                @endfor
                                            </ul>
                                            <div class="off-canvas-toggle-cover d-inline-block">
                                                <div class="off-canvas-toggle hidden d-inline-block" id="off-canvas-toggle">
                                                    <span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="search-icon d-none"><span class="mr-15 text-muted font-medium"><i class="elegant-icon icon_search mr-5"></i>{{ __('Search') }}</span></button>
                            @if (theme_option('action_button_text') && theme_option('action_button_url'))
                                <a href="{{ url(theme_option('action_button_url')) }}" class="btn btn-radius bg-primary text-white d-none d-md-inline ml-15 font-small box-shadow">{{ theme_option('action_button_text') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div id="hdFixed" class="header-sticky">
                <div class="container align-self-center">
                    <div class="mobile_menu d-lg-none d-block"></div>
                    <div class="main-nav d-none d-lg-block float-left">
                        <nav>
                            {!! Menu::renderMenuLocation('main-menu', [
                                'view'    => 'menu',
                                'options' => ['class' => 'main-menu d-none d-lg-inline font-menu'],
                            ]) !!}

                            {!! Menu::renderMenuLocation('main-menu', [
                                'view'    => 'menu',
                                'options' => ['class' => 'd-block d-lg-none text-muted', 'id' => 'mobile-menu'],
                            ]) !!}
                        </nav>
                    </div>
                    <div class="float-right header-tools text-muted font-small">
                        <ul class="header-social-network d-inline-block list-inline mr-15">
                            @for ($i = 1; $i <= 5; $i++)
                                @if (theme_option('social_' . $i . '_url') && theme_option('social_' . $i . '_name'))
                                    <li class="list-inline-item"><a class="social-icon text-xs-center" style="background: {{ theme_option('social_' . $i . '_color') }}" href="{{ theme_option('social_' . $i . '_url') }}" target="_blank" title="{{ theme_option('social_' . $i . '_name') }}" rel="nofollow"><i class="elegant-icon {{ theme_option('social_' . $i . '_icon') }}"></i></a></li>
                                @endif
                            @endfor
                        </ul>
                        <button class="search-icon"><span class="mr-15 text-muted font-medium"><i class="elegant-icon icon_search"></i></span></button>
                        <div class="off-canvas-toggle-cover d-inline-block">
                            <div class="off-canvas-toggle hidden d-inline-block" id="off-canvas-toggle">
                                <span></span>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="float-right header-tools text-muted font-small search-icon-sticky d-none">
                        <button class="search-icon"><span class="mr-15 text-muted font-medium"><i class="elegant-icon icon_search"></i></span></button>
                        <div class="off-canvas-toggle-cover d-inline-block">
                            <div class="off-canvas-toggle hidden d-inline-block" id="off-canvas-toggle">
                                <span></span>
                            </div>
                        </div>
                    </div> --}}
                    <div class="clearfix"></div>
                </div>
            </div>
        </header>
        <!--Start search form-->
        <div class="main-search-form">
            <div class="container">
                <div class="pt-50 pb-50 ">
                    <div class="row mb-20">
                        <div class="col-12 align-self-center main-search-form-cover m-auto">
                            <button class="search-icon">
                                <i class="elegant-icon icon_close"></i>
                            </button>
                            <p class="text-center"><span class="search-text-bg">{{ __('Search') }}</span></p>
                            <form action="{{ is_plugin_active('blog') ? route('public.search') : '#' }}" class="search-header">
                                <div class="input-group w-100">
                                    <input type="text" name="q" value="{{ app('request')->input('q') }}" class="form-control" placeholder="{{ __('Enter search text') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-search bg-white" type="submit">
                                            <i class="elegant-icon icon_search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Start Main content -->
        <main>
