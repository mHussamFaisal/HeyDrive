@extends('layouts.app')

@section('title', trans('customer.page_title'))

@section('bodyClass', 'skin-blue customer-panel '. ( isset($_COOKIE['eto_state_sidebar_collapse']) ? 'sidebar-collapse' : '' ) .' fixed hold-transition')


@section('header')
    <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('css','customer.css') }}?_dc={{ config('app.timestamp') }}">

    @include('partials.override_css')

    <style>
    @if (request('tmpl') == 'component')
      #etoPasswordForm #loginButton,
      .eto-sidebar-menu-logout,
      .logout-container,
      #etoPanelNavigationMaster .mobile-logout-hide {
        display: none !important;
      }
    @endif

    @if (session('isMobileApp'))
      .navbar-static-top:not(.eto-navbar-horizontal-nav) .navbar-custom-menu-user,
      .eto-sidebar-menu-logout,
      .logout-container {
          display: none !important;
      }
    @endif

    @if (!config('site.branding'))
      .sidebar {
          padding-bottom: 0px;
      }
      .copyright-box {
          display: none !important;
      }
    @endif
    </style>
@endsection


@section('content')
    <div class="language-switcher-customer hidden">
        @include('partials.language_switcher')
    </div>

    <div id="etoMainContainer">
        <div class="loadingContainer">
            <i class="ion-ios-loop fa fa-spin loadingProgress"></i>
            <div class="loadingText">{{ trans('common.loading') }}</div>
        </div>
    </div>
@endsection


@section('footer')
    <script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','form-validation/formValidation.min.js') }}"></script>
    <script src="{{ asset_url('plugins','form-validation/formValidation-bootstrap.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-readmore/readmore.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset_url('plugins','fastclick/fastclick.min.js') }}"></script>
    <script src="{{ asset_url('js','app.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script>
    var ETOLang = {!! json_encode(array_merge_deep(trans('frontend.js'), [
        'booking.buttons.more' => trans('booking.buttons.more'),
        'booking.buttons.less' => trans('booking.buttons.less'),
        'common.toggle_navigation' => trans('driver/index.toggle_navigation'),
        'common.powered_by' => trans('common.powered_by'),
    ])) !!};
    var ETOTemplate = {!! request('tmpl') == 'component' ? 1 : 0 !!};
    </script>

    @if (file_exists(asset_path('uploads','custom/js/'. app()->getLocale() .'/override_'. app()->getLocale() .'.js')))
        <script src="{{ asset_url('uploads','custom/js/'. app()->getLocale() .'/override_'. app()->getLocale() .'.js') }}"></script>
    @endif

    <script src="{{ asset_url('js','customer.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry&language={{ app()->getLocale() }}"></script>
    <script src="{{ asset_url('plugins','markerwithlabel/markerwithlabel.js') }}"></script>
    <script src="{{ asset_url('plugins','sweetalert2/sweetalert2.js') }}"></script>

    <script src="{{ asset_url('js','eto/eto.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-user.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-user-customer.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-routehistory.js') }}?_dc={{ config('app.timestamp') }}"></script>

    @php
    $redirectUrl = config('site.url_customer');
    $currentUrl = url()->current();
    $loadCheck = 0;

    if ($redirectUrl && $currentUrl) {
        $url1 = parse_url($redirectUrl, PHP_URL_HOST) . parse_url($redirectUrl, PHP_URL_PATH);
        $url2 = parse_url($currentUrl, PHP_URL_HOST) . parse_url($currentUrl, PHP_URL_PATH);

        if ($url1 == $url2) {
            $loadCheck = 1;
        }
    }
    @endphp

    <script>
    $(document).ready(function() {
        ETO.setConfig({!! json_encode(\App\Helpers\SettingsHelper::getJsConfig()) !!});

        var loadETO = 0;

        @if( session('isMobileApp') || request('no_redirect') || config('site.embed') == 0 || $loadCheck )
            loadETO = 1;
        @endif

        if (!loadETO) {
            var pUrl = '{{ config('site.url_customer') }}';
            var pUrlWithoutHash = pUrl;
            var pUrlHash = '';

            if( pUrl ) {
                var type = pUrl.split('#');
                if( type.length > 1 ) {
                    pUrlWithoutHash = type[0];
                    pUrlHash = type[1];
                }
            }

            var cUrl = window.location.href ? window.location.href : '';
            var cUrlWithoutHash = cUrl;
            var cUrlHash = '';

            if( cUrl ) {
                var type = cUrl.split('#');
                if( type.length > 1 ) {
                    cUrlWithoutHash = type[0];
                    cUrlHash = type[1];
                }
            }

            if( top === self && {{ config('site.embed') }} == 1 && pUrlWithoutHash && cUrlWithoutHash && pUrlWithoutHash != cUrlWithoutHash ) {
                $.cookie('eto_redirect_customer_url', cUrl, {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
                window.location.href = pUrl;
            }
            else {
                @if (request('tmpl') == 'component')
                    $.cookie('eto_redirect_customer_url', cUrl, {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
                @endif

                var rUrl = $.cookie('eto_redirect_customer_url') ? $.cookie('eto_redirect_customer_url') : '';
                var rUrlWithoutHash = rUrl;
                var rUrlHash = '';

                if( rUrl ) {
                    var type = rUrl.split('#');
                    if( type.length > 1 ) {
                        rUrlWithoutHash = type[0];
                        rUrlHash = type[1];
                    }
                }

                function UpdateQueryString(key, value, url) {
                    if (!url) url = window.location.href;
                    var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
                        hash;

                    if (re.test(url)) {
                        if (typeof value !== 'undefined' && value !== null) {
                            return url.replace(re, '$1' + key + "=" + value + '$2$3');
                        }
                        else {
                            hash = url.split('#');
                            url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
                            if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                                url += '#' + hash[1];
                            }
                            return url;
                        }
                    }
                    else {
                        if (typeof value !== 'undefined' && value !== null) {
                            var separator = url.indexOf('?') !== -1 ? '&' : '?';
                            hash = url.split('#');
                            url = hash[0] + separator + key + '=' + value;
                            if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                                url += '#' + hash[1];
                            }
                            return url;
                        }
                        else {
                            return url;
                        }
                    }
                }

                rUrlWithoutHash = UpdateQueryString('lang', null, rUrlWithoutHash);
                cUrlWithoutHash = UpdateQueryString('lang', null, cUrlWithoutHash);

                if( rUrlWithoutHash && cUrlWithoutHash && rUrlWithoutHash != cUrlWithoutHash ) {
                    // $.removeCookie('eto_redirect_customer_url', {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
                    window.location.href = rUrl;
                }
                else {
                    if( rUrlHash ) {
                        window.location.hash = rUrlHash;
                    }

                    loadETO = 1;
                }
            }
        }

        if (loadETO) {
            var etoAccount = new etoAppV2({
                debug: {{ config('app.debug') ? 1 : 0 }},
                isMobileApp: {{ session('isMobileApp') ? 'true' : 'false' }},
                horizontalNav: {{ session('clientType') == 'etoengine-customer' && version_compare(session('clientVersion'), '1.5.0', '<=') ? 'true' : 'false' }}
            });
            etoAccount.init();

            setTimeout(function() {
                $('body').on('click', '.eto-btn-booking-tracking', function(e) {
                    if (ETO.model === false && typeof ETO.Routehistory != "undefined" && typeof ETO.Routehistory.init != "undefined") {
                        ETO.Routehistory.init({
                            init: ['google', 'icons'],
                            lang: ['booking'],
                            type: 'customer',
                        });
                    }
                });
            }, 200);
        }
    });
    </script>

    @include('partials.branding')
@endsection
