@extends('admin.index')

@section('title', 'Types of Vehicles')


@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','form-validation/formValidation.min.css') }}">
@endsection


@section('subcontent')
  <div class="pageContainer" id="vehicles">
    <div class="pageTitle">

      <a href="#" class="btn btn-default btn-sm pull-right btnFilters" title="Search">
        <i class="fa fa-search"></i>
      </a>
        @permission('admin.vehicle_types.create')
      <a href="#" onclick="updateRecord(); return false;" class="btn btn-success btn-sm pull-right btnAdd">
        <i class="fa fa-plus"></i> <span>Add new</span>
      </a>
        @endpermission
      <h3>Types of Vehicles</h3>

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
  <script src="{{ asset_url('plugins','jquery-form/jquery.form.min.js') }}"></script>

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

    title = 'Delete vehicle';
    html = '<p style="margin-bottom:20px;">Are you sure you want to permanently delete this data?\
            <br><br><span style="color:red; font-weight:bold;">Important!</span> If you delete this type of vehicle it will also delete all <b>Distance & Time</b> and <b>Fixed pricing</b> associated with it.<br>You can’t revert this operation once it has been performed so please be careful.\
            </p>\
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
          task: 'vehicle',
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

    html = '<form method="post" enctype="multipart/form-data">\
            <input type="hidden" name="id" id="id" value="0">\
            <input type="hidden" name="site_id" id="site_id" value="0">\
            <div class="form-group field-name">\
              <label for="name">Name</label>\
              <input type="text" name="name" id="name" placeholder="Name" required class="form-control">\
            </div>\
            <div class="form-group field-description">\
              <label for="description">Description</label>\
              <textarea name="description" id="description" placeholder="Description" class="form-control"></textarea>\
            </div>\
            <div id="image_preview"></div>\
            <input type="hidden" name="image" id="image" value="">\
            <div class="form-group field-image_type">\
                <label for="image_type_gallery" class="checkbox-inline" style="padding-left:0px;">\
                    <input type="radio" name="image_type" id="image_type_gallery" value="0" checked> Gallery\
                </label>\
                <label for="image_type_upload" class="checkbox-inline">\
                    <input type="radio" name="image_type" id="image_type_upload" value="1"> Upload\
                </label>\
            </div>\
            <div class="form-group field-image_gallery" style="margin-bottom:30px;">\
                <label for="image_gallery">Choose image</label>\
                <select name="image_gallery" id="image_gallery" data-placeholder="Choose image" data-allow-clear="true" class="form-control select2"></select>\
            </div>\
            <div class="form-group field-image_upload" style="margin-bottom:30px; display:none;">\
                <label for="image_upload" style="display:inline-block;">Upload image</label>\
                <input type="file" name="image_upload" id="image_upload" class="form-control" />\
                <div style="background:#fffcd9; padding:10px; margin-top:10px;">In order to have the image display properly, please upload an image in ".png" format and 200x100 px size.</div>\
            </div>\
            <div class="form-group field-max_amount">\
              <label for="max_amount">Max vehicle amount</label>\
              <input type="text" name="max_amount" id="max_amount" placeholder="Max vehicle amount" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" data-bts-max="30" max="30">\
            </div>\
            <div class="form-group field-passengers">\
              <label for="passengers">Passengers</label>\
              <input type="text" name="passengers" id="passengers" placeholder="Passengers" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" data-bts-max="100" max="100">\
            </div>\
            <div class="form-group field-luggage">\
              <label for="luggage">Luggage</label>\
              <input type="text" name="luggage" id="luggage" placeholder="Luggage" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" data-bts-max="100" max="100">\
            </div>\
            <div class="form-group field-hand_luggage">\
              <label for="hand_luggage">Hand luggage</label>\
              <input type="text" name="hand_luggage" id="hand_luggage" placeholder="Hand luggage" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" data-bts-max="100" max="100">\
            </div>\
            <div class="form-group field-baby_seats">\
              <label for="baby_seats">Booster seats</label>\
              <input type="text" name="baby_seats" id="baby_seats" placeholder="Booster seats" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" data-bts-max="100" max="100">\
            </div>\
            <div class="form-group field-child_seats">\
              <label for="child_seats">Child seats</label>\
              <input type="text" name="child_seats" id="child_seats" placeholder="Child seats" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" data-bts-max="100" max="100">\
            </div>\
            <div class="form-group field-infant_seats">\
              <label for="infant_seats">Infant seats</label>\
              <input type="text" name="infant_seats" id="infant_seats" placeholder="Infant seats" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" data-bts-max="100" max="100">\
            </div>\
            <div class="form-group field-wheelchair">\
              <label for="wheelchair">Wheelchairs</label>\
              <input type="text" name="wheelchair" id="wheelchair" placeholder="Wheelchairs" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" data-bts-max="100" max="100">\
            </div>\
            <div class="form-group field-factor_type">\
              <label for="factor_type">Factor type</label>\
              <select name="factor_type" id="factor_type" data-placeholder="Factor type" data-minimum-results-for-search="Infinity" required class="form-control select2">\
                <option value="0">Add (+)</option>\
                <option value="1">Multiply (*)</option>\
              </select>\
            </div>\
            <div class="form-group field-price">\
              <label for="price">Factor value</label>\
              <input type="text" name="price" id="price" placeholder="Factor value" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" data-bts-max="null">\
            </div>\
            <div class="form-group field-ordering">\
              <label for="ordering">Ordering</label>\
              <input type="text" name="ordering" id="ordering" placeholder="Ordering" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" data-bts-max="null">\
            </div>\
            <div class="form-group field-service_ids" style="margin-top:20px; margin-bottom:20px; max-width:100%;">\
              <div style="margin-bottom:10px;">Assign to selected services (all if no options are selected)</div>\
              <div id="service_ids"></div>\
            </div>\
            <div class="form-group field-hourly_rate">\
              <label for="hourly_rate">Hourly rate</label>\
              <input type="text" name="hourly_rate" id="hourly_rate" placeholder="Hourly rate" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" data-bts-max="null">\
            </div>\
            <div class="form-group field-user_id">\
              <label for="user_id">Driver</label>\
              <select name="user_id" id="user_id" data-placeholder="Driver" class="form-control select2"></select>\
            </div>\
            <div class="form-group field-disable_info">\
              <label for="disable_info" class="checkbox-inline">\
                <input type="checkbox" name="disable_info" id="disable_info" value="yes"> Enable enquire button\
              </label>\
            </div>\
            <div class="form-group field-default">\
              <label for="default" class="checkbox-inline">\
                <input type="checkbox" name="default" id="default" value="1"> Default\
              </label>\
            </div>\
            <div class="form-group field-published">\
              <label for="published" class="checkbox-inline">\
                <input type="checkbox" name="published" id="published" value="1" checked> Active\
              </label>\
            </div>\
            <div class="form-group field-is_backend">\
              <label for="is_backend">Display</label>\
              <select name="is_backend" id="is_backend" data-placeholder="Display" data-minimum-results-for-search="Infinity" required class="form-control select2">\
                <option value="0">Frontend & Backend</option>\
                <option value="1">Backend</option>\
              </select>\
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
        task: 'vehicle',
        action: 'init'
      },
      success: function(response) {
        // Images
        html = '';
        if (response.imagesList) {
          html += '<option value=""></option>';
          $.each(response.imagesList, function(index, item) {
            html += '<option value="'+ item.value +'">'+ item.text +'</option>';
          });
        }
        $('#dmodal #image_gallery').html(html);

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
            $('#dmodal .field-hourly_rate').show();
        }
        else {
            $('#dmodal .field-service_ids').hide();
            $('#dmodal .field-hourly_rate').hide();
        }

        // Users
        html = '';
        if (response.usersList) {
          $.each(response.usersList, function(index, item) {
            html += '<option value="'+ item.id +'">'+ item.name +'</option>';
          });
        }
        $('#dmodal #user_id').html(html);

        if( html ) {
            $('#dmodal .field-user_id').show();
        }
        else {
            $('#dmodal .field-user_id').hide();
        }

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
          task: 'vehicle',
          action: 'read',
          id: id
        },
        success: function(response) {
          if( response.success ) {
            $('#dmodal #statusMsg').html('');

            if( response.record ) {
              $.each(response.record, function(key, value) {

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
                else if( key == 'service_ids' ) {
                    $.each(value, function(key2, value2) {
                        if( $('#dmodal form #service_ids'+ value2).length > 0 ) {
                            $('#dmodal form #service_ids'+ value2).attr('checked', true);
                        }
                    });
                }
                else if( key == 'image' ) {
                    var filename = value;
                    var filepath = '{{ asset_url('uploads','vehicles-types') }}';
                    if( filename ) {
                        var image_preview = '<img src="'+ filepath +'/'+ filename +'" style="max-width:150px; max-height:150px; margin-bottom:10px;" />';
                        $('#dmodal form #image_preview').html(image_preview);
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

    // Image preview
    $('#dmodal form #image_gallery').change(function(){
        if( $(this).val() ) {
            var filename = $(this).val();
            var filepath = '{{ asset_url('images','vehicles-types') }}';
        }
        else {
            var filename = $('#dmodal form #image').val();
            var filepath = '{{ asset_url('uploads','vehicles-types') }}';
        }
        // console.log(filename, filepath);
        if( filename ) {
            var image_preview = '<img src="'+ filepath +'/'+ filename +'" style="max-width:150px; max-height:150px; margin-bottom:10px;" />';
            $('#dmodal form #image_preview').html(image_preview);
        }
    }).change();

    // Image type
    $('#dmodal form input[name="image_type"]').change(function(){
        if( $(this).val() == 1 ) {
            $('#dmodal .field-image_gallery').hide();
            $('#dmodal .field-image_upload').show();
        }
        else {
            $('#dmodal .field-image_gallery').show();
            $('#dmodal .field-image_upload').hide();
        }
    });

    // Select
    $('#dmodal form .select2').select2();

    // Spinner
    $('#dmodal form input[type="text"].touchspin').TouchSpin({
      // max: null,
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

    $('#dmodal form').submit(function(e) {
        $('#dmodal form').formValidation('resetForm');
        e.preventDefault();
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
      // if( data.fv.getSubmitButton() ) {
        data.fv.disableSubmitButtons(false);
      // }
    })
    .on('success.field.fv', function(e, data) {
      e.preventDefault();
      // if( data.fv.getSubmitButton() ) {
        data.fv.disableSubmitButtons(false);
      // }
    })
    .on('success.form.fv', function(e) {
      e.preventDefault();
      if( isReady ) {
        // https://jquery-form.github.io/form/options/#code-samples
        $('#dmodal form').ajaxSubmit({
            url: EasyTaxiOffice.appPath +'/etov2?apiType=backend&task=vehicle&action=update',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('#dmodal #statusMsg').html('<span class="text-green"><i class="fa fa-check-circle"></i> '+ msgSuccess +'</span>');
                    if( parseInt($('#dmodal form #id').val()) <= 0 ) {
                        $('#dmodal form').trigger('reset');
                        $('#dmodal').modal('hide');
                        filterTable();
                    }
                    else {
                        if( response.id ) {
                            updateRecord(response.id);
                            filterTable();
                        }
                    }
                }
                else {
                    var msg = 'The data could not be updated';
                    if( response.errors ) {
                        msg = '';
                        $.each(response.errors, function(index, error) {
                            if( msg ) { msg += ', '; }
                            msg += error;
                        });
                    }
                    $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ msg +'</span>');
                }
                isReady = 1;
            },
            error: function(response) {
                $('#dmodal #statusMsg').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
                isReady = 1;
            },
            beforeSubmit: function() {
                $('#dmodal #statusMsg').html('<i class="fa fa-spinner fa-spin"></i> In progress');
                isReady = 0;
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
                task: 'vehicle',
                action: 'list'
            },
            dataSrc: 'list'
        },
        columns: [
                @if (auth()->user()->hasPermission(['admin.vehicle_types.edit', 'admin.vehicle_types.destroy'], true))
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
                title: 'Image',
                data: 'image',
                width: '160px'
            }, {
                title: 'Name',
                data: 'name',
                width: '200px'
            }, {
                title: 'Driver',
                data: 'user_id',
                width: '200px',
                class: 'column-user_id'
            }, {
                title: 'Services',
                data: 'service_ids',
                width: '200px',
                class: 'column-service_ids'
            }, {
                title: 'Hourly rate',
                data: 'hourly_rate',
                width: '100px',
                class: 'column-hourly_rate'
            }, {
                title: 'Capacity',
                data: 'capacity',
                width: '100px',
                orderable: false
            },/* {
        title: 'Description',
        data: 'description',
        width: '200px'
      },*/ {
                title: 'Enquire',
                data: 'disable_info',
                width: '200px'
            }, {
                title: 'Default',
                data: 'default',
                width: '100px'
            }, {
                title: 'Active',
                data: 'published',
                width: '80px'
            }, {
                title: 'Ordering',
                data: 'ordering',
                width: '100px'
            }, {
                title: 'Display',
                data: 'is_backend',
                width: '80px'
            }],
        columnDefs: [{
            targets: 0,
            data: null,
            render: function(data, type, row) {
                var h = '';
                h += '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';
                if(ETO.hasPermission('admin.vehicle_types.edit')) {
                    h += '<button type="button" onclick="updateRecord(' + row.id + '); return false;" class="btn btn-default btn-sm btnEdit" title="Edit"><i class="fa fa-pencil-square-o"></i></button>';
                }
                if(ETO.hasPermission('admin.vehicle_types.destroy')) {
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

      if (!ETO.hasPermission(['admin.vehicle_types.edit', 'admin.vehicle_types.destroy'], true)) {
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
