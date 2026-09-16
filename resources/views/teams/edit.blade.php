@extends('admin.index')

@section('title', trans('teams.page_title') .' / '. trans('teams.subtitle.edit'))
@section('subtitle', '<a href="'. route('teams.index') .'">'. trans('teams.page_title') .'</a> / '. trans('teams.subtitle.edit'))

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            {!! Form::model($team, ['method' => 'patch', 'route' => ['teams.update', $team->id], 'files' => true, 'class' => 'form-master']) !!}
                @include('teams.form', ['mode' => 'edit'])
            {!! Form::close() !!}
        </div>
    </div>
@stop
