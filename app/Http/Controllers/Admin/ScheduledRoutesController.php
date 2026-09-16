<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\ScheduledRoute;
use App\Helpers\SiteHelper;
use Carbon\Carbon;
use Yajra\Datatables\Html\Builder;
use Datatables;
use Form;
use Illuminate\Http\Request;

class ScheduledRoutesController extends Controller
{
    public function datatables(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.index')) {
            return redirect_no_permission();
        }

        if ($request->ajax()) {
            $tnUser = (new \App\Models\User)->getTable();
            $tnUserProfile = (new \App\Models\UserProfile)->getTable();
            $tnVehicle = (new \App\Models\Vehicle)->getTable();
            $tnVehicleType = (new \App\Models\VehicleType)->getTable();
            $tnScheduledRoute = (new \App\Models\ScheduledRoute)->getTable();
            $tnLocation = (new \App\Models\Location)->getTable();

            $model = ScheduledRoute::with([
                'driver',
                'driver.profile',
                'vehicle',
                'vehicleType',
                'from',
                'to',
                'event',
            ]);

            $model->leftJoin($tnLocation .' as locations_from', function ($leftJoin) use ($tnScheduledRoute) {
                $leftJoin->on('locations_from.relation_id', '=', $tnScheduledRoute .'.id');
                $leftJoin->where('locations_from.relation_type', '=', 'scheduled_route');
                $leftJoin->where('locations_from.type', '=', 'from');
            });

            $model->leftJoin($tnLocation .' as locations_to', function ($leftJoin) use ($tnScheduledRoute) {
                $leftJoin->on('locations_to.relation_id', '=', $tnScheduledRoute .'.id');
                $leftJoin->where('locations_to.relation_type', '=', 'scheduled_route');
                $leftJoin->where('locations_to.type', '=', 'to');
            });

            $model->leftJoin($tnUser, $tnUser .'.id', '=', $tnScheduledRoute .'.driver_id');
            $model->leftJoin($tnUserProfile, $tnUserProfile .'.user_id', '=', $tnScheduledRoute .'.driver_id');
            $model->leftJoin($tnVehicle, $tnVehicle .'.id', '=', $tnScheduledRoute .'.vehicle_id');
            $model->leftJoin($tnVehicleType, $tnVehicleType .'.id', '=', $tnScheduledRoute .'.vehicle_type_id');

            $model->select($tnScheduledRoute .'.*', $tnUserProfile .'.unique_id', $tnUser .'.name as driver_name',
                            $tnVehicle .'.name as vehicle_name', $tnVehicleType .'.name as vehicle_type_name',
                            'locations_from.address as location_from', 'locations_to.address as location_to');

            $model->where($tnScheduledRoute .'.relation_type', 'site');
            $model->where($tnScheduledRoute .'.relation_id', config('site.site_id'));

            $dt = Datatables::eloquent($model)
                ->addColumn('actions', function(ScheduledRoute $scheduledRoute) {
                    $buttons = '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';
                    if (auth()->user()->hasPermission('admin.scheduled_routes.show')) {
                        $buttons .= '<a href="' . route('admin.scheduled-routes.show', $scheduledRoute->id) . '" class="btn btn-default btn-sm btnView" data-original-title="' . trans('admin/scheduled_routes.button.show') . '">
                        <i class="fa fa-eye"></i>
                      </a>';
                    }
                    if (auth()->user()->hasPermission(['admin.scheduled_routes.edit','admin.scheduled_routes.destroy','admin.bookings.index'])) {
                        $buttons .= '<div class="btn-group pull-left" role="group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                          <span class="fa fa-angle-down"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">';
                        if (auth()->user()->hasPermission('admin.bookings.index')) {
                            $buttons .= '<li>
                            <a href="' . route('admin.scheduled-routes.edit', $scheduledRoute->id) .'" class="btnEdit" style="padding:3px 8px;" data-original-title="'. trans('admin/scheduled_routes.button.edit') .'">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-pencil-square-o"></i>
                              </span>
                              <span>' . trans('admin/scheduled_routes.button.edit') . '</span>
                            </a>
                          </li>';
                        }
                        if (auth()->user()->hasPermission('admin.scheduled_routes.edit')) {
                            $buttons .= '<li>
                            <a href="' . route('admin.bookings.index', ['scheduled_route' => $scheduledRoute->id]) .'" class="btnBookings" style="padding:3px 8px;" data-original-title="'. trans('admin/scheduled_routes.button.bookings') .'">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-tasks"></i>
                              </span>
                              <span>' . trans('admin/scheduled_routes.button.bookings') . '</span>
                            </a>
                          </li>';
                        }
                        if (auth()->user()->hasPermission('admin.scheduled_routes.destroy')) {
                            $buttons .= '<li>
                            <a href="#" onclick="$(\'#button_delete_id_' . $scheduledRoute->id . '\').click(); return false;" class="btnDelete" style="padding:3px 8px;" data-original-title="' . trans('admin/scheduled_routes.button.destroy') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-trash"></i>
                              </span>
                              <span>' . trans('admin/scheduled_routes.button.destroy') . '</span>
                            </a>
                          </li>';
                        }
                        $buttons .= '</ul>';
                    }
                    $buttons .= '</div>
                    </div>';

                    $buttons .= Form::open(['method' => 'delete', 'route' => ['admin.scheduled-routes.destroy', $scheduledRoute->id], 'class' => 'form-inline form-delete hide']);
                    $buttons .= Form::button(trans('admin/scheduled_routes.button.destroy'), ['type' => 'submit', 'class' => 'delete', 'name' => 'delete_modal', 'id' => 'button_delete_id_'. $scheduledRoute->id]);
                    $buttons .= Form::close();

                    return $buttons;
                })
                ->setRowId(function (ScheduledRoute $scheduledRoute) {
                    return 'scheduled_route_row_'. $scheduledRoute->id;
                })
                ->editColumn('location_from', function(ScheduledRoute $scheduledRoute) {
                    return !empty($scheduledRoute->from->address) ? $scheduledRoute->from->address : '';
                })
                ->editColumn('location_to', function(ScheduledRoute $scheduledRoute) {
                    return !empty($scheduledRoute->to->address) ? $scheduledRoute->to->address : '';
                })
                ->editColumn('driver_id', function(ScheduledRoute $scheduledRoute) {
                    return !empty($scheduledRoute->driver->id) ? '<a href="'. route('admin.users.show', $scheduledRoute->driver->id) .'" class="text-default">'. $scheduledRoute->driver->getName(true) .'</a>' : '';
                })
                ->editColumn('vehicle_id', function(ScheduledRoute $scheduledRoute) {
                    return !empty($scheduledRoute->vehicle->id) ? '<a href="'. route('admin.vehicles.show', $scheduledRoute->vehicle->id) .'" class="text-default">'. $scheduledRoute->vehicle->getName() .'</a>' : '';
                })
                ->editColumn('vehicle_type_id', function(ScheduledRoute $scheduledRoute) {
                    return !empty($scheduledRoute->vehicleType->id) ? '<a href="'. route('admin.vehicles-types.index') .'" class="text-default">'. $scheduledRoute->vehicleType->getName() .'</a>' : '';
                })
                ->editColumn('params', function(ScheduledRoute $scheduledRoute) {
                    return $scheduledRoute->getParams();
                })
                ->editColumn('is_featured', function(ScheduledRoute $scheduledRoute) {
                    $url = route('admin.scheduled-routes.featured', [$scheduledRoute->id, $scheduledRoute->is_featured ? 'no' : 'yes']);
                    $title = trans('admin/scheduled_routes.'. ($scheduledRoute->is_featured ? 'yes' : 'no'));

                    if (auth()->user()->hasPermission('admin.scheduled_routes.edit')) {
                        return '<a href="' . $url . '" title="' . $title . '" class="text-default">
                              <i class="fa ' . ($scheduledRoute->is_featured ? 'fa-star' : 'fa-star-o') . '"></i>
                            </a>';
                    }
                    return ' <i class="fa ' . ($scheduledRoute->is_featured ? 'fa-star' : 'fa-star-o') . '"></i>';
                })
                ->editColumn('status', function(ScheduledRoute $scheduledRoute) {
                    if (auth()->user()->hasPermission('admin.scheduled_routes.edit')) {
                        return '<a href="' . route('admin.scheduled-routes.status', [$scheduledRoute->id, $scheduledRoute->status == 'active' ? 'inactive' : 'active']) . '" class="text-default">' . $scheduledRoute->getStatus('label') . '</a>';
                    }
                    return $scheduledRoute->getStatus('label');
                })
                ->editColumn('created_at', function(ScheduledRoute $scheduledRoute) {
                    return SiteHelper::formatDateTime($scheduledRoute->created_at);
                })
                ->editColumn('updated_at', function(ScheduledRoute $scheduledRoute) {
                    return SiteHelper::formatDateTime($scheduledRoute->updated_at);
                })
                ->orderColumn('driver_id', '-unique_id $1, driver_name $1, driver_id $1')
                ->orderColumn('vehicle_id', '-vehicle_name $1, vehicle_id $1')
                ->orderColumn('vehicle_type_id', '-vehicle_type_name $1, vehicle_type_id $1');

            return $dt->make(true);
        }
    }

    public function index(Builder $builder, Request $request)
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.index')) {
            return redirect_no_permission();
        }

        $tnUser = (new \App\Models\User)->getTable();
        $tnVehicle = (new \App\Models\Vehicle)->getTable();
        $tnVehicleType = (new \App\Models\VehicleType)->getTable();

        $columns = [
            ['data' => 'actions', 'defaultContent' => '', 'name' => 'actions', 'title' => trans('admin/scheduled_routes.actions'), 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true],
            ['data' => 'location_from', 'name' => 'locations_from.address', 'title' => trans('admin/scheduled_routes.from'), 'searchable' => true],
            ['data' => 'location_to', 'name' => 'locations_to.address', 'title' => trans('admin/scheduled_routes.to'), 'searchable' => true],
            ['data' => 'driver_id', 'name' => $tnUser .'.name', 'title' => trans('admin/scheduled_routes.driver_id'), 'searchable' => true],
            ['data' => 'vehicle_id', 'name' => $tnVehicle .'.name', 'title' => trans('admin/scheduled_routes.vehicle_id'), 'searchable' => true],
            ['data' => 'vehicle_type_id', 'name' => $tnVehicleType .'.name', 'title' => trans('admin/scheduled_routes.vehicle_type_id'), 'searchable' => true],
            ['data' => 'params', 'name' => 'params', 'title' => trans('admin/scheduled_routes.params'), 'orderable' => false, 'searchable' => false],
            ['data' => 'is_featured', 'name' => 'is_featured', 'title' => trans('admin/scheduled_routes.is_featured'), 'searchable' => false],
            ['data' => 'order', 'name' => 'order', 'title' => trans('admin/scheduled_routes.order'), 'searchable' => false, 'visible' => false],
            ['data' => 'status', 'name' => 'status', 'title' => trans('admin/scheduled_routes.status'), 'searchable' => false],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => trans('admin/scheduled_routes.updated_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('admin/scheduled_routes.created_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'id', 'name' => 'id', 'title' => trans('admin/scheduled_routes.id'), 'visible' => false]
        ];

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
                'searchPlaceholder' => trans('admin/scheduled_routes.search_placeholder'),
                'lengthMenu' => '_MENU_',
                'paginate' => [
                    'first' => '<i class="fa fa-angle-double-left"></i>',
                    'previous' => '<i class="fa fa-angle-left"></i>',
                    'next' => '<i class="fa fa-angle-right"></i>',
                    'last' => '<i class="fa fa-angle-double-right"></i>'
                ]
            ],
            'dom' => '<"row topContainer"<"col-xs-6 col-sm-6 col-md-7 dataTablesHeaderLeft"B><"col-xs-6 col-sm-6 col-md-5 dataTablesHeaderRight"f>><"dataTablesBody"rt><"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
            'drawCallback' => 'function() { $(\'#scheduled_routes [data-toggle="tooltip"]\').tooltip(\'hide\'); $(\'#scheduled_routes [title]\').tooltip({ placement: \'auto\', container: \'body\', selector: \'\', html: true, trigger: \'hover\', delay: {\'show\': 500,\'hide\': 100 } }); }',
            'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
            'searchDelay' => 350,
            'buttons' => [
                [
                    'extend' => 'colvis',
                    'text' => '<i class="fa fa-eye"></i>',
                    'titleAttr' => trans('admin/scheduled_routes.button.column_visibility'),
                    'postfixButtons' => ['colvisRestore'],
                    'className' => 'btn-default btn-sm'
                ], [
                    'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                    'titleAttr' => trans('admin/scheduled_routes.button.reset'),
                    'className' => 'btn-default btn-sm'
                ], [
                    'extend' => 'reload',
                    'text' => '<i class="fa fa-refresh"></i>',
                    'titleAttr' => trans('admin/scheduled_routes.button.reload'),
                    'className' => 'btn-default btn-sm'
                ]
            ]
        ];
        if (auth()->user()->hasPermission('admin.scheduled_routes.create')) {
            $parameters['buttons'][] = [
                'text' => '<div onclick="window.location.href=\''. route('admin.scheduled-routes.create') .'\';"><i class="fa fa-plus"></i> <span class="hidden-xs">'. trans('admin/scheduled_routes.button.create_new') .'</span></div>',
                'titleAttr' => trans('admin/scheduled_routes.button.create_new'),
                'className' => 'btn-success btn-sm buttons-new'
            ];
        }
        $ajax = [
            'url' => route('admin.scheduled-routes.datatables'),
            'type' => 'POST',
            'headers' => [
                'X-CSRF-TOKEN' => csrf_token()
            ],
            // 'data' => json_encode([])
        ];

        $builder->columns($columns)->parameters($parameters)->ajax($ajax);

        return view('admin.scheduled_routes.index', compact('builder'));
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.show')) {
            return redirect_no_permission();
        }

        $scheduledRoute = ScheduledRoute::findOrFail($id);

        return view('admin.scheduled_routes.show', compact('scheduledRoute'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.create')) {
            return redirect_no_permission();
        }

        $drivers = ['' => ''];
        $vehicles = ['' => ''];
        $vehicleTypes = ['' => ''];
        $driversOptions = [];
        $vehiclesOptions = [];
        $tnUser = (new \App\Models\User)->getTable();
        $tnUserProfile = (new \App\Models\UserProfile)->getTable();

        $query = User::join($tnUserProfile, $tnUserProfile .'.user_id', '=', $tnUser .'.id')
          ->select($tnUser .'.*', $tnUserProfile .'.unique_id', $tnUserProfile .'.commission')
          ->role('driver.*')
          ->where($tnUser .'.status', 'approved')
          ->orderBy($tnUserProfile .'.unique_id')
          ->orderBy($tnUser .'.name')
          ->get();

        foreach ($query as $k => $v) {
        		$drivers[$v->id] = $v->getName(true);

            $driversOptions[$v->id] = [
                'commission' => $v->commission,
            ];
        }

        $query = Vehicle::orderBy('name')->get();
        foreach ($query as $k => $v) {
            $vehicles[$v->id] = $v->getName();

            $vehiclesOptions[$v->id] = [
                'user_id' => $v->user_id,
                'is_featured' => $v->selected,
            ];
        }

        $query = VehicleType::where('site_id', config('site.site_id'))->orderBy('name')->get();
        foreach ($query as $k => $v) {
            $vehicleTypes[$v->id] = $v->getName();
        }

        return view('admin.scheduled_routes.create', compact(
            'drivers',
            'vehicles',
            'vehicleTypes',
            'driversOptions',
            'vehiclesOptions'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.create')) {
            return redirect_no_permission();
        }

        $rules = [
            'from' => 'required',
            'to' => 'required',
            'factor_type' => 'required',
            'factor_value' => 'numeric',
            'commission' => 'numeric',
            'is_featured' => 'numeric',
            'order' => 'numeric',
            'status' => 'required',
            'start_at' => 'date',
            'end_at' => 'date',
            'repeat_end' => 'date',
        ];

        $this->validate($request, $rules);

        $params = [
            'factor_type' => $request->get('factor_type', 'addition'),
            'factor_value' => $request->get('factor_value', 0),
            'commission' => $request->get('commission', 0),
        ];

        $scheduledRoute = ScheduledRoute::create([
            'relation_type' => 'site',
            'relation_id' => config('site.site_id'),
            'driver_id' => $request->get('driver_id', 0),
            'vehicle_id' => $request->get('vehicle_id', 0),
            'vehicle_type_id' => $request->get('vehicle_type_id', 0),
            'params' => json_encode($params),
            'is_featured' => $request->get('is_featured') ? 1 : 0,
            'order' => $request->get('order', 0),
            'status' => $request->get('status', 'active'),
        ]);

        if ($scheduledRoute->id) {
            $scheduledRoute->from()->updateOrCreate([
                'relation_type' => 'scheduled_route',
                'relation_id' => $scheduledRoute->id,
                'type' => 'from',
            ], [
                'type' => 'from',
                'address' => $request->get('from', ''),
                'status' => 'active',
            ]);

            $scheduledRoute->to()->updateOrCreate([
                'relation_type' => 'scheduled_route',
                'relation_id' => $scheduledRoute->id,
                'type' => 'to',
            ], [
                'type' => 'to',
                'address' => $request->get('to', ''),
                'status' => 'active',
            ]);

            $start_at = $request->get('start_at') ? Carbon::parse($request->get('start_at')) : Carbon::now();
            $end_at = $request->get('end_at') ? Carbon::parse($request->get('end_at')) : $start_at;

            if ( $request->get('repeat_type') != 'none' ) {
                $repeat_days = [];
                if (!empty($request->get('repeat_days'))) {
                    foreach ((array)$request->get('repeat_days') as $key => $value) {
                        $repeat_days[] = (int)$value;
                    }
                }
                else {
                    $repeat_days[] = $start_at->dayOfWeek;
                }
                $repeat_days = json_encode($repeat_days);

                $repeat_interval = $request->get('repeat_interval') ?: 1;
                $repeat_end = $request->get('repeat_end') ?: null;
                $repeat_limit = $request->get('repeat_limit') ?: 0;
            }
            else {
                $repeat_days = null;
                $repeat_interval = 1;
                $repeat_end = null;
                $repeat_limit = 0;
            }

            $scheduledRoute->event()->updateOrCreate([
                'relation_type' => 'scheduled_route',
                'relation_id' => $scheduledRoute->id,
            ], [
                'start_at' => $start_at,
                'end_at' => $end_at,
                'repeat_type' => $request->get('repeat_type'),
                'repeat_interval' => $repeat_interval,
                'repeat_days' => $repeat_days,
                'repeat_end' => $repeat_end,
                'repeat_limit' => $repeat_limit,
                'status' => 'active',
            ]);
        }

        session()->flash('message', trans('admin/scheduled_routes.message.store_success'));

        return redirect()->route('admin.scheduled-routes.index');
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.edit')) {
            return redirect_no_permission();
        }

        $scheduledRoute = ScheduledRoute::findOrFail($id);

        $params = $scheduledRoute->getParams('raw');
        $repeatDays = !empty($scheduledRoute->event->repeat_days) ? json_decode($scheduledRoute->event->repeat_days) : [];

        $drivers = ['' => ''];
        $vehicles = ['' => ''];
        $vehicleTypes = ['' => ''];
        $driversOptions = [];
        $vehiclesOptions = [];
        $tnUser = (new \App\Models\User)->getTable();
        $tnUserProfile = (new \App\Models\UserProfile)->getTable();

        $query = User::join($tnUserProfile, $tnUserProfile .'.user_id', '=', $tnUser .'.id')
          ->select($tnUser .'.*', $tnUserProfile .'.unique_id', $tnUserProfile .'.commission')
          ->role('driver.*')
          ->where($tnUser .'.status', 'approved')
          ->orderBy($tnUserProfile .'.unique_id')
          ->orderBy($tnUser .'.name')
          ->get();

        foreach ($query as $k => $v) {
        		$drivers[$v->id] = $v->getName(true);

            $driversOptions[$v->id] = [
                'commission' => $v->commission,
            ];
        }

        $query = Vehicle::orderBy('name')->get();
        foreach ($query as $k => $v) {
            $vehicles[$v->id] = $v->getName();

            $vehiclesOptions[$v->id] = [
                'user_id' => $v->user_id,
                'is_featured' => $v->selected,
            ];
        }

        $query = VehicleType::where('site_id', config('site.site_id'))->orderBy('name')->get();
        foreach ($query as $k => $v) {
            $vehicleTypes[$v->id] = $v->getName();
        }

        return view('admin.scheduled_routes.edit', compact(
            'drivers',
            'vehicles',
            'vehicleTypes',
            'driversOptions',
            'vehiclesOptions',
            'scheduledRoute',
            'repeatDays',
            'params'
        ));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.edit')) {
            return redirect_no_permission();
        }

        $scheduledRoute = ScheduledRoute::findOrFail($id);

        $rules = [
            'from' => 'required',
            'to' => 'required',
            'factor_type' => 'required',
            'factor_value' => 'numeric',
            'commission' => 'numeric',
            'is_featured' => 'numeric',
            'order' => 'numeric',
            'status' => 'required',
            'start_at' => 'date',
            'end_at' => 'date',
            'repeat_end' => 'date',
        ];

        $this->validate($request, $rules);

        $params = [
            'factor_type' => $request->get('factor_type', 'addition'),
            'factor_value' => $request->get('factor_value', 0),
            'commission' => $request->get('commission', 0),
        ];

        $scheduledRoute->update([
            'driver_id' => $request->get('driver_id', 0),
            'vehicle_id' => $request->get('vehicle_id', 0),
            'vehicle_type_id' => $request->get('vehicle_type_id', 0),
            'params' => json_encode($params),
            'is_featured' => $request->get('is_featured') ? 1 : 0,
            'order' => $request->get('order', 0),
            'status' => $request->get('status', 'active'),
        ]);

        if ($scheduledRoute->id) {
            $scheduledRoute->from()->updateOrCreate([
                'relation_type' => 'scheduled_route',
                'relation_id' => $scheduledRoute->id,
                'type' => 'from',
            ], [
                'type' => 'from',
                'address' => $request->get('from', ''),
                'status' => 'active',
            ]);

            $scheduledRoute->to()->updateOrCreate([
                'relation_type' => 'scheduled_route',
                'relation_id' => $scheduledRoute->id,
                'type' => 'to',
            ], [
                'type' => 'to',
                'address' => $request->get('to', ''),
                'status' => 'active',
            ]);

            $start_at = $request->get('start_at') ? Carbon::parse($request->get('start_at')) : Carbon::now();
            $end_at = $request->get('end_at') ? Carbon::parse($request->get('end_at')) : $start_at;

            if ( $request->get('repeat_type') != 'none' ) {
                $repeat_days = [];
                if (!empty($request->get('repeat_days'))) {
                    foreach ((array)$request->get('repeat_days') as $key => $value) {
                        $repeat_days[] = (int)$value;
                    }
                }
                else {
                    $repeat_days[] = $start_at->dayOfWeek;
                }
                $repeat_days = json_encode($repeat_days);

                $repeat_interval = $request->get('repeat_interval') ?: 1;
                $repeat_end = $request->get('repeat_end') ?: null;
                $repeat_limit = $request->get('repeat_limit') ?: 0;
            }
            else {
                $repeat_days = null;
                $repeat_interval = 1;
                $repeat_end = null;
                $repeat_limit = 0;
            }

            $scheduledRoute->event()->updateOrCreate([
                'relation_type' => 'scheduled_route',
                'relation_id' => $scheduledRoute->id,
            ], [
                'start_at' => $start_at,
                'end_at' => $end_at,
                'repeat_type' => $request->get('repeat_type'),
                'repeat_interval' => $repeat_interval,
                'repeat_days' => $repeat_days,
                'repeat_end' => $repeat_end,
                'repeat_limit' => $repeat_limit,
                'status' => 'active',
            ]);
        }

        session()->flash('message', trans('admin/scheduled_routes.message.update_success'));

        return redirect()->back();
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.destroy')) {
            return redirect_no_permission();
        }

        $scheduledRoute = ScheduledRoute::findOrFail($id);

        $scheduledRoute->from()->delete();
        $scheduledRoute->to()->delete();
        $scheduledRoute->event()->delete();
        $scheduledRoute->delete();

        session()->flash('message', trans('admin/scheduled_routes.message.destroy_success'));

        if (url()->previous() != url()->full()) {
            return redirect()->back();
        }
        else {
            return redirect()->route('admin.scheduled-routes.index');
        }
    }

    public function featured($id, $featured)
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.edit')) {
            return redirect_no_permission();
        }

        $scheduledRoute = ScheduledRoute::findOrFail($id);

        $scheduledRoute->update([
            'is_featured' => $featured == 'yes' ? 1 : 0,
        ]);

        return redirect()->back();
    }

    public function status($id, $status)
    {
        if (!auth()->user()->hasPermission('admin.scheduled_routes.edit')) {
            return redirect_no_permission();
        }

        $scheduledRoute = ScheduledRoute::findOrFail($id);

        if (in_array($status, ['active', 'inactive'])) {
            $scheduledRoute->update([
                'status' => $status,
            ]);
        }

        return redirect()->back();
    }
}
