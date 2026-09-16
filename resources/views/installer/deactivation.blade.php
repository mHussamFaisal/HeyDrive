@extends('layouts.app')

@section('title', trans('subscription.page_title'))

@section('content')
    <div id="license">
        @include('partials.alerts.errors')
        <h3>{{ trans('installer.attention') }}</h3>
        <p>{{ trans('installer.infoDeacivation') }}</p>
        <p>{{ trans('installer.infoDeacivationlicense') }}</p>
        <form class="eto-license-deactivation" method="POST" action="{{ route('etoDeactivation') }}">
            <h4 class="card-inside-title">{{ trans('subscription.enterLicense') }}</h4>
            {{ csrf_field() }}
            <div class="form-group">
                <div class="form-line">
                    <input type="text" name="license" class="form-control" placeholder="xxxx-xxxx-xxxx-xxxx">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg waves-effect btn-block">{{ trans('subscription.button.disableLicense') }}</button>
        </form>
    </div>
@stop

@section('footer')
    <script src="{{ asset_url('plugins','jquery-loading-overlay/loadingoverlay.min.js') }}"></script>
    <script src="{{ asset_url('plugins','sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset_url('js','eto/eto.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <style>
    #license {
        text-align: center;
        max-width: 500px;
        background: #f2f2f2;
        margin: 40px auto;
        padding: 20px;
    }
    #license h3 {
        margin: 0 0 20px 0;
    }
    #license form {
        margin: 20px auto 0 auto;
        width: 400px;
        max-width: 100%;
    }
    .swal2-actions .swal2-cancel {
        margin-right: 10px;
    }
    </style>

    <script>
    $(document).ready(function(){
        var deactivate = false;
        if (ETO.model === false) {
            ETO.init({ config: ['page', 'icons', 'config_site'], lang: ['user'] }, 'update');
        }
        $('body').on('submit', '.eto-license-deactivation', function(e) {
            if (deactivate === false) {
                var form = $(this);
                e.preventDefault();
                ETO.swalWithBootstrapButtons({
                    type: 'info',
                    html: '<h3>{{ trans('subscription.message.disableLicense') }}</h3>',
                    showCancelButton: true,
                    confirmButtonText: '{{ trans('subscription.button.disableLicense') }}',
                    showLoaderOnConfirm: true,
                })
                .then(function (result) {
                    if (result.value) {
                        deactivate = true;
                        form.submit();
                    }
                });
            }
        });
    });
    </script>
@stop
