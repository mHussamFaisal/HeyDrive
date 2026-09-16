@extends('admin.index')

@section('title', trans('admin/feedback.page_title') .' / '. trans('admin/feedback.subtitle.edit'))
@section('subtitle', /*'<i class="fa fa-comments-o"></i> '*/ '<a href="'. route('admin.feedback.index', request('type') ? ['type' => request('type')] : []) .'">'. trans('admin/feedback.page_title') .'</a> /  <a href="'. route('admin.feedback.show', $feedback->id) .'">'. $feedback->getName() .'</a> / '. trans('admin/feedback.subtitle.edit'))

@section('subcontent')
  @include('partials.alerts.success')
  @include('partials.alerts.errors')

  <div id="feedback">
    <div class="row">
      <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
        {!! Form::model($feedback, ['method' => 'patch', 'route' => ['admin.feedback.update', $feedback->id], 'class' => 'form-master', 'enctype' => 'multipart/form-data']) !!}
          @include('admin.feedback.form', ['mode' => 'edit'])
        {!! Form::close() !!}
      </div>
    </div>
  </div>
@stop
