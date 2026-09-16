@extends('admin.index')

@section('title', 'Discounts')


@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">
@endsection


@section('subcontent')
  <div class="pageContainer" id="discounts">
    <div class="pageTitle">

      <a href="#" class="btn btn-default btn-sm pull-right btnFilters" title="Search">
        <i class="fa fa-search"></i>
      </a>
      @permission('admin.discounts.create')
      <a href="#" onclick="updateRecord(); return false;" class="btn btn-success btn-sm pull-right btnAdd">
        <i class="fa fa-plus"></i> <span>Add new</span>
      </a>
      @endpermission
      <h3>Discounts</h3>

    </div>
    <div class="pageFilters pageFiltersHide">

      <form method="post" class="form-inline">
        <a href="#" class="pull-right btnClose" title="Close" onclick="$('.pageFilters').toggle(); return false;">
          <i class="fa fa-times"></i>
        </a>
        <div class="form-group field-type">
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

    title = 'Are you sure?';
    html += '<button type="button" class="btn btn-danger btnConfirm" title="Delete"><i class="fa fa-trash"></i> Delete</button> ';
    html += '<button type="button" class="btn btn-default btnCancel" title="Cancel">Cancel</button>';

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
          task: 'discount',
          action: 'destroy',
          id: id
        },
        success: function(response) {
          if(response.success) {
            filterTable(true);
            $('#dmodal').modal('hide');
          }
          else {
            alert('The discount could not be deleted!');
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

  // Delete
  function deleteRecord(id) {
    var html = '';
    var title = '';

    title = 'Delete discount';
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
          task: 'discount',
          action: 'destroy',
          id: id
        },
        success: function(response) {
          if(response.success) {
            $('#dmodal #statusMsg').html('');
            $('#dmodal').modal('hide');
            filterTable();
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
            <input type="hidden" name="id" id="id" value="0">\
            <input type="hidden" name="site_id" id="site_id" value="0">\
            <div class="form-group field-code">\
              <label for="code">Coupon code</label>\
              <input type="text" name="code" id="code" placeholder="Coupon code" required class="form-control">\
            </div>\
            <div class="form-group field-type">\
              <label for="type">Type</label>\
              <select name="type" id="type" data-placeholder="Type" data-minimum-results-for-search="Infinity" required class="form-control select2"></select>\
            </div>\
            <div class="form-group field-value">\
              <label for="value">Value</label>\
              <input type="text" name="value" id="value" placeholder="Value" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0">\
            </div>\
            <div class="form-group field-allowed_times clearfix" style="max-width:300px;">\
              <label for="allowed_times">Allowed times</label>\
              <div style="float:left; max-width:220px;">\
                <input type="text" name="allowed_times" id="allowed_times" placeholder="Allowed times" value="1000" required class="form-control touchspin" data-bts-step="1" data-bts-min="0">\
              </div>\
              <i class="ion-ios-information-outline" style="float: left; margin-top:5px; margin-left:10px; font-size:18px;" class="help-button" data-toggle="popover" data-title="Allowed times" data-content="Maximum available amount of coupons."></i>\
            </div>\
            <div class="form-group field-used_times clearfix" style="max-width:300px;">\
              <label for="used_times">Already used</label>\
              <div style="float:left; max-width:220px;">\
                <input type="text" name="used_times" id="used_times" placeholder="Already used" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0">\
              </div>\
              <i class="ion-ios-information-outline" style="float: left; margin-top:5px; margin-left:10px; font-size:18px;" class="help-button" data-toggle="popover" data-title="Already used" data-content="Total amount of used coupons. This value should be set to \'0\' when a new coupon is being created."></i>\
            </div>\
            <div class="form-group field-minimum_bookings">\
              <label for="minimum_bookings">Minimum bookings</label>\
              <input type="text" name="minimum_bookings" id="minimum_bookings" placeholder="Minimum bookings" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0">\
            </div>\
            <p>Limit to specific date and time</p>\
            <div class="form-group field-start_date">\
              <label for="start_date">Valid from</label>\
              <input type="text" name="start_date" id="start_date" placeholder="Valid from" class="form-control datepicker">\
            </div>\
            <div class="form-group field-end_date">\
              <label for="end_date">Valid to</label>\
              <input type="text" name="end_date" id="end_date" placeholder="Valid to" class="form-control datepicker">\
            </div>\
            <div class="form-group field-description">\
              <label for="description">Comments</label>\
              <textarea name="description" id="description" placeholder="Comments" class="form-control"></textarea>\
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

    $('[data-toggle="popover"]').popover({
        placement: 'auto right',
        container: 'body',
        trigger: 'focus hover',
        html: true
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
        task: 'discount',
        action: 'init'
      },
      success: function(response) {
        // Type
        html = '';
        if (response.typeList) {
          $.each(response.typeList, function(index, item) {
            html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
        }
        $('#type').html(html);
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
          task: 'discount',
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
                else {
                  field.val(value);
                }
              });

              // Placeholder
              $('#dmodal form').find('input:not([type="submit"]), textarea, select').each(function() {
                  updateFormPlaceholder(this);
              })
              .bind('change keyup', function(e) {
                  updateFormPlaceholder(this);
              });
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
          task: 'discount',
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
              filterTable();
            }
            else {
              $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> The data could not be updated</span>');
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
    if (ETO.model === false) {
      ETO.init({ config: [], lang: ['user'] }, 'settings');
    }
    // Select
    $('.pageFilters .select2').select2();

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
        task: 'discount',
        action: 'init'
      },
      success: function(response) {
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
          task: 'discount',
          action: 'list'
        },
        dataSrc: 'list'
      },
      columns: [
          @if (auth()->user()->hasPermission(['admin.discounts.edit', 'admin.discounts.destroy'], true))
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
        title: 'Code',
        data: 'code',
        width: '200px'
      }, {
        title: 'Type',
        data: 'type',
        width: '100px'
      }, {
        title: 'Value',
        data: 'value',
        width: '80px'
      }, {
        title: 'Allowed times',
        data: 'allowed_times',
        width: '120px'
      }, {
        title: 'Already used',
        data: 'used_times',
        width: '120px'
      }, {
        title: 'Valid from',
        data: 'start_date',
        width: '150px'
      }, {
        title: 'Valid to',
        data: 'end_date',
        width: '150px'
      }, {
        title: 'Minimum bookings',
        data: 'minimum_bookings',
        width: '150px',
        visible: false
      }, {
        title: 'Comments',
        data: 'description',
        width: '200px'
      }, {
        title: 'Created on',
        data: 'created_date',
        width: '150px'
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
          h += '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';
          if(ETO.hasPermission('admin.discounts.edit')) {
            h += '<button type="button" onclick="updateRecord(' + row.id + '); return false;" class="btn btn-default btn-sm btnEdit" title="Edit"><i class="fa fa-pencil-square-o"></i></button>';
          }
          if(ETO.hasPermission('admin.discounts.destroy')) {
            h += '<button type="button" onclick="deleteRecord(' + row.id + '); return false;" class="btn btn-default btn-sm btnDelete" title="Delete"><i class="fa fa-trash"></i></button>';
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
        [1, 'desc']
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

    if (!ETO.hasPermission(['admin.discounts.edit', 'admin.discounts.destroy'], true)) {
      delete tableOptionsn.columnDefs;
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
