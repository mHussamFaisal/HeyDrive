@extends('admin.index')

@section('title', trans('admin/scheduled_routes.page_title') .' / '. $scheduledRoute->getName())
@section('subtitle', /*'<i class="fa fa-compass"></i> '*/ '<a href="'. route('admin.scheduled-routes.index') .'">'. trans('admin/scheduled_routes.page_title') .'</a> / '. $scheduledRoute->getName())

@section('subcontent')
  @include('partials.modals.delete')

  <div id="scheduled-routes">
    <div class="row">
      <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">

        <div class="widget-user-2">
          <div class="widget-user-header clearfix">
            <h3 style="margin:0px;">{{ $scheduledRoute->getName() }}</h3>
          </div>
          <div>
            <ul class="list-group list-group-unbordered details-list">
              @if( !empty($scheduledRoute->driver->id) )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/scheduled_routes.driver_id') }}:</span>
                  <span class="details-list-value">
                    <a href="{{ route('admin.users.show', $scheduledRoute->driver->id) }}" class="text-default">{{ $scheduledRoute->driver->getName(true) }}</a>
                  </span>
                </li>
              @endif

              @if( !empty($scheduledRoute->vehicle->id) )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/scheduled_routes.vehicle_id') }}:</span>
                  <span class="details-list-value">
                    <a href="{{ route('admin.users.show', $scheduledRoute->vehicle->id) }}" class="text-default">{{ $scheduledRoute->vehicle->getName() }}</a>
                  </span>
                </li>
              @endif

              @if( !empty($scheduledRoute->vehicleType->id) )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/scheduled_routes.vehicle_type_id') }}:</span>
                  <span class="details-list-value">
                    <a href="{{ route('admin.vehicles-types.index') }}" class="text-default">{{ $scheduledRoute->vehicleType->getName() }}</a>
                  </span>
                </li>
              @endif

              @if( $scheduledRoute->getParams() )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/scheduled_routes.params') }}:</span>
                  <span class="details-list-value">{!! $scheduledRoute->getParams() !!}</span>
                </li>
              @endif
              <li class="list-group-item">
                <span class="details-list-title">{{ trans('admin/scheduled_routes.is_featured') }}:</span>
                <span class="details-list-value">
                  @permission('admin.scheduled_routes.edit')
                    <a href="{{ route('admin.scheduled-routes.featured', [$scheduledRoute->id, $scheduledRoute->is_featured ? 'no' : 'yes']) }}" title="{{ trans('admin/scheduled_routes.'. ($scheduledRoute->is_featured ? 'yes' : 'no')) }}" class="text-default">
                      <i class="fa {{ $scheduledRoute->is_featured ? 'fa-star' : 'fa-star-o' }}"></i>
                    </a>
                  @else
                    <i class="fa {{ $scheduledRoute->is_featured ? 'fa-star' : 'fa-star-o' }}"></i>
                  @endpermission
                </span>
              </li>
              <li class="list-group-item">
                <span class="details-list-title">{{ trans('admin/scheduled_routes.status') }}:</span>
                <span class="details-list-value">
                  @permission('admin.scheduled_routes.edit')
                    <a href="{{ route('admin.scheduled-routes.status', [$scheduledRoute->id, $scheduledRoute->status == 'active' ? 'inactive' : 'active']) }}">
                      {!! $scheduledRoute->getStatus('label') !!}
                    </a>
                  @else
                    {!! $scheduledRoute->getStatus('label') !!}
                  @endpermission
                </span>
              </li>
              @if( $scheduledRoute->updated_at )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/scheduled_routes.updated_at') }}:</span>
                  <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($scheduledRoute->updated_at) }}</span>
                </li>
              @endif
              @if( $scheduledRoute->created_at )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/scheduled_routes.created_at') }}:</span>
                  <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($scheduledRoute->created_at) }}</span>
                </li>
              @endif
            </ul>
            <div class="row">
              <div class="col-sm-12">

                @permission('admin.scheduled_routes.edit')
                <a href="{{ route('admin.scheduled-routes.edit', $scheduledRoute->id) }}" class="btn btn-primary">
                  {{-- <span><i class="fa fa-pencil-square-o"></i></span> --}}
                  <span>{{ trans('admin/scheduled_routes.button.edit') }}</span>
                </a>
                @endpermission

                @permission('admin.scheduled_routes.destroy')
                <a href="#" onclick="$('#button_delete_id_{{ $scheduledRoute->id }}').click(); return false;" class="btn btn-default">
                  {{-- <span class="icon-box" style="display:inline-block; width:20px; text-align:center;"><i class="fa fa-trash"></i></span> --}}
                  <span>{{ trans('admin/scheduled_routes.button.destroy') }}</span>
                </a>
                @endpermission

                @permission('admin.bookings.index')
                <a href="{{ route('admin.bookings.index', ['scheduled_route' => $scheduledRoute->id]) }}" class="btn btn-default">
                  {{-- <span class="icon-box" style="display:inline-block; width:20px; text-align:center;"><i class="fa fa-tasks"></i></span> --}}
                  <span>{{ trans('admin/scheduled_routes.button.bookings') }}</span>
                </a>
                @endpermission

                <a href="{{ url()->previous() != url()->full() ? url()->previous() : route('admin.scheduled-routes.index') }}" class="btn btn-link">
                  <span>{{ trans('admin/scheduled_routes.button.back') }}</span>
                </a>

                {!! Form::open(['method' => 'delete', 'route' => ['admin.scheduled-routes.destroy', $scheduledRoute->id], 'class' => 'form-inline form-delete hide']) !!}
                  {!! Form::button(trans('admin/scheduled_routes.button.destroy'), [
                    'type' => 'submit',
                    'class' => 'delete',
                    'name' => 'delete_modal',
                    'id' => 'button_delete_id_'. $scheduledRoute->id,
                  ]) !!}
                {!! Form::close() !!}
              </div>
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
