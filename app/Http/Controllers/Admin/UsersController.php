<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserParam;
use App\Models\Setting;
use App\Helpers\SiteHelper;
use Datatables;
use Yajra\Datatables\Html\Builder;
use Form;
use Image;
use Storage;
use Carbon\Carbon;
use DB;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function __construct()
    {
        \App\Helpers\SiteHelper::extendValidatorRules();
    }

    public function datatables(Request $request)
    {
        if (!auth()->user()->hasPermission(['admin.users.admin.index', 'admin.users.driver.index', 'admin.users.customer.index'])) {
            return redirect_no_permission();
        }

        if ($request->ajax()) {
            $tnUser = (new \App\Models\User)->getTable();
            $tnUserProfile = (new \App\Models\UserProfile)->getTable();

            $columns = [
                'id', 'user_id', 'title', 'first_name', 'last_name', 'date_of_birth', 'mobile_no', 'telephone_no',
                'emergency_no', 'profile_type', 'company_name', 'company_number', 'company_tax_number', 'address',
                'city', 'postcode', 'state', 'country', 'national_insurance_no',
                'bank_account', 'unique_id', 'commission', 'availability', 'availability_status', 'insurance', 'insurance_expiry_date',
                'driving_licence', 'driving_licence_expiry_date', 'pco_licence', 'pco_licence_expiry_date', 'phv_licence',
                'phv_licence_expiry_date', 'description', 'created_at', 'updated_at'
            ];

            $exclude = [
                'id', 'user_id', 'created_at', 'updated_at'
            ];

            $select = array_merge([$tnUser .'.*'], array_map(function($v) use($tnUserProfile) { return $tnUserProfile .'.'. $v; }, array_diff($columns, $exclude)));

            $with = [
                'profile',
                'roles',
                'usedRoleRel',
                'vehicles',
            ];

            if (config('eto.allow_fleet_operator')) {
                $with[] = 'fleet';
                $with[] = 'fleet.usedRoleRel';
            }

            $model = User::with($with)->select($select)->leftJoin($tnUserProfile, $tnUserProfile .'.user_id', '=', $tnUser .'.id');

            if ($request->get('role')) {
                $model->role($request->get('role') .'.*');
            }

            if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
                $model->where('fleet_id', auth()->user()->id);
            }

            $teamId = (int)$request->get('team');
            if (config('eto.allow_teams') && !empty($teamId)) {
                $model->whereHas('teams', function ($q) use ($teamId) {
                    $q->where('team_id', $teamId);
                });
            }

            $dt = Datatables::eloquent($model)
                ->addColumn('actions', function(User $user) {
                    $links = '';
                    $buttons = '';
                    $showBtn = '';

                    if (auth()->user()->hasPermission(['admin.users.admin.edit', 'admin.users.driver.edit', 'admin.users.customer.edit']) && (auth()->user()->level() > $user->level() || auth()->user()->hasRole('admin.root'))) {
                        $links .= '<li>
                            <a href="' . route('admin.users.edit', $user->id) . '" class="btnEdit" style="padding:3px 8px;" data-original-title="' . trans('admin/users.button.edit') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-pencil-square-o"></i>
                              </span>
                              ' . trans('admin/users.button.edit') . '
                            </a>
                          </li>';
                    }

                    if ($user->hasRole('driver.*') && auth()->user()->hasPermission('admin.bookings.index')) {
                        $links .= '<li>
                            <a href="'. route('admin.bookings.index', ['driver' => $user->id]) .'" class="btnBookings" style="padding:3px 8px;" data-original-title="'. trans('admin/users.button.jobs') .'">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-calendar"></i>
                              </span>
                              '. trans('admin/users.button.jobs') .'
                            </a>
                          </li>';
                    }
                    elseif ($user->hasRole('customer.*') && auth()->user()->hasPermission('admin.bookings.index')) {
                        $links .= '<li>
                            <a href="'. route('admin.bookings.index', ['user' => $user->id]) .'" class="btnBookings" style="padding:3px 8px;" data-original-title="'. trans('admin/users.button.bookings') .'">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-calendar"></i>
                              </span>
                              '. trans('admin/users.button.bookings') .'
                            </a>
                          </li>';
                    }

                    if ($user->vehicles->count() && auth()->user()->hasPermission('admin.vehicles.index')) {
                        $links .= '<li>
                            <a href="'. route('admin.vehicles.index', ['user' => $user->id]) .'" class="btnVehicles" style="padding:3px 8px;" data-original-title="'. trans('admin/users.button.vehicles') .'">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-car"></i>
                              </span>
                              '. trans('admin/users.button.vehicles') .'
                            </a>
                          </li>';
                    }

                    if (auth()->user()->hasPermission(['admin.users.admin.destroy', 'admin.users.driver.destroy', 'admin.users.customer.destroy']) && (auth()->user()->level() > $user->level() || auth()->user()->hasRole('admin.root'))) {
                        $links .= '<li>
                            <a href="#" onclick="$(\'#button_delete_id_' . $user->id . '\').click(); return false;" class="btnDelete" style="padding:3px 8px;" data-original-title="' . trans('admin/users.button.destroy') . '">
                              <span style="display:inline-block; width:20px; text-align:center;">
                                <i class="fa fa-trash"></i>
                              </span>
                              ' . trans('admin/users.button.destroy') . '
                            </a>
                          </li>';

                        $buttons .= Form::open(['method' => 'delete', 'route' => ['admin.users.destroy', $user->id], 'class' => 'form-inline form-delete hide']);
                        $buttons .= Form::button(trans('admin/users.button.destroy'), ['type' => 'submit', 'class' => 'delete', 'name' => 'delete_modal', 'id' => 'button_delete_id_'. $user->id]);
                        $buttons .= Form::close();
                    }

                    if (auth()->user()->hasPermission(['admin.users.admin.show', 'admin.users.driver.show', 'admin.users.customer.show'])) {
                        $showBtn = '<a href="'. route('admin.users.show', $user->id) .'" class="btn btn-default btn-sm btnView" data-original-title="'. trans('admin/users.button.show') .'">
                          <i class="fa fa-eye"></i>
                        </a>';
                    }

                    if ($buttons || $showBtn || $links) {
                        $buttons .= '<div class="btn-group" role="group" aria-label="..." style="width:70px;">';
                        $buttons .= $showBtn;

                        if ($links) {
                            $buttons .= '<div class="btn-group pull-left" role="group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                  <span class="fa fa-angle-down"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">'. $links .'</ul>
                              </div>';
                        }

                        $buttons .= '</div>';
                    }

                    return $buttons;
                })
                ->setRowId(function (User $user) {
                    return 'user_row_'. $user->id;
                })
                ->editColumn('name', function(User $user) {
                    if (auth()->user()->hasPermission(['admin.users.admin.show', 'admin.users.driver.show', 'admin.users.customer.show'])) {
                        return '<a href="'. route('admin.users.show', $user->id) . '" class="text-default">' . $user->getName(true) .'</a>';
                    }
                    return $user->getName(true);
                })
                ->editColumn('email', function(User $user) {
                    return $user->getEmailLink(['class'=>'text-default']);
                })
                ->editColumn('avatar', function(User $user) {
                    if ($user->isOnline()) {
                        $title = trans('admin/users.online');
                        $class = 'is-online-avatar';
                    }
                    else {
                        $title = trans('admin/users.offline');
                        $class = '';
                    }
                    $title .= ' - '. trans('admin/users.last_seen_at') .': '. (!empty($user->last_seen_at) ? SiteHelper::formatDateTime($user->last_seen_at) : trans('admin/users.last_seen_never'));

                    return '<img src="'. asset_url( $user->getAvatarPath() ) .'" class="img-circle '. $class .'" alt="" style="width:50px; height:50px;" title="'. $title .'" />';
                })
                ->addColumn('role', function(User $user) {
                    $htmlRoles = [];
                    foreach ($user->roles as $role) {
                        $htmlRoles[] = '<a href="'. route('admin.users.index') .'?role='. $role->getSlugGroup() .'" class="text-default">'. $role->getName() .'</a>';
                    }
                    return implode(', ', $htmlRoles);
                })
                ->editColumn('status', function(User $user) {
                    if (auth()->user()->hasPermission('admin.users.admin.edit') && (auth()->user()->level() > $user->level() || auth()->user()->hasRole('admin.root'))) {
                        return '<a href="'. route('admin.users.status', [$user->id, ($user->status == 'approved') ? 'inactive' : 'approved']) .'" class="text-success status-icon">'. $user->getStatus('label') .'</a>';
                    }
                    return $user->getStatus('label');
                })
                ->editColumn('mobile_no', function(User $user) {
                    return $user->profile->getTelLink('mobile_no', ['class'=>'text-default']);
                })
                ->editColumn('telephone_no', function(User $user) {
                    return $user->profile->getTelLink('telephone_no', ['class'=>'text-default']);
                })
                ->editColumn('emergency_no', function(User $user) {
                    return $user->profile->getTelLink('emergency_no', ['class'=>'text-default']);
                })
                ->editColumn('date_of_birth', function(User $user) {
                    return SiteHelper::formatDateTime($user->profile->date_of_birth, 'date');
                })
                ->editColumn('profile_type', function(User $user) {
                    return $user->profile->getProfileType();
                })
                ->editColumn('created_at', function(User $user) {
                    return SiteHelper::formatDateTime($user->created_at);
                })
                ->editColumn('updated_at', function(User $user) {
                    return SiteHelper::formatDateTime($user->updated_at);
                });

                if ( $request->get('role') == 'driver' ) {
                    $dt->editColumn('commission', function(User $user) {
                        return $user->profile->getCommission();
                    })
                    ->editColumn('insurance', function(User $user) {
                        return trim($user->profile->insurance .' '. $user->profile->getExpiryDate('insurance_expiry_date'));
                    })
                    ->editColumn('driving_licence', function(User $user) {
                        return trim($user->profile->driving_licence .' '. $user->profile->getExpiryDate('driving_licence_expiry_date'));
                    })
                    ->editColumn('pco_licence', function(User $user) {
                        return trim($user->profile->pco_licence .' '. $user->profile->getExpiryDate('pco_licence_expiry_date'));
                    })
                    ->editColumn('phv_licence', function(User $user) {
                        return trim($user->profile->phv_licence .' '. $user->profile->getExpiryDate('phv_licence_expiry_date'));
                    })
                    ->editColumn('availability', function(User $user) {
                        return $user->profile->getAvailability('list');
                    })
                    ->editColumn('availability_status', function(User $user) {
                        return $user->profile->getAvailabilityStatus('label');
                    });

                    if (config('eto.allow_fleet_operator')) {
                        $dt->editColumn('fleet_id', function(User $user) {
                            if (auth()->user()->hasPermission(['admin.users.admin.show'])) {
                                return '<a href="'. route('admin.users.show', $user->fleet_id) .'" class="text-default">'. $user->getFleetName() .'</a>';
                            }
                            return $user->getFleetName();
                        });
                    }
                }

            return $dt->make(true);
        }
    }

    public function index(Builder $builder, Request $request)
    {
        if (!auth()->user()->hasPermission(['admin.users.admin.index', 'admin.users.driver.index', 'admin.users.customer.index'])) {
            return redirect_no_permission();
        }

        $columns = [];

        if (auth()->user()->hasPermission([
            'admin.users.admin.edit',
            'admin.users.driver.edit',
            'admin.users.customer.edit',
            'admin.users.admin.destroy',
            'admin.users.driver.destroy',
            'admin.users.customer.destroy',
            'admin.users.admin.show',
            'admin.users.driver.show',
            'admin.users.customer.show',
            'admin.vehicles.index',
            'admin.bookings.index'
        ])) {
            $columns = array_merge($columns, [
                ['data' => 'actions', 'name' => 'actions', 'title' => trans('admin/users.actions'), 'defaultContent' => '', 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true]
            ]);
        }

        $columns = array_merge($columns, [
            ['data' => 'avatar', 'name' => 'last_seen_at', 'title' => trans('admin/users.avatar'), 'render' => null, 'orderable' => true, 'searchable' => false, 'exportable' => false, 'printable' => true],
            ['data' => 'id', 'name' => 'id', 'title' => trans('admin/users.id'), 'visible' => false],
            ['data' => 'name', 'name' => 'name', 'title' => trans('admin/users.display_name')],
            ['data' => 'email', 'name' => 'email', 'title' => trans('admin/users.email')]
        ]);

        $tnUserProfile = (new \App\Models\UserProfile)->getTable();

        if ( $request->get('role') == 'customer' || $request->get('role') == 'driver' ) {
            $columns = array_merge($columns, [
                ['data' => 'title', 'name' => $tnUserProfile .'.title', 'title' => trans('admin/users.title'), 'visible' => false],
                ['data' => 'first_name', 'name' => $tnUserProfile .'.first_name', 'title' => trans('admin/users.first_name'), 'visible' => false],
                ['data' => 'last_name', 'name' => $tnUserProfile .'.last_name', 'title' => trans('admin/users.last_name'), 'visible' => false],
                ['data' => 'date_of_birth', 'name' => $tnUserProfile .'.date_of_birth', 'title' => trans('admin/users.date_of_birth'), 'visible' => false],
                ['data' => 'mobile_no', 'name' => $tnUserProfile .'.mobile_no', 'title' => trans('admin/users.mobile_no'), 'visible' => $request->get('role') == 'driver' ? true : false],
                ['data' => 'telephone_no', 'name' => $tnUserProfile .'.telephone_no', 'title' => trans('admin/users.telephone_no'), 'visible' => false],
                ['data' => 'emergency_no', 'name' => $tnUserProfile .'.emergency_no', 'title' => trans('admin/users.emergency_no'), 'visible' => false],
                ['data' => 'address', 'name' => $tnUserProfile .'.address', 'title' => trans('admin/users.address'), 'visible' => false],
                ['data' => 'city', 'name' => $tnUserProfile .'.city', 'title' => trans('admin/users.city'), 'visible' => false],
                ['data' => 'postcode', 'name' => $tnUserProfile .'.postcode', 'title' => trans('admin/users.postcode'), 'visible' => false],
                ['data' => 'state', 'name' => $tnUserProfile .'.state', 'title' => trans('admin/users.state'), 'visible' => false],
                ['data' => 'country', 'name' => $tnUserProfile .'.country', 'title' => trans('admin/users.country'), 'visible' => false],
                ['data' => 'profile_type', 'name' => $tnUserProfile .'.profile_type', 'title' => trans('admin/users.profile_type'), 'visible' => false],
                ['data' => 'company_name', 'name' => $tnUserProfile .'.company_name', 'title' => trans('admin/users.company_name'), 'visible' => false],
                ['data' => 'company_number', 'name' => $tnUserProfile .'.company_number', 'title' => trans('admin/users.company_number'), 'visible' => false],
                ['data' => 'company_tax_number', 'name' => $tnUserProfile .'.company_tax_number', 'title' => trans('admin/users.company_tax_number'), 'visible' => false],
                ['data' => 'description', 'name' => $tnUserProfile .'.description', 'title' => trans('admin/users.description'), 'visible' => false]
            ]);
        }

        if ( $request->get('role') == 'driver' ) {
            $columns = array_merge($columns, [
                ['data' => 'unique_id', 'name' => $tnUserProfile .'.unique_id', 'title' => trans('admin/users.unique_id')],
                ['data' => 'commission', 'name' => $tnUserProfile .'.commission', 'title' => trans('admin/users.commission'), 'visible' => false],
                ['data' => 'national_insurance_no', 'name' => $tnUserProfile .'.national_insurance_no', 'title' => trans('admin/users.national_insurance_no'), 'visible' => false],
                ['data' => 'bank_account', 'name' => $tnUserProfile .'.bank_account', 'title' => trans('admin/users.bank_account'), 'visible' => false],
                ['data' => 'insurance', 'name' => $tnUserProfile .'.insurance', 'title' => trans('admin/users.insurance'), 'visible' => false],
                ['data' => 'driving_licence', 'name' => $tnUserProfile .'.driving_licence', 'title' => trans('admin/users.driving_licence'), 'visible' => false],
                ['data' => 'pco_licence', 'name' => $tnUserProfile .'.pco_licence', 'title' => trans('admin/users.pco_licence'), 'visible' => false],
                ['data' => 'phv_licence', 'name' => $tnUserProfile .'.phv_licence', 'title' => trans('admin/users.phv_licence'), 'visible' => false],
                ['data' => 'availability', 'name' => $tnUserProfile .'.availability', 'title' => trans('admin/users.availability'), 'visible' => false],
                ['data' => 'availability_status', 'name' => $tnUserProfile .'.availability_status', 'title' => trans('admin/users.availability_status'), 'visible' => true]
            ]);

            if (config('eto.allow_fleet_operator')) {
                $columns = array_merge($columns, [
                    ['data' => 'fleet_id', 'name' => 'fleet_id', 'title' => trans('admin/users.fleet_id'), 'visible' => true]
                ]);
            }
        }

        $columns = array_merge($columns, [
            ['data' => 'role', 'name' => 'role', 'title' => trans('admin/users.role'), 'orderable' => false, 'searchable' => false],
            ['data' => 'status', 'name' => 'status', 'title' => trans('admin/users.status'), 'searchable' => false],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => trans('admin/users.updated_at'), 'visible' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('admin/users.created_at'), 'visible' => false, 'searchable' => false]
        ]);

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
                [3, 'asc']
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
                ],
            ]
        ];

        if (auth()->user()->hasPermission(['admin.users.admin.create', 'admin.users.driver.create', 'admin.users.customer.create'])) {
            $parameters['buttons'][] = [
                'text' => '<div onclick="window.location.href=\''. route('admin.users.create') . ($request->get('role') ? '?role='. $request->get('role') : '') .'\';"><i class="fa fa-plus"></i> <span class="hidden-xs">'. trans('admin/users.button.create_new') .'</span></div>',
                'titleAttr' => trans('admin/users.button.create_new'),
                'className' => 'btn-success btn-sm buttons-new'
            ];
        }

        $ajax = [
            'url' => route('admin.users.datatables'),
            'type' => 'POST',
            'headers' => [
                'X-CSRF-TOKEN' => csrf_token()
            ],
            'data' => json_encode([
                'role' => $request->get('role'),
                'team' => $request->get('team')
            ])
        ];

        $builder->columns($columns)->parameters($parameters)->ajax($ajax);

        return view('admin.users.index', compact('builder'));
    }

    public function show($id)
    {
        if ((int)auth()->user()->id != (int)$id && !auth()->user()->hasPermission(['admin.users.admin.show', 'admin.users.driver.show', 'admin.users.customer.show'])) {
            return redirect_no_permission();
        }

        $user = User::with('profile');

        if (config('eto.allow_fleet_operator')) {
            if ((int)auth()->user()->id != (int)$id && auth()->user()->hasRole('admin.fleet_operator')) {
                $user->where('fleet_id', auth()->user()->id);
            }
        }

        $user = $user->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission(['admin.users.admin.create', 'admin.users.driver.create', 'admin.users.customer.create'])) {
            return redirect_no_permission();
        }

        $timezoneList = \App\Helpers\SiteHelper::getTimezoneList('group');

        $user = new User;
        $user->profile = new UserProfile;
        $user->base_address = '';

        $roles = \App\Models\Role::getListing()->byPermissions()->orderBy('level', 'asc')->get();
        $permissions = \App\Models\Permission::orderBy('slug', 'asc')->get();

        $profileTypes = $user->profile->profileTypeOptions;
        $availabilityStatus = [];
        foreach($user->profile->availabilityStatusOptions as $key => $value) {
            $availabilityStatus[$key] = $value['name'];
        }

        $status = [];
        foreach($user->statusOptions as $key => $value) {
            $status[$key] = $value['name'];
        }

        $locales = [];
        foreach (config('app.locales') as $code => $locale) {
            $locales[$code] = $locale['name'] . ' ('. $locale['native'] .')';
        }

        $fleets = [];
        if (config('eto.allow_fleet_operator')) {
            $tnUser = (new \App\Models\User)->getTable();

            $fleetUsers = User::select($tnUser .'.id', $tnUser .'.name')
              ->role('admin.fleet_operator')
              ->orderBy($tnUser .'.name', 'asc')
              ->get();

            if ($fleetUsers->count()) {
                $fleets[0] = trans('admin/users.fleet_select');
                foreach ($fleetUsers as $k => $v) {
                    $fleets[$v->id] = $v->getName(false);
                }
            }
        }

        $teamsList = [];
        if (config('eto.allow_teams')) {
            $teams = \App\Models\Team::select('id', 'name')->orderBy('name', 'asc')->get();
            foreach ($teams as $k => $v) {
                $teamsList[$v->id] = $v->getName();
            }
        }

        return view('admin.users.create', compact('roles', 'status', 'availabilityStatus', 'profileTypes', 'locales', 'fleets', 'teamsList', 'timezoneList', 'permissions'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission(['admin.users.admin.create', 'admin.users.driver.create', 'admin.users.customer.create'])) {
            return redirect_no_permission();
        }

        $errors = [];

        $rules = [
            'role' => 'required|max:255',
            'name' => 'required|max:255',
            'username' => [
                'required',
                'max:255',
                'unique:users',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users',
            ],
            'password' => 'required|min:6|confirmed',
            'avatar' => 'mimes:jpg,jpeg,gif,png'
        ];

        $rules = array_merge($rules, [
            'profile.commission' => 'numeric|between:0,999.99',
            // 'profile.first_name' => 'required|max:255',
            // 'profile.last_name' => 'required|max:255',
            'profile.date_of_birth' => 'date',
            'profile.insurance_expiry_date' => 'date',
            'profile.driving_licence_expiry_date' => 'date',
            'profile.pco_licence_expiry_date' => 'date',
            'profile.phv_licence_expiry_date' => 'date',
        ]);

        $messages = [];

        $attributeNames = [
            // 'profile.title' => 'profile title',
            // 'profile.first_name' => 'profile first name',
            // 'profile.last_name' => 'profile last name',
        ];

        $validator = $this->validate($request, $rules, $messages, $attributeNames);

        $user = new User;
        $user->fleet_id = $request->get('fleet_id', 0);
        $user->name = $request->get('name');
        $user->username = $request->get('username');
        $user->email = $request->get('email');
        $user->password = bcrypt($request->get('password'));
        $user->status = $request->get('status');

      	if ( $request->hasFile('avatar') ) {
        		$file = $request->file('avatar');
        		$filename = \App\Helpers\SiteHelper::generateFilename('avatar') .'.'. $file->getClientOriginalExtension();

            $img = Image::make($file);

            if ($img->width() > config('site.image_dimensions.avatar.width')) {
                $img->resize(config('site.image_dimensions.avatar.width'), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if ($img->height() > config('site.image_dimensions.avatar.height')) {
                $img->resize(null, config('site.image_dimensions.avatar.height'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $img->save(asset_path('uploads','avatars/'. $filename));

        		$user->avatar = $filename;
      	}

        $user->save();

      	$userRoles = $request->get('role');
      	foreach ($userRoles as $role) {
            $user->attachRole($role);
        }
      	$user->used_role = $userRoles[0];
      	$user->save();

        // Base
        if ( !empty($request->get('base_address')) ) {
            $base = new \App\Models\Base;
            $base->relation_type = 'user';
            $base->relation_id = $user->id;
            $base->name = $user->name;
            $base->address = $request->get('base_address');
            $base->status = 'activated';
            $base->save();
        }

        // Profile
        if ( $request->get('profile') && $user->id ) {
            $p = (object)$request->get('profile');

            // Aavailability
            $availability = [];

            if ( !empty($p->availability) ) {
                foreach($p->availability as $key => $value) {
                    $availability[] = [
                        'start_date' => (string)$value['start_date'],
                        'end_date' => (string)$value['end_date'],
                        'available_date' => (string)$value['available_date']
                    ];
                }
            }

            $availability = json_encode($availability);

            $profile = new UserProfile;
            $profile->user_id = $user->id;
            $profile->title = $p->title;
            $profile->first_name = $p->first_name;
            $profile->last_name = $p->last_name;
            $profile->date_of_birth = $p->date_of_birth;
            $profile->mobile_no = $p->mobile_no;
            $profile->telephone_no = $p->telephone_no;
            $profile->emergency_no = $p->emergency_no;
            $profile->address = $p->address;
            $profile->city = $p->city;
            $profile->postcode = $p->postcode;
            $profile->state = $p->state;
            $profile->country = $p->country;
            $profile->profile_type = $p->profile_type;

            if ( $p->profile_type == 'company' ) {
                $profile->company_name = $p->company_name;
                $profile->company_number = $p->company_number;
                $profile->company_tax_number = $p->company_tax_number;
            }
            else {
                $profile->company_name = null;
                $profile->company_number = null;
                $profile->company_tax_number = null;
            }

            $profile->national_insurance_no = $p->national_insurance_no;
            $profile->bank_account = $p->bank_account;
            $profile->unique_id = $p->unique_id;
            $profile->commission = $p->commission;
            $profile->availability = $availability;
            $profile->availability_status = $p->availability_status;
            $profile->insurance = $p->insurance;
            $profile->insurance_expiry_date = $p->insurance_expiry_date;
            $profile->driving_licence = $p->driving_licence;
            $profile->driving_licence_expiry_date = $p->driving_licence_expiry_date;
            $profile->pco_licence = $p->pco_licence;
            $profile->pco_licence_expiry_date = $p->pco_licence_expiry_date;
            $profile->phv_licence = $p->phv_licence;
            $profile->phv_licence_expiry_date = $p->phv_licence_expiry_date;
            $profile->description = $p->description;
            $profile->updated_at = Carbon::now();
            $profile->save();

            // Files
            if ( !empty($p->files) ) {
                foreach($p->files as $key => $value) {
                    $value = (object)$value;

                    $value->name = trim($value->name);

                    if ( $value->id > 0 ) {
                        $query = DB::table('file')
                                ->where('file_relation_type', 'user')
                                ->where('file_relation_id', $user->id)
                                ->where('file_id', $value->id)
                                ->first();

                        if ( !empty($query) ) {
                            if ( $value->delete > 0 ) {
                                if ( Storage::disk('safe')->exists($query->file_path) ) {
                                    Storage::disk('safe')->delete($query->file_path);
                                }

                                DB::table('file')->where('file_id', $query->file_id)->delete();
                            }
                            else {
                                DB::table('file')->where('file_id', $query->file_id)->update(['file_name' => $value->name]);
                            }
                        }
                    }
                    else {
                        if ( isset($request->file('profile.files')[$key]) ) {
                            $file = $request->file('profile.files')[$key];

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
                                $realPath = $file->getRealPath();
                                $size = $file->getSize();
                                $mimeType = $file->getMimeType();
                                $newName = \App\Helpers\SiteHelper::generateFilename('user') .'.'. $extension;

                                $file->move(asset_path('uploads','safe'), $newName);

                                DB::table('file')->insertGetId([
                                    'file_name' => $value->name,
                                    'file_path' => $newName,
                                    'file_site_id' => 0,
                                    'file_description' => $originalName,
                                    'file_relation_type' => 'user',
                                    'file_relation_id' => $user->id,
                                    'file_free_download' => 0,
                                    'file_ordering' => 0,
                                    'file_limit' => 0
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Settings
        if ( $request->get('settings') && $user->id ) {
            $settings = (object)$request->get('settings');

            if (isset($settings->locale)) {
                settings_save('app.locale', $settings->locale, 'user', $user->id);
            }

            if (isset($settings->timezone)) {
                settings_save('app.timezone', $settings->timezone, 'user', $user->id, true);
            }
        }

        // Teams
        if (config('eto.allow_teams')) {
            $teams = $request->get('teams');
            if (!empty($teams) && is_array($teams)) {
                $sync = [];
                foreach ($teams as $k => $v) {
                    $sync[$v] = [];
                }
                $user->teams()->sync($sync);
            }
            else {
                $user->teams()->detach();
            }
        }

        \Cache::store('file')->forget('admin_check_user_documents');

        if ( !empty($errors) ) {
            session()->flash('message', trans('admin/users.message.store_success_with_errors'));
            return redirect()->route('admin.users.edit', $user->id)->withErrors($errors);
        }
        else {
            session()->flash('message', trans('admin/users.message.store_success'));
            return redirect()->route('admin.users.index', ['role' => $user->getMaxRoleGroup()]);
        }
    }

    public function edit($id)
    {
        if ((int)auth()->user()->id != (int)$id && !auth()->user()->hasPermission(['admin.users.admin.edit', 'admin.users.driver.edit', 'admin.users.customer.edit'])) {
            return redirect_no_permission();
        }

        $timezoneList = \App\Helpers\SiteHelper::getTimezoneList('group');
        $user = User::with('profile');

        if (config('eto.allow_fleet_operator')) {
            if ((int)auth()->user()->id != (int)$id && auth()->user()->hasRole('admin.fleet_operator')) {
                $user->where('fleet_id', auth()->user()->id);
            }
        }

        $user = $user->findOrFail($id);

        if ((auth()->user()->level() <= $user->level() && !auth()->user()->hasRole('admin.root')) && (int)auth()->user()->id != (int)$id) {
            return redirect_no_permission();
        }

        $base = \App\Models\Base::where('relation_type', '=', 'user')->where('relation_id', '=', $user->id)->first();

        if ( !empty($base->address) ) {
            $user->base_address = $base->address;
        }

        $roleIds = [];
        foreach ($user->getRoles() as $role) {
            $roleIds[] = $role->id;
        }

        $roles = \App\Models\Role::whereNotIn('slug', config('roles.not_use_roles'))->orWhereIn('id', $roleIds)->orderBy('level', 'asc')->get();
        $profileTypes = $user->profile->profileTypeOptions;
        $availabilityStatus = [];

        foreach($user->profile->availabilityStatusOptions as $key => $value) {
            $availabilityStatus[$key] = $value['name'];
        }

        $status = [];
        foreach($user->statusOptions as $key => $value) {
            $status[$key] = $value['name'];
        }

        $locales = [];
        foreach (config('app.locales') as $code => $locale) {
            $locales[$code] = $locale['name'] . ' ('. $locale['native'] .')';
        }

        $fleets = [];
        if (config('eto.allow_fleet_operator')) {
            if ($user->hasRole('driver.*') && !auth()->user()->hasRole('admin.fleet_operator')) {
                $tnUser = (new \App\Models\User)->getTable();

                $fleetUsers = User::select($tnUser .'.id', $tnUser .'.name')
                  ->role('admin.fleet_operator')
                  ->orderBy($tnUser .'.name', 'asc')
                  ->get();

                if ($fleetUsers->count()) {
                    $fleets[0] = trans('admin/users.fleet_select');
                    foreach ($fleetUsers as $k => $v) {
                        $fleets[$v->id] = $v->getName(false);
                    }
                }
            }
        }

        $teamsList = [];
        if (config('eto.allow_teams')) {
            $teams = \App\Models\Team::select('id', 'name')->orderBy('name', 'asc')->get();
            foreach ($teams as $k => $v) {
                $teamsList[$v->id] = $v->getName();
            }
        }

        return view('admin.users.edit', compact('user', 'roles', 'status', 'availabilityStatus', 'profileTypes', 'locales', 'fleets', 'teamsList', 'timezoneList'));
    }

    public function update(Request $request, $id)
    {
        if ((int)auth()->user()->id != (int)$id && !auth()->user()->hasPermission(['admin.users.admin.edit', 'admin.users.driver.edit', 'admin.users.customer.edit'])) {
            return redirect_no_permission();
        }

        $tnUser = (new \App\Models\User)->getTable();
        $user = User::query();

        if (config('eto.allow_fleet_operator')) {
            if ((int)auth()->user()->id != (int)$id && auth()->user()->hasRole('admin.fleet_operator')) {
                $user->where('fleet_id', auth()->user()->id);
            }
        }

        $user = $user->findOrFail($id);

        if (auth()->user()->level() <= $user->level() && !auth()->user()->hasRole('admin.root') && (int)auth()->user()->id != (int)$id) {
            return redirect_no_permission();
        }

        $errors = [];

        $rules = [
            'role' => 'required|max:255',
            'name' => 'required|max:255',
            'username' => [
                'required',
                'max:255',
                Rule::unique($tnUser)->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique($tnUser)->ignore($user->id),
            ],
            'password' => 'min:6|confirmed',
            'avatar' => 'mimes:jpg,jpeg,gif,png'
        ];

        $rules = array_merge($rules, [
            'profile.commission' => 'numeric|between:0,999.99',
            // 'profile.first_name' => 'required|max:255',
            // 'profile.last_name' => 'required|max:255',
            'profile.date_of_birth' => 'date',
            'profile.insurance_expiry_date' => 'date',
            'profile.driving_licence_expiry_date' => 'date',
            'profile.pco_licence_expiry_date' => 'date',
            'profile.phv_licence_expiry_date' => 'date',
        ]);

        $messages = [];

        $attributeNames = [
            // 'profile.title' => 'profile title',
            // 'profile.first_name' => 'profile first name',
            // 'profile.last_name' => 'profile last name',
        ];

        $validator = $this->validate($request, $rules, $messages, $attributeNames);

        // Check if there are more admins - TO CORRECT
        // if ($user->hasRole('admin.*')) {
        //     $admins = User::role('admin.root')->where('id', '!=', $user->id)->get();
        //
        //     if (count($admins) <= 0) {
        //         $role = $user->role;
        //     }
        // }

        if (count($user->roles) == 0) {
            $userRoles = $request->get('role');
            $user->detachAllRoles();
            foreach ($userRoles as $role) {
                $user->attachRole($role);
            }
            $user->used_role = $userRoles[0];
        }

        if (config('eto.allow_fleet_operator')) {
            if ($user->hasRole('driver.*') && null !== $request->get('fleet_id')) {
                $user->fleet_id = $request->get('fleet_id', 0);
            }
        }

        $user->name = $request->get('name');
        $user->username = $request->get('username');
        $user->email = $request->get('email');
        $user->status = $request->get('status');
        $user->updated_at = Carbon::now();

        if ( $request->get('password') ) {
            $user->password = bcrypt($request->get('password'));
        }

        if ( $request->hasFile('avatar') || $request->get('avatar_delete') ) {
            if ( Storage::disk('avatars')->exists($user->avatar) ) {
                Storage::disk('avatars')->delete($user->avatar);
                $user->avatar = null;
            }
        }

      	if ( $request->hasFile('avatar') ) {
        		$file = $request->file('avatar');
        		$filename = \App\Helpers\SiteHelper::generateFilename('avatar') .'.'. $file->getClientOriginalExtension();

            $img = Image::make($file);

            if ($img->width() > config('site.image_dimensions.avatar.width')) {
                $img->resize(config('site.image_dimensions.avatar.width'), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if ($img->height() > config('site.image_dimensions.avatar.height')) {
                $img->resize(null, config('site.image_dimensions.avatar.height'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $img->save(asset_path('uploads','avatars/'. $filename));

        		$user->avatar = $filename;
      	}

        $user->save();

        // Base
        // if ( !empty($request->get('base_address')) ) {
            $base = \App\Models\Base::updateOrCreate([
                'relation_type' => 'user',
                'relation_id' => $user->id
            ], [
                'relation_type' => 'user',
                'relation_id' => $user->id,
                'name' => $user->name,
                'address' => $request->get('base_address'),
                'status' => 'activated'
            ]);
        // }

        // Profile
        if ( $request->get('profile') && $user->id ) {
            $p = (object)$request->get('profile');

            $profile = UserProfile::where('user_id', $user->id)->first();

            if ( is_null($profile) ) {
                $profile = new UserProfile;
            }

            // Aavailability
            $availability = [];

            if ( !empty($p->availability) ) {
                foreach($p->availability as $key => $value) {
                    $availability[] = [
                        'start_date' => (string)$value['start_date'],
                        'end_date' => (string)$value['end_date'],
                        'available_date' => (string)$value['available_date']
                    ];
                }
            }

            $availability = json_encode($availability);

            $profile->user_id = $user->id;
            $profile->title = $p->title;
            $profile->first_name = $p->first_name;
            $profile->last_name = $p->last_name;
            $profile->date_of_birth = $p->date_of_birth;
            $profile->mobile_no = $p->mobile_no;
            $profile->telephone_no = $p->telephone_no;
            $profile->emergency_no = $p->emergency_no;
            $profile->address = $p->address;
            $profile->city = $p->city;
            $profile->postcode = $p->postcode;
            $profile->state = $p->state;
            $profile->country = $p->country;
            $profile->profile_type = $p->profile_type;

            if ( $p->profile_type == 'company' ) {
                $profile->company_name = $p->company_name;
                $profile->company_number = $p->company_number;
                $profile->company_tax_number = $p->company_tax_number;
            }
            else {
                $profile->company_name = null;
                $profile->company_number = null;
                $profile->company_tax_number = null;
            }

            $profile->national_insurance_no = $p->national_insurance_no;
            $profile->bank_account = $p->bank_account;
            $profile->unique_id = $p->unique_id;
            $profile->commission = $p->commission;
            $profile->availability = $availability;
            $profile->availability_status = $p->availability_status;
            $profile->insurance = $p->insurance;
            $profile->insurance_expiry_date = $p->insurance_expiry_date;
            $profile->driving_licence = $p->driving_licence;
            $profile->driving_licence_expiry_date = $p->driving_licence_expiry_date;
            $profile->pco_licence = $p->pco_licence;
            $profile->pco_licence_expiry_date = $p->pco_licence_expiry_date;
            $profile->phv_licence = $p->phv_licence;
            $profile->phv_licence_expiry_date = $p->phv_licence_expiry_date;
            $profile->description = $p->description;
            $profile->updated_at = Carbon::now();
            $profile->save();

            // Files
            if ( !empty($p->files) ) {
                foreach($p->files as $key => $value) {
                    $value = (object)$value;
                    $value->name = trim($value->name);

                    if ( $value->id > 0 ) {
                        $query = DB::table('file')
                          ->where('file_relation_type', 'user')
                          ->where('file_relation_id', $user->id)
                          ->where('file_id', $value->id)
                          ->first();

                        if ( !empty($query) ) {
                            if ( $value->delete > 0 ) {
                                if ( Storage::disk('safe')->exists($query->file_path) ) {
                                    Storage::disk('safe')->delete($query->file_path);
                                }
                                DB::table('file')->where('file_id', $query->file_id)->delete();
                            }
                            else {
                                DB::table('file')->where('file_id', $query->file_id)->update(['file_name' => $value->name]);
                            }
                        }
                    }
                    else {
                        if ( isset($request->file('profile.files')[$key]) ) {
                            $file = $request->file('profile.files')[$key];

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
                                $realPath = $file->getRealPath();
                                $size = $file->getSize();
                                $mimeType = $file->getMimeType();
                                $newName = \App\Helpers\SiteHelper::generateFilename('user') .'.'. $extension;

                                $file->move(asset_path('uploads','safe'), $newName);

                                DB::table('file')->insertGetId([
                                    'file_name' => $value->name,
                                    'file_path' => $newName,
                                    'file_site_id' => 0,
                                    'file_description' => $originalName,
                                    'file_relation_type' => 'user',
                                    'file_relation_id' => $user->id,
                                    'file_free_download' => 0,
                                    'file_ordering' => 0,
                                    'file_limit' => 0
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Teams
        if (config('eto.allow_teams')) {
            $teams = $request->get('teams');
            if (!empty($teams) && is_array($teams)) {
                $sync = [];
                foreach ($teams as $k => $v) {
        						$sync[$v] = [];
      					}
                $user->teams()->sync($sync);
            }
            else {
                $user->teams()->detach();
            }
        }

        // Settings
        if ( $request->get('settings') && $user->id ) {
            $settings = (object)$request->get('settings');

            if (isset($settings->locale)) {
                settings_save('app.locale', $settings->locale, 'user', $user->id);
            }

            if (isset($settings->timezone)) {
                settings_save('app.timezone', $settings->timezone, 'user', $user->id, true);
            }
        }

        \Cache::store('file')->forget('admin_check_user_documents');

        if ( !empty($errors) ) {
            session()->flash('message', trans('admin/users.message.update_success_with_errors'));
            return redirect()->back()->withErrors($errors);
        }
        else {
            session()->flash('message', trans('admin/users.message.update_success'));
            // return redirect()->route('admin.users.show', $user->id);
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        if (auth()->user()->id == $id || !auth()->user()->hasPermission(['admin.users.admin.destroy', 'admin.users.driver.destroy', 'admin.users.customer.destroy'])) {
            return redirect_no_permission();
        }

        $user = User::query();

        if (config('eto.allow_fleet_operator')) {
            if ((int)auth()->user()->id != (int)$id && auth()->user()->hasRole('admin.fleet_operator')) {
                $user->where('fleet_id', auth()->user()->id);
            }
        }

        $user = $user->findOrFail($id);

        if (auth()->user()->level() <= $user->level() && !auth()->user()->hasRole('admin.root')) {
            return redirect_no_permission();
        }

        // Check if there are more admins
        $tnUser = (new \App\Models\User)->getTable();
        $admins = User::role('admin.root')->where($tnUser .'.id', '!=', $user->id)->get();

        if (auth()->user()->id == $id || count($admins) <= 0) {
            if ( url()->previous() != url()->full() ) {
                return redirect()->back()->withErrors([trans('admin/users.message.destroy_failure')]);
            }
            else {
                return redirect()->route('admin.users.index')->withErrors([trans('admin/users.message.destroy_failure')]);
            }
        }

        // Bases
        \App\Models\Base::where('relation_type', 'user')->where('relation_id', $user->id)->delete();

        // Events
        \App\Models\Event::where('relation_type', 'user')->where('relation_id', $user->id)->delete();

        // Profile
        UserProfile::where('user_id', $user->id)->delete();

        // Params
        UserParam::where('user_id', $user->id)->delete();

        // Uploaded files
        $query = DB::table('file')
           ->where('file_relation_type', 'user')
           ->where('file_relation_id', $user->id)
           ->get();

        if ( !empty($query) ) {
            foreach($query as $key => $value) {
                if ( Storage::disk('safe')->exists($value->file_path) ) {
                    Storage::disk('safe')->delete($value->file_path);
                }
                DB::table('file')->where('file_id', $value->file_id)->delete();
            }
        }

        // Avatar
        if ( Storage::disk('avatars')->exists($user->avatar) ) {
            Storage::disk('avatars')->delete($user->avatar);
        }

        // Remove users vehicles??

        // Teams
        $user->teams()->detach();

        // User
        $user->delete();

        \Cache::store('file')->forget('admin_check_user_documents');

        session()->flash('message', trans('admin/users.message.destroy_success'));

        if ( url()->previous() != url()->full() ) {
            return redirect()->back();
        }
        else {
            return redirect()->route('admin.users.index');
        }
    }

    public function download($id)
    {
        $query = DB::table('file')
          ->where('file_relation_type', 'user')
          // ->where('file_relation_id', $user->id)
          ->where('file_id', $id)
          ->first();

        if ( !empty($query) ) {
            $filePath = asset_path('uploads','safe/'. $query->file_path);
            return response()->download($filePath, $query->file_path);
        }
        else {
            return;
        }
    }

    public function status($id, $status)
    {
        if ((int)auth()->user()->id != (int)$id && !auth()->user()->hasPermission(['admin.users.admin.edit', 'admin.users.driver.edit', 'admin.users.customer.edit'])) {
            return redirect_no_permission();
        }

        $user = User::query();

        if (config('eto.allow_fleet_operator')) {
            if ((int)auth()->user()->id != (int)$id && auth()->user()->hasRole('admin.fleet_operator')) {
                $user->where('fleet_id', auth()->user()->id);
            }
        }

        $user = $user->findOrFail($id);

        if (auth()->user()->level() <= $user->level() && !auth()->user()->hasRole('admin.root')) {
            return redirect_no_permission();
        }

        $allowed = [
            'approved',
            'awaiting_admin_review',
            'awaiting_email_confirmation',
            'inactive',
            'rejected'
        ];

        if ( in_array($status, $allowed) ) {
            $user->status = $status;
            $user->save();
        }

        return redirect()->back();
    }
}
