@extends('admin.index')

@section('title', trans('admin/fixed_prices.subtitle.import'))
@section('subtitle', /*'<i class="fa fa-upload"></i> '.*/ trans('admin/fixed_prices.subtitle.import'))


@section('subheader')
<link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">

<style>
#fixed-prices-header {
  margin: 0 0 15px 0;
}
#fixed-prices-import #file {
  max-width: 260px;
}
#fixed-prices-import #direction,
#fixed-prices-import #import_type,
#fixed-prices-import #delimiter,
#fixed-prices-import #status,
#fixed-prices-import #date_start,
#fixed-prices-import #date_end {
  max-width: 180px;
}
#fixed-prices-import .field-file label,
#fixed-prices-import .field-direction label,
#fixed-prices-import .field-import_type label,
#fixed-prices-import .field-delimiter label,
#fixed-prices-import .field-status label,
#fixed-prices-import .field-date_start label,
#fixed-prices-import .field-date_end label {
  float: left;
  min-width: 100px;
  padding-top: 6px;
  font-weight: normal;
}
#fixed-prices-import .vehicles_table td {
  border: 0;
  padding: 2px 0;
}
#fixed-prices-import .vehicles_table select,
#fixed-prices-import .vehicles_table input[type="number"] {
  width: 85px;
  margin-right: 10px;
}
#fixed-prices-import .selection-container {
  margin-bottom: 30px;
  display: none;
}
#fixed-prices-import .section-heading {
  margin: 0 0 10px 0;
}
#fixed-prices-import .button-save {
  float: left;
  margin-right: 10px;
}
#fixed-prices-import .button-save i {
  margin-right: 5px;
}
#fixed-prices-import #status-message {
  margin-top: 6px;
}
</style>
@endsection


@section('subcontent')
<div id="fixed-prices-import">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <h3 id="fixed-prices-header">{{ trans('admin/fixed_prices.subtitle.import') }}</h3>

    <form method="post" action="{{ route('admin.fixed-prices.import', ['action' => 'save']) }}" enctype="multipart/form-data" id="fixed-prices-import-form" autocomplete="off">
        {{ csrf_field() }}

        <div style="margin-bottom:20px;">
            <a href="{{ route('admin.fixed-prices.import', ['action' => 'download', 'tmpl' => 'cross']) }}" target="_blank">Download Cross Template</a><br>
            <a href="{{ route('admin.fixed-prices.import', ['action' => 'download', 'tmpl' => 'standard']) }}" target="_blank">Download Standard Template</a><br>
            <a href="{{ route('admin.fixed-prices.import', ['action' => 'download', 'tmpl' => 'standard-vehicles']) }}" target="_blank">Download Standard Template with Vehicles</a><br><br>
            <a href="{{ route('admin.fixed-prices.import', ['action' => 'clear']) }}" style="color:red;" onclick="return confirm('Are you sure you want to delete?');">Delete all DATA</a>
        </div>

        <div class="form-group field-file">
            <label for="file">{{ trans('admin/fixed_prices.file') }}</label>
            <input type="file" name="file" id="file" class="form-control" required>
        </div>

        <div class="selection-container">

            <div class="form-group field-import_type">
                <label for="import_type">{{ trans('admin/fixed_prices.import_type') }}</label>
                <select name="import_type" id="import_type" class="form-control">
                    <option value="standard" selected>{{ trans('admin/fixed_prices.import_type_standard') }}</option>
                    <option value="cross">{{ trans('admin/fixed_prices.import_type_cross') }}</option>
                </select>
            </div>

            <div class="form-group field-delimiter">
                <label for="delimiter">{{ trans('admin/fixed_prices.delimiter') }}</label>
                <input type="text" name="delimiter" id="delimiter" placeholder=";" class="form-control">
            </div>

            <div class="form-group field-status">
                <label for="status">{{ trans('admin/fixed_prices.status') }}</label>
                <select name="status" id="status" class="form-control">
                    <option value="active" selected>{{ trans('admin/fixed_prices.status_active') }}</option>
                    <option value="inactive">{{ trans('admin/fixed_prices.status_inactive') }}</option>
                </select>
            </div>

            <div class="form-group field-direction">
                <label for="direction">{{ trans('admin/fixed_prices.direction') }}</label>
                <select name="direction" id="direction" class="form-control">
                    <option value="0">{{ trans('admin/fixed_prices.direction_option1') }}</option>
                    <option value="1">{{ trans('admin/fixed_prices.direction_option2') }}</option>
                </select>
            </div>
        </div>

        @if( $vehicles )
        <div class="selection-container">
            <h4 class="section-heading">{{ trans('admin/fixed_prices.vehicle_heading') }}</h4>
            <table class="table table-condensed vehicles_table" style="width:auto;">
                <thead>
                    <tr>
                        <td style="min-width:100px;">
                            {{ trans('admin/fixed_prices.vehicle_name') }}
                        </td>
                        <td>
                            {{ trans('admin/fixed_prices.vehicle_price') }}
                        </td>
                        <td>
                            {{ trans('admin/fixed_prices.vehicle_deposit') }}
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>

                        </td>
                        <td>
                            <select name="factor_type" id="factor_type" class="form-control">
                                <option value="0">Multiply</option>
                                <option value="1" selected>Add</option>
                                <option value="2">Override</option>
                            </select>
                        </td>
                        <td>

                        </td>
                    </tr>

                @foreach( $vehicles as $vehicle )
                    <tr>
                        <td style="vertical-align:middle;">
                            <label for="vehicle_{{ $vehicle->id }}_price" style="font-weight:normal;">{{ $vehicle->name }}</label>
                        </td>
                        <td>
                            <input type="number" name="vehicles[{{ $vehicle->id }}][price]" id="vehicle_{{ $vehicle->id }}_price" value="0" min="0" step="0.01" class="form-control">
                        </td>
                        <td>
                            <input type="number" name="vehicles[{{ $vehicle->id }}][deposit]" id="vehicle_{{ $vehicle->id }}_deposit" value="0" min="0" step="0.01" class="form-control">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if( $services )
        <div class="selection-container">
            <h4 class="section-heading">{{ trans('admin/fixed_prices.service_heading') }}</h4>
            @foreach( $services as $service )
                <div class="form-group field-service field-service_{{ $service->id }}" style="margin:0 0 5px 0;">
                    <label for="service_{{ $service->id }}" class="checkbox-inline">
                        <input type="checkbox" name="services[]" id="service_{{ $service->id }}" value="{{ $service->id }}" /> <span>{{ $service->name }}</span>
                    </label>
                </div>
            @endforeach
        </div>
        @endif

        <div class="selection-container">
            <h4 class="section-heading">{{ trans('admin/fixed_prices.date_heading') }}</h4>
            <div class="form-group field-date_start">
                <label for="date_start">{{ trans('admin/fixed_prices.date_start') }}</label>
                <input type="text" name="date_start" id="date_start" placeholder="{{ trans('admin/fixed_prices.date_restriction') }}" class="form-control datepicker">
            </div>
            <div class="form-group field-date_end">
                <label for="date_end">{{ trans('admin/fixed_prices.date_end') }}</label>
                <input type="text" name="date_end" id="date_end" placeholder="{{ trans('admin/fixed_prices.date_restriction') }}" class="form-control datepicker">
            </div>
        </div>

        <div class="clearfix selection-container">
            <button type="submit" class="btn btn-md btn-success button-save">
                <i class="fa fa-upload"></i> <span>{{ trans('admin/fixed_prices.button.import') }}</span>
            </button>
            <div id="status-message"></div>
        </div>
    </form>

</div>
@endsection


@section('subfooter')
<script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
<script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
{{-- <script src="{{ asset_url('plugins','jquery-serializejson/jquery.serializejson.min.js') }}"></script> --}}

<script>
$(document).ready(function() {
    var isReady = 1;
    var form = $('#fixed-prices-import-form');

    form.find('#file').change(function() {
        if( $(this).val() ) {
            form.find('.selection-container').show();
        }
        else {
            form.find('.selection-container').hide();
        }
    }).change();

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
        e.preventDefault();

        if( !isReady ) {
            return false;
        }

        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: '{{ route('admin.fixed-prices.import', ['action' => 'save']) }}',
            type: 'POST',
            dataType: 'json',
            cache: false,
            // data: form.serializeJSON(),
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if( response.errors ) {
                    var errors = '';
                    $.each(response.errors, function(index, error) {
                        errors += (errors ? ', ' : '') + error;
                    });
                    form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ errors +'</span>');
                }
                else {
                    isReady = 1;
                    form.find('#status-message').html('<span class="text-green"><i class="fa fa-check"></i> '+ response.message +'</span>');
                    setTimeout(function() {
                        form.find('#status-message').html('');
                    }, 5000);
                }
            },
            error: function(response) {
                form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ trans('admin/fixed_prices.message.connection_error') }}</span>');
            },
            beforeSend: function() {
                isReady = 0;
                form.find('.button-save').html('<i class="fa fa-spinner fa-spin"></i> {{ trans('admin/fixed_prices.button.importing') }}');
            },
            complete: function() {
                isReady = 1;
                form.find('.button-save').html('<i class="fa fa-upload"></i> {{ trans('admin/fixed_prices.button.import') }}');
            }
        });
    });
});
</script>
@endsection
