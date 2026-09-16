<div class="nav-tabs-custom nav-tabs-no-borders">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#account" data-toggle="tab" aria-expanded="false">
                {{ trans('driver/account.tab.account') }}
            </a>
        </li>
        <li>
            <a href="#profile" data-toggle="tab" aria-expanded="true">
                {{ trans('driver/account.tab.profile') }}
            </a>
        </li>
        <li>
            <a href="#other" data-toggle="tab" aria-expanded="true">
                {{ trans('driver/account.tab.other') }}
            </a>
        </li>
    </ul>
    <div class="tab-content" style="margin-top:10px;">
        <div class="tab-pane active" id="account">

            <div class="row">
                <div class="col-md-12">

                    <div class="form-group has-feedback{{ $errors->has('name') ? ' has-error' : '' }}">
                        {!! Form::label('name', trans('driver/account.display_name')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('name', old('name', $mode == 'edit' ? $user->getOriginalForm('name') : null), ['id' => 'name', 'class' => 'form-control', 'placeholder' => trans('driver/account.display_name'), 'required']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-user"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('name') )
                            <span class="help-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('email') ? ' has-error' : '' }}">
                        {!! Form::label('email', trans('driver/account.email')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::email('email', old('email'), ['id' => 'email', 'class' => 'form-control', 'placeholder' => trans('driver/account.email'), 'required']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-envelope"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('email') )
                            <span class="help-block">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('username') ? ' has-error' : '' }}">
                        {!! Form::label('username', trans('driver/account.username')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('username', old('username', $mode == 'create' ? uniqid('user') : null), ['id' => 'username', 'class' => 'form-control', 'placeholder' => trans('driver/account.username'), 'required', 'readonly']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('username') )
                            <span class="help-block">{{ $errors->first('username') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('password') ? ' has-error' : '' }}">
                        {!! Form::label('password', trans('driver/account.password')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::password('password', ['id' => 'password', 'class' => 'form-control', 'placeholder' => trans('driver/account.password')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-lock"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('password') )
                            <span class="help-block">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                        {!! Form::label('password_confirmation', trans('driver/account.password_confirmation')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::password('password_confirmation', ['id' => 'password_confirmation', 'class' => 'form-control', 'placeholder' => trans('driver/account.password_confirmation')]) !!}
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
                                {!! Form::checkbox('avatar_delete', 1, old('avatar_delete', $mode == 'create' ? true : null)) !!} {{ trans('driver/account.avatar_delete') }}
                              </label>
                            </div>
                            @if( $errors->has('avatar_delete') )
                                <span class="help-block">{{ $errors->first('avatar_delete') }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="form-group has-feedback{{ $errors->has('avatar') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('avatar', trans('driver/account.avatar_upload')) !!}
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

            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('settings.locale') ? ' has-error' : '' }}">
                        {!! Form::label('settings.locale', trans('driver/account.locale')) !!}
                       {{-- <div class="input-group">--}}
                            {!! Form::select('settings[locale]', $locales, old('settings.locale', $user->getSetting('app.locale') !== null ? $user->getSetting('app.locale') : config('app.locale')), ['id' => 'settings.locale', 'class' => 'form-control select2', 'data-placeholder' => trans('driver/account.locale')]) !!}
                            {{--<span class="input-group-addon"><i class="fa fa-language"></i></span>--}}
                        {{--</div>--}}
                        @if( $errors->has('settings.locale') )
                            <span class="help-block">{{ $errors->first('settings.locale') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('settings.timezone') ? ' has-error' : '' }}">
                        {!! Form::label('settings.timezone', trans('admin/users.timezone')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::select('settings[timezone]', $timezoneList, old('settings.timezone', $user->getSetting('app.timezone') !== null ? $user->getSetting('app.timezone') : config('app.timezone')), ['id' => 'settings.timezone', 'class' => 'form-control select2', 'data-placeholder' => trans('admin/users.timezone')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-clock-o"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('settings.timezone') )
                            <span class="help-block">{{ $errors->first('settings.timezone') }}</span>
                        @endif
                    </div>

                </div>
            </div>

        </div>
        <div class="tab-pane" id="profile">

            <div class="row">
                <div class="col-md-4">

                    <div class="form-group has-feedback{{ $errors->has('profile.title') ? ' has-error' : '' }}">
                        {!! Form::label('profile.title', trans('driver/account.title')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[title]', old('profile.title'), ['id' => 'profile.title', 'class' => 'form-control', 'placeholder' => trans('driver/account.title')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-user"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.title') )
                            <span class="help-block">{{ $errors->first('profile.title') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-4">

                    <div class="form-group has-feedback{{ $errors->has('profile.first_name') ? ' has-error' : '' }}">
                        {!! Form::label('profile.first_name', trans('driver/account.first_name')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[first_name]', old('profile.first_name'), ['id' => 'profile.first_name', 'class' => 'form-control', 'placeholder' => trans('driver/account.first_name'), 'required1']) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-user"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.first_name') )
                            <span class="help-block">{{ $errors->first('profile.first_name') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-4">

                    <div class="form-group has-feedback{{ $errors->has('profile.last_name') ? ' has-error' : '' }}">
                        {!! Form::label('profile.last_name', trans('driver/account.last_name')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[last_name]', old('profile.last_name'), ['id' => 'profile.last_name', 'class' => 'form-control', 'placeholder' => trans('driver/account.last_name'), 'required1']) !!}
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
                        {!! Form::label('profile.date_of_birth', trans('driver/account.date_of_birth')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::date('profile[date_of_birth]', old('profile.date_of_birth', $mode == 'edit' ? $user->profile->getOriginalForm('date_of_birth') : null), ['id' => 'profile.date_of_birth', 'class' => 'form-control', 'placeholder' => trans('driver/account.date_of_birth')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.date_of_birth') )
                            <span class="help-block">{{ $errors->first('profile.date_of_birth') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.mobile_no') ? ' has-error' : '' }}">
                        {!! Form::label('profile.mobile_no', trans('driver/account.mobile_no')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[mobile_no]', old('profile.mobile_no'), ['id' => 'profile.mobile_no', 'class' => 'form-control', 'placeholder' => trans('driver/account.mobile_no')]) !!}
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
                        {!! Form::label('profile.telephone_no', trans('driver/account.telephone_no')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[telephone_no]', old('profile.telephone_no'), ['id' => 'profile.telephone_no', 'class' => 'form-control', 'placeholder' => trans('driver/account.telephone_no')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-phone"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.telephone_no') )
                            <span class="help-block">{{ $errors->first('profile.telephone_no') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.emergency_no') ? ' has-error' : '' }}">
                        {!! Form::label('profile.emergency_no', trans('driver/account.emergency_no')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[emergency_no]', old('profile.emergency_no'), ['id' => 'profile.emergency_no', 'class' => 'form-control', 'placeholder' => trans('driver/account.emergency_no')]) !!}
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
                        {!! Form::label('profile.address', trans('driver/account.address')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[address]', old('profile.address'), ['id' => 'profile.address', 'class' => 'form-control', 'placeholder' => trans('driver/account.address')]) !!}
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
                        {!! Form::label('profile.city', trans('driver/account.city')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[city]', old('profile.city'), ['id' => 'profile.city', 'class' => 'form-control', 'placeholder' => trans('driver/account.city')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.city') )
                            <span class="help-block">{{ $errors->first('profile.city') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.postcode') ? ' has-error' : '' }}">
                        {!! Form::label('profile.postcode', trans('driver/account.postcode')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[postcode]', old('profile.postcode'), ['id' => 'profile.postcode', 'class' => 'form-control', 'placeholder' => trans('driver/account.postcode')]) !!}
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
                        {!! Form::label('profile.state', trans('driver/account.state')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[state]', old('profile.state'), ['id' => 'profile.state', 'class' => 'form-control', 'placeholder' => trans('driver/account.state')]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.state') )
                            <span class="help-block">{{ $errors->first('profile.state') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.country') ? ' has-error' : '' }}">
                        {!! Form::label('profile.country', trans('driver/account.country')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[country]', old('profile.country'), ['id' => 'profile.country', 'class' => 'form-control', 'placeholder' => trans('driver/account.country')]) !!}
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
                        {!! Form::label('profile.profile_type', trans('driver/account.profile_type')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::select('profile[profile_type]', $profileTypes, old('profile.profile_type', $mode == 'create' ? '0' : null), ['id' => 'profile.profile_type', 'class' => 'form-control select2', 'data-placeholder' => trans('driver/account.profile_type')]) !!}
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
                            {!! Form::label('profile.company_name', trans('driver/account.company_name')) !!}
                            {{-- <div class="input-group"> --}}
                                {!! Form::text('profile[company_name]', old('profile.company_name'), ['id' => 'profile.company_name', 'class' => 'form-control', 'placeholder' => trans('driver/account.company_name')]) !!}
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
                            {!! Form::label('profile.company_number', trans('driver/account.company_number')) !!}
                            {{-- <div class="input-group"> --}}
                                {!! Form::text('profile[company_number]', old('profile.company_number'), ['id' => 'profile.company_number', 'class' => 'form-control', 'placeholder' => trans('driver/account.company_number')]) !!}
                                {{-- <span class="input-group-addon"><i class="fa fa-building"></i></span> --}}
                            {{-- </div> --}}
                            @if( $errors->has('profile.company_number') )
                                <span class="help-block">{{ $errors->first('profile.company_number') }}</span>
                            @endif
                        </div>

                    </div>
                    <div class="col-md-6">

                        <div class="form-group has-feedback{{ $errors->has('profile.company_tax_number') ? ' has-error' : '' }}">
                            {!! Form::label('profile.company_tax_number', trans('driver/account.company_tax_number')) !!}
                            {{-- <div class="input-group"> --}}
                                {!! Form::text('profile[company_tax_number]', old('profile.company_tax_number'), ['id' => 'profile.company_tax_number', 'class' => 'form-control', 'placeholder' => trans('driver/account.company_tax_number')]) !!}
                                {{-- <span class="input-group-addon"><i class="fa fa-building"></i></span> --}}
                            {{-- </div> --}}
                            @if( $errors->has('profile.company_tax_number') )
                                <span class="help-block">{{ $errors->first('profile.company_tax_number') }}</span>
                            @endif
                        </div>

                    </div>
                </div>

            </div>

        </div>
        <div class="tab-pane" id="other">

            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.national_insurance_no') ? ' has-error' : '' }}">
                        {!! Form::label('profile.national_insurance_no', trans('driver/account.national_insurance_no')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[national_insurance_no]', old('profile.national_insurance_no'), [
                              'id' => 'profile.national_insurance_no',
                              'class' => 'form-control',
                              'placeholder' => trans('driver/account.national_insurance_no')
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.national_insurance_no') )
                            <span class="help-block">{{ $errors->first('profile.national_insurance_no') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.bank_account') ? ' has-error' : '' }}">
                        {!! Form::label('profile.bank_account', trans('driver/account.bank_account')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::textarea('profile[bank_account]', old('profile.bank_account'), [
                              'id' => 'profile.bank_account',
                              'class' => 'form-control',
                              'placeholder' => trans('driver/account.bank_account'),
                              'rows' => '2'
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.bank_account') )
                            <span class="help-block">{{ $errors->first('profile.bank_account') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.insurance') ? ' has-error' : '' }}">
                        {!! Form::label('profile.insurance', trans('driver/account.insurance')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[insurance]', old('profile.insurance'), [
                              'id' => 'profile.insurance',
                              'class' => 'form-control',
                              'placeholder' => trans('driver/account.insurance'),
                              'readonly' => config('site.driver_show_edit_profile_insurance') ? false : true
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.insurance') )
                            <span class="help-block">{{ $errors->first('profile.insurance') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.insurance_expiry_date') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('profile.insurance_expiry_date', trans('driver/account.insurance_expiry_date')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[insurance_expiry_date]', old('profile.insurance_expiry_date', $mode == 'edit' ? $user->profile->getOriginalForm('insurance_expiry_date') : null), [
                              'id' => 'profile.insurance_expiry_date',
                              'class' => 'form-control datepicker',
                              'placeholder' => trans('driver/account.insurance_expiry_date'),
                              'readonly' => config('site.driver_show_edit_profile_insurance') ? false : true
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.insurance_expiry_date') )
                            <span class="help-block">{{ $errors->first('profile.insurance_expiry_date') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.driving_licence') ? ' has-error' : '' }}">
                        {!! Form::label('profile.driving_licence', trans('driver/account.driving_licence')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[driving_licence]', old('profile.driving_licence'), [
                              'id' => 'profile.driving_licence',
                              'class' => 'form-control',
                              'placeholder' => trans('driver/account.driving_licence'),
                              'readonly' => config('site.driver_show_edit_profile_driving_licence') ? false : true
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.driving_licence') )
                            <span class="help-block">{{ $errors->first('profile.driving_licence') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.driving_licence_expiry_date') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('profile.driving_licence_expiry_date', trans('driver/account.driving_licence_expiry_date')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::date('profile[driving_licence_expiry_date]', old('profile.driving_licence_expiry_date', $mode == 'edit' ? $user->profile->getOriginalForm('driving_licence_expiry_date') : null), [
                              'id' => 'profile.driving_licence_expiry_date',
                              'class' => 'form-control',
                              'placeholder' => trans('driver/account.driving_licence_expiry_date'),
                              'readonly' => config('site.driver_show_edit_profile_driving_licence') ? false : true
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.driving_licence_expiry_date') )
                            <span class="help-block">{{ $errors->first('profile.driving_licence_expiry_date') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.pco_licence') ? ' has-error' : '' }}">
                        {!! Form::label('profile.pco_licence', trans('driver/account.pco_licence')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[pco_licence]', old('profile.pco_licence'), [
                              'id' => 'profile.pco_licence',
                              'class' => 'form-control',
                              'placeholder' => trans('driver/account.pco_licence'),
                              'readonly' => config('site.driver_show_edit_profile_pco_licence') ? false : true
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.pco_licence') )
                            <span class="help-block">{{ $errors->first('profile.pco_licence') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.pco_licence_expiry_date') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('profile.pco_licence_expiry_date', trans('driver/account.pco_licence_expiry_date')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::date('profile[pco_licence_expiry_date]', old('profile.pco_licence_expiry_date', $mode == 'edit' ? $user->profile->getOriginalForm('pco_licence_expiry_date') : null), [
                              'id' => 'profile.pco_licence_expiry_date',
                              'class' => 'form-control',
                              'placeholder' => trans('driver/account.pco_licence_expiry_date'),
                              'readonly' => config('site.driver_show_edit_profile_pco_licence') ? false : true
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.pco_licence_expiry_date') )
                            <span class="help-block">{{ $errors->first('profile.pco_licence_expiry_date') }}</span>
                        @endif
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.phv_licence') ? ' has-error' : '' }}">
                        {!! Form::label('profile.phv_licence', trans('driver/account.phv_licence')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::text('profile[phv_licence]', old('profile.phv_licence'), [
                              'id' => 'profile.phv_licence',
                              'class' => 'form-control',
                              'placeholder' => trans('driver/account.phv_licence'),
                              'readonly' => config('site.driver_show_edit_profile_phv_licence') ? false : true
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.phv_licence') )
                            <span class="help-block">{{ $errors->first('profile.phv_licence') }}</span>
                        @endif
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group has-feedback{{ $errors->has('profile.phv_licence_expiry_date') ? ' has-error' : '' }} placeholder-visible">
                        {!! Form::label('profile.phv_licence_expiry_date', trans('driver/account.phv_licence_expiry_date')) !!}
                        {{-- <div class="input-group"> --}}
                            {!! Form::date('profile[phv_licence_expiry_date]', old('profile.phv_licence_expiry_date', $mode == 'edit' ? $user->profile->getOriginalForm('phv_licence_expiry_date') : null), [
                              'id' => 'profile.phv_licence_expiry_date',
                              'class' => 'form-control',
                              'placeholder' => trans('driver/account.phv_licence_expiry_date'),
                              'readonly' => config('site.driver_show_edit_profile_phv_licence') ? false : true
                            ]) !!}
                            {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                        {{-- </div> --}}
                        @if( $errors->has('profile.phv_licence_expiry_date') )
                            <span class="help-block">{{ $errors->first('profile.phv_licence_expiry_date') }}</span>
                        @endif
                    </div>

                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-sm-12">
                {!! Form::button(/*'<i class="fa fa-check"></i> '. */($mode == 'edit' ? trans('driver/account.button.update') : trans('driver/account.button.create')), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
                <a href="{{ route('driver.account.index') }}" class="btn btn-link">{{ trans('driver/account.button.cancel') }}</a>
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

        // Textarea auto height
        autosize($('textarea'));

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
