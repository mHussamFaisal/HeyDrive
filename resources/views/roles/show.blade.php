@php
$permissions = $item->deleted_at !== null ? $item->permissions : $item['permissions'];
$users = $item->deleted_at !== null ? $item->users : $item['users'];
@endphp

@extends('admin.index')

@section('title', trans('roles.titles.show_role_title'))
@section('subtitle', /*'<i class="fa fa-user-secret "></i> '.*/ trans('roles.titles.show_role_title') )

@section('subcontent')
    <div class="box-header col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3" style="left: -6px;">
        <h4 class="box-title">
            @if(isset($typeDeleted))
                {!! trans('roles.titles.show_role_deleted', ['name' => $item->name]) !!}
            @else
                {!! trans('roles.titles.show_role', ['name' => $item->name]) !!}
            @endif
        </h4>

        <div class="box-tools pull-right" style="right: 0;"></div>
    </div>

    @include('roles.partials.form-status')

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            <ul class="list-group list-group-unbordered details-list">
                @role('service')
                <li class="list-group-item">
                    <span class="details-list-title">{!! trans('roles.table.id') !!}:</span>
                    <span class="details-list-value"> {{ $item->id }}</span>
                </li>
                <li class="list-group-item">
                    <span class="details-list-title">{!! trans('roles.table.name') !!}:</span>
                    <span class="details-list-value"> {{ $item->name }}</span>
                </li>
                @endrole
                @if(!empty($item->desc))
                <li class="list-group-item">
                    <span class="details-list-title">{!! trans('roles.table.desc') !!}:</span>
                    <span class="details-list-value"> {{ $item->desc }}</span>
                </li>
                @endif
{{--                <li class="list-group-item">--}}
{{--                    <span class="details-list-title">{!! trans('roles.table.role.level') !!}:</span>--}}
{{--                    <span class="details-list-value"> {{ $item->level }}</span>--}}
{{--                </li>--}}
                <li id="accordion_roles_users" class="list-group-item">
                    <span class="details-list-title" @if($users->count() > 0) data-toggle="tooltip" title="{{ trans("roles.tooltips.show-hide") }}" @endif>
                        {!! trans('roles.table.users') !!}:
                    </span>
                    <span class="details-list-value">
                        <a href="{{ route('admin.users.index') }}?role={{ $item->getSlugGroup() }}" class="text-default">{{ trans('roles.go_to_users') }}</a>
                    </span>
                </li>
                <li class="list-group-item">
                    <span class="details-list-title" @if($permissions->count() > 0) data-toggle="tooltip" title="{{ trans("roles.tooltips.show-hide") }}" @endif>
                        {!! trans('roles.form.role-permissions.label') !!}:
                    </span>
                    <span class="details-list-value">
                        @if($item->slug == config('roles.role_has_all_permissions'))
                            {!! trans('roles.table.all_count') !!}
                        @else
                            @if($permissions->count() > 0)
                                @foreach ($groups as $roleRoot=>$group)
                                    @foreach ($group as $actions)
                                        {{-- {{dump($roleRoot, $actions, $item->permissions_group)}} --}}
                                        @if(!empty($item->permissions_group[$roleRoot]) && (in_array($actions['group'], $item->permissions_group[$roleRoot]) || in_array($actions['slug'], config('roles.make_duplicate'))))
                                        <div class="eto-permission-item">
                                            <span class="eto-permission-item-group-name">{{ $actions['name'] }}</span>
                                            <div class="eto-permission-item-actions">
                                                @foreach ($actions['permissions'] as $permission)
                                                    @php
                                                    $hasPermission = count($item->permissions) > 0 && $item->permissions->contains(function ($value) use ($permission) {
                                                        return (string)$value->slug === (string)$permission->slug ||
                                                            \Illuminate\Support\Str::is($permission->slug, $value->slug) ||
                                                            \Illuminate\Support\Str::is($value->slug, $permission->slug);
                                                    })
                                                    @endphp
                                                    <div class="eto-check-inline">
                                                        <label class="form-check-label
                                                            @if (in_array($permission->id, $item->permissions_ids)
                                                                || $hasPermission
                                                            )
                                                                eto-check-label-active
                                                            @endif">{{ $permission->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                @endforeach
                            @endif
                        @endif
                    </span>
                </li>
                @if(isset($typeDeleted))
                    <li class="list-group-item">
                        <span class="details-list-title">{!! trans('roles.table.deleted') !!}:</span>
                        <span class="details-list-value">
                         {!! format_date_time($item->deleted_at) !!}
                    </span>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            @if($item->deleted_at && auth()->user()->hasPermission('admin.roles.restore'))
                <span class="eto-action-form">
                    <a href="javascript:void(0);" class="btn btn-primary text-white mb-0 eto-anchor-action"
                            data-toggle="modal" data-target="#confirmRestoreRoles"
                            data-title="{!! trans('roles.modals.restore_modal_title', ['type' => 'Role', 'item' => $item->name]) !!}"
                            data-message="{!! trans('roles.modals.restore_modal_message', ['type' => 'Role', 'item' => $item->name]) !!}"data-type="warning">
                        <i class="fa fa-fw fa-history" aria-hidden="true"></i>
                        {!! trans("roles.buttons.restore") !!}
                    </a>
                    <form action="{{ route('roles.restore', $item->id) }}" method="POST" accept-charset="utf-8">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                    </form>
                </span>
            @elseif ($item->subscription_id !== null && auth()->user()->hasPermission('admin.roles.edit'))
                <a class="btn btn-primary text-white mb-0" href="{{ route('roles.edit', $item->id) }}">
                    {!! trans("roles.buttons.edit") !!}
                </a>
            @endif
            @permission(['admin.roles.trash','admin.roles.destroy'])
            <span class="eto-action-form">
                @if ($item->deleted_at && auth()->user()->hasPermission('admin.roles.destroy'))
                    <a href="javascript:void(0);" class="btn btn-default text-white mb-0 eto-anchor-action" data-toggle="modal"
                        data-target="#confirmDestroyRoles"
                        data-title="{!! trans('roles.modals.destroy_modal_title', ['type' => 'Role', 'item' => $item->name]) !!}"
                        data-message="{!! trans('roles.modals.destroy_modal_message', ['type' => 'Role', 'item' => $item->name]) !!}" data-type="warning">
                        <i class="fa fa-trash fa-fw text-danger" aria-hidden="true"></i>
                        {!! trans("roles.buttons.destroy") !!}
                    </a>
                    <form action="{{ route('roles.destroy', $item->id) }}" method="POST" accept-charset="utf-8">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                    </form>
                @elseif ($item->subscription_id !== null && auth()->user()->hasPermission('admin.roles.trash'))
                    <a href="javascript:void(0);" class="btn btn-default text-white mb-0 eto-anchor-action"  data-toggle="modal" data-target="#confirmDelete" data-title="{!! trans('roles.modals.delete_modal_title', ['type' => 'Role', 'item' => $item->name]) !!}" data-message="{!! trans('roles.modals.delete_modal_message', ['type' => 'Role', 'item' => $item->name]) !!}" data-type="warning">
                        {{ trans("roles.buttons.delete") }}
                    </a>
                    <form action="{{  route('roles.delete', $item->id) }}" method="POST" accept-charset="utf-8">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                    </form>
                @endif
            </span>
            @endpermission
            @if(isset($typeDeleted))
                <a href="{{ route('roles.trash') }}" class="btn btn-link">
                    {!! trans('roles.buttons.cancel') !!}
                </a>
            @else
                <a href="{{ route('roles.index') }}" class="btn btn-link">
                    {!! trans('roles.buttons.back') !!}
                </a>
            @endif
        </div>
    </div>
@endsection

@section('subfooter')
    @include('roles.partials.index')
@endsection
