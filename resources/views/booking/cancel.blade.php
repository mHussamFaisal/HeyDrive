@extends('layouts.app')

@section('title', trans('booking.page.cancel.page_title'))

@section('header')
<link rel="stylesheet" href="{{ asset_url('css','booking.css') }}?_dc={{ config('app.timestamp') }}">
@include('partials.override_css')
@endsection


@section('content')
<div id="booking-cancel">
    @include('partials.loader')

    <div class="info-box bg-red">
        <span class="info-box-icon">
            <i class="ion-ios-close-outline"></i>
        </span>
        <div class="info-box-content">
            <span class="info-box-text">
                {!! App\Helpers\SiteHelper::nl2br2(trans('booking.page.cancel.title', [
                    'ref_number' => $ref_number,
                ])) !!}
            </span>
            @if( trans('booking.page.cancel.desc') )
                <span class="progress-description">
                    {!! App\Helpers\SiteHelper::nl2br2(trans('booking.page.cancel.desc')) !!}
                </span>
            @endif
        </div>
    </div>

    <div class="create-button">
        <a href="{{ route('booking.index') }}" class="btn btn-primary btn-md">{{ trans('booking.page.cancel.btn_create') }}</a>
    </div>

    @include('partials.branding')
</div>
@endsection


@section('footer')
<script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script>

<script>
$(window).load(function() {
    $('#loader').hide();
});

$(document).ready(function() {
    // Remove booking cookie
    $.removeCookie('eto_redirect_booking_url', {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});
});
</script>
@endsection
