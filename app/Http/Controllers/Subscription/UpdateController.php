<?php

namespace App\Http\Controllers\Subscription;

// use App\Models\Backup;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;

class UpdateController extends SubscriptionController
{
    public function getUpdateArchive(Request $request) {
        $maxExecutiontime = get_ini_time();
        clear_cache();

        $responseUpdate = [
            'status' => false,
            'type' => $request->type,
            'message' => trans('subscription.message.failed')
        ];
        $update = [];

        if (\Storage::disk('tmp')->exists('update.json')) {
            $fileTime = \Storage::disk('tmp')->lastModified('update.json');
            if (strtotime("+5 minutes", $fileTime) < time()) {
                $update = \Storage::disk('tmp')->get('update.json');
                if (!empty($update)) {
                    $update = json_decode($update, true);
                }
            }
        }

        if (!empty($update['folder'])) {
            $responseUpdate['status'] = true;
            $responseUpdate = array_merge($responseUpdate, $update);
            if ($responseUpdate['process'] == 'extract') {
                unset($responseUpdate['list']);
            }
        }
        else {
            $response = $this->connectApi($request, 'update', $request->type);

            if (empty($response->headers->fileName)) {
                $body = !empty($response->body) ? json_decode($response->body) : null;
                if (!empty($body->status) && $body->status == 'update_at_expired') {
                    $responseUpdate['message'] = $body->message;
                }
            }
            elseif ($response !== false) {
                \Storage::disk('tmp')->put($response->headers->fileName, $response->body);
                $filename = str_replace('.zip', '', $response->headers->fileName);

                if (\Storage::disk('tmp')->exists($filename . '.zip')) {
                    $responseUpdate['status'] = true;
                    $responseUpdate['folder'] = $filename;
                    $responseUpdate['process'] = 'extract';
                    $responseUpdate['message'] = trans('subscription.update.extract_archive');
                }
            }
        }

        return response()->json($responseUpdate, 200);
    }

    public function extractUpdateArchive(Request $request) {
        $zip = new \App\Helpers\EtoZipArchive();
        $zipFile = parse_path('tmp'. DIRECTORY_SEPARATOR . $request->folder . '.zip', 'public');
        $zipDestination = parse_path('tmp' . DIRECTORY_SEPARATOR, 'public');
        $response = $update = ['folder' => $request->folder, 'process' => 'extract'];
        $response['status'] = false;

        if ($zip->getZip($zipFile) === TRUE) {
            if (\Storage::disk('tmp')->exists('update.json')) {
                $update = \Storage::disk('tmp')->get('update.json');
                if (!empty($update)) {
                    $update = json_decode($update, true);
                }
            }

            if (empty($update['list'])) {
                $update['list'] = $zip->listFiles('^'.$request->folder.'/\.)+');
            }

            $update['list'] = $zip->extractSubdirArrayTo($zipDestination, $update['list']);
            $zip->close();

            if (empty($update['list'])) {
                $response['status'] = true;
                $response['process'] = $update['process'] = 'get-changes';
                $response['message'] = $update['message'] = trans('subscription.update.get_changes');
                unset($update['list']);
                \Storage::disk('tmp')->delete($request->folder . '.zip');
            }
            else {
                $response['list'] = count($update['list']);
            }
            \Storage::disk('tmp')->put('update.json', json_encode($update));
        }
        return response()->json($response, 200);
    }

    public function getListChanges(Request $request) {
        $responseUpdate = ['status' => false, 'message' => trans('subscription.message.failed')];
        $update = [];

        if (\Storage::disk('tmp')->exists('update.json')) {
            $update = \Storage::disk('tmp')->get('update.json');
            if (!empty($update)) {
                $update = json_decode($update, true);
            }
        }

        if (!empty($update['folder']) && \Storage::disk('tmp')->exists($update['folder'])) {
            if (\Storage::disk('tmp')->exists($update['folder'] . '/dump.json')) {
                $json = \Storage::disk('tmp')->get($update['folder'] . '/dump.json');
                $changes = \GuzzleHttp\json_decode($json, true);

                $request->type = 'system';
                $request->comments = 'Upgrade from ' . config('app.version') . ' to ' . $request->maxVersion;
                if (config('app.backup_system_type') == 'system') {
                    $files = [];

                    if (!empty($changes['delete'])) {
                        foreach ($changes['delete'] as $file) {
                            $files[] = $file;
                        }
                    }

                    if (!empty($changes['modify'])) {
                        foreach ($changes['modify'] as $file) {
                            $files[] = $file;
                        }
                    }

                    if (\Storage::disk('tmp')->exists($update['folder'] . '/updater.php')) {
                        @copy(parse_path('tmp/' . $update['folder'] . '/updater.php'), parse_path('updater.php'));
                    }

                    if (\Storage::disk('tmp')->exists($update['folder'] . '/patches')) {
                        \Storage::disk('root')->makeDirectory('patches', 0755, true, true);
                        $this->recurseCopy(parse_path('tmp/' . $update['folder'] . '/patches'), parse_path('patches'));
                    }

                    $files[] = parse_path('tmp/' . $update['folder'] . '/dump.json', 'real');
                    $request->list = $files;
                }

                if (count($request->list) > 0) {
                    $responseUpdate['status'] = true;
                    $update['list'] = $request->list;
                    $update['process'] = 'backup';

                    $responseUpdate = array_merge($responseUpdate, $update);
                }
            }
            \Storage::disk('tmp')->put('update.json', json_encode($update));
        }
        return response()->json($responseUpdate, 200);
    }

    public function generateBackupAllSteps(Request $request) {
        $responseUpdate = ['status' => false, 'message' => trans('subscription.message.failed')];
        if (\Storage::disk('tmp')->exists('update.json')) {
            $update = \Storage::disk('tmp')->get('update.json');
            if (!empty($update)) {
                $update = json_decode($update, true);
                $request->request->add(['systemBackup' => true]);
                $request->request->add(['type' => 'system']);
                $responseUpdate['folder'] = $request->folder;
                if (empty($request->step) || $request->step == 'db') {
                    $responseUpdate['message'] = trans('subscription.update.backup_files');
                    $request->request->remove('list');
                    $request->request->remove('folder');
                    unset($_POST['folder']);
                    $data = (new \App\Http\Controllers\BackupController())->backupDB($request);

                    if (empty($data['list'])) {
                        $responseUpdate['step'] = 'files';
                    }
                    else {
                        $responseUpdate['step'] = 'db';
                    }

                    $responseUpdate['backup'] = $data;
                    $responseUpdate['process'] = $request->process;
                }
                else if ($request->step == 'files') {
                    $responseUpdate['message'] = trans('subscription.update.backup_db');
                    $request->request->add(['list' => $update['list']]);
                    $data = (new \App\Http\Controllers\BackupController())->addFilesToZip($request);
                    $responseUpdate['backup'] = $data;
                    if (empty($data['list'])) {
                        $responseUpdate['process'] = 'move_backup';
                        $responseUpdate['step'] = 'move_backup';
                    }
                }
                else if ($request->step == 'move_backup') {
                    $responseUpdate['message'] = trans('subscription.update.backup_move');
                    $data = (new \App\Http\Controllers\BackupController())->moveBackupZip($request);
                    if ($data['status'] === true) {
                        $responseUpdate['process'] = 'update';
                        $responseUpdate['backup_data'] = $update['backup_data'] = \App\Models\Backup::find($request->backupId);
                        maintenance_mode('block');
                    }
                    else {
                        \Log::error(['generateBackupAllSteps(Request $request) - archive not move to backups']);
                    }
                }

                if ($responseUpdate['process'] == 'update') {
                    unset($update['backup']);
                }
                elseif (isset($data)) {
                    $update['backup'] = $data;
                }

                \Storage::disk('tmp')->put('update.json', json_encode($update));
            }
        }
        return response()->json($responseUpdate, 200);
    }

    private function recurseCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->recurseCopy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
