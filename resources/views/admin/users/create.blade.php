@extends('admin.index')

@section('title', trans('admin/users.page_title') .' / '. trans('admin/users.subtitle.create'))
@section('subtitle', /*'<i class="fa fa-users"></i> '*/ '<a href="'. route('admin.users.index') .'">'. trans('admin/users.page_title') .'</a> / '. trans('admin/users.subtitle.create'))

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="row">
        <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
            {!! Form::open(['method' => 'post', 'route' => 'admin.users.store', 'files' => true, 'class' => 'form-master']) !!}
                @include('admin.users.form', ['mode' => 'create'])
            {!! Form::close() !!}
        </div>
    </div>
@stop
