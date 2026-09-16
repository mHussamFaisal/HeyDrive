@extends('driver.index')

@section('title', trans('driver/jobs.page_title') .' / '. trans('driver/jobs.subtitle.edit'))
@section('subtitle', /*'<i class="fa fa-tasks"></i> '*/ '<a href="'. route('driver.jobs.index') .'">'. trans('driver/jobs.page_title') .'</a> / '. trans('driver/jobs.subtitle.edit'))

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            {!! Form::model($job, ['method' => 'patch', 'route' => ['driver.jobs.update', $job->id], 'files' => false, 'class' => 'form-master']) !!}
                @include('driver.jobs.form', ['mode' => 'edit'])
            {!! Form::close() !!}
        </div>
    </div>
@stop
