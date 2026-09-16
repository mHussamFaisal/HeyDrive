@extends('admin.index')

@section('title', trans('admin/bookings.page_title') .' / '. $booking->getRefNumber() .' / '. trans('admin/bookings.subtitle.edit'))
@section('subtitle', /*'<i class="fa fa-pencil-square-o"></i> '*/ '<a href="'. route('admin.bookings.index') .'">'. trans('admin/bookings.page_title') .'</a> / <a href="'. route('admin.bookings.show', $booking->id) .'">'. $booking->getRefNumber() .'</a> / '. trans('admin/bookings.subtitle.edit') )


@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','jquery-intl-tel-input/css/intlTelInput.min.css') }}?_dc={{ config('app.timestamp') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.css') }}">
  <link rel="stylesheet" href="{{ asset_url('css','eto.css') }}?_dc={{ config('app.timestamp') }}">
@endsection


@section('subcontent')
  @include('partials.alerts.success')
  @include('partials.alerts.errors')
  @include('partials.modals.popup')

  @php $formId = 'eto-booking-edit-'. time() .'-'. $booking->id; @endphp

  <div class="eto-wrapper-booking-edit">
    <div class="eto-form eto-form-booking" id="{{ $formId }}"></div>
  </div>

  @include('booking.settings')
@stop


@section('subfooter')
  <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
  <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
  <script src="{{ asset_url('plugins','jquery-intl-tel-input/js/intlTelInput-jquery.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('plugins','jquery-sortable/jquery-sortable-min.js') }}"></script>
  <script src="{{ asset_url('plugins','jquery-resize/jquery.resize.js') }}"></script>
  <script src="{{ asset_url('plugins','typeahead/typeahead.bundle.min.js') }}"></script>
  <script src="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.min.js') }}"></script>

  <script src="{{ asset_url('js','eto/eto-booking.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','eto/eto-form.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','eto/eto-booking-form.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','eto/eto-user.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','eto/eto-user-driver.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','eto/eto-user-fleet.js') }}?_dc={{ config('app.timestamp') }}"></script>

  <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry&language={{ app()->getLocale() }}"></script>

  <script>
    $(document).ready(function(){
        $.LoadingOverlay('show');

        setTimeout(function () {
            if(typeof ETO.Booking != "undefined") {
                if (typeof ETO.Booking.init != "undefined") {
                    ETO.Booking.init({
                        initializing: false,
                    });
                }
            }

            if(typeof ETO.Form != "undefined") {
                ETO.Form.init();
            }
            else {console.log('ETO.Form is not initialized');}

            if(typeof ETO.Booking.Form != "undefined") {
                ETO.Booking.Form.init({
                    bookingId: {
                        '{{ $formId }}': {{ $booking->id }},
                    },
                    siteId: {{ $siteId  }}
                });
            }
            else {console.log('ETO.Booking.Form is not initialized');}
        });

        @if(request()->system->subscription->license_status == 'suspended')
        $('.eto-form').prepend('<div class="license-suspended-block"></div>');
        @endif
    }, 0);

    $(window).load(function(){
      $.LoadingOverlay('hide');
    });
  </script>
@endsection
