@extends('admin.index')

@section('title', trans('news.page_title'))
@section('subtitle', /*'<i class="fa fa-arrow-circle-up"></i> '.*/ trans('news.page_title') )

@section('subcontent')
    <div class="box-header eto-box-header">
        <h4 class="box-title">
            {{ trans('news.page_title') }}
        </h4>

        <div class="box-tools pull-right">
            <div class="btn-group">
                <button class="eto-type-view btn btn-sm btn-success" type="button" data-eto-type-view="table">{{ trans('news.buttons.table_view') }}</button>
                <button class="eto-type-view btn btn-sm btn-default" type="button" data-eto-type-view="timeline">{{ trans('news.buttons.timeline_view') }}</button>
            </div>
            <div class="eto-field eto-field-search">
                <div class="eto-field-value">
                    <input id="search" class="eto-js-inputs eto-js-search" data-eto-name="search" value="" placeholder=" {{ trans('news.form.search') }}" type="text">
                </div>
                <div class="eto-field-placeholder hidden"> {{ trans('news.form.search') }}</div>
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
    <div class="clearfix" style="margin-left: 4px; margin-top: -18px;">
        <button type="button" class="eto-load-more btn btn-info hidden" style="/*display: none*/">
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
                uriSearch: 'news/search',
                no_data_message: '{{ trans('news.no_data') }}',
                uriAnhor: '/news/',
                containerTimeline: $('.eto-timeline-view'),
                containerTable: $('.eto-table-view'),
                typeView: $('.eto-type-view.btn-success').data('etoTypeView') ? $('.eto-type-view.btn-success').data('etoTypeView') : 'table',
            });
        });
    </script>
@stop
