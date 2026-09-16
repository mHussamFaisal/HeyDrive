@extends('admin.index')

@section('title', 'Customers')


@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">
@endsection


@section('subcontent')
  <div class="pageContainer" id="customers">
    <div class="pageTitle">

      <a href="#" class="btn btn-default btn-sm pull-right btnFilters" title="Search">
        <i class="fa fa-search"></i>
      </a>
      <a href="#" onclick="updateRecord(); return false;" class="btn btn-success btn-sm pull-right btnAdd">
        <i class="fa fa-plus"></i> <span>Add new</span>
      </a>
      <h3>Customers</h3>

    </div>
    <div class="pageFilters pageFiltersHide">

      <form method="post" class="form-inline">
        <a href="#" class="pull-right btnClose" title="Close" onclick="$('.pageFilters').toggle(); return false;">
          <i class="fa fa-times"></i>
        </a>
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
  <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
  <script src="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
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

    title = 'Delete customer';
    html = '<p style="margin-bottom:20px;">Are you sure you want to permanently delete this data?</p>\
            <button type="button" class="btn btn-danger btnConfirm" title="Delete"><i class="fa fa-trash"></i> Yes, delete</button>\
            <button type="button" class="btn btn-default btnCancel" title="Cancel">Cancel</button>\
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
          task: 'user',
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

  // Update
  function updateRecord(id) {
    var html = '';
    var title = '';
    var btnIcon = '';
    var btnTitle = '';
    var msgSuccess = '';

    if( id && id >= 0 ) {
      title = 'Edit';
      btnIcon = 'fa fa-pencil-square-o';
      btnTitle = 'Save';
      msgSuccess = 'Saved';
    }
    else {
      title = 'Add new';
      btnIcon = 'fa fa-plus';
      btnTitle = 'Add';
      msgSuccess = 'Added';
    }

    html = '<form method="post">\
            <input type="text" name="randomusernameremembered" id="randomusernameremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">\
            <input type="password" name="randompasswordremembered" id="randompasswordremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">\
            <input type="hidden" name="id" id="id" value="0">\
            <input type="hidden" name="site_id" id="site_id" value="0">\
            <div class="form-group field-title">\
              <label for="title">Title</label>\
              <input type="text" name="title" id="title" placeholder="Title" class="form-control">\
            </div>\
            <div class="form-group field-first_name">\
              <label for="first_name">First name</label>\
              <input type="text" name="first_name" id="first_name" placeholder="First name" required class="form-control">\
            </div>\
            <div class="form-group field-last_name">\
              <label for="last_name">Last name</label>\
              <input type="text" name="last_name" id="last_name" placeholder="Last name" class="form-control">\
            </div>\
            <div class="form-group field-email">\
              <label for="email">Email</label>\
              <input type="text" name="email" id="email" placeholder="Email" data-fv-emailaddress="true" class="form-control" autocomplete="no">\
            </div>\
            <div class="form-group field-password">\
              <label for="password">Password</label>\
              <input type="password" name="password" id="password" placeholder="Password" data-fv-identical="true" data-fv-identical-field="confirm_password" class="form-control" autocomplete="new-password">\
            </div>\
            <div class="form-group field-confirm_password">\
              <label for="confirm_password">Confirm Password</label>\
              <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" data-fv-identical="true" data-fv-identical-field="password" class="form-control" autocomplete="new-password">\
            </div>\
            <div class="form-group field-description">\
              <label for="description">Description</label>\
              <textarea name="description" id="description" placeholder="Description" class="form-control"></textarea>\
            </div>\
            <div class="form-group field-mobile_number">\
              <label for="mobile_number">Mobile number</label>\
              <input type="text" name="mobile_number" id="mobile_number" placeholder="Mobile number" class="form-control">\
            </div>\
            <div class="form-group field-telephone_number">\
              <label for="telephone_number">Telephone number</label>\
              <input type="text" name="telephone_number" id="telephone_number" placeholder="Telephone number" class="form-control">\
            </div>\
            <div class="form-group field-emergency_number">\
              <label for="emergency_number">Emergency number</label>\
              <input type="text" name="emergency_number" id="emergency_number" placeholder="Emergency number" class="form-control">\
            </div>\
            <div class="form-group field-address">\
              <label for="address">Address</label>\
              <input type="text" name="address" id="address" placeholder="Address" class="form-control">\
            </div>\
            <div class="form-group field-city">\
              <label for="city">City</label>\
              <input type="text" name="city" id="city" placeholder="City" class="form-control">\
            </div>\
            <div class="form-group field-postcode">\
              <label for="postcode">Postcode</label>\
              <input type="text" name="postcode" id="postcode" placeholder="Postcode" class="form-control">\
            </div>\
            <div class="form-group field-state">\
              <label for="state">County</label>\
              <input type="text" name="state" id="state" placeholder="County" class="form-control">\
            </div>\
            <div class="form-group field-country">\
              <label for="country">Country</label>\
              <input type="text" name="country" id="country" placeholder="Country" class="form-control">\
            </div>\
            <div class="form-group field-is_company">\
              <label for="is_company" class="checkbox-inline">\
                <input type="checkbox" name="is_company" id="is_company" value="1">Company Account\
              </label>\
            </div>\
            <div class="company-container">\
                <div class="form-group field-company_name">\
                  <label for="company_name">Company name</label>\
                  <input type="text" name="company_name" id="company_name" placeholder="Company name" class="form-control">\
                </div>\
                <div class="form-group field-company_number">\
                  <label for="company_number">Company number</label>\
                  <input type="text" name="company_number" id="company_number" placeholder="Company number" class="form-control">\
                </div>\
                <div class="form-group field-company_tax_number">\
                  <label for="company_tax_number">Company VAT number</label>\
                  <input type="text" name="company_tax_number" id="company_tax_number" placeholder="Company VAT number" class="form-control">\
                </div>\
                <div class="form-group field-is_account_payment clearfix" style="max-width:400px;">\
                  <label for="is_account_payment" class="checkbox-inline" style="float:left;">\
                    <input type="checkbox" name="is_account_payment" id="is_account_payment" value="1" checked>Enable Account payment method\
                  </label>\
                  <i class="ion-ios-information-outline" style="display:inline-block; margin-top:-2px; margin-left:6px; font-size:18px;" data-toggle="popover" data-title="" data-content="This setting allows admin to individually decide weather Company Account is allowed to use Account payment method. Account payment method allows to Reserved a booking without upfront payment.<br><br>For <b>Enable Account payment method</b> to work, the <b>Account</b> payment method has to be activated in Settings -> Payment Methods -> Account"></i>\
                </div>\
                <div class="form-group">\
                    <span>Departments</span>\
                    <div class="eto-departments-list"></div>\
                    <button type="button" class="btn btn-sm btn-default eto-add-department">Add department</button>\
                 </div>\
            </div>\
            <div class="form-group field-avatar placeholder-visible">\
                <span for="avatar">Upload Avatar</span>\
                <input type="file" name="avatar" id="avatar">\
            </div>\
            <div class="form-group field-ip">\
              <label for="ip">IP</label>\
              <input type="text" name="ip" id="ip" placeholder="IP" class="form-control">\
            </div>\
            <div class="form-group field-token_password">\
              <label for="token_password">Token password</label>\
              <input type="text" name="token_password" id="token_password" placeholder="Token password" class="form-control">\
            </div>\
            <div class="form-group field-token_activation">\
              <label for="token_activation">Token activation</label>\
              <input type="text" name="token_activation" id="token_activation" placeholder="Token activation" class="form-control">\
            </div>\
            <div class="form-group field-last_visit_date">\
              <label for="last_visit_date">Last visit</label>\
              <input type="text" name="last_visit_date" id="last_visit_date" placeholder="Last visit" readonly class="form-control">\
            </div>\
            <div class="form-group field-created_date">\
              <label for="created_date">Created date</label>\
              <input type="text" name="created_date" id="created_date" placeholder="Created date" readonly class="form-control">\
            </div>\
            <div class="form-group field-activated">\
              <label for="activated" class="checkbox-inline">\
                <input type="checkbox" name="activated" id="activated" value="1" checked>Verified\
              </label>\
            </div>\
            <div class="form-group field-published">\
              <label for="published" class="checkbox-inline">\
                <input type="checkbox" name="published" id="published" value="1" checked>Active\
              </label>\
            </div>\
            <div class="form-buttons">\
              <button type="submit" class="btn btn-success btnSave" title="'+ btnTitle +'"><i class="'+ btnIcon +'"></i> <span>'+ btnTitle +'</span></button>\
              <button type="button" class="btn btn-default btnCancel" title="Cancel"> <span>Cancel</span></button>\
              <span id="statusMsg"></span>\
            </div>\
          </form>';

    $('#dmodal .modal-title').html(title);
    $('#dmodal .modal-body').html(html);
    $('#dmodal').modal({
      show: true
    });

    // Profile type
    function profileType() {
        if( $('#dmodal input[name="is_company"]:checked').val() == 1 ) {
            $('#dmodal .company-container').show();
        } else {
            $('#dmodal .company-container').hide();
        }
    }

    profileType();

    $('#dmodal input[name="is_company"]').change(function() {
        profileType();
    });

    // Popover
    $('[data-toggle="popover"]').popover({
        placement: 'auto right',
        container: 'body',
        trigger: 'focus hover',
        html: true
    });

    // Load filters
    // $.ajax({
    //   headers : {
    //     'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
    //   },
    //   url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
    //   type: 'POST',
    //   dataType: 'json',
    //   cache: false,
    //   async: false,
    //   data: {
    //     task: 'user',
    //     action: 'init'
    //   },
    //   success: function(response) {
    //
    //   },
    //   error: function(response) {
    //     $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
    //   }
    // });

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
          task: 'user',
          action: 'read',
          id: id
        },
        success: function(response) {
          if( response.success ) {
            $('#dmodal #statusMsg').html('');

            if( response.record ) {
              $.each(response.record, function(key, value) {
                // console.log(key, value);
                var field = $('#dmodal form #'+ key);
                if (key == 'avatar' || key == 'avatar_path') {
                    // Do nothing
                }
                // else if (key == 'avatar_path' && value != null) {
                //     field.closest('.form-group').prepend('<img src="'+ value +'" class="img-circle" alt="" style="max-width:100px; max-height:100px; margin-bottom:20px;"><input type="checkbox" name="avatar_delete" id="avatar_delete" value="1"> Delete avatar');
                // }
                else if(key == 'departments' && typeof value == 'object') {
                    $.each(value, function (k,v) {
                        var uuid = ETO.uuidHTML();
                        $('.eto-departments-list').append('<div class="form-group field-department">\
                              <input name="departments[]" id="department-'+uuid+'" placeholder="Department" class="form-control" value="'+v+'">\
                              <i class="fa fa fa-trash eto-delete-department"></i>\
                            </div>');
                    })
                }
                else if( field.hasClass('select2') ) {
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
                else {
                  field.val(value);
                }
              });

              if (response.record.avatar != null) {
                  $('#dmodal form #avatar').closest('.form-group').prepend('<img src="'+ response.record.avatar_path +'" class="img-circle" alt="" style="max-width:100px; max-height:100px; margin-right:10px; margin-bottom:20px;"><input type="checkbox" name="avatar_delete" id="avatar_delete" value="1"> Delete avatar');
              }

              // Placeholder
              $('#dmodal form').find('input:not([type="submit"]), textarea, select').each(function() {
                  updateFormPlaceholder(this);
              })
              .bind('change keyup', function(e) {
                  updateFormPlaceholder(this);
              });
            }

            profileType();
          }
          else {
              var msg = 'The data could not be loaded';
              if (response.message) {
                  msg = response.message;
              }
              $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ msg +'</span>');
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

    // Form validation and submission
    var isReady = 1;

    $('#dmodal form').formValidation({
      framework: 'bootstrap',
      icon: {
        valid: 'glyphicon glyphicon-ok',
        invalid: 'glyphicon glyphicon-remove',
        validating: 'glyphicon glyphicon-refresh'
      },
      excluded: ':disabled'
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
          task: 'user',
          action: 'update'
        };

        ETO.ajaxWithFileUpload($("#dmodal form"), EasyTaxiOffice.appPath +'/etov2?apiType=backend', params, {
            success: function(response) {
                if(response.success) {
                    $('#dmodal #statusMsg').html('<span class="text-green"><i class="fa fa-check-circle"></i> '+ msgSuccess +'</span>');
                    if( parseInt(values.id) <= 0 ) {
                        $('#dmodal form').trigger('reset');
                        $('#dmodal').modal('hide');
                    }

                    setTimeout(function() {
                        // updateRecord(values.id);
                        updateRecord(response.customer_id);
                    }, 500);

                    filterTable();
                }
                else {
                    var msg = 'The data could not be loaded';
                    if (response.message) {
                        msg = response.message;
                    }
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

    @if (!empty(request('id')))
        updateRecord('{{ request('id') }}');
    @endif

    // Select
    $('.pageFilters .select2').select2();

    // Toggle filters
    // $('.pageFilters').toggle();
    $('.pageTitle .btnFilters').on('click', function() {
      $('.pageFilters').toggle();
    });
      $('body').on('click', '.eto-add-department', function() {
          var uuid = ETO.uuidHTML()
          $(this).closest('#dmodal').find('.eto-departments-list').append('<div class="form-group field-department">\
              <input name="departments[]" id="department-'+uuid+'" placeholder="Department" class="form-control">\
              <i class="fa fa fa-trash eto-delete-department"></i>\
            </div>');
      })
      .on('click', '.eto-delete-department', function() {
          $(this).closest('.field-department').remove();
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
      // console.log($(this).val());
      if( $(this).val() ) {
        $(this).parent('.form-group').find('label').show();
      }
      else {
        $(this).parent('.form-group').find('label').hide();
      }
      // filterTable();
      e.preventDefault();
    });

    // Table
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
          task: 'user',
          action: 'list'
        },
        dataSrc: 'list'
      },
      columns: [
       @if (auth()->user()->hasPermission(['admin.users.customer.edit', 'admin.users.customer.destroy', 'admin.bookings.index'], true))
      {
        title: '',
        data: null,
        defaultContent: '',
        orderable: false,
        className: 'actionColumn'
      },
        @endif
        {
        title: 'ID',
        data: 'id',
        width: '50px',
        visible: false
      }, {
        title: 'Avatar',
        data: 'avatar',
        width: '200px',
        orderable: false
      }, {
        title: 'Name',
        data: 'name',
        width: '200px'
      }, {
        title: 'Description',
        data: 'description',
        width: '200px'
      }, {
        title: 'Email',
        data: 'email',
        width: '200px'
      }, {
        title: 'Mobile Number',
        data: 'mobile_number',
        width: '150px'
      }, {
        title: 'Telephone Number',
        data: 'telephone_number',
        width: '150px'
      }, {
        title: 'Emergency Number',
        data: 'emergency_number',
        width: '150px'
      }, {
        title: 'Profile Type',
        data: 'is_company',
        width: '90px'
      }, {
        title: 'Company Name',
        data: 'company_name',
        width: '200px'
      }, {
        title: 'Company Number',
        data: 'company_number',
        width: '200px'
      }, {
        title: 'Company VAT Number',
        data: 'company_tax_number',
        width: '200px'
      }, {
        title: 'Account payment',
        data: 'is_account_payment',
        width: '100px'
      }, {
        title: 'Address',
        data: 'address',
        width: '150px'
      }, {
        title: 'City',
        data: 'city',
        width: '100px'
      }, {
        title: 'Postcode',
        data: 'postcode',
        width: '100px'
      }, {
        title: 'County',
        data: 'state',
        width: '150px'
      }, {
        title: 'Country',
        data: 'country',
        width: '150px'
      }, {
        title: 'IP',
        data: 'ip',
        width: '150px'
      }, {
        title: 'Last Visit',
        data: 'last_visit_date',
        width: '150px'
      }, {
        title: 'Created',
        data: 'created_date',
        width: '150px'
      }, {
        title: 'Verified',
        data: 'activated',
        width: '90px'
      }, {
        title: 'Active',
        data: 'published',
        width: '80px'
      }],
      columnDefs: [{
        targets: 0,
        data: null,
        render: function(data, type, row) {
          var h = '';
          h += '<div class="btn-group" role="group" aria-label="..." style="width:100px;">';

          if (ETO.hasPermission('admin.users.customer.edit')) {
            h += '<button type="button" onclick="updateRecord(' + row.id + '); return false;" class="btn btn-default btn-sm btnEdit" title="Edit"><i class="fa fa-pencil-square-o"></i></button>';
          }
          if (ETO.hasPermission('admin.users.customer.destroy')) {
            h += '<button type="button" onclick="deleteRecord('+ row.id +'); return false;" class="btn btn-default btn-sm btnDelete" title="Delete"><i class="fa fa-trash"></i></button>';
          }
          if (ETO.hasPermission('admin.bookings.index')) {
            h += '<button type="button" onclick="window.location.href=\'{{ route('admin.bookings.index') }}?user=' + row.id + '\'; return false;" class="btn btn-default btn-sm btnView" title="Bookings"><i class="fa fa-calendar"></i></button>';
          }

          h += '</div>';
          return h;
        }
      }],
      paging: true,
      pagingType: 'full_numbers',
      dom: 'rt<"row"<"col-xs-12 col-md-5 dataTablesFooterLeft"li><"col-xs-12 col-md-7 dataTablesFooterRight"p>><"clear">',
      scrollX: true,
      searching: true,
      ordering: true,
      lengthChange: true,
      info: true,
      autoWidth: false,
      stateSave: true,
      stateDuration: 0,
      order: [
        [3, 'desc']
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
    }

    if (!ETO.hasPermission(['admin.users.customer.edit', 'admin.users.customer.destroy', 'admin.bookings.index'], true)) {
      delete tableOptionsn.columnDefs;
    }

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

      // View button
      $('#dtable').find('button.btnView').hover(
        function() {
          $(this).removeClass('btn-default').addClass('btn-info');
        },
        function() {
          $(this).removeClass('btn-info').addClass('btn-default');
        }
      );
    });

  });
  </script>
@endsection
