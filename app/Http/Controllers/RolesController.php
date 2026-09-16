<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Services\RoleFormFields;
use App\Traits\Roles\RolesAndPermissionsHelpersTrait;
use Yajra\Datatables\Html\Builder;

class RolesController extends Controller
{
    use RolesAndPermissionsHelpersTrait;

    /**
     * Show the roles and Permissions dashboard.
     *
     * @param Builder $builder
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function index(Builder $builder, Request $request)
    {
        if (!auth()->user()->hasPermission('admin.roles.index')) {
            return redirect_no_permission();
        }

        $model = Role::getListing();

        if ($request->is('roles/trash')) {
            $model->onlyTrashed();
        }

        if ($request->ajax()) {
            $dt = \Datatables::eloquent($model)
                ->addColumn('actions', function(Role $role) use ($request) {
                    $buttons = '<div class="btn-group" role="group" aria-label="..." >';

                    if ($role->deleted_at) {
                        if (auth()->user()->hasPermission('admin.roles.show')) {
                            $buttons .= '<a href="' . route('roles.showDeleted', $role->id) . '" class="btn btn-default btn-sm">
                                  <i class="fa fa-eye"></i>
                            </a>';
                        }
                        if (auth()->user()->hasPermission(['admin.roles.restore', "admin.roles.destroy"])) {
                            $buttons .= '<div class="btn-group" role="group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                                <ul class="dropdown-menu" role="menu">';
                            if (auth()->user()->hasPermission('admin.roles.restore')) {
                                $buttons .= '<li class="eto-action-form">
                                        <a href="javascript:void(0);" style="padding:3px 8px;" class="eto-anchor-action" data-toggle="modal" data-target="#confirmRestoreRoles"
                                                data-title="' . trans('roles.modals.restore_modal_title', ['type' => 'Role', 'item' => $role->name]) . '"
                                                data-message="' . trans('roles.modals.restore_modal_message', ['type' => 'Role', 'item' => $role->name]) . '"  data-type="warning">
                                            <i class="fa fa-fw fa-history" aria-hidden="true"></i>
                                        ' . trans("roles.buttons.restore") . '
                                        </a>
                                        <form action="' . route('roles.restore', $role->id) . '" method="POST" accept-charset="utf-8">
                                             ' . csrf_field() . '
                                             ' . method_field('PUT') . '
                                        </form>
                                    </li>';
                            }
                            if (auth()->user()->hasPermission("admin.roles.destroy")) {
                                $buttons .= '<li class="eto-action-form">
                                        <a href="javascript:void(0);" style="padding:3px 8px;" class="eto-anchor-action" data-toggle="modal" data-target="#confirmDestroyRoles"
                                                onclick=""
                                                data-title="' . trans('roles.modals.destroy_modal_title', ['type' => 'Role', 'item' => $role->name]) . '"
                                                data-message="' . trans('roles.modals.destroy_modal_message', ['type' => 'Role', 'item' => $role->name]) . '" data-type="warning">
                                            <i class="fa fa-trash fa-fw text-danger" aria-hidden="true"></i>
                                            ' . trans("roles.buttons.destroy") . '
                                        </a>
                                        <form action="' . route('roles.destroy', $role->id) . '" method="POST" accept-charset="utf-8">
                                            ' . csrf_field() . '
                                            ' . method_field('DELETE') . '
                                        </form>
                                    </li>';
                            }
                            $buttons .= '</ul>';
                        }
                        $buttons .= '</div>';
                    } else {
                        if (auth()->user()->hasPermission('admin.roles.show')) {
                            $buttons .= '<a href="' . route('roles.show', $role->id) . '" class="btn btn-default btn-sm">
                                    <i class="fa fa-eye"></i>
                                </a>';
                        }
                        if ($role->subscription_id !== null && auth()->user()->hasPermission(['admin.roles.edit', "admin.roles.trash"])) {
                            $buttons .= '<div class="btn-group" role="group">
                                  <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                      <i class="fa fa-angle-down"></i>
                                  </button>
                                  <ul class="dropdown-menu" role="menu">';
                            if (auth()->user()->hasPermission("admin.roles.edit")) {
                                $buttons .= '<li class="eto-action-form">
                                          <a style="padding:3px 8px;" href="' . route('roles.edit', $role->id) . '">
                                              <span style="display:inline-block; width:20px; text-align:center;">
                                                  <i class="fa fa-pencil-square-o"></i>
                                              </span>
                                              <span class="hidden-xs hidden-sm"> ' . trans("roles.buttons.edit") . '</span>
                                          </a>
                                      </li>';
                            }
                            if (auth()->user()->hasPermission("admin.roles.trash")) {
                                $buttons .= '<li class="eto-action-form">
                                          <a style="padding:3px 8px;" href="javascript:void(0);" class="eto-anchor-action"  data-toggle="modal"
                                                data-target="#confirmDelete" data-title="' . trans('roles.modals.delete_modal_title', ['type' => 'Role', 'item' => $role->name]) . '"
                                                data-message="' . trans('roles.modals.delete_modal_message', ['type' => 'Role', 'item' => $role->name]) . '" data-type="warning">
                                              <span style="display:inline-block; width:20px; text-align:center;">
                                                  <i class="fa fa-trash"></i>
                                              </span>
                                              <span class="hidden-xs hidden-sm">' . trans("roles.buttons.delete") . '</span>
                                          </a>
                                          <form action="' . route('roles.delete', $role->id) . '" method="POST" accept-charset="utf-8">
                                              ' . csrf_field() . '
                                              ' . method_field('DELETE') . '
                                          </form>
                                      </li>';
                            }
                            $buttons .= '</ul>
                              </div>';

                        }
                    }
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->setRowId(function (Role $role) {
                    return 'report_row_'. $role->id;
                })
                ->editColumn('name', function(Role $role){
                    return $role->getName();
                })
                ->editColumn('created_at', function(Role $role) {
                    return format_date_time($role->created_at);
                });

            if ($request->is('roles/trash')) {
                $dt->editColumn('deleted_at', function (Role $role) {
                    return format_date_time($role->deleted_at);
                });
            }

            return $dt->make(true);
        }
        else {
            $columns = [
                ['data' => 'name', 'name' => 'name', 'title' => trans('roles.table.name'), 'render' => null, 'orderable' => true, 'searchable' => true, 'exportable' => true, 'printable' => true],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('roles.table.created'), 'render' => null, 'visible' => false, 'orderable' => true, 'searchable' => true, 'exportable' => true, 'printable' => true],
            ];

            if (auth()->user()->hasPermission([
                'admin.roles.restore',
                "admin.roles.destroy",
                'admin.roles.show',
                'admin.roles.edit',
                'admin.roles.trash'])
            ) {
                $columns = array_merge(
                    [['data' => 'actions', 'name' => 'actions', 'width' =>'100px', 'title' => trans('roles.table.actions'), 'defaultContent' => '', 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true]],
                    $columns
                );
            }

            if ($request->is('roles/trash')) {
                $columns[] =  ['data' => 'deleted_at', 'name' => 'created_at', 'title' => trans('roles.table.deleted'), 'render' => null, 'visible' => true, 'orderable' => true, 'searchable' => true, 'exportable' => true, 'printable' => true];
            }

            $parameters = [
                'colReorder' => true,
                'paging' => true,
                'pagingType' => 'full_numbers',
                'scrollX' => true,
                'searching' => true,
                'ordering' => true,
                'lengthChange' => true,
                'info' => true,
                'autoWidth' => true,
                'stateSave' => true,
                'stateDuration' => 0,
                'order' => [
                    [1, 'asc'],
                ],
                'pageLength' => 10,
                'lengthMenu' => [5, 10, 25, 50],
                'language' => [
                    'search' => '_INPUT_',
                    'searchPlaceholder' => trans('admin/users.search_placeholder'),
                    'lengthMenu' => '_MENU_',
                    'paginate' => [
                        'first' => '<i class="fa fa-angle-double-left"></i>',
                        'previous' => '<i class="fa fa-angle-left"></i>',
                        'next' => '<i class="fa fa-angle-right"></i>',
                        'last' => '<i class="fa fa-angle-double-right"></i>'
                    ]
                ],
                'dom' => '<"row topContainer"<"col-xs-6 col-sm-6 col-md-7 dataTablesHeaderLeft"B><"col-xs-6 col-sm-6 col-md-5 dataTablesHeaderRight"f>><"dataTablesBody"rt><"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
                'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
                'searchDelay' => 500,
                'buttons' => [
                    [
                        'extend' => 'colvis',
                        // 'collectionLayout' => 'two-column',
                        'text' => '<i class="fa fa-eye"></i>',
                        'titleAttr' => trans('admin/users.button.column_visibility'),
                        'postfixButtons' => ['colvisRestore'],
                        'className' => 'btn-default btn-sm'
                    ], [
                        'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                        'titleAttr' => trans('admin/users.button.reset'),
                        'className' => 'btn-default btn-sm',
                    ], [
                        'extend' => 'reload',
                        'text' => '<i class="fa fa-refresh"></i>',
                        'titleAttr' => trans('admin/users.button.reload'),
                        'className' => 'btn-default btn-sm'
                    ]
                ]
            ];

            $trashedCount = $model->onlyTrashed()->count();
            $params = [];

            if ($request->is('roles/trash')) {
                $routeAjax = route('roles.trash');
                $parameters['buttons'][] = [
                    'text' => '<div onclick="window.location.href=\''. route('roles.index') .'\';">'. trans('roles.buttons.back') .'</div>',
                    'titleAttr' => trans('roles.buttons.back'),
                    'className' => 'btn-default btn-sm eto-roles-btn-margin'
                ];
            }
            else {
                $routeAjax = route('roles.list');
                $parameters['buttons'][] = [
                    'text' => '<div onclick="window.location.href=\''. route('roles.create') .'\';">'. trans('roles.buttons.new') .'</div>',
                    'titleAttr' => trans('roles.buttons.new'),
                    'className' => 'btn-default btn-sm eto-roles-btn-margin'
                ];
                if ($trashedCount > 0) {
                    $parameters['buttons'][] = [
                        'text' => '<div onclick="window.location.href=\'' . route('roles.trash') . '\';">' . trans('roles.buttons.trash') . '</div>',
                        'titleAttr' => trans('roles.buttons.trash'),
                        'className' => 'btn-default btn-sm eto-roles-btn-margin'
                    ];
                }
            }

            $ajax = [
                'url' => $routeAjax,
                'type' => 'POST',
                'headers' => [
                    'X-CSRF-TOKEN' => csrf_token()
                ],
                'data' => json_encode($params)
            ];

            $builder->columns($columns)->parameters($parameters)->ajax($ajax);

            return view('roles.index', compact('builder', 'trashedCount'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function create()
    {
        if (!config('eto.allow_roles') || !auth()->user()->hasPermission('admin.roles.create')) {
            return redirect_no_permission();
        }

        $data = (new RoleFormFields())->handle();

        return view('roles.create', $data);
    }

    /**
     * Store a newly created role in storage.
     *
     * @param StoreRoleRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(StoreRoleRequest $request)
    {
        if (!config('eto.allow_roles') || !auth()->user()->hasPermission('admin.roles.create')) {
            return redirect_no_permission();
        }

        $roleData = $request->roleFillData();
        $rolePermissions = $request->get('permissions');

        if ($rolePermissions) {
            if (empty($roleData['level'])) {
                $roleData['level'] = (\App\Models\Role::where('slug', $roleData['parent_slug'])->first())->level - 1;
            }

            unset($roleData['parent_slug']);
            $role = \App\Models\Role::create($roleData);
            $role->syncPermissions($rolePermissions);

            return redirect()->route('roles.edit', $role->id)
                ->with('success', trans('roles.flash-messages.role-create', ['role' => $role->name]));
        } else {
            return redirect()->back()->withErrors([trans('roles.flash-messages.no_permissions')]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->hasPermission('admin.roles.show')) {
            return redirect_no_permission();
        }

        $item = $this->getRole($id);

        $permissions = \App\Models\Permission::orderBy('slug', 'asc')->get();
        $groups = [];

        foreach ($permissions as $permission) {
            $items = explode('.', $permission->slug);
            $roleRoot = $items[0];
            $action = end($items);
            unset($items[0]);
            unset($items[count($items)]);
            $group = implode('.',$items);
            $permission->name = trans('roles.actions_permission.' . $action, ['permission'=>'']);
            $groups[$roleRoot][$group]['permissions'][] = $permission;
            $groups[$roleRoot][$group]['name'] = trans('roles.permissions.' . $roleRoot . '.' . $group);
            $groups[$roleRoot][$group]['group'] = $group;
            $groups[$roleRoot][$group]['slug'] = $item->slug;
        }

        foreach ($groups as $rid=>$role) {
            usort($groups[$rid], function ($a, $b) {
                return $a['name'] <=> $b['name'];
            });
        }

        return view('roles.show', compact('item', 'permissions', 'groups'));
    }

    /**
     * Edit the specified resource.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function edit($id)
    {
        if (!config('eto.allow_roles') || !auth()->user()->hasPermission('admin.roles.edit')) {
            return redirect_no_permission();
        }

        $data = (new RoleFormFields($id))->handle();

        if (is_null($data['subscription_id'])) {
            return redirect_no_permission();
        }

        return view('roles.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRoleRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(UpdateRoleRequest $request, $id)
    {
        if (!config('eto.allow_roles') || !auth()->user()->hasPermission('admin.roles.edit')) {
            return redirect_no_permission();
        }

        $roleData = $request->roleFillData();
        $rolePermissions = $request->get('permissions');
        unset($roleData['subscription_id']);
        $role = \App\Models\Role::findOrFail($id);

        if (is_null($role->subscription_id)) {
            return redirect_no_permission();
        }

        if ($rolePermissions) {
            foreach ($roleData as $key => $data) {
                if (null === $data) {
                    unset($roleData[$key]);
                }
            }

            $role->fill($roleData);
            $role->save();
            $role->detachAllPermissions();

            if ($rolePermissions) {
                $role->syncPermissions($rolePermissions);
            }

            return redirect()->route('roles.edit', $role->id)
                ->with('success', trans('roles.flash-messages.role-updated', ['role' => $role->name]));
        } else {
            return redirect()->back()->withErrors([trans('roles.flash-messages.no_permissions')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function trash($id)
    {
        if (!config('eto.allow_roles') || !auth()->user()->hasPermission('admin.roles.trash')) {
            return redirect_no_permission();
        }

        $role = Role::find($id);
        $role->delete();

        return redirect(route('roles.index'))
            ->with('success', trans('roles.flash-messages.successDeletedItem', ['type' => 'Role', 'item' => $role->name]));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function restore($id)
    {
        if (!config('eto.allow_roles') || !auth()->user()->hasPermission('admin.roles.restore')) {
            return redirect_no_permission();
        }

        $role = Role::onlyTrashed()->find($id);

        if (!is_null($role)) {
            $role->restore();
        }

        return redirect()->route('roles.trash')
            ->with('success', trans('roles.flash-messages.successRestoredRole', ['role' => $role->name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        if (!config('eto.allow_roles') || !auth()->user()->hasPermission('admin.roles.destroy')) {
            return redirect_no_permission();
        }

        if ($role = Role::withTrashed()->find($id)) {
            $role->forceDelete();
        }

        return redirect()->route('roles.index');
    }
}
