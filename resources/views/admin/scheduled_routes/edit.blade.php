@extends('admin.index')

@section('title', trans('admin/scheduled_routes.page_title') .' / '. trans('admin/scheduled_routes.subtitle.edit'))
@section('subtitle', /*'<i class="fa fa-compass"></i> '*/ '<a href="'. route('admin.scheduled-routes.index') .'">'. trans('admin/scheduled_routes.page_title') .'</a> / <a href="'. route('admin.scheduled-routes.show', $scheduledRoute->id) .'">'. $scheduledRoute->getName() .'</a> / '. trans('admin/scheduled_routes.subtitle.edit'))

@section('subcontent')
  @include('partials.alerts.success')
  @include('partials.alerts.errors')

  <div id="scheduled-routes">
    <div class="row">
      <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
        {!! Form::model($scheduledRoute, ['method' => 'patch', 'route' => ['admin.scheduled-routes.update', $scheduledRoute->id], 'class' => 'form-master']) !!}
          @include('admin.scheduled_routes.form', ['mode' => 'edit'])
        {!! Form::close() !!}
      </div>
    </div>
  </div>
@stop
