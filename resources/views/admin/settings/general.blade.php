@extends('admin.index')

@section('title', trans('admin/settings.general.subtitle'))
@section('subtitle', /*'<i class="fa fa-cogs"></i> '.*/ trans('admin/settings.general.subtitle'))


@section('subcontent')
<div id="settings-general">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    {{-- <h3 id="settings-header">{{ trans('admin/settings.general.subtitle') }}</h3> --}}

    <form method="post" action="{{ route('admin.settings.general', ['action' => 'save']) }}" enctype="multipart/form-data" id="general-form" autocomplete="off">
        {{ csrf_field() }}

        <div class="form-group field-logo">
            <label for="logo">{{ trans('admin/settings.general.logo') }}</label>
            <div id="logo-preview" @if (!$settings->logo) style="display:none;" @endif>
                @if( $settings->logo )
                    <img src="{{ asset_url('uploads','logo/'. $settings->logo) }}" class="logo-preview" />
                @endif
                <label for="logo_delete" style="margin-top:10px; margin-bottom:10px; font-weight:normal;">
                    <input type="checkbox" name="logo_delete" id="logo_delete" value="1" style="float:left; margin-right:5px;" /> <span>{{ trans('admin/settings.general.logo_delete') }}</span>
                </label>
            </div>
            <input type="file" name="logo" id="logo" class="form-control">
        </div>
        <div class="clearfix">
            <button type="submit" class="btn btn-md btn-success button-save">
                <i class="fa fa-save"></i> <span>{{ trans('admin/settings.button.save') }}</span>
            </button>
            <div id="status-message"></div>
        </div>
    </form>

</div>
@endsection


@section('subfooter')
<script src="{{ asset_url('plugins','jquery-serializejson/jquery.serializejson.min.js') }}"></script>

<script>
$(document).ready(function() {
    var isReady = 1;
    var form = $('#general-form');

    form.submit(function(e) {
        e.preventDefault();

        if( !isReady ) {
            return false;
        }

        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: '{{ route('admin.settings.general', ['action' => 'save']) }}',
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
                    if( typeof response.logo !== 'undefined' ) {
                        if( response.logo ) {
                            $('#logo-preview .logo-preview').remove();
                            $('#logo-preview').prepend('<img src="{{ asset_url('uploads','logo/') }}/'+ response.logo +'" class="logo-preview" />');
                            $('#logo-preview').show();
                        }
                        else {
                            $('#logo-preview').hide();
                        }
                    }

                    form.find('#logo').val('');

                    isReady = 1;
                    form.find('#status-message').html('<span class="text-green"><i class="fa fa-check"></i> {{ trans('admin/settings.message.saved') }}</span>');
                    setTimeout(function() {
                        form.find('#status-message').html('');
                    }, 5000);
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
    });
});

</script>
@endsection
