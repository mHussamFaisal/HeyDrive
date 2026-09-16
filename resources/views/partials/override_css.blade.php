@php
$isMobileApp = session('isMobileApp') ? 1 : 0;

if ($isMobileApp) {
    $borderRadius = config('site.mobile_app_styles_border_radius');
    $defaultBgColor = config('site.mobile_app_styles_default_bg_color');
    $defaultBorderColor = config('site.mobile_app_styles_default_border_color');
    $defaultTextColor = config('site.mobile_app_styles_default_text_color');
    $activeBgColor = config('site.mobile_app_styles_active_bg_color');
    $activeBorderColor = config('site.mobile_app_styles_active_border_color');
    $activeTextColor = config('site.mobile_app_styles_active_text_color');
    $customCss = config('site.mobile_app_custom_css');
}
else {
    $borderRadius = config('site.styles_border_radius');
    $defaultBgColor = config('site.styles_default_bg_color');
    $defaultBorderColor = config('site.styles_default_border_color');
    $defaultTextColor = config('site.styles_default_text_color');
    $activeBgColor = config('site.styles_active_bg_color');
    $activeBorderColor = config('site.styles_active_border_color');
    $activeTextColor = config('site.styles_active_text_color');
    $customCss = config('site.custom_css');
}
@endphp

<style>
/* General */
body {background:none; padding:10px;}
body.component {padding:0;}
.iti-mobile .intl-tel-input.iti-container {
  position: fixed;
  top: auto;
  bottom: auto;
  left: 10px;
  right: 10px;
  height: 200px;
  max-height: 90%;
}
#system-message .alert .alert-heading {display:none;}
#system-message .alert .alert-message {margin:0;}
.alert {border-radius:0;}
a { color: {{ $defaultBgColor }}; }
a:hover, a:focus, a:active {color: {{ $activeBgColor }};}
a[data-action="close"] {color: {{ $defaultBgColor }} !important;}
.form-control:hover,
.form-control.hover,
.form-control:focus,
.form-control.focus,
.input-group:hover,
.input-group.hover,
.input-group:focus,
.input-group.focus {
  border-color: {{ $defaultBorderColor }};
}
.checkbox .cr:hover, .radio .cr:hover {border-color: {{ $defaultBorderColor }};}
.checkbox label input[type="checkbox"]:checked+.cr {
  background: {{ $defaultBgColor }};
  border-color: {{ $defaultBorderColor }};
  color: {{ $defaultTextColor }};
}
.radio label input[type="radio"]:checked+.cr {color: {{ $defaultBgColor }};}
.btn {
  border-radius: 0px !important;
  -webkit-box-shadow: none !important;
  box-shadow: none !important;
  outline: 0 !important;
}
.btn-link, .btn-link:active, .btn-link:focus {color: {{ $defaultBgColor }} !important;}
.btn-link:hover {color: {{ $activeBgColor }} !important; text-decoration: none;}
.btn-primary,
.btn-primary.active,
.btn-primary:active,
.btn-primary.focus,
.btn-primary:focus {
  background-color: {{ $defaultBgColor }} !important;
  border-color: {{ $defaultBorderColor }} !important;
  color: {{ $defaultTextColor }} !important;
}
.btn-primary.hover,
.btn-primary:hover {
  background-color: {{ $activeBgColor }} !important;
  border-color: {{ $activeBorderColor }} !important;
  color: {{ $activeTextColor }} !important;
}
.nav-pills>li.active>a,
.nav-pills>li.active>a:hover,
.nav-pills>li.active>a:focus {
  border-top-color: {{ $defaultBgColor }};
}
.nav-stacked>li.active>a,
.nav-stacked>li.active>a:hover {
  border-left-color: {{ $defaultBgColor }};
}

@if (Route::is('booking.*'))
  /* Booking */
  .tt-menu:hover {border-color:#D2D6DE;}
  .tt-suggestion.tt-cursor,
  .tt-suggestion:hover {
    background-color: {{ $defaultBgColor }};
    color: {{ $defaultTextColor }};
  }
  .bootstrap-datetimepicker-widget table td,
  .bootstrap-datetimepicker-widget table th,
  .bootstrap-datetimepicker-widget table td span {
    border-radius: 0px;
  }
  .bootstrap-datetimepicker-widget table td.active,
  .bootstrap-datetimepicker-widget table td.active:hover,
  .bootstrap-datetimepicker-widget table td span.active {
    background-color: {{ $defaultBgColor }};
    color: {{ $defaultTextColor }};
    text-shadow: none;
  }
  .bootstrap-datetimepicker-widget table td.today:before {
    border-bottom-color: {{ $defaultBorderColor }};
  }
  .etoWaypointsAddButton:hover,
  .etoRoute1MapBtnShow:hover,
  .etoRoute2MapBtnShow:hover {
    color: {{ $defaultBgColor }};
  }
  .etoVehicleInnerContainer:hover,
  .etoVehicleInnerContainerSelected {
    border-color: {{ $defaultBgColor }} !important;
  }
  .etoSwapLocationsButton:hover {color: {{ $activeBgColor }};}
  #etoRouteReturnContainer:hover .cr-label {
    color: {{ $activeBgColor }};
    border-color: {{ $activeBgColor }} !important;
  }
  .eto-v2-vehicle:hover label .eto-v2-vehicle-price,
  .eto-v2-vehicle label.etoVehicleInnerContainerSelected .eto-v2-vehicle-price,
  .eto-v2-services-tabs .radio:hover .cr-val,
  .eto-v2-services-tabs .radio input[type="radio"]:checked+.cr+.cr-val,
  .eto-v2-checkout .radio input[type="radio"]+.cr+.cr-val:hover,
  .eto-v2-checkout .radio input[type="radio"]:checked+.cr+.cr-val {
    background-color: {{ $defaultBgColor }} !important;
    border-color: {{ $defaultBorderColor }} !important;
    color: {{ $defaultTextColor }} !important;
  }
@endif

@if ($borderRadius)
  .eto-v2-vehicle-bottom .eto-v2-vehicle-price,
  .etoVehicleChildSeatsOptionsContainer,
  .etoVehicleOtherOptionsContainer,
  .etoVehicleInnerContainer,
  .eto-v2-services-tabs .cr-val,
  .eto-v2-preferred-passengers,
  .eto-v2-preferred-luggage,
  .eto-v2-preferred-hand_luggage,
  #etoRoute1DateContainer,
  #etoRoute2DateContainer,
  .checkbox .cr,
  .radio .cr,
  .form-control,
  .btn,
  .etoGhostDateTime,
  .twitter-typeahead .input-group .input-group-addon,
  .twitter-typeahead .tt-menu,
  .input-group,
  .input-group-addon {
    border-radius: {{ $borderRadius }}px !important;
  }
@endif

@if (!empty($_SERVER['HTTP_USER_AGENT']) && (
     stripos($_SERVER['HTTP_USER_AGENT'], "iPod") ||
     stripos($_SERVER['HTTP_USER_AGENT'], "iPhone") ||
     stripos($_SERVER['HTTP_USER_AGENT'], "iPad") ||
     stripos($_SERVER['HTTP_USER_AGENT'], "OS X")))
  select {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIwLjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCA0LjkgMTAiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDQuOSAxMDsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOiM0NDQ0NDQ7fQo8L3N0eWxlPgo8dGl0bGU+YXJyb3dzPC90aXRsZT4KPHBvbHlnb24gY2xhc3M9InN0MCIgcG9pbnRzPSIxLjQsNC43IDIuNSwzLjIgMy41LDQuNyAiLz4KPHBvbHlnb24gY2xhc3M9InN0MCIgcG9pbnRzPSIzLjUsNS4zIDIuNSw2LjggMS40LDUuMyAiLz4KPC9zdmc+Cg==) !important;
    background-position: 95% 50% !important;
    background-repeat: no-repeat !important;
  }
  body.modal-open {padding:0;}
  body.modal-open #etoBookingUserModal {position:absolute;}
@endif

@if ($isMobileApp)
  #etoMinimalContainer .eto-icon-geolocation,
  #etoCompleteContainer .eto-icon-geolocation,
  #etoCompleteContainer #etoBookingUserContainer,
  #etoPanelContent .etoBookingListButtons,
  #etoRegisterForm > h3,
  #etoRegisterForm #loginButton,
  #etoPasswordForm > h3,
  #etoPasswordForm #loginButton,
  #etoPasswordNewForm #loginButton,
  #etoLoginForm > h3,
  #etoLoginForm #registerButton,
  #etoLoginForm #passwordButton,
  #etoPanelNavigationMaster .mobile-logout-hide {
    display: none !important;
  }
  #etoMainContainer {overflow: hidden;}
  #etoPanelContent .panel {margin-bottom:0px;}
  .panel {border-radius: 0px; -webkit-box-shadow:none; box-shadow: none;}
  .panel-default {border-color: #eeeeee;}
  .panel-default>.panel-heading {background-color:#fbfbfb; border-color:#eeeeee;}
  @if( !config('site.branding') )
    .footer-branding {display: none !important;}
  @endif
@endif

@if ($customCss)
/* Custom CSS */
{!! $customCss !!}
@endif
</style>
