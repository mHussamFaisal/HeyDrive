@extends('layouts.app')

@section('title', trans('booking.page.finish.page_title'))

@section('header')
<link rel="stylesheet" href="{{ asset_url('css','booking.css') }}?_dc={{ config('app.timestamp') }}">
@include('partials.override_css')
@endsection


@section('content')
<div id="booking-finish">
    @include('partials.loader')

    @foreach( $grouped as $gk => $bookings )
        @php
            $class = '';
            $icon = '';
            $title = '';
            $desc = '';
            $ref_number = '';

            foreach( $bookings as $bk => $booking ) {
                $ref_number .= ($ref_number ? ', ' : '') .'<span class="ref-number">'. $booking->getRefNumber() .'</span>';
            }

            switch( $gk ) {
                case 'request':
                    $class = 'bg-aqua';
                    $icon = 'ion-ios-time-outline';
                    $title = trans('booking.page.finish.request_title', [
                        'ref_number' => $ref_number,
                    ]);
                    $desc = trans('booking.page.finish.request_desc', [
                        'time' => '<span class="request-time">'. config('site.booking_request_time') .'h</span>'
                    ]);
                break;
                case 'quote':
                    $class = 'bg-orange';
                    $icon = 'ion-ios-chatbubble-outline';
                    $title = trans('booking.page.finish.quote_title', [
                        'ref_number' => $ref_number,
                    ]);
                    $desc = trans('booking.page.finish.quote_desc');
                break;
                case 'confirm':
                    $class = 'bg-green';
                    $icon = 'ion-ios-checkmark-outline';
                    $title = trans('booking.page.finish.confirm_title', [
                        'ref_number' => $ref_number,
                    ]);
                    $desc = trans('booking.page.finish.confirm_desc');
                break;
            }
        @endphp

        <div class="info-box {{ $class }}">
            <span class="info-box-icon">
                <i class="{{ $icon }}"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">
                    {!! App\Helpers\SiteHelper::nl2br2($title) !!}
                </span>
                @if( $desc )
                    <span class="progress-description">
                        {!! App\Helpers\SiteHelper::nl2br2($desc) !!}
                    </span>
                @endif
            </div>
        </div>
    @endforeach

    <div class="footer-text">
        {!! App\Helpers\SiteHelper::nl2br2(trans('booking.page.finish.footer', [
            'company_name' => config('site.company_name'),
            'company_email' => '<span style="font-weight:bold;">'. config('site.company_email') .'</span>',
            'url_contact' => '<a href="'. config('site.url_contact') .'" target="_top">'. trans('booking.page.finish.btn_contact') .'</a>',
        ])) !!}
    </div>

    <div class="create-button">
        <a href="{{ route('booking.index') }}" class="btn btn-primary btn-md">{{ trans('booking.page.finish.btn_create') }}</a>
    </div>

    @include('partials.branding')
</div>
@endsection


@section('footer')
<script>
$(window).load(function() {
    $('#loader').hide();
});

$(document).ready(function() {
    // Tooltip
    $('#booking-finish [title]').tooltip({
        placement: 'auto',
        container: 'body',
        selector: '',
        html: true,
        trigger: 'hover',
        delay: {
            show: 500,
            hide: 100
        }
    });
});
</script>
@endsection
