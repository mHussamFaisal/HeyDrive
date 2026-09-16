@php
if (!$level){ $level = 0; }
@endphp

{{ csrf_field() }}
<div class="row eto-role-form hidden">
    <div class="col-md-6">
        <div class="form-group has-feedback{{ $errors->has('parent_role') ? ' has-error' : '' }} @if($subscription_id === null)readonly @endif">
            <label for="parent_slug">
                {{ trans("roles.form.parent_role_name.label") }}
            </label>
                <select id="parent_slug" class="form-control select2" data-placeholder="{{ trans("roles.form.parent_role_name.label") }}" required="" name="parent_slug" required @if ($subscription_id === null) disabled @endif>
                    <option></option>
                    @foreach ($roles as $id=>$role)
                        <option value="{{ $role->slug }}" @if ($parent_slug == $role->slug) selected @endif>{{ $role->getName() }}</option>
                        @php
                            $rolePermissions[$role->slug] = $role->permissions->pluck('id');
                        @endphp
                    @endforeach
                </select>
            @if( $errors->has('parent_role') )
                <span class="help-block">{{ $errors->first('parent_role') }}</span>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-feedback {{ $errors->has('name') ? ' has-error ' : '' }}">
            <label for="slug">
                {{ trans("roles.form.role-name.label") }}
            </label>
                <input id="name" class="form-control" value="{{ $name }}" placeholder="{{ trans("roles.form.role-name.label") }}" name="name" type="text" required @if ($subscription_id === null) readonly @endif>
            @if( $errors->has('name') )
                <span class="help-block">{{ $errors->first('name') }}</span>
            @endif
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group has-feedback {{ $errors->has('description') ? ' has-error ' : '' }}">
            <label for="description">
                {{ trans("roles.form.role-desc.label") }}
            </label>
                <textarea id="description" class="form-control" placeholder="{{ trans("roles.form.role-desc.label") }}" rows="2" name="description" cols="50" style="overflow: hidden visible; overflow-wrap: break-word; resize: horizontal;">{{ $description }}</textarea>
            @if( $errors->has('description') )
                <span class="help-block">{{ $errors->first('description') }}</span>
            @endif
        </div>
    </div>
    @role('service')
    <div class="col-md-6">
        <div class="form-group has-feedback {{ $errors->has('slug') ? ' has-error ' : '' }}" @if(!empty($slug))readonly @endif>
            <label for="slug">
                {{ trans("roles.form.role-slug.label") }}
            </label>
                <input id="slug" class="form-control" value="{{ $slug }}" placeholder="{{ trans("roles.form.role-slug.label") }}" name="slug" type="text" @if(!empty($slug))readonly @endif>
            @if( $errors->has('slug') )
                <span class="help-block">{{ $errors->first('slug') }}</span>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group has-feedback {{ $errors->has('level') ? ' has-error ' : '' }} @if(!empty($level))readonly @endif">
            <label for="level">
                {{ trans("roles.form.role-level.label") }}
            </label>
                <input id="level" class="form-control" value="{{ $level }}" placeholder="{{ trans("roles.form.role-level.label") }}" name="level" type="number" @if(!empty($level))readonly @endif>
            @if( $errors->has('level') )
                <span class="help-block">{{ $errors->first('level') }}</span>
            @endif
        </div>
    </div>
    @endrole
    <div class="col-md-12 eto-permission">
        <div class="eto-permission-container">
                <div class="eto-permission-list-header">{{ trans("roles.form.role-permissions.label") }}</div>
                <div class="eto-permission-list">
                @foreach ($groups as $roleRoot=>$group)
                    @foreach ($group as $actions)
                        @if(empty($rolePermissionsGroups[$roleRoot]) || (!empty($rolePermissionsGroups[$roleRoot]) && !in_array($actions['group'], $rolePermissionsGroups[$roleRoot])))
                            @continue
                        @endif
                    <div class="eto-permission-item clearfix" data-eto-group="{{ $roleRoot.'.'.$actions['group'] }}">
                        <div class="eto-permission-item-group">
                            <span class="eto-inline"><i class="fa fa fa-trash eto-delete-permission"></i></span>
                            <span class="eto-permission-item-group-name">{{ $actions['name'] }}</span>
                        </div>
                        <div class="eto-permission-item-actions">
                            @foreach ($actions['permissions'] as $permission)
                                <div class="eto-check-inline">
                                    <input name="permissions[]" class="form-check-input" type="checkbox" id="perm-{{ $permission->id }}" value="{{ $permission->id }}" @if (in_array($permission->id, $rolePermissionsIds)) checked @endif>
                                    <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endforeach
                </div>
        </div>
        <div class="eto-permission-list-empty @if(!empty($rolePermissionsIds)) hidden @endif">
            {{ trans("roles.form.no_permissions") }}
        </div>
    </div>
</div>

<script>
var groups = {!! json_encode($groups) !!},
    roles = {!! json_encode($rolesToJs) !!},
    rolesPermissions = {};

function addPermissionSelect(parent) {
    if (typeof rolesPermissions[parent] != 'undefined' && rolesPermissions[parent].length > 0) {
        var options = '',
            parentRoot = typeof parent != "undefined" && parent != '' ? parent.split('.')[0] : false;

        if (parentRoot) {
            $.each(groups, function (root, group) {
                if (parentRoot == root) {
                    $.each(group, function (k,actions) {
                        var roleHasPerm = 0;

                        if (actions.group != 'roles') {
                            $.each(actions.permissions, function (k, v) {
                                if (ETO.findWildcard(rolesPermissions[parent], v.slug) !== false) {
                                    roleHasPerm++;
                                }
                            });

                            if (roleHasPerm > 0 && $('[data-eto-group="' + root + '.' + actions.group + '"]').length === 0) {
                                options += '<option value="' + root + '.' + actions.group + '">' + actions.name + '</option>';
                            }
                        }
                    });
                }
            });

            if (options != '') {
                options = '<option></option>' + options;
                $('.eto-permission-list').append('<select class="form-control eto-permission-group-select" id="permission_select">' + options + '</select>');
                $('.eto-permission-group-select').select2({placeholder: ETO.trans('roles.select_permission_placeholder')});
            }
        } else {
            $('.eto-permission-list').html('');
        }
    }
}

$(function() {
    $('body').LoadingOverlay('show');

    $.each(roles, function (k,v) {
        var permissions = [];

        $.each(v.permissions, function (pk,pv) {
            permissions.push(pv.slug);
        });

        rolesPermissions[v.slug] = permissions;
    });

    setTimeout(function () {
        if ($('#parent_slug').val() != '') {
            addPermissionSelect($('#parent_slug').val());
        }

        $("body").on('change', '.eto-permission-group-select', function (e) {
            var select = $(this),
                items = select.val().split('.'),
                root = items[0];
            delete items[0];
            var group = items.filter(x => typeof x === 'string' && x.length > 0).join('.');

            if (group != '') {
                $('.eto-permission-list').append('<div class="eto-permission-item clearfix" data-eto-group="'+$(this).val()+'">\
                        <div class="eto-permission-item-group">\
                            <span class="eto-inline"><i class="fa fa fa-trash eto-delete-permission"></i></span>\
                            <span class="eto-permission-item-group-name">'+$(this).find(':selected').text()+'</span>\
                        </div>\
                        <div class="eto-permission-item-actions"></div>\
                    </div>');
                $('.eto-permission-group-select').select2({placeholder: ETO.trans('roles.select_permission_placeholder')});
                $(".eto-permission-list-empty").addClass('hidden');

                $.each(groups[root], function (i, actions) {
                    if(group == actions.group) {
                        $.each(actions.permissions, function (k, v) {
                            $('.eto-permission-item:last').find('.eto-permission-item-actions').append('<div class="eto-check-inline">\
                            <input name="permissions[]" class="form-check-input" type="checkbox" id="perm-' + v.id + '" value="' + v.id + '" checked>\
                            <label class="form-check-label" for="perm-' + v.id + '">' + v.name + '</label>\
                            </div>');
                        });
                    }
                });
            }

            select.select2('destroy');
            select.remove();
            addPermissionSelect($('#parent_slug').val())
        })
        .on('click', ".eto-delete-permission", function (e) {
            $(this).closest('.eto-permission-item').find('select').select2('destroy');
            $(this).closest('.eto-permission-item').remove();

            if ($(".eto-delete-permission").length !== 0) {
                $(".eto-permission-list-empty").addClass('hidden');
            } else {
                $(".eto-permission-list-empty").removeClass('hidden');
            }
            var select = $('.eto-permission-group-select');
            if (select.length > 0) {
                select.select2('destroy');
                select.remove();
            }
            addPermissionSelect($('#parent_slug').val())
        })
        .on('select2:opening', "#parent_slug", function (e) {
            if ($(".eto-delete-permission").length !== 0) {
                e.stopPropagation();
                return false;
            }
        })
        .on('change', "#parent_slug", function (e) {
            if ($('#parent_slug').val() != '') {
                var select = $('.eto-permission-group-select');
                if (select.length > 0) {
                    select.select2('destroy');
                    select.remove();
                }
                addPermissionSelect($('#parent_slug').val());
            }
        });

        $('.eto-role-form').removeClass('hidden');
        $('body').LoadingOverlay('hide');
    },0);
});
</script>
