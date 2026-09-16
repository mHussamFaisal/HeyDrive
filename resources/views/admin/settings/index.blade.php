@extends('admin.index')

@section('title', trans('admin/settings.page_title'))
@section('subtitle', /*'<i class="fa fa-cogs"></i> '.*/ trans('admin/settings.page_title'))


@section('subheader')

@endsection


@section('subcontent')
<div id="settings">
    <h3 id="settings-header">{{ trans('admin/settings.page_title') }}</h3>
    No settings here.
</div>
@endsection


@section('subfooter')

@endsection
