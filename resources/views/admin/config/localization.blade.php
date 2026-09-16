
<input type="hidden" name="settings_group" id="settings_group" value="localization">

<div class="form-group field-language">
    <label for="language">Default language</label>
    <select name="language" id="language" required class="form-control" @permission('admin.settings.localization.edit')@else readonly @endpermission>
    @foreach (config('app.locales') as $lang => $language)
        <option value="{{ $lang }}">{{ $language['name'] }}</option>
    @endforeach
    </select>
</div>

<div class="form-group field-locale_switcher_enabled field-size-fw">
    <label for="locale_switcher_enabled" class="checkbox-inline">
        <input type="checkbox" name="locale_switcher_enabled" id="locale_switcher_enabled" value="1" @permission('admin.settings.localization.edit')@else readonly @endpermission> Enable language switcher
    </label>
</div>

<div class="locale-switcher-container" style="display:none; margin-bottom:20px;">
    <div class="form-group field-locale_switcher_style">
        <label for="locale_switcher_style">Style</label>
        <select name="locale_switcher_style" id="locale_switcher_style" required class="form-control" @permission('admin.settings.localization.edit')@else readonly @endpermission>
        <option value="dropdown">Dropdown</option>
        <option value="inline">Horizontal</option>
        </select>
    </div>

    <div class="form-group field-locale_switcher_display">
        <label for="locale_switcher_display">Display</label>
        <select name="locale_switcher_display" id="locale_switcher_display" required class="form-control" @permission('admin.settings.localization.edit')@else readonly @endpermission>
        <option value="names_flags">Names & Flags</option>
        <option value="names">Names</option>
        <option value="flags">Flags</option>
        </select>
    </div>

    <div class="form-group field-locale_switcher_display_name_code field-size-fw">
        <label for="locale_switcher_display_name_code" class="checkbox-inline">
            <input type="checkbox" name="locale_switcher_display_name_code" id="locale_switcher_display_name_code" value="1" @permission('admin.settings.localization.edit')@else readonly @endpermission> Show names as language code
        </label>
    </div>

    <div style="margin-top:10px; margin-bottom:5px;">Active languages:</div>
    @foreach (config('app.locales') as $lang => $language)
        <div class="form-group field-locale_active field-size-fw" style="margin-bottom:5px;">
            <label for="locale_active_{{ $loop->iteration }}" class="checkbox-inline">
                <input type="checkbox" name="locale_active[]" id="locale_active_{{ $loop->iteration }}" value="{{ $lang }}" @if( in_array($lang, config('app.locale_active')) ) checked @endif  @permission('admin.settings.localization.edit')@else readonly @endpermission>
                <img src="{{ asset_url('images','flags/'. $lang .'.png') }}" style="width:24px; margin-top:-2px; margin-right:4px;" />
                <span title="{{ $language['native'] }}">{{ $language['name'] }}</span>
            </label>
        </div>
    @endforeach
</div>

<div style="margin-top:20px;" class="clearfix"></div>

@php
    $timezones = \App\Helpers\SiteHelper::getTimezoneList('group');
    $exampleDateTime = Carbon\Carbon::parse('2020-06-20 15:00:00');
@endphp

<div class="form-group field-timezone">
    <label for="timezone">Timezone</label>
    <select name="timezone" id="timezone" data-placeholder="Timezone" required class="form-control select2"data-minimum-results-for-search="10"  @permission('admin.settings.localization.edit')@else readonly @endpermission>
    @foreach ($timezones as $region => $list)
        <optgroup label="{{ $region }}">
            @foreach ($list as $timezone => $name)
                <option value="{{ $timezone }}">{{ $name }}</option>
            @endforeach
        <optgroup>
    @endforeach
    </select>
</div>

<div class="form-group field-date_format">
    <label for="date_format">Date format</label>
    <select name="date_format" id="date_format" data-placeholder="Date format" required class="form-control" @permission('admin.settings.localization.edit')@else readonly @endpermission>
    <option value="jS F Y">{{ $exampleDateTime->format('jS F Y') }}</option>
    <option value="j F Y">{{ $exampleDateTime->format('j F Y') }}</option>
    <option value="jS M Y">{{ $exampleDateTime->format('jS M Y') }}</option>
    <option value="j M Y">{{ $exampleDateTime->format('j M Y') }}</option>
    <option value="Y/m/d">{{ $exampleDateTime->format('Y/m/d') }}</option>
    <option value="d/m/Y">{{ $exampleDateTime->format('d/m/Y') }}</option>
    <option value="m/d/Y">{{ $exampleDateTime->format('m/d/Y') }}</option>
    <option value="Y-m-d">{{ $exampleDateTime->format('Y-m-d') }}</option>
    <option value="d-m-Y">{{ $exampleDateTime->format('d-m-Y') }}</option>
    <option value="m-d-Y">{{ $exampleDateTime->format('m-d-Y') }}</option>
    <option value="Y.m.d">{{ $exampleDateTime->format('Y.m.d') }}</option>
    <option value="d.m.Y">{{ $exampleDateTime->format('d.m.Y') }}</option>
    <option value="m.d.Y">{{ $exampleDateTime->format('m.d.Y') }}</option>
    </select>
</div>

<div class="form-group field-time_format">
    <label for="time_format">Time format</label>
    <select name="time_format" id="time_format" data-placeholder="Time format" required class="form-control" @permission('admin.settings.localization.edit')@else readonly @endpermission>
        <option value="g:i a">{{ $exampleDateTime->format('g:i a') }}</option>
        <option value="g:i A">{{ $exampleDateTime->format('g:i A') }}</option>
        <option value="H:i">{{ $exampleDateTime->format('H:i') }} (24h)</option>
    </select>
</div>

<div class="form-group field-start_of_week">
    <label for="start_of_week">Week starts on</label>
    <select name="start_of_week" id="start_of_week" data-placeholder="Week starts on" required class="form-control" @permission('admin.settings.localization.edit')@else readonly @endpermission>
        <option value="0">Sunday</option>
        <option value="1">Monday</option>
        <option value="2">Tuesday</option>
        <option value="3">Wednesday</option>
        <option value="4">Thursday</option>
        <option value="5">Friday</option>
        <option value="6">Saturday</option>
    </select>
</div>
