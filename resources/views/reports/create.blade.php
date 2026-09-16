@extends('admin.index')

@section('title', trans('reports.page_title'))
@section('subtitle', /*'<i class="fa fa-pie-chart"></i> '.*/ trans('reports.page_title') )
@section('subclass', '')

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
@endsection

@section('subcontent')

    <div class="box-header" style="left: -10px;">
        <h4 class="box-title">
            @if(request()->is('reports/fleet'))
                {{ trans('reports.titles.fleet') }}
            @elseif(request()->is('reports/driver'))
                {{ trans('reports.titles.driver') }}
            @elseif(request()->is('reports/customer'))
                {{ trans('reports.titles.customer') }}
            @elseif(request()->is('reports/payment'))
                {{ trans('reports.titles.payment') }}
            @endif
        </h4>

        <div class="box-tools pull-right" style="right: -9px;">
            @permission('admin.reports.index')
            <a href="{{ route('reports.index') }}?type={{ $type }}" class="btn btn-sm btn-default">
                <span>{{ trans('reports.button.saved_list') }}</span>
            </a>
            @endpermission
            @if($type == 'driver')
                <button type="button" class="btn btn-sm btn-default eto-btn-settings" data-toggle="modal" data-target=".eto-modal-settings" style="margin-left: 5px;">
                    <i class="fa fa-cogs"></i>
                </button>
            @endif
        </div>
    </div>
    <div class="pageContainer" id="reports">
        <div class="pageFilters">
            <form method="post" class="form-inline eto-report-form" action="{{ route('reports.generateJson', ['type'=>$type?:'driver']) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="form-group hidden">
                    <label for="service_id">{{ trans('reports.form.service') }}</label>
                    <select name="filters[service_id][]" id="service_id" multiple="multiple" size="1" class="form-control select2" data-placeholder="{{ trans('reports.form.service') }}" data-allow-clear="true">
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @if($service->selected === true) selected @endif>{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group hidden">
                    <label for="source">{{ trans('reports.form.source') }}</label>
                    <select name="filters[source][]" id="source" multiple="multiple" size="1" class="form-control select2" data-placeholder="{{ trans('reports.form.source') }}" data-allow-clear="true">
                        @foreach ($sources as $source)
                            <option value="{{ $source->id }}" @if($source->selected === true) selected @endif>{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group @if(!in_array($type, ['payment', 'driver', 'fleet'])) hidden @endif">
                    <label for="status">{{ trans('reports.form.status') }}</label>
                    <select name="filters[status][]" id="status" multiple="multiple" size="1" class="form-control select2" data-placeholder="{{ trans('reports.form.status') }}" data-allow-clear="true">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @if($status->selected === true) selected @endif>{{ $status->text }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group @if(!in_array($type, ['payment'])) hidden @endif">
                    <label for="payment_method">{{ trans('reports.form.payment_method') }}</label>
                    <select name="filters[payment_method][]" id="payment_method" multiple="multiple" size="1" class="form-control select2" data-placeholder="{{ trans('reports.form.payment_method') }}" data-allow-clear="true">
                        @foreach ($paymentMethods as $paymentMethod)
                            <option value="{{ $paymentMethod->method }}" @if($paymentMethod->selected === true) selected @endif>{{ $paymentMethod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group @if(!in_array($type, ['payment'])) hidden @endif">
                    <label for="payment_status">{{ trans('reports.form.payment_status') }}</label>
                    <select name="filters[payment_status][]" id="payment_status" multiple="multiple" size="1" class="form-control select2" data-placeholder="{{ trans('reports.form.payment_status') }}" data-allow-clear="true">
                        @foreach ($paymentStatus as $status)
                            <option value="{{ $status->value }}" @if($status->selected === true) selected @endif>{{ $status->text }}</option>
                        @endforeach
                    </select>
                </div>
                @if (config('eto.allow_fleet_operator'))
                <div class="form-group @if(!in_array($type, ['fleet'])) hidden @endif">
                    <label for="driver-name">{{ trans('reports.form.driver') }}</label>
                    <select name="filters[fleets][]" id="driver-name" multiple="multiple" size="1" class="form-control select2" data-placeholder="{{ trans('reports.form.fleet') }}" data-allow-clear="true">
                        @foreach ($fleets as $fleet)
                            <option value="{{ $fleet->id }}" @if($fleet->selected === true) selected @endif>{{ $fleet->getName(true) }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="form-group @if(!in_array($type, ['driver'])) hidden @endif">
                    <label for="driver-name">{{ trans('reports.form.driver') }}</label>
                    <select name="filters[drivers][]" id="driver-name" multiple="multiple" size="1" class="form-control select2" data-placeholder="{{ trans('reports.form.driver') }}" data-allow-clear="true">
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" @if($driver->selected === true) selected @endif>{{ $driver->getName(true) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group @if(!in_array($type, ['customer'])) hidden @endif">
                    <label for="customer-name">{{ trans('reports.form.customer') }}</label>
                    <select name="filters[customers][]" id="customer-name" multiple="multiple" size="1" class="form-control select2" data-placeholder="{{ trans('reports.form.customer') }}" data-allow-clear="true">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @if($customer->selected === true) selected @endif>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="start-date">{{ trans('reports.form.from_date') }}</label>
                    <input type="text" name="filters[from_date]" id="filter-start-date" class="form-control datepicker" placeholder="{{ trans('reports.form.from_date') }}" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="end-date">{{ trans('reports.form.to_date') }}</label>
                    <input type="text" name="filters[to_date]" id="end-date" class="form-control datepicker" placeholder="{{ trans('reports.form.to_date') }}" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="date-type">{{ trans('reports.form.date_type') }}</label>
                    <select name="filters[date_type]" id="date-type" class="form-control select2" data-placeholder="{{ trans('reports.form.date_type') }}" data-allow-clear="false">
                        @foreach ($dateTypes as $dateType)
                            <option value="{{ $dateType->id }}" @if($dateType->selected === true) selected @endif>{{ $dateType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group hidden">
                    <label for="keywords">{{ trans('reports.form.keywords') }}</label>
                    <input type="text" name="filters[search]" id="keywords" class="form-control" placeholder="{{ trans('reports.form.keywords') }}">
                </div>

                <div class="form-group  hidden">
                    <label for="scheduled_route">{{ trans('reports.form.scheduled_route') }}</label>
                    <input type="text" name="filters[scheduled_route]" id="scheduled_route" class="form-control" placeholder="{{ trans('reports.form.scheduled_route') }}">
                </div>
                <div class="form-group  hidden">
                    <label for="parent_booking">{{ trans('reports.form.parent_booking') }}</label>
                    <input type="text" name="filters[parent_booking]" id="parent_booking" class="form-control" placeholder="{{ trans('reports.form.parent_booking') }}">
                </div>
                <div class="form-group hidden">
                    <label for="booking_type">{{ trans('reports.form.booking_type') }}</label>
                    <select name="filters[booking_type][]" id="booking_type" multiple="multiple" size="1" class="form-control select2" data-placeholder="{{ trans('reports.form.booking_type') }}" data-allow-clear="true">
                        <option value="parent">{{ trans('reports.form.booking_type_parent') }}</option>
                        <option value="child">{{ trans('reports.form.booking_type_child') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-default eto-btn-generate" title=""><span>{{ trans('reports.form.generate_report') }}</span></button>
                    <button type="button" class="btn btn-default eto-btn-reset" title=""><span>{{ trans('reports.form.reset') }}</span></button>
                    </div>
            </form>
        </div>
        <div class="eto-show-report"></div>
        <div class="eto-report-actions">
            <div class="btn-group eto-btn-report-befor-save hidden">
                <button type="button" class="btn btn-sm btn-default eto-btn-save-report" title="">
                    <span>{{ trans('reports.form.save_report') }}</span>
                </button>
                <div class="btn-group dropup eto-btn-group-send" role="group">
                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu dropup" role="menu">
                        <li class="send-report-links hidden">
                            <button class="btn btn-sm btn-link eto-btn-save-report eto-btn-send-after-save" type="button">
                                <span>{{ trans('reports.button.save_send_report_to_all_'.$type) }}</span>
                            </button>
                        </li>
                        <li class="send-report-links hidden">
                            <button class="btn btn-sm btn-link eto-btn-send-report-all" type="button">
                                <span>{{ trans('reports.button.send_report_to_all_'.$type) }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="btn-group eto-btn-export-actions hidden">
                <a href="{{ route('reports.exportAll', ['format'=>'xlsx']) }}" class="btn btn-sm btn-default eto-btn-export">
                    <span>{{ trans('reports.button.export_all') }}</span>
                </a>
                <div class="btn-group dropup" role="group">
                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="{{ route('reports.exportAll', ['format'=>'xlsx']) }}" class="btn btn-sm btn-link eto-btn-export">
                                <span>{{ trans('reports.button.export_xlsx') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.exportAll', ['format'=>'xls']) }}" class="btn btn-sm btn-link eto-btn-export">
                                <span>{{ trans('reports.button.export_xls') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.exportAll', ['format'=>'pdf']) }}" class="btn btn-sm btn-link eto-btn-export">
                                <span>{{ trans('reports.button.export_pdf') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="eto-report-invalid-bookings hidden"></div>
        <div class="eto-charts"></div>
        <div class=" eto-no-data hidden">
            {{ trans('reports.no_data_available') }}
        </div>
    </div>

    @include('reports.settings')
@stop

@section('subfooter')
    <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset_url('js','eto/eto-report.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script>
    $(document).ready(function(){
        if (typeof ETO.Report != "undefined") {
            if (typeof ETO.Report.init != "undefined") {
                ETO.Report.init({
                    typeReport: '{{ $type }}',
                });
            }
        }

        @if(request()->system->subscription->license_status == 'suspended')
            $('#reports').prepend('<div class="license-suspended-block"></div>');
        @endif
    });
    </script>
@stop
