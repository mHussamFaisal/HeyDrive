<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Helpers\SiteHelper;
use Datatables;
use Yajra\Datatables\Html\Builder;
use Form;
use Image;
use Storage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehiclesController extends Controller
{
    public function datatables(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.index')) {
            return redirect_no_permission();
        }

        if ( $request->ajax() ) {
            $tnUserProfile = (new \App\Models\UserProfile)->getTable();
            $tnVehicle = (new \App\Models\Vehicle)->getTable();

            $model = Vehicle::with([
                'user',
                'user.profile',
                'user.usedRoleRel',
                'vehicleType',
            ]);

            $model->leftJoin($tnUserProfile, $tnUserProfile .'.user_id', '=', $tnVehicle .'.user_id');
            $model->select($tnVehicle .'.*', $tnUserProfile .'.unique_id');

            if ( $request->get('user') ) {
                $model->where($tnVehicle .'.user_id', '=', $request->get('user'));
            }

            $dt = Datatables::eloquent($model)
                ->addColumn('actions', function(Vehicle $vehicle) {
                    $buttons = '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';

                    if (auth()->user()->hasPermission('admin.vehicles.show')) {
                        $buttons .= '<a href="' . route('admin.vehicles.show', $vehicle->id) . '" class="btn btn-default btn-sm btnView" data-original-title="' . trans('admin/vehicles.button.show') . '">
                        <i class="fa fa-eye"></i>
                      </a>';
                    }
                    if (auth()->user()->hasPermission(['admin.vehicles.edit', 'admin.vehicles.destroy'])) {
                        $buttons .= '<div class="btn-group pull-left" role="group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                          <span class="fa fa-angle-down"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">';
                        if (auth()->user()->hasPermission('admin.vehicles.edit')) {
                            $buttons .= '<li>
                            <a href="' . route('admin.vehicles.edit', $vehicle->id) . '" class="btnEdit" style="padding:3px 8px;" data-original-title="' . trans('admin/vehicles.button.edit') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-pencil-square-o"></i>
                              </span>
                              ' . trans('admin/vehicles.button.edit') . '
                            </a>
                          </li>';
                        }
                        if (auth()->user()->hasPermission('admin.vehicles.destroy')) {
                            $buttons .= '<li>
                            <a href="#" onclick="$(\'#button_delete_id_' . $vehicle->id . '\').click(); return false;" class="btnDelete" style="padding:3px 8px;" data-original-title="' . trans('admin/vehicles.button.destroy') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-trash"></i>
                              </span>
                              ' . trans('admin/vehicles.button.destroy') . '
                            </a>
                          </li>';
                        }
                        $buttons .= '</ul>';
                    }
                    $buttons .= '</div>
                    </div>';

                    $buttons .= Form::open(['method' => 'delete', 'route' => ['admin.vehicles.destroy', $vehicle->id], 'class' => 'form-inline form-delete hide']);
                    $buttons .= Form::button(trans('admin/vehicles.button.destroy'), ['type' => 'submit', 'class' => 'delete', 'name' => 'delete_modal', 'id' => 'button_delete_id_'. $vehicle->id]);
                    $buttons .= Form::close();

                    return $buttons;
                })
                ->setRowId(function (Vehicle $vehicle) {
                    return 'vehicle_row_'. $vehicle->id;
                })
                ->editColumn('name', function(Vehicle $vehicle) {
                    return '<a href="'. route('admin.vehicles.show', $vehicle->id) .'" class="text-default">'. $vehicle->getName() .'</a>';
                })
                ->editColumn('image', function(Vehicle $vehicle) {
                    return '<img src="'. asset_url( $vehicle->getImagePath() ) .'" class="img-circle" alt="" style="max-width:50px; max-height:50px;" />';
                })
                ->editColumn('user_id', function(Vehicle $vehicle) {
                    if (auth()->user()->hasPermission('admin.users.driver.show')) {
                        return !empty($vehicle->user->name) ? '<a href="'. route('admin.users.show', $vehicle->user_id) .'" class="text-default">'. $vehicle->user->getName(true) .'</a>' : '';
                    }
                    return !empty($vehicle->user) ? $vehicle->user->getName(true) : '';
                })
                ->editColumn('vehicle_type_id', function(Vehicle $vehicle) {
                    return !empty($vehicle->vehicleType->name) ? $vehicle->vehicleType->getName() : '';
                })
                ->editColumn('mot', function(Vehicle $vehicle) {
                    return trim($vehicle->mot .' '. $vehicle->getExpiryDate('mot_expiry_date'));
                })
                ->editColumn('status', function(Vehicle $vehicle) {
                    if (auth()->user()->hasPermission('admin.vehicles.edit')) {
                        return '<a href="' . route('admin.vehicles.status', [$vehicle->id, ($vehicle->status == 'activated') ? 'inactive' : 'activated']) . '" class="text-success status-icon">' . $vehicle->getStatus('label') . '</a>';
                    }
                    return $vehicle->getStatus('label');
                })
                ->editColumn('selected', function(Vehicle $vehicle) {
                    if (auth()->user()->hasPermission('admin.vehicles.edit')) {
                        return $vehicle->selected ? '<a href="' . route('admin.vehicles.selected', [$vehicle->id, 'no']) . '" class="text-success status-icon" title="' . trans('admin/vehicles.yes') . '"><i class="fa fa-check-circle"></i></a>' : '<a href="' . route('admin.vehicles.selected', [$vehicle->id, 'yes']) . '" class="text-danger status-icon" title="' . trans('admin/vehicles.no') . '"><i class="fa fa-times-circle"></i></a>';
                    }
                    return $vehicle->selected ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-times-circle"></i>';
                })
                ->editColumn('created_at', function(Vehicle $vehicle) {
                    return SiteHelper::formatDateTime($vehicle->created_at);
                })
                ->editColumn('updated_at', function(Vehicle $vehicle) {
                    return SiteHelper::formatDateTime($vehicle->updated_at);
                })
                ->orderColumn('user_id', '-unique_id $1, user_id $1');

            return $dt->make(true);
        }
    }

    public function index(Builder $builder, Request $request)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.index')) {
            return redirect_no_permission();
        }

        $columns = [
            ['data' => 'image', 'name' => 'image', 'title' => trans('admin/vehicles.image'), 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true],
            ['data' => 'id', 'name' => 'id', 'title' => trans('admin/vehicles.id'), 'visible' => false],
            ['data' => 'name', 'name' => 'name', 'title' => trans('admin/vehicles.name')],
            ['data' => 'user_id', 'name' => 'user_id', 'title' => trans('admin/vehicles.user')],
            ['data' => 'vehicle_type_id', 'name' => 'vehicle_type_id', 'title' => trans('admin/vehicles.vehicle_type')],
            ['data' => 'registration_mark', 'name' => 'registration_mark', 'title' => trans('admin/vehicles.registration_mark')],
            ['data' => 'mot', 'name' => 'mot', 'title' => trans('admin/vehicles.mot')],
            ['data' => 'make', 'name' => 'make', 'title' => trans('admin/vehicles.make'), 'visible' => false],
            ['data' => 'model', 'name' => 'model', 'title' => trans('admin/vehicles.model'), 'visible' => false],
            ['data' => 'colour', 'name' => 'colour', 'title' => trans('admin/vehicles.colour'), 'visible' => false],
            ['data' => 'body_type', 'name' => 'body_type', 'title' => trans('admin/vehicles.body_type'), 'visible' => false],
            ['data' => 'no_of_passengers', 'name' => 'no_of_passengers', 'title' => trans('admin/vehicles.no_of_passengers'), 'visible' => false],
            ['data' => 'registered_keeper_name', 'name' => 'registered_keeper_name', 'title' => trans('admin/vehicles.registered_keeper_name'), 'visible' => false],
            ['data' => 'registered_keeper_address', 'name' => 'registered_keeper_address', 'title' => trans('admin/vehicles.registered_keeper_address'), 'visible' => false],
            ['data' => 'description', 'name' => 'description', 'title' => trans('admin/vehicles.description'), 'visible' => false],
            ['data' => 'status', 'name' => 'status', 'title' => trans('admin/vehicles.status'), 'searchable' => false],
            ['data' => 'selected', 'name' => 'selected', 'title' => trans('admin/vehicles.selected'), 'searchable' => false],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => trans('admin/vehicles.updated_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('admin/vehicles.created_at'), 'visible' => false, 'searchable' => false]
        ];

        if (auth()->user()->hasPermission(['admin.vehicles.show', 'admin.vehicles.edit', 'admin.vehicles.destroy'])) {
            $columns = array_merge(
                [['data' => 'actions', 'defaultContent' => '', 'name' => 'actions', 'title' => trans('admin/vehicles.actions'), 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true]],
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
                [2, 'asc']
            ],
            'pageLength' => 10,
            'lengthMenu' => [5, 10, 25, 50],
            'language' => [
                'search' => '_INPUT_',
                'searchPlaceholder' => trans('admin/vehicles.search_placeholder'),
                'lengthMenu' => '_MENU_',
                'paginate' => [
                    'first' => '<i class="fa fa-angle-double-left"></i>',
                    'previous' => '<i class="fa fa-angle-left"></i>',
                    'next' => '<i class="fa fa-angle-right"></i>',
                    'last' => '<i class="fa fa-angle-double-right"></i>'
                ]
            ],
            'dom' => '<"row topContainer"<"col-xs-6 col-sm-6 col-md-7 dataTablesHeaderLeft"B><"col-xs-6 col-sm-6 col-md-5 dataTablesHeaderRight"f>><"dataTablesBody"rt><"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
            // 'initComplete' => 'function() { /*$(".dataTables_length select").select2({minimumResultsForSearch: 5});*/ }',
            'drawCallback' => 'function() { $(\'#vehicles [data-toggle="tooltip"]\').tooltip(\'hide\'); $(\'#vehicles [title]\').tooltip({ placement: \'auto\', container: \'body\', selector: \'\', html: true, trigger: \'hover\', delay: {\'show\': 500,\'hide\': 100 } }); }',
            'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
            'searchDelay' => 350,
            'buttons' => [
                // [
                //     'extend' => 'collection',
                //     'text' => '<i class="fa fa-bars"></i>',
                //     'titleAttr' => trans('admin/vehicles.button.more'),
                //     'className' => 'btn-default',
                //     'buttons' => [
                //
                //     ]
                // ],
                [
                    'extend' => 'colvis',
                    'text' => '<i class="fa fa-eye"></i>',
                    'titleAttr' => trans('admin/vehicles.button.column_visibility'),
                    'postfixButtons' => ['colvisRestore'],
                    'className' => 'btn-default btn-sm'
                ],
                // [
                //     'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().colReorder.reset();"><i class="fa fa-arrows-h"></i></div>',
                //     'titleAttr' => trans('admin/vehicles.button.reset_column_order'),
                //     'className' => 'btn-default btn-sm'
                // ],
                // [
                //     'extend' => 'reset',
                //     'text' => '<i class="fa fa-undo"></i>',
                //     'titleAttr' => trans('admin/vehicles.button.reset'),
                //     'className' => 'btn-default btn-sm'
                // ],
                [
                    'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                    'titleAttr' => trans('admin/vehicles.button.reset'),
                    'className' => 'btn-default btn-sm'
                ],
                [
                    'extend' => 'reload',
                    'text' => '<i class="fa fa-refresh"></i>',
                    'titleAttr' => trans('admin/vehicles.button.reload'),
                    'className' => 'btn-default btn-sm'
                ]
            ]
        ];

        if (auth()->user()->hasPermission('admin.vehicles.create')) {
            $parameters['buttons'][] = [
                'text' => '<div onclick="window.location.href=\''. route('admin.vehicles.create') .'\';"><i class="fa fa-plus"></i> <span class="hidden-xs">'. trans('admin/vehicles.button.create_new') .'</span></div>',
                'titleAttr' => trans('admin/vehicles.button.create_new'),
                'className' => 'btn-success btn-sm buttons-new'
            ];
        }

        $ajax = [
            'url' => route('admin.vehicles.datatables'),
            'type' => 'POST',
            'headers' => [
                'X-CSRF-TOKEN' => csrf_token()
            ],
            'data' => json_encode([
                'user' => $request->get('user')
            ])
        ];

        $builder->columns($columns)->parameters($parameters)->ajax($ajax);

        return view('admin.vehicles.index', compact('builder'));
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.show')) {
            return redirect_no_permission();
        }

        $vehicle = Vehicle::findOrFail($id);

        return view('admin.vehicles.show', compact('vehicle'));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.create')) {
            return redirect_no_permission();
        }

        $usersList = User::role('driver.*')->get();
        $users = ['' => ''];
        foreach ($usersList as $k => $v) {
            $users[$v->id] = $v->getName(true);
        }

        $vehicleTypesList = VehicleType::with('site')->orderBy('site_id')->orderBy('name')->get();
        $vehicleTypes = ['' => ''];
        foreach ($vehicleTypesList as $k => $v) {
            $vehicleTypes[$v->id] = $v->getName(true) . (count($request->system->sites) > 1 && !empty($v->site->name) ? ' ('.  $v->site->getName() .')' : '');
        }

        $statusList = (new Vehicle)->statusOptions;
        $status = [];
        foreach($statusList as $key => $value) {
            $status[$key] = $value['name'];
        }

        return view('admin.vehicles.create', compact('users', 'vehicleTypes', 'status'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.create')) {
            return redirect_no_permission();
        }

        $rules = [
            'user_id' => 'numeric',
            'vehicle_type_id' => 'numeric',
            'name' => 'required|max:255',
            'image' => 'mimes:jpg,jpeg,gif,png',
            'registration_mark' => 'max:255',
            'mot' => 'max:255',
            'mot_expiry_date' => 'date',
            'make' => 'max:255',
            'model' => 'max:255',
            'colour' => 'max:255',
            'body_type' => 'max:255',
            'no_of_passengers' => 'numeric',
            'registered_keeper_name' => 'max:255',
            'registered_keeper_address' => 'max:500',
            'selected' => 'numeric'
        ];

        $this->validate($request, $rules);

        if ( $request->get('selected') ) {
            Vehicle::where('user_id', '=', $request->get('user_id'))->update(['selected' => 0]);
        }

        $vehicle = new Vehicle;
        $vehicle->user_id = (int)$request->get('user_id');
        $vehicle->vehicle_type_id = (int)$request->get('vehicle_type_id');
        $vehicle->name = $request->get('name');
        $vehicle->registration_mark = $request->get('registration_mark');
        $vehicle->mot = $request->get('mot');
        $vehicle->mot_expiry_date = $request->get('mot_expiry_date');
        $vehicle->make = $request->get('make');
        $vehicle->model = $request->get('model');
        $vehicle->colour = $request->get('colour');
        $vehicle->body_type = $request->get('body_type');
        $vehicle->no_of_passengers = $request->get('no_of_passengers');
        $vehicle->registered_keeper_name = $request->get('registered_keeper_name');
        $vehicle->registered_keeper_address = $request->get('registered_keeper_address');
        $vehicle->description = $request->get('description');
        $vehicle->status = $request->get('status');
        $vehicle->selected = $request->get('selected');

        if ( $request->hasFile('image') || $request->get('image_delete') ) {
            if ( Storage::disk('vehicles')->exists($vehicle->image) ) {
                Storage::disk('vehicles')->delete($vehicle->image);
                $vehicle->image = null;
            }
        }

        if ( $request->hasFile('image') ) {
        		$file = $request->file('image');
        		$filename = \App\Helpers\SiteHelper::generateFilename('vehicle') .'.'. $file->getClientOriginalExtension();

            $img = Image::make($file);

            if ($img->width() > config('site.image_dimensions.vehicle.width')) {
                $img->resize(config('site.image_dimensions.vehicle.width'), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if ($img->height() > config('site.image_dimensions.vehicle.height')) {
                $img->resize(null, config('site.image_dimensions.vehicle.height'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $img->save(asset_path('uploads','vehicles/'. $filename));

        		$vehicle->image = $filename;
      	}

        $vehicle->save();

        \Cache::store('file')->forget('admin_check_vehicle_documents');

        session()->flash('message', trans('admin/vehicles.message.store_success'));
        return redirect()->route('admin.vehicles.index');
    }

    public function edit(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.edit')) {
            return redirect_no_permission();
        }

        $vehicle = Vehicle::findOrFail($id);

        $usersList = User::role('driver.*')->get();
        $users = ['' => ''];
        foreach ($usersList as $k => $v) {
            $users[$v->id] = $v->getName(true);
        }

        $vehicleTypesList = VehicleType::with('site')->orderBy('site_id')->orderBy('name')->get();
        $vehicleTypes = ['' => ''];
        foreach ($vehicleTypesList as $k => $v) {
            $vehicleTypes[$v->id] = $v->getName(true) . (count($request->system->sites) > 1 && !empty($v->site->name) ? ' ('.  $v->site->getName() .')' : '');
        }

        $statusList = $vehicle->statusOptions;
        $status = [];
        foreach($statusList as $key => $value) {
            $status[$key] = $value['name'];
        }

        return view('admin.vehicles.edit', compact('vehicle', 'users', 'vehicleTypes', 'status'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.edit')) {
            return redirect_no_permission();
        }

        $vehicle = Vehicle::findOrFail($id);

        if ( $request->get('user_id') ) {
            $user = User::findOrFail( $request->get('user_id') );
        }

        $rules = [
            'user_id' => 'numeric',
            'vehicle_type_id' => 'numeric',
            'name' => 'required|max:255',
            'image' => 'mimes:jpg,jpeg,gif,png',
            'registration_mark' => 'max:255',
            'mot' => 'max:255',
            'mot_expiry_date' => 'date',
            'make' => 'max:255',
            'model' => 'max:255',
            'colour' => 'max:255',
            'body_type' => 'max:255',
            'no_of_passengers' => 'numeric',
            'registered_keeper_name' => 'max:255',
            'registered_keeper_address' => 'max:500',
            'selected' => 'numeric'
        ];

        $this->validate($request, $rules);

        if ( $request->get('selected') ) {
            Vehicle::where('user_id', '=', $request->get('user_id'))->update(['selected' => 0]);
        }

        $vehicle->user_id = (int)$request->get('user_id');
        $vehicle->vehicle_type_id = (int)$request->get('vehicle_type_id');
        $vehicle->name = $request->get('name');
        $vehicle->registration_mark = $request->get('registration_mark');
        $vehicle->mot = $request->get('mot');
        $vehicle->mot_expiry_date = $request->get('mot_expiry_date');
        $vehicle->make = $request->get('make');
        $vehicle->model = $request->get('model');
        $vehicle->colour = $request->get('colour');
        $vehicle->body_type = $request->get('body_type');
        $vehicle->no_of_passengers = $request->get('no_of_passengers');
        $vehicle->registered_keeper_name = $request->get('registered_keeper_name');
        $vehicle->registered_keeper_address = $request->get('registered_keeper_address');
        $vehicle->description = $request->get('description');
        $vehicle->status = $request->get('status');
        $vehicle->selected = $request->get('selected');
        $vehicle->updated_at = Carbon::now();

        if ( $request->hasFile('image') || $request->get('image_delete') ) {
            if ( Storage::disk('vehicles')->exists($vehicle->image) ) {
                Storage::disk('vehicles')->delete($vehicle->image);
                $vehicle->image = null;
            }
        }

        if ( $request->hasFile('image') ) {
        		$file = $request->file('image');
            $filename = \App\Helpers\SiteHelper::generateFilename('vehicle') .'.'. $file->getClientOriginalExtension();

            $img = Image::make($file);

            if ($img->width() > config('site.image_dimensions.vehicle.width')) {
                $img->resize(config('site.image_dimensions.vehicle.width'), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if ($img->height() > config('site.image_dimensions.vehicle.height')) {
                $img->resize(null, config('site.image_dimensions.vehicle.height'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $img->save(asset_path('uploads','vehicles/'. $filename));

        		$vehicle->image = $filename;
      	}

        $vehicle->save();

        \Cache::store('file')->forget('admin_check_vehicle_documents');

        session()->flash('message', trans('admin/vehicles.message.update_success'));
        return redirect()->back();
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.destroy')) {
            return redirect_no_permission();
        }

        $vehicle = Vehicle::findOrFail($id);

        if ( Storage::disk('vehicles')->exists($vehicle->image) ) {
            Storage::disk('vehicles')->delete($vehicle->image);
        }

        $vehicle->delete();

        \Cache::store('file')->forget('admin_check_vehicle_documents');

        session()->flash('message', trans('admin/vehicles.message.destroy_success'));

        if ( url()->previous() != url()->full() ) {
            return redirect()->back();
        }
        else {
            return redirect()->route('admin.vehicles.index');
        }
    }

    public function status($id, $status)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.edit')) {
            return redirect_no_permission();
        }

        $vehicle = Vehicle::findOrFail($id);

        $allowed = [
            'activated',
            'inactive'
        ];

        if ( in_array($status, $allowed) ) {
            $vehicle->status = $status;
            $vehicle->save();
        }

        return redirect()->back();
    }

    public function selected($id, $selected)
    {
        if (!auth()->user()->hasPermission('admin.vehicles.edit')) {
            return redirect_no_permission();
        }

        $vehicle = Vehicle::findOrFail($id);

        if ( $selected == 'yes' ) {
            Vehicle::where('user_id', '=', $vehicle->user_id)->update(['selected' => 0]);
        }

        $vehicle->selected = ($selected == 'yes') ? 1 : 0;
        $vehicle->save();

        return redirect()->back();
    }
}
