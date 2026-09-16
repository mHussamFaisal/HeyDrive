@extends('admin.index')

@section('title', trans('teams.page_title') .' / '. trans('teams.subtitle.create'))
@section('subtitle', '<a href="'. route('teams.index') .'">'. trans('teams.page_title') .'</a> / '. trans('teams.subtitle.create'))

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            {!! Form::open(['method' => 'post', 'route' => 'teams.store', 'files' => true, 'class' => 'form-master']) !!}
                @include('teams.form', ['mode' => 'create'])
            {!! Form::close() !!}
        </div>
    </div>
@stop
