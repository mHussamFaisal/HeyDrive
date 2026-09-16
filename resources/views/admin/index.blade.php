@extends('layouts.app')

@section('title', 'Admin')

@section('bodyClass', 'skin-blue admin-panel '. ( isset($_COOKIE['eto_state_sidebar_collapse']) ? 'sidebar-collapse' : '' ) .' fixed hold-transition')

@section('header')
    <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">

    @yield('subheader')

    <link rel="stylesheet" href="{{ asset_url('css','admin.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('css','eto.css') }}?_dc={{ config('app.timestamp') }}">

    <style>
    @if( session('isMobileApp') )
      .logout-container {
          display: none !important;
      }
    @endif

    @if( !config('site.allow_services') )
      body.admin-panel #config .field-booking_service_dropdown,
      body.admin-panel #config .field-booking_service_display_mode,
      body.admin-panel #config .field-admin_calendar_show_service_type,
      body.admin-panel #itemsList .additional-item-address,
      body.admin-panel .sidebar-menu .main-menu-services,
      body.admin-panel .sidebar-menu .main-menu-scheduled-routes,
      body.admin-panel #bookings .pageFilters .filter-booking_type,
      body.admin-panel #bookings .pageFilters .filter-service_id,
      body.admin-panel #bookings .dataTable .column-service_id,
      body.admin-panel #bookings .dataTable .column-service_duration,
      body.admin-panel #bookings .dataTable .column-scheduled_route_id,
      body.admin-panel #bookings #etoServicesContainer,
      body.admin-panel #payment-methods .dataTable .column-service_ids,
      body.admin-panel #payment-methods #dmodal .field-service_ids,
      body.admin-panel #fixed-prices .pageFilters .field-service_ids,
      body.admin-panel #fixed-prices .dataTable .column-service_ids,
      body.admin-panel #fixed-prices #dmodal .field-service_ids,
      body.admin-panel #vehicles .dataTable .column-service_ids,
      body.admin-panel #vehicles .dataTable .column-hourly_rate,
      body.admin-panel #vehicles .dataTable .column-user_id,
      body.admin-panel #vehicles #dmodal .field-service_ids,
      body.admin-panel #vehicles #dmodal .field-hourly_rate,
      body.admin-panel #vehicles #dmodal .field-user_id {
          display: none !important;
      }
    @endif
    </style>

    @if( (Route::is('admin.*') || Route::is('dispatch.*') || Route::is('subscription.*') || Route::is('reports.*')) && !Route::is('admin.callerid.*') &&
        request('tmpl') != 'body' && config('site.callerid_type') )
      @include('admin.callerid.header')
    @endif
@stop

@php
$request = request();
$sitesList = App\Helpers\AdminHelper::getSitesList();
$feedbackCounts = App\Helpers\AdminHelper::getFeedbackCounts();
$bookingCounts = App\Helpers\AdminHelper::getBookingCounts();
$checkUserDocuments = App\Helpers\AdminHelper::checkUserDocuments();
$checkVehicleDocuments = App\Helpers\AdminHelper::checkVehicleDocuments();
@endphp

@section('content')
    <div class="wrapper">
        <header class="main-header">
            {{-- <a href="{{ route('admin.index') }}" class="logo">
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
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <span class="main-page-title">@yield('subtitle')</span>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <img src="{{ asset(auth()->user()->getAvatarPath()) }}" class="user-image" alt="">
                                <span class="hidden-xs">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header">
                                    <img src="{{ asset( auth()->user()->getAvatarPath() ) }}" class="img-circle" alt="">
                                    <p>
                                        {{ auth()->user()->name }}
                                        <small>Member since {{ Carbon\Carbon::parse(auth()->user()->getOriginal('created_at'))->diffForHumans(null, true, false, 2) }}</small>
                                    </p>
                                </li>
                                <li class="user-footer">
                                    <div class="pull-left">
                                        {{-- <a href="{{ route('admin.account.index') }}" class="btn btn-default btn-flat"> --}}
                                        <a href="{{ route('admin.users.show', auth()->user()->id) }}" class="btn btn-default btn-flat">
                                            <i class="fa fa-user"></i> <span>Profile</span>
                                        </a>
                                    </div>
                                    <div class="pull-right logout-container">
                                        <a href="{{ route('logout') }}" class="btn btn-default btn-flat" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                            <i class="fa fa-sign-out"></i> <span>Sign Out</span>
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
                {{--
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="{{ asset( auth()->user()->getAvatarPath() ) }}" class="img-circle" alt="">
                    </div>
                    <div class="pull-left info">
                        <p>{{ auth()->user()->name }}</p>
                        <a href="#">
                            <i class="fa fa-circle text-success"></i> <span>Online</span>
                        </a>
                    </div>
                </div>
                --}}

                @if (!empty($request->system->subscription))
                <ul class="sidebar-menu">

                    @if (auth()->user()->hasPermission('admin.sites.switch'))
                        <li class="header switch-profile-box" style="background:#222d32; display:none;">
                            <select name="switch_profile" id="switch_profile" data-placeholder="Switch profile" data-minimum-results-for-search="Infinity" class="form-control select2" style="width:100%;">
                            @foreach ($sitesList as $site)
                                <option value="{{ $site->value }}" @if($site->selected) selected="selected" @endif>{{ $site->text }}</option>
                            @endforeach
                            </select>
                        </li>
                    @endif

                    {{--
                    <li class="header" style="padding-top:1px; padding-bottom:1px;"></li>
                    <li @if( Route::is('admin.dashboard.*') ) class="active" @endif>
                        <a href="{{ route('admin.dashboard.index') }}">
                            <i class="fa fa-th"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    --}}

                    @permission('admin.settings.getting_started.index')
                    @if( config('site.admin_default_page') == 'getting-started' )
                    <li @if( Route::is('admin.getting-started') ) class="active" @endif>
                        <a href="{{ route('admin.getting-started') }}">
                            {{-- <i class="fa fa-mouse-pointer"></i>  --}}
                            <span>Getting Started</span>
                        </a>
                    </li>
                    @endif
                    @endpermission

                    @permission('admin.dispatch.index')
                    <li @if( Route::is('dispatch.*') ) class="active" @endif>
                        <a href="{{ route('dispatch.index') }}" class="menu-dispatch-new-jobs">
                            {{-- <i class="fa fa-flag"></i>  --}}
                            <span>Dispatch</span>
                            <span class="pull-right-container" title="New bookings">
                                <small class="label pull-right bg-aqua @if( !$bookingCounts->latest ) hide @endif" id="latest-jobs">{{ $bookingCounts->latest }}</small>
                            </span>
                        </a>
                    </li>
                    @endpermission

                    @permission('admin.bookings.index')
                    <li class="treeview @if( Route::is('admin.bookings.*') || Route::is('admin.calendar.*') || Route::is('admin.index') ) active @endif">
                        <a href="{{ route('admin.bookings.index') }}">
                            {{-- <i class="fa fa-tasks"></i>  --}}
                            <span>Bookings</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li @if( Route::is('admin.bookings.index') && request('page') == 'next24' ) class="active" @endif>
                                <a href="{{ route('admin.bookings.index') }}?page=next24" class="menu-next24-jobs">
                                    {{-- <i class="fa fa-circle-o text-aqua"></i>  --}}
                                    <span>{{ trans('admin/index.bookings_next24') }}</span>
                                    <span class="pull-right-container">
                                        <small class="label pull-right bg-aqua @if( !$bookingCounts->next24 ) hide @endif" id="next24-jobs">{{ $bookingCounts->next24 }}</small>
                                    </span>
                                </a>
                            </li>
                            <li @if( Route::is('admin.bookings.index') && request('page') == 'latest' ) class="active" @endif>
                                <a href="{{ route('admin.bookings.index') }}?page=latest" class="menu-latest-jobs">
                                    {{-- <i class="fa fa-circle-o text-aqua"></i>  --}}
                                    <span>{{ trans('admin/index.bookings_latest') }}</span>
                                    <span class="pull-right-container">
                                        <small class="label pull-right bg-aqua @if( !$bookingCounts->latest ) hide @endif" id="latest-jobs" title="New bookings">{{ $bookingCounts->latest }}</small>
                                    </span>
                                </a>
                            </li>
                            <li class=" @if( Route::is('admin.bookings.index') && request('page') == 'requested' ) active @endif @if( !($bookingCounts->requested || config('site.booking_request_enable')) ) hide @endif">
                                <a href="{{ route('admin.bookings.index') }}?page=requested" class="menu-requested-jobs">
                                    {{-- <i class="fa fa-circle-o text-orange"></i>  --}}
                                    <span>{{ trans('admin/index.bookings_requested') }}</span>
                                    <span class="pull-right-container">
                                        <small class="label pull-right bg-orange @if( !$bookingCounts->requested ) hide @endif" id="requested-jobs">{{ $bookingCounts->requested }}</small>
                                    </span>
                                </a>
                            </li>
                            <li @if( Route::is('admin.bookings.index') && request('page') == 'completed' ) class="active" @endif>
                                <a href="{{ route('admin.bookings.index') }}?page=completed" class="menu-completed-jobs">
                                    {{-- <i class="fa fa-circle-o text-green"></i>  --}}
                                    <span>{{ trans('admin/index.bookings_completed') }}</span>
                                    {{-- <span class="pull-right-container">
                                        <small class="label pull-right bg-green @if( !$bookingCounts->completed ) hide @endif" id="completed-jobs">{{ $bookingCounts->completed }}</small>
                                    </span> --}}
                                </a>
                            </li>
                            <li @if( Route::is('admin.bookings.index') && request('page') == 'canceled' ) class="active" @endif>
                                <a href="{{ route('admin.bookings.index') }}?page=canceled" class="menu-canceled-jobs">
                                    {{-- <i class="fa fa-circle-o text-red"></i>  --}}
                                    <span>{{ trans('admin/index.bookings_canceled') }}</span>
                                    {{-- <span class="pull-right-container">
                                        <small class="label pull-right bg-red @if( !$bookingCounts->canceled ) hide @endif" id="canceled-jobs">{{ $bookingCounts->canceled }}</small>
                                    </span> --}}
                                </a>
                            </li>
                            <li @if( (Route::is('admin.bookings.index') && request('page') == '') || Route::is('admin.index') ) class="active" @endif>
                                <a href="{{ route('admin.bookings.index') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>{{ trans('admin/index.bookings_all') }}</span>
                                </a>
                            </li>
                            @permission(['admin.bookings.trash', 'admin.bookings.destroy', 'admin.bookings.restore'])
                            <li @if( (Route::is('admin.bookings.index') && request('page') == 'trash') || Route::is('admin.index') ) class="active" @endif>
                                <a href="{{ route('admin.bookings.index') }}?page=trash">
                                    {{-- <i class="fa fa-trash"></i>  --}}
                                    <span>{{ trans('admin/index.bookings_trash') }}</span>
                                </a>
                            </li>
                            @endpermission
                            <li @if( Route::is('admin.calendar.index') ) class="active" @endif>
                                <a href="{{ route('admin.calendar.index') }}">
                                    {{-- <i class="fa fa-calendar"></i>  --}}
                                    <span>Calendar</span>
                                </a>
                            </li>
                            @permission('admin.bookings.create')
                            <li @if( Route::is('admin.bookings.create') ) class="active" @endif>
                                <a href="{{ route('admin.bookings.create') }}">
                                    {{-- <i class="fa fa-plus text-green"></i>  --}}
                                    <span>Add New</span>
                                </a>
                            </li>
                            @endpermission
                        </ul>
                    </li>
                    @endpermission
                    {{-- <li @if( Route::is('admin.map.*') ) class="active" @endif>
                        <a href="{{ route('admin.map.index') }}">
                            <i class="fa fa-map"></i> <span>Map</span>
                        </a>
                    </li> --}}

                    @permission('admin.reports.create')
                    <li class="treeview @if( Route::is('reports.*') ) active @endif">
                        <a href="{{ route('reports.index') }}">
                            {{-- <i class="fa fa-pie-chart"></i>  --}}
                            <span>{{ trans('admin/index.menu.reports.index') }}</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            @if (config('eto.allow_fleet_operator'))
                            <li @if( request()->is('reports/fleet') || ((request()->is('reports') || request()->is('reports/*')) && request('type') == 'fleet')) class="active" @endif>
                                <a href="{{ route('reports.new', ['type' => 'fleet']) }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>{{ trans('admin/index.menu.reports.fleet') }}</span>
                                </a>
                            </li>
                            @endif
                            <li @if( request()->is('reports/driver') || ((request()->is('reports') || request()->is('reports/*')) && request('type') == 'driver')) class="active" @endif>
                                <a href="{{ route('reports.new', ['type' => 'driver']) }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Driver</span>
                                </a>
                            </li>
                            {{-- <li @if( request()->is('reports/customer') ) class="active" @endif>
                               <a href="{{ route('reports.new', ['type' => 'customer']) }}">
                                   <i class="fa fa-circle-o"></i>
                                   <span>{{ trans('admin/index.menu.reports.customer') }}</span>
                               </a>
                            </li> --}}
                            <li @if( request()->is('reports/payment') || ((request()->is('reports') || request()->is('reports/*')) && request('type') == 'payment') ) class="active" @endif>
                                <a href="{{ route('reports.new', ['type' => 'payment']) }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>{{ trans('admin/index.menu.reports.payment') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endpermission

                    @permission(['admin.users.admin.index', 'admin.users.driver.index', 'admin.users.customer.index', 'admin.teams.index'])
                    <li class="treeview @if( Route::is('admin.users.*') || Route::is('admin.customers.*') || Route::is('teams.*') ) active @endif">
                        <a href="{{ route('admin.users.index') }}">
                            {{-- <i class="fa fa-users"></i>  --}}
                            <span>Users</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                                @if( $checkUserDocuments )
                                    <small class="label pull-right bg-gray" style="padding: .2em .3em .2em .3em;" data-toggle="tooltip" data-placement="bottom" title="{!! $checkUserDocuments !!}"><i class="fa fa-exclamation"></i></small>
                                @endif
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            @permission('admin.users.customer.index')
                            <li @if( Route::is('admin.customers.index') ) class="active" @endif>
                                <a href="{{ route('admin.customers.index') }}">
                                    {{-- <i class="fa fa-circle-o text-yellow"></i>  --}}
                                    <span>Customers</span>
                                </a>
                            </li>
                            {{--
                            <li @if( Route::is('admin.users.index') && request('role') == 'customer' ) class="active" @endif>
                                <a href="{{ route('admin.users.index') }}?role=customer">
                                    <i class="fa fa-circle-o text-yellow"></i>
                                    <span>Customers</span>
                                </a>
                            </li>
                            --}}
                            @endpermission

                            @permission('admin.users.driver.index')
                            <li @if( Route::is('admin.users.index') && request('role') == 'driver' ) class="active" @endif>
                                <a href="{{ route('admin.users.index') }}?role=driver">
                                    {{-- <i class="fa fa-circle-o text-aqua"></i>  --}}
                                    <span>Driver</span>
                                    {{--
                                    @if( $checkUserDocuments )
                                        <span class="pull-right-container">
                                            <small class="label pull-right bg-gray" style="padding: .2em .3em .2em .3em;"><i class="fa fa-exclamation"></i></small>
                                        </span>
                                    @endif
                                    --}}
                                </a>
                            </li>
                            @endpermission
                            
                            <!---->
                            <!--      @permission('admin.users.driver.index')-->
                            <!--<li @if( Route::is('admin.users.index') && request('role') == 'driver' ) class="active" @endif>-->
                            <!--    <a href="{{ route('admin.users.index') }}?role=driver">-->
                            <!--        {{-- <i class="fa fa-circle-o text-aqua"></i>  --}}-->
                            <!--        <span>Drivers</span>-->
                            <!--        {{---->
                            <!--        @if( $checkUserDocuments )-->
                            <!--            <span class="pull-right-container">-->
                            <!--                <small class="label pull-right bg-gray" style="padding: .2em .3em .2em .3em;"><i class="fa fa-exclamation"></i></small>-->
                            <!--            </span>-->
                            <!--        @endif-->
                            <!--        --}}-->
                            <!--    </a>-->
                            <!--</li>-->
                            <!--@endpermission-->
                            
                            
                            <!---->
                            
                            <!-- adding new user -->
                            <!-- @permission('admin.users.driver.index')-->
                            <!--<li @if( Route::is('admin.users.index') && request('role') == 'supplier' ) class="active" @endif>-->
                            <!--    <a href="{{ route('admin.users.index') }}?role=supplier">-->
                            <!--        {{-- <i class="fa fa-circle-o text-aqua"></i>  --}}-->
                            <!--        <span>Supplier</span>-->
                            <!--        {{---->
                            <!--        @if( $checkUserDocuments )-->
                            <!--            <span class="pull-right-container">-->
                            <!--                <small class="label pull-right bg-gray" style="padding: .2em .3em .2em .3em;"><i class="fa fa-exclamation"></i></small>-->
                            <!--            </span>-->
                            <!--        @endif-->
                            <!--        --}}-->
                            <!--    </a>-->
                            <!--</li>-->
                            <!--@endpermission-->
                            
                            <!--add new user end-->

                            @permission('admin.users.admin.index')
                            <li @if( Route::is('admin.users.index') && request('role') == 'admin' ) class="active" @endif>
                                <a href="{{ route('admin.users.index') }}?role=admin">
                                    {{-- <i class="fa fa-circle-o text-red"></i>  --}}
                                    <span>Admins</span>
                                </a>
                            </li>
                            @endpermission

                            {{--
                            <li @if( Route::is('admin.users.index') && request('role') == '' ) class="active" @endif>
                                <a href="{{ route('admin.users.index') }}">
                                    <i class="fa fa-list"></i> <span>All</span>
                                </a>
                            </li>
                            --}}

                            @permission('admin.teams.index')
                            @if (config('eto.allow_teams'))
                            <li @if(Route::is('teams.*')) class="active" @endif>
                                <a href="{{ route('teams.index') }}">
                                    <span>Teams</span>
                                </a>
                            </li>
                            @endif
                            @endpermission

                            @permission(['admin.users.admin.create', 'admin.users.driver.create'])
                            <li @if( Route::is('admin.users.create') ) class="active" @endif>
                                <a href="{{ route('admin.users.create') }}">
                                    {{-- <i class="fa fa-plus text-green"></i>  --}}
                                    <span>Add New</span>
                                </a>
                            </li>
                            @endpermission
                        </ul>
                    </li>
                    @endpermission
                    @permission('admin.feedback.index')
                    <li class="treeview @if( Route::is('admin.feedback.*') ) active @endif">
                      <a href="{{ route('admin.feedback.index') }}">
                        {{-- <i class="fa fa-comments-o"></i>  --}}
                        <span>Feedback</span>
                        <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
                          <small class="label pull-right bg-gray @if( !$feedbackCounts->comment && !$feedbackCounts->lost_found && !$feedbackCounts->complaint ) hide @endif" style="padding: .2em .3em .2em .3em;" id="menu-feedback-icon">
                            <i class="fa fa-exclamation"></i>
                          </small>
                        </span>
                      </a>
                      <ul class="treeview-menu">
                        <li @if( Route::is('admin.feedback.index') && request('type') == 'comment' ) class="active" @endif>
                          <a href="{{ route('admin.feedback.index', ['type' => 'comment']) }}">
                            {{-- <i class="fa fa-circle-o"></i>  --}}
                            <span>Comments</span>
                            <span class="pull-right-container">
                              <small class="label pull-right bg-orange @if( !$feedbackCounts->comment ) hide @endif" id="menu-feedback-counter-comments">{{ $feedbackCounts->comment }}</small>
                            </span>
                          </a>
                        </li>
                        <li @if( Route::is('admin.feedback.index') && request('type') == 'lost_found' ) class="active" @endif>
                          <a href="{{ route('admin.feedback.index', ['type' => 'lost_found']) }}">
                            {{-- <i class="fa fa-circle-o"></i>  --}}
                            <span>Lost & Found</span>
                            <span class="pull-right-container">
                              <small class="label pull-right bg-orange @if( !$feedbackCounts->lost_found ) hide @endif" id="menu-feedback-counter-lost-found">{{ $feedbackCounts->lost_found }}</small>
                            </span>
                          </a>
                        </li>
                        <li @if( Route::is('admin.feedback.index') && request('type') == 'complaint' ) class="active" @endif>
                          <a href="{{ route('admin.feedback.index', ['type' => 'complaint']) }}">
                            {{-- <i class="fa fa-circle-o"></i>  --}}
                            <span>Complaints</span>
                            <span class="pull-right-container">
                              <small class="label pull-right bg-orange @if( !$feedbackCounts->complaint ) hide @endif" id="menu-feedback-counter-lost-found">{{ $feedbackCounts->complaint }}</small>
                            </span>
                          </a>
                        </li>
                        @permission('admin.feedback.create')
                        <li @if( Route::is('admin.feedback.create') ) class="active" @endif>
                          <a href="{{ route('admin.feedback.create', request('type') ? ['type' => request('type')] : []) }}">
                            {{-- <i class="fa fa-plus text-green"></i>  --}}
                            <span>Add New</span>
                          </a>
                        </li>
                        @endpermission
                      </ul>
                    </li>
                    @endpermission

                    @permission('admin.activity.index')
                    @if (config('laravel-activitylog.enabled'))
                      <li @if( Route::is('activity.*') ) class="active" @endif>
                          <a href="{{ route('activity.index') }}">
                              {{-- <i class="fa fa-shield"></i>  --}}
                              <span>Activity</span>
                          </a>
                      </li>
                    @endif
                    @endpermission

                    @permission([
                        'admin.settings.*',
                        'admin.backups.*',
                        'admin.fixed_prices.*',
                        'admin.discounts.*',
                        'admin.services.*',
                        'admin.scheduled_routes.*',
                        'admin.translations.*',
                        'admin.vehicles.*',
                        'admin.vehicle_types.*',
                        'admin.locations.*',
                        'admin.categories.*',
                        'admin.zones.*',
                        'admin.roles.*',
                    ])
                    <li class="treeview @if(
                         Route::is('admin.config.mileage-time') ||
                         Route::is('admin.config.deposit-payments') ||
                         Route::is('admin.config.driver-income') ||
                         Route::is('admin.config.night-surcharge') ||
                         Route::is('admin.config.holiday-surcharge') ||
                         Route::is('admin.config.additional-charges') ||
                         Route::is('admin.config.other-discounts') ||
                         Route::is('admin.config.tax') ||
                         Route::is('admin.settings.charges') ||
                         Route::is('admin.fixed-prices.*') ||
                         Route::is('admin.zones.*') ||
                         Route::is('admin.discounts.*') ||
                         Route::is('admin.vehicles.*') ||
                         Route::is('admin.vehicles-types.*') ||
                         Route::is('admin.services.*') ||
                         Route::is('admin.scheduled-routes.*') ||
                         Route::is('admin.locations.*') ||
                         Route::is('admin.categories.*') ||
                         Route::is('admin.payments.*') ||
                         Route::is('admin.excluded-routes.*') ||
                         Route::is('admin.meeting-points.*') ||
                         Route::is('admin.settings.notifications') ||
                         Route::is('admin.config.index') ||
                         Route::is('admin.config.localization') ||
                         Route::is('admin.config.booking') ||
                         Route::is('admin.config.web-booking-widget') ||
                         Route::is('admin.config.google') ||
                         Route::is('admin.config.bases') ||
                         Route::is('admin.config.invoices') ||
                         Route::is('admin.config.styles') ||
                         Route::is('admin.config.users') ||
                         Route::is('admin.config.integration') ||
                         Route::is('admin.config.airport-detection') ||
                         Route::is('admin.config.auto-dispatch') ||
                         (config('site.admin_default_page') != 'getting-started' && Route::is('admin.getting-started')) ||
                         Route::is('admin.mobile-app') ||
                         Route::is('admin.web-widget') ||
                         Route::is('backup.index')  ||
                         Route::is('backup.*') ||
                         Route::is('translations.*') ||
                         Route::is('logs') ||
                         Route::is('roles.*') ||
                         Route::is('export.index') ||
                         Route::is('admin.backups.*') ||
                         Route::is('debug.*')) active @endif">
                        <a href="{{ route('admin.config.index') }}">
                            {{-- <i class="fa fa-cogs"></i>  --}}
                            <span>Settings</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            @permission(['admin.settings.mileage_time.*',
                                'admin.fixed_prices.*',
                                'admin.settings.deposit_payments.*',
                                'admin.settings.driver_income.*',
                                'admin.settings.night_surcharge.*',
                                'admin.settings.holiday_surcharge.*',
                                'admin.settings.additional_charges.*',
                                'admin.settings.charges.*',
                                'admin.discounts.*'
                            ])
                            <li class="treeview @if( Route::is('admin.config.mileage-time') ||
                                 Route::is('admin.config.deposit-payments') ||
                                 Route::is('admin.config.driver-income') ||
                                 Route::is('admin.config.night-surcharge') ||
                                 Route::is('admin.config.holiday-surcharge') ||
                                 Route::is('admin.config.additional-charges') ||
                                 Route::is('admin.config.other-discounts') ||
                                 Route::is('admin.config.tax') ||
                                 Route::is('admin.settings.charges') ||
                                 Route::is('admin.fixed-prices.*') ||
                                 Route::is('admin.discounts.*') ) active @endif">

                               <a href="{{ route('admin.config.index') }}">
                                   {{-- <i class="fa fa-gbp"></i>  --}}
                                   <span>Pricing</span>
                                   <span class="pull-right-container">
                                       <i class="fa fa-angle-left pull-right"></i>
                                   </span>
                               </a>
                               <ul class="treeview-menu">

                                   @permission('admin.settings.additional_charges.index')
                                   <li @if( Route::is('admin.config.additional-charges') ) class="active" @endif>
                                       <a href="{{ route('admin.config.additional-charges') }}">
                                           {{-- <i class="fa fa-circle-o"></i>  --}}
                                           <span>Additional Charges</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.settings.deposit_payments.index')
                                   <li @if( Route::is('admin.config.deposit-payments') ) class="active" @endif>
                                       <a href="{{ route('admin.config.deposit-payments') }}">
                                           {{-- <i class="fa fa-circle-o"></i>  --}}
                                           <span>Deposit Payments</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.settings.mileage_time.index')
                                   <li @if( Route::is('admin.config.mileage-time') ) class="active" @endif>
                                       <a href="{{ route('admin.config.mileage-time') }}">
                                           {{-- <i class="fa fa-circle-o"></i>  --}}
                                           <span>Distance & Time</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.settings.driver_income.index')
                                   <li @if( Route::is('admin.config.driver-income') ) class="active" @endif>
                                       <a href="{{ route('admin.config.driver-income') }}">
                                           {{-- <i class="fa fa-circle-o"></i> --}}
                                           <span>{{ trans('admin/index.menu.settings.driver_income') }}</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.fixed_prices.index')
                                   <li @if( Route::is('admin.fixed-prices.index') ) class="active" @endif>
                                       <a href="{{ route('admin.fixed-prices.index') }}">
                                           {{-- <i class="fa fa-circle-o"></i>  --}}
                                           <span>Fixed Prices</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.settings.holiday_surcharge.index')
                                   <li @if( Route::is('admin.config.holiday-surcharge') ) class="active" @endif>
                                       <a href="{{ route('admin.config.holiday-surcharge') }}">
                                           {{-- <i class="fa fa-circle-o"></i>  --}}
                                           <span>Holiday Surcharge</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.settings.night_surcharge.index')
                                   <li @if( Route::is('admin.config.night-surcharge') ) class="active" @endif>
                                       <a href="{{ route('admin.config.night-surcharge') }}">
                                           {{-- <i class="fa fa-circle-o"></i>  --}}
                                           <span>Night Surcharge</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.settings.other_discounts.index')
                                   <li @if( Route::is('admin.config.other-discounts') ) class="active" @endif>
                                       <a href="{{ route('admin.config.other-discounts') }}">
                                           {{-- <i class="fa fa-percent"></i>  --}}
                                           <span>Other Discounts</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.settings.charges.index')
                                   <li @if( Route::is('admin.settings.charges') ) class="active" @endif>
                                       <a href="{{ route('admin.settings.charges') }}">
                                           {{-- <i class="fa fa-circle-o"></i>  --}}
                                           <span>Parking Charge</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.settings.tax.index')
                                   <li @if( Route::is('admin.config.tax') ) class="active" @endif>
                                       <a href="{{ route('admin.config.tax') }}">
                                           {{-- <i class="fa fa-circle-o"></i>  --}}
                                           <span>Tax</span>
                                       </a>
                                   </li>
                                   @endpermission

                                   @permission('admin.discounts.index')
                                   <li @if( Route::is('admin.discounts.index') ) class="active" @endif>
                                       <a href="{{ route('admin.discounts.index') }}">
                                           {{-- <i class="fa fa-percent"></i>  --}}
                                           <span>Voucher Discounts</span>
                                       </a>
                                   </li>
                                   @endpermission
                               </ul>
                            </li>
                            @endpermission

                            @permission('admin.settings.airport_detection.index')
                            <li @if( Route::is('admin.config.airport-detection') ) class="active" @endif>
                                <a href="{{ route('admin.config.airport-detection') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Airport Detection</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.auto_dispatch.index')
                            @if (config('eto.allow_auto_dispatch') == 1)
                                <li @if( Route::is('admin.config.auto-dispatch') ) class="active" @endif>
                                    <a href="{{ route('admin.config.auto-dispatch') }}">
                                        {{-- <i class="fa fa-circle-o"></i>  --}}
                                        <span>Auto Dispatch</span>
                                    </a>
                                </li>
                            @endif
                            @endpermission

                            @permission('admin.backups.index')
                            @if (config('eto.allow_backups'))
                                <li @if( Route::is('backup.index') || Route::is('backup.*') ) class="active" @endif>
                                    <a href="{{ route('backup.index') }}">
                                        {{-- <i class="fa fa-database"></i>  --}}
                                        <span>Backups</span>
                                    </a>
                                </li>
                            @endif
                            @endpermission

                            @permission('admin.settings.booking.index')
                            <li @if( Route::is('admin.config.booking') ) class="active" @endif>
                                <a href="{{ route('admin.config.booking') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Booking</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.debug.index')
                            <li @if( Route::is('debug.*') ) class="active" @endif>
                                <a href="{{ route('debug.index') }}">
                                    {{-- <i class="fa fa-map-marker"></i>  --}}
                                    <span>{{ trans('admin/index.menu.settings.debug') }}</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.export.index')
                            @if (config('eto.allow_export'))
                                <li @if( Route::is('export.index') || Route::is('export.*') ) class="active" @endif>
                                    <a href="{{ route('export.index') }}">
                                        {{-- <i class="fa fa-download"></i>  --}}
                                        <span>Export</span>
                                    </a>
                                </li>
                            @endif
                            @endpermission

                            @permission('admin.settings.general.index')
                            <li @if( Route::is('admin.config.index') ) class="active" @endif>
                                <a href="{{ route('admin.config.index') }}">
                                    {{-- <i class="fa fa-list"></i>  --}}
                                    <span>General</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.getting_started.index')
                            <li @if( Route::is('admin.getting-started') ) class="active" @endif>
                                <a href="{{ route('admin.getting-started') }}">
                                    {{-- <i class="fa fa-mouse-pointer"></i>  --}}
                                    <span>Getting Started</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.google.index')
                            <li @if( Route::is('admin.config.google') ) class="active" @endif>
                                <a href="{{ route('admin.config.google') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Google</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.integration.index')
                            <li @if( Route::is('admin.config.integration') ) class="active" @endif>
                                <a href="{{ route('admin.config.integration') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Integration</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.invoices.index')
                            <li @if( Route::is('admin.config.invoices') ) class="active" @endif>
                                <a href="{{ route('admin.config.invoices') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Invoices</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.localization.index')
                            <li @if( Route::is('admin.config.localization') ) class="active" @endif>
                                <a href="{{ route('admin.config.localization') }}">
                                    {{-- <i class="fa fa-globe"></i>  --}}
                                    <span>Localization</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.categories.index')
                            <li @if( Route::is('admin.categories.index') ) class="active" @endif>
                                <a href="{{ route('admin.categories.index') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Location Categories</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.locations.index')
                            <li @if( Route::is('admin.locations.index') ) class="active" @endif>
                                <a href="{{ route('admin.locations.index') }}">
                                    {{-- <i class="fa fa-map-marker"></i>  --}}
                                    <span>Locations</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.meeting_points.index')
                            <li @if( Route::is('admin.meeting-points.index') ) class="active" @endif>
                                <a href="{{ route('admin.meeting-points.index') }}">
                                    {{-- <i class="fa fa-street-view"></i>  --}}
                                    <span>Meeting Points</span>
                                </a>
                            </li>
                            @endpermission

                            <!--@permission('admin.mobile_app.index')-->
                            <!--@if( !session('isMobileApp') )-->
                            <!--    <li @if( Route::is('admin.mobile-app') ) class="active" @endif>-->
                            <!--        <a href="{{ route('admin.mobile-app') }}">-->
                            <!--            {{-- <i class="fa fa-download"></i>  --}}-->
                            <!--            <span>{{ trans('admin/index.mobile_app') }}</span>-->

                            <!--            @if( !empty(config('site.expiry_driver_app')) )-->
                            <!--                <span class="pull-right-container">-->
                            <!--                    <small class="label pull-right bg-gray" style="padding: .2em .3em .2em .3em; color:red;"><i class="fa fa-exclamation"></i></small>-->
                            <!--                </span>-->
                            <!--            @endif-->
                            <!--        </a>-->
                            <!--    </li>-->
                            <!--@endif-->
                            <!--@endpermission-->

                            @permission('admin.settings.notifications.index')
                            <li @if( Route::is('admin.settings.notifications') ) class="active" @endif>
                                <a href="{{ route('admin.settings.notifications') }}">
                                    {{-- <i class="fa fa-bullhorn"></i>  --}}
                                    <span>Notifications</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.bases.index')
                            <li @if( Route::is('admin.config.bases') ) class="active" @endif>
                                <a href="{{ route('admin.config.bases') }}">
                                    {{-- <i class="fa fa-location-arrow"></i>  --}}
                                    <span>Operating Areas</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.payments.index')
                            <li @if( Route::is('admin.payments.index') ) class="active" @endif>
                                <a href="{{ route('admin.payments.index') }}">
                                    {{-- <i class="fa fa-credit-card"></i>  --}}
                                    <span>Payment Methods</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.excluded_routes.index')
                            <li @if( Route::is('admin.excluded-routes.index') ) class="active" @endif>
                                <a href="{{ route('admin.excluded-routes.index') }}">
                                    {{-- <i class="fa fa-lock"></i>  --}}
                                    <span>Restricted Areas</span>
                                </a>
                            </li>
                            @endpermission

                            @if (config('eto.allow_roles') && auth()->user()->hasPermission('admin.roles'))
                                <li @if( Route::is('roles.*') ) class="active" @endif>
                                    <a href="{{ route('roles.index') }}">
                                        {{-- <i class="fa fa-user-secret "></i>  --}}
                                        <span>{{ trans('roles.titles.roles') }}</span>
                                    </a>
                                </li>
                            @endif

                            @permission('admin.scheduled_routes.index')
                            <li class="@if( Route::is('admin.scheduled-routes.*') ) active @endif main-menu-scheduled-routes">
                                <a href="{{ route('admin.scheduled-routes.index') }}">
                                    {{-- <i class="fa fa-compass"></i>  --}}
                                    <span>Scheduled Routes</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.services.index')
                            <li class="@if( Route::is('admin.services.*') ) active @endif main-menu-services">
                                <a href="{{ route('admin.services.index') }}">
                                    {{-- <i class="fa fa-sliders"></i>  --}}
                                    <span>Services</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.logs.index')
                            @if (config('eto.allow_system_logs'))
                                <li @if( Route::is('logs') ) class="active" @endif>
                                    <a href="{{ route('logs') }}">
                                        {{-- <i class="fa fa-file-text-o "></i>  --}}
                                        <span>{{ trans('admin/index.menu.system_logs') }}</span>
                                    </a>
                                </li>
                            @endif
                            @endpermission

                            @permission('admin.settings.styles.index')
                            <li @if( Route::is('admin.config.styles') ) class="active" @endif>
                                <a href="{{ route('admin.config.styles') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Styles</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.translations.index')
                            @if (config('eto.allow_translations'))
                                <li @if( Route::is('translations.*') ) class="active" @endif>
                                   <a href="{{ route('translations.index') }}">
                                       {{-- <i class="fa fa-language"></i>  --}}
                                       <span>{{ trans('admin/index.menu.settings.translations') }}</span>
                                   </a>
                                </li>
                            @endif
                            @endpermission

                            @permission('admin.vehicle_types.index')
                            <li @if( Route::is('admin.vehicles-types.index') ) class="active" @endif>
                                <a href="{{ route('admin.vehicles-types.index') }}">
                                    {{-- <i class="fa fa-car"></i>  --}}
                                    <span>Types of Vehicles</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.users.index')
                            <li @if( Route::is('admin.config.users') ) class="active" @endif>
                                <a href="{{ route('admin.config.users') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Users</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.vehicles.index')
                            <li class="@if( Route::is('admin.vehicles.*') ) active @endif">
                                <a href="{{ route('admin.vehicles.index') }}">
                                    {{-- <i class="fa fa-car"></i>  --}}
                                    <span>Vehicles</span>
                                    <span class="pull-right-container">
                                        @if( $checkVehicleDocuments )
                                            <small class="label pull-right bg-gray" style="padding: .2em .3em .2em .3em;" data-toggle="tooltip" data-placement="bottom" title="{!! $checkVehicleDocuments !!}"><i class="fa fa-exclamation"></i></small>
                                        @endif
                                    </span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.settings.web_booking_widget.index')
                            <li @if( Route::is('admin.config.web-booking-widget') ) class="active" @endif>
                                <a href="{{ route('admin.config.web-booking-widget') }}">
                                    {{-- <i class="fa fa-circle-o"></i>  --}}
                                    <span>Web Booking Widget</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.web_widget.index')
                            <li @if( Route::is('admin.web-widget') ) class="active" @endif>
                                <a href="{{ route('admin.web-widget') }}">
                                    {{-- <i class="fa fa-plug"></i>  --}}
                                    <span>Web Widgets</span>
                                </a>
                            </li>
                            @endpermission

                            @permission('admin.zones.index')
                            <li @if( Route::is('admin.zones.*') ) class="active" @endif>
                                <a href="{{ route('admin.zones.index') }}">
                                    {{-- <i class="fa fa-map-marker"></i>  --}}
                                    <span>Zones</span>
                                </a>
                            </li>
                            @endpermission

                            {{--
                            <li @if( Route::is('admin.profiles.index') ) class="active" @endif>
                                <a href="{{ route('admin.profiles.index') }}">
                                    <i class="fa fa-files-o"></i> <span>Sites</span>
                                </a>
                            </li>
                            --}}
                        </ul>
                    </li>
                    @endpermission

                    <!--@permission('admin.documentation.index')-->
                    <!--<li>-->
                    <!--    <a href="{{ config('app.docs_url') }}" target="_blank">-->
                    <!--        {{-- <i class="fa fa-support"></i>  --}}-->
                    <!--        <span>Documentation</span>-->
                    <!--    </a>-->
                    <!--</li>-->
                    <!--@endpermission-->

                    @permission('admin.news.index')
                    @if (config('eto.allow_news'))
                        <li @if( Route::is('news.*') ) class="active" @endif>
                            <a href="{{ route('news.index') }}">
                                {{-- <i class="fa fa-newspaper-o"></i>  --}}
                                <span>{{ trans('admin/index.menu.news') }}</span>
                                 <span class="pull-right-container">
                                    <small class="label pull-right bg-green @if( config('eto.news.count') === 0 ) hide @endif" id="system-available-news">
                                        {{ config('eto.news.count') }}
                                    </small>
                                 </span>
                            </a>
                        </li>
                    @endif
                    @endpermission

                    <!--@permission('admin.subscription.index')-->
                    <!--<li @if( Route::is('subscription.*') ) class="active" @endif @if( config('eto.modules_access') == false ) style="display:none !important;" @endif>-->
                    <!--    <a href="{{ route('subscription.index') }}">-->
                    <!--        {{-- <i class="fa fa-id-card-o"></i>  --}}-->
                    <!--        <span>{{ trans('subscription.menu_label') }}</span>-->
                    <!--         <span class="pull-right-container">-->
                    <!--            <small class="label pull-right bg-red @if( request()->system->modules_new === 0 ) hide @endif" id="system-modules-new" title="New updates available">-->
                    <!--                {{ request()->system->modules_new }}-->
                    <!--            </small>-->
                    <!--         </span>-->
                    <!--    </a>-->
                    <!--</li>-->
                    <!--@endpermission-->

                    <li @if( Route::is('admin.account.*') ) class="active" @endif>
                        {{-- <a href="{{ route('admin.account.index') }}"> --}}
                        <a href="{{ route('admin.users.show', auth()->user()->id) }}">
                            {{-- <i class="fa fa-user"></i>  --}}
                            <span>Profile</span>
                        </a>
                    </li>

                    <li class="logout-container">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{-- <i class="fa fa-sign-out"></i>  --}}
                            <span>Sign Out</span>
                        </a>
                    </li>
                </ul>
                @else
                <ul class="sidebar-menu">
                    <li class="logout-container">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa fa-sign-out"></i> <span>Sign Out</span>
                        </a>
                    </li>
                </ul>
                @endif

                <div class="copyright-box">
                    &copy; {{ date('Y') }} <a href="https://techsoftsystem.com/" target="_blank">Tech Soft System</a>
                    {{-- <br><a href="{{ route('admin.license') }}">{{ trans('admin/index.licence') }}</a> <span class="divider">|</span> --}}
                </div>
            </section>
        </aside>

        <div class="content-wrapper @yield('subclass')">
            @if(request()->system->subscription->license_status == 'suspended')
                <div class="callout callout-danger license-suspended">
                    <h4>
                        <span class="fa-stack fa-lg" style="font-size:18px;">
                            <i class="fa fa-square-o fa-stack-2x"></i>
                            <i class="fa fa-exclamation fa-stack-1x"></i>
                        </span>
                        {{ trans('admin/index.alerts.license_suspended.title') }}
                    </h4>
                    <p>{!! \App\Helpers\SiteHelper::nl2br2(trans('admin/index.alerts.license_suspended.message')) !!}</p>
                </div>
            @elseif(request()->system->subscription->license_status == 'suspension_warning')
                <div class="callout callout-warning license-suspended">
                    <h4>
                        <span class="fa-stack fa-lg" style="font-size:18px;">
                            <i class="fa fa-square-o fa-stack-2x"></i>
                            <i class="fa fa-exclamation fa-stack-1x"></i>
                        </span>
                        {{ trans('admin/index.alerts.license_suspension_warning.title') }}
                    </h4>
                    @php
                    $days = 0;
                    if (!empty($request->system->subscription->license_status_expire_at)) {
                        $checkStatus = check_expire($request->system->subscription->license_status_expire_at);
                        if (!$checkStatus->isExpire) {
                            $days = $checkStatus->diff;
                        }
                    }
                    $days += 1;
                    @endphp
                    <p>{!! \App\Helpers\SiteHelper::nl2br2(trans('admin/index.alerts.license_suspension_warning.message', ['days' => $days])) !!}</p>
                </div>
            @endif

            <section class="content">
                @yield('subcontent')
            </section>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            {{ csrf_field() }}
        </form>
    </div>

    {{-- @include('admin.reminder') --}}
@stop

@section('footer')
    @if (config('eto.allow_reminders'))
        @include('admin.reminder')
    @endif

    <script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('plugins','jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset_url('plugins','fastclick/fastclick.min.js') }}"></script>
    <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
    <script src="{{ asset_url('js','app.js') }}?_dc={{ config('app.timestamp') }}"></script>

    @if (config('eto.allow_reminders'))
        <script src="{{ asset_url('js','reminder.js') }}?_dc={{ config('app.timestamp') }}"></script>
    @endif

    <script>
    @if (config('eto.allow_reminders'))
        window.reminder = {};
        window.reminder.lang = {!! \GuzzleHttp\json_encode(trans('admin/index.reminder')) !!};
        window.reminder.data = {!!\GuzzleHttp\json_encode((new \App\Http\Controllers\ReminderController)->getListJson(request(), true)) !!}
    @endif

    $(document).ready(function() {
        // Select
        $('.select2').select2({
            minimumResultsForSearch: 5
        });

        $('#switch_profile.select2').select2({
            dropdownParent: $('.switch-profile-box')
        });

        // Tooltip
        $('[title]').tooltip({
            placement: 'auto',
            container: 'body',
            selector: '',
            html: true,
            trigger: 'hover',
            delay: { show: 500, hide: 100 }
        });

        @if (count($sitesList) > 1)
            $('.switch-profile-box').show();
        @endif

        // Switch profile
        $('#switch_profile').on('change', function(e) {
            $.ajax({
                headers : {
                    'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                url: EasyTaxiOffice.appPath +'/etov2?apiType=backend',
                type: 'POST',
                dataType: 'json',
                cache: false,
                async: false,
                data: {
                    task: 'profile',
                    action: 'switch',
                    id: $(this).val()
                },
                success: function(response) {
                    if ( response.success ) {
                        window.location.reload();
                    }
                    else {
                        alert('Could not switch to this profile');
                    }
                },
                error: function(response) {
                    alert('An error occurred while processing your request');
                }
            });

            e.preventDefault();
        });
    });
    </script>

    @yield('subfooter')
@stop
