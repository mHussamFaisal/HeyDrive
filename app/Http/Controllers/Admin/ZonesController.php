<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Relation;
use App\Helpers\SiteHelper;
use Datatables;
use Yajra\Datatables\Html\Builder;
use Form;
use Carbon\Carbon;
use DB;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ZonesController extends Controller
{
    public function datatables(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.zones.index')) {
            return redirect_no_permission();
        }

        if ($request->ajax()) {
            $model = Location::select('*')->ofType('zone');

            $dt = Datatables::eloquent($model)
                ->addColumn('actions', function(Location $zone) {
                    $buttons = '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';

                    if (auth()->user()->hasPermission('admin.zones.show')) {
                        $buttons .= '<a href="' . route('admin.zones.show', $zone->id) . '" class="btn btn-default btn-sm btnView" data-original-title="' . trans('admin/zones.button.show') . '">
                        <i class="fa fa-eye"></i>
                      </a>';
                    }
                    if (auth()->user()->hasPermission(['admin.zones.create','zones.edit','admin.zones.destroy'])) {
                    $buttons .= '<div class="btn-group pull-left" role="group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                          <span class="fa fa-angle-down"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">';
                        if (auth()->user()->hasPermission('admin.zones.edit')) {
                            $buttons .= '<li>
                            <a href="' . route('admin.zones.edit', $zone->id) . '" class="btnEdit" style="padding:3px 8px;" data-original-title="' . trans('admin/zones.button.edit') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-pencil-square-o"></i>
                              </span>
                              ' . trans('admin/zones.button.edit') . '
                            </a>
                          </li>';
                        }
                        if (auth()->user()->hasPermission('admin.zones.create')) {
                            $buttons .= '<li>
                            <a href="' . route('admin.zones.copy', $zone->id) . '" class="btnCopy" style="padding:3px 8px;" data-original-title="' . trans('admin/zones.button.copy') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-files-o"></i>
                              </span>
                              ' . trans('admin/zones.button.copy') . '
                            </a>
                          </li>';
                        }
                        if (auth()->user()->hasPermission('admin.zones.destroy')) {
                            $buttons .= '<li>
                            <a href="#" onclick="$(\'#button_delete_id_' . $zone->id . '\').click(); return false;" class="btnDelete" style="padding:3px 8px;" data-original-title="' . trans('admin/zones.button.destroy') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-trash"></i>
                              </span>
                              ' . trans('admin/zones.button.destroy') . '
                            </a>
                          </li>';
                        }
                        $buttons .= '</ul>';
                    }
                    $buttons .= '</div>
                    </div>';

                    $buttons .= Form::open(['method' => 'delete', 'route' => ['admin.zones.destroy', $zone->id], 'class' => 'form-inline form-delete hide']);
                    $buttons .= Form::button(trans('admin/zones.button.destroy'), ['type' => 'submit', 'class' => 'delete', 'name' => 'delete_modal', 'id' => 'button_delete_id_'. $zone->id]);
                    $buttons .= Form::close();

                    return $buttons;
                })
                ->setRowId(function (Location $zone) {
                    return 'zone_row_'. $zone->id;
                })
                ->editColumn('name', function(Location $zone) {
                    if (auth()->user()->hasPermission('admin.zones.show')) {
                        return '<a href="' . route('admin.zones.show', $zone->id) . '" class="text-default">' . $zone->getName() . '</a>';
                    }
                    return $zone->getName();
                })
                ->editColumn('status', function(Location $zone) {
                    if (auth()->user()->hasPermission('admin.zones.edit')) {
                        return '<a href="' . route('admin.zones.status', [$zone->id, ($zone->status == 'active') ? 'inactive' : 'active']) . '" class="text-success status-icon">' . $zone->getStatus('label') . '</a>';
                    }
                    return $zone->getStatus('label');
                })
                ->editColumn('created_at', function(Location $zone) {
                    return SiteHelper::formatDateTime($zone->created_at);
                })
                ->editColumn('updated_at', function(Location $zone) {
                    return SiteHelper::formatDateTime($zone->updated_at);
                });

            return $dt->make(true);
        }
    }

    public function index(Builder $builder, Request $request)
    {
        if (!auth()->user()->hasPermission('admin.zones.index')) {
            return redirect_no_permission();
        }

        $columns = [
            // ['data' => 'id', 'name' => 'id', 'title' => trans('admin/zones.id'), 'visible' => false],
            ['data' => 'name', 'name' => 'name', 'title' => trans('admin/zones.name')],
            ['data' => 'order', 'name' => 'order', 'title' => trans('admin/zones.order'), 'visible' => false],
            ['data' => 'status', 'name' => 'status', 'title' => trans('admin/zones.status'), 'searchable' => false],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => trans('admin/zones.updated_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('admin/zones.created_at'), 'visible' => false, 'searchable' => false],
        ];

        if (auth()->user()->hasPermission(['admin.zones.show', 'admin.zones.create', 'admin.zones.edit', 'admin.zones.destroy'])) {
            $columns = array_merge(
                [['data' => 'actions', 'name' => 'actions', 'title' => trans('admin/zones.actions'), 'defaultContent' => '', 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true, 'width' => '100px']],
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
                [3, 'desc']
            ],
            'pageLength' => 10,
            'lengthMenu' => [5, 10, 25, 50],
            'language' => [
                'search' => '_INPUT_',
                'searchPlaceholder' => trans('admin/zones.search_placeholder'),
                'lengthMenu' => '_MENU_',
                'paginate' => [
                    'first' => '<i class="fa fa-angle-double-left"></i>',
                    'previous' => '<i class="fa fa-angle-left"></i>',
                    'next' => '<i class="fa fa-angle-right"></i>',
                    'last' => '<i class="fa fa-angle-double-right"></i>'
                ]
            ],
            'dom' => '<"row topContainer"<"col-xs-6 col-sm-6 col-md-7 dataTablesHeaderLeft"B><"col-xs-6 col-sm-6 col-md-5 dataTablesHeaderRight"f>><"dataTablesBody"rt><"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
            'drawCallback' => 'function() { $(\'#zones [data-toggle="tooltip"]\').tooltip(\'hide\'); $(\'#zones [title]\').tooltip({ placement: \'auto\', container: \'body\', selector: \'\', html: true, trigger: \'hover\', delay: {\'show\': 500,\'hide\': 100 } }); }',
            'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
            'searchDelay' => 350,
            'buttons' => [
                [
                    'extend' => 'colvis',
                    'collectionLayout' => 'two-column',
                    'text' => '<i class="fa fa-eye"></i>',
                    'titleAttr' => trans('admin/zones.button.column_visibility'),
                    'postfixButtons' => ['colvisRestore'],
                    'className' => 'btn-default btn-sm'
                ], [
                    'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                    'titleAttr' => trans('admin/zones.button.reset'),
                    'className' => 'btn-default btn-sm',
                ], [
                    'extend' => 'reload',
                    'text' => '<i class="fa fa-refresh"></i>',
                    'titleAttr' => trans('admin/zones.button.reload'),
                    'className' => 'btn-default btn-sm'
                ]
            ]
        ];

        if (auth()->user()->hasPermission('admin.zones.create')) {
            $parameters['buttons'][] = [
                'text' => '<div onclick="window.location.href=\''. route('admin.zones.create') . ($request->get('role') ? '?role='. $request->get('role') : '') .'\';"><i class="fa fa-plus"></i> <span class="hidden-xs">'. trans('admin/zones.button.create_new') .'</span></div>',
                'titleAttr' => trans('admin/zones.button.create_new'),
                'className' => 'btn-success btn-sm buttons-new'
            ];
        }

        $ajax = [
            'url' => route('admin.zones.datatables'),
            'type' => 'POST',
            'headers' => [
                'X-CSRF-TOKEN' => csrf_token()
            ],
            'data' => json_encode([
                'role' => $request->get('role')
            ])
        ];

        $builder->columns($columns)->parameters($parameters)->ajax($ajax);

        return view('admin.zones.index', compact('builder'));
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermission('admin.zones.show')) {
            return redirect_no_permission();
        }

        $zone = Location::findOrFail($id);

        return view('admin.zones.show', compact('zone'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('admin.zones.create')) {
            return redirect_no_permission();
        }

        $zone = new Location;

        $status = [];
        foreach($zone->statusOptions as $key => $value) {
            $status[$key] = $value['name'];
        }

        return view('admin.zones.create', compact('status'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.zones.create')) {
            return redirect_no_permission();
        }

        $errors = [];
        $rules = [
            'name' => 'required|max:255',
            'lat' => 'numeric|required',
            'lng' => 'numeric|required',
            'radius' => 'numeric|required',
            'order' => 'numeric|required',
            'status' => 'required',
        ];
        $messages = [];
        $attributeNames = [];

        $validator = $this->validate($request, $rules, $messages, $attributeNames);

        $zone = new Location;
        $zone->relation_id = 0;
        $zone->relation_type = 'site';
        $zone->type = 'zone';
        $zone->name = $request->get('name');
        $zone->address = $request->get('address');
        $zone->postcode = null;
        $zone->city = null;
        $zone->state = null;
        $zone->country = null;
        $zone->lat = $request->get('lat');
        $zone->lng = $request->get('lng');
        $zone->radius = $request->get('radius');
        $zone->params = null;
        $zone->order = $request->get('order');
        $zone->status = $request->get('status');
        $zone->save();

        if ( !empty($errors) ) {
            session()->flash('message', trans('admin/zones.message.store_success_with_errors'));
            return redirect()->route('admin.zones.edit', $zone->id)->withErrors($errors);
        }
        else {
            session()->flash('message', trans('admin/zones.message.store_success'));
            return redirect()->route('admin.zones.index');
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermission('admin.zones.edit')) {
            return redirect_no_permission();
        }

        $zone = Location::findOrFail($id);

        $status = [];
        foreach($zone->statusOptions as $key => $value) {
            $status[$key] = $value['name'];
        }

        return view('admin.zones.edit', compact('zone', 'status'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('admin.zones.edit')) {
            return redirect_no_permission();
        }

        $zone = Location::findOrFail($id);

        $errors = [];
        $rules = [
            'name' => 'required|max:255',
            'lat' => 'numeric|required',
            'lng' => 'numeric|required',
            'radius' => 'numeric|required',
            'order' => 'numeric|required',
            'status' => 'required',
        ];
        $messages = [];
        $attributeNames = [];

        $validator = $this->validate($request, $rules, $messages, $attributeNames);

        $zone->relation_id = 0;
        $zone->relation_type = 'site';
        $zone->type = 'zone';
        $zone->name = $request->get('name');
        $zone->address = $request->get('address');
        $zone->postcode = null;
        $zone->city = null;
        $zone->state = null;
        $zone->country = null;
        $zone->lat = $request->get('lat');
        $zone->lng = $request->get('lng');
        $zone->radius = $request->get('radius');
        $zone->params = null;
        $zone->order = $request->get('order');
        $zone->status = $request->get('status');
        $zone->updated_at = Carbon::now();
        $zone->save();

        if ( !empty($errors) ) {
            session()->flash('message', trans('admin/zones.message.update_success_with_errors'));
            // return redirect()->route('admin.zones.edit', $zone->id)->withErrors($errors);
            return redirect()->back()->withErrors($errors);
        }
        else {
            session()->flash('message', trans('admin/zones.message.update_success'));
            // return redirect()->route('admin.zones.index');
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('admin.zones.destroy')) {
            return redirect_no_permission();
        }

        Relation::where('relation_type', 'fixed_price_location')->where('target_id', $id)->delete();
        Location::findOrFail($id)->delete();

        session()->flash('message', trans('admin/zones.message.destroy_success'));

        if ( url()->previous() != url()->full() ) {
            return redirect()->back();
        }
        else {
            return redirect()->route('admin.zones.index');
        }
    }

    public function copy($id)
    {
        if (!auth()->user()->hasPermission('admin.zones.create')) {
            return redirect_no_permission();
        }

        $old = Location::findOrFail($id);
        $new = $old->replicate();
        $new->name .= ' - Copy';
        $new->save();

        session()->flash('message', trans('admin/zones.message.copy_success'));

        return redirect()->back();
    }

    public function status($id, $status)
    {
        if (!auth()->user()->hasPermission('admin.zones.edit')) {
            return redirect_no_permission();
        }

        $zone = Location::findOrFail($id);

        $allowed = [
            'active',
            'inactive',
        ];

        if (in_array($status, $allowed)) {
            $zone->status = $status;
            $zone->save();
        }

        return redirect()->back();
    }
}
