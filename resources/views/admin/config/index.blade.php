@extends('admin.index')

@section('title', 'Settings')

@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">

  <style>
  .hide_advanced {
    display: none !important;
  }
  .eto-config-form-group {
    margin-bottom: 5px;
  }
  .eto-config-section-header {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 5px;
  }
  #config .pageTitle {
    margin-bottom: 20px;
  }
  #config .panel {
    border-radius: 0;
    -webkit-box-shadow: none;
    box-shadow: none;
  }
  #config .panel-group .panel+.panel {
    margin-top: 1px;
  }
  #config .panel-default {
    border-color: #eaeaea;
    border: 0;
  }
  #config .panel-heading {
    border: 1px #eaeaea solid;
    background-color: #fbfbfb;
  }
  #config .panel-body {
    padding: 14px 5px !important;
    border: 0;
  }
  /* #config .panel-title > a[aria-expanded="true"] {
    font-weight: bold !important;
    color: #000 !important;
  } */
  .field-booking_member_benefits {
    margin-bottom: 15px;
  }
  .field-booking_member_benefits textarea {
    min-height: 100px;
  }
  </style>
@endsection


@section('subcontent')
<div class="pageContainer" id="config">
    <div class="pageContent">
        <form method="post" autocomplete="false">

            <!-- This option will disable autocomplete option in Google Chrome -->
            <input type="text" name="randomusernameremembered" id="randomusernameremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">
            <input type="password" name="randompasswordremembered" id="randompasswordremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">
            <!-- End -->

            <input type="hidden" name="site_id" id="site_id" value="0">

            @if (Route::is('admin.config.index') && auth()->user()->hasPermission('admin.settings.general.index'))

                <div data-eto-section="admin.settings.general">
                    <div class="pageTitle">
                        <h3>Settings - General</h3>
                    </div>
                    @include('admin.config.general')
                </div>

            @elseif (Route::is('admin.config.localization') && auth()->user()->hasPermission('admin.settings.localization.index'))

                <div data-eto-section="admin.settings.localization">
                    <div class="pageTitle">
                        <h3>Settings - Localization</h3>
                    </div>
                    @include('admin.config.localization')
                </div>

            @elseif (Route::is('admin.config.booking') && auth()->user()->hasPermission('admin.settings.booking.index'))

                <div data-eto-section="admin.settings.booking">
                    <div class="pageTitle">
                        <h3>Settings - Booking</h3>
                    </div>
                    @include('admin.config.booking')
                </div>

            @elseif (Route::is('admin.config.auto-dispatch') && auth()->user()->hasPermission('admin.settings.auto_dispatch.index'))

                <div data-eto-section="admin.settings.auto_dispatch">
                    <div class="pageTitle clearfix">
                        <h3 style="float:left;">Settings - Auto Dispatch</h3>
                        <span style="float:left; margin-top:2px; margin-left:5px;" data-toggle="popover" data-title="" data-content="For the auto dispatch to dispatch jobs to the driver, you need to ensure a Vehicle type has been assigned to the driver's vehicle in Settings -> Vehicle -> option Vehicle type.">
                            <i class="ion-ios-information-outline" style="font-size:18px; color:#636363"></i>
                        </span>
                    </div>
                    @include('admin.config.auto_dispatch')
                </div>

            @elseif (Route::is('admin.config.web-booking-widget') && auth()->user()->hasPermission('admin.settings.web_booking_widget.index'))

                <div data-eto-section="admin.settings.web_booking_widget">
                    <div class="pageTitle">
                        <h3>Settings - Web Booking Widget</h3>
                    </div>
                    @include('admin.config.web_booking_widget')
                </div>

            @elseif (Route::is('admin.config.google') && auth()->user()->hasPermission('admin.settings.google.index'))

                <div data-eto-section="admin.settings.google">
                    <div class="pageTitle">
                        <h3>Settings - Google</h3>
                    </div>
                    @include('admin.config.google')
                </div>

            @elseif (Route::is('admin.config.mileage-time') && auth()->user()->hasPermission('admin.settings.mileage_time.index'))

                <div data-eto-section="admin.settings.mileage_time">
                    <div class="pageTitle">
                        <h3>Settings - Distance & Time Pricing</h3>
                    </div>
                    @include('admin.config.mileage_time')
                </div>

            @elseif (Route::is('admin.config.deposit-payments') && auth()->user()->hasPermission('admin.settings.deposit_payments.index'))

                <div data-eto-section="admin.settings.deposit_payments">
                    <div class="pageTitle">
                        <h3>Settings - Deposit Payments</h3>
                    </div>
                    @include('admin.config.deposit_payments')
                </div>

            @elseif (Route::is('admin.config.driver-income') && auth()->user()->hasPermission('admin.settings.driver_income.index'))

                <div data-eto-section="admin.settings.driver_income">
                    <div class="pageTitle">
                        <h3>Settings - {{ trans('admin/index.menu.settings.driver_income') }}</h3>
                    </div>
                    @include('admin.config.driver_income')
                </div>

            @elseif (Route::is('admin.config.bases') && auth()->user()->hasPermission('admin.settings.bases.index'))

                <div data-eto-section="admin.settings.bases">
                    <div class="pageTitle">
                        <h3>Settings - Operating Areas</h3>
                    </div>
                    @include('admin.config.bases')
                </div>

            @elseif (Route::is('admin.config.night-surcharge') && auth()->user()->hasPermission('admin.settings.night_surcharge.index'))

                <div data-eto-section="admin.settings.night_surcharge">
                    <div class="pageTitle">
                        <h3>Settings - Night Surcharge</h3>
                    </div>
                    @include('admin.config.night_surcharge')
                </div>

            @elseif (Route::is('admin.config.holiday-surcharge') && auth()->user()->hasPermission('admin.settings.holiday_surcharge.index'))

                <div data-eto-section="admin.settings.holiday_surcharge">
                    <div class="pageTitle">
                        <h3>Settings - Holiday / Rush Hours Surcharge</h3>
                    </div>
                    @include('admin.config.holiday_surcharge')
                </div>

            @elseif (Route::is('admin.config.additional-charges') && auth()->user()->hasPermission('admin.settings.additional_charges.index'))

                <div data-eto-section="admin.settings.additional_charges">
                    <div class="pageTitle">
                        <h3>Settings - Additional Charges</h3>
                    </div>
                    @include('admin.config.additional_charges')
                </div>

            @elseif (Route::is('admin.config.other-discounts') && auth()->user()->hasPermission('admin.settings.other_discounts.index'))

                <div data-eto-section="admin.settings.other_discount">
                    <div class="pageTitle">
                        <h3>Settings - Return Journey and Account Discounts</h3>
                    </div>
                    @include('admin.config.other_discounts')
                </div>

            @elseif (Route::is('admin.config.tax') && auth()->user()->hasPermission('admin.settings.tax.index'))

                <div data-eto-section="admin.settings.tax">
                    <div class="pageTitle">
                        <h3>Settings - Tax</h3>
                    </div>
                    @include('admin.config.tax')
                </div>

            @elseif (Route::is('admin.config.invoices') && auth()->user()->hasPermission('admin.settings.invoices.index'))

                <div data-eto-section="admin.settings.invoices">
                    <div class="pageTitle">
                        <h3>Settings - Invoices</h3>
                    </div>
                    @include('admin.config.invoices')
                </div>

            @elseif (Route::is('admin.config.airport-detection') && auth()->user()->hasPermission('admin.settings.airport_detection.index'))

                <div data-eto-section="admin.settings.airport_detection">
                    <div class="pageTitle">
                        <h3>Settings - Airport Detection</h3>
                    </div>
                    @include('admin.config.airport_detection')
                </div>

            @elseif (Route::is('admin.config.users') && auth()->user()->hasPermission('admin.settings.users.index'))

                <div data-eto-section="admin.settings.users">
                    <div class="pageTitle">
                        <h3>Settings - Users</h3>
                    </div>
                    @include('admin.config.users')
                </div>

            @elseif (Route::is('admin.config.styles') && auth()->user()->hasPermission('admin.settings.styles.index'))

                <div data-eto-section="admin.settings.styles">
                    <div class="pageTitle">
                        <h3>Settings - Styles</h3>
                    </div>
                    @include('admin.config.styles')
                </div>

            @elseif (Route::is('admin.config.integration') && auth()->user()->hasPermission('admin.settings.integration.index'))

                <div data-eto-section="admin.settings.integration">
                    <div class="pageTitle">
                        <h3>Settings - Integration</h3>
                    </div>
                    @include('admin.config.integration')
                </div>

            @elseif (Route::is('admin.config.debug') && auth()->user()->hasPermission('admin.settings.debug.index'))

                <div data-eto-section="admin.settings.debug">
                    <div class="pageTitle">
                        <h3>Settings - Debug</h3>
                    </div>
                    @include('admin.config.debug')
                </div>

            @endif

            <div class="form-buttons" style="margin-top:20px;">
                <button type="submit" class="btn btn-success btnSave" title="Save"><i class="fa fa-pencil-square-o"></i> <span>Save</span></button>
                <span id="statusMsg"></span>
            </div>

            <div class="modal-overlay"><i class="fa fa-refresh fa-spin"></i></div>
        </form>

    </div>
</div>
@endsection


@section('subfooter')
  <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset_url('plugins','data-tables/dataTables.bootstrap.min.js') }}"></script>
  <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
  <script src="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.min.js') }}"></script>
  <script src="{{ asset_url('plugins','jquery-serialize-object/jquery.serialize-object.min.js') }}"></script>
  <script src="{{ asset_url('plugins','form-validation/formValidation.min.js') }}"></script>
  <script src="{{ asset_url('plugins','form-validation/formValidation-bootstrap.min.js') }}"></script>
  <script src="{{ asset_url('plugins','md5/md5.min.js') }}"></script>
  <script src="{{ asset_url('js','eto/eto.js') }}"></script>

  <script>
  function checkRefNumber() {
      var tags = [
          '{pickupDateTime}',
          '{pickupDate}',
          '{pickupTime}',
          '{pickupDateTimeFormatted}',
          '{pickupDateFormatted}',
          '{pickupTimeFormatted}',
          '{createDateTime}',
          '{createDate}',
          '{createTime}',
          '{createDateTimeFormatted}',
          '{createDateFormatted}',
          '{createTimeFormatted}',
          '{year}',
          '{month}',
          '{day}',
          '{hour}',
          '{minute}',
          '{second}',
          '{rand}',
          '{rand2}',
          '{rand3}',
          '{rand4}',
          '{rand5}',
          '{rand6}',
          '{rand7}',
          '{rand8}',
          '{rand9}',
          '{rand10}',
          '{id}'
      ];
      var refFormat = $('form #ref_format').val();
      var exists = false;

      for (var i = 0; i < tags.length; i++) {
          if (refFormat.indexOf(tags[i]) > -1) {
              exists = true;
          }
      }
      return exists;
  }

  function checkPermission(container) {
      container.each(function() {
          var section = $(this).data('etoSection');

          if (typeof section != "undefined" && !ETO.hasPermission(section + '.edit')) {
              $(this).find(".select2").select2({disabled:'readonly'});
              $(this).find("select:not(.select2), input").attr('readonly', true);
          }
      });
  }

  $(document).ready(function(){
      if (ETO.model === false) {
          ETO.init({ config: [], lang: ['user'] }, 'settings');
      }

      $('body').on('click', '.eto-pass-generate', function(e) {
          $(this).closest('.input-group').find('input').val(md5(generatePassword(20)));
      })
      .on('click', '.eto-pass-view', function(e) {
          $(this).closest('.input-group').find('input').attr('type', 'text');
          $(this).removeClass('eto-pass-view').addClass('eto-pass-hide');
          $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
      })
      .on('click', '.eto-pass-hide', function(e) {
          $(this).closest('.input-group').find('input').attr('type', 'password');
          $(this).removeClass('eto-pass-hide').addClass('eto-pass-view');
          $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
      })
      .on('click', '.panel a', function(e) {
          var container = $(this).closest('.panel');

          checkPermission(container);
      })
      // Booking ref number check
      .on('click', '.btnSave', function(e) {
          if ($('form #ref_format').length > 0 && !checkRefNumber()) {
              alert('Settings->Booking->Booking Reference Number option has to contain at least one auto generated tag. All available tags are displayed in help section next to the field.');
              e.preventDefault();
              e.stopPropagation();
          }
      });


    // Select
    $('#config form .select2').select2({
        minimumResultsForSearch: 'Infinity'
    });

    // Spinner
    $('#config form input[type="text"].touchspin').TouchSpin({
      max: null,
      booster: true,
      boostat: 5,
      mousewheel: true,
      verticalbuttons: true,
      verticalupclass: 'fa fa-plus',
      verticaldownclass: 'fa fa-minus'
    });

    $('.eto-send-test-email button').on('click', function () {
        var email = $(this).closest('.eto-send-test-email').find('#test_mail').val(),
            re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

        if (email == '' || !re.test(String(email).toLowerCase())) {
            alert('Please enter your email address and try again.');
            return false;
        }

        $.ajax({
            headers : {
              'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: EasyTaxiOffice.appPath +'/get-config/sendTestEmail',
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                email: email,
            },
            success: function(response) {
              if(response.status == 'OK') {
                  alert('Test message has been successfully sent.');
                  // swal.mixin({
                  //   // toast: true,
                  //   // position: 'top-end',
                  //   showConfirmButton: false,
                  //   timer: 3000,
                  //   type: 'success',
                  //   title: 'Test message has been successfully sent.',
                  //   text: 'Test message has been successfully sent.',
                  // });
              }
              else if(response.status == 'FAIL') {
                  alert(response.message);
                  // swal.mixin({
                  //   // toast: true,
                  //   // position: 'top-end',
                  //   type: 'danger',
                  //   title: response.message
                  // });
              }
              else {
                  alert('Test message could not be sent.');
                  // swal.mixin({
                  //   // toast: true,
                  //   // position: 'top-end',
                  //   type: 'danger',
                  //   title: 'Test message could not be sent.'
                  // });
              }
            },
            error: function(response) {
                alert('Test message could not be sent.');
                // swal.mixin({
                //   // toast: true,
                //   // position: 'top-end',
                //   type: 'danger',
                //   title: 'Test message could not be sent.'
                // });
            },
        });
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

    $('#config form').find('input:not([type="submit"]), textarea, select').each(function() {
        updateFormPlaceholder(this);
    })
    .bind('change keyup', function(e) {
        updateFormPlaceholder(this);
    });


    // Load data
    $.ajax({
      headers : {
        'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
      },
      url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
      type: 'POST',
      dataType: 'json',
      cache: false,
      data: {
        task: 'config',
        action: 'read'
      },
      success: function(response) {
        if( response.success ) {
          $('#config #statusMsg').html('');

          var html = '';

          // Vehicle min price
          var options = '';
          $.each(response.vehicleList, function(key2, value2) {
              options += '<option value="'+ value2.id +'">'+ value2.name +'</option>';
          });

          if( !options ) {
              options += '<option value="0">-- Select --</option>';
          }

          html = '<div class="table-responsive2">\
                    <table class="table table-condensed table-hover1" cellspacing="0" width="100%" style="width:auto; margin-bottom:10px; border:0;">\
                    <tfoot>\
                      <tr>\
                        <td style="padding:0px 0 0 0; border:0;">\
                            <div class="form-group field-vehicle_min_price_options" style="margin:0; width:100%; max-width:100%;" id="vehicle_min_price_panel">\
                                <div class="input-group">\
                                    <select id="vehicle_min_price_options" class="form-control">\
                                        '+ options +'\
                                    </select>\
                                    <div class="input-group-addon btnNewVehicleMinPriceList" title="Add" style="cursor:pointer;">\
                                        <i class="fa fa-plus"></i>\
                                    </div>\
                                </div>\
                            </div>\
                        </td>\
                      </tr>\
                    </tfoot>\
                    <tbody></tbody>\
                    </table>\
                  </div>';

          $('#vehicleMinPriceList').html(html);

          $('#vehicleMinPriceList .btnNewVehicleMinPriceList').hover(
              function() {
                  $(this).removeClass('btn-default').addClass('btn-success');
              },
              function() {
                  $(this).removeClass('btn-success').addClass('btn-default');
              }
          );

          var lastIndexVMP = 0;

          function updateVehicleMinPriceOptions() {
              var hidden = 0;

              $('#vehicle_min_price_options option').show();
              $('#vehicleMinPriceList #vehicle_min_price_id').each(function() {
                  $('#vehicle_min_price_options option[value="'+ $(this).val() +'"]').hide();
                  hidden++;
              });

              if ($('#vehicle_min_price_options option').length <= hidden) {
                  $('#vehicle_min_price_panel').hide();
              }
              else {
                  $('#vehicle_min_price_panel').show();
              }

              if ($('#vehicleMinPriceList #vehicle_min_price_id').length > 0) {
                  $('#vehicleMinPriceList table tbody').show();
              }
              else {
                  $('#vehicleMinPriceList table tbody').hide();
              }

              $('#vehicle_min_price_options option').each(function() {
                  if ($(this).css('display') != 'none') {
                      // $(this).prop("selected", true);
                      $('#vehicle_min_price_options').val($(this).val());
                      return false;
                  }
              });
          }

          updateVehicleMinPriceOptions();

          function newVehicleMinPriceList(id) {
            var html = '';
            var vId = 0;
            var vName = '';

            var selected = $('#vehicle_min_price_options option[value="'+ id +'"]');
            if (parseInt(selected.val()) > 0) {
                vId = parseInt(selected.val());
                vName = selected.text();
            }

            html = '<tr class="vehicleMinPriceListRow'+ lastIndexVMP +'">\
                      <!--<td style="vertical-align:middle; padding:2px 10px 6px 0; border:0; min-width:84px;">\
                        <span id="vehicle_min_price_name">'+ vName +'</span>\
                      </td>-->\
                      <td style="padding:2px 0px 6px 0; border:0;">\
                        <input type="hidden" name="vehicleMinPriceList['+ lastIndexVMP +'][id]" id="vehicle_min_price_id" value="'+ vId +'" required class="form-control">\
                        <div class="form-group field-vehicle_min_price_value" style="margin:0; max-width:100%;">\
                          <div class="input-group">\
                              <div class="input-group-addon" style="min-width:180px; text-align:left; background: #f7f7f7;">\
                                  '+ vName +'\
                              </div>\
                              <input type="number" name="vehicleMinPriceList['+ lastIndexVMP +'][value]" id="vehicle_min_price_value" value="0" required class="form-control" min="0" step="0.01" style="width:80px;">\
                              <div class="input-group-addon btnDelete" title="Delete" style="cursor:pointer;">\
                                  <i class="fa fa-minus"></i>\
                              </div>\
                          </div>\
                        </div>\
                      </td>\
                    </tr>';

            $('#vehicleMinPriceList table tbody').append(html);

            $('#vehicleMinPriceList .vehicleMinPriceListRow'+ lastIndexVMP).find('.btnDelete')
            .hover(
                function() {
                    $(this).removeClass('btn-default').addClass('btn-danger');
                },
                function() {
                    $(this).removeClass('btn-danger').addClass('btn-default');
                }
            )
            .on('click', function() {
                $('[data-toggle="popover"]').popover('hide');
                $('#config [title]').tooltip('hide');
                $(this).closest('tr').remove();
                updateVehicleMinPriceOptions();
                return false;
            });

            var index = lastIndexVMP;
            lastIndexVMP++;
            return index;
          }

          $('#vehicleMinPriceList .btnNewVehicleMinPriceList').on('click', function(e) {
              var vId = 0;
              var selected = $('#vehicle_min_price_options option:selected');
              if (parseInt(selected.val()) > 0) {
                  vId = parseInt(selected.val());
              }
              if (vId <= 0) { return false; }
              newVehicleMinPriceList(vId);
              updateVehicleMinPriceOptions();
              e.preventDefault();
          });


          // Bases
          html = '<div class="table-responsive">\
                    <table class="table table-condensed table-hover" cellspacing="0" width="100%" style="width:auto;margin-bottom:10px;">\
                    <thead>\
                      <tr>\
                        <td>Address</td>\
                        <td>Radius (mi/km)</td>\
                        <td>Status</td>\
                        <td></td>\
                      </tr>\
                    </thead>\
                    </table>\
                  </div>';

            if(ETO.hasPermission('admin.settings.bases.edit')) {
                html += '<button type="button" class="btn btn-success btn-xs btnNewBasesList" title="New"><i class="fa fa-plus"></i> <span>New</span></button>';
            }

          $('#basesList').html(html);
          var lastIndex = 0;

          function newBasesList() {
            var html = '',
                readonly = !ETO.hasPermission('admin.settings.bases.edit') ? 'readonly' : '',
                delBtnBases = ETO.hasPermission('admin.settings.bases.edit') ? '<button type="button" onclick="$(this).closest(\'tr\').remove(); return false;" class="btn btn-default btn-md btnDelete" title="Delete"><i class="fa fa-minus"></i></button>' : '';

            html += '<tr class="basesListRow'+ lastIndex +'">\
                      <td>\
                        <input type="hidden" name="basesList['+ lastIndex +'][id]" id="bases_id" placeholder="" value="0" required class="form-control" '+readonly+'>\
                        <input type="text" name="basesList['+ lastIndex +'][address]" id="bases_address" placeholder="Address..." value="" required class="form-control" style="width:250px;">\
                      </td>\
                      <td>\
                        <input type="number" name="basesList['+ lastIndex +'][radius]" id="bases_radius" value="1" required class="form-control" min="1" step="1" style="width:100px;" '+readonly+'>\
                      </td>\
                      <td>\
                          <select name="basesList['+ lastIndex +'][status]" id="bases_status" class="form-control" required>\
                              <option value="activated">Active</option>\
                              <option value="inactive">Inactive</option>\
                          </select>\
                      </td>\
                      <td>\
                        '+delBtnBases+'\
                      </td>\
                    </tr>';

            $('#basesList table').append(html);

            // Spinner
            $('#basesList input[type="text"].touchspin').TouchSpin({
              max: null,
              booster: true,
              boostat: 5,
              mousewheel: true,
              verticalbuttons: true,
              verticalupclass: 'fa fa-plus',
              verticaldownclass: 'fa fa-minus'
            });

            // Delete button
            $('#basesList').find('button.btnDelete').hover(
              function() {
                $(this).removeClass('btn-default').addClass('btn-danger');
              },
              function() {
                $(this).removeClass('btn-danger').addClass('btn-default');
              }
            );

            var index = lastIndex;
            lastIndex++;
            return index;
          }

          $('#basesList .btnNewBasesList').on('click', function(e) {
            newBasesList();
            e.preventDefault();
          });


            // Items
            html = '<div class="table-responsive">\
                      <table class="table table-condensed table-hover" cellspacing="0" width="100%" style="width:auto;margin-bottom:10px;">\
                      <thead>\
                        <tr>\
                          <td>Name</td>\
                          <td>Price</td>\
                          <td>Type</td>\
                          <td>Options</td>\
                          <td></td>\
                        </tr>\
                      </thead>\
                      </table>\
                    </div>';

            if(ETO.hasPermission('admin.settings.additional_charges.edit')) {
                html += '<button type="button" class="btn btn-success btn-xs btnNewItemsList" title="New"><i class="fa fa-plus"></i> <span>New</span></button>';
            }
            $('#itemsList').html(html);

            var lastIndex = 0;

            function newItemsList() {
              var html = '';

              html = '<tr class="itemsListRow'+ lastIndex +'">\
                        <td>\
                          <input type="text" name="itemsList['+ lastIndex +'][name]" id="item_name" placeholder="" value="" required class="form-control" style="width:200px;">\
                        </td>\
                        <td>\
                          <input type="number" name="itemsList['+ lastIndex +'][value]" id="item_value" placeholder="" value="0" required class="form-control" style="width:80px;" step="0.01" min="0">\
                        </td>\
                        <td>\
                          <select name="itemsList['+ lastIndex +'][type]" id="item_type" class="form-control" required style="width:100px;">\
                              <option value="amount">Amount</option>\
                              <option value="custom">Select</option>\
                              <option value="input">Input</option>\
                              <option value="address" class="additional-item-address">Address</option>\
                          </select>\
                        </td>\
                        <td>\
                          <input type="number" name="itemsList['+ lastIndex +'][amount]" id="item_amount" placeholder="" value="1" required class="form-control" step="1" min="1" title="Max available amount" style="width:70px; display:none;">\
                          <input type="text" name="itemsList['+ lastIndex +'][custom]" id="item_custom" placeholder="e.g. Option 1, Option 2 etc." value="" required class="form-control" style="width:200px; display:none;">\
                        </td>\
                        <td>\
                          <button type="button" onclick="$(this).closest(\'tr\').remove(); return false;" class="btn btn-default btn-sm btnDelete" title="Delete">\
                            <i class="fa fa-trash"></i>\
                          </button>\
                        </td>\
                      </tr>';

              $('#itemsList table').append(html);

              // Type
              $('#itemsList #item_type').change(function() {
                  $(this).closest('tr').find('#item_amount').hide();
                  $(this).closest('tr').find('#item_custom').hide();

                  if( $(this).val() == 'amount' ) {
                      $(this).closest('tr').find('#item_amount').show();
                  }
                  else if( $(this).val() == 'custom' ) {
                      $(this).closest('tr').find('#item_custom').show();
                  }
              }).change();

              // Delete button
              $('#itemsList').find('button.btnDelete').hover(
                function() {
                  $(this).removeClass('btn-default').addClass('btn-danger');
                },
                function() {
                  $(this).removeClass('btn-danger').addClass('btn-default');
                }
              );

              var index = lastIndex;
              lastIndex++;
              return index;
            }

            $('#itemsList .btnNewItemsList').on('click', function(e) {
              newItemsList();
              e.preventDefault();
            });


            // Deposit
            var options = '';
            $.each(response.vehicleList, function(key2, value2) {
                options += '<option value="'+ value2.id +'">'+ value2.name +'</option>';
            });

            html = '<div class="table-responsive">\
                      <table class="table table-condensed table-hover table-depositList" cellspacing="0" width="100%" style="width:auto;margin-bottom:10px;">\
                      <thead>\
                        <tr>\
                          <td>Name</td>\
                          <td>Type</td>\
                          <td>Value</td>\
                          <td></td>\
                        </tr>\
                      </thead>\
                      <tbody></tbody>\
                      <tfoot>\
                      </tfoot>\
                      </table>\
                    </div>';

            $('#depositList').html(html);
            if(ETO.hasPermission('admin.settings.deposit_payments.edit')) {
                $('.table-depositList tfoot').append('<tr>\
                    <td style="width:180px;">\
                    <select id="deposit_id" placeholder="Name" class="form-control select2" data-minimum-results-for-search="Infinity">\
                    <option value="0">Default</option>\
                    ' + options + '\
                    </select>\
                    </td>\
                    <td style="width:130px;">\
                    <select id="deposit_type" placeholder="Type" class="form-control select2" data-minimum-results-for-search="Infinity">\
                    <option value="multiplication">Percent (%)</option>\
                    <option value="addition">Flat (+)</option>\
                    </select>\
                    </td>\
                    <td>\
                    <input type="text" id="deposit_value" placeholder="Value" value="0" required class="form-control touchspin" style="width:80px;" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0">\
                    </td>\
                    <td>\
                    <button type="button" class="btn btn-success btn-sm btnNewDepositList" title="New">\
                    <i class="fa fa-plus"></i>\
                    </button>\
                    </td>\
                    </tr>');
            }
            // Spinner
            $('#depositList input[type="text"].touchspin').TouchSpin({
              max: null,
              booster: true,
              boostat: 5,
              mousewheel: true,
              verticalbuttons: true,
              verticalupclass: 'fa fa-plus',
              verticaldownclass: 'fa fa-minus'
            });

            // Select
            $('#depositList .select2').select2({
                minimumResultsForSearch: 'Infinity'
            });

            var depositListIndex = 0;

            function createDepositRow(data) {
                var html = '';

                html += '<tr id="deposit_row_'+ data.id +'">';
                    html += '<td>';
                        html += '<input type="hidden" name="depositList['+ depositListIndex +'][id]" value="'+ data.id +'">';
                        var name = 'Default';
                        if( data.id ) {
                            $.each(response.vehicleList, function(key2, value2) {
                                if( data.id == value2.id ) {
                                    name = value2.name;
                                }
                            });
                        }
                        html += '<span style="margin-top:5px; display:inline-block;">'+ name +'</span>';
                    html += '</td>';
                    html += '<td>';
                        html += '<input type="hidden" name="depositList['+ depositListIndex +'][type]" value="'+ data.type +'">';
                        var name = 'Unknown';
                        if( data.type == 'multiplication' ) {
                            name = 'Percent (%)';
                        }
                        else if( data.type == 'addition' ) {
                            name = 'Flat (+)';
                        }
                        html += '<span style="margin-top:5px; display:inline-block;">'+ name +'</span>';
                    html += '</td>';
                    html += '<td>';
                        html += '<input type="hidden" name="depositList['+ depositListIndex +'][value]" value="'+ data.value +'">';
                        html += '<span style="margin-top:5px; display:inline-block;">'+ data.value +'</span>';
                    html += '</td>';
                    html += '<td>';
                    if(ETO.hasPermission('admin.settings.deposit_payments.edit')) {
                        html += '<button type="button" onclick="$(this).closest(\'tr\').remove(); return false;" class="btn btn-default btn-sm btnDelete" title="Delete">';
                        html += '<i class="fa fa-trash"></i>';
                        html += '</button>';
                    }
                    html += '</td>';

                html += '</tr>';

                $('#depositList table').append(html);

                // Delete button
                $('#depositList').find('button.btnDelete').hover(
                  function() {
                    $(this).removeClass('btn-default').addClass('btn-danger');
                  },
                  function() {
                    $(this).removeClass('btn-danger').addClass('btn-default');
                  }
                );

                var index = depositListIndex;
                depositListIndex++;
                return index;
            }

            $('#depositList .btnNewDepositList').on('click', function(e) {
                if( $('#deposit_row_'+ $('#deposit_id').val()).length <= 0 ) {
                    rData = {}
                    rData.id = $('#deposit_id').val();
                    rData.type = $('#deposit_type').val();
                    rData.value = $('#deposit_value').val();
                    createDepositRow(rData);
                }
                else {
                    alert('This deposit already exists!');
                }
                e.preventDefault();
            });


            // Night surcharge
            var oV = '';
            $.each(response.vehicleList, function(kV, vV) {
              oV += '<option value="'+ vV.id +'">'+ vV.name +'</option>';
            });

            var oT = '';
            for (var iT = 0; iT <= 23; iT++) {
              for (var jT = 0; jT < 60; jT+=5) {
                var vT = (iT < 10 ? '0'+ iT : iT) +':'+ (jT < 10 ? '0'+ jT : jT);
                oT += '<option value="'+ vT +'">'+ vT +'</option>';
              }
            }

            html = '<div class="table-responsive">\
              <table class="table table-condensed table-hover" cellspacing="0" width="100%" style="width:auto; margin-bottom:10px; border:0;">\
              <thead>\
                <tr>\
                  <td>Vehicle</td>\
                  <td>Time</td>\
                  <td>Surcharge</td>\
                  <td></td>\
                </tr>\
              </thead>\
              <tbody></tbody>\
              <tfoot>\
                <tr>\
                  <td colspan="4" style="min-width:500px;">\
                    <div style="border:1px solid #f4f4f4; padding:10px;">\
                      <div class="clearfix" style="margin-bottom:10px;">\
                        <select id="night_surcharge_vehicle_id" placeholder="Vehicle" class="form-control" style="width:160px; float:left; padding:4px; margin-right:5px; height:30px;">\
                          <option value="0">All</option>\
                          '+ oV +'\
                        </select>\
                        <select id="night_surcharge_time_start" placeholder="From" class="form-control" style="width:70px; float:left; padding:4px; margin-right:5px; height:30px;">\
                          '+ oT +'\
                        </select>\
                        <span style="display:block; float:left; margin:5px 5px 5px 5px;">-</span>\
                        <select id="night_surcharge_time_end" placeholder="To" class="form-control" style="width:70px; float:left; padding:4px; margin-right:5px; height:30px;">\
                          '+ oT +'\
                        </select>\
                        <select id="night_surcharge_factor_type" placeholder="Factor" class="form-control" style="width:40px; float:left; margin-right:5px; padding:4px; height:30px;">\
                          <option value="multiplication">*</option>\
                          <option value="addition">+</option>\
                        </select>\
                        <input type="number" id="night_surcharge_factor_value" placeholder="Value" value="0" required class="form-control" style="width:70px; float:left; margin-right:5px; padding:4px; height:30px;" step="0.01" min="0">\
                        <button type="button" class="btn btn-success btn-sm btnNewNightSurchargeList" title="New">\
                          <i class="fa fa-plus"></i>\
                        </button>\
                      </div>\
                      <div class="clearfix">\
                        <label class="checkbox-inline"><input type="checkbox" value="1" class="ns_repeat_days_add">Mon</label>\
                        <label class="checkbox-inline"><input type="checkbox" value="2" class="ns_repeat_days_add">Tue</label>\
                        <label class="checkbox-inline"><input type="checkbox" value="3" class="ns_repeat_days_add">Wed</label>\
                        <label class="checkbox-inline"><input type="checkbox" value="4" class="ns_repeat_days_add">Thu</label>\
                        <label class="checkbox-inline"><input type="checkbox" value="5" class="ns_repeat_days_add">Fri</label>\
                        <label class="checkbox-inline"><input type="checkbox" value="6" class="ns_repeat_days_add">Sat</label>\
                        <label class="checkbox-inline"><input type="checkbox" value="0" class="ns_repeat_days_add">Sun</label>\
                      </div>\
                    </div>\
                  </td>\
                </tr>\
              </tfoot>\
              </table>\
            </div>';

            $('#nightSurchargeList').html(html);

            var lastNightSurchargeIndex = 0;

            function createNightSurchargeRow(data) {
              var html = '';

              html += '<tr id="night_surcharge_row_'+ lastNightSurchargeIndex +'">';
                  html += '<td>';
                    html += '<input type="hidden" name="nightSurchargeList['+ lastNightSurchargeIndex +'][vehicle_id]" class="ns_vehicle_id" value="'+ data.vehicle_id +'">';
                    var name = 'All';
                    if( data.vehicle_id ) {
                      $.each(response.vehicleList, function(kV, vV) {
                        if( data.vehicle_id == vV.id ) {
                          name = vV.name;
                        }
                      });
                    }
                    html += '<span style="margin-top:5px; display:inline-block;">'+ name +'</span>';
                  html += '</td>';
                  html += '<td>';
                    var repeat_days_ids = '';
                    var repeat_days_names = '';
                    var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                    if (data.repeat_days && data.repeat_days.length > 0) {
                      $.each(data.repeat_days, function(rDk, rDv) {
                        repeat_days_ids += (repeat_days_ids ? ',' : '') + rDv;
                        repeat_days_names += (repeat_days_names ? ', ' : '') + days[rDv];
                      });
                    }
                    else {
                      repeat_days_names = 'Mon-Sun';
                    }

                    html += '<input type="hidden" name="nightSurchargeList['+ lastNightSurchargeIndex +'][repeat_days]" class="ns_repeat_days" value="'+ repeat_days_ids +'">';
                    html += '<input type="hidden" name="nightSurchargeList['+ lastNightSurchargeIndex +'][time_start]" class="ns_time_start" value="'+ data.time_start +'">';
                    html += '<input type="hidden" name="nightSurchargeList['+ lastNightSurchargeIndex +'][time_end]" class="ns_time_end" value="'+ data.time_end +'">';
                    html += '<span style="margin-top:5px; display:inline-block;">'+ repeat_days_names +', '+ data.time_start +' - '+ data.time_end +'</span>';
                  html += '</td>';
                  html += '<td>';
                    html += '<input type="hidden" name="nightSurchargeList['+ lastNightSurchargeIndex +'][factor_type]" class="ns_factor_type" value="'+ data.factor_type +'">';
                    html += '<input type="hidden" name="nightSurchargeList['+ lastNightSurchargeIndex +'][factor_value]" class="ns_factor_value" value="'+ data.factor_value +'">';
                    html += '<span style="margin-top:5px; display:inline-block;">'+ (data.factor_value > 0 ? (data.factor_type == 'multiplication' ? '*' : '+') + data.factor_value : '') +'</span>';
                  html += '</td>';
                  html += '<td>';
                  if(ETO.hasPermission('admin.settings.night_surcharge.edit')) {
                      html += '<button type="button" onclick="$(this).tooltip(\'hide\'); $(this).closest(\'tr\').remove(); return false;" class="btn btn-default btn-sm btnDelete" title="Delete">';
                      html += '<i class="fa fa-minus"></i>';
                      html += '</button>';
                  }
                  html += '</td>';
              html += '</tr>';

              $('#nightSurchargeList table tbody').append(html);

              // Delete button
              $('#nightSurchargeList').find('button.btnDelete').hover(
                function() {
                  $(this).removeClass('btn-default').addClass('btn-danger');
                },
                function() {
                  $(this).removeClass('btn-danger').addClass('btn-default');
                }
              );

              var index = lastNightSurchargeIndex;
              lastNightSurchargeIndex++;
              return index;
            }

            $('#nightSurchargeList .btnNewNightSurchargeList').on('click', function(e) {
              var exists = 0;

              var repeat_days = [];
              $('#nightSurchargeList table tfoot .ns_repeat_days_add:checked').each(function() {
                repeat_days.push($(this).val());
              });

              var ns = {
                vehicle_id: $('#night_surcharge_vehicle_id').val(),
                repeat_days: repeat_days,
                time_start: $('#night_surcharge_time_start').val(),
                time_end: $('#night_surcharge_time_end').val(),
                factor_type: $('#night_surcharge_factor_type').val(),
                factor_value: $('#night_surcharge_factor_value').val()
              };

              $('#nightSurchargeList .ns_vehicle_id').each(function() {
                var row = $(this).closest('tr');

                if( row.find('.ns_vehicle_id').val() == ns.vehicle_id &&
                    JSON.stringify(row.find('.ns_repeat_days').val().split(',')) == JSON.stringify(ns.repeat_days) &&
                    row.find('.ns_time_start').val() == ns.time_start &&
                    row.find('.ns_time_end').val() == ns.time_end &&
                    row.find('.ns_factor_type').val() == ns.factor_type &&
                    row.find('.ns_factor_value').val() == ns.factor_value ) {
                  exists = 1;
                }
              });

              if (!exists ) {
                createNightSurchargeRow(ns);
              }
              else {
                alert('This night surcharge already exists!');
              }

              e.preventDefault();
            });


          // Milage table
          if( response.vehicleList ) {
            var th = '';

            $.each(response.vehicleList, function(key, value) {
              th += '<td><span>'+ value.name +'</span></td>';
            });

            html = '<div class="table-responsive">\
                      <table class="table table-condensed table-hover table-blank-inputs" cellspacing="0" width="100%" style="margin-bottom:10px; width:auto;">\
                          <thead>\
                            <tr>\
                              <td><span style="float:left; margin-right:5px;">Distance (mi/km)</span> <i class="ion-ios-information-outline" title="<div style=\'text-align:left;\'><b>Distance (mi/km)</b><br>Distance allows setting up a number of pricing thresholds charge between X to X mi/km.<br><br>If first distance is set 10, system will automatically recognise that this threshold is 0 to 10 mi/km.<br>If second distance is set 20, the system will automatically recognise that last threshold was 10 and it will set that this threshold is from 10 to 20 mi/km.<br>If second distance is set 50, the system will automatically recognise that last threshold was 20 and it will set that this threshold is from 20 to 50 mi/km.<br>So on..<br><br>We recommend to set last as a higher number e.g. 9999. Otherwise prices will be display 0 above last threshold.</div>"></i></td>\
                              <td><span style="float:left; margin-right:5px;">Base price (mi/km)</span> <i class="ion-ios-information-outline" title="<div style=\'text-align:left;\'><b>Base Price (mi/km)</b><br>Defines how much is charge per single mi/km.</div>"></i></td>\
                              <td><span style="float:left; margin-right:5px;">Base price modifier</span> <i class="ion-ios-information-outline" title="<div style=\'text-align:left;\'><b>Base Price Modifier</b> and <b>Vehicle Type</b> (Saloon, Estate, Executive, etc) allow additionally influence the price<br><br>\
                                <ul><li>When Base price modifier is set to Override, it will completely override the Base price.<br>\
                                If set Saloon value to \'2.5\', it will calculates that journey with Saloon will cost £2.5 per mi/km.<br>\
                                If set Estate value to \'2.7\', it will calculates that journey with Estate will cost £2.7 per mi/km.<br><br></li>\
                                <li>When Base price modifier is set to Add, it will add additional value to Base price.<br>\
                                If set Saloon value to \'0\', it will add £0 to journey with Saloon, calculating £2.5 + £0 = £2.5 per mile. (no increase)<br>\
                                If set Estate value to \'0.2\', it will add £0.2 to journey with Estate, calculating £2.5 + £0.2 = £2.7 per mile. (20p increase)<br><br></li>\
                               <li>When Base price modifier is set to Multiply, it will multiply Base price by certain percentage<br>set Saloon value to \'1\' and it will calculates that journey with Saloon will cost £2.5 * 1 = £2.5 per mile (no increase)<br>\
                               set Estate value to \'1.1\' and it will calculates that journey with Estate will cost £2.5 * 1.1 = £2.75 per mile (10% increase in comparison to Saloon)</li></ul></div>"></i></td>\
                              '+ th +'\
                              <td></td>\
                            </tr>\
                          </thead>\
                          <tbody></tbody>\
                      </table>\
                    </div>';
              @permission('admin.settings.mileage_time.edit')
              html += '<button type="button" class="btn btn-success btn-xs btnNewDistanceRange" title="New">\
                      <i class="fa fa-plus"></i> <span>New</span>\
                    </button>';
              @endpermission
            $('#distanceRangesList').html(html);

            var lastIndex1 = 0;

            function newDistanceRange(type) {
              var html = '';
              var td = '';

              $.each(response.vehicleList, function(key, value) {
                td += '<td>\
                        <input type="number" name="distanceRanges['+ lastIndex1 +'][vehicle'+ value.id +']" id="range_vehicle_'+ value.id +'" placeholder="" value="0" required class="form-control touchspin range_vehicle" step="0.01" data-bts-decimals="2" min="0">\
                      </td>';
              });

              html = '<tr class="distanceRangesRow'+ lastIndex1 +'">\
                        <td>\
                            <div class="range_distance_prev_continer">\
                                <span id="range_distance_prev">0</span>\
                                <input type="number" name="distanceRanges['+ lastIndex1 +'][distance]" id="range_distance" placeholder="" value="0" required class="form-control touchspin range_distance" step="0.1" data-bts-decimals="1" min="0">\
                            </div>\
                        </td>\
                        <td>\
                          <input type="number" name="distanceRanges['+ lastIndex1 +'][value]" id="range_value" placeholder="" value="0" required class="form-control touchspin range_value" step="0.01" data-bts-decimals="2" min="0">\
                        </td>\
                        <td>\
                          <select name="distanceRanges['+ lastIndex1 +'][factor_type]" id="range_factor_type" data-placeholder="" required class="form-control range_factor_type">\
                            <option value="0">Override</option>\
                            <option value="1">Add</option>\
                            <option value="2" selected>Multiply</option>\
                          </select>\
                        </td>\
                        '+ td +'\
                        <td>';
              if(ETO.hasPermission('admin.settings.mileage_time.edit')) {
                  html += '<button type="button" onclick="$(this).closest(\'tr\').remove(); var prev = 0; $(\'#distanceRangesList #range_distance\').each(function(key, value) { $(this).closest(\'tr\').find(\'#range_distance_prev\').html(prev); prev = $(this).val(); }); return false;" class="btn btn-default btn-sm btnDelete" title="Delete">\
                            <i class="fa fa-trash"></i>\
                          </button>'
              }
                html += '</td>\
                      </tr>';

                if( type == 'new' ) {
                    $('#distanceRangesList table tbody').prepend(html);
                }
                else {
                    $('#distanceRangesList table tbody').append(html);
                }

              $('#distanceRangesList tbody .distanceRangesRow'+ lastIndex1 +' #range_distance').on('change', function(){
                  var val = $(this).val();
                  var cls = '';

                  $('#distanceRangesList #range_distance').each(function(key, value) {
                      if( parseFloat($(this).val()) < parseFloat(val) ) {
                          cls = $(this).closest('tr').attr('class');
                      }
                  });

                  if( cls ) {
                      $(this).closest('tr').insertAfter('#distanceRangesList tbody tr.'+ cls);
                  }
                  else {
                      $(this).closest('tr').insertBefore('#distanceRangesList tbody tr:first-child');
                  }

                  var prev = 0;
                  $('#distanceRangesList #range_distance').each(function(key, value) {
                      $(this).closest('tr').find('#range_distance_prev').html(prev);
                      prev = $(this).val();
                  });
              });

              // Select
            //   $('#distanceRangesList .select2').select2();

              // Spinner
            //   $('#distanceRangesList input[type="text"].touchspin').TouchSpin({
              // max: null,
            //     booster: true,
            //     boostat: 5,
            //     mousewheel: true,
            //     verticalbuttons: true,
            //     verticalupclass: 'fa fa-plus',
            //     verticaldownclass: 'fa fa-minus'
            //   });

              // Delete button
              $('#distanceRangesList').find('button.btnDelete').hover(
                function() {
                  $(this).removeClass('btn-default').addClass('btn-danger');
                },
                function() {
                  $(this).removeClass('btn-danger').addClass('btn-default');
                }
              );

              var index = lastIndex1;
              lastIndex1++;
              return index;
            }

            $('#distanceRangesList .btnNewDistanceRange').on('click', function(e) {
              newDistanceRange('new');
              e.preventDefault();
            });
          }

          // Holiday table
          html = '<div class="table-responsive">\
                    <table class="table table-condensed table-hover" cellspacing="0" width="100%">\
                    <thead>\
                      <tr>\
                        <th>Message</th>\
                        <th>Action</th>\
                        <th>Factor value</th>\
                        <th>From</th>\
                        <th>To</th>\
                        <th></th>\
                      </tr>\
                    </thead>\
                    </table>\
                  </div>';

          if(ETO.hasPermission('admin.settings.holiday_surcharge.edit')) {
              html += '<button type="button" class="btn btn-success btn-xs btnNewCharge" title="New"> <i class="fa fa-plus"></i> <span>New</span></button>';
          }

          $('#chargesList').html(html);

          var lastIndex2 = 0;

          function newCharge() {
            var html = '';

            html = '<tr class="chargesRow'+ lastIndex2 +'">\
                      <td>\
                        <input type="hidden" name="charges['+ lastIndex2 +'][id]" id="charges_id" value="0" required class="form-control">\
                        <textarea name="charges['+ lastIndex2 +'][note]" id="charges_note" placeholder="eg. Christmas... (optional)" class="form-control" style="height:34px;"></textarea>\
                      </td>\
                      <td>\
                        <select name="charges['+ lastIndex2 +'][factor_type]" id="charges_factor_type" required class="form-control" style="width:100px;">\
                            <option value="0" selected>Multiply</option>\
                            <option value="1">Add</option>\
                        </select>\
                      </td>\
                      <td>\
                        <input type="number" name="charges['+ lastIndex2 +'][value]" id="charges_value" placeholder="" value="0" required class="form-control" step="0.01" data-bts-decimals="2" min="0" style="width:80px;">\
                      </td>\
                      <td>\
                        <input type="text" name="charges['+ lastIndex2 +'][start_date]" id="charges_start_date" placeholder="From" required value="" class="form-control datepicker">\
                      </td>\
                      <td>\
                        <input type="text" name="charges['+ lastIndex2 +'][end_date]" id="charges_end_date" placeholder="To" required value="" class="form-control datepicker">\
                      </td>\
                      <td>\
                      </td>\
                    </tr>';

              $('#chargesList table').append(html);
              if(ETO.hasPermission('admin.settings.holiday_surcharge.edit')) {
                  $('#chargesList table td:last').append('<button type="button" onclick="$(this).closest(\'tr\').remove(); return false;" class="btn btn-default btn-sm btnDelete" title="Delete">\
                          <i class="fa fa-trash"></i>\
                        </button>');
              }

            // Spinner
            // $('#chargesList input[type="text"].touchspin').TouchSpin({
              // max: null,
            //   booster: true,
            //   boostat: 5,
            //   mousewheel: true,
            //   verticalbuttons: true,
            //   verticalupclass: 'fa fa-plus',
            //   verticaldownclass: 'fa fa-minus'
            // });

            // Date picker (date and time)
            $('#chargesList input[type="text"].datepicker').focus(function(){

                $(this).daterangepicker({
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

            });

            // Delete button
            $('#chargesList').find('button.btnDelete').hover(
              function() {
                $(this).removeClass('btn-default').addClass('btn-danger');
              },
              function() {
                $(this).removeClass('btn-danger').addClass('btn-default');
              }
            );

            var index = lastIndex2;
            lastIndex2++;
            return index;
          }

          $('#chargesList .btnNewCharge').on('click', function(e) {
            newCharge();
            e.preventDefault();
          });

          // Other settings
          if( response.configList ) {
            $.each(response.configList, function(key, value) {
              var field = $('#config form #'+ key);

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
              else if ($.inArray(key, [
                  'check_availability_status',
                  'enable_autodispatch',
                  'only_auto_dispatch_status',
                  'customer_show_only_lead_passenger',
                  'booking_price_status',
                  'booking_price_status_off'
              ]) >= 0) {
                  $("input[name='"+ key +"'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'driver_income_child_seats' ) {
                  $("input[name='driver_income_child_seats'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'driver_income_additional_items' ) {
                  $("input[name='driver_income_additional_items'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'driver_income_parking_charges' ) {
                  $("input[name='driver_income_parking_charges'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'driver_income_payment_charges' ) {
                  $("input[name='driver_income_payment_charges'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'driver_income_meet_and_greet' ) {
                  $("input[name='driver_income_meet_and_greet'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'driver_income_discounts' ) {
                  $("input[name='driver_income_discounts'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'booking_discount_child_seats' ) {
                  $("input[name='booking_discount_child_seats'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'booking_discount_additional_items' ) {
                  $("input[name='booking_discount_additional_items'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'booking_discount_parking_charges' ) {
                  $("input[name='booking_discount_parking_charges'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'booking_discount_payment_charges' ) {
                  $("input[name='booking_discount_payment_charges'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'booking_discount_meet_and_greet' ) {
                  $("input[name='booking_discount_meet_and_greet'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'booking_request_enable' ) {
                  $("input[name='booking_request_enable'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'booking_pricing_mode' ) {
                  $("input[name='booking_pricing_mode'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'feedback_type' ) {
                  $("input[name='feedback_type'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'cron_update_auto' ) {
                  $("input[name='cron_update_auto'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'terms_type' ) {
                  $("input[name='terms_type'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'driver_allow_cancel' ) {
                  $("input[name='driver_allow_cancel'][value='"+ value +"']").attr('checked', true);
              }
              else if( key == 'quote_distance_range' ) {
                if( value ) {
                    var prev = 0;

                  $.each(value, function(key2, value2) {
                    var index = newDistanceRange();
                    var row = $('#distanceRangesList tr.distanceRangesRow'+ index);

                    $.each(value2.vehicle, function(key3, value3) {
                      if( value3 ) {
                        row.find('#range_vehicle_'+ value3.id).val(value3.value);
                      }
                    });

                    row.find('#range_distance_prev').html(prev);
                    prev = value2.distance;

                    row.find('#range_distance').val(value2.distance);
                    row.find('#range_value').val(value2.value);
                    row.find('#range_factor_type').val(value2.factor_type).trigger('change');
                  });
                }
              }
              else if( key == 'bases' ) {
                if( value ) {
                  $.each(value, function(key2, value2) {
                    var index = newBasesList();
                    var row = $('#basesList tr.basesListRow'+ index);
                    row.find('#bases_id').val(value2.id);
                    row.find('#bases_address').val(value2.address);
                    row.find('#bases_radius').val(value2.radius);
                    row.find('#bases_status').val(value2.status);
                  });
                }
              }
              else if( key == 'booking_base_action' ) {
                if( value ) {
                    field.val(value).change();
                }
              }
              else if( key == 'booking_vehicle_min_price' ) {
                if( value ) {
                  $.each(value, function(key2, value2) {
                    var index = newVehicleMinPriceList(value2.id);
                    var row = $('#vehicleMinPriceList tr.vehicleMinPriceListRow'+ index);
                    row.find('#vehicle_min_price_id').val(value2.id);
                    row.find('#vehicle_min_price_value').val(value2.value);
                  });
                }

                updateVehicleMinPriceOptions();
              }
              else if( key == 'booking_items' ) {
                if( value ) {
                  $.each(value, function(key2, value2) {
                    var index = newItemsList();
                    var row = $('#itemsList tr.itemsListRow'+ index);
                    row.find('#item_name').val(value2.name);
                    row.find('#item_value').val(value2.value);
                    row.find('#item_type').val(value2.type ? value2.type : 'amount').change();
                    row.find('#item_amount').val(value2.amount);
                    row.find('#item_custom').val(value2.custom ? value2.custom : '');
                  });
                }
              }
              else if( key == 'booking_deposit' ) {
                if( value ) {
                  $.each(value, function(key2, value2) {
                      createDepositRow(value2);
                  });
                }
              }
              else if( key == 'booking_night_surcharge' ) {
                if( value ) {
                  $.each(value, function(key2, value2) {
                      createNightSurchargeRow(value2);
                  });
                }
              }
              else if( key == 'override' ) {
                if( value ) {
                  $.each(value, function(key2, value2) {
                    var index = newCharge();
                    var row = $('#chargesList tr.chargesRow'+ index);

                    row.find('#charges_id').val(value2.id);
                    row.find('#charges_note').val(value2.note);
                    row.find('#charges_factor_type').val(value2.factor_type);
                    row.find('#charges_value').val(value2.value);
                    row.find('#charges_start_date').val(value2.start_date);
                    row.find('#charges_end_date').val(value2.end_date);
                  });
                }
              }
              else {
                field.val(value);
              }
            });

            // Driver journey
            $('#booking_base_action').change(function() {
                if( $(this).val() == 'allow' ) {
                    $('.field-booking_base_calculate_type').show();
                }
                else {
                    $('.field-booking_base_calculate_type').hide();
                }
            }).change();

            // Fixed price
            $('#fixed_prices_deposit_enable').change(function() {
                if( $(this).is(':checked') == true ) {
                    $('.field-fixed_prices_deposit_type').show();
                }
                else {
                    $('.field-fixed_prices_deposit_type').hide();
                }
            }).change();

            // Mail
            $('#mail_driver').change(function() {
                // SMTP
                if( $(this).val() == 'smtp' ) {
                    $('#config-mail-smtp').show();
                }
                else {
                    $('#config-mail-smtp').hide();
                }

                // Sendmail
                if( $(this).val() == 'sendmail' ) {
                    $('#config-mail-sendmail').show();
                }
                else {
                    $('#config-mail-sendmail').hide();
                }
            }).change();

            // Caller ID Type
            $('#callerid_type').change(function() {
                // RingCentral
                if( $(this).val() == 'ringcentral' ) {
                    $('#config-callerid-ringcentral').show();
                }
                else {
                    $('#config-callerid-ringcentral').hide();
                }

                if( $(this).val() != '' ) {
                    $('#config-callerid-general').show();
                }
                else {
                    $('#config-callerid-general').hide();
                }
            }).change();


            // SMS Service Type
            $('#sms_service_type').change(function() {
                $('.config-sms_service_type-textlocal').hide();
                $('.config-sms_service_type-twilio').hide();
                $('.config-sms_service_type-smsgateway').hide();

                if( $(this).val() == 'textlocal' ) {
                    $('.config-sms_service_type-textlocal').show();
                }
                else if( $(this).val() == 'twilio' ) {
                    $('.config-sms_service_type-twilio').show();
                }
                else if( $(this).val() == 'smsgateway' ) {
                    $('.config-sms_service_type-smsgateway').show();
                }
            }).change();

            $("#url_locales").change();

            // T&C
            $('#config #terms_enable').change(function() {
                if( $(this).is(':checked') == true ) {
                    $('#config .terms-container').show();
                }
                else {
                    $('#config .terms-container').hide();
                }
            }).change();

            $('#config input[name="feedback_type"]').change(function() {
                if( $('#config input[name="feedback_type"]:checked').val() == 2 ) {
                    $('#config .feedback-type0-container').hide();
                    $('#config .feedback-type1-container').hide();
                    var optionEnabled = false;
                }
                else if( $('#config input[name="feedback_type"]:checked').val() == 1 ) {
                    $('#config .feedback-type0-container').hide();
                    $('#config .feedback-type1-container').show();
                    var optionEnabled = false;
                }
                else {
                    $('#config .feedback-type0-container').show();
                    $('#config .feedback-type1-container').hide();
                    var optionEnabled = true;
                }

                $('#config #url_feedback').attr('required', optionEnabled);
                formValidation.formValidation('enableFieldValidators', 'url_feedback', optionEnabled);
            }).change();

            $('#config input[name="terms_type"]').change(function() {
                if( $('#config input[name="terms_type"]:checked').val() == 1 ) {
                    $('#config .terms-type0-container').hide();
                    $('#config .terms-type1-container').show();
                    var optionEnabled = false;
                }
                else {
                    $('#config .terms-type0-container').show();
                    $('#config .terms-type1-container').hide();
                    var optionEnabled = true;
                }

                $('#config #url_terms').attr('required', optionEnabled);
                formValidation.formValidation('enableFieldValidators', 'url_terms', optionEnabled);
            }).change();

            $('#config input[name="booking_price_status"]').change(function() {
                if($('#config input[name="booking_price_status"]:checked').val() == 1) {
                    $('#config .eto-container-booking_price_status_0').hide();
                    $('#config .eto-container-booking_price_status_1').show();
                }
                else {
                    $('#config .eto-container-booking_price_status_0').show();
                    $('#config .eto-container-booking_price_status_1').hide();
                }
            }).change();

            // Display labels
            $('#config form').find('input:not([type="submit"]), textarea, select').each(function() {
                updateFormPlaceholder(this);
            })
            .bind('change keyup', function(e) {
                updateFormPlaceholder(this);
            });

            // Tooltip
            $('#config [title]').tooltip({
              placement: 'auto',
              container: 'body',
              selector: '',
              html: true,
              trigger: 'hover',
              delay: {
                'show': 500,
                'hide': 100
              }
            });

            $('[data-toggle="popover"]').popover({
                placement: 'auto right',
                container: 'body',
                trigger: 'click focus hover',
                html: true
            });

            // Color picker
            $('.colorpicker').each( function(){
                $(this).minicolors({
                    animationSpeed: 50,
                    animationEasing: 'swing',
                    change: null,
                    changeDelay: 0,
                    control: 'hue',
                    defaultValue: $(this).attr('value') || '',
                    format: 'hex',
                    hide: null,
                    hideSpeed: 100,
                    inline: false,
                    keywords: '',
                    letterCase: 'lowercase',
                    opacity: false,
                    position: 'bottom left',
                    show: null,
                    showSpeed: 100,
                    theme: 'bootstrap',
                    swatches: [
                        '#000000',
                        '#ffffff',
                        '#FF0000',
                        '#777777',
                        '#337ab7',
                        '#5cb85c',
                        '#5bc0de',
                        '#f0ad4e',
                        '#d9534f'
                    ]
                });
            });

            $('.admin-settings-styles-box input.form-control').on('change', function() {
                var el = $(this);
                var box = el.closest('.admin-settings-styles-box');
                var default_color = el.attr('data-default_color');
                var current_color = el.val();

                if (default_color != current_color) {
                    box.find('.admin-settings-styles-remove').remove();
                    el.after('<span class="admin-settings-styles-remove"><i class="fa fa-trash"></i></span>');
                }
            }).change();

            $('body').on('click', '.admin-settings-styles-remove', function() {
                var input = $(this).closest('.admin-settings-styles-box').find('input.form-control');
                input.minicolors('value', input.attr('data-default_color'));
                $(this).remove();
            });

          }

          $('#config form #locale_switcher_enabled').change(function() {
              if( $(this).is(':checked') ) {
                  $('#config form .locale-switcher-container').show();
              }
              else {
                  $('#config form .locale-switcher-container').hide();
              }
          }).change();

          $('#config form #language').change(function() {
              $('#config form input[name="locale_active[]"][value="'+ $(this).val() +'"]').attr('checked', true);
          });

          var container = $('.panel-collapse.in').closest('.panel');

          checkPermission(container);
        }
        else {
          $('#config #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> The data could not be loaded</span>');
        }
      },
      error: function(response) {
        $('#config #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
      },
      beforeSend: function() {
        $('#config #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> In progress');
        $('#config .modal-overlay').show();
      },
      complete: function() {
        $('#config .modal-overlay').hide();
      }
    });

    // Form validation and submission
    var isReady = 1;

    var formValidation = $('#config form').formValidation({
      framework: 'bootstrap',
      icon: {
        valid: 'glyphicon glyphicon-ok',
        invalid: 'glyphicon glyphicon-remove',
        validating: 'glyphicon glyphicon-refresh'
      },
      excluded: ':disabled'
      // excluded: ':readonly'
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

        // var formValues = $("#config form").serializeArray();

        // var values = {};
        // $.each(formValues, function() {
        //   if (values[this.name] || values[this.name] == '') {
        //     if (!values[this.name].push) {
        //       values[this.name] = [values[this.name]];
        //     }
        //     values[this.name].push(this.value || '');
        //   } else {
        //     values[this.name] = this.value || '';
        //   }
        // });

        var values = $('#config form').serializeObject();

        values.distanceRanges = JSON.stringify(values.distanceRanges);
        values.charges = JSON.stringify(values.charges);
        values.basesList = JSON.stringify(values.basesList);
        values.vehicleMinPriceList = JSON.stringify(values.vehicleMinPriceList);
        values.itemsList = JSON.stringify(values.itemsList);
        values.depositList = JSON.stringify(values.depositList);
        values.nightSurchargeList = JSON.stringify(values.nightSurchargeList);

        var params = {
          task: 'config',
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
              $('#config #statusMsg').html('<span class="text-green"><i class="fa fa-check-circle"></i> Saved</span>');
            }
            else {
              $('#config #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> The data could not be updated</span>');
            }
          },
          error: function(response) {
            $('#config #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
          },
          beforeSend: function() {
            $('#config #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> In progress');
            isReady = 0;
          },
          complete: function() {
            isReady = 1;
          }
        });
      }
    });

  });
  </script>
@endsection
