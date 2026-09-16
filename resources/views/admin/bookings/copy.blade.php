@extends('admin.index')

@section('title', trans('admin/bookings.page_title') .' / '. $booking->getRefNumber() .' / '. trans('admin/bookings.subtitle.copy'))
@section('subtitle', /*'<i class="fa fa-files-o"></i> '*/ '<a href="'. route('admin.bookings.index') .'">'. trans('admin/bookings.page_title') .'</a> / <a href="'. route('admin.bookings.show', $booking->id) .'">'. $booking->getRefNumber() .'</a> / '. trans('admin/bookings.subtitle.copy') )


@section('subheader')
<link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
@endsection


@section('subcontent')
<div id="booking-copy">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <form method="post" id="booking-copy-form" autocomplete="off">
        <input type="hidden" name="submit_action" id="submit_action" value="copy">
        <div class="clearfix">
            <div class="form-group field-date">
                <label for="date">{{ trans('admin/bookings.date') }}</label>
                <input type="text" name="date" id="date" value="{{ $booking->date->format('Y-m-d H:i') }}" placeholder="" required class="form-control datepicker">
            </div>
            <div class="form-group field-driver">
                <label for="driver">{{ trans('admin/bookings.driver_name') }}</label>
                <select name="driver" id="driver" class="form-control">
                    <option value="0">{{ trans('admin/bookings.assign_driver') }}</option>
                    @foreach( $drivers as $driver )
                        <option value="{{ $driver->value }}" @if ($booking->driver_id == $driver->value) selected @endif>{{ $driver->text }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group field-vehicle">
                <label for="vehicle">{{ trans('admin/bookings.vehicle_name') }}</label>
                <select name="vehicle" id="vehicle" class="form-control">
                    <option value="0" user_id="0">{{ trans('admin/bookings.assign_vehicle') }}</option>
                    @foreach( $vehicles as $vehicle )
                        <option value="{{ $vehicle->value }}" user_id="{{ $vehicle->user_id }}" @if ($booking->vehicle_id == $vehicle->value) selected @endif>{{ $vehicle->text }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group field-commission">
                <label for="commission">{{ trans('admin/bookings.commission') }}</label>
                <div style="position:relative;">
                    <input type="number" name="commission" id="commission" value="{{ $booking->commission }}" placeholder="0.00" min="0" step="1" class="form-control" onkeyup="var inputGroup = $(this).closest('.form-group').find('button'); if($(this).val()) { inputGroup.removeClass('hidden') } else { inputGroup.addClass('hidden'); }">
                    <button type="button" class="btn btn-link btn-flat @if(!$booking->commission) hidden @endif" onclick="$(this).closest('.form-group').find('input[type=number]').val(''); $(this).closest('.form-group').find('button').addClass('hidden');" title="Clear" style="position:absolute; top:0; right:-40px;">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="form-group field-cash">
                <label for="cash">{{ trans('admin/bookings.cash') }}</label>
                <div style="position:relative;">
                    <input type="number" name="cash" id="cash" value="{{ $booking->cash }}" placeholder="0.00" min="0" step="1" class="form-control" onkeyup="var inputGroup = $(this).closest('.form-group').find('button'); if($(this).val()) { inputGroup.removeClass('hidden') } else { inputGroup.addClass('hidden'); }">
                    <button type="button" class="btn btn-link btn-flat @if(!$booking->cash) hidden @endif" onclick="$(this).closest('.form-group').find('input[type=number]').val(''); $(this).closest('.form-group').find('button').addClass('hidden');" title="Clear" style="position:absolute; top:0; right:-40px;">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="form-group field-status">
                <label for="status">{{ trans('admin/bookings.status') }}</label>
                <select name="status" id="status" class="form-control">
                    @foreach( $statuses as $status )
                        <option value="{{ $status->value }}" style="color:{{ $status->color }};" @if ($booking->status == $status->value) selected @endif>{{ $status->text }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="clearfix">
            <div class="btn-group button-copy-group">
                <button type="submit" class="btn btn-md btn-default button-copy" onclick="$('#booking-copy-form #submit_action').val('copy');">
                    <span>{{ trans('admin/bookings.copy.button.copy') }}</span>
                </button>
            </div>
            <div class="btn-group button-copy-group dropup">
                <button type="button" class="btn btn-md btn-default button-copy-close" onclick="$('#booking-copy-form #submit_action').val('copy_close'); $('#booking-copy-form').submit(); return false;">
                    <span>{{ trans('admin/bookings.copy.button.copy_close') }}</span>
                </button>
                <button type="button" class="btn btn-md btn-default dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li class="button-copy-edit"><a href="#" onclick="$('#booking-copy-form #submit_action').val('copy_edit'); $('#booking-copy-form').submit(); return false;">{{ trans('admin/bookings.copy.button.copy_edit') }}</a></li>
                </ul>
            </div>
        </div>
        <div id="status-message"></div>
    </form>
</div>
@stop


@section('subfooter')
<script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
<script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset_url('plugins','jquery-serializejson/jquery.serializejson.min.js') }}"></script>

<script>
$(document).ready(function(){
    var isReady = 1;
    var form = $('#booking-copy-form');

    form.find('#driver').change(function(e) {
        form.find('#vehicle option').hide();
        form.find('#vehicle option[user_id="'+ $(this).val() +'"]').show();

        var length = form.find('#vehicle option[user_id="'+ $(this).val() +'"]').not('option[value="0"]').length;

        if( length > 1 ) {
            form.find('.field-vehicle').show();
        }
        else {
            form.find('.field-vehicle').hide();
        }

        if( length > 0 ) {
            if( form.find('#vehicle option[user_id="'+ $(this).val() +'"]').is(':selected') ) {
                var val = form.find('#vehicle option[user_id="'+ $(this).val() +'"]:selected').val();
            }
            else {
                var val = form.find('#vehicle option[user_id="'+ $(this).val() +'"]').first().val();
            }
            form.find('#vehicle option[value="0"]').hide();
        }
        else {
            var val = 0;
            form.find('#vehicle option[value="0"]').show();
        }

        form.find('#vehicle').val(val);
    }).change();

    form.submit(function(e) {
        e.preventDefault();
        if( !isReady ) { return false; }

        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: '{{ route('admin.bookings.copy', $booking->id) }}',
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                action: 'save',
                params: form.serializeJSON()
            },
            success: function(response) {
                if( !response.error ) {
                    isReady = 1;

                    form.find('#status-message').html('<span class="text-green"><i class="fa fa-check"></i> {{ trans('admin/bookings.copy.message.copied') }}</span>');

                    setTimeout(function() {
                        form.find('#status-message').html('');
                    }, 5000);

                    switch( form.find('#submit_action').val() ) {
                        case 'copy_close':
                            parent.$('#modal-popup').modal('hide');
                        break;
                        case 'copy_edit':
                            parent.$('#modal-popup').removeClass('modal-booking-copy');
                            window.location.href = response.url +'{{ (request('tmpl') == 'body') ? '?tmpl=body' : '' }}';
                        break;
                        default:
                            // form.trigger('reset');
                            // form.find('#driver').change();
                        break;
                    }
                }
                else {
                    form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ response.error +'</span>');
                }
            },
            error: function(response) {
                form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ trans('admin/bookings.copy.message.error_connection') }}</span>');
            },
            beforeSend: function() {
                isReady = 0;
                form.find('.button-copy').html('<i class="fa fa-spinner fa-spin"></i> {{ trans('admin/bookings.copy.button.copying') }}');
            },
            complete: function() {
                isReady = 1;
                form.find('.button-copy').html('{{ trans('admin/bookings.copy.button.copy') }}');
            }
        });
    });

    // Date picker
    form.find('.datepicker').daterangepicker({
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
</script>
@endsection
