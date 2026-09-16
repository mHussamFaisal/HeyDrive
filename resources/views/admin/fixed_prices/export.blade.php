@extends('admin.index')

@section('title', trans('admin/fixed_prices.subtitle.export'))
@section('subtitle', /*'<i class="fa fa-download"></i> '.*/ trans('admin/fixed_prices.subtitle.export'))


@section('subheader')
<link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">

<style>
#fixed-prices-header {
  margin: 0 0 15px 0;
}
#fixed-prices-export #date_start,
#fixed-prices-export #date_end {
  max-width: 180px;
}
#fixed-prices-export .field-date_start label,
#fixed-prices-export .field-date_end label {
  float: left;
  min-width: 100px;
  padding-top: 6px;
  font-weight: normal;
}
#fixed-prices-export .selection-container {
  margin-bottom: 30px;
}
#fixed-prices-export .section-heading {
  margin: 0 0 10px 0;
}
#fixed-prices-export .button-save {
  float: left;
  margin-right: 10px;
}
#fixed-prices-export .button-save i {
  margin-right: 5px;
}
#fixed-prices-export #status-message {
  margin-top: 6px;
}
</style>
@endsection


@section('subcontent')
<div id="fixed-prices-export">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <h3 id="fixed-prices-header">{{ trans('admin/fixed_prices.subtitle.export') }}</h3>

    <form method="post" action="{{ route('admin.fixed-prices.export', ['action' => 'save']) }}" id="fixed-prices-export-form" autocomplete="off">
        {{ csrf_field() }}

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
                <i class="fa fa-download"></i> <span>{{ trans('admin/fixed_prices.button.export') }}</span>
            </button>
            <div id="status-message"></div>
        </div>
    </form>

</div>
@endsection


@section('subfooter')
<script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
<script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset_url('plugins','jquery-serializejson/jquery.serializejson.min.js') }}"></script>

<script>
$(document).ready(function() {
    var isReady = 1;
    var form = $('#fixed-prices-export-form');

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

    /*
    form.submit(function(e) {
        e.preventDefault();

        if( !isReady ) {
            return false;
        }

        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: '{{ route('admin.fixed-prices.export', ['action' => 'save']) }}',
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: form.serializeJSON(),
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
                form.find('.button-save').html('<i class="fa fa-spinner fa-spin"></i> {{ trans('admin/fixed_prices.button.exporting') }}');
            },
            complete: function() {
                isReady = 1;
                form.find('.button-save').html('<i class="fa fa-download"></i> {{ trans('admin/fixed_prices.button.export') }}');
            }
        });
    });
    */
});
</script>
@endsection
