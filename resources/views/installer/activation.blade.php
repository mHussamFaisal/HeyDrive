@extends('layouts.app')

@section('title', trans('subscription.page_title'))

@section('content')
    <div id="license">
        @include('partials.alerts.errors')
        <h3>{{ trans('installer.attention') }}</h3>
        <p>{{ trans('installer.license_activation') }}</p>
        <form class="eto-license-deactivation" method="POST" action="{{ route('etoActivation') }}">
            <h4 class="card-inside-title">{{ trans('subscription.enterLicense') }}</h4>
            {{ csrf_field() }}
            <div class="form-group">
                <div class="form-line">
                    <input type="text" name="license" class="form-control" placeholder="xxxx-xxxx-xxxx-xxxx">
                </div>
            </div>
            <button type="submit" class="btn btn-success waves-effect btn-block">{{ trans('installer.btn_activation') }}</button>
        </form>
    </div>
@stop

@section('footer')
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
    </style>
@stop
