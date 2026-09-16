@extends('admin.index')

@if (request()->is('roles'))
    @section('title', trans('roles.titles.roles'))
    @section('subtitle', /*'<i class="fa fa-user-secret "></i> '.*/ trans('roles.titles.roles') )
@elseif (request()->is('roles/trash'))
    @section('title', trans('roles.titles.roles_deleted'))
    @section('subtitle', /*'<i class="fa fa-user-secret "></i> '.*/ trans('roles.titles.roles_deleted') )
@endif

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
            @if (request()->is('roles'))
                {!! trans('roles.titles.roles') !!}
            @elseif (request()->is('roles/trash'))
                {!! trans('roles.titles.roles_deleted') !!}
            @endif
        </h4>

        <div class="box-tools pull-right" style="right: -5px;">
{{--        @if (request()->is('roles'))--}}
{{--            <a href="{{ route('roles.create') }}" class="btn btn-sm btn-default">--}}
{{--                {!! trans('roles.buttons.new') !!}--}}
{{--            </a>--}}
{{--            @if($trashedCount > 0)--}}
{{--            <a href="{{ route('roles.trash') }}" class="btn btn-sm btn-default">--}}
{{--                {!! trans('roles.buttons.trash') !!}--}}
{{--            </a>--}}
{{--            @endif--}}
{{--        @elseif (request()->is('roles/trash'))--}}
{{--            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-default">--}}
{{--                {!! trans('roles.buttons.back') !!}--}}
{{--            </a>--}}
{{--        @endif--}}
        </div>
    </div>
    <div class="pageContainer" id="roles">
    <div class="pageContent">
       {!! $builder->table(['class' => 'table table-hover', 'width' => '100%', 'data-form' => 'deleteForm'], false) !!}
    </div>
    </div>
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

    {!! $builder->scripts() !!}
    @include('roles.partials.index')

    <script>
    function updateTableHeight() {
       var height = parseFloat($('.wrapper > .content-wrapper').css('min-height')) -
           $('.data-table > .topContainer').height() -
           $('.data-table > .bottomContainer').height() -
           $('.dataTables_scrollHead').height() - 140;

       if( height < 200 ) {
           height = 200;
       }

       $('.dataTables_scrollBody').css({'min-height': height +'px'});
    }
    $(document).ready(function(){
       @if(request()->system->subscription->license_status == 'suspended')
           $('#roles').prepend('<div class="license-suspended-block"></div>');
       @endif

       updateTableHeight();
    });

    $(window).resize(function() {
       updateTableHeight();
    });
    </script>
@stop
