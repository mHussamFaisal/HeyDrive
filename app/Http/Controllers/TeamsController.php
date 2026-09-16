<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Helpers\SiteHelper;
use Datatables;
use Yajra\Datatables\Html\Builder;
use Form;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamsController extends Controller
{
    public function datatables(Request $request)
    {
        if (!config('eto.allow_teams') || !auth()->user()->hasPermission('admin.teams.index')) {
            return redirect_no_permission();
        }

        if ($request->ajax()) {
            $model = Team::where('subscription_id', $request->system->subscription->id);

            $dt = Datatables::eloquent($model)
                ->addColumn('actions', function(Team $team) {
                    $buttons = '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';

                    if (auth()->user()->hasPermission('admin.teams.show')) {
                        $buttons .= '<a href="' . route('teams.show', $team->id) . '" class="btn btn-default btn-sm btnView" data-original-title="' . trans('teams.button.show') . '">
                          <i class="fa fa-eye"></i>
                        </a>';
                    }
                    if (auth()->user()->hasPermission(['admin.teams.edit', 'admin.teams.destroy'])) {
                        $buttons .= '<div class="btn-group pull-left" role="group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                          <span class="fa fa-angle-down"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">';
                        if (auth()->user()->hasPermission('admin.teams.edit')) {
                            $buttons .= '<li>
                              <a href="' . route('teams.edit', $team->id) . '" class="btnEdit" style="padding:3px 8px;" data-original-title="' . trans('teams.button.edit') . '">
                                <span style="display:inline-block; width:20px; text-align:center;">
                                  <i class="fa fa-pencil-square-o"></i>
                                </span>
                                ' . trans('teams.button.edit') . '
                              </a>
                            </li>';
                        }

                        if (auth()->user()->hasPermission(['admin.users.admin.index', 'admin.users.driver.index', 'admin.users.customer.index']) && $team->users->count()) {
                            $buttons .= '<li>
                              <a href="'. route('admin.users.index') .'?team='. $team->id .'" class="btnEdit" style="padding:3px 8px;" data-original-title="'. trans('teams.button.users') .'">
                                <span style="display:inline-block; width:20px; text-align:center;">
                                  <i class="fa fa-users"></i>
                                </span>
                                ' . trans('teams.button.users') . '
                              </a>
                            </li>';
                        }

                        if (auth()->user()->hasPermission('admin.teams.destroy')) {
                            $buttons .= '<li>
                              <a href="#" onclick="$(\'#button_delete_id_' . $team->id . '\').click(); return false;" class="btnDelete" style="padding:3px 8px;" data-original-title="' . trans('teams.button.destroy') . '">
                                <span style="display:inline-block; width:20px; text-align:center;">
                                  <i class="fa fa-trash"></i>
                                </span>
                                ' . trans('teams.button.destroy') . '
                              </a>
                            </li>';
                        }
                        $buttons .= '</ul>';
                    }
                    $buttons .= '</div>
                    </div>';

                    $buttons .= Form::open(['method' => 'delete', 'route' => ['teams.destroy', $team->id], 'class' => 'form-inline form-delete hide']);
                    $buttons .= Form::button(trans('teams.button.destroy'), ['type' => 'submit', 'class' => 'delete', 'name' => 'delete_modal', 'id' => 'button_delete_id_'. $team->id]);
                    $buttons .= Form::close();

                    return $buttons;
                })
                ->setRowId(function (Team $team) {
                    return 'team_row_'. $team->id;
                })
                ->editColumn('name', function(Team $team) {
                    return '<a href="'. route('teams.show', $team->id) .'" class="text-default">'. $team->getName() .'</a>';
                })
                ->editColumn('status', function(Team $team) {
                    if (auth()->user()->hasPermission('admin.teams.edit')) {
                        return '<a href="'. route('teams.status', [$team->id, ($team->status == 1) ? 'inactive' : 'active']) .'" class="text-success status-icon">'. $team->getStatus('label') .'</a>';
                    }
                    return $team->getStatus('label');
                })
                ->editColumn('created_at', function(Team $team) {
                    return SiteHelper::formatDateTime($team->created_at);
                })
                ->editColumn('updated_at', function(Team $team) {
                    return SiteHelper::formatDateTime($team->updated_at);
                });

            return $dt->make(true);
        }
    }

    public function index(Builder $builder, Request $request)
    {
        if (!config('eto.allow_teams') || !auth()->user()->hasPermission('admin.teams.index')) {
            return redirect_no_permission();
        }

        $columns = [
            ['data' => 'name', 'name' => 'name', 'title' => trans('teams.name')],
            ['data' => 'status', 'name' => 'status', 'title' => trans('teams.status'), 'searchable' => false],
            ['data' => 'order', 'name' => 'order', 'title' => trans('teams.order'), 'visible' => false, 'searchable' => false],
            ['data' => 'internal_note', 'name' => 'internal_note', 'title' => trans('teams.internal_note'), 'visible' => false],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => trans('teams.updated_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('teams.created_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'id', 'name' => 'id', 'title' => trans('teams.id'), 'visible' => false],
        ];

        if (auth()->user()->hasPermission(['admin.teams.show', 'admin.teams.edit', 'admin.teams.destroy'])) {
            $columns = array_merge(
                [['data' => 'actions', 'defaultContent' => '', 'name' => 'actions', 'title' => trans('teams.actions'), 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true]],
                $columns
            );
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
                [1, 'asc']
            ],
            'pageLength' => 10,
            'lengthMenu' => [5, 10, 25, 50],
            'language' => [
                'search' => '_INPUT_',
                'searchPlaceholder' => trans('teams.search_placeholder'),
                'lengthMenu' => '_MENU_',
                'paginate' => [
                    'first' => '<i class="fa fa-angle-double-left"></i>',
                    'previous' => '<i class="fa fa-angle-left"></i>',
                    'next' => '<i class="fa fa-angle-right"></i>',
                    'last' => '<i class="fa fa-angle-double-right"></i>'
                ]
            ],
            'dom' => '<"row topContainer"<"col-xs-6 col-sm-6 col-md-7 dataTablesHeaderLeft"B><"col-xs-6 col-sm-6 col-md-5 dataTablesHeaderRight"f>><"dataTablesBody"rt><"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
            'drawCallback' => 'function() { $(\'#teams [data-toggle="tooltip"]\').tooltip(\'hide\'); $(\'#teams [title]\').tooltip({ placement: \'auto\', container: \'body\', selector: \'\', html: true, trigger: \'hover\', delay: {\'show\': 500,\'hide\': 100 } }); }',
            'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
            'searchDelay' => 350,
            'buttons' => [
                [
                    'extend' => 'colvis',
                    'text' => '<i class="fa fa-eye"></i>',
                    'titleAttr' => trans('teams.button.column_visibility'),
                    'postfixButtons' => ['colvisRestore'],
                    'className' => 'btn-default btn-sm'
                ], [
                    'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                    'titleAttr' => trans('teams.button.reset'),
                    'className' => 'btn-default btn-sm'
                ], [
                    'extend' => 'reload',
                    'text' => '<i class="fa fa-refresh"></i>',
                    'titleAttr' => trans('teams.button.reload'),
                    'className' => 'btn-default btn-sm'
                ]
            ]
        ];

        if (auth()->user()->hasPermission('admin.teams.create')) {
            $parameters['buttons'][] = [
                'text' => '<div onclick="window.location.href=\''. route('teams.create') .'\';"><i class="fa fa-plus"></i> <span class="hidden-xs">'. trans('teams.button.create_new') .'</span></div>',
                'titleAttr' => trans('teams.button.create_new'),
                'className' => 'btn-success btn-sm buttons-new'
            ];
        }

        $ajax = [
            'url' => route('teams.datatables'),
            'type' => 'POST',
            'headers' => [
                'X-CSRF-TOKEN' => csrf_token()
            ],
        ];

        $builder->columns($columns)->parameters($parameters)->ajax($ajax);

        return view('teams.index', compact('builder'));
    }

    public function show(Request $request, $id)
    {
        if (!config('eto.allow_teams') || !auth()->user()->hasPermission('admin.teams.show')) {
            return redirect_no_permission();
        }

        $team = Team::where('subscription_id', $request->system->subscription->id)->findOrFail($id);

        return view('teams.show', ['team' => $team]);
    }

    public function create(Request $request)
    {
        if (!config('eto.allow_teams') || !auth()->user()->hasPermission('admin.teams.create')) {
            return redirect_no_permission();
        }

        $statusList = \App\Helpers\FormHelper::getStatusList('team', 'name');

        return view('teams.create', ['statusList' => $statusList]);
    }

    public function store(Request $request)
    {
        if (!config('eto.allow_teams') || !auth()->user()->hasPermission('admin.teams.create')) {
            return redirect_no_permission();
        }

        $rules = [
            'name' => 'required|max:255',
            'status' => 'numeric',
            'order' => 'numeric',
            // 'internal_note' => '',
        ];

        $this->validate($request, $rules);

        $status = (int)$request->get('status');
        $statusList = \App\Helpers\FormHelper::getStatusList('team', 'id');

        $team = new Team;
        $team->subscription_id = $request->system->subscription->id;
        $team->name = (string)$request->get('name') ?: null;
        $team->status = array_key_exists($status, $statusList) ? $status : 0;
        $team->order = (int)$request->get('order');
        $team->internal_note = (string)$request->get('internal_note') ?: null;
        $team->updated_at = Carbon::now();
        $team->save();

        session()->flash('message', trans('teams.message.store_success'));
        return redirect()->route('teams.index');
    }

    public function edit(Request $request, $id)
    {
        if (!config('eto.allow_teams') || !auth()->user()->hasPermission('admin.teams.edit')) {
            return redirect_no_permission();
        }

        $team = Team::where('subscription_id', $request->system->subscription->id)->findOrFail($id);
        $statusList = \App\Helpers\FormHelper::getStatusList('team', 'name');

        return view('teams.edit', ['team' => $team, 'statusList' => $statusList]);
    }

    public function update(Request $request, $id)
    {
        if (!config('eto.allow_teams') || !auth()->user()->hasPermission('admin.teams.edit')) {
            return redirect_no_permission();
        }

        $rules = [
            'name' => 'required|max:255',
            'status' => 'numeric',
            'order' => 'numeric',
            // 'internal_note' => '',
        ];

        $this->validate($request, $rules);

        $status = (int)$request->get('status');
        $statusList = \App\Helpers\FormHelper::getStatusList('team', 'id');

        $team = Team::where('subscription_id', $request->system->subscription->id)->findOrFail($id);
        $team->name = (string)$request->get('name') ?: null;
        $team->status = array_key_exists($status, $statusList) ? $status : 0;
        $team->order = (int)$request->get('order');
        $team->internal_note = (string)$request->get('internal_note') ?: null;
        $team->updated_at = Carbon::now();
        $team->save();

        session()->flash('message', trans('teams.message.update_success'));
        return redirect()->back();
    }

    public function destroy(Request $request, $id)
    {
        if (!config('eto.allow_teams') || !auth()->user()->hasPermission('admin.teams.destroy')) {
            return redirect_no_permission();
        }

        $team = Team::where('subscription_id', $request->system->subscription->id)->findOrFail($id);
        $team->users()->detach();
        $team->delete();

        session()->flash('message', trans('teams.message.destroy_success'));

        if (url()->previous() != url()->full()) {
            return redirect()->back();
        }
        else {
            return redirect()->route('teams.index');
        }
    }

    public function status(Request $request, $id, $status)
    {
        if (!config('eto.allow_teams') || !auth()->user()->hasPermission('admin.teams.edit')) {
            return redirect_no_permission();
        }

        $statusList = \App\Helpers\FormHelper::getStatusList('team', 'id');

        $team = Team::where('subscription_id', $request->system->subscription->id)->findOrFail($id);
        $team->status = in_array($status, $statusList) ? array_search($status, $statusList) : 0;
        $team->save();

        return redirect()->back();
    }
}
