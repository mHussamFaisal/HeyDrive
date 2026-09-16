@extends('layouts.app')

@section('title', trans('feedback.page_title'))

@section('header')
  @include('partials.override_css')
@stop

@section('content')
  @include('partials.alerts.success')
  @include('partials.alerts.errors')

  <div id="feedback">

  </div>
@stop

@section('footer')
  {{-- <script type="text/javascript">
  $(document).ready(function() {

  });
  </script> --}}
@stop
