@extends('admin.index')

@section('title', trans('admin/pages.license.page_title'))
@section('subtitle', /*'<i class="fa fa-file-text-o"></i> '.*/ trans('admin/pages.license.page_title'))

@section('subcontent')
    {!! $license !!}
@stop
