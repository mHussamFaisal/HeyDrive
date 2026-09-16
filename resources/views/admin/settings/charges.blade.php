@extends('admin.index')

@section('title', trans('admin/settings.charges.subtitle'))
@section('subtitle', /*'<i class="fa fa-cogs"></i> '.*/ trans('admin/settings.charges.subtitle'))


@section('subheader')
<link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">

<style>
#charges-form .container-general>div:not(.field-type) {
  display: none;
}
.container-general .form-group,
.container-datetime .form-group {
  float: left;
}
.container-name,
.container-location,
.container-vehicle,
.container-datetime {
  margin: 0px 0px 20px 0px;
}
.container-datetime {
  margin-bottom: 10px;
}
.form-group {
  position: relative;
  max-width: 200px;
  margin-right: 10px;
  margin-bottom: 10px;
}
.form-group .input-group-addon {
  position: relative;
  width: 34px;
  color: #b7b7b7;
  font-weight: bold;
}
.form-group:hover .input-group-addon {
  color: #333;
}
.form-group .input-group-addon i {
  display: inline-block;
  position: absolute;
  top: 8px;
  left: 50%;
  margin-left: -25%;
  width: 16px;
  font-size: 16px;
}
.form-group label {
  position: absolute;
  top: -8px;
  left: 8px;
  display: block;
  background: #fff;
  line-height: 12px;
  font-size: 12px;
  color: #888;
  margin: 0;
  padding: 2px 4px;
  z-index: 99;
}
.checkbox {
  margin: 0px 10px 10px 0;
}
label {
  font-weight: normal;
}
.field-name-enabled label,
.field-status label,
.field-vehicle label,
.field-enabled label {
  padding-left: 0px;
}
.field-name-enabled input,
.field-status input,
.field-vehicle input,
.field-enabled input {
  display: none;
}
.field-name-enabled i,
.field-status i,
.field-vehicle i,
.field-enabled i {
  margin-right: 8px;
  color: #c1c1c1;
  font-size: 16px;
  line-height: 20px;
  float: left;
}
.field-name-enabled:hover i,
.field-status:hover i,
.field-vehicle:hover i,
.field-enabled:hover i {
  color: #333;
}
.field-name {
  margin-bottom: 0;
  max-width: 360px;
}
#vehicle-list .field-vehicle:last-child,
#location-list .field-address:last-child {
  margin-bottom: 0;
}
.field-address {
  max-width: 360px;
}
.field-address .input-group {
  width: 100%;
}
.button-datetime-action,
.button-address-action {
  display: none;
}
.field-datetime:hover .button-datetime-action,
.field-address:hover .button-address-action {
  display: table-cell;
}
.button-datetime-action,
.button-address-action {
  color: #b7b7b7;
  cursor: pointer;
}
.button-address-delete {
  border-left: 0;
}
.button-datetime-clear:hover,
.button-address-delete:hover {
  color: #ac2925 !important;
}
.button-address-create:hover {
  color: #00a65a !important;
}
#charges-form .field-price {
  max-width: 140px;
}
#charges-form .field-location-type {
  max-width: 140px;
}
.field-datetime {
  max-width: 240px;
}
#charges-list td {
  vertical-align: middle;
}
.no-charges {
  text-align: center;
  color: #888;
}
.button-add {
  margin-top: 6px;
}
.button-save-group {
  float: left;
  margin-right: 10px;
}
.button-save i {
  margin-right: 5px;
}
.button-charge-edit,
.button-charge-delete {
  margin-right: 5px;
}
.restriction-item {
  font-size: 12px;
  margin: 2px 0px;
}
.restriction-title {
  display: inline-block;
  color: #a5953d;
}
#status-message {
  margin-top: 6px;
}
.pac-container {
  z-index: 9999;
}
</style>
@endsection


@section('subcontent')
<div id="settings-charges">
    <div class="clearfix">
        @permission('admin.settings.charges.create')
        <a href="#" class="btn btn-sm btn-success button-add pull-right" onclick="addCharge(); return false;">
            <i class="fa fa-plus"></i> <span>Add</span>
        </a>
        @endpermission
        <h3 id="settings-header">{{ trans('admin/settings.charges.subtitle') }}</h3>
    </div>

    <table id="charges-list" class="table table-hover table-condensed">
        <thead>
            <tr>
                <th></th>
                <th>{{ trans('admin/settings.charges.name') }}</th>
                <th>{{ trans('admin/settings.charges.price') }}</th>
                <th>Restrictions</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr class="no-charges">
                <td colspan="5">Nothing to display here yet.</td>
            </tr>
        </tbody>
    </table>

    <div id="charges-modal" class="modal fade" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h4 class="modal-title"></h4>
              </div>
              <div class="modal-body">

                    <form method="post" action="{{ route('admin.settings.charges', ['action' => 'save']) }}" id="charges-form" autocomplete="off">
                        {{ csrf_field() }}

                        <input type="hidden" name="submit_action" id="submit_action" value="save">
                        <input type="hidden" name="id" id="id" value="0">

                        <div class="clearfix container-general">
                            <div class="form-group field-type" title="{{ trans('admin/settings.charges.type') }}">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-gears"></i>
                                    </div>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">{{ trans('admin/settings.charges.type_options.select') }}</option>
                                        <option value="parking">{{ trans('admin/settings.charges.type_options.parking') }}</option>
                                        {{-- <option value="waiting">{{ trans('admin/settings.charges.type_options.waiting') }}</option> --}}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group field-price" title="{{ trans('admin/settings.charges.price') }}">
                                <div class="input-group">
                                    <div class="input-group-addon">{{ config('site.currency_symbol') ? config('site.currency_symbol') : config('site.currency_code') }}</div>
                                    <input type="number" name="price" id="price" class="form-control" value="0" min="0" step="0.01">
                                </div>
                            </div>
                        </div>

                        <div class="checkbox field-enabled field-name-enabled">
                            <label for="name-enabled">
                                <input type="checkbox" name="name_enabled" id="name-enabled" value="1"> <span><i class="fa fa-circle-thin"></i> {{ trans('admin/settings.charges.name_enabled') }}</span>
                            </label>
                        </div>
                        <div class="clearfix container-name">
                            <div class="form-group field-name" title="{{ trans('admin/settings.charges.name') }}">
                                <input type="text" name="name" id="name" class="form-control" value="" placeholder="{{ trans('admin/settings.charges.name') }}">
                            </div>
                        </div>

                        <div class="checkbox field-enabled field-location-enabled">
                            <label for="location-enabled">
                                <input type="checkbox" name="location[enabled]" id="location-enabled" value="1"> <span><i class="fa fa-circle-thin"></i> {{ trans('admin/settings.charges.location_enabled') }}</span>
                            </label>
                        </div>
                        <div class="clearfix container-location">
                            <div class="form-group field-location-type" title="{{ trans('admin/settings.charges.location_type') }}">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-location-arrow"></i>
                                    </div>
                                    <select name="location[type]" id="location-type" class="form-control">
                                        <option value="all">{{ trans('admin/settings.charges.location_type_options.all') }}</option>
                                        <option value="from">{{ trans('admin/settings.charges.location_type_options.from') }}</option>
                                        <option value="to">{{ trans('admin/settings.charges.location_type_options.to') }}</option>
                                        {{-- <option value="via">{{ trans('admin/settings.charges.location_type_options.via') }}</option> --}}
                                    </select>
                                </div>
                            </div>
                            <div id="location-list"></div>
                        </div>

                        <div class="checkbox field-enabled field-vehicle-enabled">
                            <label for="vehicle-enabled">
                                <input type="checkbox" name="vehicle[enabled]" id="vehicle-enabled" value="1"> <span><i class="fa fa-circle-thin"></i> {{ trans('admin/settings.charges.vehicle_enabled') }}</span>
                            </label>
                        </div>
                        <div class="clearfix container-vehicle">
                            <div id="vehicle-list">
                                @php
                                    $vehicles = App\Models\VehicleType::where('site_id', config('site.site_id'))
                                      ->where('published', 1)
                                      ->orderBy('ordering', 'asc')
                                      ->orderBy('name', 'asc')
                                      ->get();
                                @endphp

                                @if( $vehicles )
                                    <div class="checkbox field-vehicle field-vehicle-all">
                                        <label for="vehicle-all">
                                            <input type="checkbox" id="vehicle-all" value="all"> <span><i class="fa fa-square-o"></i> {{ trans('admin/settings.charges.vehicle_all') }}</span>
                                        </label>
                                    </div>
                                    @foreach($vehicles as $vehicle)
                                        <div class="checkbox field-vehicle field-vehicle-{{ $vehicle->id }}">
                                            <label for="vehicle-{{ $vehicle->id }}">
                                                <input type="checkbox" name="vehicle[list][]" id="vehicle-{{ $vehicle->id }}" value="{{ $vehicle->id }}" vehicle_name="{{ $vehicle->name }}"> <span><i class="fa fa-square-o"></i> {{ $vehicle->name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <div>{{ trans('admin/settings.charges.message.no_vehicles') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="checkbox field-enabled field-datetime-enabled">
                            <label for="datetime-enabled">
                                <input type="checkbox" name="datetime[enabled]" id="datetime-enabled" value="1"> <span><i class="fa fa-circle-thin"></i> {{ trans('admin/settings.charges.datetime_enabled') }}</span>
                            </label>
                        </div>
                        <div class="clearfix container-datetime">
                            <div class="form-group field-datetime field-datetime-start" title="{{ trans('admin/settings.charges.datetime_start') }}">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar-o"></i>
                                    </div>
                                    <input type="text" name="datetime[start]" id="datetime-start" class="form-control datepicker" value="" placeholder="{{ trans('admin/settings.charges.datetime_start') }}">
                                    <div class="input-group-addon button-datetime-action button-datetime-clear" onclick="$('#datetime-start').val(''); return false;" title="{{ trans('admin/settings.button.clear') }}">
                                        <i class="fa fa-remove"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group field-datetime field-datetime-end" title="{{ trans('admin/settings.charges.datetime_end') }}">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar-o"></i>
                                    </div>
                                    <input type="text" name="datetime[end]" id="datetime-end" class="form-control datepicker" value="" placeholder="{{ trans('admin/settings.charges.datetime_end') }}">
                                    <div class="input-group-addon button-datetime-action button-datetime-clear" onclick="$('#datetime-end').val(''); return false;" title="{{ trans('admin/settings.button.clear') }}">
                                        <i class="fa fa-remove"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix">
                            <div class="checkbox field-status">
                                <label for="status">
                                    <input type="checkbox" name="status" id="status" value="1"> <span><i class="fa fa-square-o"></i> {{ trans('admin/settings.charges.status') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="clearfix">
                            <div class="btn-group button-save-group">
                                <button type="submit" class="btn btn-sm btn-success button-save" onclick="$('#charges-form #submit_action').val('save');">
                                    <i class="fa fa-save"></i> <span>{{ trans('admin/settings.button.save') }}</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown">
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    {{-- <li class="button-save-close"><a href="#" onclick="$('#charges-form #submit_action').val('save_close'); $('#charges-form').submit(); return false;">Save & Close</a></li> --}}
                                    <li class="button-save-new"><a href="#" onclick="$('#charges-form #submit_action').val('save_new'); $('#charges-form').submit(); return false;">Save & New</a></li>
                                    <li class="button-save-copy"><a href="#" onclick="$('#charges-form #submit_action').val('save'); $('#charges-form #id').val(0); $('#charges-form').submit(); return false;">Save as Copy</a></li>
                                </ul>
                            </div>
                            <div id="status-message"></div>
                        </div>
                    </form>

              </div>
              <div class="modal-overlay"><i class="fa fa-refresh fa-spin"></i></div>
            </div>
        </div>
    </div>

</div>
@endsection


@section('subfooter')
<script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
<script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset_url('plugins','jquery-serializejson/jquery.serializejson.min.js') }}"></script>

<script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places"></script>

<script>
var isReady = 1;
var chargeIndex = 0;
var addressIndex = 0;

function createCharge(data) {
    chargeIndex++;
    var htmlActions = '';
    var htmlName = '';
    var htmlPrice = '';
    var htmRestrictions = '';
    var htmStatus = '';

    if( data.name ) {
        htmlName += (htmlName ? ', ' : '') + data.name;
    }

    if( data.type ) {
        var name = '';
        switch( data.type ) {
            case 'parking':
                name += 'Parking';
            break;
            case 'waiting':
                name += 'Waiting';
            break;
        }
        if( name && !htmlName ) {
            htmlName += name;
        }
    }

    if( data.price > 0 ) {
        htmlPrice = '{{ config('site.currency_symbol') }}'+ data.price +'{{ config('site.currency_code') }}';
    }

    if( data.location && data.location.enabled ) {
        var type = '';
        var list = '';
        if( data.location.type ) {
            switch( data.location.type ) {
                case 'all':
                    type += 'Any';
                break;
                case 'from':
                    type += 'Pickup';
                break;
                case 'to':
                    type += 'Dropoff';
                break;
                case 'via':
                    type += 'Via';
                break;
            }
        }
        if( data.location.list ) {
            $.each(data.location.list, function(index, item) {
                // list += (list ? ', ' : '') + item;
                list += '<div>'+ item +'</div>';
            });
        }
        if( list ) {
            htmRestrictions += '<div class="restriction-item"><span class="restriction-title">Location '+ (type ? '('+ type +')' : '') +':</span> <div style="max-height:50px; overflow:auto;">'+ list +'</div></div>';
        }
    }

    if( data.vehicle && data.vehicle.enabled ) {
        var list = '';
        if( data.vehicle.list ) {
            $.each(data.vehicle.list, function(index, item) {
                list += (list ? ', ' : '') + $('#vehicle-'+ item).attr('vehicle_name');
            });
        }
        if( list ) {
            htmRestrictions += '<div class="restriction-item"><span class="restriction-title">Vehicle type:</span> '+ list +'</div>';
        }
    }

    if( data.datetime && data.datetime.enabled ) {
        if( data.datetime.start ) {
            htmRestrictions += '<div class="restriction-item"><span class="restriction-title">Starts on:</span> '+ data.datetime.start +'</div>';
        }
        if( data.datetime.end ) {
            htmRestrictions += '<div class="restriction-item"><span class="restriction-title">Ends on:</span> '+ data.datetime.end +'</div>';
        }
    }

    if( data.status ) {
        htmStatus += '<span class="text-success status-icon" title="Active"><i class="fa fa-check-circle"></i></span>';
    }
    else {
        htmStatus += '<span class="text-danger status-icon" title="Inactive"><i class="fa fa-times-circle"></i></span>';
    }
    htmlActions = '';
    if(ETO.hasPermission('admin.settings.charges.edit')) {
        htmlActions += '<a href="#" onclick="editCharge(' + chargeIndex + ', ' + data.id + '); return false;" class="btn btn-sm btn-default button-charge-edit" title="Edit"><i class="fa fa-pencil-square-o"></i></a>';
    }
    if(ETO.hasPermission('admin.settings.charges.destroy')) {
        htmlActions += '<a href="#" onclick="deleteCharge(' + chargeIndex + ', ' + data.id + '); return false;" class="btn btn-sm btn-default button-charge-delete" title="Delete"><i class="fa fa-trash"></i></a>';
    }

    html = '<tr class="row-charge row-charge-'+ chargeIndex +'" >'+
              '<td>'+ htmlActions +'</td>'+
              '<td>'+ htmlName +'</td>'+
              '<td>'+ htmlPrice +'</td>'+
              '<td>'+ htmRestrictions +'</td>'+
              '<td>'+ htmStatus +'</td>'+
            '</tr>';

    $('#charges-list tbody').append(html);

    $('#charges-list').find('.button-charge-edit').hover(
        function() {
            $(this).removeClass('btn-default').addClass('btn-primary');
        },
        function() {
            $(this).removeClass('btn-primary').addClass('btn-default');
        }
    );

    $('#charges-list').find('.button-charge-delete').hover(
        function() {
            $(this).removeClass('btn-default').addClass('btn-danger');
        },
        function() {
            $(this).removeClass('btn-danger').addClass('btn-default');
        }
    );

    return chargeIndex;
}

function listCharge() {
    if( !isReady ) {
        return false;
    }

    $.ajax({
        headers : {
            'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
        },
        url: '{{ route('admin.settings.charges', ['action' => 'list']) }}',
        type: 'POST',
        dataType: 'json',
        cache: false,
        success: function(response) {
            if( !response.error ) {
                $('#charges-list .row-charge').remove();

                $.each(response.results, function(index, item) {
                    createCharge(item);
                });

                if( $('#charges-list .row-charge').length > 0 ) {
                    $('#charges-list .no-charges').hide();
                }
                else {
                    $('#charges-list .no-charges').show();
                }
            }
            else {
                //
            }
        },
        error: function(response) {
            //
        },
        beforeSend: function() {
            isReady = 0;
        },
        complete: function() {
            isReady = 1;
        }
    });
}

function addCharge() {
    var form = $('#charges-form');
    form.find('#type').val(form.find('#type').find('option:first').val()).change();
    $('#charges-modal .modal-title').html('Add');
    $('#charges-modal').modal('show');
}

function editCharge(index, id) {
    if( !isReady ) {
        return false;
    }

    $.ajax({
        headers : {
            'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
        },
        url: '{{ route('admin.settings.charges', ['action' => 'read']) }}',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
            id: id
        },
        success: function(response) {
            if( !response.error ) {

                var data = response.results;
                var form = $('#charges-form');

                if( data.type ) {
                    form.find('#type').val(data.type).change();
                }

                form.find('#status').attr('checked', data.status ? true : false).change();

                form.find('#name-enabled').attr('checked', data.name_enabled ? true : false).change();
                if( data.name ) {
                    form.find('#name').val(data.name);
                }

                form.find('#id').val(data.id);

                if( data.price ) {
                    form.find('#price').val(data.price);
                }

                if( data.location && data.location.enabled ) {
                    form.find('#location-enabled').attr('checked', data.location.enabled ? true : false).change();
                    if( data.location.type ) {
                        form.find('#location-type').val(data.location.type);
                    }
                    if( data.location.list ) {
                        form.find('#location-list').html('');
                        $.each(data.location.list, function(index, item) {
                            var id = createAddress();
                            form.find('#address-'+ id).val(item);
                        });
                    }
                }

                if( data.vehicle && data.vehicle.enabled ) {
                    form.find('#vehicle-enabled').attr('checked', data.vehicle.enabled ? true : false).change();
                    if( data.vehicle.list ) {
                        $.each(data.vehicle.list, function(index, item) {
                            form.find('#vehicle-'+ item).attr('checked', true).change();
                        });
                    }

                    var checked = true;
                    form.find('#vehicle-list input[name="vehicle[list][]"]').each(function(index, item) {
                        if( !$(this).is(':checked') ) { checked = false; }
                    });
                    form.find('#vehicle-all').attr('checked', checked);
                }

                if( data.datetime && data.datetime.enabled ) {
                    form.find('#datetime-enabled').attr('checked', data.datetime.enabled ? true : false).change();
                    if( data.datetime.start ) {
                        form.find('#datetime-start').val(data.datetime.start);
                    }
                    if( data.datetime.end ) {
                        form.find('#datetime-end').val(data.datetime.end);
                    }
                }

                $('#charges-list .row-charge-'+ index +' .button-charge-edit i').attr('class', 'fa fa-pencil-square-o');

                $('#charges-form .button-save-copy').show();
                $('#charges-modal .modal-title').html('Edit');
                $('#charges-modal').modal('show');
            }
            else {
                //
            }
        },
        error: function(response) {
            //
        },
        beforeSend: function() {
            isReady = 0;
            $('#charges-list .row-charge-'+ index +' .button-charge-edit i').attr('class', 'fa fa-spinner fa-spin');
        },
        complete: function() {
            isReady = 1;
        }
    });
}

function saveCharge() {
    if( !isReady ) {
        return false;
    }

    var form = $('#charges-form');

    $.ajax({
        headers : {
            'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
        },
        url: '{{ route('admin.settings.charges', ['action' => 'save']) }}',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
            params: form.serializeJSON()
        },
        success: function(response) {
            if( !response.error ) {
                isReady = 1;
                listCharge();

                // if( form.find('#submit_action').val() == 'save_close' ) {
                //     $('#charges-modal').modal('hide');
                // }
                if( form.find('#submit_action').val() == 'save_new' ) {
                    addCharge();
                }
                else {
                    $('#charges-modal').modal('hide');
                }

                form.find('#status-message').html('<span class="text-green"><i class="fa fa-check"></i> {{ trans('admin/settings.message.saved') }}</span>');
                setTimeout(function() {
                    form.find('#status-message').html('');
                }, 5000);
            }
            else {
                form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ response.error +'</span>');
            }
        },
        error: function(response) {
            form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ trans('admin/settings.message.connection_error') }}</span>');
        },
        beforeSend: function() {
            isReady = 0;
            form.find('.button-save').html('<i class="fa fa-spinner fa-spin"></i> {{ trans('admin/settings.button.saving') }}');
        },
        complete: function() {
            isReady = 1;
            form.find('.button-save').html('<i class="fa fa-save"></i> {{ trans('admin/settings.button.save') }}');
        }
    });
}

function deleteCharge(index, id) {
    if( !isReady ) {
        return false;
    }

    $.ajax({
        headers : {
            'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
        },
        url: '{{ route('admin.settings.charges', ['action' => 'delete']) }}',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
            id: id
        },
        success: function(response) {
            if( !response.error ) {
                $('#charges-list .row-charge-'+ index).remove();

                if( $('#charges-list .row-charge').length > 0 ) {
                    $('#charges-list .no-charges').hide();
                }
                else {
                    $('#charges-list .no-charges').show();
                }
            }
            else {
                // form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ response.error +'</span>');
            }
        },
        error: function(response) {
            // form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> An error occurred while processing your request</span>');
        },
        beforeSend: function() {
            isReady = 0;
            $('#charges-list .row-charge-'+ index +' .button-charge-delete i').attr('class', 'fa fa-spinner fa-spin');
            // form.find('.button-save').html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        },
        complete: function() {
            isReady = 1;
            // form.find('.button-save').html('<i class="fa fa-save"></i> Save');
        }
    });
}

function createAddress(position = 0) {
    addressIndex++;

    var html = '<div class="form-group field-address field-address-'+ addressIndex +'" title="Address">\
                    <div class="input-group">\
                        <div class="input-group-addon">\
                            <i class="fa fa-map-marker"></i>\
                        </div>\
                        <input type="text" name="location[list][]" id="address-'+ addressIndex +'" class="form-control" value="" placeholder="Address">\
                        <div class="input-group-addon button-address-action button-address-delete" onclick="deleteAddress('+ addressIndex +'); return false;" title="Delete">\
                            <i class="fa fa-minus"></i>\
                        </div>\
                        <div class="input-group-addon button-address-action button-address-create" onclick="createAddress('+ addressIndex +'); return false;" title="Add new address">\
                            <i class="fa fa-plus"></i>\
                        </div>\
                    </div>\
                </div>';

    var form = $('#charges-form #location-list');

    if( position ) {
        form.find('.field-address-'+ position).after(html);
    }
    else {
        form.append(html);
    }

    autocomplete = new google.maps.places.Autocomplete(document.getElementById('address-'+ addressIndex), {
        types: ['geocode']
    });
    // autocomplete.addListener('place_changed', function(){
    //
    // });

    if( form.find('.field-address').length <= 1 ) {
        form.find('.button-address-delete').addClass('hide');
    }
    else {
        form.find('.button-address-delete').removeClass('hide');
    }

    return addressIndex;
}

function deleteAddress(id = 0) {
    var form = $('#charges-form #location-list');

    form.find('.field-address-'+ id).remove();

    if( form.find('.field-address').length <= 0 ) {
        createAddress();
    }

    if( form.find('.field-address').length <= 1 ) {
        form.find('.button-address-delete').addClass('hide');
    }
    else {
        form.find('.button-address-delete').removeClass('hide');
    }
}

function checkForm() {
    var form = $('#charges-form');
    var errors = [];
    var message = '';

    if( !form.find('#type').val() ) {
        errors.push('Please select charge!');
    }

    if( !form.find('#price').val() ) {
        errors.push('Please enter price!');
    }

    var isAddress = 1;

    form.find('input[name="location[list][]"]').each(function(index, item) {
        if( !$(this).val() ) {
            isAddress = 0;
        }
    });

    if( form.find('#location-enabled').is(':checked') && !isAddress ) {
        errors.push('Please enter address!');
    }

    $.each(errors, function(index, item) {
        message += item + "\r\n";
    });

    if( message ) {
        alert(message);
        return false;
    }
    else {
        return true;
    }
}

function updateForm() {
    var form = $('#charges-form');

    form.find('#id').val(0);
    form.find('.field-price').hide();
    form.find('#price').val(0);

    form.find('.field-status').hide();

    form.find('.field-name-enabled').hide();
    form.find('#name-enabled').attr('checked', false).change();
    form.find('#name').val('');

    form.find('.field-location-enabled').hide();
    form.find('#location-enabled').attr('checked', false).change();
    form.find('#location-type').val('all');
    form.find('#location-list').html('');
    if( form.find('#location-list .field-address').length <= 0 ) {
        createAddress();
    }

    form.find('.field-vehicle-all').hide();
    form.find('.field-vehicle-enabled').hide();
    form.find('#vehicle-enabled').attr('checked', false).change();
    form.find('#vehicle-all').attr('checked', false).change();
    form.find('#vehicle-list input[name="vehicle[list][]"]').each(function(index, item) {
        $(this).attr('checked', false).change();
    });

    form.find('.field-datetime-enabled').hide();
    // form.find('#location-enabled').attr('disabled', false);
    form.find('#datetime-enabled').attr('checked', false).change();
    form.find('#datetime-start').val('');
    form.find('#datetime-end').val('');

    form.find('.button-save-group').hide();
    form.find('#status-message').html('');
    form.find('.button-save-copy').hide();

    form.find('.field-type').hide();
    form.find('#type').val('parking');

    var type = form.find('#type').val();

    switch( type ) {
        case 'parking':
            form.find('.field-price').show();
            form.find('.field-location-enabled').show();
            // form.find('#location-enabled').attr('disabled', true);
            form.find('#location-enabled').attr('checked', true).change();
            form.find('.field-vehicle-enabled').show();
            // form.find('.field-datetime-enabled').show();
            form.find('.field-name-enabled').show();
            form.find('#name-enabled').attr('checked', true).change();
            form.find('#name').val('Parking');
        break;
        case 'waiting':
            form.find('.field-price').show();
            form.find('.field-name-enabled').show();
            form.find('#name-enabled').attr('checked', true).change();
            form.find('#name').val('Waiting');
        break;
    }

    if( type ) {
        form.find('.field-status').show();
        form.find('.button-save-group').show();
    }
}

$(document).ready(function() {
    if (ETO.model === false) {
        ETO.init({ config: [], lang: ['user'] }, 'settings');
    }

    listCharge();

    $('#charges-modal').modal({
      show: false
    });

    var form = $('#charges-form');

    form.find('.datepicker').daterangepicker({
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

    form.submit(function(e) {
        if( checkForm() ) {
            saveCharge();
        }
        e.preventDefault();
    });

    form.find('#type').change(function(e){
        updateForm();
        e.preventDefault();
    }).change();

    form.find('#name-enabled').change(function(e){
        var container = form.find('.container-name');
        var checked = $(this).is(':checked') ? true : false;
        if( checked ) { container.show(); } else { container.hide(); }
        $(this).closest('label').find('span i').attr('class', 'fa '+ (checked ? 'fa-check-circle' : 'fa-circle-thin'));
        e.preventDefault();
    }).change();

    form.find('#location-enabled').change(function(e){
        var container = form.find('.container-location');
        var checked = $(this).is(':checked') ? true : false;
        if( checked ) { container.show(); } else { container.hide(); }
        $(this).closest('label').find('span i').attr('class', 'fa '+ (checked ? 'fa-check-circle' : 'fa-circle-thin'));
        e.preventDefault();
    }).change();

    form.find('#vehicle-enabled').change(function(e){
        var container = form.find('.container-vehicle');
        var checked = $(this).is(':checked') ? true : false;
        if( checked ) { container.show(); } else { container.hide(); }
        $(this).closest('label').find('span i').attr('class', 'fa '+ (checked ? 'fa-check-circle' : 'fa-circle-thin'));
        e.preventDefault();
    }).change();

    form.find('#vehicle-all').change(function(e){
        var checked = $(this).is(':checked') ? true : false;
        form.find('#vehicle-list input[name="vehicle[list][]"]').each(function(index, item) {
            $(this).attr('checked', checked).change();
        });
        e.preventDefault();
    }).change();

    form.find('#vehicle-list input[type="checkbox"]').change(function(e) {
        var checked = $(this).is(':checked') ? true : false;
        $(this).closest('label').find('span i').attr('class', 'fa '+ (checked ? 'fa-check-square' : 'fa-square-o'));
        e.preventDefault();
    });

    form.find('#datetime-enabled').change(function(e){
        var container = form.find('.container-datetime');
        var checked = $(this).is(':checked') ? true : false;
        if( checked ) { container.show(); } else { container.hide(); }
        $(this).closest('label').find('span i').attr('class', 'fa '+ (checked ? 'fa-check-circle' : 'fa-circle-thin'));
        e.preventDefault();
    }).change();

    form.find('#status').change(function(e){
        var checked = $(this).is(':checked') ? true : false;
        $(this).closest('label').find('span i').attr('class', 'fa '+ (checked ? 'fa-check-square' : 'fa-square-o'));
        e.preventDefault();
    }).change();
});
</script>
@endsection
