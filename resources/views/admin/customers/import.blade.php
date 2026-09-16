@extends('admin.index')

@section('title', trans('admin/users.subtitle.import'))
@section('subtitle', /*'<i class="fa fa-upload"></i> '.*/ trans('admin/users.subtitle.import'))


@section('subheader')
<style>
#customers-header {
  margin: 0 0 15px 0;
}
#customers-import #file {
  max-width: 260px;
}
#customers-import #delimiter {
  max-width: 180px;
}
#customers-import .field-file label,
#customers-import .field-delimiter labell {
  float: left;
  min-width: 100px;
  padding-top: 6px;
  font-weight: normal;
}
#customers-import .selection-container {
  margin-bottom: 30px;
  display: none;
}
#customers-import .section-heading {
  margin: 0 0 10px 0;
}
#customers-import .button-save {
  float: left;
  margin-right: 10px;
}
#customers-import .button-save i {
  margin-right: 5px;
}
#customers-import #status-message {
  margin-top: 6px;
}
</style>
@endsection


@section('subcontent')
<div id="customers-import">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <h3 id="customers-header">{{ trans('admin/users.subtitle.import') }}</h3>

    <form method="post" action="{{ route('admin.customers.import', ['action' => 'save']) }}" enctype="multipart/form-data" id="customers-import-form" autocomplete="off">
        {{ csrf_field() }}

        <div style="margin-bottom:20px;">
            <a href="{{ route('admin.customers.import', ['action' => 'download']) }}" target="_blank">Download Standard Template</a><br>
        </div>

        <div class="form-group field-file">
            <label for="file">{{ trans('admin/fixed_prices.file') }}</label>
            <input type="file" name="file" id="file" class="form-control" required>
        </div>

        <div class="selection-container">
            <div class="form-group field-delimiter">
                <label for="delimiter">{{ trans('admin/fixed_prices.delimiter') }}</label>
                <input type="text" name="delimiter" id="delimiter" placeholder=";" class="form-control">
            </div>
        </div>

        <div class="clearfix selection-container">
            <button type="submit" class="btn btn-md btn-success button-save">
                <i class="fa fa-upload"></i> <span>{{ trans('admin/users.button.import') }}</span>
            </button>
            <div id="status-message"></div>
        </div>
    </form>

</div>
@endsection


@section('subfooter')
<script>
$(document).ready(function() {
    var isReady = 1;
    var form = $('#customers-import-form');

    form.find('#file').change(function() {
        if( $(this).val() ) {
            form.find('.selection-container').show();
        }
        else {
            form.find('.selection-container').hide();
        }
    }).change();

    form.submit(function(e) {
        e.preventDefault();

        if( !isReady ) {
            return false;
        }

        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: '{{ route('admin.customers.import', ['action' => 'save']) }}',
            type: 'POST',
            dataType: 'json',
            cache: false,
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
                form.find('.button-save').html('<i class="fa fa-spinner fa-spin"></i> {{ trans('admin/users.button.importing') }}');
            },
            complete: function() {
                isReady = 1;
                form.find('.button-save').html('<i class="fa fa-upload"></i> {{ trans('admin/users.button.import') }}');
            }
        });
    });
});
</script>
@endsection
