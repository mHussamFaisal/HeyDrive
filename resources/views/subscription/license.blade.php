@extends('layouts.app')

@section('title', trans('subscription.page_title'))

@section('content')
    <div id="license">
        <form class="eto-add-repo" method="POST" action="{{ route('etoInstall.setLicense') }}?reload_invalid_license=0">
            <h2 class="card-inside-title">{{ trans('subscription.enterLicense') }}</h2>
            {{ csrf_field() }}
            <div class="form-group">
                <div class="form-line">
                    <input type="text" name="license" class="form-control" placeholder="xxxx-xxxx-xxxx-xxxx">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg waves-effect btn-block">{{ trans('subscription.validate') }}</button>
        </form>
    </div>
@stop

@section('footer')
    <style>
    #license h1 {
        text-align: center;
    }
    #license form {
        margin: 0 auto;
        width: 400px;
        max-width: 100%;
    }
    </style>
@stop
