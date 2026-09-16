<?php

namespace App\Http\Controllers\Report;

use App\Helpers\ReportHelper;
use App\Helpers\SiteHelper;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Yajra\Datatables\Html\Builder;
use Datatables;
use Form;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Builder $builder
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Builder $builder, Request $request)
    {
        dd($request);
        $type = $request->get('type');

        if (!auth()->user()->hasPermission('admin.reports.index') || ($type == 'fleet' && !config('eto.allow_fleet_operator'))) {
            return redirect_no_permission();
        }

        if ($request->ajax()) {
            $dt = Datatables::eloquent(Report::getReports($request->is('reports/list_trash')))
                ->addColumn('actions', function(Report $report) use ($request) {
                    $buttons = '<div class="btn-group" role="group" aria-label="..." >';
                    if ($report->deleted_at) {
                        if (auth()->user()->hasPermission('admin.reports.show')) {
                            $buttons .= '<button data-eto-action="' . route('reports.trashShow', $report->id) . '" onclick="ETO.Report.showReport(this); return false;" class="btn btn-default btn-sm btnViewOnReport" data-report-type="' . $report->type . '" title="' . trans('reports.button.show') . '">
                                <i class="fa fa-eye"></i>
                            </button>';
                        }
                        if (auth()->user()->hasPermission('admin.reports.trash')) {
                            $buttons .= '<button class="btn btn-default btn-sm eto-btn-destroy-report" data-report-id="' . $report->id . '" title="' . trans('reports.button.destroy') . '">
                                <i class="fa fa-trash"></i>
                            </button>';
                        }
                        if (auth()->user()->hasPermission('admin.reports.sms')) {
                            $buttons .= '<button class="btn btn-default btn-sm eto-btn-restore-report" data-report-id="' . $report->id . '" title="' . trans('reports.button.restore') . '">
                                <i class="fa fa-mail-reply"></i>
                            </button>';
                        }
                    }
                    else {
                        if (auth()->user()->hasPermission('admin.reports.show')) {
                            $buttons .= '<button data-eto-action="' . route('reports.show', $report->id) . '" onclick="ETO.Report.showReport(this); return false;" class="btn btn-default btn-sm btnViewOnReport" data-report-type="' . $report->type . '" title="' . trans('reports.button.show') . '">
                                <i class="fa fa-eye"></i>
                            </button>';
                        }
                        if (auth()->user()->hasPermission('admin.reports.destroy')) {
                            $buttons .= '<button class="btn btn-default btn-sm eto-btn-trash-report" data-report-id="' . $report->id . '" title="' . trans('reports.button.trash') . '">
                                <i class="fa fa-trash"></i>
                            </button>';
                        }
                    }
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->setRowId(function (Report $report) {
                    return 'report_row_'. $report->id;
                })
                ->editColumn('type', function(Report $report) {
                    return '<a href="'. route('reports.index') .'?type='.$report->type.'" class="text-default eto-refresh-page">'. trans('admin/index.menu.reports.'.$report->type) .'</a>';
                })
                ->editColumn('name', function(Report $report){
                    if (empty($report->name)) {
                         $values = [];
                         $reportData = $this->show(request(), $report->id, true);
                         $i = 1;
                         if ($report->type == 'driver' && !empty($reportData->drivers)) {
                             foreach ($reportData->drivers as $id=>$driver) {
                                 $values['drivers'][] = $driver->name;
                                 $i++;
                                 if ($i > 3) {
                                     break;
                                 }
                             }
                             $values['drivers'] = implode(', ', $values['drivers']) . '...';
                         }
                         if ($report->type == 'payment' && !empty($reportData->payments)) {
                             foreach ($reportData->payments as $id=>$payment) {
                                 $values['payment'][] = $payment->name;
                                 $i++;
                                 if ($i > 3) {
                                     break;
                                 }
                             }
                             $values['payment'] = implode(', ', $values['payment']) . '...';
                         }

                         if (!empty($values)) {
                             $report->name = trans('reports.' . $report->type . '_report_for', $values);
                         } else {
                             $report->name = trans('reports.file_deleted');
                         }
                    }
                    if ($report->deleted_at) {
                        return '<button data-eto-action="' . route('reports.trashShow', $report->id) . '" onclick="ETO.Report.showReport(this); return false;" class="btn-link btnViewOnReport" data-report-type="' . $report->type . '">
                            ' . $report->name . '
                        </button>';
                    }
                    else {
                        return '<button data-eto-action="' . route('reports.show', $report->id) . '" onclick="ETO.Report.showReport(this); return false;" class="btn-link btnViewOnReport" data-report-type="' . $report->type . '">
                            ' . $report->name . '
                        </button>';
                    }
                })
                ->editColumn('from_date', function(Report $report) {
                    return format_date_time($report->from_date, 'date');
                })
                ->editColumn('to_date', function(Report $report) {
                    return format_date_time($report->to_date, 'date');
                })
                ->editColumn('created_at', function(Report $report) {
                    return format_date_time($report->created_at);
                })
                ->editColumn('deleted_at', function(Report $report) {
                    return format_date_time($report->deleted_at);
                });

            if ($request->is('reports/list_trash')) {
                $dt->withTrashed();
            }

            return $dt->make(true);
        }
        else {
            $columns = [
                ['data' => 'name', 'name' => 'name', 'title' => trans('reports.columns.name'), 'render' => null, 'orderable' => true, 'searchable' => true, 'exportable' => true, 'printable' => true],
                ['data' => 'type', 'name' => 'type', 'title' => trans('reports.columns.type'), 'render' => null, 'visible' => false, 'orderable' => true, 'searchable' => true, 'exportable' => true, 'printable' => true],
                ['data' => 'from_date', 'name' => 'from_date', 'title' => trans('reports.columns.from_date')],
                ['data' => 'to_date', 'name' => 'to_date', 'title' => trans('reports.columns.to_date')],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('reports.columns.created_at'), 'render' => null, 'visible' => false, 'orderable' => true, 'searchable' => true, 'exportable' => true, 'printable' => true],
            ];

            if (auth()->user()->hasPermission(['admin.reports.show','admin.reports.trash','admin.reports.sms','admin.reports.destroy'])
            ) {
                $columns = array_merge(
                    [['data' => 'actions', 'name' => 'actions', 'title' => trans('reports.columns.actions'), 'defaultContent' => '', 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true]],
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
                    [1, 'asc'],
                    [2, 'desc'],
                    [3, 'desc'],
                    [4, 'desc'],
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
                // 'initComplete' => 'function() { /*$(".dataTables_length select").select2({minimumResultsForSearch: 5});*/ }',
                // 'initComplete' => 'function() { handlePhoneNumber(); }',
                'drawCallback' => 'function() { $(\'#users [data-toggle="tooltip"]\').tooltip(\'hide\'); $(\'#users [title]\').tooltip({ placement: \'auto\', container: \'body\', selector: \'\', html: true, trigger: \'hover\', delay: {\'show\': 500,\'hide\': 100 } }); }',
                'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
                'searchDelay' => 500,
                'buttons' => [
                    [
                        'extend' => 'colvis',
                        'collectionLayout' => 'two-column',
                        'text' => '<i class="fa fa-eye"></i>',
                        'titleAttr' => trans('admin/users.button.column_visibility'),
                        'postfixButtons' => ['colvisRestore'],
                        'className' => 'btn-default btn-sm'
                    ],
                    [
                        'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                        'titleAttr' => trans('admin/users.button.reset'),
                        'className' => 'btn-default btn-sm',
                    ],
                    [
                        'extend' => 'reload',
                        'text' => '<i class="fa fa-refresh"></i>',
                        'titleAttr' => trans('admin/users.button.reload'),
                        'className' => 'btn-default btn-sm'
                    ]
                ]
            ];

            $params = [];
            $routeAjax = route('reports.listJson');

            if (!empty($request->get('type')) || $request->is('reports/trash')) {
                if (!empty($type)) {
                    $params['type'] = $type;
                }

                if ($request->is('reports/trash')) {
                    $columns[] = ['data' => 'deleted_at', 'name' => 'deleted_at', 'title' => trans('reports.columns.deleted_at'), 'render' => null, 'visible' => false, 'orderable' => true, 'searchable' => true, 'exportable' => true, 'printable' => true];
                    $routeAjax = route('reports.listTrash');
                }
            }

            if (!empty($type)) {
                $routeAjax .= '?type=' . $type;
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

            return view('reports.index', compact('builder', 'type'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param bool $type
     * @return Report|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function create($type = false)
    {
        if (!auth()->user()->hasPermission('admin.reports.create') || ($type == 'fleet' && !config('eto.allow_fleet_operator'))) {
            return redirect_no_permission();
        }

        $request = request();

        if ($request->ajax()) {
            try {
                 $report = new Report();
                 return $report->renderReports($type);
            }
            catch (\Exception $e) {
                return response()->json([], 200);
            }
        }
        else {
            $services = ReportHelper::getServicesList($request->services ?: []);
            $sources = ReportHelper::getSourcesList($request->sources ?: []);
            $statuses = ReportHelper::getStatusesList($request->statuses ?: ['completed']);
            $paymentMethods = ReportHelper::getPaymentMethodsList($request->payment_methods ?: []);
            $paymentStatus = ReportHelper::getPaymentStatusList($request->payment_status ?: []);
            $drivers = ReportHelper::getDriverList($request->drivers ?: []);
            $customers = ReportHelper::getCustomerList($request->customers ?: []);
            $dateTypes = ReportHelper::getDateTypesList($request->date_types ?: 'date');
            $fleets = ReportHelper::getFleetList($request->fleets ?: []);
            return view('reports.create', compact(
                'drivers',
                'customers',
                'services',
                'sources',
                'statuses',
                'paymentMethods',
                'paymentStatus',
                'dateTypes',
                'type',
                'fleets'
            ));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.reports.create')) {
            return redirect_no_permission();
        }

        $data = json_decode($request->data, true);

        if (empty($data['type'])) {
            abort(404);
        }

        $filters = $data['filters'];
        unset($filters['from_date']);
        unset($filters['to_date']);
        unset($filters['_token']);
        unset($filters['type']);

        $report = new Report();
        $report->type = $data['type'];
        $report->from_date = !empty($data['from_date']) ? $data['from_date'] : null;
        $report->to_date = !empty($data['to_date']) ? $data['to_date'] : null;
        $report->name = !empty($data['name']) ? $data['name'] : null;
        $report->filters = json_encode($filters);
        $report->save();

        $data['created_at'] = $report->created_at;
        unset($data['unassignedList']);

        if (!empty($data['drivers'])) {
            foreach($data['drivers'] as $idDriver=>$driver) {
                unset($driver['bookings']);
                unset($driver['cash']);
                unset($driver['commission']);
                $data['drivers'][$idDriver] = $driver;
            }
        }
        elseif (!empty($data['fleets'])) {
            foreach($data['fleets'] as $idFleet=>$fleet) {
                unset($fleet['bookings']);
                unset($fleet['commission']);
                $data['fleets'][$idFleet] = $fleet;
            }
        }

        \Storage::disk('archive')->put('reports' . DIRECTORY_SEPARATOR . $report->id . '.json', json_encode($data));

        return response()->json($report, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $id
     * @param bool $getObject
     * @param bool $relationId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show(Request $request, $id, $getObject = false, $relationId = false)
    {
        if (!auth()->user()->hasPermission('admin.reports.show')) {
            return redirect_no_permission();
        }

        $report = Report::getReports($request->is('reports/trash/show/*'), $id)->withTrashed()->first();

        if ($report) {
            $report->renderReports($report->type, $relationId);

            if ($getObject) {
                return json_decode(json_encode($report->toArray()));
            }
            elseif ( $request->ajax() ) {
                return response()->json($report, 200);
            }

            return view('reports.show', compact('report'));
        }

        return response()->json([], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function trash($id)
    {
        if (!auth()->user()->hasPermission('admin.reports.trash')) {
            return redirect_no_permission();
        }

        $report = Report::find($id);
        $report->delete();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function restore($id)
    {
        if (!auth()->user()->hasPermission('admin.reports.restore')) {
            return redirect_no_permission();
        }

        $report = Report::onlyTrashed()->find($id);
        if (!is_null($report)) {
            $report->restore();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('admin.reports.destroy')) {
            return redirect_no_permission();
        }

        if ($report = Report::withTrashed()->find($id)) {
            \Storage::disk('archive')->delete('reports' . DIRECTORY_SEPARATOR . $report->id . '.json');
            $report->forceDelete();
        }
    }

    /**
     * @param bool $report
     * @param bool $userId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendReport($report = false, $userId = false)
    {
        if (!auth()->user()->hasPermission('admin.reports.send')) {
            return redirect_no_permission();
        }

        $routeName = \Route::currentRouteName();

        if ($routeName == 'reports.sendReport' || $routeName == 'reports.sendReportToAll') {
            $userId = $report;
            $reportData = json_decode(request('data'));
        } else {
            $reportData = $this->show(request(), $report, true, $userId);
        }

        $subject = trans('reports.titles.driver') . ' ' . format_date_time($reportData->from_date, 'date')
            . ' - ' . format_date_time($reportData->to_date, 'date');
        $type = $reportData->type;

        if ($type == 'driver' && !empty($reportData->drivers)) {
            foreach ($reportData->drivers as $id => $driver) {
                if (!$userId || ($userId && (int)$id === (int)$userId)) {
                    $data = [
                        'subject' => $subject,
                        'driver' => $driver,
                        'version' => $reportData->version,
                        'bookings' => $reportData->bookings,
                    ];

                    if ($reportData->version > 1) {
                        if (config('eto_report.email.company_take') === true) {
                            $data['payments'] = $reportData->payments;
                            $data['statusColors'] = $reportData->status_colors;
                        }
                    }

                    $reportData->drivers->$id = $this->sendMail($data, $driver, $type);
                }
            }
        }

        if ($type == 'fleet' && !empty($reportData->fleets)) {
            foreach ($reportData->fleets as $id => $fleet) {
                if (!$userId || ($userId && (int)$id === (int)$userId)) {
                    $data = [
                        'subject' => $subject,
                        'fleet' => $fleet,
                        'bookings' => $reportData->bookings,
                    ];

                    if (config('eto_report.email.company_take') === true) {
                        $data['payments'] = $reportData->payments;
                        $data['statusColors'] = $reportData->status_colors;
                    }

                    $reportData->fleets->$id = $this->sendMail($data, $fleet, $type);
                }
            }
        }

        if (!empty($reportData->id)) {
            $reportData = (array)$reportData;
            unset($reportData['id']);
            unset($reportData['id']);

            \Storage::disk('archive')->put('reports' . DIRECTORY_SEPARATOR . $report . '.json', json_encode($reportData));
        }
    }

    /**
     * @param $data
     * @param $driver
     * @return array|object
     */
    private function sendMail($data, $driver, $type = 'driver') {
        $data['company'] = (object)[
            'name' => config('site.company_name'),
            'phone' => config('site.company_telephone'),
            'email' => config('site.company_email'),
            'address' => SiteHelper::nl2br2(config('site.company_address')),
            'url_home' => config('site.url_home'),
            'url_feedback' =>config('site.url_feedback'),
            'url_contact' => config('site.url_contact'),
            'url_booking' => config('site.url_booking'),
            'url_customer' => config('site.url_customer')
        ];
        $subject = $data['subject'];

        if (!empty($driver->email)) {
            try {
                \Mail::send([
                    'html' => 'emails.'.$type.'_report',
                ], $data,
                    function ($message) use ($driver, $subject) {
                        $message->from(config('site.company_email'), config('site.company_name'))
                            ->to($driver->email, $driver->name)
                            ->subject($subject);
                    }
                );
                $sent = true;
            }
            catch (\Exception $e) {
                \Log::info([$driver->email, $driver->name, $e->getMessage()]);
                $sent = false;
            }

            if ($sent) {
                $driver->is_notified = 1;
            }

            $driver = (array)$driver;
            unset($driver['bookings']);
            unset($driver['cash']);
            unset($driver['commission']);
            $driver = (object)$driver;
        }

        return $driver;
    }
}
