@extends('layouts.app')

@section('title', trans('driver/index.page_title'))

@section('bodyClass', 'skin-blue driver-panel '. ( isset($_COOKIE['eto_state_sidebar_collapse']) ? 'sidebar-collapse' : '' ) .' fixed hold-transition')

@section('header')
    <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">

    @yield('subheader')

    <link rel="stylesheet" href="{{ asset_url('css','driver.css') }}?_dc={{ config('app.timestamp') }}">

    @if( session('isMobileApp') )
        <style>
        .logout-container {
            display: none !important;
        }

        .eto-modal-booking-tracking .modal-header button,
        #calendar #modal-popup .modal-header button {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 99;
        }
        .eto-modal-booking-tracking .modal-header .modal-title {
            text-align: center;
        }

        @if( !config('site.branding') )
            .sidebar {
                padding-bottom: 0px;
            }
            .copyright-box {
                display: none !important;
            }
        @endif
        </style>
    @endif
@stop


@php
$user = auth()->user();
$driverStatus = $user->profile->availability_status;
$counts = App\Helpers\DriverHelper::getBookingCounts($user->id);
$currentJobUrl = App\Helpers\DriverHelper::getCurrentJobUrl($user->id, $counts->current);
$checkUserDocuments = App\Helpers\DriverHelper::checkUserDocuments();
@endphp


@section('content')
    <div class="wrapper">
        <header class="main-header">
            {{-- <a href="{{ route('driver.index') }}" class="logo">
                <span class="logo-mini">
                    @if ( config('site.logo') )
                        <img src="{{ asset_url('uploads','logo/'. config('site.logo')) }}" alt="{{ config('app.name') }}">
                    @else
                        {{ config('app.name') }}
                    @endif
                </span>
                <span class="logo-lg">
                    @if ( config('site.logo') )
                        <img src="{{ asset_url('uploads','logo/'. config('site.logo')) }}" alt="{{ config('app.name') }}">
                    @else
                        {{ config('app.name') }}
                    @endif
                </span>
            </a> --}}
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">{{ trans('driver/index.toggle_navigation') }}</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <span class="main-page-title">@yield('subtitle')</span>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <img src="{{ asset( $user->getAvatarPath() ) }}" class="user-image" alt="">
                                <span class="hidden-xs">{{ $user->name }}</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header">
                                    <img src="{{ asset( $user->getAvatarPath() ) }}" class="img-circle" alt="">
                                    <p>
                                        {{ $user->name }}
                                        <small>{{ trans('driver/index.member_since') }} {{ Carbon\Carbon::parse($user->getOriginal('created_at'))->diffForHumans(null, true, false, 2) }}</small>
                                    </p>
                                </li>
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="{{ route('driver.account.index') }}" class="btn btn-default btn-flat">
                                            <i class="fa fa-user"></i> <span>{{ trans('driver/index.profile') }}</span>
                                        </a>
                                    </div>
                                    <div class="pull-right logout-container">
                                        <a href="{{ route('logout') }}" class="btn btn-default btn-flat" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                            <i class="fa fa-sign-out"></i> <span>{{ trans('driver/index.logout') }}</span>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <aside class="main-sidebar">
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="icon-toggle"></span>
            </a>
            <section class="sidebar">
                {{-- <div class="user-panel">
                    <div class="pull-left image">
                        <img src="{{ asset( $user->getAvatarPath() ) }}" class="img-circle" alt="">
                    </div>
                    <div class="pull-left info">
                        <p style="margin: 10px 0 0 0;">
                            <a href="{{ route('driver.account.index') }}" style="color:#fff;">
                                {{ $user->name }}
                            </a>
                        </p>
                        <a href="#">
                            <i class="fa fa-circle text-success"></i> <span>{{ trans('driver/index.online') }}</span>
                        </a>
                    </div>
                </div> --}}

                <ul class="sidebar-menu">
                    @if( !session('isMobileApp') || (!empty(session('clientVersion')) && version_compare(session('clientVersion'), '1.4.0', '<')) )
                    <li class="header eto-availability-status-box" style="background:#222d32; display:none;">
                        <select class="form-control select2" id="eto-availability-status" data-minimum-results-for-search="Infinity" style="width:100%;" data-placeholder="Availability Status" name="availability_status">
                            <option value="0" @if($driverStatus == 0) selected="selected" @endif>{{ trans('common.user_availability_status_options.unavailable') }}</option>
                            <option value="1" @if($driverStatus == 1) selected="selected" @endif>{{ trans('common.user_availability_status_options.available') }}</option>
                            <option value="2" @if($driverStatus == 2) selected="selected" @endif>{{ trans('common.user_availability_status_options.onbreak') }}</option>
                        </select>
                    </li>
                    @endif

                    @permission('driver.jobs.index')
                    <li @if( Route::is('driver.dashboard.*') || Route::is('driver.index') ) class="active" @endif>
                        <a href="{{ route('driver.dashboard.index') }}">
                            {{-- <i class="fa fa-th"></i>  --}}
                            <span>{{ trans('driver/index.dashboard') }}</span>
                        </a>
                    </li>
                    <li class="treeview @if( Route::is('driver.jobs.*') ) active @endif">
                        <a href="{{ route('driver.jobs.index') }}">
                            {{-- <i class="fa fa-tasks"></i>  --}}
                            <span>{{ trans('driver/index.jobs') }}</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="current-container @if( (
                                Route::is('driver.jobs.index') && request('status') == 'current'
                              ) || (
                                Route::is('driver.jobs.show') && route('driver.jobs.show', $job->id) == $currentJobUrl
                              ) ) active @endif @if( !$counts->current ) hide @endif">
                                <a href="{{ $currentJobUrl }}">
                                    {{-- <i class="fa fa-circle-o text-purple"></i> --}}
                                    <span>{{ trans('driver/index.job_current') }}</span>
                                </a>
                            </li>

                            <li @if( Route::is('driver.jobs.index') && request('status') == 'assigned' ) class="active" @endif>
                                <a href="{{ route('driver.jobs.index') }}?status=assigned" class="assigned-container">
                                    {{-- <i class="fa fa-circle-o text-yellow"></i> --}}
                                    <span>{{ trans('driver/index.job_assigned') }}</span>
                                    <span class="pull-right-container">
                                        <small class="label pull-right bg-yellow counter @if( !$counts->assigned ) hide @endif" id="assigned-jobs-count">{{ $counts->assigned }}</small>
                                    </span>
                                </a>
                            </li>
                            <li @if( Route::is('driver.jobs.index') && request('status') == 'accepted' ) class="active" @endif>
                                <a href="{{ route('driver.jobs.index') }}?status=accepted">
                                    {{-- <i class="fa fa-circle-o text-aqua"></i> --}}
                                    <span>{{ trans('driver/index.job_accepted') }}</span>
                                </a>
                            </li>
                            <li @if( Route::is('driver.jobs.index') && request('status') == 'completed' ) class="active" @endif>
                                <a href="{{ route('driver.jobs.index') }}?status=completed">
                                    {{-- <i class="fa fa-circle-o text-green"></i> --}}
                                    <span>{{ trans('driver/index.job_completed') }}</span>
                                </a>
                            </li>
                            @if($counts->canceled > 0)
                                <li @if( Route::is('driver.jobs.index') && request('status') == 'canceled' ) class="active" @endif>
                                    <a href="{{ route('driver.jobs.index') }}?status=canceled">
                                        {{-- <i class="fa fa-circle-o text-red"></i> --}}
                                        <span>{{ trans('driver/index.job_canceled') }}</span>
                                    </a>
                                </li>
                            @endif
                            <li @if( (Route::is('driver.jobs.index') && request('status') == '') || Route::is('driver.index') ) class="active" @endif>
                                <a href="{{ route('driver.jobs.index') }}">
                                    {{-- <i class="fa fa-circle-o"></i> --}}
                                    <span>{{ trans('driver/index.job_all') }}</span>
                                </a>
                            </li>

                            {{--
                            <li @if( Route::is('driver.jobs.create') ) class="active" @endif>
                                <a href="{{ route('driver.jobs.create') }}">
                                    <i class="fa fa-plus text-green"></i> <span>{{ trans('driver/index.job_create') }}</span>
                                </a>
                            </li>
                            --}}
                        </ul>
                    </li>
                    @endpermission
                    @permission('driver.calendar.index')
                    <li class="menu-link-calendar @if( Route::is('driver.calendar.*') ) active @endif">
                        <a href="{{ route('driver.calendar.index') }}">
                            {{-- <i class="fa fa-calendar"></i>  --}}
                            <span>{{ trans('driver/index.calendar') }}</span>
                            @permission('driver.calendar.create')
                            @if( config('site.allow_driver_availability') )
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            @endif
                            @endpermission
                        </a>
                        @permission('driver.calendar.create')
                        @if( config('site.allow_driver_availability') )
                            <ul class="treeview-menu">
                                <li @if( Route::is('driver.calendar.index') ) class="active" @endif>
                                    <a href="{{ route('driver.calendar.index') }}">
                                        {{-- <i class="fa fa-list"></i>  --}}
                                        <span>{{ trans('driver/index.calendar_all') }}</span>
                                    </a>
                                </li>
                                <li @if( Route::is('driver.calendar.create') ) class="active" @endif>
                                    <a href="{{ route('driver.calendar.create') }}">
                                        {{-- <i class="fa fa-plus text-green"></i>  --}}
                                        <span>{{ trans('driver/index.calendar_create') }}</span>
                                    </a>
                                </li>
                            </ul>
                        @endif
                        @endpermission
                    </li>
                    @endpermission

                    {{--
                    <li>
                        <a href="{{ route('driver.messages.index') }}">
                            <i class="fa fa-envelope"></i> <span>{{ trans('driver/index.messages') }}</span>
                            <span class="pull-right-container">
                                <small class="label pull-right bg-yellow">12</small>
                                <small class="label pull-right bg-green">16</small>
                                <small class="label pull-right bg-red">5</small>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('driver.settings.index') }}">
                            <i class="fa fa-cogs"></i> <span>{{ trans('driver/index.settings') }}</span>
                        </a>
                    </li>
                    --}}

                    <li @if( Route::is('driver.account.*') ) class="active" @endif>
                        <a href="{{ route('driver.account.index') }}">
                            {{-- <i class="fa fa-user"></i>  --}}
                            <span>{{ trans('driver/index.profile') }}</span>
                            @if( $checkUserDocuments )
                            <span class="pull-right-container">
                                <small class="label pull-right bg-gray" style="padding: .2em .3em .2em .3em;" data-toggle="tooltip" data-placement="bottom" title="{!! $checkUserDocuments !!}"><i class="fa fa-exclamation"></i></small>
                            </span>
                            @endif
                        </a>
                    </li>

                    <!--@if( !session('isMobileApp') )-->
                    <!--    <li @if( Route::is('driver.mobile-app') ) class="active" @endif>-->
                    <!--        <a href="{{ route('driver.mobile-app') }}">-->
                    <!--            {{-- <i class="fa fa-download"></i>  --}}-->
                    <!--            <span>{{ trans('driver/index.mobile_app') }}</span>-->
                    <!--        </a>-->
                    <!--    </li>-->
                    <!--@endif-->

                    <li class="logout-container">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            {{-- <i class="fa fa-sign-out"></i>  --}}
                            <span>{{ trans('driver/index.logout') }}</span>
                        </a>
                    </li>
                </ul>

                <div class="copyright-box">
                    {{ trans('common.powered_by') }} <a href="https://techsoftsystem.com/" target="_blank">Tech Soft System</a>
                </div>
            </section>
        </aside>

        <div class="content-wrapper">
            <section class="content">
                @yield('subcontent')
            </section>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            {{ csrf_field() }}
        </form>
    </div>
@stop

@section('footer')
    <script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset_url('plugins','fastclick/fastclick.min.js') }}"></script>
    <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
    <script src="{{ asset_url('js','app.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script type="text/javascript">
    var ajaxReady = 1,
        driverStatus = {{ $driverStatus }};

    function getJobsCounts () {
        if( ajaxReady == 0 ) { return; }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: '{{ route('driver.dashboard.index') }}',
            type: 'GET',
            data: {
                action: 'counts',
            },
            dataType: 'json',
            async: true,
            cache: false,
            success: function(response) {
                $('.assigned-container .counter').html(response.assigned);
                $('.accepted-container .counter').html(response.accepted);
                $('.completed-container .counter').html(response.completed);
                $('.canceled-container .counter').html(response.canceled);
                $('.current-container .counter').html(response.current);

                if (response.assigned) {
                    $('.sidebar-menu .assigned-container .counter').removeClass('hide');
                }
                else {
                    $('.sidebar-menu .assigned-container .counter').addClass('hide');
                }

                if (response.current) {
                    $('.current-container').removeClass('hide');
                }
                else {
                    $('.current-container').addClass('hide');
                }

                $('.sidebar-menu .current-container a, #dashboard a.current-container').attr('href', response.currentJobUrl);
            },
            error: function() {
                console.log('AJAX error');
            },
            beforeSend: function() {
                ajaxReady = 0;
            },
            complete: function() {
                ajaxReady = 1;
            }
        });
    }

    $(document).ready(function() {
        // Counts
        setInterval(getJobsCounts, 1000 * 30);

        // Select
        $('.select2').select2({
            minimumResultsForSearch: 5
        });

        // Tooltip
        $('[title]').tooltip({
            placement: 'auto',
            container: 'body',
            selector: '',
            html: true,
            trigger: 'hover',
            delay: {
                show: 500,
                hide: 100
            }
        });

        $('.eto-availability-status-box').show();

        $('#eto-availability-status').select2({
            dropdownParent: $('.eto-availability-status-box'),
        });

        $('#eto-availability-status').on('select2:select', function(e) {
            var statusId = parseInt($(this).val());
            $.ajax({
                headers : {
                    'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                url: EasyTaxiOffice.appPath +'/driver/set-status',
                type: 'POST',
                dataType: 'json',
                cache: false,
                async: false,
                data: {
                    statusId: statusId
                },
                success: function(response) {
                    if (response.success) {
                        driverStatus = statusId;
                    }
                    else {
                        alert('Could not switch to this status');
                    }
                },
                error: function(response) {
                    alert('An error occurred while processing your request');
                }
            });
        });
    });
    </script>

    @yield('subfooter')
@stop
