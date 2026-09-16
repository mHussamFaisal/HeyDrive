<div class="eto-modal-form-settings modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4>Form settings</h4>
            </div>
            <div class="modal-body">
                <div class="row form-group clearfix">
                    <label for="advance_open" class="col-sm-10 control-label">
                        {{ trans('booking.settingAdvanceOpen') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="advance_open" class="onoffswitch-input eto-settings-advance_open" type="checkbox" value="1" data-eto-relation="user" data-eto-key="eto_booking.form.view.advance_open">
                            <label class="onoffswitch-label" for="advance_open"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="amounts_view_passenger" class="col-sm-10 control-label">
                        {{ trans('booking.settingAmountsViewPassenger') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="amounts_view_passenger" class="onoffswitch-input eto-settings-amounts_view_passenger" type="checkbox" value="1" data-eto-relation="user" data-eto-key="eto_booking.form.view.amounts_view_passenger">
                            <label class="onoffswitch-label" for="amounts_view_passenger"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="amounts_view_suitcase" class="col-sm-10 control-label">
                        {{ trans('booking.settingAmountsViewSuitcase') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="amounts_view_suitcase" class="onoffswitch-input eto-settings-amounts_view_suitcase" type="checkbox" value="1" data-eto-relation="user" data-eto-key="eto_booking.form.view.amounts_view_suitcase">
                            <label class="onoffswitch-label" for="amounts_view_suitcase"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="amounts_view_carry_on" class="col-sm-10 control-label">
                        {{ trans('booking.settingAmountsViewCarryOn') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="amounts_view_carry_on" class="onoffswitch-input eto-settings-amounts_view_carry_on" type="checkbox" value="1" data-eto-relation="user" data-eto-key="eto_booking.form.view.amounts_view_carry_on">
                            <label class="onoffswitch-label" for="amounts_view_carry_on"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="send_notification" class="col-sm-10 control-label">
                        {{ trans('booking.bookingNotificationsPlaceholder') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="send_notification" class="onoffswitch-input eto-settings-send_notification" type="checkbox" value="1" data-eto-relation="user" data-eto-key="eto_booking.form.checked.send_notification">
                            <label class="onoffswitch-label" for="send_notification"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="waiting_time" class="col-sm-10 control-label">
                        {{ trans('booking.settingWaitingTime') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="waiting_time" class="onoffswitch-input eto-settings-waiting_time" type="checkbox" value="1" data-eto-relation="user" data-eto-key="eto_booking.form.view.waiting_time">
                            <label class="onoffswitch-label" for="waiting_time"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="instant_dispatch_color_system" class="col-sm-10 control-label">
                        {{ trans('booking.instantDispatchColorSystem') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="instant_dispatch_color_system" class="onoffswitch-input eto-settings-instant_dispatch_color_system" type="checkbox" value="1" data-eto-relation="user" data-eto-key="eto_booking.form.view.instant_dispatch_color_system">
                            <label class="onoffswitch-label" for="instant_dispatch_color_system"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="show_inactive_drivers_form" class="col-sm-10 control-label">
                        {{ trans('booking.show_inactive_drivers') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="show_inactive_drivers_form" class="onoffswitch-input eto-settings-show_inactive_drivers_form" type="checkbox" value="1" data-eto-relation="user" data-eto-key="eto_booking.form.view.show_inactive_drivers_form">
                            <label class="onoffswitch-label" for="show_inactive_drivers_form"></label>
                        </div>
                    </div>
                </div>

                <div class="row form-group clearfix">
                    <label for="statusColorSettings" class="col-sm-10 control-label">
                        {{ trans('booking.status_color_settings') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="statusColorSettings" class="onoffswitch-input eto-settings-statusColorSettings" type="checkbox" value="1">
                            <label class="onoffswitch-label" for="statusColorSettings"></label>
                        </div>
                    </div>
                </div>
                <div class="eto-color-settings hidden">
                    @foreach ((new \App\Models\BookingRoute)->options->status as $key=>$status)
                    <div class="row form-group clearfix">
                        <label for="status_color_{{ $key }}" class="col-sm-8 control-label">
                            {{ $status['name'] }}
                        </label>
                        <div class="col-sm-4">
                            <span class="pull-right">
                                <input type="text" id="status_color_{{ $key }}" value="" class="form-control colorpicker eto-settings-status_color eto-settings-status_color_{{ $key }}" data-eto-relation="subscription" data-eto-key="eto_booking.status_color.{{ $key }}" style="width:115px;">
                            </span>
                            <span class="eto-color-btn-clear pull-right hidden" data-eto-status="{{ $key }}" title="Reset">
                                <i class="fa fa-trash-o"></i>
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="row form-group clearfix">
                    <label for="custom_field.display" class="col-sm-10 control-label">
                        {{ trans('booking.custom_field_display') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="custom_field.display" class="onoffswitch-input eto-settings-custom_field_display" type="checkbox" value="1" data-eto-relation="subscription" data-eto-key="eto_booking.custom_field.display">
                            <label class="onoffswitch-label" for="custom_field.display"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="custom_field.name" class="col-sm-6 control-label">
                        {{ trans('booking.custom_field_name') }}<i class="ion-ios-information-outline" style="font-size:18px; line-height:22px; margin-top:5px;margin-left: 5px;" data-toggle="popover" data-title="{{ trans('booking.custom_field_name') }}" data-content="<div style='max-width:350px; line-height:20px;'>{{ trans('booking.custom_field_info') }}</div>"></i>
                    </label>
                    <div class="col-sm-6">
                        <input id="custom_field.name" class="form-control pull-right eto-settings-custom_field_name" type="text" placeholder="{{ trans('booking.custom_field_name') }}" data-eto-relation="subscription" data-eto-key="eto_booking.custom_field.name" style="width: 150px;float: right;">
                    </div>
                </div>
                <div class="row form-group clearfix hidden">
                    <label for="add_vehicle_button" class="col-sm-10 control-label">
                        {{ trans('booking.addVehicleButton') }}
                    </label>
                    <div class="col-sm-2">
                        <div class="onoffswitch pull-right">
                            <input id="add_vehicle_button" class="onoffswitch-input eto-settings-add_vehicle_button" type="checkbox" value="1" data-eto-relation="subscription" data-eto-ke="eto_booking.form.add_vehicle_button">
                            <label class="onoffswitch-label" for="add_vehicle_button"></label>
                        </div>
                    </div>
                </div>
            </div>
            {{--<div class="modal-footer">--}}
            {{--<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>--}}
            {{--</div>--}}
        </div>
    </div>
</div>
