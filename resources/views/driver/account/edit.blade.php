@extends('driver.index')

@section('title', trans('driver/account.page_title') .' / '. trans('driver/account.subtitle.edit'))
@section('subtitle', /*'<i class="fa fa-user"></i> '*/ '<a href="'. route('driver.account.index') .'">'. trans('driver/account.page_title') .'</a> / '. trans('driver/account.subtitle.edit'))

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="row">
        <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
            {!! Form::model($user, ['method' => 'patch', 'route' => ['driver.account.update'], 'files' => true, 'class' => 'form-master']) !!}
                @include('driver.account.form', ['mode' => 'edit'])
            {!! Form::close() !!}
        </div>
    </div>
@stop
