@extends('admin.index')

@section('title', trans('admin/users.page_title') .' / '. trans('admin/users.subtitle.edit'))
@section('subtitle', /*'<i class="fa fa-users"></i> '*/ '<a href="'. route('admin.users.index') .'">'. trans('admin/users.page_title') .'</a> / '. trans('admin/users.subtitle.edit') )

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="row">
        <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
            {!! Form::model($user, ['method' => 'patch', 'route' => ['admin.users.update', $user->id], 'files' => true, 'class' => 'form-master']) !!}
                @include('admin.users.form', ['mode' => 'edit'])
            {!! Form::close() !!}
        </div>
    </div>
@stop
