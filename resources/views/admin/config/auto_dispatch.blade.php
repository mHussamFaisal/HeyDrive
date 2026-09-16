
<input type="hidden" name="settings_group" id="settings_group" value="auto_dispatch">

<div style="margin-bottom:2px;">Auto dispatch</div>
<div class="form-group field-enable_autodispatch field-size-fw">
    <div class="radio" style="margin-top:0;">
        <label for="enable_autodispatch_1" class="checkbox-inline">
            <input type="radio" name="enable_autodispatch" id="enable_autodispatch_1" value="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> Active
        </label>
        <label for="enable_autodispatch_0" class="checkbox-inline">
            <input type="radio" name="enable_autodispatch" id="enable_autodispatch_0" value="0" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> Inactive
        </label>
    </div>
</div>

<div style="margin-top:15px; margin-bottom:2px;">Auto dispatch a job to a driver (x) minutes before the pick-up time</div>
<div class="form-group field-time_to_assign field-size-lg" style="max-width:120px;">
    {{-- <label for="time_to_assign">Auto assing driver (x) minutes before the pick-up time</label> --}}
    <input type="number" name="time_to_assign" id="time_to_assign" placeholder="0" required class="form-control" min="0" step="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission>
</div>

<div style="margin-top:15px; margin-bottom:2px;">No auto dispatching (x) minutes before the pick-up time</div>
<div class="form-group field-extra_time_slot field-size-lg" style="max-width:120px;">
    {{-- <label for="extra_time_slot">Add extra time slot (x) minutes before and after pick-up time</label> --}}
    <input type="number" name="extra_time_slot" id="extra_time_slot" placeholder="0" required class="form-control" min="0" step="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission>
</div>

<div style="margin-top:15px; margin-bottom:2px;">Find a driver in radius (km/mi)</div>
<div class="form-group field-check_within_radius field-size-lg" style="max-width:120px;">
    {{-- <label for="check_within_radius">Find driver in radius (km/mi)</label> --}}
    <input type="number" name="check_within_radius" id="check_within_radius" placeholder="0" required class="form-control" min="0" step="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission>
</div>

<div style="margin-top:15px; margin-bottom:2px;">Amount of time drive has to confirm the job (minutes)</div>
<div class="form-group field-time_to_confirm field-size-lg" style="max-width:120px;">
    {{-- <label for="time_to_confirm">Driver has (x) minutes to confirm the job</label> --}}
    <input type="number" name="time_to_confirm" id="time_to_confirm" placeholder="0" required class="form-control" min="0" step="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission>
</div>

<div style="margin-top:15px; margin-bottom:2px;">Allow auto dispatching to driver with Unavailable status</div>
<div class="form-group field-check_availability_status field-size-fw">
    <div class="radio" style="margin-top:0;">
        <label for="check_availability_status_0" class="checkbox-inline">
            <input type="radio" name="check_availability_status" id="check_availability_status_0" value="0" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> Yes
        </label>
        <label for="check_availability_status_1" class="checkbox-inline">
            <input type="radio" name="check_availability_status" id="check_availability_status_1" value="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> No
        </label>
    </div>
</div>

<div style="margin-top:15px; margin-bottom:2px;">Only auto dispatch booking with Auto-dispatch booking status</div>
<div class="form-group field-only_auto_dispatch_status field-size-fw">
    <div class="radio" style="margin-top:0;">
        <label for="only_auto_dispatch_status_1" class="checkbox-inline">
            <input type="radio" name="only_auto_dispatch_status" id="only_auto_dispatch_status_1" value="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> Yes
        </label>
        <label for="only_auto_dispatch_status_0" class="checkbox-inline">
            <input type="radio" name="only_auto_dispatch_status" id="only_auto_dispatch_status_0" value="0" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> No
        </label>
    </div>
</div>

{{--
<div class="form-group field-time_every_minute field-size-fw">
    <label for="time_every_minute" class="checkbox-inline">
        <input type="checkbox" name="time_every_minute" id="time_every_minute" value="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> Dispaly booking form time picker every 1 minute (default 5 minutes)
    </label>
</div>

<div class="form-group field-time_last_seen field-size-lg">
    <label for="time_last_seen">Driver has to be active for (x) minutes to get a job (Good for food deliveries)</label>
    <input type="number" name="time_last_seen" id="time_last_seen" placeholder="Driver has to be active for (x) minutes to get a job (Good for food deliveries)" required class="form-control" min="0" step="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission>
</div>

<div class="form-group field-check_trashed field-size-fw">
    <label for="check_trashed" class="checkbox-inline">
        <input type="checkbox" name="check_trashed" id="check_trashed" value="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> Check trashed requests
    </label>
</div>

<div class="form-group field-delete_expired field-size-fw">
    <label for="delete_expired" class="checkbox-inline">
        <input type="checkbox" name="delete_expired" id="delete_expired" value="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> Delete expired job requests
    </label>
</div>

<div class="form-group field-assign_max_drivers field-size-lg">
    <label for="assign_max_drivers">Assign max drivers per booking</label>
    <input type="number" name="assign_max_drivers" id="assign_max_drivers" placeholder="Assign max drivers per booking" required class="form-control" min="0" step="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission>
</div>

<div class="form-group field-assign_driver_on_status_change field-size-fw">
    <label for="assign_driver_on_status_change" class="checkbox-inline">
        <input type="checkbox" name="assign_driver_on_status_change" id="assign_driver_on_status_change" value="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> Assign driver on status change
    </label>
</div>

<div class="form-group field-assign_driver_on_reject field-size-fw">
    <label for="assign_driver_on_reject" class="checkbox-inline">
        <input type="checkbox" name="assign_driver_on_reject" id="assign_driver_on_reject" value="1" @permission('admin.settings.auto_dispatch.edit')@else readonly @endpermission> Assign driver on reject status
    </label>
</div>
--}}
