@extends('driver.index')

@section('title', trans('driver/settings.page_title'))
@section('subtitle', /*'<i class="fa fa-cogs"></i> '*/ '<a href="'. route('driver.settings.index') .'">'. trans('driver/settings.page_title') .'</a>')

@section('subheader')

@stop

@section('subcontent')
<div id="settings" style="position:relative;">
    @include('partials.loader')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    Comming soon...
</div>
@stop
