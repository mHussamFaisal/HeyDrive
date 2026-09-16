@extends('admin.index')

@section('title', trans('admin/pages.getting_started.page_title'))
@section('subtitle', /*'<i class="fa fa-mouse-pointer"></i> '.*/ trans('admin/pages.getting_started.page_title'))

@section('subcontent')
<div id="getting_started">

    <h3 style="margin-top:0px; margin-bottom:15px;">{{ trans('admin/pages.getting_started.page_title') }}</h3>

    @include('partials.alerts.success', ['close' => false])
    @include('partials.alerts.errors')

    Tech Soft System<br><br>

    Here's some useful information on how to test the software.<br><br>
    <ol>
        <li>Add a test booking via <a href="{{ route('dispatch.index') }}">Dispatch</a> tab.</li>
        <li>Test <a href="{{ route('booking.widget') }}" target="_blank">Web Booking</a> to see how customer booking journey looks like.</li>
        <li>Dispatch job to a driver.</li>
        <li>Test how to setup and use Driver App, <a href="{{ route('admin.getting-started') }}?action=create_driver">create driver account</a>. Read step by step guide </li>
        <!--<a href="{{ route('admin.mobile-app') }}">Settings -> Mobile Apps</a>.-->
        <li>Add company details and <a href="{{ route('admin.settings.notifications') }}">preview</a> all ongoing communication between customer, driver and operator. Once set, all three (email, sms, push) ways of communication are fully automatic.</li>
        <li><a href="{{ route('admin.getting-started') }}?action=create_customer">Create customer account</a> and learn what tools are available for your customer.</li>
        <li>Set your own pricing in <a href="{{ route('admin.config.mileage-time') }}">Settings -> Pricing</a>.</li>
    </ol>

    <hr style="margin-top:50px; margin-bottom:15px;">

    <div class="form-group field-admin_default_page clearfix">
      <label for="admin_default_page" style="float: left; font-weight: normal; margin-top: 3px; margin-right: 10px;">Which page would you like to see after login?</label>
      <select name="admin_default_page" id="admin_default_page" class="form-control" style="float:left; width: 220px; border: 1px #ececec solid; border-width: 0 0 1px 0; background: #f9f9f9; height: 28px; padding: 4px 12px;">
         <option value="getting-started" @if(config('site.admin_default_page') == 'getting-started') selected="selected" @endif>Getting Started</option>
         <option value="dispatch" @if(config('site.admin_default_page') == 'dispatch') selected="selected" @endif>Dispatch</option>
         <option value="bookings-next24" @if(config('site.admin_default_page') == 'bookings-next24') selected="selected" @endif>Bookings -> Next 24</option>
         <option value="bookings-latest" @if(config('site.admin_default_page') == 'bookings-latest') selected="selected" @endif>Bookings -> Latest</option>
         <option value="bookings-unconfirmed" @if(config('site.admin_default_page') == 'bookings-unconfirmed') selected="selected" @endif>Bookings -> Unconfirmed</option>
      </select>
      <span id="status-message" style="float:left; margin-top: 3px; margin-left:10px;"></span>
    </div>

    <script>
    $(document).ready(function() {
        var isReady = 1;

        $('#admin_default_page').change(function(){
            if( isReady ) {
                $.ajax({
                    headers : {
                        'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                    },
                    url: '{{ route('admin.getting-started', ['action' => 'update']) }}',
                    type: 'GET',
                    dataType: 'json',
                    cache: false,
                    data: {
                        admin_default_page: $(this).val()
                    },
                    success: function(response) {
                        if( response.errors ) {
                            var errors = '';
                            $.each(response.errors, function(index, error) {
                                errors += (errors ? ', ' : '') + error;
                            });
                            $('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ errors +'</span>');
                        }
                        else {
                            isReady = 1;
                            $('#status-message').html('<span class="text-green"><i class="fa fa-check"></i> {{ trans('admin/settings.message.saved') }}</span>');
                            setTimeout(function() {
                                $('#status-message').html('');
                            }, 5000);
                        }
                    },
                    error: function(response) {
                        $('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ trans('admin/settings.message.connection_error') }}</span>');
                    },
                    beforeSend: function() {
                        isReady = 0;
                    },
                    complete: function() {
                        isReady = 1;
                    }
                });
            }
        });
    });
    </script>

</div>
@stop
