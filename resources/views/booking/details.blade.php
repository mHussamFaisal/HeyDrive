@extends('layouts.app')

@section('title', trans('booking.page_title'))

@section('header')
    <link rel="stylesheet" href="{{ asset_url('css','eto.css') }}?_dc={{ config('app.timestamp') }}">

    <style>
    html, body {
        height: 100%;
    }
    body {
        margin: 0;
        padding: 0;
        width: 100%;
    }
    .details-box {
        margin-bottom: 20px;
    }
    .section-box {
        margin-bottom: 10px;
    }
    .header-box {
        font-weight: bold;
    }
    .row-box {
        clear: both;
        margin-bottom: 2px;
    }
    .label-box {
        color: #afafaf;
        display: inline-block;
        min-width: 140px;
        float: left;
    }
    @media (max-width: 480px) {
        .label-box {
            display: block;
        }
    }
    </style>
@endsection


@section('content')
    <div class="container2" style="margin:0 auto; max-width:600px; padding:10px;">
        <div style="text-align:left; border-bottom:1px #e8e8e8 solid; margin-bottom:20px;">
            <a href="{{ config('site.url_home') ? config('site.url_home') : url('/') }}" target="_blank">
                @if (config('site.logo'))
                    <img src="{{ asset_url('uploads','logo/'. config('site.logo')) }}" alt="{{ config('app.name') }}" style="max-width:300px;" />
                @else
                    <span style="font-size:22px; color:#333;">{{ config('app.name') }}</span>
                @endif
            </a>
        </div>

        <div class="details-box">
            <div class="section-box">
                <div class="header-box">{{ trans('booking.heading.journey') }}</div>
                <div class="row-box">
                    <span class="label-box">{{ trans('booking.ref_number') }}:</span>
                    <span class="value-box">{{ $booking->getRefNumber() }}</span>
                </div>
                <div class="row-box">
                    <span class="label-box">{{ trans('booking.date') }}:</span>
                    <span class="value-box">{{ \App\Helpers\SiteHelper::formatDateTime($booking->date) }}</span>
                </div>
                <div class="row-box">
                    <span class="label-box">{{ trans('booking.from') }}:</span>
                    <span class="value-box">{!! $booking->getFrom() !!}</span>
                </div>
                @if ($booking->getVia())
                    <div class="row-box">
                        <span class="label-box">{{ trans('booking.via') }}:</span>
                        <span class="value-box">{!! $booking->getVia() !!}</span>
                    </div>
                @endif
                <div class="row-box">
                    <span class="label-box">{{ trans('booking.to') }}:</span>
                    <span class="value-box">{!! $booking->getTo() !!}</span>
                </div>
            </div>

            @if( !empty($driver->id) )
                <div class="section-box">
                    <div class="header-box">{{ trans('booking.heading.driver') }}</div>
                    @if( $driver->avatar )
                        <div class="row-box">
                            <span class="label-box">{{ trans('admin/users.avatar') }}:</span>
                            <span class="value-box"><img src="{{ asset($driver->getAvatarPath()) }}" alt="" style="padding:0px; margin:0px; max-width:100px;" /></span>
                        </div>
                    @endif
                    @if( $driver->profile->getFullName() )
                        <div class="row-box">
                            <span class="label-box">{{ trans('admin/users.name') }}:</span>
                            <span class="value-box">{{ $driver->profile->getFullName() }}</span>
                        </div>
                    @endif
                    @if( $driver->profile->mobile_no )
                        <div class="row-box">
                            <span class="label-box">{{ trans('admin/users.mobile_no') }}:</span>
                            <span class="value-box">{!! $driver->profile->getTelLink('mobile_no', ['style'=>'color:#333333;']) !!}</span>
                        </div>
                    @endif
                    @if( $driver->profile->pco_licence )
                        <div class="row-box">
                            <span class="label-box">{{ trans('admin/users.pco_licence') }}:</span>
                            <span class="value-box">{{ $driver->profile->pco_licence }}</span>
                        </div>
                    @endif
                </div>
            @endif

            @if( !empty($vehicle->id) )
                <div class="section-box">
                    <div class="header-box">{{ trans('booking.heading.vehicle') }}</div>
                    @if( $vehicle->registration_mark )
                        <div class="row-box">
                            <span class="label-box">{{ trans('admin/vehicles.registration_mark') }}:</span>
                            <span class="value-box">{{ $vehicle->registration_mark }}</span>
                        </div>
                    @endif
                    @if( $vehicle->make )
                        <div class="row-box">
                            <span class="label-box">{{ trans('admin/vehicles.make') }}:</span>
                            <span class="value-box">{{ $vehicle->make }}</span>
                        </div>
                    @endif
                    @if( $vehicle->model )
                        <div class="row-box">
                            <span class="label-box">{{ trans('admin/vehicles.model') }}:</span>
                            <span class="value-box">{{ $vehicle->model }}</span>
                        </div>
                    @endif
                    @if( $vehicle->colour )
                        <div class="row-box">
                            <span class="label-box">{{ trans('admin/vehicles.colour') }}:</span>
                            <span class="value-box">{{ $vehicle->colour }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <a href="javascript:void(0)" class="hidden eto-btn-booking-tracking eto-btn-booking-tracking-customer" data-eto-id="{{ $booking->id }}">Tracking history</a>
        <div class="eto-tracking-panel">
            <div class="eto-booking-tracking-map" style="width:100%; height:500px; border:1px #e8e8e8 solid;"></div>
        </div>
    </div>
@endsection


@section('footer')
    {{-- <script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script> --}}
    <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry&language={{ app()->getLocale() }}"></script>
    <script src="{{ asset_url('plugins','markerwithlabel/markerwithlabel.js') }}"></script>
    <script src="{{ asset_url('plugins','sweetalert2/sweetalert2.js') }}"></script>

    @include('layouts/eto-js')

    <script src="{{ asset_url('js','eto/eto.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-user.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-user-customer.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-routehistory.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script>
    $(document).ready(function() {
        {{-- ETO.setConfig({!! json_encode(\App\Helpers\SettingsHelper::getJsConfig()) !!}); --}}

        if (ETO.model === false && typeof ETO.Routehistory != "undefined" && typeof ETO.Routehistory.init != "undefined") {
            ETO.Routehistory.init({
                init: ['google', 'icons'],
                lang: ['booking'],
                type: 'passenger',
            });
        }
    });
    </script>
@endsection
