<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Spatie\Activitylog\Models\Activity;
use Datatables;
use Yajra\Datatables\Html\Builder;

class ActivityController extends Controller
{
    protected $routes = [
        'booking' => 'admin/bookings/',
        '' => '#'
    ];
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->hasPermission('admin.activity.index')) {
            return redirect_no_permission();
        }

        return view('activity.index');
    }

    public function search(Request $request) {
        $items = Activity::select('*');

        if ($request->subject) {
            $items->where('subject_type', $request->subject);
        }
        if ($request->subject_id) {
            $items->where('subject_id', $request->subject_id);
        }
        if ($request->causer) {
            $items->where('causer_type', $request->causer);
        }
        if ($request->causer_id) {
            $items->where('causer_id', $request->causer_id);
        }

        if (!empty($request->search['value'])) {
            $items->where(function ($query) use ($request) {
                $search = $request->search['value'];
                $query->where(function ($query) use ($search) {
                    $query->where('log_name', 'like', '%' . $search . '%');
                    $query->orWhere('description', 'like', '%' . $search . '%');
                    $query->orWhere('properties', 'like', '%' . $search . '%');
                    $query->orWhere('created_at', 'like', '%' . $search . '%');
                });
            });
        }

        $limit = $request->length ?: 10;
        $offset = $request->start ?: 0;

        if ($request->order) {
            foreach ($request->order as $order) {
                $dir = isset($order['dir']) ? $order['dir'] : 'desc';
                $column = isset($order['column']) ? $order['column'] : 'created_at';
                $items->orderBy($column, $dir);
            }
        }

        $total = clone $items;
        $recordsFiltered = $total->get()->count();
        $items->offset($offset)->limit($limit);
        $data = $items->get();

        foreach ($data as $id=>$item) {
            if ( $item->subject_type == 'App\Models\Booking' ) {
                $item->subject_type = 'booking';
            }
            $data[$id]->slug = !in_array( $item->log_name, ['delete', 'move_to_trash'] )
                ? url($this->routes[$item->subject_type] . $item->subject_id)
                : url('/#');

            $data[$id]->name = trans('activity.'.$item->subject_type.'.'.$item->log_name).' '.$item->description;
            $data[$id]->excerpt = '';

            if ( !empty($item->properties) ) {
                foreach($item->properties as $property=>$value) {
                    if (!empty($value) && $item->log_name == 'update') {
                        $data[$id]->excerpt .= trans('activity.updated')
                            .' '.$property.' '
                            .trans('activity.from').'<b> '.$value['from'].' </b> '
                            .trans('activity.to').'<b> '.$value['to'].' </b><br>';
                    }
                }
            }

            $item->date = $item->created_at->format('Y-m-d');

            $item->properties = [];
        }

        return response()->json(['news'=>$data, 'recordsTotal'=>Activity::all()->count(), 'recordsFiltered'=>$recordsFiltered], 200);
    }

    public function getList(Request $request, $getObject = false) {
        $limit = $request->limit ?: 10;
        $offset = $request->offset ?: 0;
        $activity = Activity::select('*');

        if ($request->subject) {
            $activity->where('subject_type', $request->subject);
        }
        if ($request->subject_id) {
            $activity->where('subject_id', $request->subject_id);
        }
        if ($request->causer) {
            $activity->where('causer_type', $request->causer);
        }
        if ($request->causer_id) {
            $activity->where('causer_id', $request->causer_id);
        }

        $activity->limit($limit);
        $activity->offset($offset);
        $activity->orderBy('created_at', 'desc');
        $activity = $activity->get();

        if ($getObject) {
            return $activity;
        }

        return response()->json($activity);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function index__(Builder $builder, Request $request)
    {
        $columns = [
            [
                'data' => 'log_name',
                'name' => 'log_name',
                'title' => trans('activity.log_name'),
                'exportable' => false,
                'printable' => true
            ], [
                'data' => 'description',
                'name' => 'description',
                'title' => trans('activity.description'),
                'exportable' => false,
                'printable' => true
            ], [
                'data' => 'subject_id',
                'name' => 'subject_id',
                'title' => trans('activity.subject'),
                'exportable' => false,
                'printable' => true
            ], [
                'data' => 'causer_id',
                'name' => 'causer_id',
                'title' => trans('activity.causer'),
                'exportable' => false,
                'printable' => true
            ], [
                'data' => 'properties',
                'name' => 'properties',
                'title' => trans('activity.properties'),
                'exportable' => false,
                'printable' => true,
                'visible' => false
            ], [
                'data' => 'created_at',
                'name' => 'created_at',
                'title' => trans('activity.created_at'),
                'exportable' => false,
                'printable' => true
            ],
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
                [3, 'desc']
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
//            'dom' => '<"row topContainer"<"col-xs-6 col-sm-6 col-md-7 dataTablesHeaderLeft"B><"col-xs-6 col-sm-6 col-md-5 dataTablesHeaderRight"f>><"dataTablesBody"rt><"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
            'dom' => '<"row topContainer"<"col-xs-12 col-sm-12 col-md-12 dataTablesHeaderLeft">
                <"col-xs-12 col-sm-6 col-md-5 dataTablesHeaderRight">><"dataTablesBody"rt>
                <"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p>
                <"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"liB>>',
            // 'initComplete' => 'function() { /*$(".dataTables_length select").select2({minimumResultsForSearch: 5});*/ }',
            // 'initComplete' => 'function() { handlePhoneNumber(); }',
            'drawCallback' => 'function() { $(\'#users [data-toggle="tooltip"]\').tooltip(\'hide\'); $(\'#users [title]\').tooltip({ placement: \'auto\', container: \'body\', selector: \'\', html: true, trigger: \'hover\', delay: {\'show\': 500,\'hide\': 100 } }); }',
            'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
            'searchDelay' => 350,
            'buttons' => [
                [
                    'extend' => 'colvis',
                    'collectionLayout' => 'two-column',
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

        $ajax = [
            'url' => route('activity.list'),
            'type' => 'POST',
            'headers' => [
                'X-CSRF-TOKEN' => csrf_token()
            ],
            'data' => json_encode([
                'role' => $request->get('role')
            ])
        ];

        $builder->columns($columns)->parameters($parameters)->ajax($ajax);

        return view('activity.index', compact('builder'));
    }

    public function list__(Request $request)
    {
        $activities = Activity::select('*');

        $dt = Datatables::eloquent($activities)
            ->setRowId(function (Activity $activity) {
                return 'activity_row_'. $activity->id;
            })
            ->editColumn('subject_id', function(Activity $activity) {
                if (null === $activity->subject_id) {
                    $out = trans('activity.empty');
                }
                else {
                    $out = $activity->subject_type . ': ' . $activity->subject_id;
                }
                return $out;
            })
            ->editColumn('causer_id', function(Activity $activity) {
                if (null === $activity->causer_id) {
                    $out = trans('activity.empty');
                }
                else {
                    $out = $activity->causer_type . ': ' . $activity->causer_id;
                }
                return $out;
            })
            ->editColumn('created_at', function(Activity $activity) {
                return \App\Helpers\SiteHelper::formatDateTime($activity->created_at);
            })
            ->editColumn('properties', function(Activity $activity) {
                ob_start();
                dump($activity->properties->toArray());
                $out = ob_get_clean();
                return $out;
            });

        return $dt->make(true);
    }
}
