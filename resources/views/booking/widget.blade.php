@extends('layouts.app')

@section('title', trans('booking.page_title'))


@section('header')
    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-datetimepicker/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('css','booking.css') }}?_dc={{ config('app.timestamp') }}">

    @include('partials.override_css')
@endsection


@section('content')
    <div class="eto-main-container">
        <div class="language-switcher-booking-widget">
            @include('partials.language_switcher')
        </div>

        <div id="etoMinimalContainer">
            <div class="loadingContainer">
                <i class="ion-ios-loop fa fa-spin loadingProgress"></i>
                <div class="loadingText">{{ trans('common.loading') }}</div>
            </div>
        </div>

        {{-- @include('partials.branding') --}}
    </div>
@endsection


@section('footer')
    {{-- <script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script> --}}
    <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="{{ asset_url('plugins','typeahead/typeahead.bundle.min.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset_url('plugins','combodate/combodate.js') }}"></script>

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
    @endphp

    <script>
    $(document).ready(function() {
        etoApp({
            debug: {{ config('app.debug') ? 1 : 0 }},
            layout: 'minimal',
            mainContainer: 'etoMinimalContainer',
            urlParams: {!! $urlParams !!}
        });
    });
    </script>
@endsection
