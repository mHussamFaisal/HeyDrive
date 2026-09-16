@extends('layouts.app')

@section('title', trans('booking.page.terms.page_title'))

@section('header')
<link rel="stylesheet" href="{{ asset_url('css','booking.css') }}?_dc={{ config('app.timestamp') }}">
@include('partials.override_css')

@if (request('action') == 'download')
  <style>
  #booking-terms {
    padding:10px;
  }
  </style>
@endif
@endsection

@section('content')
<div id="booking-terms">
  @if (request('action') != 'download')
    @include('partials.loader')
  @endif

  <div id="terms-container">
    {!! $html !!}
  </div>

  @if ($html && config('site.terms_download') && request('action') != 'download')
    <div style="margin-top:10px;">
      <a href="{{ route('booking.terms', ['action' => 'download']) }}">
        <i class="fa fa-download"></i> <span>{{ trans('booking.page.terms.button.download') }}</span>
      </a>
    </div>
  @endif
</div>
@endsection

@section('footer')
@if (request('action') != 'download')
  <script>
  $(window).load(function() {
    $('#loader').hide();
  });
  </script>
@endif
@endsection
