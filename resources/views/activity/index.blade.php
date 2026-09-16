@extends('admin.index')

@section('title', trans('activity.page_title'))
@section('subtitle', /*'<i class="fa fa-shield"></i> '.*/ trans('activity.page_title'))

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('css','eto.css') }}?_dc={{ config('app.timestamp') }}">
@stop

@section('subcontent')
    <div class="box-header eto-box-header @if(request()->subject) hidden @endif">
        <h4 class="box-title">{{ trans('activity.page_title') }}</h4>

        <div class="box-tools pull-right">
            <div class="btn-group">
                <button class="eto-type-view btn btn-sm btn-success" type="button" data-eto-type-view="table">{{ trans('activity.buttons.table_view') }}</button>
                <button class="eto-type-view btn btn-sm btn-default" type="button" data-eto-type-view="timeline">{{ trans('activity.buttons.timeline_view') }}</button>
            </div>
            <div class="eto-field eto-field-search">
                <div class="eto-field-value clearfix">
                    <input id="search" class="eto-js-inputs eto-js-search" data-eto-name="search" value="" placeholder=" {{ trans('activity.form.search') }}" type="text">
                </div>
                <div class="eto-field-placeholder hidden">{{ trans('activity.form.search') }}</div>
            </div>
        </div>
    </div>

    <div class="eto-table-view">
        <table class="table table-condensed">
            <tbody></tbody>
        </table>
    </div>
    <div class="eto-timeline-view">
        <ul class="timeline"></ul>
    </div>
    <div class="clearfix" style="margin-left: 4px;margin-top:-18px">
        <button type="button" class="eto-load-more btn btn-info hidden">
            <i class="fa fa-clock-o"></i>
            {{ trans('common.button.load_more') }}
        </button>
    </div>
@stop

@section('subfooter')
    <script src="{{ asset_url('plugins','moment/moment.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>

    @include('layouts.eto-js')
    <script src="{{ asset_url('js','eto/eto-timeline.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script>
    $(function() {
        ETO.Timeline.init({
            uriSearch: 'activity/search',
            no_data_message: '{{ trans('activity.no_data') }}',
            uriAnhor: false,
            containerTimeline: $('.eto-timeline-view'),
            containerTable: $('.eto-table-view'),
            typeView: $('.eto-type-view.btn-success').data('etoTypeView') ? $('.eto-type-view.btn-success').data('etoTypeView') : 'table',
        });
    });
    </script>
@stop
