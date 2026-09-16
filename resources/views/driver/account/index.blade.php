@extends('driver.index')

@section('title', trans('driver/account.page_title'))
@section('subtitle', /*'<i class="fa fa-user"></i> '.*/ trans('driver/account.page_title'))

@section('subcontent')
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">

            <div class="widget-user-2">
                <div class="widget-user-header clearfix">

                    <div class="widget-user-image">
                        <img class="img-circle" src="{{ asset( $user->getAvatarPath() ) }}" alt="">
                    </div>
                    <h3 class="widget-user-username">{{ $user->getName(true) }}</h3>
                    <h5 class="widget-user-desc">{{ trans('driver/account.member_since') }} {{ Carbon\Carbon::parse($user->getOriginal('created_at'))->diffForHumans(null, true, false, 2) }}</h5>

                </div>
                <div>

                    <ul class="list-group list-group-unbordered details-list">
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('driver/account.username') }}:</span>
                            <span class="details-list-value">{{ $user->username }}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('driver/account.email') }}:</span>
                            <span class="details-list-value">{!! $user->getEmailLink(['class'=>'text-default']) !!}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('driver/account.locale') }}:</span>
                            <span class="details-list-value">
                                @php $locale = $user->getSetting('app.locale') !== null ? $user->getSetting('app.locale') : config('app.locale'); @endphp

                                {{ config('app.locales')[$locale]['name'] }}

                                @if( config('app.locales')[$locale]['name'] != config('app.locales')[$locale]['native'] )
                                    ({{ config('app.locales')[$locale]['native'] }})
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('driver/account.timezone') }}:</span>
                            <span class="details-list-value">
                                {{ $user->getSetting('app.timezone') !== null ? $user->getSetting('app.timezone') : config('app.timezone') }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('driver/account.status') }}:</span>
                            <span class="details-list-value">{!! $user->getStatus('label') !!}</span>
                        </li>
                        @if( $user->profile )

                            {{--
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/account.commission') }}:</span>
                                <span class="details-list-value">{{ $user->profile->getCommission() }}</span>
                            </li>
                            --}}

                            @if( $user->profile->date_of_birth )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.date_of_birth') }}:</span>
                                    <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($user->profile->date_of_birth, 'date') }}</span>
                                </li>
                            @endif
                            @if( $user->profile->mobile_no )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.mobile_no') }}:</span>
                                    <span class="details-list-value">{!! $user->profile->getTelLink('mobile_no', ['class'=>'text-default']) !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->telephone_no )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.telephone_no') }}:</span>
                                    <span class="details-list-value">{!! $user->profile->getTelLink('telephone_no', ['class'=>'text-default']) !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->emergency_no )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.emergency_no') }}:</span>
                                    <span class="details-list-value">{!! $user->profile->getTelLink('emergency_no', ['class'=>'text-default']) !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->address )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.address') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->address }}</span>
                                </li>
                            @endif
                            @if( $user->profile->city )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.city') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->city }}</span>
                                </li>
                            @endif
                            @if( $user->profile->postcode )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.postcode') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->postcode }}</span>
                                </li>
                            @endif
                            @if( $user->profile->state )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.state') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->state }}</span>
                                </li>
                            @endif
                            @if( $user->profile->country )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.country') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->country }}</span>
                                </li>
                            @endif
                            @if( $user->profile->profile_type == 'company' )

                                @if( $user->profile->company_name )
                                    <li class="list-group-item">
                                        <span class="details-list-title">{{ trans('driver/account.company_name') }}:</span>
                                        <span class="details-list-value">{{ $user->profile->company_name }}</span>
                                    </li>
                                @endif
                                @if( $user->profile->company_number )
                                    <li class="list-group-item">
                                        <span class="details-list-title">{{ trans('driver/account.company_number') }}:</span>
                                        <span class="details-list-value">{{ $user->profile->company_number }}</span>
                                    </li>
                                @endif
                                @if( $user->profile->company_tax_number )
                                    <li class="list-group-item">
                                        <span class="details-list-title">{{ trans('driver/account.company_tax_number') }}:</span>
                                        <span class="details-list-value">{{ $user->profile->company_tax_number }}</span>
                                    </li>
                                @endif

                            @endif
                            @if( $user->profile->national_insurance_no )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.national_insurance_no') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->national_insurance_no }}</span>
                                </li>
                            @endif
                            @if( $user->profile->bank_account )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.bank_account') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->bank_account }}</span>
                                </li>
                            @endif
                            @if( $user->profile->insurance )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.insurance') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->insurance }} {!! $user->profile->getExpiryDate('insurance_expiry_date') !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->driving_licence )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.driving_licence') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->driving_licence }} {!! $user->profile->getExpiryDate('driving_licence_expiry_date') !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->pco_licence )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.pco_licence') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->pco_licence }} {!! $user->profile->getExpiryDate('pco_licence_expiry_date') !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->phv_licence )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('driver/account.phv_licence') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->phv_licence }} {!! $user->profile->getExpiryDate('phv_licence_expiry_date') !!}</span>
                                </li>
                            @endif

                            {{--
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/account.updated_at') }}:</span>
                                <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($user->updated_at) }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/account.created_at') }}:</span>
                                <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($user->created_at) }}</span>
                            </li>
                            --}}

                        @endif
                    </ul>
                    <div class="row">
                        <div class="col-sm-12">
                            @if (config('site.driver_show_edit_profile_button'))
                            <a href="{{ route('driver.account.edit') }}" class="btn btn-primary">
                                {{-- <i class="fa fa-edit"></i> --}}
                                <span>{{ trans('driver/account.button.edit') }}</span>
                            </a>
                            @endif
                            {{-- <a href="{{ url()->previous() }}" class="btn btn-link"> --}}
                                {{-- <i class="fa fa-arrow-left"></i> --}}
                                {{-- <span>{{ trans('driver/account.button.back') }}</span> --}}
                            {{-- </a> --}}
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@stop
