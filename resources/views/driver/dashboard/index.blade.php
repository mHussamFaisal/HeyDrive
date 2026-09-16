@extends('driver.index')

@section('title', trans('driver/dashboard.page_title'))
@section('subtitle', /*'<i class="fa fa-th"></i> '*/ '<a href="'. route('driver.dashboard.index') .'">'. trans('driver/dashboard.page_title') .'</a>')

@section('subcontent')
<div id="dashboard">
    <div class="dashboard-inner clearfix">

        <a href="{{ $currentJobUrl }}" class="booking-staus-container current-container @if( !$current ) hide @endif" style="color:#333;">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="ion ion-ios-navigate-outline"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="margin-top:15px;">{{ trans('driver/dashboard.button.current') }}</span>
                    <span class="info-box-number counter">{{ $current }}</span>
                </div>
            </div>
        </a>

        <a href="{{ route('driver.jobs.index') }}?status=assigned" class="booking-staus-container assigned-container" style="color:#333;">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="ion ion-ios-help-outline"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="margin-top:15px;">{{ trans('driver/dashboard.button.assigned') }}</span>
                    <span class="info-box-number counter">{{ $assigned }}</span>
                </div>
            </div>
        </a>

        <a href="{{ route('driver.jobs.index') }}?status=accepted" class="booking-staus-container accepted-container" style="color:#333;">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="ion ion-ios-clock-outline"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="margin-top:15px;">{{ trans('driver/dashboard.button.accepted') }}</span>
                    <span class="info-box-number counter">{{ $accepted }}</span>
                </div>
            </div>
        </a>

        <a href="{{ route('driver.jobs.index') }}?status=completed" class="booking-staus-container completed-container" style="color:#333;">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="ion ion-ios-checkmark-outline"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="margin-top:15px;">{{ trans('driver/dashboard.button.completed') }}</span>
                    <span class="info-box-number counter">{{ $completed }}</span>
                </div>
            </div>
        </a>

        @if($canceled > 0)
            <a href="{{ route('driver.jobs.index') }}?status=canceled" class="booking-staus-container canceled-container" style="color:#333;">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="ion ion-ios-close-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" style="margin-top:15px;">{{ trans('driver/dashboard.button.canceled') }}</span>
                        <span class="info-box-number counter">{{ $canceled }}</span>
                    </div>
                </div>
            </a>
        @endif

    </div>
</div>
@stop
