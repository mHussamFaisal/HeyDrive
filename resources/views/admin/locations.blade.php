@extends('admin.index')

@section('title', 'Locations')


@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">
@endsection


@section('subcontent')
  <div class="pageContainer" id="locations">
    <div class="pageTitle">

      <a href="#" class="btn btn-default btn-sm pull-right btnFilters" title="Search">
        <i class="fa fa-search"></i>
      </a>
      @permission('admin.locations.create')
      <a href="#" onclick="updateRecord(); return false;" class="btn btn-success btn-sm pull-right btnAdd">
        <i class="fa fa-plus"></i> <span>Add new</span>
      </a>
      @endpermission
      <h3 class="clearfix">
        <span style="float:left;">Locations</span>
        <a style="float:left; margin-top:3px; margin-left:5px;" href="#" class="help-button" data-toggle="popover" data-title="Locations" data-content="Click <a href='{{ config('app.docs_url') }}/uncategorised/locations/' target='_blank'>here</a> to find out how to correctly set locations.">
          <i class="ion-ios-information-outline"></i>
        </a>
      </h3>

    </div>
    <div class="pageFilters pageFiltersHide">

      <form method="post" class="form-inline">
        <a href="#" class="pull-right btnClose" title="Close" onclick="$('.pageFilters').toggle(); return false;">
          <i class="fa fa-times"></i>
        </a>
        <div class="form-group field-category">
          <label for="filter-category">Category</label>
          <select name="filter-category[]" id="filter-category" multiple="multiple" size="1" class="form-control select2" data-placeholder="Category" data-allow-clear="true"></select>
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

    title = 'Delete location';
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
          task: 'location',
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

    var popContent = "Click <a href='{{ config('app.docs_url') }}/uncategorised/locations/' target='_blank'>here</a> to find out how to correctly set locations.";
    title += '<a style="margin-left:5px;" href="#" class="help-button" data-toggle="popover" data-title="How to add Locations?" data-content="'+ popContent +'"><i class="ion-ios-information-outline"></i></a>';

    html = '<form method="post">\
            <input type="hidden" name="id" id="id" value="0">\
            <input type="hidden" name="site_id" id="site_id" value="0">\
            <div class="form-group field-category_id">\
              <label for="category_id">Category</label>\
              <select name="category_id" id="category_id" data-placeholder="Category" required class="form-control select2"></select>\
            </div>\
            <div class="form-group field-name">\
              <label for="name">Display name</label>\
              <input type="text" name="name" id="name" placeholder="Display name" required class="form-control">\
              <small style="color:#bbb; font-style:italic;">e.g. London City Airport</small>\
            </div>\
            <div class="form-group field-full_address">\
              <label for="full_address">Full address</label>\
              <input type="text" name="full_address" id="full_address" placeholder="Full address" required class="form-control">\
              <small style="color:#bbb; font-style:italic;">e.g. London City Airport, E16 2PX, UK</small>\
            </div>\
            <div class="form-group field-address">\
              <label for="address">Postcode</label>\
              <input type="text" name="address" id="address" placeholder="Postcode" required class="form-control">\
              <small style="color:#bbb; font-style:italic;">e.g. E16 2PX</small>\
            </div>\
            <div class="form-group field-search_keywords">\
              <label for="search_keywords">Search keywords</label>\
              <textarea name="search_keywords" id="search_keywords" placeholder="Search keywords" class="form-control"></textarea>\
              <small style="color:#bbb; font-style:italic;">e.g. London City Airport, London Airport</small>\
            </div>\
            <div class="form-group field-lat">\
              <label for="lat">Latitude</label>\
              <input type="text" name="lat" id="lat" placeholder="Latitude" value="0" required class="form-control touchspin" data-bts-step="0.1" data-bts-decimals="10">\
            </div>\
            <div class="form-group field-lon">\
              <label for="lon">Longitude</label>\
              <input type="text" name="lon" id="lon" placeholder="Longitude" value="0" required class="form-control touchspin" data-bts-step="0.1" data-bts-decimals="10">\
            </div>\
            <div class="form-group field-ordering">\
              <label for="ordering">Ordering</label>\
              <input type="text" name="ordering" id="ordering" placeholder="Ordering" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0">\
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
        task: 'location',
        action: 'init'
      },
      success: function(response) {
        // Category
        html = '';
        if (response.categoryList) {
          $.each(response.categoryList, function(index, item) {
            html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
        }
        $('#dmodal #category_id').html(html);
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
          task: 'location',
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
          task: 'location',
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
      ETO.init({ config: [], lang: ['user'] }, 'locatins');
    }

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
        task: 'location',
        action: 'init'
      },
      success: function(response) {
        // console.log(response);
        html = '';

        // Category
        html = '';
        if (response.categoryList) {
          $.each(response.categoryList, function(index, item) {
            html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
        }
        $('#filter-category').html(html);
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
          task: 'location',
          action: 'list'
        },
        dataSrc: 'list'
      },
      columns: [
          @if (auth()->user()->hasPermission(['admin.locations.edit', 'admin.locations.destroy'], true))
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
        title: 'Category',
        data: 'category_id',
        width: '120px'
      }, {
        title: 'Display name',
        data: 'name',
        width: '300px'
      }, {
        title: 'Postcode',
        data: 'address',
        width: '120px'
      }, {
        title: 'Full address',
        data: 'full_address',
        width: '200px'
      }, {
        title: 'Search keywords',
        data: 'search_keywords',
        width: '200px'
      }, {
        title: 'Lat',
        data: 'lat',
        width: '100px',
        visible: false
      }, {
        title: 'Lon',
        data: 'lon',
        width: '100px',
        visible: false
      }, {
        title: 'Ordering',
        data: 'ordering',
        width: '100px'
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
          if(ETO.hasPermission('admin.locations.edit')) {
            h += '<button type="button" onclick="updateRecord(' + row.id + '); return false;" class="btn btn-default btn-sm btnEdit" title="Edit"><i class="fa fa-pencil-square-o"></i></button>';
          }
          if(ETO.hasPermission('admin.locations.destroy')) {
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
        [2, 'desc']
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

    if (!ETO.hasPermission(['admin.locations.edit', 'admin.locations.destroy'], true)) {
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
