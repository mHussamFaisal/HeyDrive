<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Helpers\SiteHelper;
use Yajra\Datatables\Html\Builder;
use Datatables;
use Form;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function datatables(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.services.index')) {
            return redirect_no_permission();
        }

        if ($request->ajax()) {
            $model = Service::query();

            $model->where('relation_type', 'site')->where('relation_id', config('site.site_id'));

            $dt = Datatables::eloquent($model)
                ->addColumn('actions', function(Service $service) {
                    $buttons = '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';
                    if (auth()->user()->hasPermission('admin.services.show')) {
                        $buttons .= '<a href="' . route('admin.services.show', $service->id) . '" class="btn btn-default btn-sm btnView" data-original-title="' . trans('admin/services.button.show') . '">
                        <i class="fa fa-eye"></i>
                      </a>';
                    }
                    if (auth()->user()->hasPermission(['admin.services.edit','admin.services.destroy'])) {
                        $buttons .= '<div class="btn-group pull-left" role="group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                          <span class="fa fa-angle-down"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">';
                        if (auth()->user()->hasPermission('admin.services.edit')) {
                            $buttons .= '<li>
                            <a href="' . route('admin.services.edit', $service->id) . '" class="btnEdit" style="padding:3px 8px;" data-original-title="' . trans('admin/services.button.edit') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-pencil-square-o"></i>
                              </span>
                              <span>' . trans('admin/services.button.edit') . '</span>
                            </a>
                          </li>';
                        }
                        if (auth()->user()->hasPermission('admin.services.destroy')) {
                            $buttons .= '<li>
                            <a href="#" onclick="$(\'#button_delete_id_' . $service->id . '\').click(); return false;" class="btnDelete" style="padding:3px 8px;" data-original-title="' . trans('admin/services.button.destroy') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-trash"></i>
                              </span>
                              <span>' . trans('admin/services.button.destroy') . '</span>
                            </a>
                          </li>';
                        }
                        $buttons .= '</ul>';
                    }
                    $buttons .= '</div>
                    </div>';

                    $buttons .= Form::open(['method' => 'delete', 'route' => ['admin.services.destroy', $service->id], 'class' => 'form-inline form-delete hide']);
                    $buttons .= Form::button(trans('admin/services.button.destroy'), ['type' => 'submit', 'class' => 'delete', 'name' => 'delete_modal', 'id' => 'button_delete_id_'. $service->id]);
                    $buttons .= Form::close();

                    return $buttons;
                })
                ->setRowId(function (Service $service) {
                    return 'service_row_'. $service->id;
                })
                ->editColumn('name', function(Service $service) {
                    return '<a href="'. route('admin.services.show', $service->id) .'" class="text-default">'. $service->getName() .'</a>';
                })
                ->editColumn('type', function(Service $service) {
                    return trans('admin/services.types.'. $service->type);
                })
                ->editColumn('params', function(Service $service) {
                    return $service->getParams();
                })
                ->editColumn('is_featured', function(Service $service) {
                    $url = route('admin.services.featured', [$service->id, $service->is_featured ? 'no' : 'yes']);
                    $title = trans('admin/services.'. ($service->is_featured ? 'yes' : 'no'));
                    if (auth()->user()->hasPermission('admin.services.edit')) {
                        return '<a href="' . $url . '" title="' . $title . '" class="text-default">
                              <i class="fa ' . ($service->is_featured ? 'fa-star' : 'fa-star-o') . '"></i>
                            </a>';
                    }
                    return ' <i class="fa ' . ($service->is_featured ? 'fa-star' : 'fa-star-o') . '"></i>';
                })
                ->editColumn('status', function(Service $service) {
                    if (auth()->user()->hasPermission('admin.services.edit')) {
                        return '<a href="' . route('admin.services.status', [$service->id, $service->status == 'active' ? 'inactive' : 'active']) . '" class="text-default">' . $service->getStatus('label') . '</a>';
                    }
                    return $service->getStatus('label');
                })
                ->editColumn('created_at', function(Service $service) {
                    return SiteHelper::formatDateTime($service->created_at);
                })
                ->editColumn('updated_at', function(Service $service) {
                    return SiteHelper::formatDateTime($service->updated_at);
                });

            return $dt->make(true);
        }
    }

    public function index(Builder $builder, Request $request)
    {
        if (!auth()->user()->hasPermission('admin.services.index')) {
            return redirect_no_permission();
        }

        $columns = [
            ['data' => 'actions', 'defaultContent' => '', 'name' => 'actions', 'title' => trans('admin/services.actions'), 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true],
            ['data' => 'name', 'name' => 'name', 'title' => trans('admin/services.name')],
            ['data' => 'description', 'name' => 'description', 'title' => trans('admin/services.description'), 'visible' => false],
            ['data' => 'type', 'name' => 'type', 'title' => trans('admin/services.type')],
            ['data' => 'params', 'name' => 'params', 'title' => trans('admin/services.params'), 'orderable' => false, 'searchable' => false],
            ['data' => 'is_featured', 'name' => 'is_featured', 'title' => trans('admin/services.is_featured'), 'searchable' => false],
            ['data' => 'order', 'name' => 'order', 'title' => trans('admin/services.order'), 'searchable' => false, 'visible' => false],
            ['data' => 'status', 'name' => 'status', 'title' => trans('admin/services.status'), 'searchable' => false],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => trans('admin/services.updated_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('admin/services.created_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'id', 'name' => 'id', 'title' => trans('admin/services.id'), 'visible' => false]
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
                'searchPlaceholder' => trans('admin/services.search_placeholder'),
                'lengthMenu' => '_MENU_',
                'paginate' => [
                    'first' => '<i class="fa fa-angle-double-left"></i>',
                    'previous' => '<i class="fa fa-angle-left"></i>',
                    'next' => '<i class="fa fa-angle-right"></i>',
                    'last' => '<i class="fa fa-angle-double-right"></i>'
                ]
            ],
            'dom' => '<"row topContainer"<"col-xs-6 col-sm-6 col-md-7 dataTablesHeaderLeft"B><"col-xs-6 col-sm-6 col-md-5 dataTablesHeaderRight"f>><"dataTablesBody"rt><"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
            'drawCallback' => 'function() { $(\'#services [data-toggle="tooltip"]\').tooltip(\'hide\'); $(\'#services [title]\').tooltip({ placement: \'auto\', container: \'body\', selector: \'\', html: true, trigger: \'hover\', delay: {\'show\': 500,\'hide\': 100 } }); }',
            'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
            'searchDelay' => 350,
            'buttons' => [
                [
                    'extend' => 'colvis',
                    'text' => '<i class="fa fa-eye"></i>',
                    'titleAttr' => trans('admin/services.button.column_visibility'),
                    'postfixButtons' => ['colvisRestore'],
                    'className' => 'btn-default btn-sm'
                ], [
                    'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                    'titleAttr' => trans('admin/services.button.reset'),
                    'className' => 'btn-default btn-sm'
                ], [
                    'extend' => 'reload',
                    'text' => '<i class="fa fa-refresh"></i>',
                    'titleAttr' => trans('admin/services.button.reload'),
                    'className' => 'btn-default btn-sm'
                ]
            ]
        ];

        if (auth()->user()->hasPermission('admin.services.create')) {
            $parameters['buttons'][] = [
                'text' => '<div onclick="window.location.href=\''. route('admin.services.create') .'\';"><i class="fa fa-plus"></i> <span class="hidden-xs">'. trans('admin/services.button.create_new') .'</span></div>',
                'titleAttr' => trans('admin/services.button.create_new'),
                'className' => 'btn-success btn-sm buttons-new'
            ];
        }

        $ajax = [
            'url' => route('admin.services.datatables'),
            'type' => 'POST',
            'headers' => [
                'X-CSRF-TOKEN' => csrf_token()
            ],
            // 'data' => json_encode([])
        ];

        $builder->columns($columns)->parameters($parameters)->ajax($ajax);

        return view('admin.services.index', compact('builder'));
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermission('admin.services.show')) {
            return redirect_no_permission();
        }

        $service = Service::findOrFail($id);

        return view('admin.services.show', compact('service'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('admin.services.create')) {
            return redirect_no_permission();
        }

        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.services.create')) {
            return redirect_no_permission();
        }

        $rules = [
            'name' => 'required|max:255',
            'type' => 'required',
            'availability' => 'numeric',
            'hide_location' => 'numeric',
            'duration' => 'numeric',
            'duration_min' => 'numeric',
            'duration_max' => 'numeric',
            'factor_type' => 'required',
            'factor_value' => 'numeric',
            'is_featured' => 'numeric',
            'order' => 'numeric',
            'status' => 'required',
        ];

        $this->validate($request, $rules);

        if ($request->get('is_featured')) {
            Service::where('relation_type', 'site')
              ->where('relation_id', config('site.site_id'))
              ->where('is_featured', 1)
              ->update(['is_featured' => 0]);
        }

        $params = [
            'availability' => $request->get('availability') ? 1 : 0,
            'hide_location' => $request->get('hide_location') ? 1 : 0,
            'duration' => $request->get('duration') ? 1 : 0,
            'duration_min' => $request->get('duration_min', 0),
            'duration_max' => $request->get('duration_max', 0),
            'factor_type' => $request->get('factor_type', 'addition'),
            'factor_value' => $request->get('factor_value', 0),
        ];

        if ($request->get('type') == 'scheduled') {
            $params = array_merge($params, [
                'availability' => 0,
                'hide_location' => 0,
                'duration' => 0,
                'duration_min' => 0,
                'duration_max' => 0,
                'factor_type' => 'addition',
                'factor_value' => 0,
            ]);
        }

        Service::create([
            'relation_type' => 'site',
            'relation_id' => config('site.site_id'),
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'type' => $request->get('type', 'standard'),
            'params' => json_encode($params),
            'is_featured' => $request->get('is_featured') ? 1 : 0,
            'order' => $request->get('order', 0),
            'status' => $request->get('status', 'active'),
        ]);

        session()->flash('message', trans('admin/services.message.store_success'));

        return redirect()->route('admin.services.index');
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermission('admin.services.edit')) {
            return redirect_no_permission();
        }

        $service = Service::findOrFail($id);

        $service->params = $service->getParams('raw');

        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('admin.services.edit')) {
            return redirect_no_permission();
        }

        $service = Service::findOrFail($id);

        $rules = [
            'name' => 'required|max:255',
            'type' => 'required',
            'availability' => 'numeric',
            'hide_location' => 'numeric',
            'duration' => 'numeric',
            'duration_min' => 'numeric',
            'duration_max' => 'numeric',
            'factor_type' => 'required',
            'factor_value' => 'numeric',
            'is_featured' => 'numeric',
            'order' => 'numeric',
            'status' => 'required',
        ];

        $this->validate($request, $rules);

        if ($request->get('is_featured')) {
            Service::where('relation_type', 'site')
              ->where('relation_id', config('site.site_id'))
              ->where('is_featured', 1)
              ->where('id', '!=', $service->id)
              ->update(['is_featured' => 0]);
        }

        $params = [
            'availability' => $request->get('availability') ? 1 : 0,
            'hide_location' => $request->get('hide_location') ? 1 : 0,
            'duration' => $request->get('duration') ? 1 : 0,
            'duration_min' => $request->get('duration_min', 0),
            'duration_max' => $request->get('duration_max', 0),
            'factor_type' => $request->get('factor_type', 'addition'),
            'factor_value' => $request->get('factor_value', 0),
        ];

        if ($request->get('type') == 'scheduled') {
            $params = array_merge($params, [
                'availability' => 0,
                'hide_location' => 0,
                'duration' => 0,
                'duration_min' => 0,
                'duration_max' => 0,
                'factor_type' => 'addition',
                'factor_value' => 0,
            ]);
        }

        $service->update([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'type' => $request->get('type', 'standard'),
            'params' => json_encode($params),
            'is_featured' => $request->get('is_featured') ? 1 : 0,
            'order' => $request->get('order', 0),
            'status' => $request->get('status', 'active'),
        ]);

        session()->flash('message', trans('admin/services.message.update_success'));

        return redirect()->back();
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('admin.services.destroy')) {
            return redirect_no_permission();
        }

        $service = Service::findOrFail($id);

        $service->delete();

        session()->flash('message', trans('admin/services.message.destroy_success'));

        if (url()->previous() != url()->full()) {
            return redirect()->back();
        }
        else {
            return redirect()->route('admin.services.index');
        }
    }

    public function featured($id, $featured)
    {
        if (!auth()->user()->hasPermission('admin.services.edit')) {
            return redirect_no_permission();
        }

        $service = Service::findOrFail($id);

        if ($featured == 'yes') {
            Service::where('relation_type', 'site')
              ->where('relation_id', config('site.site_id'))
              ->where('is_featured', 1)
              ->where('id', '!=', $service->id)
              ->update(['is_featured' => 0]);
        }

        $service->update([
            'is_featured' => $featured == 'yes' ? 1 : 0,
        ]);

        return redirect()->back();
    }

    public function status($id, $status)
    {
        if (!auth()->user()->hasPermission('admin.services.edit')) {
            return redirect_no_permission();
        }

        $service = Service::findOrFail($id);

        if (in_array($status, ['active', 'inactive'])) {
            $service->update([
                'status' => $status,
            ]);
        }

        return redirect()->back();
    }
}
