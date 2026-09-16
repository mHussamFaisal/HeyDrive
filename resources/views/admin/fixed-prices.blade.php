@extends('admin.index')

@section('title', 'Fixed Prices')


@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.dataTables.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.dataTables.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">
  <style>
  .popover {
    max-width: 700px !important;
  }
  .popover-content {
    overflow: auto;
    height: 240px;
    width: 350px;
  }
  </style>
@endsection


@section('subcontent')
  <div class="pageContainer" id="fixed-prices">
    <div class="pageTitle">

      <a href="#" class="btn btn-default btn-sm pull-right btnFilters" title="Search">
        <i class="fa fa-search"></i>
      </a>
      @permission('admin.fixed_prices.create')
      <a href="#" onclick="updateRecord(); return false;" class="btn btn-success btn-sm pull-right btnAdd">
        <i class="fa fa-plus"></i> <span>Add new</span>
      </a>
      @endpermission
      @if( config('site.allow_fixed_prices_import') )
        <a href="{{ route('admin.fixed-prices.import') }}" class="btn btn-default btn-sm pull-right btnImport" style="margin-right:5px;">
          <i class="fa fa-upload"></i> <span>Import</span>
        </a>
      @endif

      <h3>
        Fixed Prices
        <a style="margin-left:5px;" href="#" class="help-button" data-toggle="popover" data-title="Fixed Prices" data-content="This panel allow management of Fixed Prices between location A and B which automatically overwrites mileage/kilometer calculation.<br><br>If you would like to add new price just press <b>Add new</b><br><br>Symbol <b>&quot;Z&quot;</b> in Fixed prices means the fixed price is using Zone based location.<br><br>Please note postcodes like <b>W1, EC1, EC2, etc</b> is not accepted by the system. Reason for it is such postcodes does not exist, they are broken down into smaller districts like for <b>W1</b> are <b>WC1A, WC1B, WC1E, WC1H, WC1N, etc</b><br><br>For more details please see Wikipedia map <a href='https://en.wikipedia.org/wiki/London_postal_district' target='_blank'>https://en.wikipedia.org/wiki/London_postal_district</a>">
          <i class="ion-ios-information-outline"></i>
        </a>
      </h3>

    </div>
    <div class="pageFilters pageFiltersHide">

      <form method="post" class="form-inline">
        <a href="#" class="pull-right btnClose" title="Close" onclick="$('.pageFilters').toggle(); return false;">
          <i class="fa fa-times"></i>
        </a>
        <div class="form-group field-service_ids">
          <label for="filter-service_ids">Service Type</label>
          <select name="filter-service_ids[]" id="filter-service_ids" multiple="multiple" size="1" class="form-control select2" data-placeholder="Service Type" data-allow-clear="true"></select>
        </div>
        <div class="form-group field-type hidden">
          <label for="filter-type">Type</label>
          <select name="filter-type[]" id="filter-type" multiple="multiple" size="1" class="form-control select2" data-placeholder="Type" data-allow-clear="true"></select>
        </div>
        <div class="form-group field-keywords">
          <label for="filter-keywords">Keywords</label>
          <input type="text" name="filter-keywords" id="filter-keywords" class="form-control" placeholder="Keywords">
        </div>
        <div class="form-group field-btn-search">
          <button type="button" class="btn btn-default btnSearch" title=""><i class="fa fa-search"></i> <span>Search</span></button>
        </div>
        <div class="form-group field-btn-reset">
          <button type="button" class="btn btn-link btnReset" title=""><i class="fa fa-times"></i> <span>Reset</span></button>
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
            <div class="modal-overlay"><i class="fa fa-refresh fa-spin"></i></div>
          </div>
        </div>
      </div>

    </div>
  </div>
@endsection


@section('subfooter')
  <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/dataTables.bootstrap.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/dataTables.buttons.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.bootstrap.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.colVis.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.server-side.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/extensions/ColReorder/js/dataTables.colReorder.min.js') }}"></script>
  <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
  <script src="{{ asset_url('plugins','form-validation/formValidation.min.js') }}"></script>
  <script src="{{ asset_url('plugins','form-validation/formValidation-bootstrap.min.js') }}"></script>
  <script>
  // Filter
  function filterTable(isDelete, isPageReset) {
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

  // Delete
  function deleteRecord(id) {
      var html = '';
      var title = '';

      title = 'Delete';
      html = '<p style="margin-bottom:20px;">Are you sure you want to permanently delete this fixed price?</p>\
              <button type="button" class="btn btn-danger btnConfirm" title="Delete"><i class="fa fa-trash"></i> Yes, delete</button>\
              <button type="button" class="btn btn-link btnCancel" title="Cancel">Cancel</button>\
              <span id="statusMsg"></span>';

      $('#dmodal .modal-title').html(title);
      $('#dmodal .modal-body').html(html);
      $('#dmodal').modal({
          show: true
      });

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
            task: 'fixedprices',
            action: 'destroy',
            id: id
          },
          success: function(response) {
            if(response.success) {
              $('#dmodal #statusMsg').html('');
              $('#dmodal').modal('hide');
              filterTable(true);
            }
            else {
              $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> The data could not be deleted</span>');
            }
          },
          error: function(response) {
            $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
          },
          beforeSend: function() {
            $('#dmodal #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> Loading');
            $('#dmodal .modal-overlay').show();
          },
          complete: function() {
            $('#dmodal .modal-overlay').hide();
          }
        });
      });

      $('#dmodal .btnCancel').on('click', function() {
        $('#dmodal').modal('hide');
      });
  }

  // Copy
  function copyRecord(id) {
      var html = '';
      var title = '';

      title = 'Duplicate';
      html = '<p style="margin-bottom:20px;">Are you sure you want to duplicate this fixed price?</p>\
              <button type="button" class="btn btn-success btnConfirm" title="Duplicate"> Yes, duplicate</button>\
              <button type="button" class="btn btn-link btnCancel" title="Cancel">Cancel</button>\
              <span id="statusMsg"></span>';

      $('#dmodal .modal-title').html(title);
      $('#dmodal .modal-body').html(html);
      $('#dmodal').modal({
          show: true
      });

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
                  task: 'fixedprices',
                  action: 'copy',
                  id: id
              },
              success: function(response) {
                if(response.success) {
                    $('#dmodal #statusMsg').html('');
                    $('#dmodal').modal('hide');
                    filterTable(true);
                }
                else {
                    $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> The data could not be copied</span>');
                }
              },
              error: function(response) {
                  $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
              },
              beforeSend: function() {
                  $('#dmodal #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> Loading');
                  $('#dmodal .modal-overlay').show();
              },
              complete: function() {
                  $('#dmodal .modal-overlay').hide();
              }
          });
      });

      $('#dmodal .btnCancel').on('click', function() {
          $('#dmodal').modal('hide');
      });
  }

  // Field options
  function applyFieldOptions() {
      if( {{ config('site.fixed_prices_deposit_enable') }} ) {
          $('#table-pricing .deposit').show();
      }
      else {
          $('#table-pricing .deposit').hide();
      }

      // Select
      $('#dmodal form .select2').select2();

      // Spinner
      $('#dmodal form input[type="text"].touchspin').TouchSpin({
        max: null,
        booster: true,
        boostat: 5,
        mousewheel: true,
        verticalbuttons: true,
        verticalupclass: 'fa fa-plus',
        verticaldownclass: 'fa fa-minus'
      });

      // Date picker
      $('#dmodal form .datepicker').daterangepicker({
        drops: 'up',
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

      // Placeholder
      function updateFormPlaceholder(that) {
          var $container = $(that).closest('.form-group:not(.placeholder-disabled)');

          if( $(that).val() != '' || $container.hasClass('placeholder-visible') ) {
              $container.find('label').show();
          }
          else {
              $container.find('label').hide();
          }
      }

      $('#dmodal form').find('input:not([type="submit"]), textarea, select').each(function() {
          updateFormPlaceholder(this);
      })
      .bind('change keyup', function(e) {
          updateFormPlaceholder(this);
      });

      $('[data-toggle="popover"]').popover({
          placement: 'auto right',
          container: 'body',
          trigger: 'click focus hover',
          html: true
      });

      $('body').on('click', function (e) {
          // did not click a popover toggle, or icon in popover toggle, or popover
          if ($(e.target).data('toggle') !== 'popover'
              && $(e.target).parents('[data-toggle="popover"]').length === 0
              && $(e.target).parents('.popover.in').length === 0) {
              $('[data-toggle="popover"]').popover('hide');
          }
      });
  }

  // Update
  function updateRecord(id) {
    var html = '';
    var title = '';
    var btnIcon = '';
    var btnTitle = '';
    var msgSuccess = '';
    var buttonsHTML = '';

    if( id && id >= 0 ) {
        title = 'Edit';
        btnIcon = 'fa fa-pencil-square-o';
        btnTitle = 'Save';
        msgSuccess = 'Saved';
        buttonsHTML = '<div class="btn-group dropdown">\
            <button class="btn btn-success btnSaveClose" type="button" onclick="$(\'#dmodal form #submit_action\').val(\'save\'); $(\'#dmodal form #submit_action\').submit(); return false;">\
                <span>'+ btnTitle +'</span>\
            </button>\
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">\
                <i class="fa fa-angle-down"></i>\
            </button>\
            <ul class="dropdown-menu" role="menu">\
                <li>\
                    <a href="#" onclick="$(\'#dmodal form #submit_action\').val(\'save_edit\'); $(\'#dmodal form #submit_action\').submit(); return false;">'+ btnTitle +' &amp; Edit</a>\
                </li>\
            </ul>\
          </div>';
    }
    else {
        title = 'Add new';
        btnIcon = 'fa fa-plus';
        btnTitle = 'Add';
        msgSuccess = 'Added';
        buttonsHTML = '<button type="submit" class="btn btn-success btnSave" title="'+ btnTitle +'" onclick="$(\'#dmodal form #submit_action\').val(\'save\');"><span>'+ btnTitle +'</span></button>';
    }

    // <i class="'+ btnIcon +'"></i>\
    // <button class="btn btn-success btnSave" type="button" onclick="$(\'#dmodal form #submit_action\').val(\'save\');"><span>'+ btnTitle +'</span></button>\

    var popContent = '<b>Direction</b><br>\
    It define if new price will be apply one way from A to B only or both direction.<br><br>\
    <b>From and To</b><br>\
    <ul style=\'padding-left:20px; margin-bottom:20px;\'>\
    <li>Add postcode(s) by typing first part of postcode e.g. E1 and click enter. If there is more than one postcode then put next postcode and click enter e.g. E2 and click enter, E3 and click enter, so on.</li>\
    <li>When typing a new postcode or location, system will make suggestion display in dropdown list, you can choose those by simply clicking on of the suggestions e.g. Heathrow Terminal 1 (TW6 1AP) or E2</li>\
    <li>To add new suggestion go to Settings -> Location -> Add new location</li>\
    </ul>\
    Important: Fixed prices only work with postcodes and pre-set location (Settings -> Location -> Add new location). Any location names not added in Location tab e.g. towns, areas, etc. won\'t be detected.<br><br>\
    <b>Price</b><br>\
    “Action” option allows to choose how price will be calculated<br>\
    <ul style=\'padding-left:20px; margin-bottom:20px;\'>\
    <li><b>Flat</b> - this is a final value which will be display as a price</li>\
    <li><b>Increase base price by %</b> - add Base price value and for each vehicle type add % value by which Base price will be multiply, e.g.<br>\
    Base price is £50<br>\
    set Saloon value to “1”, calculated price for Saloon is £50<br>\
    set Estate value to “1.1”, calculated price for Estate is £55 (10% increase)<br>\
    set Executive value to “1.3”, calculated price for Executive is £65 (30% increase)</li>\
    <li><b>Increase base price by X</b> - add Base price value and for each vehicle type add X value by which Base price  will be increase, e.g.<br>\
    Base price = £50<br>\
    set Saloon value to “0”, calculated price for Saloon is £50<br>\
    set Estate value to “6”, calculated cost price Estate is £56<br>\
    set Executive value to “13”, calculated price for Executive is £63</li>\
    </ul>\
    <b>Limit to specific Time and Date</b><br>\
    Setting specific time and date will set Fixed Price to be available only to specific period of time, e.g christmas or school break time.<br><br>\
    <b>Active</b> tickbox<br>\
    When the tixbox is marked, Fixed Price is set to active.';

    var popDeposit = 'To enable/disable individual deposit for each vehicle type, first Save this Fixed Price then go to Settings -> Deposit Payment -> click Active Fixed Price deposit -> choose type of deposit: Percent or Flat<br><br>\
    <ul style=\'padding-left:20px; margin-bottom:20px;\'>\
    <li>eg. Saloon price is set £100, deposit Percent value is set to 30, then customer will be asked for £30 deposit.</li>\
    <li>eg. Saloon price is set £100, deposit Flat value is set to 20, then customer will be asked for 20 deposit.</li>\
    </ul>\
    If Deposit is set for one vehicle type in this Fixed Price, then it should to be set to all vehicle types, otherwise if is left as 0, deposit will not be taken. Default deposit doesn’t apply in this case.<br><br>\
    If Fixed Price deposit setting is set to active but in none of the vehicle type deposit is set in this Fixed Price, then default site deposit is apply instead.';

    title += '<a style="margin-left:5px;" href="#" class="help-button" data-toggle="popover" data-title="How to add Fixed Price?" data-content="'+ popContent +'"><i class="ion-ios-information-outline"></i></a>';

    html = '<form method="post">\
            <input type="hidden" name="submit_action" id="submit_action" value="save">\
            <input type="hidden" name="id" id="id" value="0">\
            <input type="hidden" name="site_id" id="site_id" value="0">\
            <div class="form-group field-type">\
              <label for="type">Type</label>\
              <select name="type" id="type" data-placeholder="Type" data-minimum-results-for-search="Infinity" required class="form-control select2"></select>\
            </div>\
            <div class="form-group field-direction">\
              <label for="direction">Direction</label>\
              <select name="direction" id="direction" data-placeholder="Direction" data-minimum-results-for-search="Infinity" required class="form-control select2">\
                <option value="0">Both Ways</option>\
                <option value="1">From -> To</option>\
              </select>\
            </div>\
            <div class="form-group field-start_type">\
              <label for="start_type">From match</label>\
              <select name="start_type" id="start_type" data-placeholder="From match" data-minimum-results-for-search="Infinity" required class="form-control select2">\
                <option value="0">Include</option>\
                <option value="1">Exclude</option>\
              </select>\
            </div>\
            <div class="field-is_zone">\
                <label style="margin-right: 10px;font-weight: normal;">\
                <input name="is_zone" id="is_zone_0" data-placeholder="Postcode" type="radio" value="0" class="is_zone" checked="checked">\
                Postcodes <a href="{{ route('admin.locations.index') }}" target="_blank" data-toggle="tooltip" data-title="Add postcode"><i class="fa fa-plus"></i></a>\
                </label>\
                <label style="margin-right: 10px;font-weight: normal;">\
                <input name="is_zone" id="is_zone_1" data-placeholder="Zones" type="radio" value="1" class="is_zone">\
                Zones <a href="{{ route('admin.zones.index') }}" target="_blank" data-toggle="tooltip" data-title="Add zone"><i class="fa fa-plus"></i></a>\
                </label>\
            </div>\
            <div class="form-group field-start_postcode">\
              <label for="start_postcode">From postcodes</label>\
              <select name="start_postcode[]" id="start_postcode" data-placeholder="From postcodes (All)" data-tags="true" data-allow-clear="true" multiple="multiple" size="1" class="form-control select2"></select>\
            </div>\
            <div class="form-group field-start_zone hidden">\
              <label for="start_zone">From zones</label>\
              <select name="start_zone[]" id="start_zone" data-placeholder="From zones (All)" data-tags="false" data-allow-clear="true" multiple="multiple" size="1" class="form-control select2"></select>\
            </div>\
            <div class="form-group field-end_type">\
              <label for="end_type">To match</label>\
              <select name="end_type" id="end_type" data-placeholder="To match" data-minimum-results-for-search="Infinity" required class="form-control select2">\
                <option value="0">Include</option>\
                <option value="1">Exclude</option>\
              </select>\
            </div>\
            <div class="form-group field-end_postcode">\
              <label for="end_postcode">To postcodes</label>\
              <select name="end_postcode[]" id="end_postcode" data-placeholder="To postcodes (All)" data-tags="true" data-allow-clear="true" multiple="multiple" size="1" class="form-control select2"></select>\
            </div>\
            <div class="form-group field-end_zone hidden">\
              <label for="end_zone">To zones</label>\
              <select name="end_zone[]" id="end_zone" data-placeholder="To zones (All)" data-tags="false" data-allow-clear="true" multiple="multiple" size="1" class="form-control select2"></select>\
            </div>\
            <table id="table-pricing" class="table table-condensed table-hover table-blank-inputs">\
              <thead>\
                <th></th>\
                <th>Price</th>\
                <th class="deposit">Deposit  <i class="ion-ios-information-outline" style="font-size:16px; margin-left:2px;" data-toggle="popover" data-title="Fixed Price Deposit" data-content="'+ popDeposit +'"></i></th>\
              </thead>\
              <tbody>\
                <tr>\
                  <td>\
                    Base price\
                  </td>\
                  <td>\
                    <div class="form-group">\
                      <input type="number" name="value" id="value" value="0" required class="form-control" step="0.01" min="0">\
                    </div>\
                  </td>\
                  <td class="deposit">\
                    <div class="form-group">\
                      <input type="number" name="deposit" id="deposit" value="0" required class="form-control" step="0.01" min="0">\
                    </div>\
                  </td>\
                </tr>\
                <tr>\
                  <td>Action</td>\
                  <td>\
                    <div class="form-group">\
                      <select name="factor_type" id="factor_type" class="form-control">\
                        <option value="2">Override - Flat (“Base price” not available as there is nothing to override)</option>\
                        <option value="0">Multiply - Increase base price by %</option>\
                        <option value="1">Add - Increase base price by X</option>\
                      </select>\
                    </div>\
                  </td>\
                  <td class="deposit" style="padding:0 10px;">\
                    {{ config('site.fixed_prices_deposit_type') ? 'Flat' : 'Percent' }} <i class="ion-ios-information-outline" style="font-size:16px; margin-left:2px;" title="You can change action in deposit configuration in settings tab."></i>\
                  </td>\
                </tr>\
              </tbody>\
            </table>\
            <p>Limit to specific date and time</p>\
            <div class="clearfix">\
              <div class="form-group field-start_date">\
                <label for="start_date">Start date</label>\
                <input type="text" name="start_date" id="start_date" placeholder="Start date" class="form-control datepicker">\
              </div>\
              <div class="form-group field-end_date">\
                <label for="end_date">End date</label>\
                <input type="text" name="end_date" id="end_date" placeholder="End date" class="form-control datepicker">\
              </div>\
            </div>\
            <div class="form-group field-ordering">\
              <label for="ordering">Ordering</label>\
              <input type="text" name="ordering" id="ordering" placeholder="Ordering" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0">\
            </div>\
            <div class="form-group field-service_ids" style="margin-top:20px; margin-bottom:20px; max-width:100%;">\
              <div style="margin-bottom:10px;">Assign to selected services (all if no options are selected)</div>\
              <div id="service_ids"></div>\
            </div>\
            <div class="form-group field-published">\
              <label for="published" class="checkbox-inline">\
                <input type="checkbox" name="published" id="published" value="1" checked> Active\
              </label>\
            </div>\
            <div class="form-buttons">\
              '+ buttonsHTML +'\
              <button type="button" class="btn btn-default btnCancel" title="Cancel" onclick="$(\'#dmodal form #submit_action\').val(\'cancel\');"><span>Cancel</span></button>\
              <span id="statusMsg"></span>\
            </div>\
          </form>';

    $('#dmodal .modal-title').html(title);
    $('#dmodal .modal-body').html(html);
    $('#dmodal').modal({
      show: true
    });

    // Load filters
    $.ajax({
      headers : {
        'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
      },
      url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
      type: 'POST',
      dataType: 'json',
      cache: false,
      async: false,
      data: {
        task: 'fixedprices',
        action: 'init'
      },
      success: function(response) {

          // Services
          html = '';
          if (response.servicesList) {
            $.each(response.servicesList, function(index, item) {
              html += '<div><label for="service_ids'+ item.id +'" class="checkbox-inline">';
                html += '<input type="checkbox" name="service_ids[]" id="service_ids'+ item.id +'" value="'+ item.id +'"> '+ item.name +'';
              html += '</label></div>';
            });
          }
          $('#dmodal #service_ids').html(html);

          if( html ) {
              $('#dmodal .field-service_ids').show();
          }
          else {
              $('#dmodal .field-service_ids').hide();
          }

        // Type
        html = '';
        if (response.typeList) {
          $.each(response.typeList, function(index, item) {
            html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
        }
        $('#type').html(html);

        // Postcodes
        html = '';
        if (response.postcodeList) {
          $.each(response.postcodeList, function(index, item) {
            html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
        }
        $('#dmodal #start_postcode').html(html);
        $('#dmodal #end_postcode').html(html);

        // Zones
        html = '';
        if (response.zoneList) {
          $.each(response.zoneList, function(index, item) {
            html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
        }
        $('#dmodal #start_zone').html(html);
        $('#dmodal #end_zone').html(html);

        // Vehicles
        html = '';
        if (response.vehicleList) {
          $.each(response.vehicleList, function(index, item) {
            html += '<tr>\
                        <td>\
                          '+ item.name +'\
                        </td>\
                        <td>\
                          <div class="form-group">\
                            <input type="number" name="vehicle_'+ item.id +'" id="vehicle_'+ item.id +'" value="0" required class="form-control" step="0.01" min="0">\
                          </div>\
                        </td>\
                        <td class="deposit">\
                          <div class="form-group">\
                            <input type="number" name="vehicle_deposit_'+ item.id +'" id="vehicle_deposit_'+ item.id +'" value="0" required class="form-control" step="0.01" min="0">\
                          </div>\
                        </td>\
                    </tr>';
          });
        }
        $('#dmodal #table-pricing tbody tr:last-child').after(html);
      },
      error: function(response) {
        $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
      }
    });

    // Load data
    if( id && id >= 0 ) {
      $.ajax({
        headers : {
          'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
        },
        url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
          task: 'fixedprices',
          action: 'read',
          id: id
        },
        success: function(response) {
          if( response.success ) {
            $('#dmodal #statusMsg').html('');

            if( response.record ) {
              $.each(response.record, function(key, value) {

                if (key == 'is_zone') {
                    var field = $('#dmodal form input.is_zone');
                }
                else {
                    var field = $('#dmodal form #'+ key);
                }

                if( field.hasClass('select2') ) {
                  field.val(value).trigger('change');
                }
                else if( field.is(':checkbox') ) {
                  if( parseInt(value) ) {
                    field.attr('checked', true);
                  }
                  else {
                    field.attr('checked', false);
                  }
                }
                else if( field.is(':radio') ) {
                  if(parseInt(value)) {
                    field.each(function(k,el) {
                      if(parseInt($(el).val()) === parseInt(value)) {
                        $(el).attr('checked', true)
                      }
                      else {
                        $(el).attr('checked', false);
                      }
                    });
                  }
                  // else {
                  //   // field.attr('checked', false);
                  // }
                }
                else if( key == 'params' ) {
                    $('#dmodal form #factor_type').val(value.factor_type);
                    $('#dmodal form #deposit').val(value.deposit ? value.deposit : 0);
                    if( value.vehicle ) {
                        $.each(value.vehicle, function(key2, value2) {
                            $('#dmodal form #vehicle_'+ value2.id).val(value2.value);
                            $('#dmodal form #vehicle_deposit_'+ value2.id).val(value2.deposit ? value2.deposit : 0);
                        });
                    }
                }
                else if( key == 'service_ids' ) {
                    $.each(value, function(key2, value2) {
                        if( $('#dmodal form #service_ids'+ value2).length > 0 ) {
                            $('#dmodal form #service_ids'+ value2).attr('checked', true);
                        }
                    });
                }
                else {
                  field.val(value);
                }
              });

              applyFieldOptions();
              $('.is_zone').change();
            }
          }
          else {
            $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> The data could not be loaded</span>');
          }
        },
        error: function(response) {
          $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
        },
        beforeSend: function() {
          $('#dmodal #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> In progress');
          $('#dmodal .modal-overlay').show();
        },
        complete: function() {
          $('#dmodal .modal-overlay').hide();
        }
      });
    }
    else {
        applyFieldOptions();
    }

    // Form validation and submission
    var isReady = 1;

    $('#dmodal form').formValidation({
      framework: 'bootstrap',
      icon: {
        valid: 'glyphicon glyphicon-ok',
        invalid: 'glyphicon glyphicon-remove',
        validating: 'glyphicon glyphicon-refresh'
      },
      excluded: [':disabled', ':hidden', ':not(:visible)']
    })
    .on('err.field.fv', function(e, data) {
      e.preventDefault();
      if( data.fv.getSubmitButton() ) {
        data.fv.disableSubmitButtons(false);
      }
    })
    .on('success.field.fv', function(e, data) {
      e.preventDefault();
      if( data.fv.getSubmitButton() ) {
        data.fv.disableSubmitButtons(false);
      }
    })
    .on('success.form.fv', function(e) {
      e.preventDefault();
      if( isReady ) {
        var formValues = $("#dmodal form").serializeArray();

        var values = {};
        $.each(formValues, function() {
          if (values[this.name] || values[this.name] == '') {
            if (!values[this.name].push) {
              values[this.name] = [values[this.name]];
            }
            values[this.name].push(this.value || '');
          } else {
            values[this.name] = this.value || '';
          }
        });

        var params = {
          task: 'fixedprices',
          action: 'update'
        };

        $.ajax({
          headers : {
            'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
          },
          url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
          type: 'POST',
          dataType: 'json',
          cache: false,
          data: $.extend(params, values),
          success: function(response) {
            if(response.success) {
              $('#dmodal #statusMsg').html('<span class="text-green"><i class="fa fa-check-circle"></i> '+ msgSuccess +'</span>');

              if( parseInt(values.id) <= 0 ) {
                  $('#dmodal form').trigger('reset');
                  $('#dmodal').modal('hide');
              }

              switch( $('#dmodal form').find('#submit_action').val() ) {
                  case 'save':
                      $('#dmodal form').trigger('reset');
                      $('#dmodal').modal('hide');
                  break;
                  case 'save_edit':
                      //
                  break;
              }

              filterTable();
            }
            else {
                var msg = 'The data could not be updated';
                if (response.message) { msg = response.message; }
                $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ msg +'</span>');
            }
          },
          error: function(response) {
            $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
          },
          beforeSend: function() {
            $('#dmodal #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> In progress');
            isReady = 0;
          },
          complete: function() {
            isReady = 1;
          }
        });
      }
    });

    // Cancel button
    $('#dmodal .btnCancel').on('click', function() {
      $('#dmodal').modal('hide');
    });
  }

  // Page loaded
  $(document).ready(function(){
    $('[data-toggle="popover"]').popover({
        placement: 'auto right',
        container: 'body',
        trigger: 'click focus hover',
        html: true
    });

    $('body').on('click', function (e) {
        // did not click a popover toggle, or icon in popover toggle, or popover
        if ($(e.target).data('toggle') !== 'popover'
            && $(e.target).parents('[data-toggle="popover"]').length === 0
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });


    // Select
    $('.pageFilters .select2').select2();

    $('body').on('change', '.is_zone', function() {
      if($(this).closest('.field-is_zone').find(':checked').val() == '1') {
        $('#dmodal .field-start_postcode, #dmodal .field-end_postcode').addClass('hidden');
        $('#dmodal .field-end_zone, #dmodal .field-start_zone').removeClass('hidden');
        // $('#dmodal .field-start_postcode, #dmodal .field-end_postcode').find('select').attr('disabled', true);
        // $('#dmodal .field-end_zone, #dmodal .field-start_zone').find('select').attr('disabled', false);
      }
      else {
        $('#dmodal .field-start_postcode, #dmodal .field-end_postcode').removeClass('hidden');
        $('#dmodal .field-end_zone, #dmodal .field-start_zone').addClass('hidden');
        // $('#dmodal .field-start_postcode, #dmodal .field-end_postcode').find('select').attr('disabled', false);
        // $('#dmodal .field-end_zone, #dmodal .field-start_zone').find('select').attr('disabled', true);
      }
    });

    // Toggle filters
    // $('.pageFilters').toggle();
    $('.pageTitle .btnFilters').on('click', function() {
      $('.pageFilters').toggle();
    });

    // Reset form
    $('.btnReset').on('click', function(e) {
      // $('.pageFilters form').trigger('reset');
      $('.pageFilters form input[type="text"]').val('').trigger('change');

      $('.pageFilters form select.select2').each(function( index ) {
        $(this).val([]).trigger('change');
        // $(this).val(null).trigger('change');
        // $(this).select2('val', 0);
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
      // filterTable();
      e.preventDefault();
    });

    // Load filters
    $.ajax({
      headers : {
        'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
      },
      url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
      type: 'POST',
      dataType: 'json',
      cache: false,
      data: {
        task: 'fixedprices',
        action: 'init'
      },
      success: function(response) {

          // Services
          html = '';
          if (response.servicesList) {
            $.each(response.servicesList, function(index, item) {
              html += '<option value="'+ item.id +'">'+ item.name +'</option>';
            });
          }
          $('#filter-service_ids').html(html);

        // Type
        html = '';
        if (response.typeList) {
          $.each(response.typeList, function(index, item) {
            html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
        }
        $('#filter-type').html(html);

      },
      error: function(response) {
        // Msg
      }
    });

    var tableOptionsn = {
        processing: true,
        serverSide: true,
        ajax: {
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
            method: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                task: 'fixedprices',
                action: 'list'
            },
            dataSrc: 'list'
        },
        columns: [
                @if (auth()->user()->hasPermission(['admin.fixed_prices.edit', 'admin.fixed_prices.create', 'admin.fixed_prices.destroy'], true))
            {
            title: '',
            data: null,
            defaultContent: '',
            orderable: false,
            className: 'actionColumn'
        },
            @endif
            {
            title: '',
            data: null,
            defaultContent: '',
            orderable: false,
            className: 'infoColumn',
            width: '50px',
        }, {
            title: 'Services',
            data: 'service_ids',
            width: '200px',
            class: 'column-service_ids'
        },/* {
        title: 'Type',
        data: 'type',
        width: '150px',
        visible: false
      },*/ {
            title: 'Location type',
            data: 'is_zone_text',
            width: '150px',
            visible: false
        }, {
            title: 'From',
            data: 'start_postcode',
            width: '280px',
            orderable: false,
        }, {
            title: 'To',
            data: 'end_postcode',
            width: '280px',
            orderable: false,
        }, {
            title: 'Direction',
            data: 'direction',
            width: '130px'
        }, {
            title: 'Price',
            data: 'price',
            width: '250px',
            orderable: false
        }, {
            title: 'Deposit',
            data: 'deposit',
            width: '250px',
            orderable: false,
            class: '{{ config('site.fixed_prices_deposit_enable') ? '' : 'hide' }}',
        }, {
            title: 'Start date',
            data: 'start_date',
            width: '150px'
        }, {
            title: 'End date',
            data: 'end_date',
            width: '150px'
        }, {
            title: 'Active',
            data: 'published',
            width: '80px'
        }, {
            title: 'Ordering',
            data: 'ordering',
            width: '100px',
            visible: false
        }, {
            title: 'Modified date',
            data: 'modified_date',
            width: '140px',
            visible: false
        }, {
            title: 'Site ID',
            data: 'site_id',
            width: '100px',
            visible: false
        }, {
            title: 'ID',
            data: 'id',
            width: '50px',
            // class: 'hide',
            visible: false
        }],
        columnDefs: [{
            targets: 0,
            data: null,
            render: function(data, type, row) {
                var h = '';
                h += '<div class="btn-group" role="group" aria-label="..." style="width:140px;">';
            @permission('admin.fixed_prices.edit')
                h += '<button type="button" onclick="updateRecord('+ row.id +'); return false;" class="btn btn-default btn-sm btnEdit" title="Edit"><i class="fa fa-pencil-square-o"></i></button>';
            @endpermission
            @permission('admin.fixed_prices.create')
                h += '<button type="button" onclick="copyRecord('+ row.id +'); return false;" class="btn btn-default btn-sm btnCopy" title="Duplicate"><i class="fa fa-files-o"></i></button>';
            @endpermission
            @permission('admin.fixed_prices.destroy')
                h += '<button type="button" onclick="deleteRecord('+ row.id +'); return false;" class="btn btn-default btn-sm btnDelete" title="Delete"><i class="fa fa-trash"></i></button>';
            @endpermission
                h += '</div>';
                return h;
            }
        }, {
            targets: 1,
            data: null,
            render: function(data, type, row) {
                var h = '<div class="btn-group" role="group" aria-label="..." style="width:140px;">';
                h += row.is_zone ? '<span class="eto-fixedprice-symbol" data-toggle="tooltip" data-title="Zone">Z</span>' : '';
                h += '</div>';
                return h;
            }
        }],
        paging: true,
        pagingType: 'full_numbers',
        // dom: 'rt<"row"<"col-xs-12 col-md-5 dataTablesFooterLeft"li><"col-xs-12 col-md-7 dataTablesFooterRight"p>><"clear">',
        dom: '<"row topContainer"<"col-xs-12 col-sm-12 col-md-12 dataTablesHeaderLeft"><"col-xs-12 col-sm-6 col-md-5 dataTablesHeaderRight">><"dataTablesBody"rt><"row bottomContainer"<"col-xs-12 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-12 col-sm-6 col-md-5 dataTablesFooterLeft"liB>>',
        buttons: [
            {
                'extend': 'colvis',
                'collectionLayout': 'fixed two-column',
                'text': '<i class="fa fa-eye"></i>',
                'titleAttr': '{{ trans('admin/users.button.column_visibility') }}',
                'postfixButtons': ['colvisRestore'],
                'className': 'btn-datatable btn-sm'
            }, {
                'text': '<div onclick="$(\'#dtable\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                'titleAttr': '{{ trans('admin/users.button.reset') }}',
                'className': 'btn-datatable btn-sm'
            }, {
                'extend': 'reload',
                'text': '<i class="fa fa-refresh"></i>',
                'titleAttr': '{{ trans('admin/users.button.reload') }}',
                'className': 'btn-datatable btn-sm'
            }
        ],
        scrollX: true,
        searching: true,
        ordering: true,
        lengthChange: true,
        info: true,
        autoWidth: false,
        // stateSave: true,
        stateDuration: 0,
        order: [
            [14, 'desc']
        ],
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
        },
        infoCallback: function( settings, start, end, max, total, pre ) {
            return '<i class="ion-ios-information-outline" title="'+ pre +'"></i>';
        }
    };

    if (!ETO.hasPermission(['admin.fixed_prices.edit', 'admin.fixed_prices.create', 'admin.fixed_prices.destroy'], true)) {
        delete tableOptionsn.columnDefs[0];
    }

    // Table
    $('#dtable').DataTable(tableOptionsn)
    .on('draw.dt', function() {
      // $('.dataTables_length select').select2();

      // Edit button
      $('#dtable').find('button.btnEdit').hover(
        function() {
          $(this).removeClass('btn-default').addClass('btn-success');
        },
        function() {
          $(this).removeClass('btn-success').addClass('btn-default');
        }
      );

      // Delete button
      $('#dtable').find('button.btnDelete').hover(
        function() {
          $(this).removeClass('btn-default').addClass('btn-danger');
        },
        function() {
          $(this).removeClass('btn-danger').addClass('btn-default');
        }
      );
    });

  });
  </script>
@endsection
