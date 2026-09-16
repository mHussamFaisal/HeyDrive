@extends('admin.index')

@section('title', trans('reports.page_title'))
@section('subtitle', /*'<i class="fa fa-pie-chart"></i> '.*/ trans('reports.page_title') )
@section('subclass', '')

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}">
@stop

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')
    <div class="box-header" style="left: -10px;">
        <h4 class="box-title">
            @if(request()->is('reports/trash'))
                @if(!empty($type))
                    {{ trans('reports.titles.'.$type.'_trashed') }}
                @else
                    {{ trans('reports.titles.trashed') }}
                @endif
            @else
                @if(!empty($type))
                {{ trans('reports.titles.'.$type.'_saved') }}
                @else
                {{ trans('reports.titles.saved') }}
                @endif
            @endif
        </h4>

        <div class="box-tools pull-right" style="right: -9px;">
            <a href="{{ route('reports.new', ['type'=>$type]) }}" style="margin-right: 5px;" class="btn btn-sm btn-default">
                <span>{{ trans('reports.button.generate') }}</span>
            </a>
            @if(!request()->is('reports/trash'))
                <a href="{{ route('reports.trashIndex') }}@if(!empty($type))?type={{ $type }}@endif" class="btn btn-sm btn-default">
                    <span>{{ trans('reports.button.show_trash') }}</span>
                </a>
            @else
                <a href="{{ route('reports.index') }}@if(!empty($type))?type={{ $type }}@endif" class="btn btn-sm btn-default">
                    <span>{{ trans('reports.button.go_to_all') }}</span>
                </a>
            @endif
           {{-- <button type="button" class="btn btn-sm btn-default eto-btn-settings" data-toggle="modal" data-target=".eto-modal-settings">
               <i class="fa fa-cogs"></i>
           </button> --}}
        </div>
    </div>
    <div class="pageContainer" id="reports">
        <div class="pageContent">
            {!! $builder->table(['class' => 'table table-hover', 'width' => '100%', 'data-form' => 'deleteForm'], false) !!}
        </div>
    </div>

    <div class="eto-charts-body"></div>

    @include('reports.settings')
@stop

@section('subfooter')
    <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.server-side.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/ColReorder/js/dataTables.colReorder.min.js') }}"></script>

    <script src="{{ asset_url('js','eto/eto-report.js') }}?_dc={{ config('app.timestamp') }}"></script>

    {!! $builder->scripts() !!}

    <script>
    $(document).ready(function(){
        if (typeof ETO.Report != "undefined") {
            if (typeof ETO.Report.init != "undefined") {
                ETO.Report.init({
                    isTrash: {{ request()->is('reports/trash') ? 1 : 0 }}
                });
            }
        }

        @if(request()->system->subscription->license_status == 'suspended')
            $('#reports').prepend('<div class="license-suspended-block"></div>');
        @endif
    });
    </script>
@stop
