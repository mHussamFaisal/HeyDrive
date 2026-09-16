@extends('admin.index')

@section('title', trans('admin/services.page_title') .' / '. trans('admin/services.subtitle.create'))
@section('subtitle', /*'<i class="fa fa-sliders"></i> '*/ '<a href="'. route('admin.services.index') .'">'. trans('admin/services.page_title') .'</a> / '. trans('admin/services.subtitle.create'))

@section('subcontent')
  @include('partials.alerts.success')
  @include('partials.alerts.errors')

  <div id="services">
    <div class="row">
      <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
        {!! Form::open(['method' => 'post', 'route' => 'admin.services.store', 'class' => 'form-master']) !!}
          @include('admin.services.form', ['mode' => 'create'])
        {!! Form::close() !!}
      </div>
    </div>
  </div>
@stop
