@extends('admin.index')

@section('title', trans('admin/vehicles.page_title') .' / '. $vehicle->getName())
@section('subtitle', /*'<i class="fa fa-car"></i> '*/ '<a href="'. route('admin.vehicles.index') .'">'. trans('admin/vehicles.page_title') .'</a> / '. $vehicle->getName())

@section('subcontent')
    @include('partials.modals.delete')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">

            <div class="widget-user-2">
                <div class="widget-user-header clearfix">
                    <div class="widget-user-image">
                        <img class="img-circle" src="{{ asset( $vehicle->getImagePath() ) }}" alt="">
                    </div>
                    <h3 class="widget-user-username" style="margin-top:15px; margin-bottom:15px;">{{ $vehicle->getName() }}</h3>
                </div>
                <div>
                    <ul class="list-group list-group-unbordered details-list">
                        @if( $vehicle->user )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.user') }}:</span>
                                <span class="details-list-value">
                                    @permission('admin.users.driver.show')
                                        <a href="{{ route('admin.users.show', $vehicle->user_id) }}" class="text-default">
                                            {{ $vehicle->user->getName(true) }}
                                        </a>
                                    @else
                                        {{ $vehicle->user->getName(true) }}
                                    @endpermission
                                </span>
                            </li>
                        @endif
                        @if( $vehicle->vehicle_type_id && !empty($vehicle->vehicleType->name) )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.vehicle_type') }}:</span>
                                <span class="details-list-value">{{ $vehicle->vehicleType->getName() }}</span>
                            </li>
                        @endif
                        @if( $vehicle->registration_mark )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.registration_mark') }}:</span>
                                <span class="details-list-value">{{ $vehicle->registration_mark }}</span>
                            </li>
                        @endif
                        @if( $vehicle->mot )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.mot') }}:</span>
                                <span class="details-list-value">{{ $vehicle->mot }} {!! $vehicle->getExpiryDate('mot_expiry_date') !!}</span>
                            </li>
                        @endif
                        @if( $vehicle->make )
                        <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.make') }}:</span>
                                <span class="details-list-value">{{ $vehicle->make }}</span>
                            </li>
                        @endif
                        @if( $vehicle->model )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.model') }}:</span>
                                <span class="details-list-value">{{ $vehicle->model }}</span>
                            </li>
                        @endif
                        @if( $vehicle->colour )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.colour') }}:</span>
                                <span class="details-list-value">{{ $vehicle->colour }}</span>
                            </li>
                        @endif
                        @if( $vehicle->body_type )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.body_type') }}:</span>
                                <span class="details-list-value">{{ $vehicle->body_type }}</span>
                            </li>
                        @endif
                        @if( $vehicle->no_of_passengers )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.no_of_passengers') }}:</span>
                                <span class="details-list-value">{{ $vehicle->no_of_passengers }}</span>
                            </li>
                        @endif
                        @if( $vehicle->registered_keeper_name )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.registered_keeper_name') }}:</span>
                                <span class="details-list-value">{{ $vehicle->registered_keeper_name }}</span>
                            </li>
                        @endif
                        @if( $vehicle->registered_keeper_address )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.registered_keeper_address') }}:</span>
                                <span class="details-list-value">{{ $vehicle->registered_keeper_address }}</span>
                            </li>
                        @endif
                        @if( $vehicle->description )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('admin/vehicles.description') }}:</span>
                                <span class="details-list-value">{{ $vehicle->description }}</span>
                            </li>
                        @endif
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/vehicles.status') }}:</span>
                            <span class="details-list-value">
                                @permission('admin.vehicles.edit')
                                    <a href="{{ route('admin.vehicles.status', [$vehicle->id, ($vehicle->status == 'activated') ? 'inactive' : 'activated']) }}" class="text-success status-icon">
                                        {!! $vehicle->getStatus('label') !!}
                                    </a>
                                @else
                                    {!! $vehicle->getStatus('label') !!}
                                @endpermission
                            </span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/vehicles.selected') }}:</span>
                            <span class="details-list-value">
                                @permission('admin.vehicles.edit')
                                    @if( $vehicle->selected )
                                        <a href="{{ route('admin.vehicles.selected', [$vehicle->id, 'no']) }}" class="text-success status-icon" title="{{ trans('admin/vehicles.yes') }}"><i class="fa fa-check-circle"></i></a>
                                    @else
                                        <a href="{{ route('admin.vehicles.selected', [$vehicle->id, 'yes']) }}" class="text-danger status-icon" title="{{ trans('admin/vehicles.no') }}"><i class="fa fa-times-circle"></i></a>
                                    @endif
                                @else
                                    @if( $vehicle->selected )
                                        <i class="fa fa-check-circle"></i>
                                    @else
                                        <i class="fa fa-times-circle"></i>
                                    @endif
                                @endpermission
                            </span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/vehicles.updated_at') }}:</span>
                            <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($vehicle->updated_at) }}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('admin/vehicles.created_at') }}:</span>
                            <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($vehicle->created_at) }}</span>
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-sm-12">
                            @permission('admin.vehicles.edit')
                            <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-primary">
                                {{-- <i class="fa fa-edit"></i>  --}}
                                <span>{{ trans('admin/vehicles.button.edit') }}</span>
                            </a>
                            @endpermission

                            @permission('admin.vehicles.destroy')
                            {!! Form::open(['method' => 'delete', 'route' => ['admin.vehicles.destroy', $vehicle->id], 'class' => 'form-inline form-delete']) !!}
                                {!! Form::button(/*'<i class="fa fa-trash-o"></i> <span>'. */trans('admin/vehicles.button.destroy') .'</span>', ['title' => trans('admin/vehicles.button.destroy'), 'type' => 'submit', 'class' => 'btn btn-default delete', 'name' => 'delete_modal']) !!}
                            {!! Form::close() !!}
                            @endpermission

                            @if( request('noBack') != '1' )
                                <a href="{{ url()->previous() != url()->full() ? url()->previous() : route('admin.vehicles.index') }}" class="btn btn-link">
                                    {{-- <i class="fa fa-arrow-left"></i>  --}}
                                    <span>{{ trans('admin/vehicles.button.back') }}</span>
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
