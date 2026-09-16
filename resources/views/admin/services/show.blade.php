@extends('admin.index')

@section('title', trans('admin/services.page_title') .' / '. $service->getName())
@section('subtitle', /*'<i class="fa fa-sliders"></i> '*/ '<a href="'. route('admin.services.index') .'">'. trans('admin/services.page_title') .'</a> / '. $service->getName())

@section('subcontent')
  @include('partials.modals.delete')

  <div id="services">
    <div class="row">
      <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">

        <div class="widget-user-2">
          <div class="widget-user-header clearfix">
            <h3 style="margin:0px;">{{ $service->getName() }}</h3>
          </div>
          <div>
            <ul class="list-group list-group-unbordered details-list">
              @if( $service->description )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/services.description') }}:</span>
                  <span class="details-list-value">{{ $service->description }}</span>
                </li>
              @endif
              <li class="list-group-item">
                <span class="details-list-title">{{ trans('admin/services.type') }}:</span>
                <span class="details-list-value">{{ trans('admin/services.types.'. $service->type) }}</span>
              </li>
              @if( $service->getParams() )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/services.params') }}:</span>
                  <span class="details-list-value">{!! $service->getParams() !!}</span>
                </li>
              @endif
              <li class="list-group-item">
                <span class="details-list-title">{{ trans('admin/services.is_featured') }}:</span>
                <span class="details-list-value">
                  @permission('admin.services.edit')
                    <a href="{{ route('admin.services.featured', [$service->id, $service->is_featured ? 'no' : 'yes']) }}" title="{{ trans('admin/services.'. ($service->is_featured ? 'yes' : 'no')) }}" class="text-default">
                      <i class="fa {{ $service->is_featured ? 'fa-star' : 'fa-star-o' }}"></i>
                    </a>
                  @else
                    <i class="fa {{ $service->is_featured ? 'fa-star' : 'fa-star-o' }}"></i>
                  @endpermission
                </span>
              </li>
              <li class="list-group-item">
                <span class="details-list-title">{{ trans('admin/services.status') }}:</span>
                <span class="details-list-value">
                  @permission('admin.services.edit')
                    <a href="{{ route('admin.services.status', [$service->id, $service->status == 'active' ? 'inactive' : 'active']) }}">
                      {!! $service->getStatus('label') !!}
                    </a>
                  @else
                    {!! $service->getStatus('label') !!}
                  @endpermission
                </span>
              </li>
              @if( $service->updated_at )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/services.updated_at') }}:</span>
                  <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($service->updated_at) }}</span>
                </li>
              @endif
              @if( $service->created_at )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/services.created_at') }}:</span>
                  <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($service->created_at) }}</span>
                </li>
              @endif
            </ul>
            <div class="row">
              <div class="col-sm-12">

                @permission('admin.services.edit')
                <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-primary">
                  {{-- <span><i class="fa fa-pencil-square-o"></i></span> --}}
                  <span>{{ trans('admin/services.button.edit') }}</span>
                </a>
                @endpermission

                @permission('admin.services.destroy')
                <a href="#" onclick="$('#button_delete_id_{{ $service->id }}').click(); return false;" class="btn btn-default">
                  {{-- <span class="icon-box"><i class="fa fa-trash"></i></span> --}}
                  <span>{{ trans('admin/services.button.destroy') }}</span>
                </a>
                @endpermission

                <a href="{{ url()->previous() != url()->full() ? url()->previous() : route('admin.services.index') }}" class="btn btn-link">
                  <span>{{ trans('admin/services.button.back') }}</span>
                </a>

                {!! Form::open(['method' => 'delete', 'route' => ['admin.services.destroy', $service->id], 'class' => 'form-inline form-delete hide']) !!}
                  {!! Form::button(trans('admin/services.button.destroy'), [
                    'type' => 'submit',
                    'class' => 'delete',
                    'name' => 'delete_modal',
                    'id' => 'button_delete_id_'. $service->id,
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
