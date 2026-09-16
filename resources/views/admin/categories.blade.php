@extends('admin.index')

@section('title', 'Categories')


@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','fontawesome-iconpicker/fontawesome-iconpicker.min.css') }}">
@endsection


@section('subcontent')
  <div class="pageContainer" id="categories">
    <div class="pageTitle">

      <a href="#" class="btn btn-default btn-sm pull-right btnFilters" title="Search">
        <i class="fa fa-search"></i>
      </a>
      @permission('admin.categories.create')
      <a href="#" onclick="updateRecord(); return false;" class="btn btn-success btn-sm pull-right btnAdd">
        <i class="fa fa-plus"></i> <span>Add new</span>
      </a>
      @endpermission
      <h3>Categories <i class="ion-ios-information-outline" style="font-size:24px; margin-left:5px;" title="You can only remove custom categories.<br>The system categories can only be deactivated."></i></h3>

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
  <script src="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.min.js') }}"></script>
  <script src="{{ asset_url('plugins','fontawesome-iconpicker/fontawesome-iconpicker.min.js') }}"></script>

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

    title = 'Delete category';
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
          task: 'category',
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

  function applyFieldOptions() {
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

      // Color picker
      $('#dmodal form .colorpicker').each( function(){
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

      // Icon picker
      $('#dmodal form .iconpicker').iconpicker({
        icons: $.merge(['glyphicon-asterisk','glyphicon-plus','glyphicon-euro','glyphicon-eur','glyphicon-minus','glyphicon-cloud','glyphicon-envelope','glyphicon-pencil','glyphicon-glass','glyphicon-music','glyphicon-search','glyphicon-heart','glyphicon-star','glyphicon-star-empty','glyphicon-user','glyphicon-film','glyphicon-th-large','glyphicon-th','glyphicon-th-list','glyphicon-ok','glyphicon-remove','glyphicon-zoom-in','glyphicon-zoom-out','glyphicon-off','glyphicon-signal','glyphicon-cog','glyphicon-trash','glyphicon-home','glyphicon-file','glyphicon-time','glyphicon-road','glyphicon-download-alt','glyphicon-download','glyphicon-upload','glyphicon-inbox','glyphicon-play-circle','glyphicon-repeat','glyphicon-refresh','glyphicon-list-alt','glyphicon-lock','glyphicon-flag','glyphicon-headphones','glyphicon-volume-off','glyphicon-volume-down','glyphicon-volume-up','glyphicon-qrcode','glyphicon-barcode','glyphicon-tag','glyphicon-tags','glyphicon-book','glyphicon-bookmark','glyphicon-print','glyphicon-camera','glyphicon-font','glyphicon-bold','glyphicon-italic','glyphicon-text-height','glyphicon-text-width','glyphicon-align-left','glyphicon-align-center','glyphicon-align-right','glyphicon-align-justify','glyphicon-list','glyphicon-indent-left','glyphicon-indent-right','glyphicon-facetime-video','glyphicon-picture','glyphicon-map-marker','glyphicon-adjust','glyphicon-tint','glyphicon-edit','glyphicon-share','glyphicon-check','glyphicon-move','glyphicon-step-backward','glyphicon-fast-backward','glyphicon-backward','glyphicon-play','glyphicon-pause','glyphicon-stop','glyphicon-forward','glyphicon-fast-forward','glyphicon-step-forward','glyphicon-eject','glyphicon-chevron-left','glyphicon-chevron-right','glyphicon-plus-sign','glyphicon-minus-sign','glyphicon-remove-sign','glyphicon-ok-sign','glyphicon-question-sign','glyphicon-info-sign','glyphicon-screenshot','glyphicon-remove-circle','glyphicon-ok-circle','glyphicon-ban-circle','glyphicon-arrow-left','glyphicon-arrow-right','glyphicon-arrow-up','glyphicon-arrow-down','glyphicon-share-alt','glyphicon-resize-full','glyphicon-resize-small','glyphicon-exclamation-sign','glyphicon-gift','glyphicon-leaf','glyphicon-fire','glyphicon-eye-open','glyphicon-eye-close','glyphicon-warning-sign','glyphicon-plane','glyphicon-calendar','glyphicon-random','glyphicon-comment','glyphicon-magnet','glyphicon-chevron-up','glyphicon-chevron-down','glyphicon-retweet','glyphicon-shopping-cart','glyphicon-folder-close','glyphicon-folder-open','glyphicon-resize-vertical','glyphicon-resize-horizontal','glyphicon-hdd','glyphicon-bullhorn','glyphicon-bell','glyphicon-certificate','glyphicon-thumbs-up','glyphicon-thumbs-down','glyphicon-hand-right','glyphicon-hand-left','glyphicon-hand-up','glyphicon-hand-down','glyphicon-circle-arrow-right','glyphicon-circle-arrow-left','glyphicon-circle-arrow-up','glyphicon-circle-arrow-down','glyphicon-globe','glyphicon-wrench','glyphicon-tasks','glyphicon-filter','glyphicon-briefcase','glyphicon-fullscreen','glyphicon-dashboard','glyphicon-paperclip','glyphicon-heart-empty','glyphicon-link','glyphicon-phone','glyphicon-pushpin','glyphicon-usd','glyphicon-gbp','glyphicon-sort','glyphicon-sort-by-alphabet','glyphicon-sort-by-alphabet-alt','glyphicon-sort-by-order','glyphicon-sort-by-order-alt','glyphicon-sort-by-attributes','glyphicon-sort-by-attributes-alt','glyphicon-unchecked','glyphicon-expand','glyphicon-collapse-down','glyphicon-collapse-up','glyphicon-log-in','glyphicon-flash','glyphicon-log-out','glyphicon-new-window','glyphicon-record','glyphicon-save','glyphicon-open','glyphicon-saved','glyphicon-import','glyphicon-export','glyphicon-send','glyphicon-floppy-disk','glyphicon-floppy-saved','glyphicon-floppy-remove','glyphicon-floppy-save','glyphicon-floppy-open','glyphicon-credit-card','glyphicon-transfer','glyphicon-cutlery','glyphicon-header','glyphicon-compressed','glyphicon-earphone','glyphicon-phone-alt','glyphicon-tower','glyphicon-stats','glyphicon-sd-video','glyphicon-hd-video','glyphicon-subtitles','glyphicon-sound-stereo','glyphicon-sound-dolby','glyphicon-sound-5-1','glyphicon-sound-6-1','glyphicon-sound-7-1','glyphicon-copyright-mark','glyphicon-registration-mark','glyphicon-cloud-download','glyphicon-cloud-upload','glyphicon-tree-conifer','glyphicon-tree-deciduous','glyphicon-cd','glyphicon-save-file','glyphicon-open-file','glyphicon-level-up','glyphicon-copy','glyphicon-paste','glyphicon-alert','glyphicon-equalizer','glyphicon-king','glyphicon-queen','glyphicon-pawn','glyphicon-bishop','glyphicon-knight','glyphicon-baby-formula','glyphicon-tent','glyphicon-blackboard','glyphicon-bed','glyphicon-apple','glyphicon-erase','glyphicon-hourglass','glyphicon-lamp','glyphicon-duplicate','glyphicon-piggy-bank','glyphicon-scissors','glyphicon-bitcoin','glyphicon-btc','glyphicon-xbt','glyphicon-yen','glyphicon-jpy','glyphicon-ruble','glyphicon-rub','glyphicon-scale','glyphicon-ice-lolly','glyphicon-ice-lolly-tasted','glyphicon-education','glyphicon-option-horizontal','glyphicon-option-vertical','glyphicon-menu-hamburger','glyphicon-modal-window','glyphicon-oil','glyphicon-grain','glyphicon-sunglasses','glyphicon-text-size','glyphicon-text-color','glyphicon-text-background','glyphicon-object-align-top','glyphicon-object-align-bottom','glyphicon-object-align-horizontal','glyphicon-object-align-left','glyphicon-object-align-vertical','glyphicon-object-align-right','glyphicon-triangle-right','glyphicon-triangle-left','glyphicon-triangle-bottom','glyphicon-triangle-top','glyphicon-console','glyphicon-superscript','glyphicon-subscript','glyphicon-menu-left','glyphicon-menu-right','glyphicon-menu-down','glyphicon-menu-up'],
          $.iconpicker.defaultOptions.icons),
        // defaultValue: true,
        fullClassFormatter: function(val) {
          if( val.match(/^fa-/) ){
              return 'fa '+ val;
          }
          else{
              return 'glyphicon '+ val;
          }
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
    var modalCls = '';

    if( id && id >= 0 ) {
      title = 'Edit';
      btnIcon = 'fa fa-pencil-square-o';
      btnTitle = 'Save';
      msgSuccess = 'Saved';
      modalCls = 'modal-edit';
    }
    else {
      title = 'Add new';
      btnIcon = 'fa fa-plus';
      btnTitle = 'Add';
      msgSuccess = 'Added';
      modalCls = 'modal-add';
    }

    html = '<form method="post">\
            <input type="hidden" name="id" id="id" value="0">\
            <input type="hidden" name="site_id" id="site_id" value="0">\
            <div class="form-group field-type hide">\
              <label for="type">Type</label>\
              <select name="type" id="type" data-placeholder="Type" required class="form-control select2"></select>\
            </div>\
            <div class="form-group field-name">\
              <label for="name">Name</label>\
              <input type="text" name="name" id="name" placeholder="Name" required class="form-control">\
            </div>\
            <div class="form-group field-icon">\
              <label for="icon">Icon</label>\
              <div class="input-group iconpicker-container">\
                  <span class="input-group-addon">\
                      <i class="fa fa-map-marker"></i>\
                  </span>\
                  <input type="text" name="icon" id="icon" value="fa-map-marker" required class="form-control icp icp-auto iconpicker-element iconpicker-input iconpicker" data-placement="bottomLeft">\
              </div>\
            </div>\
            <div class="form-group field-color">\
              <label for="color">Colour</label>\
              <input type="text" name="color" id="color" value="#cccccc" placeholder="Colour" required class="form-control colorpicker">\
            </div>\
            <div class="form-group field-ordering">\
              <label for="ordering">Ordering</label>\
              <input type="text" name="ordering" id="ordering" placeholder="Ordering" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0">\
            </div>\
            <div class="form-group field-featured">\
              <label for="featured" class="checkbox-inline">\
                <input type="checkbox" name="featured" id="featured" value="1">Featured\
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

    $('#dmodal').removeClass('modal-edit modal-add').addClass(modalCls);
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
        task: 'category',
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
        $('#dmodal #type').html(html);
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
          task: 'category',
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

              applyFieldOptions();
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
          task: 'category',
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
      ETO.init({ config: [], lang: ['user'] }, 'location-categories');
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
          task: 'category',
          action: 'list'
        },
        dataSrc: 'list'
      },
      columns: [
      @if (auth()->user()->hasPermission(['admin.categories.edit', 'admin.categories.destroy'], true))
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
        title: 'Name',
        data: 'name',
        width: '200px'
      }, {
        title: 'Type',
        data: 'type',
        width: '200px',
        visible: false
      }, {
        title: 'Colour',
        data: 'color',
        width: '100px',
        visible: false
      }, {
        title: 'Icon',
        data: 'icon',
        width: '100px'
      }, {
        title: 'Featured',
        data: 'featured',
        width: '100px'
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
          if ( ETO.hasPermission('admin.categories.edit') ) {
            h += '<button type="button" onclick="updateRecord(' + row.id + '); return false;" class="btn btn-default btn-sm btnEdit" title="Edit"><i class="fa fa-pencil-square-o"></i></button>';
          }
          if ( ETO.hasPermission('admin.categories.destroy') && row.type == 'custom' ) {
            h += '<button type="button" onclick="deleteRecord('+ row.id +'); return false;" class="btn btn-default btn-sm btnDelete" title="Delete"><i class="fa fa-trash"></i></button>';
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
        [6, 'asc']
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

    if (!ETO.hasPermission(['admin.categories.edit', 'admin.categories.destroy'], true)) {
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
