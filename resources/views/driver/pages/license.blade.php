@extends('driver.index')

@section('title', trans('driver/pages.license.page_title'))
@section('subtitle', /*'<i class="fa fa-file-text-o"></i> '.*/ trans('driver/pages.license.page_title'))

@section('subcontent')
    {!! $license !!}
@stop
