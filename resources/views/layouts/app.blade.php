<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="format-detection" content="telephone=no">
    <meta name="robots" content="noindex, nofollow" />
    <title>@yield('title') - {{ config('app.name') }}</title>

    @if ( config('site.logo') )
        <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="{{ asset_url('uploads','logo/'. config('site.logo')) }}" />
      	<link rel="icon" type="image/x-icon" href="{{ asset_url('uploads','logo/'. config('site.logo')) }}" />
      	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset_url('uploads','logo/'. config('site.logo')) }}">
      	<link rel="icon" sizes="192x192" href="{{ asset_url('uploads','logo/'. config('site.logo')) }}">
    @endif

    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','font-awesome/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','ionicons/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('css','AdminLTE.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','sweetalert2/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','jquery-webui-popover/jquery.webui-popover.css') }}">

    @php
    $gtagID = config('site.google_analytics_tracking_id') ? config('site.google_analytics_tracking_id') : config('site.google_adwords_conversion_id');
    @endphp

    @if( $gtagID && !Route::is('admin.*') )
      <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gtagID }}"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        @if( config('site.google_analytics_tracking_id') )
          gtag('config', '{{ config('site.google_analytics_tracking_id') }}');
        @endif

        @if( config('site.google_adwords_conversion_id') && config('site.google_adwords_conversions') )
          gtag('config', '{{ config('site.google_adwords_conversion_id') }}');
        @endif
      </script>
    @endif

    

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="{{ asset_url('plugins','html5shiv/html5shiv.min.js') }}"></script>
    <script src="{{ asset_url('plugins','respond/respond.min.js') }}"></script>
    <![endif]-->

    <script src="{{ asset_url('plugins','jquery/jquery.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery/jquery-migrate.min.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap/bootstrap.min.js') }}"></script>

    @if( Route::is('admin.*') || Route::is('driver.*') || Route::is('dispatch.*') || Route::is('subscription.*') || Route::is('reports.*') )
        <script data-cfasync="false" src="{{ asset_url('plugins','iframe-resizer/iframeResizer.min.js') }}"></script>
    @endif

    @if( !Route::is('admin.map.index') )
        <script data-cfasync="false" src="{{ asset_url('plugins','iframe-resizer/iframeResizer.contentWindow.min.js')}}"></script>
    @endif

    <script src="{{ asset_url('plugins','jquery-loading-overlay/loadingoverlay.min.js') }}"></script>

    @if( Route::is('admin.*') || Route::is('driver.*') || Route::is('dispatch.*') || Route::is('subscription.*') || Route::is('reports.*'))
        @include('layouts.eto-js')
    @endif

    <script>
    $(function() {
        $.LoadingOverlaySetup({
            image: '',
            fontawesome: 'fa fa-spinner fa-spin',
            maxSize: '80px',
            minSize: '20px',
            resizeInterval: 0,
            size: '50%',
            fade: [0,200]
        });
    });

    var EasyTaxiOffice = {
        csrfToken: '{{ csrf_token() }}',
        appPath: '{{ url('/') }}',
        timestamp: '{{ config('app.timestamp') }}',
        timezone: '{{ config('app.timezone') }}',
        cookiePath: '{{ config('session.path') }}',
        cookieSecure: {{ config('session.secure') ? 'true' : 'false' }},
        cookieSameSite: {!! config('session.same_site') ? "'".config('session.same_site')."'" : 'null' !!},
    };
    </script>

    @if( request()->get('tmpl') == 'body' )
        @yield('subheader')

        @if( Route::is('admin.*') )
            <link rel="stylesheet" href="{{ asset_url('css','admin.css') }}?_dc={{ config('app.timestamp') }}">
        @elseif( Route::is('driver.*') )
            <link rel="stylesheet" href="{{ asset_url('css','driver.css') }}?_dc={{ config('app.timestamp') }}">
        @endif
    @else
        @yield('header')
    @endif

    @if( config('site.code_head') && !Route::is('admin.*') && !Route::is('driver.*') )
        {!! config('site.code_head') !!}
    @endif
</head>
<body class="@yield('bodyClass')">
    <!--
    Copyright (c) 2020 by EasyTaxiOffice - All Rights Reserved
    Website: https://easytaxioffice.com
    Licence: https://easytaxioffice.com/licence/
    -->

    @if( request()->get('tmpl') == 'body' )
        @yield('subcontent')
        @yield('subfooter')
    @else
        @yield('content')
        @yield('footer')
    @endif

    @if( config('site.code_body') && !Route::is('admin.*') && !Route::is('driver.*') )
        {!! config('site.code_body') !!}
    @endif
</body>
</html>
