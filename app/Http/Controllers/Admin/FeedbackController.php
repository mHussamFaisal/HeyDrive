<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Helpers\SiteHelper;
use Yajra\Datatables\Html\Builder;
use Datatables;
use Form;
use Validator;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct()
    {
        \App\Helpers\SiteHelper::extendValidatorRules();
    }

    public function datatables(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.feedback.index')) {
            return redirect_no_permission();
        }

        if ($request->ajax()) {
            $model = Feedback::query();

            // $model->where('relation_type', 'site')->where('relation_id', config('site.site_id'));

            if ($request->get('type')) {
                $model->where('type', $request->get('type'));
            }

            if ($request->get('ref_number')) {
                $model->where('ref_number', $request->get('ref_number'));
            }

            $dt = Datatables::eloquent($model)
                ->addColumn('actions', function(Feedback $feedback) {
                    $params = array_merge(['id' => $feedback->id], request('type') ? ['type' => request('type')] : []);

                    $buttons = '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';

                    if (auth()->user()->hasPermission('admin.feedback.show')) {
                        $buttons .= '<a href="' . route('admin.feedback.show', $params) . '" class="btn btn-default btn-sm btnView" data-original-title="' . trans('admin/feedback.button.show') . '">
                            <i class="fa fa-eye"></i>
                        </a>';
                    }
                    if (auth()->user()->hasPermission(['admin.feedback.edit', 'admin.feedback.destroy'])) {
                        $buttons .= '<div class="btn-group pull-left" role="group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                              <span class="fa fa-angle-down"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">';
                        if (auth()->user()->hasPermission('admin.feedback.edit')) {
                            $buttons .= '<li>
                                <a href="' . route('admin.feedback.edit', $params) . '" class="btnEdit" style="padding:3px 8px;" data-original-title="' . trans('admin/feedback.button.edit') . '">
                                  <span style="display:inline-block; width:20px; text-align:center;">
                                    <i class="fa fa-pencil-square-o"></i>
                                  </span>
                                  ' . trans('admin/feedback.button.edit') . '
                                </a>
                              </li>';
                        }
                        if (auth()->user()->hasPermission('admin.feedback.destroy')) {
                            $buttons .= '<li>
                                <a href="#" onclick="$(\'#button_delete_id_'. $feedback->id .'\').click(); return false;" class="btnDelete" style="padding:3px 8px;" data-original-title="' . trans('admin/feedback.button.destroy') . '">
                                  <span style="display:inline-block; width:20px; text-align:center;">
                                    <i class="fa fa-trash"></i>
                                  </span>
                                  ' . trans('admin/feedback.button.destroy') . '
                                </a>
                              </li>';
                        }
                        $buttons .= '</ul>
                          </div>
                        </div>';
                    }

                    $buttons .= Form::open(['method' => 'delete', 'url' => route('admin.feedback.destroy', $params), 'class' => 'form-inline form-delete hide']);
                    $buttons .= Form::button(trans('admin/feedback.button.destroy'), ['type' => 'submit', 'class' => 'delete', 'name' => 'delete_modal', 'id' => 'button_delete_id_'. $feedback->id]);
                    $buttons .= Form::close();

                    return $buttons;
                })
                ->setRowId(function (Feedback $feedback) {
                    return 'feedback_row_'. $feedback->id;
                })
                ->editColumn('name', function(Feedback $feedback) {
                    if (!auth()->user()->hasPermission('admin.feedback.show')) {
                        return $feedback->getName();
                    }
                    return '<a href="'. route('admin.feedback.show', array_merge(['id' => $feedback->id], request('type') ? ['type' => request('type')] : [])) .'" class="text-default" '. (!$feedback->is_read ? 'style="font-weight:bold;"' : '') .'>'. $feedback->getName() .'</a>';
                })
                ->editColumn('description', function(Feedback $feedback) {
                    return SiteHelper::nl2br2($feedback->description);
                })
                ->editColumn('ref_number', function(Feedback $feedback) {
                    return $feedback->getRefNumberLink(['class'=>'text-default']);
                })
                ->editColumn('email', function(Feedback $feedback) {
                    return SiteHelper::mailtoLink($feedback->email, ['class'=>'text-default']);
                })
                ->editColumn('phone', function(Feedback $feedback) {
                    return SiteHelper::telLink($feedback->phone, ['class'=>'text-default']);
                })
                ->editColumn('type', function(Feedback $feedback) {
                    return $feedback->getTypeLink(['class'=>'text-default']);
                })
                ->editColumn('params', function(Feedback $feedback) {
                    return $feedback->getParams();
                })
                ->editColumn('status', function(Feedback $feedback) {
                    if (!auth()->user()->hasPermission('admin.feedback.edit')) {
                        return $feedback->getStatus('label');
                    }
                    return '<a href="'. route('admin.feedback.status', [$feedback->id, $feedback->status == 'active' ? 'inactive' : 'active']) .'" class="text-default">'. $feedback->getStatus('label') .'</a>';
                })
                ->editColumn('created_at', function(Feedback $feedback) {
                    return SiteHelper::formatDateTime($feedback->created_at);
                })
                ->editColumn('updated_at', function(Feedback $feedback) {
                    return SiteHelper::formatDateTime($feedback->updated_at);
                });

            return $dt->make(true);
        }
    }

    public function index(Builder $builder, Request $request)
    {
        if (!auth()->user()->hasPermission('admin.feedback.index')) {
            return redirect_no_permission();
        }

        $columns = [
            ['data' => 'name', 'name' => 'name', 'title' => trans('admin/feedback.name')],
            ['data' => 'description', 'name' => 'description', 'title' => trans('admin/feedback.description'), 'visible' => false],
            ['data' => 'ref_number', 'name' => 'ref_number', 'title' => trans('admin/feedback.ref_number')],
            ['data' => 'email', 'name' => 'email', 'title' => trans('admin/feedback.email')],
            ['data' => 'phone', 'name' => 'phone', 'title' => trans('admin/feedback.phone')],
            ['data' => 'type', 'name' => 'type', 'title' => trans('admin/feedback.type'), 'visible' => false],
            ['data' => 'params', 'name' => 'params', 'title' => trans('admin/feedback.params'), 'orderable' => false, 'searchable' => false, 'visible' => false],
            ['data' => 'order', 'name' => 'order', 'title' => trans('admin/feedback.order'), 'searchable' => false, 'visible' => false],
            ['data' => 'status', 'name' => 'status', 'title' => trans('admin/feedback.status'), 'searchable' => false],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => trans('admin/feedback.updated_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('admin/feedback.created_at'), 'searchable' => false],
            ['data' => 'id', 'name' => 'id', 'title' => trans('admin/feedback.id'), 'visible' => false]
        ];


        if (auth()->user()->hasPermission([
            'admin.feedback.show',
            'admin.feedback.edit',
            'admin.feedback.destroy',
        ])) {
            $columns = array_merge(
                [['data' => 'actions', 'defaultContent' => '', 'name' => 'actions', 'title' => trans('admin/feedback.actions'), 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true]],
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
                [11, 'desc']
            ],
            'pageLength' => 10,
            'lengthMenu' => [5, 10, 25, 50],
            'language' => [
                'search' => '_INPUT_',
                'searchPlaceholder' => trans('admin/feedback.search_placeholder'),
                'lengthMenu' => '_MENU_',
                'paginate' => [
                    'first' => '<i class="fa fa-angle-double-left"></i>',
                    'previous' => '<i class="fa fa-angle-left"></i>',
                    'next' => '<i class="fa fa-angle-right"></i>',
                    'last' => '<i class="fa fa-angle-double-right"></i>'
                ]
            ],
            'dom' => '<"row topContainer"<"col-xs-6 col-sm-6 col-md-7 dataTablesHeaderLeft"B><"col-xs-6 col-sm-6 col-md-5 dataTablesHeaderRight"f>><"dataTablesBody"rt><"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
            'drawCallback' => 'function() { $(\'#feedback [data-toggle="tooltip"]\').tooltip(\'hide\'); $(\'#feedback [title]\').tooltip({ placement: \'auto\', container: \'body\', selector: \'\', html: true, trigger: \'hover\', delay: {\'show\': 500,\'hide\': 100 } }); }',
            'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
            'searchDelay' => 350,
            'buttons' => [
                [
                    'extend' => 'colvis',
                    'text' => '<i class="fa fa-eye"></i>',
                    'titleAttr' => trans('admin/feedback.button.column_visibility'),
                    'postfixButtons' => ['colvisRestore'],
                    'className' => 'btn-default btn-sm'
                ], [
                    'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                    'titleAttr' => trans('admin/feedback.button.reset'),
                    'className' => 'btn-default btn-sm'
                ], [
                    'extend' => 'reload',
                    'text' => '<i class="fa fa-refresh"></i>',
                    'titleAttr' => trans('admin/feedback.button.reload'),
                    'className' => 'btn-default btn-sm'
                ]
            ]
        ];

        if (auth()->user()->hasPermission('admin.feedback.create')) {
            $parameters['buttons'][] = [
                'text' => '<div onclick="window.location.href=\''. route('admin.feedback.create', request('type') ? ['type' => request('type')] : []) .'\';"><i class="fa fa-plus"></i> <span class="hidden-xs">'. trans('admin/feedback.button.create_new') .'</span></div>',
                'titleAttr' => trans('admin/feedback.button.create_new'),
                'className' => 'btn-success btn-sm buttons-new'
            ];
        }

        $ajax = [
            'url' => route('admin.feedback.datatables'),
            'type' => 'POST',
            'headers' => [
                'X-CSRF-TOKEN' => csrf_token()
            ],
            'data' => json_encode([
                'type' => $request->get('type'),
                'ref_number' => $request->get('ref_number')
            ])
        ];

        $builder->columns($columns)->parameters($parameters)->ajax($ajax);

        return view('admin.feedback.index', compact('builder'));
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermission('admin.feedback.show')) {
            return redirect_no_permission();
        }

        $feedback = Feedback::findOrFail($id);

        $feedback->update(['is_read' => 1]);

        return view('admin.feedback.show', compact('feedback'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('admin.feedback.create')) {
            return redirect_no_permission();
        }

        return view('admin.feedback.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.feedback.create')) {
            return redirect_no_permission();
        }

        $errors = [];
        $rules = [
            'name' => 'required|max:255',
            'email' => [
                'email',
                'max:255',
            ],
            'description' => 'required',
            'type' => 'required',
            'order' => 'numeric',
            'status' => 'required',
        ];

        $this->validate($request, $rules);

        $params = [
            // 'param' => $request->get('param', ''),
        ];

        $feedback = Feedback::create([
            'relation_type' => 'site',
            'relation_id' => config('site.site_id'),
            'type' => $request->get('type', 'comment'),
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'ref_number' => $request->get('ref_number'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'params' => json_encode($params),
            'is_read' => 1,
            'order' => $request->get('order', 0),
            'status' => $request->get('status', 'active'),
        ]);

        // Files
        $filesList = (object)$request->get('files');
        if ( !empty($filesList) ) {
            foreach($filesList as $key => $value) {
                $value = (object)$value;

                $value->name = trim($value->name);

                if ( $value->id > 0 ) {
                    $query = \DB::table('file')
                        ->where('file_relation_type', 'feedback')
                        ->where('file_relation_id', $feedback->id)
                        ->where('file_id', $value->id)
                        ->first();

                    if ( !empty($query) ) {
                        if ( $value->delete > 0 ) {
                            if ( \Storage::disk('safe')->exists($query->file_path) ) {
                                \Storage::disk('safe')->delete($query->file_path);
                            }
                            \DB::table('file')->where('file_id', $query->file_id)->delete();
                        }
                        else {
                            \DB::table('file')->where('file_id', $query->file_id)->update(['file_name' => $value->name]);
                        }
                    }
                }
                else {
                    if ( isset($request->file('files')[$key]['file']) ) {
                        $file = $request->file('files')[$key]['file'];

                        $files = [
                            'file' => $file
                        ];

                        $rules = [
                            'file' => 'required|file_extension:'. config('eto.allowed_file_extensions')
                        ];

                        $validator = Validator::make($files, $rules);

                        if ( $validator->fails() ) {
                            $errors = array_merge($errors, $validator->errors()->all());
                        }
                        else {
                            $originalName = $file->getClientOriginalName();
                            $extension = $file->getClientOriginalExtension();
                            $newName = \App\Helpers\SiteHelper::generateFilename('feedback') .'.'. $extension;

                            $realPath = $file->getRealPath();
                            $size = $file->getSize();
                            $mimeType = $file->getMimeType();
                            $params['files'][] = [$realPath, $size, $mimeType, $newName, $extension];

                            $file->move(asset_path('uploads','safe'), $newName);

                            \DB::table('file')->insertGetId([
                                'file_name' => $value->name,
                                'file_path' => $newName,
                                'file_site_id' => 0,
                                'file_description' => $originalName,
                                'file_relation_type' => 'feedback',
                                'file_relation_id' => $feedback->id,
                                'file_free_download' => 0,
                                'file_ordering' => 0,
                                'file_limit' => 0
                            ]);
                        }
                    }
                }
            }
        }

        if (request('type')) {
           $params['type'] = request('type');
        }

        if (isset($errors)) {
            $params['errors'] = $errors;
        }

        if (request('tmpl') == 'body') {
            $params['tmpl'] = request('tmpl');

            if (request('ref_number')) {
               $params['ref_number'] = request('ref_number');
            }

            $redirect = redirect()->route('admin.feedback.create', $params);
        }
        else {
            $redirect = redirect()->route('admin.feedback.index', $params);
        }

        if (!empty($errors)) {
            session()->flash('message', trans('admin/feedback.message.store_success_with_errors'));
            $redirect->withErrors($errors);
        }
        else {
            session()->flash('message', trans('admin/feedback.message.store_success'));
        }

        return $redirect;
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermission('admin.feedback.edit')) {
            return redirect_no_permission();
        }

        $feedback = Feedback::findOrFail($id);

        $feedback->params = $feedback->getParams('raw');

        return view('admin.feedback.edit', compact('feedback'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('admin.feedback.edit')) {
            return redirect_no_permission();
        }

        $errors = [];
        $feedback = Feedback::findOrFail($id);

        $rules = [
            'name' => 'required|max:255',
            'email' => [
                'email',
                'max:255',
            ],
            'description' => 'required',
            'type' => 'required',
            'order' => 'numeric',
            'status' => 'required',
        ];

        $this->validate($request, $rules);

        $params = [
            // 'param' => $request->get('param', ''),
        ];

        $feedback->update([
            'type' => $request->get('type', 'comment'),
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'ref_number' => $request->get('ref_number'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'params' => json_encode($params),
            'is_read' => 1,
            'order' => $request->get('order', 0),
            'status' => $request->get('status', 'active'),
        ]);

        // Files
        $filesList = (object)$request->get('files');
        if ( !empty($filesList) ) {
            foreach($filesList as $key => $value) {
                $value = (object)$value;
                $value->name = trim($value->name);

                if ( $value->id > 0 ) {
                    $query = \DB::table('file')
                        ->where('file_relation_type', 'feedback')
                        ->where('file_relation_id', $feedback->id)
                        ->where('file_id', $value->id)
                        ->first();

                    if ( !empty($query) ) {
                        if ( $value->delete > 0 ) {
                            if ( \Storage::disk('safe')->exists($query->file_path) ) {
                                \Storage::disk('safe')->delete($query->file_path);
                            }
                            \DB::table('file')->where('file_id', $query->file_id)->delete();
                        }
                        else {
                            \DB::table('file')->where('file_id', $query->file_id)->update(['file_name' => $value->name]);
                        }
                    }
                }
                else {
                    if ( isset($request->file('files')[$key]['file']) )
                    {
                        $file = $request->file('files')[$key]['file'];

                        $files = [
                            'file' => $file
                        ];

                        $rules = [
                            'file' => 'required|file_extension:'. config('eto.allowed_file_extensions')
                        ];

                        $validator = Validator::make($files, $rules);

                        if ( $validator->fails() ) {
                            $errors = array_merge($errors, $validator->errors()->all());
                        }
                        else {
                            $originalName = $file->getClientOriginalName();
                            $extension = $file->getClientOriginalExtension();
                            $newName = \App\Helpers\SiteHelper::generateFilename('feedback') .'.'. $extension;

                            $file->move(asset_path('uploads','safe'), $newName);

                            \DB::table('file')->insertGetId([
                                'file_name' => $value->name,
                                'file_path' => $newName,
                                'file_site_id' => 0,
                                'file_description' => $originalName,
                                'file_relation_type' => 'feedback',
                                'file_relation_id' => $feedback->id,
                                'file_free_download' => 0,
                                'file_ordering' => 0,
                                'file_limit' => 0
                            ]);
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            session()->flash('message', trans('admin/feedback.message.update_success_with_errors'));
            $redirect = redirect()->back()->withErrors($errors);
        }
        else {
            session()->flash('message', trans('admin/feedback.message.update_success'));
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('admin.feedback.destroy')) {
            return redirect_no_permission();
        }

        $feedback = Feedback::findOrFail($id);
        $feedback->deleteFiles();
        $feedback->delete();

        session()->flash('message', trans('admin/feedback.message.destroy_success'));

        if (url()->previous() != url()->full()) {
            return redirect()->back();
        }
        else {
            return redirect()->route('admin.feedback.index', request('type') ? ['type' => request('type')] : []);
        }
    }

    public function download($id)
    {
        $query = \DB::table('file')
            ->where('file_relation_type', 'feedback')
            // ->where('file_relation_id', $user->id)
            ->where('file_id', $id)
            ->first();

        if ( !empty($query) ) {
            $filePath = asset_path('uploads','safe/'. $query->file_path);
            return response()->download($filePath, $query->file_path);
        }
        return;
    }

    public function status($id, $status)
    {
        if (!auth()->user()->hasPermission('admin.feedback.edit')) {
            return redirect_no_permission();
        }

        $feedback = Feedback::findOrFail($id);

        if (in_array($status, ['active', 'inactive'])) {
            $feedback->update([
                'status' => $status,
            ]);
        }
        return redirect()->back();
    }
}
