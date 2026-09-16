@extends('admin.index')

@section('title', trans('admin/callerid.page_title'))
@section('subtitle', /*'<i class="fa fa-user"></i> '.*/ trans('admin/callerid.page_title') )
@section('subclass', 'callerid-wrapper')

@section('subheader')
<style>
#callerid .td-label {
  width: 130px;
  display: none;
  margin-right: 5px;
  color: #808080;
}
#callerid .th-route {
  width: 240px;
}
@media (max-width: 600px) {
  #callerid table tr {
    display: block !important;
    margin-bottom: 10px !important;
    padding-bottom: 10px !important;
    border-bottom: 1px solid rgb(244, 244, 244) !important;
  }
  #callerid table tr:last-child {
    border-bottom: 0 !important;
  }
  #callerid table thead {
    display: none !important;
  }
  #callerid table td {
    display: block !important;
    padding: 2px 5px !important;
    border: 0 !important;
  }
  #callerid .td-label {
    display: inline-block !important;
  }
  #callerid .th-route {
    width: auto;
  }
}
#callerid .eto-address-more {
  white-space: pre-line;
  min-width: 200px;
}
#callerid .eto-address-more-link {
  font-size: 12px;
}
#callerid .th-ref_number {
  min-width:50px;
}
#callerid .th-date {
  min-width:100px;
}
</style>
@stop

@section('subcontent')
  <div id="callerid">
    @if (request('tmpl') != 'body')
      <h3 style="margin-top:0px; margin-bottom:20px;">{{ trans('admin/callerid.page_title') }} {{ $phoneNumber }}</h3>
    @endif

    @php
    $params = [];
    if (request('tmpl') == 'body') {
        $params['tmpl'] = 'body';
    }

    $paramsCreate = $params;
    $paramsCreate['phoneNumber'] = $phoneNumber;
    $isCustomer = false;

    if (count($bookings)) {
        foreach ($bookings as $booking) {
            if (empty($paramsCreate['customerName']) && !empty($booking->contact_name)) {
                $paramsCreate['customerName'] = $booking->contact_name;
            }
            if (empty($paramsCreate['customerEmail']) && !empty($booking->contact_email)) {
                $paramsCreate['customerEmail'] = $booking->contact_email;
            }
            if (empty($paramsCreate['customerPhone']) && !empty($booking->contact_mobile)) {
                $paramsCreate['customerPhone'] = $booking->contact_mobile;
            }

            if ($isCustomer == false) {
                $customer = $booking->assignedCustomer();

                if (!empty($customer->id)) {
                    $paramsCreate['customerId'] = $customer->id;
                    $paramsCreate['customerName'] = $customer->name;
                    $paramsCreate['customerEmail'] = $customer->email;
                    $paramsCreate['customerPhone'] = $customer->mobile_number;
                    $isCustomer = true;
                }
            }

            if ($isCustomer == true && !empty($paramsCreate['customerName']) && !empty($paramsCreate['customerEmail']) && !empty($paramsCreate['customerPhone'])) {
                break;
            }
        }
    }
    @endphp

    @if (1)
      <div class="clearfix" style="margin-bottom:10px; padding:0 5px;">
        <h4 style="color:red; margin:0;">{{ !empty($paramsCreate['customerName']) ? $paramsCreate['customerName'] : trans('admin/callerid.new_customer') }}</h4>
      </div>

      {{-- <iframe src="{{ route('admin.bookings.create', $paramsCreate) }}" frameborder="0" height="500" width="100%" id="modal-callerid-iframe-add" scrolling="no" style="border:1px #e8e7e7 solid; margin-bottom:20px;"></iframe>
      <script>
      $(document).ready(function() {
          $('#modal-callerid-iframe-add').iFrameResize({
              heightCalculationMethod: 'lowestElement',
              log: false,
              targetOrigin: '*',
              checkOrigin: false
          });
      });
      </script> --}}

      <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
      <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
      <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
      <link rel="stylesheet" href="{{ asset_url('plugins','jquery-intl-tel-input/css/intlTelInput.min.css') }}?_dc={{ config('app.timestamp') }}">
      <link rel="stylesheet" href="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.css') }}">
      <link rel="stylesheet" href="{{ asset_url('css','eto.css') }}?_dc={{ config('app.timestamp') }}">

      @include('partials.modals.popup')

      @php $formId = 'eto-booking-create-'. time(); @endphp

      <div class="eto-wrapper-booking-create">
        <div class="eto-form eto-form-booking" id="{{ $formId }}" style="border:1px #e8e7e7 solid; margin-bottom:30px;"></div>
      </div>

      @include('booking.settings')

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

      @php
      foreach ($paramsCreate as $k => $v) {
          $paramsCreate[$k] = urlencode($v);
      }
      @endphp

      <script>
        $(document).ready(function(){
          $.LoadingOverlay('show');

          setTimeout(function() {
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
                  requestParams: {!! json_encode($paramsCreate) !!},
                });
              }
              else {console.log('ETO.Booking.Form is not initialized');}

              @if(request()->system->subscription->license_status == 'suspended')
                $('.eto-form').prepend('<div class="license-suspended-block"></div>');
              @endif
          }, 0);
        });

        $(window).load(function(){
          $.LoadingOverlay('hide');
        });
      </script>

      <div class="clearfix" style="margin-bottom:10px; padding:0 5px;">
        <h4 style="margin:0;">{{ trans('admin/callerid.latest_bookings') }}</h4>
      </div>
    @else
      <div class="clearfix" style="margin-bottom:10px; padding:0 5px;">
        <a href="{{ route('admin.bookings.create', $paramsCreate) }}" class="btn btn-sm btn-success pull-right" style="margin-top:5px;">
          {{ trans('admin/callerid.btn_add') }}
        </a>
        <h4 style="margin:15px 0 0 0;">{{ trans('admin/callerid.latest_bookings') }}</h4>
      </div>
    @endif

    <table class="table table-striped table-condensed no-margin">
      <thead>
        <tr>
          <th class="th-ref_number">{{ trans('admin/callerid.ref_number') }}</th>
          <th class="th-passenger">{{ trans('admin/callerid.passenger') }}</th>
          <th class="th-route">{{ trans('admin/callerid.route') }}</th>
          <th class="th-date">{{ trans('admin/callerid.date') }}</th>
          <th class="th-status">{{ trans('admin/callerid.status') }}</th>
          <th class="th-customer">{{ trans('admin/callerid.customer') }}</th>
          <th class="th-driver">{{ trans('admin/callerid.driver') }}</th>
        </tr>
      </thead>
      <tbody>
      @if (count($bookings))
        @foreach ($bookings as $booking)
          @php
          $customer = $booking->assignedCustomer();
          $customerLink = '';

          if (!empty($customer->id)) {
              $customerLink .= '<a href="'. route('admin.customers.index', array_merge(['id' => $customer->id], $params)) .'">';
              $customerLink .= (config('site.user_show_company_name') && $customer->company_name ? trim($customer->company_name) . ' - ' : ''). $customer->name;
              $customerLink .= '</a>';
          }

          $driver = $booking->assignedDriver();
          $driverLink = '';

          if (!empty($driver->id)) {
              $driverLink .= '<a href="'. route('admin.users.show', array_merge(['id' => $driver->id], $params)) .'">';
              $driverLink .= $driver->getName(true);
              $driverLink .= '</a>';
          }
          @endphp

          <tr>
            <td>
              <span class="td-label">{{ trans('admin/callerid.ref_number') }}:</span>
              <a href="{{ route('admin.bookings.show', array_merge(['id' => $booking->id], $params)) }}">{{ $booking->ref_number }}</a>
            </td>
            <td>
              <span class="td-label">{{ trans('admin/callerid.passenger') }}:</span>
              {{ $booking->getContactFullName() }}
            </td>
            <td>
              <span class="td-label">{{ trans('admin/callerid.route') }}:</span>
              <div class="eto-address-more">{!! $booking->getFrom() !!} - {!! $booking->getTo() !!}</div>
            </td>
            <td>
              <span class="td-label">{{ trans('admin/callerid.date') }}:</span>
              {{ App\Helpers\SiteHelper::formatDateTime($booking->date) }}
            </td>
            <td>
              <span class="td-label">{{ trans('admin/callerid.status') }}:</span>
              {!! $booking->getStatus('label') !!}
            </td>
            <td>
              <span class="td-label">{{ trans('admin/callerid.customer') }}:</span>
              {!! $customerLink !!}
            </td>
            <td>
              <span class="td-label">{{ trans('admin/callerid.driver') }}:</span>
              {!! $driverLink !!}
            </td>
          </tr>
        @endforeach
      @else
        <td colspan="7" style="text-align:center;">{{ trans('admin/callerid.msg_no_bookings') }}</td>
      @endif
      </tbody>
    </table>

  </div>
@stop

@section('subfooter')
  <script src="{{ asset_url('plugins','jquery-readmore/readmore.min.js') }}"></script>

  <script>
  $(document).ready(function(){
      $('.eto-address-more').readmore({
          collapsedHeight: 40,
          moreLink: '<a href="#" class="eto-address-more-link">{{ trans('booking.buttons.more') }}</a>',
          lessLink: '<a href="#" class="eto-address-more-link">{{ trans('booking.buttons.less') }}</a>'
      });
  });
  </script>
@stop
