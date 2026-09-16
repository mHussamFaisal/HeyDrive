@extends('admin.index')

@section('title', trans('admin/zones.page_title') .' / '. trans('admin/zones.subtitle.edit'))
@section('subtitle', /*'<i class="fa fa-map-marker"></i> '*/ '<a href="'. route('admin.zones.index') .'">'. trans('admin/zones.page_title') .'</a> / '. trans('admin/zones.subtitle.edit') )

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div id="zones">
        <div class="row">
            <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
                {!! Form::model($zone, ['method' => 'patch', 'route' => ['admin.zones.update', $zone->id], 'files' => true, 'class' => 'form-master']) !!}
                    @include('admin.zones.form', ['mode' => 'edit'])
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop
