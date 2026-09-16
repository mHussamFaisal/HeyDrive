<div class="nav-tabs-custom nav-tabs-no-borders">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#account" data-toggle="tab" aria-expanded="false">
                {{ trans('admin/users.tab.account') }}
            </a>
        </li>
        <li>
            <a href="#profile" data-toggle="tab" aria-expanded="true">
                {{ trans('admin/users.tab.profile') }}
            </a>
        </li>
        <li class="user-tab-link-other">
            <a href="#other" data-toggle="tab" aria-expanded="true">
                {{ trans('admin/users.tab.other') }}
            </a>
        </li>
    </ul>

    <div class="tab-content" style="margin-top:10px;">
        <div class="tab-pane active" id="account">

            <div class="row">
                <div class="col-md-12 driver-only1">

                    <div class="form-group has-feedback{{ $errors->has('name') ? ' has-error' : '' }}">
                        {!! Form::label('name', trans('admin/users.display_name')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('name', old('name', $mode == 'edit' ? $user->getOriginalForm('name') : null), ['id' => 'name', 'class' => 'form-control', 'placeholder' => trans('admin/users.display_name'), 'required']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-user"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('name') )
                            <span class="help-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6 driver-only2" style="display:none;">

                    <div class="form-group has-feedback{{ $errors->has('profile.unique_id') ? ' has-error' : '' }}">
                        {!! Form::label('profile.unique_id', trans('admin/users.unique_id')) !!}
                        {{-- <div class="input-group" title="{{ trans('admin/users.help.unique_id') }}"> --}}
                            {!! Form::text('profile[unique_id]', old('profile.unique_id'), ['id' => 'profile.unique_id', 'class' => 'form-control', 'placeholder' => trans('admin/users.unique_id')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.unique_id') )
                            <span class="help-block">{{ $errors->first('profile.unique_id') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('username') ? ' has-error' : '' }}">
                        {!! Form::label('username', trans('admin/users.username')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('username', old('username', $mode == 'create' ? uniqid('user') : null), ['id' => 'username', 'class' => 'form-control', 'placeholder' => trans('admin/users.username'), 'required']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('username') )
                            <span class="help-block">{{ $errors->first('username') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('email') ? ' has-error' : '' }}">
                        {!! Form::label('email', trans('admin/users.email')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::email('email', old('email'), ['id' => 'email', 'class' => 'form-control', 'placeholder' => trans('admin/users.email'), 'required']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-envelope"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('email') )
                            <span class="help-block">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('password') ? ' has-error' : '' }}">
                        {!! Form::label('password', trans('admin/users.password')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::password('password', ['id' => 'password', 'class' => 'form-control', 'placeholder' => trans('admin/users.password'), 'autocomplete' => 'new-password']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-lock"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('password') )
                            <span class="help-block">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                        {!! Form::label('password_confirmation', trans('admin/users.password_confirmation')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::password('password_confirmation', ['id' => 'password_confirmation', 'class' => 'form-control', 'placeholder' => trans('admin/users.password_confirmation'), 'autocomplete' => 'new-password']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-lock"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('password_confirmation') )
                            <span class="help-block">{{ $errors->first('password_confirmation') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                @if( !empty($user->avatar) )
                    <div class="col-md-2">
                        <img src="{{ asset( $user->getAvatarPath() ) }}" class="img-circle" alt="" style="max-width:100px; max-height:100px; margin-bottom:20px;">
                    </div>
                @endif
                <div class="@if( !empty($user->avatar) ) col-md-10 @else col-md-12 @endif">

                    @if( !empty($user->avatar) )
                        <div class="form-group has-feedback{{ $errors->has('avatar_delete') ? ' has-error' : '' }} placeholder-disabled">
                            <div class="checkbox">
                              <label>
                                {!! Form::checkbox('avatar_delete', 1, old('avatar_delete', $mode == 'create' ? true : null)) !!} {{ trans('admin/users.avatar_delete') }}
                              </label>
                            </div>
                            @if( $errors->has('avatar_delete') )
                                <span class="help-block">{{ $errors->first('avatar_delete') }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="form-group has-feedback{{ $errors->has('avatar') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('avatar', trans('admin/users.avatar_upload')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::file('avatar', ['id' => 'avatar', 'class' => 'form-control']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-upload"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('avatar') )
                            <span class="help-block">{{ $errors->first('avatar') }}</span>
                        @endif
                    </div>

                </div>
            </div>

            @php
            $allowRoleEdit = empty($user->roles) ? 1 : 0;
            $allowStatusEdit = (!($mode == 'edit' && !empty($user->id) && $user->id == auth()->user()->id) || auth()->user()->hasRole('admin.root')) ? 1 : 0;
            @endphp

            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('role') ? ' has-error' : '' }}">
                        {!! Form::label('role', trans('admin/users.role')) !!}

                        @if (!$allowRoleEdit)
                            <div class="form-control" style="padding-top:11px; color:#888;">
                                @php
                                $userRoles = [];
                                foreach ($user->getRoles() as $role) {
                                    $userRoles[] = $role->getName();
                                }
                                $userRoles = implode(', ', $userRoles);
                                @endphp
                                {{ $userRoles }}
                            </div>
                        @endif

                        <div class="input-group1 @if(!$allowRoleEdit) hidden @endif">
                            <select id="role" class="form-control select2" data-placeholder="{{ trans('admin/users.role') }}" required name="role[]" >
                                <option></option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @if (
                                        ($mode == 'create' && empty(old('role')) && !empty(request('role')) && (
                                            $role->slug == request('role') ||
                                            $role->slug == request('role') .'.root')
                                        )
                                        || (!empty($user) && $user->hasRole($role->slug))
                                        || (!empty(old('role')) && in_array($role->id, old('role')))
                                    ) selected="selected" @endif>{{ $role->getName() }}</option>
                                @endforeach
                            </select>
                            {{-- <span class="input-group-addon"><i class="fa fa-universal-access"></i></span> --}}
                       </div>
                        @if( $errors->has('role') )
                            <span class="help-block">{{ $errors->first('role') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('status') ? ' has-error' : '' }}">
                        {!! Form::label('status', trans('admin/users.status')) !!}

                        @if (!$allowStatusEdit)
                            <div class="form-control" style="padding-top:11px; color:#888;">
                                {{ $user->getStatus() }}
                            </div>
                        @endif

                        <div class="input-group1 @if(!$allowStatusEdit) hidden @endif">
                            {!! Form::select('status', $status, old('status', $mode == 'create' ? 'approved' : null), ['id' => 'status', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/users.status'), 'required']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-eye"></i></span> --}}
                        </div>
                        @if( $errors->has('status') )
                            <span class="help-block">{{ $errors->first('status') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('settings.locale') ? ' has-error' : '' }}">
                        {!! Form::label('settings.locale', trans('admin/users.locale')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::select('settings[locale]', $locales, old('settings.locale', $mode == 'edit' && $user->getSetting('app.locale') !== null ? $user->getSetting('app.locale') : config('app.locale')), ['id' => 'settings.locale', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/users.locale')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-language"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('settings.locale') )
                            <span class="help-block">{{ $errors->first('settings.locale') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('settings.timezone') ? ' has-error' : '' }}">
                        {!! Form::label('settings.timezone', trans('admin/users.timezone')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::select('settings[timezone]', $timezoneList, old('settings.timezone', $mode == 'edit' && $user->getSetting('app.timezone') !== null ? $user->getSetting('app.timezone') : config('app.timezone')), ['id' => 'settings.timezone', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/users.timezone')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-clock-o"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('settings.timezone') )
                            <span class="help-block">{{ $errors->first('settings.timezone') }}</span>
                        @endif
                    </div>

                </div>
            </div>

            @if (config('eto.allow_teams'))
            <div class="teams_container form-group has-feedback{{ $errors->has('teams') ? ' has-error' : '' }}">
                {!! Form::label('teams', trans('admin/users.teams')) !!}
                {!! Form::select('teams[]', $teamsList, old('teams', $mode == 'edit' ? $user->teams->pluck('id')->toArray() : []), ['multiple' => 'multiple', 'id' => 'teams', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/users.teams')]) !!}
                @if ($errors->has('teams'))
                    <span class="help-block">{{ $errors->first('teams') }}</span>
                @endif
            </div>
            @endif

            @if (!empty($fleets))
                @role('admin.fleet_operator')
                    <input type="hidden" name="fleet_id" value="{{ auth()->user()->id }}">
                @else
                    <div class="fleet_container form-group has-feedback{{ $errors->has('fleet_id') ? ' has-error' : '' }}">
                        {!! Form::label('fleet_id', trans('admin/users.fleet_id')) !!}
                        {!! Form::select('fleet_id', $fleets, old('fleet_id'), ['id' => 'fleet_id', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/users.fleet_id')]) !!}
                        @if( $errors->has('fleet_id') )
                            <span class="help-block">{{ $errors->first('fleet_id') }}</span>
                        @endif
                    </div>
                @endrole
            @endif

        </div>
        <div class="tab-pane" id="profile">

            <div class="row">
                <div class="col-md-4">

                    <div class="form-group has-feedback{{ $errors->has('profile.title') ? ' has-error' : '' }}">
                        {!! Form::label('profile.title', trans('admin/users.title')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[title]', old('profile.title'), ['id' => 'profile.title', 'class' => 'form-control', 'placeholder' => trans('admin/users.title')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-user"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.title') )
                            <span class="help-block">{{ $errors->first('profile.title') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-4">

                    <div class="form-group has-feedback{{ $errors->has('profile.first_name') ? ' has-error' : '' }}">
                        {!! Form::label('profile.first_name', trans('admin/users.first_name')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[first_name]', old('profile.first_name'), ['id' => 'profile.first_name', 'class' => 'form-control', 'placeholder' => trans('admin/users.first_name'), 'required1']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-user"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.first_name') )
                            <span class="help-block">{{ $errors->first('profile.first_name') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-4">

                    <div class="form-group has-feedback{{ $errors->has('profile.last_name') ? ' has-error' : '' }}">
                        {!! Form::label('profile.last_name', trans('admin/users.last_name')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[last_name]', old('profile.last_name'), ['id' => 'profile.last_name', 'class' => 'form-control', 'placeholder' => trans('admin/users.last_name'), 'required1']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-user"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.last_name') )
                            <span class="help-block">{{ $errors->first('profile.last_name') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.date_of_birth') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('profile.date_of_birth', trans('admin/users.date_of_birth')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::date('profile[date_of_birth]', old('profile.date_of_birth', $mode == 'edit' ? $user->profile->getOriginalForm('date_of_birth') : null), ['id' => 'profile.date_of_birth', 'class' => 'form-control', 'placeholder' => trans('admin/users.date_of_birth')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.date_of_birth') )
                            <span class="help-block">{{ $errors->first('profile.date_of_birth') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.mobile_no') ? ' has-error' : '' }}">
                        {!! Form::label('profile.mobile_no', trans('admin/users.mobile_no')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[mobile_no]', old('profile.mobile_no'), ['id' => 'profile.mobile_no', 'class' => 'form-control', 'placeholder' => trans('admin/users.mobile_no')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-phone"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.mobile_no') )
                            <span class="help-block">{{ $errors->first('profile.mobile_no') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.telephone_no') ? ' has-error' : '' }}">
                        {!! Form::label('profile.telephone_no', trans('admin/users.telephone_no')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[telephone_no]', old('profile.telephone_no'), ['id' => 'profile.telephone_no', 'class' => 'form-control', 'placeholder' => trans('admin/users.telephone_no')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-phone"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.telephone_no') )
                            <span class="help-block">{{ $errors->first('profile.telephone_no') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.emergency_no') ? ' has-error' : '' }}">
                        {!! Form::label('profile.emergency_no', trans('admin/users.emergency_no')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[emergency_no]', old('profile.emergency_no'), ['id' => 'profile.emergency_no', 'class' => 'form-control', 'placeholder' => trans('admin/users.emergency_no')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-phone"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.emergency_no') )
                            <span class="help-block">{{ $errors->first('profile.emergency_no') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-12">

                    <div class="form-group has-feedback{{ $errors->has('profile.address') ? ' has-error' : '' }}">
                        {!! Form::label('profile.address', trans('admin/users.address')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[address]', old('profile.address'), ['id' => 'profile.address', 'class' => 'form-control', 'placeholder' => trans('admin/users.address')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.address') )
                            <span class="help-block">{{ $errors->first('profile.address') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.city') ? ' has-error' : '' }}">
                        {!! Form::label('profile.city', trans('admin/users.city')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[city]', old('profile.city'), ['id' => 'profile.city', 'class' => 'form-control', 'placeholder' => trans('admin/users.city')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.city') )
                            <span class="help-block">{{ $errors->first('profile.city') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.postcode') ? ' has-error' : '' }}">
                        {!! Form::label('profile.postcode', trans('admin/users.postcode')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[postcode]', old('profile.postcode'), ['id' => 'profile.postcode', 'class' => 'form-control', 'placeholder' => trans('admin/users.postcode')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.postcode') )
                            <span class="help-block">{{ $errors->first('profile.postcode') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.state') ? ' has-error' : '' }}">
                        {!! Form::label('profile.state', trans('admin/users.state')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[state]', old('profile.state'), ['id' => 'profile.state', 'class' => 'form-control', 'placeholder' => trans('admin/users.state')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.state') )
                            <span class="help-block">{{ $errors->first('profile.state') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.country') ? ' has-error' : '' }}">
                        {!! Form::label('profile.country', trans('admin/users.country')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[country]', old('profile.country'), ['id' => 'profile.country', 'class' => 'form-control', 'placeholder' => trans('admin/users.country')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.country') )
                            <span class="help-block">{{ $errors->first('profile.country') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-12">

                    <div class="form-group has-feedback{{ $errors->has('profile.profile_type') ? ' has-error' : '' }}">
                        {!! Form::label('profile.profile_type', trans('admin/users.profile_type')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::select('profile[profile_type]', $profileTypes, old('profile.profile_type', $mode == 'create' ? '0' : null), ['id' => 'profile.profile_type', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/users.profile_type')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-building"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.profile_type') )
                            <span class="help-block">{{ $errors->first('profile.profile_type') }}</span>
                        @endif
                    </div>

                </div>
            </div>

            <div class="company-container">

                <div class="row">
                    <div class="col-md-12">

                        <div class="form-group has-feedback{{ $errors->has('profile.company_name') ? ' has-error' : '' }}">
                            {!! Form::label('profile.company_name', trans('admin/users.company_name')) !!}
                            {{-- <div class="input-group"> --}}
                                {!! Form::text('profile[company_name]', old('profile.company_name'), ['id' => 'profile.company_name', 'class' => 'form-control', 'placeholder' => trans('admin/users.company_name')]) !!}
                                {{-- <span class="input-group-addon"><i class="fa fa-building"></i></span> --}}
                            {{-- </div> --}}
                            @if( $errors->has('profile.company_name') )
                                <span class="help-block">{{ $errors->first('profile.company_name') }}</span>
                            @endif
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group has-feedback{{ $errors->has('profile.company_number') ? ' has-error' : '' }}">
                            {!! Form::label('profile.company_number', trans('admin/users.company_number')) !!}
                            {{-- <div class="input-group"> --}}
                                {!! Form::text('profile[company_number]', old('profile.company_number'), ['id' => 'profile.company_number', 'class' => 'form-control', 'placeholder' => trans('admin/users.company_number')]) !!}
                                {{-- <span class="input-group-addon"><i class="fa fa-building"></i></span> --}}
                            {{-- </div> --}}
                            @if( $errors->has('profile.company_number') )
                                <span class="help-block">{{ $errors->first('profile.company_number') }}</span>
                            @endif
                        </div>

                    </div>
                    <div class="col-md-6">

                        <div class="form-group has-feedback{{ $errors->has('profile.company_tax_number') ? ' has-error' : '' }}">
                            {!! Form::label('profile.company_tax_number', trans('admin/users.company_tax_number')) !!}
                            {{-- <div class="input-group"> --}}
                                {!! Form::text('profile[company_tax_number]', old('profile.company_tax_number'), ['id' => 'profile.company_tax_number', 'class' => 'form-control', 'placeholder' => trans('admin/users.company_tax_number')]) !!}
                                {{-- <span class="input-group-addon"><i class="fa fa-building"></i></span> --}}
                            {{-- </div> --}}
                            @if( $errors->has('profile.company_tax_number') )
                                <span class="help-block">{{ $errors->first('profile.company_tax_number') }}</span>
                            @endif
                        </div>

                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-12">

                    <div class="form-group has-feedback{{ $errors->has('profile.description') ? ' has-error' : '' }}">
                        {!! Form::label('profile.description', trans('admin/users.description')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::textarea('profile[description]', old('profile.description'), ['id' => 'profile.description', 'class' => 'form-control', 'placeholder' => trans('admin/users.description'), 'rows' => '2']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.description') )
                            <span class="help-block">{{ $errors->first('profile.description') }}</span>
                        @endif
                    </div>

                </div>
            </div>

        </div>
        <div class="tab-pane" id="other">

            <div class="row fleet_operator_hide">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.national_insurance_no') ? ' has-error' : '' }}">
                        {!! Form::label('profile.national_insurance_no', trans('admin/users.national_insurance_no')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[national_insurance_no]', old('profile.national_insurance_no'), ['id' => 'profile.national_insurance_no', 'class' => 'form-control', 'placeholder' => trans('admin/users.national_insurance_no')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.national_insurance_no') )
                            <span class="help-block">{{ $errors->first('profile.national_insurance_no') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.bank_account') ? ' has-error' : '' }}">
                        {!! Form::label('profile.bank_account', trans('admin/users.bank_account')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::textarea('profile[bank_account]', old('profile.bank_account'), ['id' => 'profile.bank_account', 'class' => 'form-control', 'placeholder' => trans('admin/users.bank_account'), 'rows' => '2']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.bank_account') )
                            <span class="help-block">{{ $errors->first('profile.bank_account') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row fleet_operator_hide">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.insurance') ? ' has-error' : '' }}">
                        {!! Form::label('profile.insurance', trans('admin/users.insurance')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[insurance]', old('profile.insurance'), ['id' => 'profile.insurance', 'class' => 'form-control', 'placeholder' => trans('admin/users.insurance')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.insurance') )
                            <span class="help-block">{{ $errors->first('profile.insurance') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.insurance_expiry_date') ? ' has-error' : '' }}">
                        {!! Form::label('profile.insurance_expiry_date', trans('admin/users.insurance_expiry_date')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[insurance_expiry_date]', old('profile.insurance_expiry_date', $mode == 'edit' ? $user->profile->getOriginalForm('insurance_expiry_date') : null), ['id' => 'profile.insurance_expiry_date', 'class' => 'form-control datepicker', 'placeholder' => trans('admin/users.insurance_expiry_date')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.insurance_expiry_date') )
                            <span class="help-block">{{ $errors->first('profile.insurance_expiry_date') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row fleet_operator_hide">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.driving_licence') ? ' has-error' : '' }}">
                        {!! Form::label('profile.driving_licence', trans('admin/users.driving_licence')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[driving_licence]', old('profile.driving_licence'), ['id' => 'profile.driving_licence', 'class' => 'form-control', 'placeholder' => trans('admin/users.driving_licence')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.driving_licence') )
                            <span class="help-block">{{ $errors->first('profile.driving_licence') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.driving_licence_expiry_date') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('profile.driving_licence_expiry_date', trans('admin/users.driving_licence_expiry_date')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::date('profile[driving_licence_expiry_date]', old('profile.driving_licence_expiry_date', $mode == 'edit' ? $user->profile->getOriginalForm('driving_licence_expiry_date') : null), ['id' => 'profile.driving_licence_expiry_date', 'class' => 'form-control', 'placeholder' => trans('admin/users.driving_licence_expiry_date')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.driving_licence_expiry_date') )
                            <span class="help-block">{{ $errors->first('profile.driving_licence_expiry_date') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row fleet_operator_hide">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.pco_licence') ? ' has-error' : '' }}">
                        {!! Form::label('profile.pco_licence', trans('admin/users.pco_licence')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[pco_licence]', old('profile.pco_licence'), ['id' => 'profile.pco_licence', 'class' => 'form-control', 'placeholder' => trans('admin/users.pco_licence')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.pco_licence') )
                            <span class="help-block">{{ $errors->first('profile.pco_licence') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.pco_licence_expiry_date') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('profile.pco_licence_expiry_date', trans('admin/users.pco_licence_expiry_date')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::date('profile[pco_licence_expiry_date]', old('profile.pco_licence_expiry_date', $mode == 'edit' ? $user->profile->getOriginalForm('pco_licence_expiry_date') : null), ['id' => 'profile.pco_licence_expiry_date', 'class' => 'form-control', 'placeholder' => trans('admin/users.pco_licence_expiry_date')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.pco_licence_expiry_date') )
                            <span class="help-block">{{ $errors->first('profile.pco_licence_expiry_date') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row fleet_operator_hide">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.phv_licence') ? ' has-error' : '' }}">
                        {!! Form::label('profile.phv_licence', trans('admin/users.phv_licence')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[phv_licence]', old('profile.phv_licence'), ['id' => 'profile.phv_licence', 'class' => 'form-control', 'placeholder' => trans('admin/users.phv_licence')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.phv_licence') )
                            <span class="help-block">{{ $errors->first('profile.phv_licence') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.phv_licence_expiry_date') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('profile.phv_licence_expiry_date', trans('admin/users.phv_licence_expiry_date')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::date('profile[phv_licence_expiry_date]', old('profile.phv_licence_expiry_date', $mode == 'edit' ? $user->profile->getOriginalForm('phv_licence_expiry_date') : null), ['id' => 'profile.phv_licence_expiry_date', 'class' => 'form-control', 'placeholder' => trans('admin/users.phv_licence_expiry_date')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.phv_licence_expiry_date') )
                            <span class="help-block">{{ $errors->first('profile.phv_licence_expiry_date') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.commission') ? ' has-error' : '' }}">
                        {!! Form::label('profile.commission', trans('admin/users.commission') .' (%)') !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[commission]', old('profile.commission'), ['id' => 'profile.commission', 'class' => 'form-control', 'placeholder' => trans('admin/users.commission') .' (%)']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-percent"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.commission') )
                            <span class="help-block">{{ $errors->first('profile.commission') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6 fleet_operator_hide">

                    <div class="form-group has-feedback{{ $errors->has('base_address') ? ' has-error' : '' }}">
                        {!! Form::label('base_address', trans('admin/users.base_address')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('base_address', old('base_address'), ['id' => 'base_address', 'class' => 'form-control', 'placeholder' => trans('admin/users.base_address')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('base_address') )
                            <span class="help-block">{{ $errors->first('base_address') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row fleet_operator_hide">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.availability_status') ? ' has-error' : '' }}">
                        {!! Form::label('profile.availability_status', trans('admin/users.availability_status')) !!}
                        <div class="input-group">
                            {!! Form::select('profile[availability_status]', $availabilityStatus, old('profile.availability_status', $mode == 'create' ? '0' : null), ['id' => 'profile.availability_status', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/users.availability_status')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-eye"></i></span> --}}
                            <span class="input-group-addon" data-toggle="popover" data-title="" data-content="{{ trans('admin/users.availability_status_help') }}">
                                <i class="ion-ios-information-outline" style="font-size:18px; color:#636363"></i>
                            </span>
                        </div>
                        @if( $errors->has('profile.availability_status') )
                            <span class="help-block">{{ $errors->first('profile.availability_status') }}</span>
                        @endif
                    </div>

                </div>
            </div>

            <div class="hide">
                <div>{{ trans('admin/users.availability') }}</div>
                <div id="availabilityList"></div>
            </div>

            <div class="fleet_operator_hide">
                <div>{{ trans('admin/users.files') }}</div>
                <div id="fileList"></div>
            </div>

        </div>

        <div class="row">
            <div class="col-sm-12">
                {!! Form::button(/*'<i class="fa fa-check"></i> '. */($mode == 'edit' ? trans('admin/users.button.update') : trans('admin/users.button.create')), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
                <a href="{{ route('admin.users.index') }}" class="btn btn-link">{{ trans('admin/users.button.cancel') }}</a>
            </div>
        </div>
    </div>
</div>

@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
@stop


@section('subfooter')
    <script src="{{ asset_url('plugins','autosize/autosize.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        var roles = {!! json_encode($roles) !!}

        // Date picker
        $('.form-master .datepicker').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            timePicker: true,
            timePicker24Hour: {{ config('site.time_format') == 'H:i' ? 'true' : 'false' }},
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD HH:mm',
                firstDay: {{ config('site.start_of_week') }}
            }
        })
        .on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm')).change();
        });

        // Tooltips
        $('[data-toggle="popover"]').popover({
            placement: 'auto right',
            container: 'body',
            trigger: 'click focus hover',
            html: true
        });

        // Driver other tab display
        function checkRoleType(role) {
            $.each(roles, function(k,v) {
                if(v.id == role) {
                    role = v.slug
                }
            });

            $('#other .fleet_operator_hide').removeClass('hidden');

            if (role.includes('admin.fleet_operator')) {
                $('.user-tab-link-other').show();
                $('.driver-only1').removeClass('col-md-12').addClass('col-md-6');
                $('.driver-only2').show();

                $('.fleet_container').hide();
                $('#fleet_id').val('0').trigger('change');
                $('#other .fleet_operator_hide').addClass('hidden');
            }
            else if (role.includes('driver')) {
                $('.user-tab-link-other').show();
                $('.driver-only1').removeClass('col-md-12').addClass('col-md-6');
                $('.driver-only2').show();

                $('.fleet_container').show();
            }
            else {
                $('.user-tab-link-other').hide();
                $('.driver-only1').removeClass('col-md-6').addClass('col-md-12');
                $('.driver-only2').hide();

                $('.fleet_container').hide();
                $('#fleet_id').val('0').trigger('change');
            }
        }

        checkRoleType($('#role').val());
        $('#role').change(function(){
            checkRoleType($('#role').val());
        });

        // Availability table
        $('#availabilityList').html(
        '<div class="table-responsive">\
          <table class="table table-hover" cellspacing="0" width="100%">\
          <thead>\
            <tr>\
              <th>{{ trans('admin/users.availability_start') }}</th>\
              <th>{{ trans('admin/users.availability_end') }}</th>\
              <th>{{ trans('admin/users.availability_available') }}</th>\
              <th></th>\
            </tr>\
          </thead>\
          </table>\
        </div>\
        <button type="button" class="btn btn-success btn-xs btnNewAvailability" title="New">\
          <i class="fa fa-plus"></i> <span>{{ trans('admin/users.availability_new') }}</span>\
        </button>');

        var lastIndex = 0;

        function newAvailability() {
            $('#availabilityList table').append(
              '<tr class="availabilityRow'+ lastIndex +'">\
                <td>\
                  <input type="date" name="profile[availability]['+ lastIndex +'][start_date]" id="availability_start_date" placeholder="{{ trans('admin/users.availability_start') }}" value="" class="form-control">\
                </td>\
                <td>\
                  <input type="date" name="profile[availability]['+ lastIndex +'][end_date]" id="availability_end_date" placeholder="{{ trans('admin/users.availability_end') }}" value="" class="form-control">\
                </td>\
                <td>\
                  <input type="date" name="profile[availability]['+ lastIndex +'][available_date]" id="availability_available_date" placeholder="{{ trans('admin/users.availability_available') }}" value="" class="form-control">\
                </td>\
                <td>\
                  <button type="button" onclick="$(this).closest(\'tr\').remove(); return false;" class="btn btn-default btn-sm btnDelete" title="{{ trans('admin/users.button.destroy') }}">\
                    <i class="fa fa-trash"></i>\
                  </button>\
                </td>\
              </tr>'
            );

            // Delete button
            $('#availabilityList').find('button.btnDelete').hover(
              function() {
                  $(this).removeClass('btn-default').addClass('btn-danger');
              },
              function() {
                  $(this).removeClass('btn-danger').addClass('btn-default');
              }
            );

            var index = lastIndex;
            lastIndex++;
            return index;
        }

        $('#availabilityList .btnNewAvailability').on('click', function(e) {
            newAvailability();
            e.preventDefault();
        });

        var availability = {!! $mode == 'edit' ? ($user->profile->availability ?: '[]') : '[]' !!};

        if( availability ) {
            $.each(availability, function(key, value) {
                var index = newAvailability();
                var row = $('#availabilityList tr.availabilityRow'+ index);
                row.find('#availability_id').val(value.id);
                row.find('#availability_start_date').val(value.start_date);
                row.find('#availability_end_date').val(value.end_date);
                row.find('#availability_available_date').val(value.available_date);
            });
        }

        // File table
        $('#fileList').html(
            '<div class="table-responsive">\
              <table class="table table-hover" cellspacing="0" width="100%">\
              <thead>\
                <tr>\
                  <th>{{ trans('admin/users.files_name') }}</th>\
                  <th>{{ trans('admin/users.files_file') }}</th>\
                  <th></th>\
                </tr>\
              </thead>\
              </table>\
            </div>\
            <button type="button" class="btn btn-success btn-xs btnNewFile" title="New">\
              <i class="fa fa-plus"></i> <span>{{ trans('admin/users.files_new') }}</span>\
            </button>'
        );

        var lastIndex2 = 0;

        function newFile() {
          $('#fileList table').append(
              '<tr class="fileRow'+ lastIndex2 +'">\
                <td>\
                  <input type="hidden" name="profile[files]['+ lastIndex2 +'][id]" id="file_id" value="0" required class="form-control">\
                  <input type="hidden" name="profile[files]['+ lastIndex2 +'][delete]" id="file_delete" value="0" required class="form-control">\
                  <input type="text" name="profile[files]['+ lastIndex2 +'][name]" id="file_name" placeholder="{{ trans('admin/users.files_name') }}" value="" required class="form-control">\
                </td>\
                <td>\
                  <input type="file" name="profile[files]['+ lastIndex2 +']" id="file_path" required class="form-control">\
                  <div class="filename"></div>\
                </td>\
                <td>\
                  <button type="button" onclick="var tr = $(this).closest(\'tr\'); tr.find(\'#file_delete\').val(1); tr.find(\'#file_name, #file_path\').removeAttr(\'required\'); tr.hide(); return false;" class="btn btn-default btn-sm btnDelete" title="{{ trans('admin/users.button.destroy') }}">\
                    <i class="fa fa-trash"></i>\
                  </button>\
                </td>\
              </tr>'
          );

          // Delete button
          $('#fileList').find('button.btnDelete').hover(
            function() {
                $(this).removeClass('btn-default').addClass('btn-danger');
            },
            function() {
                $(this).removeClass('btn-danger').addClass('btn-default');
            }
          );

          var index = lastIndex2;
          lastIndex2++;
          return index;
        }

        $('#fileList .btnNewFile').on('click', function(e) {
            newFile();
            e.preventDefault();
        });

        var files = {!! $mode == 'edit' ? ($user->profile->getFiles(true) ?: '[]') : '[]' !!};

        if( files ) {
            $.each(files, function(key, value) {
                var index = newFile();
                var row = $('#fileList tr.fileRow'+ index);
                row.find('#file_id').val(value.id);
                row.find('#file_name').val(value.name);

                var file = row.find('#file_path');
                file.remove();
                row.find('.filename').html('<a href="'+ value.path +'">{{ trans('admin/users.download') }}</a>');
            });
        }

        // Profile type
        function profileType() {
            if( $('select[name="profile[profile_type]"]').val() == 'company' ) {
                $('.company-container').show();
            } else {
                $('.company-container').hide();
            }
        }

        profileType();

        $('select[name="profile[profile_type]"]').change(function() {
            profileType();
        });

        // Textarea auto height
        autosize($('textarea'));

        // Placeholder
        function updateFormPlaceholder(that) {
            var $container = $(that).closest('.form-group:not(.placeholder-disabled)');

            if( $(that).val() != '' || $container.hasClass('placeholder-visible') ) {
                $container.find('label').show();
            }
            else {
                $container.find('label').hide();
            }
        }

        $('.form-master').find('input:not([type="submit"]), textarea, select').each(function() {
            updateFormPlaceholder(this);
        })
        .bind('change keyup', function(e) {
            updateFormPlaceholder(this);
        });
    });
    </script>
@stop
