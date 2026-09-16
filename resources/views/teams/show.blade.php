@extends('admin.index')

@section('title', trans('teams.page_title') .' / '. $team->getName())
@section('subtitle', '<a href="'. route('teams.index') .'">'. trans('teams.page_title') .'</a> / '. $team->getName())

@section('subcontent')
    @include('partials.modals.delete')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">

            <h3 class="widget-user-username" style="margin-top:5px; margin-bottom:5px;">
                {{ $team->getName() }}
            </h3>
            <ul class="list-group list-group-unbordered details-list">
                @permission(['admin.users.admin.index', 'admin.users.driver.index', 'admin.users.customer.index'])
                @if ($team->users->count())
                <li class="list-group-item">
                    <span class="details-list-title">{{ trans('teams.button.users') }}:</span>
                    <span class="details-list-value">
                        <a href="{{ route('admin.users.index') }}?team={{ $team->id }}" class="text-default">
                            {{ trans('teams.button.show_all') }}
                        </a>
                    </span>
                </li>
                @endif
                @endpermission

                @if ($team->internal_note)
                    <li class="list-group-item">
                        <span class="details-list-title">{{ trans('teams.internal_note') }}:</span>
                        <span class="details-list-value">{{ $team->internal_note }}</span>
                    </li>
                @endif
                <li class="list-group-item">
                    <span class="details-list-title">{{ trans('teams.status') }}:</span>
                    <span class="details-list-value">
                        @permission('admin.teams.edit')
                            <a href="{{ route('teams.status', [$team->id, ($team->status == 1) ? 'inactive' : 'active']) }}" class="text-success status-icon">
                                {!! $team->getStatus('label') !!}
                            </a>
                        @else
                            {!! $team->getStatus('label') !!}
                        @endpermission
                    </span>
                </li>
                @if ($team->order)
                <li class="list-group-item">
                    <span class="details-list-title">{{ trans('teams.order') }}:</span>
                    <span class="details-list-value">{{ $team->order }}</span>
                </li>
                @endif
                <li class="list-group-item">
                    <span class="details-list-title">{{ trans('teams.updated_at') }}:</span>
                    <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($team->updated_at) }}</span>
                </li>
                <li class="list-group-item">
                    <span class="details-list-title">{{ trans('teams.created_at') }}:</span>
                    <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($team->created_at) }}</span>
                </li>
            </ul>
            <div class="row">
                <div class="col-sm-12">
                    @permission('admin.teams.edit')
                    <a href="{{ route('teams.edit', $team->id) }}" class="btn btn-primary">
                        <span>{{ trans('teams.button.edit') }}</span>
                    </a>
                    @endpermission

                    @permission('admin.teams.destroy')
                    {!! Form::open(['method' => 'delete', 'route' => ['teams.destroy', $team->id], 'class' => 'form-inline form-delete']) !!}
                        {!! Form::button(trans('teams.button.destroy') .'</span>', ['title' => trans('teams.button.destroy'), 'type' => 'submit', 'class' => 'btn btn-default delete', 'name' => 'delete_modal']) !!}
                    {!! Form::close() !!}
                    @endpermission

                    @if( request('noBack') != '1' )
                        <a href="{{ url()->previous() != url()->full() ? url()->previous() : route('teams.index') }}" class="btn btn-link">
                            <span>{{ trans('teams.button.back') }}</span>
                        </a>
                    @endif
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
