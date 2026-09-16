@extends('admin.index')

@section('title', trans('admin/dispatch.page_title'))
@section('subtitle', /*'<i class="fa fa-flag"></i> '.*/ trans('admin/dispatch.page_title'))
@section('subclass', 'dispatch-wrapper')

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.dataTables.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.bootstrap.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.dataTables.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset_url('plugins','x-editable/css/bootstrap-editable.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','goldenlayout/goldenlayout.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','jquery-intl-tel-input/css/intlTelInput.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.css') }}">

    @if(config('site.admin_booking_listing_highlight'))
        @php
        $statusList = (new \App\Models\BookingRoute)->getStatusList();
        $html = '';
        foreach ($statusList as $vS) {
            $color = \App\Helpers\SiteHelper::colorBlendByOpacity($vS->color, 10);
            if($color == false) { $color = $vS->color; }
            $html .= '.row-booking-status-'. $vS->value .' {background-color:#'. $color .';}';
        }
        @endphp
        @if ($html)<style>{{ $html }}</style>@endif
    @endif
@endsection

@section('subcontent')

    @if(config('site.allow_dispatch'))
        @php
        $text = '';
        if (config('site.expiry_dispatch')) {
            if( \Carbon\Carbon::parse(config('site.expiry_dispatch'))->lte(\Carbon\Carbon::now()) ) {
                $text = 'Your trial has expired. If you would like to keep using dispatch, please <a href="https://easytaxioffice.co.uk/pricing" target="_blank">purchase</a> the license';
            }
            else {
                $text = 'Your dispatch trial will expire on '. \App\Helpers\SiteHelper::formatDateTime(\Carbon\Carbon::parse(config('site.expiry_dispatch'))->toDateTimeString(), 'date') .'. <a href="https://easytaxioffice.co.uk/pricing" target="_blank">Purchase now</a>';
            }
        }
        if ($text) {
            echo '<div style="font-size: 14px; color: #ad2e2e; background: #f4dfdf; padding: 2px 10px; border-bottom: 1px #d9c1c1 solid;">'. $text .'</div>';
        }
        @endphp

        @include('partials.modals.popup')

        <div class="eto-modal-booking-edit modal" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        @if(request()->system->subscription->license_status == 'suspended')
                            <div class="license-suspended-block"></div>
                        @endif
                        <div class="eto-form eto-modal eto-form-booking" id=""></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="eto-modal-booking-tracking modal" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        <div class="eto-booking-tracking-map" style="height: 500px"></div>
                    </div>
                </div>
            </div>
        </div>

        @include('dispatch.settings-map')
        @include('booking.settings')

        <div id="dispatch"></div>
    @else
        <div style="font-size: 14px; color: #ad2e2e; background: #f4dfdf; padding: 2px 10px; border-bottom: 1px #d9c1c1 solid;">Please purchase appropriate license to use dispatch module, please visit <a href="https://easytaxioffice.co.uk/pricing" target="_blank">this</a> page for more details.</div>
    @endif

@endsection


@section('subfooter')

    @if(config('site.allow_dispatch') && !(
        config('site.expiry_dispatch') && \Carbon\Carbon::parse(config('site.expiry_dispatch'))->lte(\Carbon\Carbon::now())
    ))
    <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/jquery.dataTables.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/dataTables.bootstrap.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/dataTables.buttons.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.bootstrap.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.colVis.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.server-side.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/ColReorder/js/dataTables.colReorder.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset_url('plugins','x-editable/js/bootstrap-editable.min.js') }}"></script>
    <script src="{{ asset_url('plugins','goldenlayout/goldenlayout.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-readmore/readmore.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-intl-tel-input/js/intlTelInput-jquery.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','jquery-sortable/jquery-sortable-min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-resize/jquery.resize.js') }}"></script>
    <script src="{{ asset_url('plugins','typeahead/typeahead.bundle.min.js') }}"></script>

    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry&language={{ app()->getLocale() }}"></script>
    <script src="{{ asset_url('plugins','markerwithlabel/markerwithlabel.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-resizable/jquery-resizable.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.min.js') }}"></script>

    <script src="{{ asset_url('js','assign-driver.js') }}"></script>
    <script src="{{ asset_url('js','assign-fleet.js') }}"></script>
    <script src="{{ asset_url('js','eto/eto-dispatch.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-map.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-booking.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-user.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-user-driver.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-user-fleet.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-form.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-booking-form.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-notification.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-routehistory.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script>
    var etoBookingRequestData = {
        statusList: {!! (new \App\Models\BookingRoute)->getStatusList('json') !!},
        fleetList: {!! \App\Helpers\BookingHelper::getFleetList('json') !!},
        driverList: {!! \App\Helpers\BookingHelper::getDriverList('json') !!},
        vehicleList: {!! \App\Helpers\BookingHelper::getVehicleList(null, 'json') !!},
    };

    function updateEditable() {
        if ($(window).width() >= 500) {
            $.fn.editable.defaults.mode = 'popup';
        }
        else {
            $.fn.editable.defaults.mode = 'inline';
        }
    }

    $(document).ready(function(){
        $.LoadingOverlay('show');

        if (typeof ETO.Dispatch != "undefined") {
            ETO.Dispatch.init({init: ['google']});
        } else {
            console.log('ETO.Dispatch is not initialized');
        }

        if (typeof ETO.Booking != "undefined" && typeof ETO.Booking.init != "undefined") {
            ETO.Booking.init();
        } else {
            console.log('ETO.Booking is not initialized');
        }

        if (typeof ETO.Map != "undefined" && typeof ETO.Map.init != "undefined") {
            ETO.Map.init();
        } else {
            console.log('ETO.Map is not initialized');
        }

        if (typeof ETO.Form != "undefined" && typeof ETO.Form.init != "undefined") {
            ETO.Form.init();
        } else {
            console.log('ETO.Form is not initialized');
        }

        if (typeof ETO.Booking.Form != "undefined" && typeof ETO.Booking.Form.init != "undefined") {
            ETO.Booking.Form.init();
        }
        else {
            console.log('ETO.Booking.Form is not initialized');
        }

        if (typeof ETO.Notifications != "undefined" && typeof ETO.Notifications.init != "undefined") {
            ETO.Notifications.init();
        } else {
            console.log('ETO.Notifications is not initialized');
        }

        if (typeof ETO.Routehistory != "undefined" && typeof ETO.Routehistory.init != "undefined") {
            ETO.Routehistory.init();
        } else {
            console.log('ETO.Routehistory is not initialized');
        }

        ETO.Form.setTouchSpin('#mapRefresh', false, 20, 1, false, null, true, 'btn btn-primary');
        ETO.Form.setTouchSpin('#driverRefresh', false, 20, 1, false, null, true, 'btn btn-primary');

        updateEditable();

        @if(request()->system->subscription->license_status == 'suspended')
            $('.eto-form').closest('.lm_content').prepend('<div class="license-suspended-block"></div>');
        @endif
    });

    $(window).load(function(){
        $.LoadingOverlay('hide');
        updateEditable();
    });
    </script>
    @endif

@endsection
