@extends('admin.index')

@section('title', trans('reports.page_title'))
@section('subtitle', /*'<i class="fa fa-pie-chart"></i> '.*/ trans('reports.page_title') )
@section('subclass', '')

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
@endsection

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')
    <div class="pageContainer" id="reports">
        <div class="pageTitle">
            <h3>{{ trans('reports.titles.'.$report->type) }}</h3>
        </div>
        <div class="pageContent eto-show-report"></div>
    </div>
@stop

@section('subfooter')
    <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset_url('js','eto/eto-report.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script>
    $(document).ready(function(){
        if (typeof ETO.Report != "undefined") {
            if (typeof ETO.Report.init != "undefined") {
                ETO.Report.init({
                    typeReport: '{{ $report->type }}',
                });
                ETO.Report.renderReport({!! json_encode($report) !!});
            }
        }

        @if(request()->system->subscription->license_status == 'suspended')
            $('#reports').prepend('<div class="license-suspended-block"></div>');
        @endif
    });
    </script>
@stop
