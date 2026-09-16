@extends('admin.index')

@section('title', trans('admin/bookings.page_title'))
@section('subtitle', /*'<i class="fa fa-tasks"></i> '.*/ trans('admin/bookings.page_title'))


@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.dataTables.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.bootstrap.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.dataTables.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','x-editable/css/bootstrap-editable.css') }}">

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
  <div class="pageContainer" id="bookings">
    @include('partials.modals.popup')

    <div class="pageFilters pageFiltersHide" style="margin-bottom:10px;">

      <form method="post" class="form-inline">
        <a href="#" class="pull-right btnClose" title="Close" onclick="$('.pageFilters').toggle(); return false;">
          <i class="fa fa-times"></i>
        </a>
        <div class="form-group filter-service_id">
          <label for="filter-service_id">Service Type</label>
          <select name="filter-service_id[]" id="filter-service_id" multiple="multiple" size="1" class="form-control select2" data-placeholder="Service Type" data-allow-clear="true"></select>
        </div>
        <div class="form-group">
          <label for="filter-source">Source</label>
          <select name="filter-source[]" id="filter-source" multiple="multiple" size="1" class="form-control select2" data-placeholder="Source" data-allow-clear="true"></select>
        </div>
        <div class="form-group">
          <label for="filter-status">Status</label>
          <select name="filter-status[]" id="filter-status" multiple="multiple" size="1" class="form-control select2" data-placeholder="Status" data-allow-clear="true"></select>
        </div>
        <div class="form-group">
          <label for="filter-payment_method">Payment method</label>
          <select name="filter-payment_method[]" id="filter-payment_method" multiple="multiple" size="1" class="form-control select2" data-placeholder="Payment method" data-allow-clear="true"></select>
        </div>
        <div class="form-group">
          <label for="filter-payment_status">Payment status</label>
          <select name="filter-payment_status[]" id="filter-payment_status" multiple="multiple" size="1" class="form-control select2" data-placeholder="Payment status" data-allow-clear="true"></select>
        </div>
        <div class="form-group @if(!config('eto.allow_fleet_operator') || auth()->user()->hasRole('admin.fleet_operator')) hide @endif">
          <label for="filter-fleet-name">Fleet Operator</label>
          <select name="filter-fleet-name[]" id="filter-fleet-name" multiple="multiple" size="1" class="form-control select2" data-placeholder="Fleet Operator" data-allow-clear="true"></select>
        </div>
        <div class="form-group">
          <label for="filter-driver-name">Supplier</label>
          <select name="filter-driver-name[]" id="filter-driver-name" multiple="multiple" size="1" class="form-control select2" data-placeholder="Driver" data-allow-clear="true"></select>
        </div>
        <div class="form-group">
          <label for="filter-customer-name">Customer</label>
          <select name="filter-customer-name[]" id="filter-customer-name" multiple="multiple" size="1" class="form-control select2" data-placeholder="Customer" data-allow-clear="true"></select>
        </div>
        <div class="form-group">
          <label for="filter-start-date">From</label>
          <input type="text" name="filter-start-date" id="filter-start-date" class="form-control datepicker" placeholder="From">
        </div>
        <div class="form-group">
          <label for="filter-end-date">To</label>
          <input type="text" name="filter-end-date" id="filter-end-date" class="form-control datepicker" placeholder="To">
        </div>
        <div class="form-group">
          <label for="filter-date-type">Date type</label>
          <select name="filter-date-type" id="filter-date-type" class="form-control select2" data-placeholder="Date type" data-allow-clear="true"></select>
        </div>
        <div class="form-group">
          <label for="filter-keywords">Keywords</label>
          <input type="text" name="filter-keywords" id="filter-keywords" class="form-control" placeholder="Keywords">
        </div>

        <div class="form-group hide">
          <label for="filter-scheduled_route_id">Scheduled route ID</label>
          <input type="text" name="filter-scheduled_route_id" id="filter-scheduled_route_id" class="form-control" placeholder="Scheduled route ID">
        </div>
        <div class="form-group hide">
          <label for="filter-parent_booking_id">Parent booking ID</label>
          <input type="text" name="filter-parent_booking_id" id="filter-parent_booking_id" class="form-control" placeholder="Parent booking ID">
        </div>
        <div class="form-group filter-booking_type">
          <label for="filter-booking_type">Booking type</label>
          <select name="filter-booking_type[]" id="filter-booking_type" multiple="multiple" size="1" class="form-control select2" data-placeholder="Booking type" data-allow-clear="true"></select>
        </div>

        @permission('admin.bookings.export')
        <div class="form-group" style="display:none;">
          <div class="dropdown export_button" style="display:inline-block; margin-left:8px; float:left;">
            <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-download"></i> <span>Export</span>
            <span class="caret"></span></button>
            <ul class="dropdown-menu">
              <li><a href="#xlsx" onclick="exportType('xlsx'); return false;">MS Excel XLSx</a></li>
              <li><a href="#xls" onclick="exportType('xls'); return false;">MS Excel XLS</a></li>
              <li><a href="#csv" onclick="exportType('csv'); return false;">CSV</a></li>
              {{-- <li><a href="#pdf" onclick="exportType('pdf'); return false;">PDF</a></li> --}}
              {{-- <li><a href="#ods" onclick="exportType('ods'); return false;">ODS</a></li> --}}
              {{-- <li><a href="#html" onclick="exportType('html'); return false;">HTML</a></li> --}}
              <li><a href="#invoice_download" onclick="exportType('invoice_download'); return false;">Download invoice(s)</a></li>
              <li><a>---------------</a></li>
              <li><a href="#invoice_send" onclick="if( confirm('Are you sure you want to do it?') ) { exportType('invoice_send'); return false; } else { return false; }">Send invoice to all filtered customers via email</a></li>
            </ul>
          </div>
        </div>
        @endpermission

       <div class="form-group" style="display:none !important;">
         <label for="filter-summary" class="checkbox-inline btn btn-success" style="margin-left:10px; margin-right:10px;">
           <input type="checkbox" name="filter-summary" id="filter-summary" value="1" style="margin-left:0; position:relative; margin-right:5px;"> View Reports
         </label>
       </div>

        <div class="clearfix">
          <div class="form-group">
            <button type="button" class="btn btn-default btnSearch" title="" onclick="$('.pageFilters #filter-summary').attr('checked', false);"><i class="fa fa-search"></i> <span>Search</span></button>
            <button type="button" class="btn btn-link btnReset" title=""><i class="fa fa-times"></i> <span>Reset</span></button>
          </div>
        </div>

      </form>

    </div>
    <div class="pageContent">

      <table class="table table-hover" id="dtable" style="width:100%;"></table>

      <div id="dmodal" class="modal fade" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body"></div>
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

      <div id="exportIframeContainer"></div>
    </div>
  </div>
@endsection


@section('subfooter')
  <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
  <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
  <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
  <script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('plugins','jquery-serializejson/jquery.serializejson.min.js') }}"></script>
  <script src="{{ asset_url('plugins','jquery-readmore/readmore.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/jquery.dataTables.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/dataTables.bootstrap.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/dataTables.buttons.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.bootstrap.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.colVis.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.server-side.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/ColReorder/js/dataTables.colReorder.min.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
  <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
  <script src="{{ asset_url('plugins','x-editable/js/bootstrap-editable.min.js') }}"></script>

  <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry&language={{ app()->getLocale() }}"></script>
  <script src="{{ asset_url('plugins','markerwithlabel/markerwithlabel.js') }}"></script>
  <script src="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.min.js') }}"></script>

  <script src="{{ asset_url('js','assign-driver.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','assign-fleet.js') }}"></script>
  <script src="{{ asset_url('js','eto/eto-booking.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','eto/eto-notification.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','eto/eto-routehistory.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','eto/eto-user.js') }}?_dc={{ config('app.timestamp') }}"></script>
  <script src="{{ asset_url('js','eto/eto-user-driver.js') }}?_dc={{ config('app.timestamp') }}"></script>

  @php
  $pageName = 'all';
  if (config('eto.allow_fleet_operator') && !auth()->user()->hasRole('admin.fleet_operator')) {
      $defaultOrder = [42, 'desc'];
  }
  else {
      $defaultOrder = [40, 'desc'];
  }

  switch(request('page')) {
      case 'next24':
          $pageName = 'next24';
          $defaultOrder = [2, 'asc'];
      break;
      case 'latest':
          $pageName = 'latest';
      break;
      case 'requested':
          $pageName = 'requested';
      break;
      case 'completed':
          $pageName = 'completed';
      break;
      case 'canceled':
          $pageName = 'canceled';
      break;
      case 'trash':
          $pageName = 'trash';
      break;
  }
  @endphp

  <script>
  var etoBookingRequestData = {
      statusList: {!! (new \App\Models\BookingRoute)->getStatusList('json') !!},
      fleetList: {!! \App\Helpers\BookingHelper::getFleetList('json') !!},
      driverList: {!! \App\Helpers\BookingHelper::getDriverList('json') !!},
      vehicleList: {!! \App\Helpers\BookingHelper::getVehicleList(null, 'json') !!},
      serviceList: {!! \App\Helpers\BookingHelper::getServiceList('json') !!},
      sourceList: {!! \App\Helpers\BookingHelper::getSourceList('json') !!},
      paymentMethodList: {!! \App\Helpers\BookingHelper::getPaymentMethodList('json') !!},
      paymentStatusList: {!! (new \App\Models\Transaction)->getStatusList('json') !!},
      customerList: {!! \App\Helpers\BookingHelper::getCustomerList('json') !!},
      dateTypeList: {!! \App\Helpers\BookingHelper::getDateTypeList('json') !!},
  };

  // Filter
  function filterTable(isDelete, isPageReset) {
      $('#bookings [title]').tooltip('hide');
      $('#bookings [data-toggle="tooltip"]').tooltip('hide');
      $('#bookings [data-toggle="popover"]').popover('hide');

      var filter_data = $('.pageFilters form').serializeJSON();
      filter_data = JSON.stringify(filter_data);
      $.cookie('admin_bookings_filter_{{ str_slug(request('page', 'all'), '_') }}', filter_data, {path: EasyTaxiOffice.cookiePath, secure: EasyTaxiOffice.cookieSecure, same_site: EasyTaxiOffice.cookieSameSite});

      getFilters();

      var values = $('.pageFilters form').serialize();
      var isDelete = isDelete ? true : false;
      var isPageReset = isPageReset ? true : false;
      var dTable = $('#dtable').DataTable();
      var dInfo = dTable.page.info();
      var dRows = dTable.rows({page:'current'}).nodes();
      // console.log(dRows.any(), dRows.length, isDelete, dInfo);

      dTable.search(values);
      if (dInfo.page > 0 && dRows.length - 1 <= 0 && isDelete === true) {
          dTable.page('previous');
      }
      dTable.draw(isPageReset);
  }

  function removeFilter(id, val) {
      $('#'+ id +' option[value="'+ val +'"]').attr('selected', false).trigger('change');
  }

  function getFilters() {
      var html = '';
      var filters = [];

      // Service Type
      $('#filter-service_id option:selected').each(function(index, item) {
          filters.push({
              click: 'removeFilter(\'filter-service_id\', \''+ $(this).val() +'\');',
              title: 'Service',
              name: $(this).text()
          });
      });

      // Source
      $('#filter-source option:selected').each(function(index, item) {
          filters.push({
              click: 'removeFilter(\'filter-source\', \''+ $(this).val() +'\');',
              title: 'Source',
              name: $(this).text()
          });
      });

      // Status
      $('#filter-status option:selected').each(function(index, item) {
          filters.push({
              click: 'removeFilter(\'filter-status\', \''+ $(this).val() +'\');',
              title: 'Status',
              name: $(this).text()
          });
      });

      // Payment method
      $('#filter-payment_method option:selected').each(function(index, item) {
          filters.push({
              click: 'removeFilter(\'filter-payment_method\', \''+ $(this).val() +'\');',
              title: 'Payment Method',
              name: $(this).text()
          });
      });

      // Payment status
      $('#filter-payment_status option:selected').each(function(index, item) {
          filters.push({
              click: 'removeFilter(\'filter-payment_status\', \''+ $(this).val() +'\');',
              title: 'Payment Status',
              name: $(this).text()
          });
      });

      // Driver
      $('#filter-driver-name option:selected').each(function(index, item) {
          filters.push({
              click: 'removeFilter(\'filter-driver-name\', \''+ $(this).val() +'\');',
              title: 'Supplier',
              name: $(this).text()
          });
      });

      // Customer
      $('#filter-customer-name option:selected').each(function(index, item) {
          filters.push({
              click: 'removeFilter(\'filter-customer-name\', \''+ $(this).val() +'\');',
              title: 'Customer',
              name: $(this).text()
          });
      });

      // Fleet Operator
      $('#filter-fleet-name option:selected').each(function(index, item) {
          filters.push({
              click: 'removeFilter(\'filter-fleet-name\', \''+ $(this).val() +'\');',
              title: 'Fleet Operator',
              name: $(this).text()
          });
      });

      // From
      var start_date = $('#filter-start-date').val();
      if( start_date ) {
          filters.push({
              click: '$(\'#filter-start-date\').val(\'\');',
              title: 'From',
              name: 'From: '+ start_date
          });
      }

      // To
      var end_date = $('#filter-end-date').val();
      if( end_date ) {
          filters.push({
              click: '$(\'#filter-end-date\').val(\'\');',
              title: 'To',
              name: 'To: '+ end_date
          });
      }

      // Date Type
      $('#filter-date-type option:selected').each(function(index, item) {
          if( $(this).val() ) {
            filters.push({
                click: 'removeFilter(\'filter-date-type\', \''+ $(this).val() +'\');',
                title: 'Date Type',
                name: $(this).text()
            });
          }
      });

      // Keywords
      var keywords = $('#filter-keywords').val();
      if( keywords ) {
          filters.push({
              click: '$(\'#filter-keywords\').val(\'\');',
              title: 'Keywords',
              name: keywords
          });
      }

      // Booking Type
      $('#filter-booking_type option:selected').each(function(index, item) {
          filters.push({
              click: 'removeFilter(\'filter-booking_type\', \''+ $(this).val() +'\');',
              title: 'Booking type',
              name: $(this).text()
          });
      });

      // Scheduled route id
      if( $('#filter-scheduled_route_id').val() ) {
          @php
          $title = 'Scheduled route';
          $name = $title;
          if (request('scheduled_route')) {
              $scheduled = \App\Models\ScheduledRoute::find(request('scheduled_route'));
              if (!empty($scheduled->id)) {
                  $name = $scheduled->getName();
              }
          }
          @endphp
          filters.push({
              click: '$(\'#filter-scheduled_route_id\').val(\'\');',
              name: '{{ $name }}',
              title: '{{ $title }}',
          });
      }

      // Parent booking id
      if( $('#filter-parent_booking_id').val() ) {
          filters.push({
              click: '$(\'#filter-parent_booking_id\').val(\'\');',
              name: 'Group booking',
              title: '',
          });
      }

      $.each(filters, function(index, item) {
          html += '<span onclick="'+ item.click +' filterTable();" title="'+ item.title +'">'+ item.name +'</span>';
      });

      $('#used-filters').remove();

      if( html ) {
          $('#dtable_wrapper .dataTablesBody').before('<div id="used-filters">'+ html +'</div>');
      }
  }

  // Delete
  function deleteRecord(id, booking_children) {
    var html = '';
    var title = 'Are you sure?';

    if (booking_children > 0) {
        html += '<div style="margin-bottom:20px;" class="eto-booking-delete-parent"><span style="font-weight:bold; color:red;">Important!</span> Please note that deleting main booking will also cause deleting all sub-bookings associated with it.</div>';
    }
    html += '<button type="button" class="btn btn-danger btnConfirm" title="Delete"><i class="fa fa-trash"></i> Delete</button> ';
    html += '<button type="button" class="btn btn-link btnCancel" title="Cancel">Cancel</button>';

    $('#dmodal').addClass('modal-booking-delete');
    $('#dmodal .modal-title').html(title);
    $('#dmodal .modal-body').html(html);
    $('#dmodal').modal('show');

    $('#dmodal .btnConfirm').on('click', function() {
      $.ajax({
        headers : {
          'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
        },
        url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
          task: 'bookings',
          action: 'destroy',
          id: id
        },
        success: function(response) {
          if(response.success) {
            filterTable(true);
            $('#dmodal').modal('hide');
          }
          else {
            alert('The booking could not be deleted!');
          }
        },
        error: function(response) {
          // Msg
        }
      });
    });

    $('#dmodal .btnCancel').on('click', function() {
      $('#dmodal').modal('hide');
    });
  }

  // Export
  function exportType(id) {
    if( id ) {
        // var values = $('.pageFilters form').serialize();
        // var params = {
        //     search : {
        //         value : values,
        //         regex : false
        //     }
        // };
        // var str = jQuery.param( params );
        // var url = EasyTaxiOffice.appPath +'/etov2?apiType=backend&task=bookings&action=list&exportType='+ id +'&start=0&length=100000000&draw=1&'+ str;
        // $('#exportIframeContainer').html('<iframe src="'+ url +'" frameborder="0" scrolling="no" id="exportIframe" style="display:none;"></iframe>');

        var dTable = $('#dtable').DataTable();
        var params = $.extend(true, dTable.ajax.params(), {
            search : {
                value : $('.pageFilters form').serialize(),
                regex : false
            },
            action: 'list',
            exportType: id,
            page: '{{ request('page', 'all') }}',
            start: 0,
            length: 100000000,
            draw: 1,
            columnsVisibility: dTable.columns().visible().join(',')
        });
        var url = EasyTaxiOffice.appPath +'/etov2?apiType=backend&task=bookings';
        var payload = decodeURI($.param(params)).split('&');
        var form = $('<form>', {'method': 'POST', 'action': url}).hide();
        $.each(payload, function(k, v) {
            v = v.split('=');
            form.append($('<input>', {'type': 'hidden', 'name': String(v[0]), 'value': String(decodeURIComponent(v[1]))}));
        });
        // $('body').append(form);
        // form.submit();
        // form.remove();

        var $iframe = $('<iframe src="about:blank" frameborder="0" scrolling="no" id="exportIframe" style="display:none;"></iframe>');
        $('#exportIframeContainer').html('').append($iframe);

        setTimeout(function() {
            $iframe.contents().find('body').html(form);
            form.submit();
        }, 0);

        // This code is not working in some browsers
        // $iframe.on('load', function() {
        //     $iframe.off('load');
        //     $(this).contents().find('body').html(form);
        //     form.submit();
        // });
    }
    return false;
  }

  function updateTableHeight() {
     var height = parseFloat($('.wrapper > .content-wrapper').css('min-height')) -
                  $('#dtable_wrapper > .topContainer').height() -
                  $('#dtable_wrapper > .bottomContainer').height() -
                  $('.dataTables_scrollHead').height() - 40;

      if( height < 200 ) {
         height = 200;
      }
      // if( parseFloat($('.wrapper > .content-wrapper').css('min-height')) > $(window).height() ) {
      //    height = 0;
      // }
      $('#bookings .dataTables_scrollBody').css({'min-height': height +'px'});
  }

  function updateEditable() {
      if ($(window).width() >= 500) {
          $.fn.editable.defaults.mode = 'popup';
      }
      else {
          $.fn.editable.defaults.mode = 'inline';
      }
  }

  function modalIframe(el) {
      var url = $(el).attr('href') + (($(el).attr('href').indexOf('?') < 0) ? '?' : '&') + 'tmpl=body';
      var title = $(el).attr('title') ? $(el).attr('title') : $(el).attr('data-original-title');
      var html = '<iframe src="'+ url +'" frameborder="0" height="400" width="100%"></iframe>';
      var modal = $('#modal-popup');

      if( $(el).hasClass('btnView') ) {
          modal.addClass('modal-booking-view');
      }
      else if( $(el).hasClass('btnEdit') ) {
          modal.addClass('modal-booking-edit');
      }
      else if( $(el).hasClass('btnCopy') ) {
          modal.addClass('modal-booking-copy');
      }
      else if( $(el).hasClass('btnInvoice') ) {
          modal.addClass('modal-booking-invoice');
      }
      else if( $(el).hasClass('btnSMS') ) {
          modal.addClass('modal-booking-sms');
      }
      else if( $(el).hasClass('btnFeedback') ) {
          modal.addClass('modal-booking-feedback');
      }
      else if( $(el).hasClass('btnMeetingBoard') ) {
          modal.addClass('modal-booking-meeting-board');
      }

      if(modal.find('iframe').length > 0) {
          modal.find('iframe').attr('src', url);
      }
      else {
          modal.find('.modal-body').html(html);
          modal.find('iframe').iFrameResize({
              heightCalculationMethod: 'lowestElement',
              log: false,
              targetOrigin: '*',
              checkOrigin: false
          });
      }

      modal.find('.modal-title').html(title);
      modal.modal('show');

      return false;
  }

  function markAsRead(id, is_read) {
      ETO.ajax('booking2/markBooking/' + id, {
          data: {is_read: is_read == 1 ? 0 : 1},
          async: false,
          success: function(data) {},
          complete: function() {}
      });
      var dTable = new $.fn.dataTable.Api("#dtable");
      dTable.ajax.reload(null, false);
  }

  function prepareRecord(id, bookingChildren, table, action) {
      ETO.ajax('etov2?apiType=backend', {
          data: {
              task: 'bookings',
              action: action,
              id: id,
              bookingChildren: bookingChildren
          },
          success: function(response) {
              if (response.success) {
                  table.draw();
              }
              else {
                  alert('The booking could not be deleted!');
              }
          },
      });
  }

  function loadFilters(response) {
      html = '';

      // Service Type
      html = '';
      if (response.services) {
          $.each(response.services, function(index, item) {
              html += '<option value="'+ item.id +'">'+ item.name +'</option>';
          });
      }
      $('#filter-service_id').html(html);

      // Source
      html = '';
      if (response.source) {
          $.each(response.source, function(index, item) {
              html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
      }
      $('#filter-source').html(html);

      // Status
      html = '';
      if (response.status) {
          $.each(response.status, function(index, item) {
              html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
      }
      $('#filter-status').html(html);

      // Payment method
      html = '';
      if (response.payment_method) {
          $.each(response.payment_method, function(index, item) {
              html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
      }
      $('#filter-payment_method').html(html);

      // Payment status
      html = '';
      if (response.payment_status) {
          $.each(response.payment_status, function(index, item) {
              html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
      }
      $('#filter-payment_status').html(html);

      // Driver
      html = '';
      if (response.driver) {
          $.each(response.driver, function(index, item) {
              html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
      }
      $('#filter-driver-name').html(html);

      // Customer
      html = '';
      if (response.customer) {
          $.each(response.customer, function(index, item) {
              html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
      }
      $('#filter-customer-name').html(html);

      // Fleet Operator
      html = '';
      if (response.fleetList) {
          $.each(response.fleetList, function(index, item) {
              html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
      }
      $('#filter-fleet-name').html(html);

      // Date Type
      html = '';
      if (response.dateType) {
          $.each(response.dateType, function(index, item) {
              html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
      }
      $('#filter-date-type').html(html);

      // Booking Type
      html = '<option value="parent">Parent booking</option>';
      html += '<option value="child">Child booking</option>';
      $('#filter-booking_type').html(html);

      // Load filters from cookie
      var filter_data = $.cookie('admin_bookings_filter_{{ str_slug(request('page', 'all'), '_') }}');
      if( filter_data ) {
          filter_data = JSON.parse(filter_data);
          $.each(filter_data, function(i, item){
              $('#'+ i).val(item);
          });
      }

      @if( request('user') )
          @php
          $customer = \App\Models\User::select('id', 'name')->find(request('user'));
          if( !empty($customer->id) ) {
              echo "if( $('#filter-customer-name option[value=". $customer->id ."]').length <= 0 ) { $('#filter-customer-name').append('<option value=". $customer->id .">". $customer->name ."</option>'); }";
          }
          @endphp
          $('#filter-customer-name').val({{ request('user') }});
      @endif

      @if( request('driver') )
          @php
          $driver = \App\Models\User::select('id', 'name')->find(request('driver'));
          if( !empty($driver->id) ) {
              echo "if( $('#filter-driver-name option[value=". $driver->id ."]').length <= 0 ) { $('#filter-driver-name').append('<option value=". $driver->id .">". $driver->name ."</option>'); }";
          }
          @endphp
          $('#filter-driver-name').val({{ request('driver') }});
      @endif

      @if( request('fleet') )
          @php
          $fleet = \App\Models\User::select('id', 'name')->find(request('fleet'));
          if( !empty($fleet->id) ) {
              echo "if( $('#filter-fleet-name option[value=". $fleet->id ."]').length <= 0 ) { $('#filter-fleet-name').append('<option value=". $fleet->id .">". $fleet->name ."</option>'); }";
          }
          @endphp
          $('#filter-fleet-name').val({{ request('fleet') }});
      @endif

      @if( request('search') )
          $('#filter-keywords').val('{{ request('search') }}');
      @endif

      @if( request('scheduled_route') )
          $('#filter-scheduled_route_id').val('{{ request('scheduled_route') }}');
      @endif

      setTimeout(function() {
          filterTable();
      },0);
  }

  $(document).ready(function(){
    if(typeof ETO.Notifications != "undefined") {
        if (typeof ETO.Notifications.init != "undefined") {
            ETO.Notifications.init({
                init: ['google', 'icons'],
            });
        }
        if (typeof ETO.Routehistory.init != "undefined") {
            ETO.Routehistory.init({});
        }
    }

    // Popup standard
    $('#dmodal').modal({
        show: false
    })
    .on('hidden.bs.modal', function(){
        $(this).removeClass(
          'modal-booking-delete '+
          'modal-booking-add '+
          'modal-booking-report'
        );
    });

    // Popup
    $('#modal-popup').modal({
        show: false,
    })
    .on('hidden.bs.modal', function(e) {
        if($(this).hasClass('modal-booking-edit') || $(this).hasClass('modal-booking-copy')) {
            filterTable();
        }

        $(this).find('iframe').attr('src', 'about:blank').contents().find('body').append('');

        $(this).removeClass(
          'modal-booking-view '+
          'modal-booking-edit '+
          'modal-booking-copy '+
          'modal-booking-invoice '+
          'modal-booking-sms '+
          'modal-booking-feedback '+
          'modal-booking-meeting-board'
        );
    });

    // Select
    $('.pageFilters .select2').select2({
        minimumResultsForSearch: 'Infinity'
    });

    // Date picker
    $('.pageFilters .datepicker').daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      timePicker: true,
      timePicker24Hour: {{ config('site.time_format') == 'H:i' ? 'true' : 'false' }},
      autoUpdateInput: false,
      locale: {
        format: 'YYYY-MM-DD HH:mm',
        firstDay: {{ config('site.start_of_week') }}
      }
    })
    .on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm')).change();
    });

    // Toggle filters
    $('.pageTitle .btnFilters').on('click', function() {
      $('.pageFilters').toggle();
    });

    // Reset form
    $('.btnReset').on('click', function(e) {
      $('.pageFilters form input[type="text"]').val('');
      $('.pageFilters form #filter-summary').removeAttr('checked');
      $('.pageFilters form input[type="text"]').trigger('change');

      $('.pageFilters form select.select2').each(function( index ) {
        $(this).val([]).trigger('change');
        // $(this).val(null).trigger('change');
      });

      // $('#dtable').DataTable().search('').draw();
      $('#dtable').DataTable().search('');
      filterTable(false, true);
      e.preventDefault();
    });

    // Filter table
    $('.pageFilters .btnSearch').on('click', function(e) {
      filterTable(false, true);
      e.preventDefault();
    });

    $('.pageFilters form').submit(function(e) {
      filterTable();
      e.preventDefault();
    });

    // Display labels
    $('.pageFilters form').find('input, select').on('change', function(e) {
      if( $(this).val() ) {
        $(this).parent('.form-group').find('label').show();
      }
      else {
        $(this).parent('.form-group').find('label').hide();
      }
      e.preventDefault();
    });

    // Load filters
    loadFilters({
        services: etoBookingRequestData.serviceList,
        source: etoBookingRequestData.sourceList,
        status: etoBookingRequestData.statusList,
        payment_method: etoBookingRequestData.paymentMethodList,
        payment_status: etoBookingRequestData.paymentStatusList,
        driver: etoBookingRequestData.driverList,
        customer: etoBookingRequestData.customerList,
        fleetList: etoBookingRequestData.fleetList,
        dateType: etoBookingRequestData.dateTypeList,
    });

    // $.ajax({
    //   headers : {
    //     'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
    //   },
    //   url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
    //   type: 'POST',
    //   dataType: 'json',
    //   cache: false,
    //   data: {
    //     task: 'bookings',
    //     action: 'init',
    //     loadFilter: 'all'
    //   },
    //   success: function(response) {
    //     loadFilters(response);
    //   },
    //   error: function(response) {
    //     // Msg
    //   }
    // });

    // State start
    $('body').on('click', '.eto-booking-listing-clear-state', function() {
        if (confirm('Are you sure you would like to reset current columns visibility and sorting settings?') == true) {
            dtStateSave(null, true);
        }
    });

    var dtPageKey = '{{ $pageName }}';
    var dtStorageKey = 'ETO_admin_bookings_'+ dtPageKey +'_1';
    if (ETO.config.eto_booking && ETO.config.eto_booking.admin_bookings_state && ETO.config.eto_booking.admin_bookings_state) {
        var dtCurrentState = dtFixJson(ETO.config.eto_booking.admin_bookings_state);
    } else {
        var dtCurrentState = null;
    }
    var dtIsFirstLoad = true;
    var dtDelayTimer = null;

    function dtFixJson(data) {
        if (data) {
            data = JSON.stringify(data);
            data = data.replace(/\"true\"/g, 'true');
            data = data.replace(/\"false\"/g, 'false');
            data = JSON.parse(data);
        }
        return data;
    }

    function dtStateSave(data, clear) {
        if (dtIsFirstLoad) {
            dtIsFirstLoad = false;
            return false;
        }

        window.localStorage.setItem(dtStorageKey, JSON.stringify(data));
        var changed = false;

        if (typeof data != 'undefined' && data != null && typeof dtCurrentState != 'undefined' && dtCurrentState != null) {
            if (JSON.stringify(data.ColReorder) != JSON.stringify(dtCurrentState.ColReorder)) {
                changed = true;
            }
            else if (JSON.stringify(data.columns) != JSON.stringify(dtCurrentState.columns)) {
                changed = true;
            }
            else if (JSON.stringify(data.order) != JSON.stringify(dtCurrentState.order)) {
                changed = true;
            }
        } else {
            changed = true;
        }

        if (changed || clear == true) {
            dtCurrentState = dtFixJson(data);
            var dtDelayTimerWait = clear == true ? 0 : 2000;
            clearTimeout(dtDelayTimer);
            dtDelayTimer = setTimeout(function() {
                $.ajax({
                    headers : {
                        'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                    },
                    url: EasyTaxiOffice.appPath +'/admin/saveDtState',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {
                        type: 'admin_bookings_state',
                        state: data,
                        page: dtPageKey
                    },
                    success: function(response) {
                        if (response.status == false) {
                            console.log('Booking listing table state could not be saved!');
                        } else {
                            if (clear == true) {
                                window.location.reload();
                            }
                        }
                    }
                });
            }, dtDelayTimerWait);
        }
    }

    function dtStateLoad(settings, callback) {
        var state = JSON.parse(window.localStorage.getItem(dtStorageKey));
        if (typeof dtCurrentState != 'undefined' && dtCurrentState != null) {
            state = dtFixJson(dtCurrentState);
        }
        callback(state);
    }
    // State end


    // Table
    var uriParams = ETO.getUrlParams(window.location.search),
        datatableOptions = {
            processing: true,
            serverSide: true,
            deferLoading: 0,
            ajax: {
                headers : {
                    'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
                method: 'POST',
                dataType: 'json',
                cache: false,
                data: {
                    task: 'bookings',
                    action: 'list',
                    page: '{{ request('page', 'all') }}'
                },
                dataSrc: 'bookings',
                dataFilter: function(data){
                    var json = jQuery.parseJSON(data);
                    var html = '';

                    if( json && json.summary ) {
                        html = json.summary;
                    }

                    if( html ) {
                        $('#dmodal').addClass('modal-booking-report');
                        $('#dmodal .modal-title').html('Reports');
                        $('#dmodal .modal-body').html(html);
                        $('#dmodal').modal('show');

                        $('#dmodal').on('hidden.bs.modal', function(){
                            $('.pageFilters form #filter-summary').removeAttr('checked');
                            filterTable();
                        });
                    }

                    return JSON.stringify(json);
                }
            },
            columns: ETO.Booking.columns(ETO.current_user.role, uriParams.page),
            columnDefs: [{
                targets: 0,
                data: null,
                render: function(data, type, row) {
                    var h = '',
                        isReadTrans = row.is_read == '0' ? '{{ trans('admin/bookings.button.markAsRead') }}' : '{{ trans('admin/bookings.button.markAsUnread') }}';

                    h += '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';
                    if (ETO.hasPermission('admin.bookings.show')) {
                        h += '<a href="' + row.url_show + '" onclick="modalIframe(this); return false;" class="btn btn-default btn-sm btnView" data-original-title="{{ trans('admin/bookings.button.show') }} #' + row.ref_number + '"><i class="fa fa-eye"></i></a>';
                    }

                    if ((uriParams.page == 'trash' && (
                            ETO.hasPermission('admin.bookings.destroy')
                            || ETO.hasPermission('admin.bookings.restore')
                        )) || (
                            ETO.hasPermission('admin.bookings.edit')
                            || ETO.hasPermission('admin.bookings.tracking')
                            || ETO.hasPermission('admin.bookings.transactions')
                            || ETO.hasPermission('admin.bookings.invoice')
                            || ETO.hasPermission('admin.bookings.create')
                            || ETO.hasPermission('admin.bookings.sms')
                            || ETO.hasPermission('admin.feedback.show')
                            || ETO.hasPermission('admin.bookings.meeting_board')
                            || ETO.hasPermission('admin.bookings.mark_as_read')
                            || ETO.hasPermission('admin.bookings.notifications')
                            || ETO.hasPermission('admin.activity.index')
                            || ETO.hasPermission('admin.bookings.trash')
                        )
                    ) {
                        h += '<div class="btn-group pull-left" role="group">'+
                            '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'+
                            '<span class="fa fa-angle-down"></span>'+
                            '</button>'+
                            '<ul class="dropdown-menu" role="menu">';
                        @if (request('page') == 'trash')
                            if (ETO.hasPermission('admin.bookings.destroy')) {
                                h += '<li>\
                                    <a href="#" class="btnRemoveFromTrash" data-eto-id="' + row.id + '" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.remove_from_trash') }}">\
                                        <span style="display:inline-block; width:20px; text-align:center;">\
                                            <i class="fa fa-trash"></i>\
                                        </span>\
                                        {{ trans('admin/bookings.button.remove_from_trash') }}\
                                    </a>\
                                </li>';
                            }
                            if (ETO.hasPermission('admin.bookings.restore')) {
                                h += '<li>\
                                    <a href="#" class="btnRestoreFromTrash" data-eto-id="' + row.id + '" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.restore_from_trash') }}">\
                                        <span style="display:inline-block; width:20px; text-align:center;">\
                                            <i class="fa fa-reply"></i>\
                                        </span>\
                                        {{ trans('admin/bookings.button.restore_from_trash') }}\
                                    </a>\
                                </li>';
                            }
                        @else
                            if (ETO.hasPermission('admin.bookings.edit')) {
                                h += '<li>\
                                    <a href="' + row.url_edit + '" class="btnEdit" onclick="modalIframe(this); return false;" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.edit') }} #' + row.ref_number + '">\
                                        <span style="display:inline-block; width:20px; text-align:center;">\
                                            <i class="fa fa-pencil-square-o"></i>\
                                        </span>\
                                        {{ trans('admin/bookings.button.edit') }}\
                                    </a>\
                                </li>';
                            }
                            if (ETO.hasPermission('admin.bookings.tracking')) {
                                h += '<li>\
                                    <a href="javascript:void(0)" class="eto-btn-booking-tracking" style="padding:3px 8px;" data-eto-id="'+row.id+'" data-original-title="{{ trans('admin/bookings.button.tracking') }} #'+ row.ref_number +'">\
                                        <span style="display:inline-block; width:20px; text-align:center;">\
                                          <i class="fa fa-map-marker"></i>\
                                        </span>\
                                        {{ trans('admin/bookings.button.tracking') }}\
                                    </a>\
                                </li>';
                            }
                            if (ETO.hasPermission(['admin.transactions.index', 'admin.transactions.create', 'admin.transactions.edit', 'admin.transactions.destroy'])) {
                                h += '<li>\
                                <a href="' + row.url_transactions + '" class="btnTransactions" onclick="modalIframe(this); return false;" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.transactions') }} #' + row.ref_number + '">\
                                    <span style="display:inline-block; width:20px; text-align:center;">\
                                        <i class="fa fa-credit-card"></i>\
                                    </span>\
                                    {{ trans('admin/bookings.button.transactions') }}\
                                </a>\
                            </li>';
                            }
                            if (ETO.hasPermission('admin.bookings.invoice')) {
                                h += '<li>\
                                <a href="' + row.url_invoice + '" class="btnInvoice" onclick="modalIframe(this); return false;" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.invoice') }} #' + row.ref_number + '">\
                                    <span style="display:inline-block; width:20px; text-align:center;">\
                                        <i class="fa fa-file-pdf-o"></i>\
                                    </span>\
                                    {{ trans('admin/bookings.button.invoice') }}\
                                </a>\
                            </li>';
                            }
                            if (ETO.hasPermission('admin.bookings.create')) {
                                h += '<li>\
                                    <a href="' + row.url_copy + '" class="btnCopy" onclick="modalIframe(this); return false;" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.copy') }} #' + row.ref_number + '">\
                                        <span style="display:inline-block; width:20px; text-align:center;">\
                                            <i class="fa fa-files-o"></i>\
                                        </span>\
                                        {{ trans('admin/bookings.button.copy') }}\
                                    </a>\
                                </li>'
                            }
                            if (ETO.hasPermission('admin.bookings.sms')) {
                                h += '<li>\
                                  <a href="' + row.url_sms + '" class="btnSMS" onclick="modalIframe(this); return false;" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.sms') }} #' + row.ref_number + '">\
                                      <span style="display:inline-block; width:20px; text-align:center;">\
                                          <i class="fa fa-commenting"></i>\
                                      </span>\
                                      {{ trans('admin/bookings.button.sms') }}\
                                  </a>\
                              </li>';
                            }
                            if (ETO.hasPermission('admin.feedback.show')) {
                                h += '<li>\
                                      <a href="'+ row.url_feedback +'" class="btnFeedback" onclick="modalIframe(this); return false;" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.feedback') }} #'+ row.ref_number +'">\
                                          <span style="display:inline-block; width:20px; text-align:center;">\
                                              <i class="fa fa-comments-o"></i>\
                                          </span>\
                                          {{ trans('admin/bookings.button.feedback') }}\
                                      </a>\
                                  </li>';
                            }
                            @if (config('site.booking_meeting_board_enabled'))
                                if (ETO.hasPermission('admin.bookings.meeting_board')) {
                                    h += '<li>\
                                    <a href="' + row.url_meeting_board + '" class="btnMeetingBoard" onclick="modalIframe(this); return false;" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.meeting_board') }} #' + row.ref_number + '">\
                                        <span style="display:inline-block; width:20px; text-align:center;">\
                                            <i class="fa fa-address-card-o"></i>\
                                        </span>\
                                        {{ trans('admin/bookings.button.meeting_board') }}\
                                    </a>\
                                </li>';
                                }
                            @endif
                            if (ETO.hasPermission('admin.bookings.mark_as_read')) {
                                h += '<li>\
                                <a href="#" onclick="markAsRead(' + row.id + ', ' + row.is_read + '); return false;"  data-title="' + isReadTrans + ' #' + row.ref_number + '" style="padding:3px 8px;">\
                                    <span style="display:inline-block; width:20px; text-align:center;">\
                                        <i class="fa fa-eye' + (row.is_read == '0' ? '' : '-slash') + '"></i>\
                                    </span>\
                                    ' + isReadTrans + '\
                                </a>\
                            </li>';
                            }
                            if (ETO.hasPermission('admin.bookings.notifications')) {
                                h += '<li>\
                                <a href="#" class="eto-notifications" data-eto-id="' + row.id + '" data-title="{{ trans('admin/bookings.button.notifications') }} #' + row.ref_number + '" style="padding:3px 8px;">\
                                    <span style="display:inline-block; width:20px; text-align:center;">\
                                        <i class="fa fa-bell"></i>\
                                    </span>\
                                    {{ trans('admin/bookings.button.notifications') }}\
                                </a>\
                            </li>';
                            }
                            @if(config('laravel-activitylog.enabled'))
                                if (ETO.hasPermission('admin.activity.index')) {
                                    h += '<li>\
                                       <a href="' + ETO.config.appPath + '/activity?subject=booking&subject_id=' + row.id + '" class="eto-wrapper-booking-activity" onclick="modalIframe(this); return false;" style="padding:3px 8px;" data-original-title="#' + row.ref_number + ' {{ trans('admin/bookings.button.activity') }}">\
                                            <span style="display:inline-block; width:20px; text-align:center;">\
                                                <i class="fa fa-shield"></i>\
                                            </span>\
                                            {{ trans('admin/bookings.button.activity') }}\
                                        </a>\
                                    </li>';
                                }
                            @endif
                            if (ETO.hasPermission('admin.bookings.trash')) {
                                h += '<li>\
                                    <a href="#" onclick="deleteRecord(' + row.id + ', ' + row.booking_children + '); return false;" class="btnDelete" style="padding:3px 8px;" data-original-title="{{ trans('admin/bookings.button.destroy') }} #' + row.ref_number + '">\
                                        <span style="display:inline-block; width:20px; text-align:center;">\
                                            <i class="fa fa-trash"></i>\
                                        </span>\
                                        {{ trans('admin/bookings.button.destroy') }}\
                                    </a>\
                                </li>';
                            }
                        @endif
                            h += '</ul>'+
                            '</div>';
                    }

                    h += '</div>';

                    return h;
                }
            }],
            colReorder: true,
            paging: true,
            pagingType: 'full_numbers',
            // dom: 'rt<"row"<"col-xs-12 col-md-5 dataTablesFooterLeft"li><"col-xs-12 col-md-7 dataTablesFooterRight"p>><"clear">',
            dom: '<"row topContainer"<"col-xs-12 col-sm-12 col-md-12 dataTablesHeaderLeft"B><"col-xs-12 col-sm-6 col-md-5 dataTablesHeaderRight">><"dataTablesBody"rt><"row bottomContainer"<"col-xs-12 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-12 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
            buttons: [
                {
                    'extend': 'colvis',
                    'collectionLayout': 'fixed1 two-column',
                    'text': '<i class="fa fa-eye"></i>',
                    'titleAttr': '{{ trans('admin/users.button.column_visibility') }}',
                    'postfixButtons': ['colvisRestore'],
                    'className': 'btn-default btn-sm'
                }, /*{
                    'text': '<div onclick="$(\'#dtable\').DataTable().colReorder.reset();"><i class="fa fa-arrows-h"></i></div>',
                    'titleAttr': '{{ trans('admin/users.button.reset_column_order') }}',
                    'className': 'btn-default btn-sm'
                }, {
                    'extend': 'reset',
                    'text': '<i class="fa fa-undo"></i>',
                    'titleAttr': '{{ trans('admin/users.button.reset') }}',
                    'className': 'btn-default btn-sm'
                },*/ {
                    // 'text': '<div onclick="$(\'.btnReset\').trigger(\'click\');"><i class="fa fa-undo"></i></div>',
                    // 'text': '<div onclick="$(\'#dtable\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                    'text': '<div class="eto-booking-listing-clear-state"><i class="fa fa-undo"></i></div>',
                    'titleAttr': '{{ trans('admin/users.button.reset') }}',
                    'className': 'btn-default btn-sm'
                }, {
                    'extend': 'reload',
                    'text': '<i class="fa fa-refresh"></i>',
                    'titleAttr': '{{ trans('admin/users.button.reload') }}',
                    'className': 'btn-default btn-sm'
                }, {
                    'text': '<div class="btnFilters" onclick="$(\'.pageFilters\').toggle();"><i class="fa fa-search"></i> <span class="hidden-xs">Filter / Search</span></div>',
                    'titleAttr': 'Filter / Search',
                    'className': 'btn-default btn-sm buttons-new search_button'
                }, {
                    'text': '<div class="btnReports" onclick="@if(request()->system->subscription->license_status != 'suspended')$(\'.pageFilters #filter-summary\').attr(\'checked\', true); @endif filterTable();" ><i class="fa fa-pie-chart"></i> <span class="hidden-xs">Reports</span></div>',
                    'titleAttr': 'Reports',
                    'className': 'btn-default btn-sm buttons-new reports_button' + (ETO.hasPermission('admin.reports.create') ? '' : ' hidden')
                }, {
                    'text': '<div onclick="window.location.href=\'{{ route('admin.bookings.create') }}\';"><i class="fa fa-plus"></i> <span class="hidden-xs">Add new booking</span></div>',
                    'titleAttr': 'Add new booking',
                    'className': 'btn-success btn-sm buttons-new' + (ETO.hasPermission('admin.bookings.create') ? '' : ' hidden')
                }
            ],
            scrollX: true,
            searching: true,
            ordering: true,
            lengthChange: true,
            info: true,
            autoWidth: false,
            stateSave: true,
            stateSaveCallback: function(settings, data) {
                dtStateSave(data);
            },
            stateLoadCallback: function(settings, callback) {
                if (typeof callback == 'undefined') { return null; }
                dtStateLoad(settings, callback);
            },
            stateDuration: 0,
            order: [{!! json_encode($defaultOrder, true) !!}],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            language: {
                lengthMenu: '_MENU_',
                paginate: {
                    first: '<i class="fa fa-angle-double-left"></i>',
                    previous: '<i class="fa fa-angle-left"></i>',
                    next: '<i class="fa fa-angle-right"></i>',
                    last: '<i class="fa fa-angle-double-right"></i>'
                }
            },
            drawCallback: function(settings) {
                var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                pagination.toggle(this.api().page.info().pages > 1);

                @if (request()->system->subscription->license_status == 'suspended')
                    $('.btnReports').closest('a').attr('disabled', true);
                @endif
            },
            infoCallback: function( settings, start, end, max, total, pre ) {
                return '<i class="ion-ios-information-outline" title="'+ pre +'"></i>';
            },
        };

      if ((uriParams.page == 'trash' && !ETO.hasPermission('admin.bookings.destroy') && !ETO.hasPermission('admin.bookings.restore'))
          || (!ETO.hasPermission('admin.bookings.edit')
              && !ETO.hasPermission('admin.bookings.show')
              && !ETO.hasPermission('admin.bookings.tracking')
              && !ETO.hasPermission('admin.bookings.transactions')
              && !ETO.hasPermission('admin.bookings.invoice')
              && !ETO.hasPermission('admin.bookings.create')
              && !ETO.hasPermission('admin.bookings.sms')
              && !ETO.hasPermission('admin.feedback.show')
              && !ETO.hasPermission('admin.bookings.meeting_board')
              && !ETO.hasPermission('admin.bookings.mark_as_read')
              && !ETO.hasPermission('admin.bookings.notifications')
              && !ETO.hasPermission('admin.activity.index')
              && !ETO.hasPermission('admin.bookings.trash')
          )
      ) {
          delete datatableOptions.columnDefs;
      }

    var datatable = $('#dtable').DataTable(datatableOptions)
    .on('preDraw', function() {
        $('#bookings [title]').tooltip('hide');
        $('#bookings [data-toggle="tooltip"]').tooltip('hide');
        $('#bookings [data-toggle="popover"]').popover('hide');
    })
    .on('draw.dt', function() {
        $('#bookings .buttons-reload i').removeClass('fa-spin');

        // https://github.com/jedfoster/Readmore.js
        $('.eto-address-more').readmore('destroy').readmore({
            collapsedHeight: 40,
            moreLink: '<a href="#" class="eto-address-more-link">{{ trans('booking.buttons.more') }}</a>',
            lessLink: '<a href="#" class="eto-address-more-link">{{ trans('booking.buttons.less') }}</a>'
        });

        // Tooltip - start
        $('#bookings [title]').tooltip('destroy').tooltip({
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

        // Popover
        $('#bookings [data-toggle="popover"]').popover('destroy').popover({
            placement: 'auto right',
            container: 'body',
            trigger: 'focus hover',
            html: true
        });

        // Select
        // $('.dataTables_length select').select2();

        // $('#dtable').find('.actionColumn button').addClass('hide');

        // $('#dtable').find('tr').hover(
        //   function() {
        //     $(this).find('button').removeClass('hide');
        //   },
        //   function() {
        //     $(this).find('button').addClass('hide');
        //   }
        // );

        // // Edit button
        // $('#dtable').find('button.btnEdit').hover(
        //   function() {
        //     $(this).removeClass('btn-default').addClass('btn-success');
        //   },
        //   function() {
        //     $(this).removeClass('btn-success').addClass('btn-default');
        //   }
        // );
        //
        // // Delete button
        // $('#dtable').find('button.btnDelete').hover(
        //   function() {
        //     $(this).removeClass('btn-default').addClass('btn-danger');
        //   },
        //   function() {
        //     $(this).removeClass('btn-danger').addClass('btn-default');
        //   }
        // );
        //
        // // View button
        // $('#dtable').find('button.btnView').hover(
        //   function() {
        //     $(this).removeClass('btn-default').addClass('btn-info');
        //   },
        //   function() {
        //     $(this).removeClass('btn-info').addClass('btn-default');
        //   }
        // );
        //
        // // Copy button
        // $('#dtable').find('button.btnCopy').hover(
        //   function() {
        //     $(this).removeClass('btn-default').addClass('btn-warning');
        //   },
        //   function() {
        //     $(this).removeClass('btn-warning').addClass('btn-default');
        //   }
        // );
    })
    .on('stateSaveParams.dt', function (e, settings, data) {
        data.search.search = '';
    });

    $('body').on('click', '#bookings .inline-editing', function(e) {
        e.preventDefault();

        $(this).editable({
            // mode: 'popup',
            placement: 'bottom',
            savenochange: false,
            ajaxOptions: {
                headers: {
                  'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                type: 'GET',
                dataType: 'json',
            },
            sourceOptions: {
                cache: true,
                // type: 'POST',
                // headers: {
                //   'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                // },
            },
            source: function() {
                if (typeof etoBookingRequestData !== 'undefined') {
                    if ($(this).hasClass('inline-editing-status')) {
                        return etoBookingRequestData.statusList;
                    }
                    else if ($(this).hasClass('inline-editing-fleet')) {
                        return etoBookingRequestData.fleetList;
                    }
                    else if ($(this).hasClass('inline-editing-driver')) {
                        return etoBookingRequestData.driverList;
                    }
                    else if ($(this).hasClass('inline-editing-vehicle')) {
                        var driverId = parseInt($(this).attr('data-driver_id'));
                        var vehicles = [];
                        $.each(etoBookingRequestData.vehicleList, function(k, v) {
                            if (v.driver_id == driverId) {
                                vehicles.push(v);
                            }
                        });
                        return vehicles;
                    }
                }
                return null;
            },
            // sourceCache: true,
            display: function(value, sourceData, response) {
                var new_value = '';

                if( response && response.new_value ) {
                    new_value = response.new_value;
                }
                else if( sourceData && sourceData.new_value ) {
                    new_value = sourceData.new_value;
                }

                if( new_value && new_value != '<i class="fa fa-info-circle"></i>' ) {
                    $(this).html(new_value +' <i class="fa fa-edit"></i>');
                }
            },
            success: function(response, newValue) {
                filterTable();
            }
        })
        .editable('show');
    })
    .on('click', '.btnRemoveFromTrash', function(e) {
        e.preventDefault();

        var id = $(this).attr('data-eto-id'),
            table = $('table [data-eto-id="'+id+'"]').closest('table'),
            bookingChildren = $(this).attr('data-eto-booking-children');

        var html = 'You won\'t be able to revert this!';

        if (bookingChildren > 0) {
            html += '<div style="margin-bottom:20px;" class="eto-booking-delete-parent"><span style="font-weight:bold; color:red;">Important!</span> Please note that deleting main booking will also cause deleting all sub-bookings associated with it.</div>';
        }

        ETO.swalWithBootstrapButtons({
            title: 'Are you sure?',
            html: html,
            type: 'warning',
            showCancelButton: true,
        })
        .then(function(result){
            if (result.value) {
                prepareRecord(id, bookingChildren, datatable, 'removeFromTrash');
                ETO.toast({
                    type: 'success',
                    title: 'Deleted'
                });
            }
        });
    })
    .on('click', '.btnRestoreFromTrash', function(e) {
        e.preventDefault();

        var id = $(this).attr('data-eto-id'),
            table = $('table [data-eto-id="'+id+'"]').closest('table'),
            bookingChildren = $(this).attr('data-eto-booking-children');

        prepareRecord(id, bookingChildren, datatable, 'restoreFromTrash');
    });

    // Auto refresh
    @if( config('site.booking_listing_refresh_type') > 0 )
        var refreshInterval = {{ config('site.booking_listing_refresh_interval') }}; // Seconds
        var nofitfyInterval = refreshInterval;
        var refreshTime = parseInt(moment().format('X')) + refreshInterval;
        var i = nofitfyInterval;

        setInterval(function() {
            var secs = parseInt(moment().format('X'));

            @if( config('site.booking_listing_refresh_counter') > 0 )
                if( secs > refreshTime - nofitfyInterval ) {
                    if( $('#reload-counter').length > 0 ) {
                        $('#reload-counter').html(i);
                    }
                    else {
                        $('#bookings .buttons-reload').append('<span id="reload-counter">'+ i +'</span>');
                    }
                    i--;
                }
                else {
                    $('#reload-counter').remove();
                    i = nofitfyInterval;
                }
            @endif

            if( secs > refreshTime ) {
                refreshTime = secs + refreshInterval;
                i = nofitfyInterval;
                $('#bookings .buttons-reload i').addClass('fa-spin');
                datatable.ajax.reload(null, false); // user paging is not reset on reload
            }
        }, 1000);
    @endif

    // Move export button
      if (ETO.hasPermission('admin.bookings.filter')) {
          $('.search_button').after($('.export_button'));
      } else {
          $('.search_button').remove();
      }

    // Adjust panels
    updateTableHeight();
    updateEditable();
  });

  $(window).resize(function() {
      updateTableHeight();
      updateEditable();
  });
  </script>
@endsection
