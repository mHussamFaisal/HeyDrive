<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use ZanySoft\Zip\Zip;

class ExportController extends Controller
{
    use \App\Traits\Filters\Bookings;
    use \App\Traits\Filters\Users;
    use \App\Traits\Filters\Customers;
    use \App\Traits\Filters\Feedback;
    use \App\Traits\Filters\FixedPrices;
    use \App\Traits\Filters\VehicleTypes;
    use \App\Traits\Filters\Vehicles;

    protected $select;
    protected $sites;
    protected $limitPerFile = 500;

    public function __construct()
    {
        $this->select = new \stdClass();
        $this->select->type = 'select';
        $this->select->multiple = true;
        $this->select->translations = false;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fileSystem = new Filesystem();
        $fileSystem->deleteDirectory(parse_path('tmp/export'));

        return view('export.index', ['sections' => $this->getSectionsConfig()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = [];
        $settings = [];
        $sections = $request->all();

        if ($sections) {
            foreach ($sections as $section => $filters) {
                if (!empty($filters['filters'])) {
                    $method = 'parse' . ucfirst($section) . 'Filters';
                    $data[$section] = $this->$method($filters['filters'], $filters['filters']['filter_name']);
                }
            }
        }

        foreach ($data as $sectionName=>$section) {
            foreach ($section as $filters) {
                $keyFromName = preg_replace('#\W+#', '_', $filters['name']);
                $settings[] = [
                    'eto_export.sections.'.$sectionName.'.filters.'.$keyFromName.'.name',
                    \mysql_escape($filters['name']),
                    'subscription',
                    $request->system->subscription->id,
                ];
                $settings[] = [
                    'eto_export.sections.'.$sectionName.'.filters.'.$keyFromName.'.type',
                    $filters['type'],
                    'subscription',
                    $request->system->subscription->id,
                ];
                $settings[] = [
                    'eto_export.sections.'.$sectionName.'.filters.'.$keyFromName.'.data',
                    \GuzzleHttp\json_encode($filters['data']),
                    'subscription',
                    $request->system->subscription->id,
                ];
            }
        }

        settings_save($settings, null, null, null, true);

        return response()->json(config('eto_export.sections'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function getSectionsConfig()
    {
        $config = json_decode(json_encode(config('eto_export.sections')));

        foreach($config as $sectionName=>$section) {
            $method = 'get' . ucfirst($sectionName) . 'Filters';
            $config->$sectionName = $this->$method($section);
        }

        return json_decode(json_encode($config));
    }

    public function download(Request $request, $format)
    {
        $this->sites = [];
        foreach(request()->system->sites as $site) {
            $this->sites[] = $site->id;
        }
        $config = [];
        $columns = [];
        $data = [];
        $sections = $this->getSectionsConfig();
        $SectionsToExport = $request->toExport;

        foreach ($request->all() as $section=>$filters) {
            if ($section == 'toExport' || (int)$SectionsToExport[$section] !== 1) {
                continue;
            }

            if (empty($config[$section])) {
                $config[$section] = [];
            }

            if (!empty($filters['tags'])) {
                foreach ($filters['tags'] as $id=>$tag) {
                    if (!empty($sections->$section->filters->$tag)) {
                        $extend = json_decode(json_encode($sections->$section->filters->$tag), true);

                        if (empty($config[$section])) {
                            $config[$section] = $extend['data'];
                        }
                        else {
                            $config[$section] = array_unique(array_merge_recursive($config[$section], $extend['data']));
                        }
                    }
                }
            }

            if (!empty($filters['filters'])) {
                if (empty($config[$section])) {
                    $config[$section] = $filters['filters'];
                }
                else {
                    $config[$section] = array_unique(array_merge_recursive($config[$section], $filters['filters']));
                }
            }

            if (!empty($filters['columns'])) {
                $sections->$section->columns = $filters['columns'];
            }

            foreach($sections->$section->columns as $col=>$toExport) {
                if (is_array($toExport)) {
                    $subColumns = $toExport;
                    foreach($subColumns as $subCol=>$subToExport) {
                        $columns[$section][$col.'.'.$subCol] = $subToExport;
                    }
                }
                else {
                    $columns[$section][$col] = $toExport;
                }
            }

            $data[$section] = $this->getData($section, $config[$section]);
        }

        return $this->generateZip($data, $columns, $format);
    }

    private function getData($section, $config)
    {
        $method = 'get'.ucfirst($section).'Data';

        try {
            return $this->$method($config);
        }
        catch (\Exception $e) {}

        return false;
    }

    private function setFilePerChunkSection($section, $page, $format, $cells) {
        $filename = 'ETO_Export_'. $section . '_' . $page . '_' . Carbon::now()->format('Y-m-d_H-i');

        $excel = \Excel::create($filename, function ($excel) use ($section, $cells) {
            $excel->sheet($section, function ($sheet) use ($cells) {
                $sheet->fromArray($cells, null, 'A1', false, false);
                $sheet->row(1, function ($row) {
                    $row->setBackground('#F5F5F5');
                    $row->setFontWeight('bold');
                });
            });
        });

        $excel->store($format);
    }

    public function generateZip($data, $columns, $format)
    {
        $folder = parse_path('tmp/export/ETO_export_'.Carbon::now()->format('Y-m-d_H-i'));
        config(['excel.export.store.path'=> $folder]);

        foreach($data as $section=>$collection) {
            $method = 'get'.ucfirst($section).'File';
            try {
                $this->$method($collection, $columns[$section], $format, $folder);
            }
            catch (\Exception $e) {}
        }

        $fileSystem = new Filesystem();
        if ($fileSystem->exists($folder)) {
            Zip::create($folder . '.zip')->add($folder)->close();

            if (Zip::check($folder . '.zip')) {
                @chmod($folder . '.zip', 0644);
                $fileSystem->deleteDirectory($folder);

                return response()->download($folder . '.zip', basename($folder . '.zip'));
            }
        }

        abort(404);
    }
}
