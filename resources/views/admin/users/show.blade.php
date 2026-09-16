@extends('admin.index')

@section('title', trans('admin/users.page_title') .' / '. $user->getName(true))
@section('subtitle', /*'<i class="fa fa-users"></i> '*/ '<a href="'. route('admin.users.index') .'">'. trans('admin/users.page_title') .'</a> / '. $user->getName(true))

@section('subcontent')
    @include('partials.modals.delete')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">

            <div class="widget-user-2">
                <div class="widget-user-header clearfix">

                    <div class="row">
                        <div class="col-xs-12 col-sm-7">
                            @php
                            if ($user->isOnline()) {
                                $title = trans('admin/users.online');
                                $class = 'is-online-avatar';
                            }
                            else {
                                $title = trans('admin/users.offline');
                                $class = '';
                            }
                            $title .= ' - '. trans('admin/users.last_seen_at') .': '. (!empty($user->last_seen_at) ? App\Helpers\SiteHelper::formatDateTime($user->last_seen_at) : trans('admin/users.last_seen_never'));
                            @endphp

                            <div class="widget-user-image">
                                <img class="img-circle {{ $class }}" src="{{ asset( $user->getAvatarPath() ) }}" alt="" title="{{ $title }}">
                            </div>
                            <h3 class="widget-user-username">{{ $user->getName(true) }}</h3>
                            <h5 class="widget-user-desc">{{ trans('admin/users.member_since') }} {{ Carbon\Carbon::parse($user->getOriginal('created_at'))->diffForHumans(null, true, false, 2) }}</h5>

                        </div>
                        <div class="col-xs-12 col-sm-5">

                            <div class="pull-right" style="margin-top:10px;">
                                @if( $user->hasRole('driver.*') && auth()->user()->hasPermission('admin.bookings.index') )
                                    <a href="{{ route('admin.bookings.index') }}?driver={{ $user->id }}" class="btn btn-info" title="{{ trans('admin/users.button.jobs') }}">
                                        {{-- <i class="fa fa-calendar"></i> --}}
                                        {{ trans('admin/users.button.jobs') }}
                                    </a>
                                @endif

                                @if( $user->hasRole('customer.*') && auth()->user()->hasPermission('admin.bookings.index') )
                                    <a href="{{ route('admin.bookings.index') }}?user={{ $user->id }}" class="btn btn-info" title="{{ trans('admin/users.button.bookings') }}">
                                        {{-- <i class="fa fa-calendar"></i> --}}
                                        {{ trans('admin/users.button.bookings') }}
                                    </a>
                                @endif

                                @if( $user->vehicles->count() && auth()->user()->hasPermission('admin.vehicles.index') )
                                    <a href="{{ route('admin.vehicles.index') }}?user={{ $user->id }}" class="btn btn-info" title="{{ trans('admin/users.button.vehicles') }}">
                                        {{-- <i class="fa fa-car"></i> --}}
                                        {{ trans('admin/users.button.vehicles') }}
                                    </a>
                                @endif
                            </div>

                        </div>
                    </div>

                </div>
                <div>
                    @php
                    $htmlTeams = [];
                    if (config('eto.allow_teams')) {
                        foreach ($user->teams as $team) {
                            if (auth()->user()->hasPermission(['admin.teams.index', 'admin.teams.show'])) {
                                $htmlTeams[] = '<a href="'. route('teams.show', $team->id) .'" class="text-default">'. $team->getName() .'</a>';
                            }
                            else {
                                $htmlTeams[] = $team->getName();
                            }
                        }
                    }

                    $htmlRoles = [];
                    foreach ($user->roles as $role) {
                        if (auth()->user()->hasPermission(['admin.users.admin.index', 'admin.users.driver.index', 'admin.users.customer.index'])) {
                            $htmlRoles[] = '<a href="'. route('admin.users.index') .'?role='. $role->getSlugGroup() .'" class="text-default">'. $role->getName() .'</a>';
                        }
                        else {
                            $htmlRoles[] = $role->getName();
                        }
                    }
                    @endphp

                    <ul class="list-group list-group-unbordered details-list">
                        @if (config('eto.allow_fleet_operator') && $user->fleet_id)
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/users.fleet_id') }}:</span>
                                <span class="details-list-value">
                                  @permission('admin.users.admin.show')
                                      <a href="{{ route('admin.users.show', $user->fleet_id) }}" class="text-default">{{ $user->getFleetName() }}</a>
                                  @else
                                      {{ $user->getFleetName() }}
                                  @endpermission
                                </span>
                            </li>
                        @endif

                        @if (!empty($htmlTeams))
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/users.teams') }}:</span>
                                <span class="details-list-value">{!! implode(', ', $htmlTeams) !!}</span>
                            </li>
                        @endif

                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/users.role') }}:</span>
                            <span class="details-list-value">{!! implode(', ', $htmlRoles) !!}</span>
                        </li>

                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/users.username') }}:</span>
                            <span class="details-list-value">{{ $user->username }}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/users.email') }}:</span>
                            <span class="details-list-value">{!! $user->getEmailLink(['class'=>'text-default']) !!}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/users.locale') }}:</span>
                            <span class="details-list-value">
                                @php $locale = $user->getSetting('app.locale') !== null ? $user->getSetting('app.locale') : config('app.locale'); @endphp
                                {{ config('app.locales')[$locale]['name'] }}

                                @if( config('app.locales')[$locale]['name'] != config('app.locales')[$locale]['native'] )
                                    ({{ config('app.locales')[$locale]['native'] }})
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/users.timezone') }}:</span>
                            <span class="details-list-value">
                                {{ $user->getSetting('app.timezone') !== null ? $user->getSetting('app.timezone') : config('app.timezone') }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/users.status') }}:</span>
                            <span class="details-list-value">
                                @permission('admin.users.admin.edit')
                                    <a href="{{ route('admin.users.status', [$user->id, ($user->status == 'approved') ? 'inactive' : 'approved']) }}" class="text-success status-icon">
                                        {!! $user->getStatus('label') !!}
                                    </a>
                                @else
                                    {!! $user->getStatus('label') !!}
                                @endpermission
                            </span>
                        </li>
                        @if( $user->profile )

                            @if( $user->profile->availability_status )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.availability_status') }}:</span>
                                    <span class="details-list-value">
                                        {!! $user->profile->getAvailabilityStatus() !!}
                                    </span>
                                </li>
                            @endif

                            @if( $user->profile->unique_id )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.unique_id') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->unique_id }}</span>
                                </li>
                            @endif
                            @if( $user->profile->commission )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.commission') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->getCommission() }}</span>
                                </li>
                            @endif
                            @if( $user->profile->date_of_birth )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.date_of_birth') }}:</span>
                                    <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($user->profile->date_of_birth, 'date') }}</span>
                                </li>
                            @endif
                            @if( $user->profile->mobile_no )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.mobile_no') }}:</span>
                                    <span class="details-list-value">{!! $user->profile->getTelLink('mobile_no', ['class'=>'text-default']) !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->telephone_no )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.telephone_no') }}:</span>
                                    <span class="details-list-value">{!! $user->profile->getTelLink('telephone_no', ['class'=>'text-default']) !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->emergency_no )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.emergency_no') }}:</span>
                                    <span class="details-list-value">{!! $user->profile->getTelLink('emergency_no', ['class'=>'text-default']) !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->address )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.address') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->address }}</span>
                                </li>
                            @endif
                            @if( $user->profile->city )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.city') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->city }}</span>
                                </li>
                            @endif
                            @if( $user->profile->postcode )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.postcode') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->postcode }}</span>
                                </li>
                            @endif
                            @if( $user->profile->state )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.state') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->state }}</span>
                                </li>
                            @endif
                            @if( $user->profile->country )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.country') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->country }}</span>
                                </li>
                            @endif
                            @if( $user->profile->profile_type == 'company' )

                                @if( $user->profile->getProfileType() )
                                    <li class="list-group-item">
                                        <span class="details-list-title">{{ trans('admin/users.profile_type') }}:</span>
                                        <span class="details-list-value">{{ $user->profile->getProfileType() }}</span>
                                    </li>
                                @endif
                                @if( $user->profile->company_name )
                                    <li class="list-group-item">
                                        <span class="details-list-title">{{ trans('admin/users.company_name') }}:</span>
                                        <span class="details-list-value">{{ $user->profile->company_name }}</span>
                                    </li>
                                @endif
                                @if( $user->profile->company_number )
                                    <li class="list-group-item">
                                        <span class="details-list-title">{{ trans('admin/users.company_number') }}:</span>
                                        <span class="details-list-value">{{ $user->profile->company_number }}</span>
                                    </li>
                                @endif
                                @if( $user->profile->company_tax_number )
                                    <li class="list-group-item">
                                        <span class="details-list-title">{{ trans('admin/users.company_tax_number') }}:</span>
                                        <span class="details-list-value">{{ $user->profile->company_tax_number }}</span>
                                    </li>
                                @endif

                            @endif
                            @if( $user->profile->national_insurance_no )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.national_insurance_no') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->national_insurance_no }}</span>
                                </li>
                            @endif
                            @if( $user->profile->bank_account )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.bank_account') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->bank_account }}</span>
                                </li>
                            @endif
                            @if( $user->profile->insurance )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.insurance') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->insurance }} {!! $user->profile->getExpiryDate('insurance_expiry_date') !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->driving_licence )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.driving_licence') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->driving_licence }} {!! $user->profile->getExpiryDate('driving_licence_expiry_date') !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->pco_licence )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.pco_licence') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->pco_licence }} {!! $user->profile->getExpiryDate('pco_licence_expiry_date') !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->phv_licence )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.phv_licence') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->phv_licence }} {!! $user->profile->getExpiryDate('phv_licence_expiry_date') !!}</span>
                                </li>
                            @endif
                            @if( $user->profile->description )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.description') }}:</span>
                                    <span class="details-list-value">{{ $user->profile->description }}</span>
                                </li>
                            @endif
                            @if( $user->profile->getAvailability('list') )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.availability') }}:</span>
                                    <span class="details-list-value">{!! $user->profile->getAvailability('list') !!}</span>
                                </li>
                            @endif

                            @php $files = $user->profile->getFiles(); @endphp
                            @if( $files )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.files') }}:</span>
                                    <span class="details-list-value">
                                        @foreach($files as $file)
                                            <a href="{{ $file->path }}">{{ $file->name }}</a><br />
                                        @endforeach
                                    </span>
                                </li>
                            @endif

                            @if( $user->vehicles->count() )
                                <li class="list-group-item">
                                    <span class="details-list-title">{{ trans('admin/users.vehicles') }}:</span>
                                    <span class="details-list-value">
                                        @foreach($user->vehicles as $vehicle)
                                            @permission('admin.vehicles.show')
                                                <a href="{{ route('admin.vehicles.show', $vehicle->id) }}">{{ $vehicle->name }}</a>
                                            @else
                                                {{ $vehicle->name }}
                                            @endpermission
                                            <br />
                                        @endforeach
                                    </span>
                                </li>
                            @endif

                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/users.updated_at') }}:</span>
                                <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($user->updated_at) }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/users.created_at') }}:</span>
                                <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($user->created_at) }}</span>
                            </li>

                        @endif
                    </ul>
                    <div class="row">
                        <div class="col-sm-12">
                            @if (auth()->user()->id == $user->id || auth()->user()->hasPermission(['admin.users.admin.edit', 'admin.users.driver.edit', 'admin.users.customer.edit']))
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                                    {{-- <i class="fa fa-edit"></i>  --}}
                                    <span>{{ trans('admin/users.button.edit') }}</span>
                                </a>
                            @endif

                            @if (auth()->user()->id != $user->id && auth()->user()->hasPermission(['admin.users.admin.destroy', 'admin.users.driver.destroy', 'admin.users.customer.destroy']))
                                {!! Form::open(['method' => 'delete', 'route' => ['admin.users.destroy', $user->id], 'class' => 'form-inline form-delete']) !!}
                                    {!! Form::button(/*'<i class="fa fa-trash-o"></i> <span>'. */trans('admin/users.button.destroy') .'</span>', ['title' => trans('admin/users.button.destroy'), 'type' => 'submit', 'class' => 'btn btn-default delete', 'name' => 'delete_modal']) !!}
                                {!! Form::close() !!}
                            @endif

                            @if( request('noBack') != '1' )
                                <a href="{{ url()->previous() != url()->full() ? url()->previous() : route('admin.users.index') }}" class="btn btn-link">
                                    {{-- <i class="fa fa-arrow-left"></i>  --}}
                                    <span>{{ trans('admin/users.button.back') }}</span>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@stop

@section('subfooter')
    <script type="text/javascript">
    $('.form-delete').on('click', function(e){
        e.preventDefault();
        var $form = $(this);
        $('#modal-delete').modal().on('click', '#delete-btn', function(){
            $form.submit();
        });
    });
    </script>
@stop
