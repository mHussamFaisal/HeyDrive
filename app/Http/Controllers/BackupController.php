<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Filesystem\Filesystem;
use ZanySoft\Zip\Zip;

class BackupController extends Controller
{
    protected $isCron = false;
    protected $localDisk = 'local';
    protected $limit = 1000; // rows per sql file
    protected $waitingTime = 7; // seconds
    protected $timeOut = 300; // seconds
    protected $backupListDir = [
        'app',
        'assets',
        'bootstrap',
        'config',
        'database',
        'resources',
        'routes',
        'storage',
        'tests',
        'uploads',
    ];
    protected $backupListFiles = [
        '.htaccess',
        'artisan',
        'CHANGELOG.txt',
        'config.php',
        'index.php',
        'LICENSE.txt',
        'robots.txt',
        'updater.php',
        'web.config',
    ];
    protected $backupListMultiSubscription = [
        'uploads',
    ];
    protected $backupId = 0;
    protected $disk = '';
    protected $type = '';
    protected $backupName = '';
    protected $tmpDirectory = '';
    protected $tableDelimiter = '_offset_';

    /**
     * BackupController constructor.
     */
    public function __construct() {
        if (request('isCron')) {
            $this->isCron = request('isCron');
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        if (!auth()->user()->hasPermission('admin.backups.index')) {
            return redirect_no_permission();
        }

        $this->removeSystemBackups();
        $fileSystem = new Filesystem();
        $fileList = $fileSystem->files(base_path(), true);
        $dirList = $fileSystem->directories(base_path());
        $disks = ['backup_ftp'=>config('filesystems.disks.backup_ftp')];

        foreach($fileList as $id=>$item){
            $fileList[$id] = str_replace(base_path(), '', $item);
            $fileList[$id] = preg_replace('/(^\\\\|^\/)/m', '', $fileList[$id]);
        }

        foreach($dirList as $id=>$item){
            $dirList[$id] = str_replace(base_path(), '', $item);
            $dirList[$id] = preg_replace('/(^\\\\|^\/)/m', '', $dirList[$id]);
            if (in_array($dirList[$id],['backups','tmp'])) {
                unset($dirList[$id]);
            }
        }

        $backups = Backup::where('type', '!=', 'system')->get();
        $freeSpace = disk_free_space(base_path());
        $minSize = 70 * 1024 * 1024; //mb to bytes

        if (null !== $backups && count((array)$backups) > 0) {
            if ($lastBackup = $backups->last()) {
                $minSize = (5 + $lastBackup->size) * 2;
            }
            foreach ($backups as $idb=>$backup) {
                $backups[$idb]->file_exists = \Storage::disk('backup_' . $backup->disk)->exists($backup->file . '.zip');
            }
        }

        return view('backup.index', compact('backups', 'dirList', 'fileList', 'freeSpace', 'minSize', 'disks'));
    }

    /**
     * @param $name
     * @param null $comments
     * @return mixed
     */
    private function updateNewBackupRecord($name, $comments = null) {
        $backup = new \App\Models\Backup();
        $backup->file = $this->backupName;
        $backup->name = $name ?: trans('backup.type.' . $this->type) . ' ' . trans('backup.backup');
        $backup->comments = $comments ?: null;
        $backup->type = $this->type;
        $backup->disk = $this->disk;
        $backup->status = 0;
        $backup->save();

        return $backup->id;
    }

    /**
     * @param $request
     * @param bool $getZipInstance
     * @return bool
     */
    private function getOrCreateZip($request, $getZipInstance = true)
    {
        if ((count((array)$request->dirList) > 0 || count((array)$request->fileList) > 0) && $request->type != 'system') {
            $type = 'custom';
        }
        else {
            $type = $request->type;
        }

        $this->disk = $request->backupDisk ?: $this->localDisk;
        $this->type = $type;
        $this->backupName = $this->backupName ?: ($request->backupName ?: $this->type . '_' . $this->disk . '_' . date('Y_m_d__H_i_s'));
        $this->tmpDirectory = 'tmp' . DIRECTORY_SEPARATOR . $this->backupName;
        $this->backupId = $this->backupId ?: ($request->backupId ?:  $this->updateNewBackupRecord($request->name, $request->comments));
        if (!\Storage::disk('tmp')->exists($this->backupName . '.zip') && $getZipInstance === false && !$request->systemBackup) {
            clear_tmp();
        }
        if ($getZipInstance) {
            $zip =  \Zipper::make($this->tmpDirectory . '.zip');
            $zip->folder($this->backupName)->add(public_path('config.php')); // Multi subscription
            // $zip->folder($this->backupName)->add(base_path('config.php'));
            return $zip;
        }
        else {
            return true;
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function backupDB(Request $request)
    {
        $executionStartTime = microtime(true);
        $maxExecutionTime = get_ini_time($this->timeOut, $this->isCron);
        $this->getOrCreateZip($request, false);
        $response = ['status' => false, 'type' => $this->type];

        if (in_array($this->type, ['full', 'system', 'db', 'custom', 'subscription']) && $request->type != 'files') {
            try {
                $zipDB = \Zipper::make('tmp' . DIRECTORY_SEPARATOR . '_db.zip');
                $status = true;
                $tables = $request->list ?: $this->listTables();
                $system = true; // For updater.php
                $etoConfig = (array)app('etoConfig'); // For updater.php
                include(base_path('updater.php'));
                $beforeForeachStartTime = microtime(true) - $executionStartTime;
                $tableKeys = \ETODB::getIndexAndForeignKeys($tables, true);

                foreach ($tables as $id=>$table) {
                    list($name, $offset) = explode($this->tableDelimiter, $table);

                    $foreachStartTime = microtime(true);
                    $sql = \ETODB::dumpTable($name, $offset, $this->limit, true);

                    if (!empty($sql) && is_string($sql)) {
                        $zipDB->addString($name . '_limit_' . $this->limit . '_offset_' . $offset . '.sql', $sql);
                    }
                    else {
                        \Log::error(['App\Http\Controllers\backupDB(Request $request) - no dump table', $name, $offset, is_string($sql)]);
                    }

                    if ((int)$offset === 0 && !empty($tableKeys[$name])) {
                        $sqlIndexAndForeignKeys = \ETODB::dumpIndexAndForeignKeys($tableKeys[$name], $name, true);

                        if (!empty($sql) && is_string($sql)) {
                            $zipDB->addString('indexes_and_foreign_keys_' .$name . '.sql', $sqlIndexAndForeignKeys);
                        }
                        else {
                            \Log::error(['App\Http\Controllers\backupDB(Request $request) - no dump indexes and foreign keys for table', $name, is_string($sqlIndexAndForeignKeys)]);
                        }
                    }

                    unset($tables[$id]);
                    $executionTime = microtime(true);
                    $loopTime = $executionTime - $foreachStartTime;

                    if ((($executionTime - $executionStartTime) + $beforeForeachStartTime + $loopTime + $this->waitingTime) > $maxExecutionTime) {
                        $status = false;
                        break;
                    }
                }

                $zipDB->close();

                $response['status'] = $status;
                $response['backupId'] = $this->backupId;
                $response['backupName'] = $this->backupName;

                if (count((array)$tables) > 0) {
                    $response['list'] = $tables;
                }
                else {
                    $zip = $this->getOrCreateZip($request);
                    $zip->folder($this->backupName)->add(public_path('tmp' . DIRECTORY_SEPARATOR . '_db.zip'));
                    $zip->close();
                    $fileSystem = new Filesystem();
                    $fileSystem->delete(public_path('tmp' . DIRECTORY_SEPARATOR . '_db.zip'));
                    $response['status'] = true;
                }
            }
            catch (\Exception $e) {
                if (!$request->systemBackup) {
                    clear_tmp();
                }
                $error = $e->getMessage();
                \Log::error(['App\Http\Controllers\backupDB(Request $request)', $error]);
            }
        }
        elseif ($request->type == 'files') {
            $response['status'] = true;
        }

        if (!$request->systemBackup) {
            return response()->json($response);
        }
        else {
            return $response;
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function addFilesToZip(Request $request)
    {
        $executionStartTime = microtime(true);
        $maxExecutionTime = get_ini_time($this->timeOut, $this->isCron);
        $this->getOrCreateZip($request, false);
        $response = ['status' => false, 'type' => $this->type];

        if (in_array($this->type, ['full', 'system', 'files', 'custom', 'subscription']) && $request->type != '_db') {
            $fileSystem = new Filesystem();
            $status = true;

            if (config('eto.multi_subscription') || $request->type == 'subscription') {
                $list = $this->backupListMultiSubscription;
            } else if ($request->type == 'system') {
                $list = $request->list;
            } else {
                if (count((array)$request->dirList) > 0) {
                    $this->backupListDir = $request->dirList;
                }

                if (count((array)$request->fileList) > 0) {
                    $this->backupListFiles = $request->fileList;
                }

                $list = array_merge($this->backupListDir, $this->backupListFiles);
            }

            $list = $request->list ? $request->list : $list;

            if (empty($list)) { $list = []; }

            try {
                $zipTimestampPath = str_replace(['.',' '], '', 'tmp' . DIRECTORY_SEPARATOR . 'app_'.microtime()).'.zip';
                $zipApp = \Zipper::make($zipTimestampPath);
                $beforeForeachStartTime = microtime(true) - $executionStartTime;
                foreach ($list as $id=>$item) {
                    $foreachStartTime = microtime(true);
                    if ($fileSystem->exists(public_path($item))) {
                        if ($fileSystem->isDirectory(public_path($item))) {
                            $zipApp->folder(basename($item))->add($item);
                        }
                        elseif ($fileSystem->isDirectory(public_path(dirname($item))) && strpos($item, "/") !== false) {
                            $item = parse_path($item, 'real');
                            $zipApp->folder(dirname($item))->add($item);
                        }
                        else {
                            $zipApp->home()->add($item);
                        }
                    }
                    unset($list[$id]);
                    $executionTime = microtime(true);
                    $loopTime = $executionTime - $foreachStartTime;

                    if ((($executionTime - $executionStartTime) + $beforeForeachStartTime + $loopTime + $this->waitingTime) > $maxExecutionTime) {
                        $status = false;
                        break;
                    }
                }

                $zipApp->close();
                if ($fileSystem->exists(public_path($zipTimestampPath))) {
                    $zip = $this->getOrCreateZip($request);
                    $zip->folder($this->backupName)->add(public_path($zipTimestampPath));
                    $zip->close();
                    $fileSystem->delete(public_path($zipTimestampPath));
                }
                $response['status'] = $status;
                $response['backupId'] = $this->backupId;
                $response['backupName'] = $this->backupName;

                if (count((array)$list) > 0) {
                    $response['list'] = $list;
                }
            }
            catch (Exception $e) {
                if (!$request->systemBackup) {
                    clear_tmp();
                }
                $error = $e->getMessage();
                \Log::error(['addFilesToZip(Request $request)', $error]);
            }
        }
        else {
            $response['status'] = true;
        }

        if (!$request->systemBackup) {
            return response()->json($response);
        }
        else {
            return $response;
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function addVendorToZip(Request $request)
    {
        $executionStartTime = microtime(true);
        $maxExecutionTime = get_ini_time($this->timeOut, $this->isCron);
        $this->getOrCreateZip($request, false);
        $response = ['status' => false, 'type' => $this->type];

        if (in_array($this->type, ['full', 'system', 'files', 'custom', 'subscription']) && $request->type != '_db') {
            $fileSystem = new Filesystem();
            $vendor = !config('eto.multi_subscription') && $request->type != 'subscription' ? ($request->vendor ?: $fileSystem->directories(public_path('vendor'))) : [];
            $status = true;

            try {
                if (!config('eto.multi_subscription') && $request->type != 'subscription') {
                    $zipTimestampPath = str_replace(['.', ' '], '', 'tmp' . DIRECTORY_SEPARATOR . 'vendor_' . microtime()) . '.zip';
                    $zipVendor = \Zipper::make($zipTimestampPath);

                    $beforeForeachStartTime = microtime(true) - $executionStartTime;
                    foreach ($vendor as $id => $item) {
                        $foreachStartTime = microtime(true);
                        if ($fileSystem->exists($item)) {
                            $zipVendor->folder('vendor' . DIRECTORY_SEPARATOR . basename($item))->add($item);
                        }

                        unset($vendor[$id]);
                        $executionTime = microtime(true);
                        $loopTime = $executionTime - $foreachStartTime;

                        if ((($executionTime - $executionStartTime) + $beforeForeachStartTime + $loopTime + $this->waitingTime + 3) > $maxExecutionTime) {
                            $status = false;
                            break;
                        }
                    }

                    $zipVendor->close();
                    $zip = $this->getOrCreateZip($request);
                    $zip->folder($this->backupName)->add(public_path($zipTimestampPath));
                    $zip->close();
                    $fileSystem->delete(public_path($zipTimestampPath));
                }

                $response['status'] = $status;
                $response['backupId'] = $this->backupId;
                $response['backupName'] = $this->backupName;
                if (count((array)$vendor) > 0) {
                    $response['vendor'] = $vendor;
                } else {
                    $response['status'] = true;
                }
            }
            catch (Exception $e) {
                if (!$request->systemBackup) {
                    clear_tmp();
                }
                $responseCode = $e->getCode();
                $error = $e->getMessage();
                \Log::error(['addVendorToZip(Request $request)', $error, $responseCode]);
            }
        }
        else {
            $response['status'] = true;
        }

        if (!$request->systemBackup) {
            return response()->json($response);
        }
        else {
            return $response;
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function moveBackupZip(Request $request)
    {
        $this->getOrCreateZip($request, false);
        $response = ['status' => false, 'type' => $this->type];

        if (\Storage::disk('tmp')->exists($this->backupName . '.zip')) {
            \Storage::disk('backup_' . $this->disk)->put($this->backupName . '.zip', \Storage::disk('tmp')->get($this->backupName . '.zip'));

            $backup = Backup::find($this->backupId);
            $backup->status = 1;
            $backup->size = \Storage::disk('backup_' . $this->disk)->size($this->backupName . '.zip');
            $backup->save();
            $response['status'] = true;
        }

        if (!$request->systemBackup) {
            clear_tmp();
        }
        else {
            \Storage::disk('tmp')->delete($this->backupName . '.zip');
            \Storage::disk('backup_' . $this->disk)->deleteDirectory( $this->tmpDirectory );
        }

        if (!$request->systemBackup) {
            return response()->json($response);
        }
        else {
            return $response;
        }
    }

    /**
     * @param string $disk
     */
    public function removeSystemBackups($disk = 'local')
    {
        $backups = Backup::where('disk', $disk)
            ->where('type', config('app.backup_system_type'))
            ->where('created_at', '<=', \Carbon\Carbon::now()->subDays(config('app.backup_system_expiry_days'))->toDateTimeString())
            ->orderBy('created_at', 'desc')
            ->get();

        $firstSave = false;

        foreach($backups as $backup) {
            if (!$firstSave) {
                $firstSave = true;
                continue;
            }

            \Storage::disk('backup_' . $backup->disk)->delete( $backup->file . '.zip' );
            if (!\Storage::disk('backup_' . $backup->disk)->exists($backup->file . '.zip')) {
                $backup->delete();
            }
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id) {
        $backup = Backup::find($id);
        if ($backup) {
            if (\Storage::disk('backup_' . $backup->disk)->exists($backup->file . '.zip')) {
                $filecontent = \Storage::disk('backup_' . $backup->disk)->get($backup->file . '.zip'); // read file content
                // download file.
                return \Response::make($filecontent, '200', array(
                    'Content-Type' => 'application/octet-stream',
                    'Content-Disposition' => 'attachment; filename="'.$backup->file.'.zip"'
                ));
            }
        }
        return back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id) {
        if (!auth()->user()->hasPermission('admin.backups.destroy')) {
            return redirect_no_permission();
        }

        $backup = Backup::find($id);

        if ($backup) {
            if ((int)$backup->status === 1 && \Storage::disk('backup_' . $backup->disk)->exists($backup->file . '.zip')) {
                \Storage::disk('backup_' . $backup->disk)->delete($backup->file . '.zip');
            }
            $backup->delete();
        }
        return back();
    }

    /**
     * @param $zip
     * @return mixed
     */
    private function listTables()
    {
        $col = 'Tables_in_' . config('database.connections.mysql.database');
        $result = \DB::select('SHOW FULL TABLES WHERE `Table_Type` = "BASE TABLE" AND `'.$col.'` LIKE "'. $this->escape_like(eto_config('DB_PREFIX')) . '%"');

        foreach ($result as $table) {
            $count = \DB::select("SELECT COUNT(*) as `count` FROM `". $table->$col ."`");
            $count = $count[0]->count;
            if ($count > $this->limit) {
                $offset = 0;
                for ($i = 0; $i < ($count / $this->limit); $i++) {
                    $tables[] = $table->$col .'_offset_' . $offset;
                    $offset = $offset + $this->limit;
                }
            }
            else {
                $tables[] = $table->$col . $this->tableDelimiter . 0;
            }
        }
        return $tables;
    }

    /**
     * @param $search
     * @return mixed
     */
    public function escape_like($search) {
        return str_replace(array('_', '%'), array('\_', '\%'), $search);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function recoveryExtract($id) {
        if (!auth()->user()->hasPermission('admin.backups.recovery')) {
            return redirect_no_permission();
        }

        $request = request();

        if (empty($request->license) || (!empty($request->license) && $request->system->subscription->license != $request->license)) {
            return response()->json(['status' => false, 'message'=> trans('installer.invalid_license_key')]);
        }

        $response = ['status' => false];
        $fileSystem = new Filesystem();
        $backup = Backup::find($id);

        if (\Storage::disk('backup_' . $backup->disk)->exists($backup->file . '.zip')) {
            if ($backup->disk != $this->localDisk) {
                \Storage::disk('tmp')->put($backup->file . '.zip', \Storage::disk('backup_' . $backup->disk)->get($backup->file . '.zip'));
                $zip = Zip::open(public_path('tmp' . DIRECTORY_SEPARATOR . $backup->file . '.zip'));
            }
            else {
                $zip = Zip::open(public_path('backups' . DIRECTORY_SEPARATOR . $backup->file . '.zip'));
            }

            $zip->extract(public_path('tmp'));
            $zip->close();

            if ($fileSystem->exists(public_path('tmp' . DIRECTORY_SEPARATOR . $backup->file))) {
                if ($backup->disk != $this->localDisk) {
                    \Storage::disk('tmp')->delete($backup->file . '.zip');
                }
                $response['status'] = true;
                $response['backup'] = $backup;

                maintenance_mode('block', 'Recovery - '.$backup->name.' - '.$backup->created_at);
                $fileSystem->put(public_path('/tmp/recovery-progres.json'), \GuzzleHttp\json_encode(['backup' => $backup, 'status' => 'extracted', 'mode' => 'system']));
            }
        }
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @param bool $deleteOrigin
     * @return \Illuminate\Http\JsonResponse
     */
    public function copyBackupToDisk(Request $request, $deleteOrigin = false) {
        if (!auth()->user()->hasPermission('admin.backups.create')) {
            return redirect_no_permission();
        }

        set_time_limit(500);
        ini_set('max_execution_time', 500);

        $response = ['status' => false];
        if ($request->backupDisk) {
            $disk = $request->backupDisk;
            $backup = Backup::find($request->id);

            if (\Storage::disk('backup_' . $disk)->put($backup->file . '.zip', \Storage::disk('backup_' . $backup->disk)->get($backup->file . '.zip'))) {
                if ($deleteOrigin === true) {
                    \Storage::disk('backup_' . $backup->disk)->delete($backup->file . '.zip');
                    $backup->disk = $disk;

                    $backup->save();
                    $response['status'] = true;
                }
                else {
                    $copy = new \App\Models\Backup();
                    $copy->file = $backup->file;
                    $copy->disk = $disk;
                    $copy->name = $backup->name;
                    $copy->type = $backup->type;
                    $copy->size = $backup->size;
                    $copy->comments = $backup->comments;
                    $copy->status = $backup->status;
                    $copy->parent_id = $backup->id;

                    $copy->save();
                    $response['status'] = true;
                }
            }
        }
        else {
            $response['message'] = '';
        }

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveBackupToDisk(Request $request) {
        if (!auth()->user()->hasPermission('admin.backups.move')) {
            return redirect_no_permission();
        }

        return $this->copyBackupToDisk($request, true);
    }
}
