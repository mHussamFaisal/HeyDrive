@extends('admin.index')

@section('title', trans('admin/services.page_title') .' / '. trans('admin/services.subtitle.edit'))
@section('subtitle', /*'<i class="fa fa-sliders"></i> '*/ '<a href="'. route('admin.services.index') .'">'. trans('admin/services.page_title') .'</a> /  <a href="'. route('admin.services.show', $service->id) .'">'. $service->getName() .'</a> / '. trans('admin/services.subtitle.edit'))

@section('subcontent')
  @include('partials.alerts.success')
  @include('partials.alerts.errors')

  <div id="services">
    <div class="row">
      <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
        {!! Form::model($service, ['method' => 'patch', 'route' => ['admin.services.update', $service->id], 'class' => 'form-master']) !!}
          @include('admin.services.form', ['mode' => 'edit'])
        {!! Form::close() !!}
      </div>
    </div>
  </div>
@stop
