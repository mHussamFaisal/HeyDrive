@extends('driver.index')

@section('title', trans('driver/calendar.page_title') .' / '. trans('driver/calendar.subtitle.create'))
@section('subtitle', /*'<i class="fa fa-calendar"></i> '*/ '<a href="'. route('driver.calendar.index') .'">'. trans('driver/calendar.page_title') .'</a> / '. trans('driver/calendar.subtitle.create') )

@section('subcontent')
<div id="calendar">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            {!! Form::open(['method' => 'post', 'route' => 'driver.calendar.store', 'files' => false, 'class' => 'form-master']) !!}
                @include('driver.calendar.form', ['mode' => 'create'])
            {!! Form::close() !!}
        </div>
    </div>
</div>
@stop
