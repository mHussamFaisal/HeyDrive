@extends('layouts.app')

@section('title', trans('booking.page_title'))

@php
$isMobileApp = session('isMobileApp');
@endphp

@if($isMobileApp)
    @section('bodyClass', 'skin-blue booking-panel '. ( isset($_COOKIE['eto_state_sidebar_collapse']) ? 'sidebar-collapse' : '' ) .' fixed hold-transition')
@endif

@section('header')
    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-datetimepicker/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','jquery-intl-tel-input/css/intlTelInput.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('css','booking.css') }}?_dc={{ config('app.timestamp') }}">

    @include('partials.override_css')
@endsection


@section('content')

    @if ($isMobileApp)
    <style>
    body.booking-panel {
      padding: 0;
    }
    .footer-branding, .language-switcher-booking {
      display:none !important;
    }
    .navbar-static-top:not(.eto-navbar-horizontal-nav) .navbar-custom-menu-user,
    .eto-sidebar-menu-logout,
    .logout-container {
        display: none !important;
    }
    @media (max-width:540px) {
      .v2-steps-step {
        display: table;
        width: 30%;
        padding: 4px 10px 4px 28px;
      }
      .v2-steps {
        margin-right: 0px;
      }
      .v2-steps-title {
        display: inline-block !important;
        line-height: 28px !important;
        font-size: 12px !important;
      }
      .v2-steps-lang,
      .v2-steps-name {
        display: none !important;
      }
    }
    @if( !config('site.branding') )
      .sidebar {
          padding-bottom: 0px;
      }
      .copyright-box {
          display: none !important;
      }
    @endif
    </style>

    @php
    $userId = (int)session('etoUserId', 0);
    $userName = '';
    $userSince = '';
    $userAvatar = asset_url('images','placeholders/avatar.png');

    if ($userId) {
        $model = \App\Models\Customer::select('id', 'name', 'created_date', 'avatar')
            ->where('id', $userId)
            ->where('site_id', config('site.site_id') )
            ->first();

        if (!empty($model->id)) {
            $userId = (int)$model->id;
            $userName = (string)$model->name;
            $userAvatar = $model->getAvatarPath();
            $userSince = trans('driver/account.member_since') .' '. \Carbon\Carbon::parse($model->created_date)->diffForHumans(null, true, false, 2);
        }
    }
    @endphp

    <div class="wrapper eto-wrapper-booking-list">
      <header class="main-header">
        <nav class="navbar navbar-static-top @if( session('clientType') == 'etoengine-customer' && version_compare(session('clientVersion'), '1.5.0', '<=')) eto-navbar-horizontal-nav @endif">
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">{{ trans('driver/index.toggle_navigation') }}</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <div class="navbar-custom-menu navbar-custom-menu-horizontal">
            <ul class="nav navbar-nav">
              <!--<li><a href="{{ route('customer.index') }}#booking/list"><span>{{ trans('frontend.js.panel_Bookings') }}</span></a></li>-->
              <!--<li><a href="{{ route('customer.index') }}#booking/new"><span>{{ trans('frontend.js.panel_NewBooking') }}</span></a></li>-->
              <li><a href="{{ route('customer.index') }}#user"><span>{{ trans('frontend.js.panel_Profile') }}</span></a></li>
            <ul>
          </div>
          <span class="main-page-title">{{ trans('frontend.js.panel_NewBooking') }}</span>
          <div class="navbar-custom-menu navbar-custom-menu-user">
            <ul class="nav navbar-nav">
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="{{ $userAvatar }}" class="user-image" alt="">
                  <span class="hidden-xs">{{ $userName }}</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="user-header">
                    <p>{{ $userName }}<small>{{ $userSince }}</small></p>
                  </li>
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="{{ route('customer.index') }}#user" class="btn btn-default btn-flat">
                        <span>{{ trans('frontend.js.panel_Profile') }}</span>
                      </a>
                    </div>
                    <div class="pull-right logout-container">
                      <a href="{{ route('customer.index') }}#logout" class="btn btn-default btn-flat">
                        <span>{{ trans('frontend.js.panel_Logout') }}</span>
                      </a>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <aside class="main-sidebar"> <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"> <span class="icon-toggle"></span> </a>
        <section class="sidebar">
          <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ $userAvatar }}" class="img-circle" alt="">
            </div>
            <div class="pull-left info">
              <p style="max-width:120px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; @if(config('app.locale_switcher_enabled')) margin: 0 0 5px 0; @else margin: 10px 0 0 0;  @endif">{{ $userName }}</p>
              @if(config('app.locale_switcher_enabled'))
                  <div class="user-panel-locale">
                    <div class="btn-group">
                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <span class="eto-language-name">{{ config('app.locales')[app()->getLocale()]['native'] }}</span>
                        <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu">
                        @foreach (config('app.locales') as $lang => $language)
                            @if( in_array($lang, config('app.locale_active')) )
                              <li><a href="{{ route('locale.change', $lang) }}" class="clearfix">
                                  <img src="{{ asset_url('images','flags/'. $lang .'.png') }}" class="eto-language-flag">
                                  <span class="eto-language-name">{{ $language['native'] }}</span>
                              </a></li>
                            @endif
                        @endforeach
                      </ul>
                    </div>
                  </div>
              @endif
            </div>
          </div>
          <ul class="sidebar-menu">
            <li class="eto-sidebar-menu-bookings"><a href="{{ route('customer.index') }}#booking/list"><span>{{ trans('frontend.js.panel_Bookings') }}</span></a></li>
            <li class="eto-sidebar-menu-booking-new active"><a href="{{ route('customer.index') }}#booking/new"><span>{{ trans('frontend.js.panel_NewBooking') }}</span></a></li>
            <li class="eto-sidebar-menu-profile"><a href="{{ route('customer.index') }}#user"><span>{{ trans('frontend.js.panel_Profile') }}</span></a></li>
            <li class="eto-sidebar-menu-logout mobile-logout-hide"><a href="{{ route('customer.index') }}#logout"><span>{{ trans('frontend.js.panel_Logout') }}</span></a></li>
          </ul>
          <div class="copyright-box">
            {{ trans('common.powered_by') }} <a href="https://easytaxioffice.com" target="_blank">EasyTaxiOffice</a>
          </div>
        </section>
      </aside>
      <div class="content-wrapper">
        <section class="content">
    @endif

    <div class="eto-main-container">
        <div class="language-switcher-booking">
            @include('partials.language_switcher')
        </div>

        <div id="etoCompleteContainer">
            <div class="loadingContainer">
                <i class="ion-ios-loop fa fa-spin loadingProgress"></i>
                <div class="loadingText">{{ trans('common.loading') }}.</div>
            </div>
        </div>

        @include('partials.branding')
    </div>

    @if($isMobileApp)
        </section>
      </div>
    </div>
    @endif
@endsection


@section('footer')
    <script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="{{ asset_url('plugins','typeahead/typeahead.bundle.min.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset_url('plugins','combodate/combodate.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-intl-tel-input/js/intlTelInput-jquery.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','form-validation/formValidation.min.js') }}"></script>
    <script src="{{ asset_url('plugins','form-validation/formValidation-bootstrap.min.js') }}"></script>

    @if($isMobileApp)
    <script src="{{ asset_url('plugins','jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset_url('plugins','fastclick/fastclick.min.js') }}"></script>
    <script src="{{ asset_url('js','app.js') }}?_dc={{ config('app.timestamp') }}"></script>
    @endif

    <script>
    var ETOLang = {!! json_encode(trans('frontend.js')) !!};
    var ETOBookingType = '{{ request()->get('bookingType') }}';
    </script>

    @if (file_exists(asset_path('uploads','custom/js/'. app()->getLocale() .'/override_'. app()->getLocale() .'.js')))
        <script src="{{ asset_url('uploads','custom/js/'. app()->getLocale() .'/override_'. app()->getLocale() .'.js') }}?_dc={{ config('app.timestamp') }}"></script>
    @endif

    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places&language={{ app()->getLocale() }}"></script>

    <script src="{{ asset_url('js','booking.js') }}?_dc={{ config('app.timestamp') }}"></script>

    @php
    parse_str(urldecode(request()->getQueryString()), $urlParams);
    $urlParams = json_encode($urlParams);

    $redirectUrl = config('site.url_booking');
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
        var loadETO = 0;

        @if( session('isMobileApp') || request('no_redirect') || config('site.embed') == 0 || $loadCheck )
            loadETO = 1;
            $.cookie('eto_redirect_booking_url', window.location.href, {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
            // $.removeCookie('eto_redirect_booking_url', {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
        @endif

        if (!loadETO) {
            var pUrl = '{{ config('site.url_booking') }}';
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
                $.cookie('eto_redirect_booking_url', cUrl, {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
                window.location.href = pUrl;
            }
            else {
                var rUrl = $.cookie('eto_redirect_booking_url') ? $.cookie('eto_redirect_booking_url') : '';
                var rUrlWithoutHash = rUrl;
                var rUrlHash = '';

                if( rUrl ) {
                    var type = rUrl.split('#');
                    if( type.length > 1 ) {
                        rUrlWithoutHash = type[0];
                        rUrlHash = type[1];
                    }
                }

                if( rUrlWithoutHash && cUrlWithoutHash && rUrlWithoutHash != cUrlWithoutHash ) {
                    // $.removeCookie('eto_redirect_booking_url', {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
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
            etoApp({
                debug: {{ config('app.debug') ? 1 : 0 }},
                customerURL: 'customer',
                urlParams: {!! $urlParams !!}
            });
        }
    });
    </script>
@endsection
