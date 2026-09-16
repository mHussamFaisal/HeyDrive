@extends('admin.index')

@section('title', trans('admin/feedback.page_title') .' / '. $feedback->getName())
@section('subtitle', /*'<i class="fa fa-comments-o"></i> '*/ '<a href="'. route('admin.feedback.index', request('type') ? ['type' => request('type')] : []) .'">'. trans('admin/feedback.page_title') .'</a> / '. $feedback->getName())

@section('subcontent')
  @include('partials.modals.delete')

  <div id="feedback">
    <div class="row">
      <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">

        <div class="widget-user-2">
          <div class="widget-user-header clearfix">
            <h3 style="margin:0px;">{{ $feedback->getName() }}</h3>
          </div>
          <div>
            <ul class="list-group list-group-unbordered details-list">
              @if( $feedback->description )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/feedback.description') }}:</span>
                  <span class="details-list-value">{!! App\Helpers\SiteHelper::nl2br2($feedback->description) !!}</span>
                </li>
              @endif
              @if( $feedback->ref_number )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/feedback.ref_number') }}:</span>
                  <span class="details-list-value">{!! $feedback->getRefNumberLink(['class'=>'text-default']) !!}</span>
                </li>
              @endif
              @if( $feedback->email )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/feedback.email') }}:</span>
                  <span class="details-list-value">{!! App\Helpers\SiteHelper::mailtoLink($feedback->email, ['class'=>'text-default']) !!}</span>
                </li>
              @endif
              @if( $feedback->phone )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/feedback.phone') }}:</span>
                  <span class="details-list-value">{!! App\Helpers\SiteHelper::telLink($feedback->phone, ['class'=>'text-default']) !!}</span>
                </li>
              @endif
              <li class="list-group-item">
                <span class="details-list-title">{{ trans('admin/feedback.type') }}:</span>
                <span class="details-list-value">{!! $feedback->getTypeLink(['class'=>'text-default']) !!}</span>
              </li>
              @if( $feedback->getParams() )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/feedback.params') }}:</span>
                  <span class="details-list-value">{!! $feedback->getParams() !!}</span>
                </li>
              @endif

              @php $files = $feedback->getFiles(); @endphp
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

              <li class="list-group-item">
                <span class="details-list-title">{{ trans('admin/feedback.status') }}:</span>
                <span class="details-list-value">
                  @permission('admin.feedback.edit')
                    <a href="{{ route('admin.feedback.status', [$feedback->id, $feedback->status == 'active' ? 'inactive' : 'active']) }}">
                      {!! $feedback->getStatus('label') !!}
                    </a>
                  @else
                    {!! $feedback->getStatus('label') !!}
                  @endpermission
                </span>
              </li>
              @if( $feedback->updated_at )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/feedback.updated_at') }}:</span>
                  <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($feedback->updated_at) }}</span>
                </li>
              @endif
              @if( $feedback->created_at )
                <li class="list-group-item">
                  <span class="details-list-title">{{ trans('admin/feedback.created_at') }}:</span>
                  <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($feedback->created_at) }}</span>
                </li>
              @endif
            </ul>
            <div class="row">
              <div class="col-sm-12">

                @permission('admin.feedback.edit')
                <a href="{{ route('admin.feedback.edit', array_merge(['id' => $feedback->id], request('type') ? ['type' => request('type')] : [])) }}" class="btn btn-primary">
                  {{-- <span><i class="fa fa-pencil-square-o"></i></span> --}}
                  <span>{{ trans('admin/feedback.button.edit') }}</span>
                </a>
                @endpermission

                @permission('admin.feedback.destroy')
                <a href="#" onclick="$('#button_delete_id_{{ $feedback->id }}').click(); return false;" class="btn btn-default">
                  {{-- <span class="icon-box"><i class="fa fa-trash"></i></span> --}}
                  <span>{{ trans('admin/feedback.button.destroy') }}</span>
                </a>
                @endpermission

                <a href="{{ url()->previous() != url()->full() ? url()->previous() : route('admin.feedback.index') }}" class="btn btn-link">
                  <span>{{ trans('admin/feedback.button.back') }}</span>
                </a>

                {!! Form::open(['method' => 'delete', 'url' => route('admin.feedback.destroy', array_merge(['id' => $feedback->id], request('type') ? ['type' => request('type')] : [])), 'class' => 'form-inline form-delete hide']) !!}
                  {!! Form::button(trans('admin/feedback.button.destroy'), [
                    'type' => 'submit',
                    'class' => 'delete',
                    'name' => 'delete_modal',
                    'id' => 'button_delete_id_'. $feedback->id,
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
