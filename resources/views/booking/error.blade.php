@extends('layouts.app')

@section('title', trans('booking.page.error.page_title'))

@section('header')
<link rel="stylesheet" href="{{ asset_url('css','booking.css') }}?_dc={{ config('app.timestamp') }}">
@include('partials.override_css')
@endsection


@section('content')
<div id="booking-error">
    @include('partials.loader')

    <div class="alert alert-danger">
        <a href="#" class="details-link" onclick="$('#errors-details').toggle(); return false;" title="{{ trans('booking.page.error.btn_details') }}">
            <i class="ion-ios-information-outline"></i>
        </a>
        {{ trans('booking.page.error.message') }}
        <div style="display:none;" id="errors-details">
            @foreach ($errors as $error)
                - {{ $error }}<br>
            @endforeach
        </div>
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
    $('#booking-error [title]').tooltip({
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
