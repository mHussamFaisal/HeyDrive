<?php

if (!function_exists('format_error')) {
    function format_error($filename, $errline, $errstr, $type, $errno = false) {
        return '[' . date("Y-m-d H:i:s") . '] '
            . eto_env('APP_ENV', 'production') . '.' . $type . ': '
            . ($errno !== false ? $errno . ' ' : '') . "$errstr ($filename:$errline)" . PHP_EOL;
    }
}
if (!function_exists('ETO_error_handler')) {
    function ETO_error_handler($errno, $errstr, $filename, $errline) {
        $logfile_dir = __DIR__ . DS . 'storage' . DS . 'logs';
        $logfile_delete_days = 30;
        $logfile = $logfile_dir . DS . 'eto_updater_' . date('Y_m_d') . '.log';;

        if (!(error_reporting() & $errno)) {
            return false;
        }

        switch ($errno) {
            case E_USER_ERROR:
                file_put_contents($logfile, format_error($filename, $errline, $errstr, 'ERROR', $errno), FILE_APPEND | LOCK_EX);
                exit(1);
            break;
            case E_USER_WARNING:
                file_put_contents($logfile, format_error($filename, $errline, $errstr, 'WARNING'), FILE_APPEND | LOCK_EX);
            break;
            case E_USER_NOTICE:
                file_put_contents($logfile, format_error($filename, $errline, $errstr, 'NOTICE'), FILE_APPEND | LOCK_EX);
            break;
            default:
                file_put_contents($logfile, format_error($filename, $errline, $errstr, 'UNKNOWN'), FILE_APPEND | LOCK_EX);
            break;
        }

        // delete any files older than 30 days
        $files = glob($logfile_dir . "*");
        $now = time();

        foreach ($files as $file) {
            if (is_file($file) && !preg_match('#.gitignore$#', $file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $logfile_delete_days) {
                    unlink($file);
                }
            }
        }

        return true; // Don't execute PHP internal error handler
    }
}

error_reporting(E_ALL|E_NOTICE);
set_error_handler("ETO_error_handler");

//trigger_error("testing 1,2,3", E_USER_NOTICE);

if (!isset($system)) {
    define('PANEL', false);
    set_time_limit(500);
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    $time = $_SERVER['REQUEST_TIME'];
    $timeout_duration = 1800;

    if (isset($_SESSION['LAST_ACTIVITY']) && ($time - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
        session_unset();
        session_destroy();
        session_start();
    }

    $_SESSION['LAST_ACTIVITY'] = $time;
}
if (!empty($etoConfig)) {
    $_SESSION['ETO_CONFIG'] = $etoConfig;
}

define('ETO', true);
define('DS', DIRECTORY_SEPARATOR);
define('LOCAL_DISK', 'local');
define('API_LICENSE_URL', 'https://api.easytaxioffice.com/api/v1/license/');
define('API_DOWNLOAD_URL', 'https://api.easytaxioffice.com/api/v1/download/');
define('SUPER_ADMIN', 1);

if (!function_exists('url')) {
    function url($uri) {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/$uri";
    }
}
if (!function_exists('eto_env')) {
    function eto_env($key, $default = null) {
        $env = [];
        if (!empty($_SESSION['ETO_CONFIG'])) {
            $env = $_SESSION['ETO_CONFIG'];
        } elseif (file_exists(__DIR__ . DS . 'config.php')) {
            $env = include(__DIR__ . DS . 'config.php');
        }
        return !empty($env[$key]) ? $env[$key] : $default;
    }
}
if (!function_exists('eto_config')) {
    function eto_config($key, $default = null) {
        return eto_env($key, $default);
    }
}
if (!function_exists('session')) {
    function session($key = null, $default = null, $set = false) {
        if (is_null($key)) { return $_SESSION; }
        if (is_array($key)) { return !empty($_SESSION[$key]) ? $_SESSION[$key] : null; }
        if ($set) { $_SESSION[$key] = $default; }
        return !empty($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
}
if (!function_exists('dump')) {
    function dump($data, $die = false) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        if ($die) { die(); }
    }
}
if (!function_exists('url_parse')) {
    function url_parse() {
        $url = new stdClass();
        $url->protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $url->host = $_SERVER['HTTP_HOST'];
        $url->uri = (object)parse_url($_SERVER['REQUEST_URI']);
        $url->path = [];
        if (!empty($url->uri->path)) {
            foreach (explode('/', $url->uri->path) as $key => $value) {
                if (!empty($value) && $value != basename(__FILE__)) { $url->path[] = $value; }
            }
        }
        if (!empty($url->uri->query)) {
            parse_str($url->uri->query, $query);
            foreach ($query as $key => $value) {
                $url->$key = $value;
            }
        }
        return $url;
    }
}

session('url', url_parse(), true);
$url = session('url');
$dataBackup = new stdClass();

//if ( file_exists(__DIR__ . DS . 'patches' . DS . 'autoload.php')) {
//    include(__DIR__ . DS . 'patches' . DS . 'autoload.php');
//    $status = ETOPathes::init();
//    if ($status === true) {
//        header("Location: http://{$_SERVER['HTTP_HOST']}/updater.php/patches-delete");
//        exit();
//    }
//}
if (file_exists(__DIR__ . DS . 'tmp' . DS . 'recovery-progres.json')) {
    $dataBackup = json_decode(file_get_contents(__DIR__ . DS . 'tmp' . DS . 'recovery-progres.json'));
}
if (empty($_POST) && empty($_GET) && empty($url->path)) {
    if (!session('license_status')) {
        ETOFileManager::setLicenseFormForbidden();
    }
    else {
        ETOFileManager::displayListBackups();
    }
}
elseif (!empty($url->path) && session('license_status') && empty($_POST['selected_backup'])) {
    if (!empty($url->path[0])) {
        if ($url->path[0] == 'logout') {
            session_destroy();
            ETOFileManager::recoveryComplete(false, false);
            header("Location: http://{$_SERVER['HTTP_HOST']}/updater.php");
            exit();
        }
        elseif ($url->path[0] == 'recovery') {
            ETOFileManager::displayListBackups();
        }
//        elseif ($url->path[0] == 'get-patches') {
//            ETOFileManager::getPatches();
//        }
//        elseif ($url->path[0] == 'patches' && file_exists(__DIR__ . DS . 'patches' . DS . 'autoload.php')) {
//            include(__DIR__ . DS . 'patches' . DS . 'autoload.php');
//            ETOPathes::init();
//        }
//        elseif ($url->path[0] == 'patches-delete') {
//            @unlink(file_exists(__DIR__ . DS . 'patches.php'));
//            ETOFileManager::deleteFileDirectory(__DIR__ . DS . 'patches');
//            @rmdir(__DIR__ . DS . 'patches');
//            ETOFileManager::recoveryComplete(false, false);
//            header("Location: http://{$_SERVER['HTTP_HOST']}/updater.php");
//            exit();
//        }
    }
}
elseif (!empty($_POST['folder']) && is_string($_POST['folder']) && empty($_POST['systemBackup']) ) {
    if(empty($url->path) && session('license_status')) {
        session_destroy();
    }
    ETOFileManager::update($_POST['folder']);
}
elseif (!empty($_POST['set_license']) && is_string($_POST['set_license'])) {
    ETOFileManager::displayListBackups($_POST['set_license']);
}
elseif (!empty($_POST['selected_backup']) && is_string($_POST['selected_backup'])) {
    ETOFileManager::prepareBackup(preg_replace('#.zip$#' , '', $_POST['selected_backup']));
}
elseif (file_exists(__DIR__ . DS . 'storage' . DS . 'framework' . DS . 'down')) {
    if (!empty($_GET['dir']) && !empty($_GET['get']) && $_GET['get'] == 'extract') {
        ETOFileManager::extractAllZip($_GET['dir']);
    }
    elseif (!empty($_GET['dir']) && !empty($_GET['get']) && $_GET['get'] == 'files') {
        ETOFileManager::recoveryFiles($_GET['dir']);
    }
    elseif (!empty($_GET['dir']) && !empty($_GET['get']) && $_GET['get'] == 'db') {
        ETOFileManager::recoveryDb($_GET['dir']);
    }
    elseif (!empty($_GET['dir']) && !empty($_GET['get']) && $_GET['get'] == 'complete') {
        ETOFileManager::recoveryComplete($_GET['dir']);
    }
    elseif ($url->path[0] == 'db-dump' && $_POST['sqlFile']) {
        ETODB::dumpMySQL($_POST['sqlFile']);
    }
}
else {
    if (!isset($system)) {
        ETOFileManager::setLicenseFormForbidden();
    }
}

class ETOFileManager
{
    private static $url = API_LICENSE_URL;

    public static function update($folder = false) {
        $response = ['status' => false];

        if ($folder && file_exists(__DIR__ . DS . 'tmp' . DS . $folder)) {
            $updateFolder = __DIR__ . DS . 'tmp' . DS . $folder;
            $app = __DIR__;

            $json = @file_get_contents($updateFolder . DS . 'dump.json');
            $etoChanges = json_decode($json, true);

            if (!empty($etoChanges['delete'])) {
                foreach ($etoChanges['delete'] as $file) {
                    if (preg_match('#(^backups|^uploads|^storage|^tmp|^updater\.php|^config\.php)#', $file)) {
                        continue;
                    }

                    if (is_dir($app . DS . $file)) {
                        self::deleteFileDirectory($app . DS . $file);
                    }
                    else {
                        @unlink($app . DS . $file);
                    }
                }
            }
            if (!empty($etoChanges['modify'])) {
                foreach ($etoChanges['modify'] as $file) {
                    if (preg_match('#(^backups|^uploads|^storage|^tmp|^updater\.php|^config\.php)#', $file)) {
                        continue;
                    }
                    self::copyFile($updateFolder . DS . $file, $app . DS . $file);
                }
            }
            if (!empty($etoChanges['add'])) {
                foreach ($etoChanges['add'] as $file) {
                    if (preg_match('#(^backups|^uploads|^storage|^tmp|^updater\.php|^config\.php)#', $file)) {
                        continue;
                    }
                    self::copyFile($updateFolder . DS . $file, $app . DS . $file, true);
                }
            }

            self::deleteFileDirectory($updateFolder);

            $response['status'] = true;
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

//    public static function getPatches() {
//        $result = ETOLicenseApi::http_response(API_DOWNLOAD_URL . 'get-patches', ['getHeaders' => true, 'version' => session('version'), 'core_type' => 'eto'], 200);
//
//        if (!empty($result['headers']['content-disposition'])) {
//            $scriptDirectory = __DIR__;
//            $zipName = str_ireplace("filename=", "", stristr($result['headers']['content-disposition'], "filename="));
//            $zipFile = @fopen("$scriptDirectory/$zipName", "w+");
//            $fwrite = @fwrite($zipFile, $result['body']);
//            if (filter_var($fwrite, FILTER_VALIDATE_INT)) {
//                $zippy = new ZipArchive;
//                if ($zippy->open("$scriptDirectory/$zipName") === true) {
//                    $zippy->extractTo($scriptDirectory);
//                    $zippy->close();
//                    unset($zippy);
//                    unset($zipFile);
//                    unset($fwrite);
//                    $removedFilesTotal = self::deleteFileDirectory($scriptDirectory, array($zipName));
//                    if (!filter_var($removedFilesTotal)) {
//                        $error = "Can't delete downloaded ZIP archive.";
//                    }
//                    else {
//                        header("Location: http://{$_SERVER['HTTP_HOST']}/updater.php/patches");
//                        exit();
//                    }
//                }
//                else {
//                    $error = "Can't extract downloaded ZIP archive or write files.";
//                }
//            }
//            else {
//                $error = "Can't extract downloaded ZIP archive or write files.";
//            }
//        }
//    }

    public static function recoveryFiles($backupDir = false, $fromPanel = true) {
        $error = [];
        $backupDir = $backupDir ?: self::find_latest('tmp');
        list($nameWithDate, $time) = explode('__', $backupDir);
        if ($nameWithDate && $time) {
            list($type, $disk, $year, $month, $day) = explode('_', $nameWithDate);

            $backupDir = __DIR__ . DS . 'tmp' . DS . $backupDir;
            $filesList = scandir($backupDir);

            foreach ($filesList as $key => $value) {
                if ($value != "." && $value != ".." && $value != '_db') {
                    try {
                        // if (is_dir(__DIR__ . DS . $value) && $type != 'system') {
                            // self::deleteFileDirectory($backupDir . DS . $value);
                        // }
                        self::copyFileRecursive($backupDir . DS . $value, __DIR__ . DS . $value);
                    }
                    catch (Exception $e) {
                        $responseCode = $e->getCode();
                        $message = $e->getMessage();
                        $error[$value] = [$message, $responseCode];

                        trigger_error('(' .$responseCode . ') ' . $message, E_USER_NOTICE);
                    }
                }
            }
        }
        else {
            $error[$backupDir] = 'No backup data';
        }

        if ($fromPanel === true) {
            header('Content-Type: application/json');
            if (count($error) > 0) {
                echo json_encode(['status' => false, 'fails' => $error]);
            }
            else {
                echo json_encode(['status' => true]);
            }
            exit();
        }
    }
    public static function recoveryDb($backupDir = false, $fromPanel = true) {
        $error = [];
        $backupDir = $backupDir ?: self::find_latest('tmp');

        list($nameWithDate, $time) = explode('__', $backupDir);
        if ($nameWithDate && $time) {
            list($type, $disk, $year, $month, $day) = explode('_', $nameWithDate);
            $backupName = basename($backupDir);
            if ($disk != LOCAL_DISK) {
                $backupSize = filesize( __DIR__ . DS . 'tmp' . DS . $backupDir . '.zip');
            }
            else {
                $backupSize = filesize( __DIR__ . DS . 'backups' . DS . $backupDir . '.zip');
            }

            $backupDir = __DIR__ . DS . 'tmp' . DS . $backupDir;

            if (file_exists($backupDir . DS . '_db')) {
                ETODB::runSql('SET FOREIGN_KEY_CHECKS=0;');

                foreach( new DirectoryIterator($backupDir . DS . '_db') as $file ) {
                    if (preg_match('#.sql$#', $file) && !preg_match('#^indexes_and_foreign_keys_#', $file)) {
                        $sql = file($backupDir . DS . '_db' . DS . $file);
                        $result = ETODB::runSql($sql);

                        if ($result['status'] === false) {
                            $fails = $error['query'] = $result['fails'];
                            trigger_error("recoveryDb: $file ($fails)", E_USER_NOTICE);
                        }
                    }
                }

                foreach( new DirectoryIterator($backupDir . DS . '_db') as $file ) {
                    if (preg_match('#.sql$#', $file) && preg_match('#^indexes_and_foreign_keys_#', $file)) {
                        $sql = file($backupDir . DS . '_db' . DS . $file);
                        $result = ETODB::runSql($sql);

                        if ($result['status'] === false) {
                            $error['query'] = $result['fails'];
                        }
                    }
                }

                ETODB::runSql('SET FOREIGN_KEY_CHECKS=1;');
            }
            if (empty($error['query'])) {
                $prefix = eto_env('DB_PREFIX', 'eto_');
                $result = ETODB::runSql(["UPDATE {$prefix}backups SET status = 1, size = '{$backupSize}' WHERE file = '{$backupName}';"]);

                if ($result['status'] === false) {
                    $error['query'] = $result['fails'];
                }
            }
        }
        else {
            $error[$backupDir] = 'No backup data';
        }

        if ($fromPanel === true) {
            header('Content-Type: application/json');
            if (count($error) > 0) {
                echo json_encode(['status' => false, 'fails' => $error]);
            }
            else {
                echo json_encode(['status' => true]);
            }
            exit();
        }
    }
    public static function recoveryComplete($backupDir = false, $fromPanel = true) {
        if (file_exists(__DIR__ . DS . 'storage' . DS . 'framework' . DS . 'down')) {
            unlink(__DIR__ . DS . 'storage' . DS . 'framework' . DS . 'down');
        }

        if (file_exists(__DIR__ . DS . 'tmp' . DS . 'recovery-progres.json')) {
            unlink(__DIR__ . DS . 'tmp' . DS . 'recovery-progres.json');
        }

//        if ($backupDir) {
//            if (file_exists(__DIR__ . DS . 'tmp' . DS . $backupDir)) {
//                self::deleteFileDirectory(__DIR__ . DS . 'tmp' . DS . $backupDir);
//            }
//            if (file_exists(__DIR__ . DS . 'tmp' . DS . $backupDir . '.zip')) {
//                unlink(__DIR__ . DS . 'tmp' . DS . $backupDir . '.zip');
//            }
//        }

        self::clear_tmp();

        if ($fromPanel === true) {
            header('Content-Type: application/json');
            echo json_encode(['status' => true, 'dir' => $backupDir]);
            exit();
        }
    }
    public static function copyFileRecursive( $path, $dest )
    {
        if ( is_dir($path) ) {
            @mkdir( $dest );
            $objects = scandir($path);
            if ( sizeof($objects) > 0 ) {
                foreach( $objects as $file ) {
                    if ( $file == "." || $file == ".." ) {
                        continue;
                    }

                    if ( is_dir( $path.DS.$file ) ) {
                        self::copyFileRecursive( $path.DS.$file, $dest.DS.$file );
                    }
                    else {
                        copy( $path.DS.$file, $dest.DS.$file );
                    }
                }
            }
            return true;
        }
        elseif ( is_file($path) ) {
            return copy($path, $dest);
        }
        else {
            return false;
        }
    }
    public static function copyFile($fileSource, $fileDestination, $isAdded = false) {
        $fileSource = preg_replace('#' . DS . '+#', DS, $fileSource);
        $fileDestination = preg_replace('#' . DS . '+#', DS, $fileDestination);

        if (file_exists($fileSource)) {
            if (!file_exists(dirname($fileDestination))) {
                @mkdir(dirname($fileDestination), 0755, true);
            }

            if ($isAdded === false) {
                if (is_dir($fileDestination)) {
                    @chmod($fileDestination, 0755);
                }
                else {
                    @chmod($fileDestination, 0644);
                }
            }

            @copy($fileSource, $fileDestination);

            if ($isAdded === true) {
                if (is_dir($fileDestination)) {
                    @chmod($fileDestination, 0755);
                }
                else {
                    @chmod($fileDestination, 0644);
                }
            }
            return true;
        }
        return false;
    }
    public static function setLicenseFormForbidden() {
        header('HTTP/1.0 403 Forbidden');

        $header = "Error 403";
        $content = '<h2>Enter your license code for authorization.</h2>
                    <form method="post" action="" class="license">
                        <fieldset>
                            <div class="form-group row">
                                <label for="set_license" class="col-sm-3 col-form-label text-right">Your
                                    License key</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control form-control-lg" id="set_license"
                                           placeholder="xxxx-xxxx-xxxx-xxxx" name="set_license">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </fieldset>
                    </form>';
        self::content($header, $content);
    }
    public static function find_latest($dir = 'backups')
    {
        $l = 0;
        $r = '';
        foreach( new DirectoryIterator(__DIR__ . DS . $dir) as $file ) {
            $ctime = $file->getCTime();    // Time file was created
            $fname = $file->getFileName(); // File name
            if ( $ctime > $l ) {
                $l = $ctime;
                $r = $fname;
            }
        }
        return $r;
    }

    public static function extractAllZip($file, $fromPanel = true) {
        $error = [];
        $data = json_decode(json_encode(self::recoveryProgres()));

        if ((!empty($data->verifyLicense) && $data->verifyLicense === true) || PANEL) {
//            if (preg_match('#^system_#',$file)) {
                self::extractBackup('backups'  . DS . $file . '.zip');
//            }
            if (file_exists(__DIR__ . DS . 'tmp'  . DS . $file)) {
                foreach (new DirectoryIterator(__DIR__ . DS . 'tmp' . DS . $file) as $zip) {
                    $fname = $zip->getFileName(); // File name
                    try {
                        if (preg_match('#\.zip#', $fname)) {
                            $filePath = 'tmp' . DS . $file . DS . $fname;
                            $destination = 'tmp' . DS . $file;

                            if (preg_match('#\_db.zip#', $fname)) {
                                $destination .= DS . '_db';
                            }

                            if (!self::extractBackup($filePath, $destination)) {
                                $error[$fname] = [$file . DS . $fname, 'tmp' . DS . $file];
                            }
                            else {
                                @unlink(__DIR__ . DS . $filePath);
                            }
                        }
                    }
                    catch (Exception $e) {
                        $responseCode = $e->getCode();
                        $message = $e->getMessage();
                        $error[$fname] = [$message, $responseCode];

                        trigger_error('(' . $responseCode . ') ' . $message, E_USER_NOTICE);
                    }
                }
            }
            else {
                $error['no_extract'] = "ZIP archive ($file.zip) was not unpacked correctly, the process was aborted.";
                trigger_error($error['no_extract'], E_USER_NOTICE);
            }
        }

        if (PANEL === false) {
            header('Content-Type: application/json');
            if (count($error) > 0) {
                echo json_encode(['status' => false, 'fails' => $error]);
            }
            else {
                echo json_encode(['status' => true]);
            }
            exit();
        }
    }
    public static function prepareBackup($file) {
        $data = json_decode(json_encode(self::recoveryProgres()));

        if (!empty($data->verifyLicense) && $data->verifyLicense === true) {
            $type = 'full';
            list($nameWithDate, $time) = explode('__', $file);

            if ($nameWithDate && $time) {
                list($type, $disk, $year, $month, $day) = explode('_', $nameWithDate);
            }

            self::extractAllZip($file);

            if ($type != 'files') {
                self::recoveryDb($file, false);
            }
            if ($type != 'db') {
                self::recoveryFiles($file, false);
            }

            self::recoveryComplete($file, false);
        }

        session_destroy();
        header("Location: http://".$_SERVER['HTTP_HOST'].'/subscription');
        exit();
    }
    private static function recoveryProgres($data = [], $set = false) {
        if (file_exists(__DIR__ . DS . 'tmp' . DS . 'recovery-progres.json')) {
            $dataBackup = json_decode(file_get_contents(__DIR__ . DS . 'tmp' . DS . 'recovery-progres.json'), true);
            if (is_array($dataBackup)) {
                $data = array_merge($dataBackup, $data);
            }
        }
        if ($set) {
            file_put_contents(__DIR__ . DS . 'tmp' . DS . 'recovery-progres.json', json_encode($data));
        }
        return $data;
    }
    private static function extractBackup($file, $destination = 'tmp') {
        $zip = new ETOZipArchive;

        if ($zip->open(__DIR__ . DS . $file) === true) {
            $zip->extractTo(__DIR__ . DS . $destination);
            $zip->close();
            unset($zip);
            return true;
        }
        else {
            return false;
        }
    }

    private static function listBackups() {
        $files = [];
        foreach( new DirectoryIterator(__DIR__ . DS . 'backups') as $file ) {
            $ctime = $file->getCTime();    // Time file was created
            $fname = $file->getFileName(); // File name
            if (preg_match('#.zip$#', $fname)) {
                if (SUPER_ADMIN === 0 && !preg_match('#^full#', $fname)) {
                    continue;
                }
                $files[$ctime] = [
                    'path' => __DIR__ . DS . 'backups' . DS . $file,
                    'name' => $fname,
                    'date' => date('d/m/Y H:i:s', $ctime),
                ];
            }
        }
        return $files;
    }

    public static function displayListBackups($license = false) {
        if (!session('license_status')) {
            self::verifyLicense($license);
        }

        if (session('license_status')) {
            if (!file_exists(__DIR__ . DS . 'storage' . DS . 'framework' . DS . 'down')) {
                file_put_contents(__DIR__ . DS . 'storage' . DS . 'framework' . DS . 'down', 'Recovery from updater.php');
            }

            $dataBackup = json_decode(json_encode(self::recoveryProgres()));

            if (empty($dataBackup)) {
                $dataBackup = new stdClass();
            }

            $dataBackup->mode = 'user';
            $dataBackup->verifyLicense = true;

            self::recoveryProgres(json_decode(json_encode($dataBackup), true), true);

            $backups = self::listBackups();
            $header = "Select backup";

            $content = '<h2>_________________________________________</h2>
                            <form method="post" action="" class="backup">
                                <fieldset>
                                ';
            if (count($backups) > 0) {
                foreach ($backups as $backup) {
                    $content .= '
                        <div class="form-group row">
                            <label for="'. $backup['path'].'"  class="col-sm-11 col-form-label text-right">
                                ' . $backup['name'] . ' - date: ' . $backup['date'] . '</label>
                            <div class="col-sm-1" style="display: inline;">
                                <input id="'. $backup['path'].'" type="radio" class="form-control form-control-lg selected_backup" name="selected_backup" value="' . $backup['name'] . '" />
                            </div>
                        </div>';
                }

                $content .= '<button type="submit" class="btn btn-primary">Submit</button>
                                </fieldset>
                            </form>';
            }
            else {
                $content .= '<div class="form-group row">
                                  <div class="col-sm-12" style="display: inline;">
                                      You don\'t have any backups
                                  </div>
                              </div>
                              </fieldset>
                            </form>';
            }
            self::content($header, $content);
        }
    }
    public static function verifyLicense($license) {
        $result = ETOLicenseApi::http_response(self::$url . 'check-install-core', ['license_key' => $license, 'installation_type' => 'core', 'core_type' => 'eto'], 200);

        if (!empty($result->status) && $result->status == 'OK') {
            $config = include(__DIR__ . DS . 'config' . DS . 'app.php');
            session('license_status', true, true);
            session('license_code', $license, true);
            session('version', !empty($config['version']) ? $config['version'] : null, true);
        }
    }
    private static function content($header, $content)
    {
        if (PANEL) {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <title>ETO Updater</title>
                <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet"
                      integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
                      crossorigin="anonymous">
                <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/cerulean/bootstrap.min.css"
                      rel="stylesheet"
                      integrity="sha384-C++cugH8+Uf86JbNOnQoBweHHAe/wVKN/mb0lTybu/NZ9sEYbd+BbbYtNpWYAsNP"
                      crossorigin="anonymous">
                <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"
                      rel="stylesheet"
                      integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN"
                      crossorigin="anonymous">
                <style>
                body {
                    padding-top: 120px
                }
                pre {
                    background: #f7f7f9
                }
                @media (min-width: 768px) {
                    body > .navbar-transparent {
                        box-shadow: none
                    }
                    body > .navbar-transparent .navbar-nav > .open > a {
                        box-shadow: none
                    }
                }
                #home, #help {
                    font-size: 0.9rem
                }
                #home .navbar, #help .navbar {
                    background: #349aed;
                    background: linear-gradient(145deg, #349aed 50%, #34d8ed 100%);
                    transition: box-shadow 200ms ease-in
                }
                #home .navbar-transparent, #help .navbar-transparent {
                    background: none !important;
                    box-shadow: none
                }
                #home .navbar-brand .nav-link, #help .navbar-brand .nav-link {
                    display: inline-block;
                    margin-right: -30px
                }
                #home .navbar-brand img, #help .navbar-brand img {
                    display: inline-block;
                    margin: 0 10px;
                    width: 30px
                }
                #home .nav-link, #help .nav-link {
                    text-transform: uppercase;
                    font-weight: 500;
                    color: #fff
                }
                #home {
                    padding-top: 0px
                }
                #home .btn {
                    padding: 0.6rem 0.55rem 0.5rem;
                    box-shadow: none;
                    font-size: 0.7rem;
                    font-weight: 500
                }
                .bs-docs-section {
                    margin-top: 4em
                }
                .bs-docs-section .page-header h1 {
                    padding: 2rem 0;
                    font-size: 3rem
                }
                .dropdown-menu.show[aria-labelledby="themes"] {
                    display: flex;
                    width: 420px;
                    flex-wrap: wrap
                }
                .dropdown-menu.show[aria-labelledby="themes"] .dropdown-item {
                    width: 33.333%
                }
                .dropdown-menu.show[aria-labelledby="themes"] .dropdown-item:first-child {
                    width: 100%
                }
                .bs-component {
                    position: relative
                }
                .bs-component + .bs-component {
                    margin-top: 1rem
                }
                .bs-component .card {
                    margin-bottom: 1rem
                }
                .bs-component .modal {
                    position: relative;
                    top: auto;
                    right: auto;
                    left: auto;
                    bottom: auto;
                    z-index: 1;
                    display: block
                }
                .bs-component .modal-dialog {
                    width: 90%
                }
                .bs-component .popover {
                    position: relative;
                    display: inline-block;
                    width: 220px;
                    margin: 20px
                }
                #source-button {
                    position: absolute;
                    top: 0;
                    right: 0;
                    z-index: 100;
                    font-weight: bold
                }
                #source-modal pre {
                    max-height: calc(100vh - 11rem);
                    background-color: rgba(0, 0, 0, 0.7);
                    color: rgba(255, 255, 255, 0.7)
                }
                .nav-tabs {
                    margin-bottom: 15px
                }
                .progress {
                    margin-bottom: 10px
                }
                #footer {
                    margin: 5em 0
                }
                #footer li {
                    float: left;
                    margin-right: 1.5em;
                    margin-bottom: 1.5em
                }
                #footer p {
                    clear: left;
                    margin-bottom: 0
                }
                .splash {
                    padding: 12em 0 6em;
                    background: #349aed;
                    background: linear-gradient(145deg, #349aed 50%, #34d8ed 100%);
                    color: #fff;
                    text-align: center
                }
                .splash .logo {
                    width: 160px
                }
                .splash h1 {
                    font-size: 3em;
                    color: #fff
                }
                .splash #social {
                    margin: 2em 0 3em
                }
                .splash .alert {
                    margin: 2em 0;
                    border: none
                }
                .splash .sponsor a {
                    color: #fff
                }
                .section-tout {
                    padding: 6em 0 1em;
                    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                    background-color: #eaf1f1
                }
                .section-tout .fa {
                    margin-right: 0.2em
                }
                .section-tout p {
                    margin-bottom: 5em
                }
                .section-preview {
                    padding: 4em 0 4em
                }
                .section-preview .preview {
                    margin-bottom: 4em;
                    background-color: #eaf1f1
                }
                .section-preview .preview img {
                    max-width: 100%
                }
                .section-preview .preview .image {
                    position: relative
                }
                .section-preview .preview .image:before {
                    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.1);
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    content: "";
                    pointer-events: none
                }
                .section-preview .preview .options {
                    padding: 2em;
                    border: 1px solid rgba(0, 0, 0, 0.05);
                    border-top: none;
                    text-align: center
                }
                .section-preview .preview .options p {
                    margin-bottom: 2em
                }
                .section-preview .dropdown-menu {
                    text-align: left
                }
                .section-preview .lead {
                    margin-bottom: 2em
                }
                @media (max-width: 767px) {
                    .section-preview .image img {
                        width: 100%
                    }
                }
                .sponsor img {
                    max-width: 100%
                }
                .sponsor #carbonads {
                    max-width: 240px;
                    margin: 0 auto
                }
                .sponsor .carbon-text {
                    display: block;
                    margin-top: 1em;
                    font-size: 12px
                }
                .sponsor .carbon-poweredby {
                    float: right;
                    margin-top: 1em;
                    font-size: 10px
                }
                @media (max-width: 767px) {
                    .splash {
                        padding-top: 8em
                    }
                    .splash .logo {
                        width: 100px
                    }
                    .splash h1 {
                        font-size: 2em
                    }
                    #banner {
                        margin-bottom: 2em;
                        text-align: center
                    }
                }
                </style>
            </head>
            <body>
            <nav class="navbar navbar-expand-lg  fixed-top navbar-light bg-light">
                <a class="navbar-brand" href="https://easytaxioffice.co.uk/">
                    <img src="https://easytaxioffice.co.uk/wp-content/uploads/eto-logo-new-2.png" alt="EasyTaxiOffice" id="logo" data-height-percentage="100" data-actual-width="230" data-actual-height="52">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor03" aria-controls="navbarColor03" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarColor03">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item"><a class="nav-link" href="https://docs.easytaxioffice.com" target="_blank">Documentation</a>
                        </li>
                        <?php
                        if (session('license_status')) :
                            ?>
                            <li class="nav-item"><a class="nav-link" href="/updater.php/logout">Logout</a></li>
                            <!--                        <li class="nav-item"><a class="nav-link" href="/updater.php/get-patches">Get patches if You have problems</a></li>-->
                        <?php
                        endif;
                        ?>
                    </ul>
                </div>
            </nav>
            <div class="container">
                <div class="bs-docs-section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="page-header"><h1 id="forms"><?= $header ?></h1></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="bs-component"><?php echo $content; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://code.jquery.com/jquery-3.3.1.min.js"
                    integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
                    integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
                    crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"
                    integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o"
                    crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.6/dist/loadingoverlay.min.js"></script>
            <script>
                (function () {
                    $('.license').on('submit', function (e) {
                        // console.log($(this));
                        var license = $('#set_license').val();

                        if ((typeof license != 'undefined' && license.length === 0) || typeof license == 'undefined') {
                            e.preventDefault();
                            $('.eto-error').remove();
                            $(this).prepend('<span class="eto-error" style="color: darkred">Please enter Your license key</span>')
                        } else {
                            $.LoadingOverlay("show", {
                                image: "",
                                fontawesome: "fa fa-cog fa-spin",
                                text: "Please wait, we are prepare data."
                            });
                        }
                    });
                    $('.backup').on('submit', function (e) {
                        // console.log($(this));
                        var file = $('.selected_backup:checked').val();

                        if ((typeof file != 'undefined' && file.length === 0) || typeof file == 'undefined') {
                            e.preventDefault();
                            $('.eto-error').remove();
                            $(this).prepend('<span class="eto-error" style="color: darkred">Please select ZIP to recovery</span>')
                        } else {
                            $.LoadingOverlay("show", {
                                image: "",
                                fontawesome: "fa fa-cog fa-spin",
                                text: "Please wait, we are prepare data."
                            });
                        }
                    });
                })();
            </script>
            </body>
            </html>
            <?php
        } else {
            self::pageNotFound();
        }
    }
    private static function pageNotFound()
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Page not found</title>
            <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
            <style>
                html, body {
                    height: 100%;
                }
                body {
                    margin: 0;
                    padding: 0;
                    width: 100%;
                    color: #B0BEC5;
                    display: table;
                    font-weight: 100;
                    font-family: 'Lato', sans-serif;
                }
                .container {
                    text-align: center;
                    display: table-cell;
                    vertical-align: middle;
                }
                .content {
                    text-align: center;
                    display: inline-block;
                }
                .title {
                    font-size: 50px;
                    margin-bottom: 40px;
                }
                .link {
                    font-size: 24px;
                    margin-bottom: 40px;
                }
                .link a {
                    color: #000;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
        <div class="container">
            <div class="content">
                <div class="title">Page not found</div>
            </div>
        </div>
        </body>
        </html>
        <?php
    }

    public static function deleteFileDirectory($root, $files=null) {
        $removed=0;
        if (!empty($root) && is_dir($root)) {
            if (empty($files)) {$files=scandir($root);}
            $files = array_filter($files);
            $files = array_diff($files, array(".", "..", ""));
            $files = array_values($files);
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (is_file("$root/$file")) {
                        if (unlink("$root/$file")) {
                            $removed++;
                        }
                    }
                    if (is_dir("$root/$file")) {
                        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/$file", FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                            $path->isDir() && !$path->isLink() ? @rmdir($path->getPathname()) : @unlink($path->getPathname());
                        }
                        if (@rmdir("$root/$file")) {$removed++;}
                    }
                }
                if (@rmdir("$root")) {$removed++;}
            }

            // Makes sure empty catalogs are removed
            if (file_exists($root)) {
                $files = scandir($root);
                $files = array_filter($files);
                $files = array_diff($files, array(".", "..", ""));
                $files = array_values($files);
                if (empty($files)) {
                    if (rmdir("$root")) {
                        $removed++;
                    }
                }
            }
        }
        return $removed;
    }

    public static function clear_tmp() {
        self::deleteFileDirectory(__DIR__ . DS . 'tmp');
        if(!file_exists(__DIR__ . DS . 'tmp')) {
            mkdir(__DIR__ . DS . 'tmp');
        }
        file_put_contents(__DIR__ . DS . 'tmp' . DS . '.gitignore', "*\r\n!.gitignore");
    }
}

class ETOLicenseApi
{
    private static $getHeaders = false;
    private static $getInfo = false;

    public static function http_response($url, $data, $status = null, $wait = 3)
    {
        if (!empty($data['getHeaders']) && $data['getHeaders'] === true) {
            self::$getHeaders = true;
        }
        $data['recovery'] = 1;
        $formattedHeaders = $responseData = $information = array();
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        // we are the parent
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);

        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        if (self::$getHeaders) {
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
            curl_setopt($ch, CURLOPT_HEADERFUNCTION,
                function ($curl, $header) use (&$formattedHeaders) {
                    $len = strlen($header);
                    $header = explode(":", $header, 2);
                    if (count($header) < 2) {
                        return $len;
                    }
                    $name = strtolower(trim($header[0]));
                    $formattedHeaders[$name] = trim($header[1]);
                    return $len;
                }
            );
        }
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (self::$getInfo) {
            $information = curl_getinfo($ch);
        }

        curl_close($ch);

        if (!$result) {
            return FALSE;
        }

        try {
            $resultJson = json_decode($result);

            if (null !== $resultJson && $resultJson !== false) {
                $result = $resultJson;
            }
        }
        catch (Exception $e) {
            $responseCode = $e->getCode();
            $message = $e->getMessage();
            $result = (object)['content' => $result];
            trigger_error('(' .$responseCode . ') ' . $message, E_USER_NOTICE);
        }

        if (is_object($result)) {
            $result->httpCode = $httpCode;

            if (self::$getHeaders) {
                $result->information = $information;
                $result->headers = $formattedHeaders;
            }
        }

        if ($status === null) {
            if ($httpCode < 400) {
                return $result;
            }
            else {
                return FALSE;
            }
        }
        elseif ($status == $httpCode) {
            if (is_string($result)) {
                $result = ['body' => $result, 'httpCode' => $httpCode];
                if (self::$getHeaders) {
                    $result['information'] = $information;
                    $result['headers'] = $formattedHeaders;
                }
            }

            return $result;
        }

        return FALSE;
    }
}

class ETODB
{
    public static function getConnectionMysql() {
        $host = eto_env('DB_HOST', 'localhost');
        $userName = eto_env('DB_USERNAME', 'eto');
        $password = eto_env('DB_PASSWORD', '');
        $database = eto_env('DB_DATABASE', 'eto');
        $port = eto_env('DB_PORT', 3306);

        $mysqli = new mysqli($host, $userName, $password, $database, $port);

        if (!mysqli_connect_errno()) {
            return $mysqli;
        }
        return false;
    }

    public static function runSql($sqlScript) {
        $error = [];
        $mysqli = self::getConnectionMysql();

        if ($mysqli && !empty($sqlScript)) {
            $query = '';
            foreach ((array)$sqlScript as $line)	{
                $startWith = substr(trim($line), 0 ,2);
                $endWith = substr(trim($line), -1 ,1);

                if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
                    continue;
                }

                $query = $query . $line;

                if ($endWith == ';') {
                    try {
                        $mysqli->query($query);
                        $query = '';
                    }
                    catch (Exception $e) {
                        $responseCode = $e->getCode();
                        $message = $e->getMessage();
                        $error = [$message, $responseCode];

                        trigger_error('(' .$responseCode . ') ' . $message, E_USER_NOTICE);
                    }
                }
            }

            $mysqli->close();
        }
        else {
            $error[] = 'No connect to DB';
        }

        if (count($error) > 0) {
            return ['status' => false, 'fails' => $error];
        }
        else {
            return ['status' => true];
        }
    }

    public static function select($query) {
        $error = [];
        $mysqli = self::getConnectionMysql();

        if ($mysqli) {
            try {
                $result = $mysqli->query($query);
            }
            catch (Exception $e) {
                $responseCode = $e->getCode();
                $message = $e->getMessage();
                $error = [$message, $responseCode];

                trigger_error('(' .$responseCode . ') ' . $message, E_USER_NOTICE);
            }
        }
        else {
            $error[] = 'No connect to DB';
        }

        if (count($error) > 0) {
            return ['status' => false, 'fails' => $error];
        }
        else {
            $rows = [];
            while ($obj = $result->fetch_object()) {
                $data = new stdClass();
                foreach($obj as $col=>$val) {
                    $data->$col = $val;
                }
                $rows[] = $data;
            }
            return [
                'num_rows' => $result->num_rows,
                'rows' => $rows,
            ];
        }
    }

    public static function dumpMySQL($fileSql = __DIR__, $limit = 1000) {
        $etoTables = [];
        $result = self::select('SHOW TABLES');

        if ($result['num_rows'] > 0) {
            $data = $result['rows'];
            $col = 'Tables_in_' . eto_env('DB_DATABASE');

            foreach ($data as $table) {
                if (preg_match_all('/(^'. eto_env('DB_PREFIX') .')/m', $table->$col)) {
                    $etoTables[] = $table->$col;
                    $count = self::select("SELECT COUNT(*) as `count` FROM `". $table->$col ."`")['rows'];
                    $count = $count[0]->count;

                    if ($count > $limit) {
                        $offset = 0;
                        for($i=0; $i < ($count/$limit); $i++) {
                            self::dumpTable($table->$col, $fileSql . DIRECTORY_SEPARATOR . '_db' . DIRECTORY_SEPARATOR . $table->$col . '_' . microtime() . '.sql', $offset, $limit);
                            $offset = $offset + $limit;
                        }
                    }
                    else {
                        self::dumpTable($table->$col, $fileSql . DIRECTORY_SEPARATOR . '_db' . DIRECTORY_SEPARATOR . $table->$col . '.sql');
                    }
                }
            }
            return true;
        }
        return false;
    }

    public static function dumpTable($table, $offset = false, $limit = 1000, $returnOutput = false) {
        $config = [
            'host' => eto_env('DB_HOST'),
            'username' => eto_env('DB_USERNAME'),
            'password' => eto_env('DB_PASSWORD'),
            'db_name' => eto_env('DB_DATABASE'),
            'include_tables' => is_array($table) ? $table : [$table],
            'return_output' => $returnOutput,
        ];

        if ($offset !== false) {
            $config['include_tables_offset'] = $offset;
            $config['include_tables_limit'] = $limit;
        }

        $dumper = new ETODumper($config);
        return $dumper->dump();
    }

    public static function getIndexAndForeignKeys($table, $returnOutput = false) {
        $config = [
            'host' => eto_env('DB_HOST'),
            'username' => eto_env('DB_USERNAME'),
            'password' => eto_env('DB_PASSWORD'),
            'db_name' => eto_env('DB_DATABASE'),
            'include_tables' => is_array($table) ? $table : [$table],
            'return_output' => $returnOutput,
        ];
        $dumper = new ETODumper($config);
        return $dumper->getIndexAndForeignKeys();
    }

    public static function dumpIndexAndForeignKeys($data, $table, $returnOutput = false) {
        $config = [
            'host' => eto_env('DB_HOST'),
            'username' => eto_env('DB_USERNAME'),
            'password' => eto_env('DB_PASSWORD'),
            'db_name' => eto_env('DB_DATABASE'),
            'return_output' => $returnOutput,
        ];

        $dumper = new ETODumper($config);
        return $dumper->dumpIndexAndForeignKeys($data, $table);
    }
}

class ETOZipArchive extends ZipArchive
{
    /**
     * @param $destination
     * @param $subdirs
     */
    public function extractSubdirArrayTo($destination, $subdirs) {
        foreach($subdirs as $subdir) {
            $this->extractSubdirTo($destination . '/' . $subdir, $subdir);
        }
    }

    /**
     * @param $destination
     * @param $subdir
     * @return array
     */
    public function extractSubdirTo($destination, $subdir)
    {
        $errors = array();

        // Prepare dirs
        $destination = str_replace(array("/", "\\"), DS, $destination);
        $subdir = str_replace(array("/", "\\"), "/", $subdir);

        if (substr($destination, mb_strlen(DS, "UTF-8") * -1) != DS) {
            $destination .= DS;
        }

        if (substr($subdir, -1) != "/") {
            $subdir .= "/";
        }

        // Extract files
        for ($i = 0; $i < $this->numFiles; $i++) {
            $filename = $this->getNameIndex($i);

            if (substr($filename, 0, mb_strlen($subdir, "UTF-8")) == $subdir) {
                $relativePath = substr($filename, mb_strlen($subdir, "UTF-8"));
                $relativePath = str_replace(array("/", "\\"), DS, $relativePath);

                if (mb_strlen($relativePath, "UTF-8") > 0) {
                    if (substr($filename, -1) == "/") { // Directory
                        // New dir
                        if (!is_dir($destination . $relativePath)) {
                            if (!@mkdir($destination . $relativePath, 0755, true)) {
                                $errors[$i] = $filename;
                            }
                        }
                    }
                    else {
                        if (dirname($relativePath) != ".") {
                            if (!is_dir($destination . dirname($relativePath))) {
                                // New dir (for file)
                                @mkdir($destination . dirname($relativePath), 0755, true);
                            }
                        }

                        // New file
                        if (@file_put_contents($destination . $relativePath, $this->getFromIndex($i)) === false) {
                            $errors[$i] = $filename;
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param null $regexFilter
     * @param bool $onlyFilter
     * @return array
     */
    public function listFiles($regexFilter = null, $onlyFilter = true, $regexFilterSkip = null)
    {
        $list = [];

        for ( $i = 0; $i < $this->numFiles; $i++ ) {
            $name = $this->getNameIndex($i);

            if ($regexFilterSkip && preg_match($regexFilterSkip, $name)) {
                continue;
            }

            if ($regexFilter) {
                set_error_handler(function () { });

                preg_match($regexFilter, $name, $matches);
                restore_error_handler();
                if ($matches) {
                    if ($onlyFilter) {
                        array_push($list, $matches[0]);
                    }
                    else {
                        array_push($list, $name);
                    }
                }
            }
            else {
                array_push($list, $name);
            }
        }

        $list = array_unique($list);

        return $list;
    }

    public function extractShell($location, $new_location) {
        if (exec("unzip $location",$arr)) {
            mkdir($new_location);
            $source_dir = dirname($location);
            for($i = 1;$i< count($arr);$i++) {
                $file = trim(preg_replace("~inflating: ~","",$arr[$i]));
                copy($source_dir."/".$file,$new_location."/".$file);
                unlink($source_dir."/".$file);
            }
            return true;
        }
        return false;
    }
}

class ETOSystemExec
{
    public static function hasAccess() {
        if (!is_callable('shell_exec')) {
            return false;
        }
        $disabled_functions = ini_get('disable_functions');
        return stripos($disabled_functions, 'shell_exec') === false;
    }

    public static function isCommandAvailable($command) {
        if (preg_match('~win~i', PHP_OS)) {
            /*
            On Windows, the `where` command checks for availabilty in PATH. According
            to the manual(`where /?`), there is quiet mode:
            ....
                /Q       Returns only the exit code, without displaying the list
                         of matched files. (Quiet mode)
            ....
            */
            $output = array();
            exec('where /Q ' . $command, $output, $return_val);

            if (intval($return_val) === 1) {
                return false;
            }
            else {
                return true;
            }
        }
        else {
            $last_line = exec('which ' . $command);
            $last_line = trim($last_line);

            // Whenever there is at least one line in the output,
            // it should be the path to the executable
            if (empty($last_line)) {
                return false;
            }
            else {
                return true;
            }
        }
    }
}

/**
 * Dumper DB
 */
class ETODumper {
    /**
     * Maximum length of single insert statement
     */
    const INSERT_THRESHOLD = 838860;

    /**
     * @var ETODBConn
     */
    public $db;

    /**
     * file SQL string
     */
    public $sqlString;

    /**
     * End of line style used in the dump
     */
    public $eol = "\r\n";

    /**
     * Specificed tables to include
     */
    public $include_tables = [];
    public $include_tables_offset;
    public $include_tables_limit;
    public $return_output = false;

    /**
     * Factory method for dumper on current hosts's configuration.
     */
    function __construct($db_options) {
        $db = ETODBConn::create($db_options);
        $db->connect();
        $this->db = $db;

        if (isset($db_options['include_tables'])) { $this->include_tables = $db_options['include_tables']; }
        if (isset($db_options['include_tables_offset'])) { $this->include_tables_offset = $db_options['include_tables_offset']; }
        if (isset($db_options['include_tables_limit'])) { $this->include_tables_limit = $db_options['include_tables_limit']; }
        if (isset($db_options['return_output'])) { $this->return_output = $db_options['return_output']; }

        return $this;
    }

    public function dump() {
        $eol = $this->eol;

        $this->sqlString = '';

        $this->write("-- Generation time: " . date('r') . $eol);
        $this->write("-- Host: " . $this->db->host . $eol);
        $this->write("-- DB name: " . $this->db->name . $eol);
        $this->write("/*!40030 SET NAMES UTF8 */;$eol");

        $this->write("/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;$eol");
        $this->write("/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;$eol");
        $this->write("/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;$eol");
        $this->write("/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;$eol");
        $this->write("/*!40103 SET TIME_ZONE='+00:00' */;$eol");
        $this->write("/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;$eol");
        $this->write("/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;$eol");
        $this->write("/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;$eol");
        $this->write("/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;$eol$eol");

        foreach ($this->include_tables as $table) {
            $this->dump_table($table);
        }

        $this->write("/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;$eol");
        $this->write("/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;$eol");
        $this->write("/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;$eol");
        $this->write("/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;$eol");
        $this->write("/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;$eol");
        $this->write("/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;$eol");
        $this->write("/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;$eol$eol");

        return $this->sqlString;
    }

    public function dumpIndexAndForeignKeys($data, $table) {
        $eol = $this->eol;
        $this->sqlString = '';

        $this->write("-- Generation time: " . date('r') . $eol);
        $this->write("-- Host: " . $this->db->host . $eol);
        $this->write("-- DB name: " . $this->db->name . $eol);
        $this->write("/*!40030 SET NAMES UTF8 */;$eol");

        $this->write("/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;$eol");
        $this->write("/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;$eol");
        $this->write("/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;$eol");
        $this->write("/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;$eol");
        $this->write("/*!40103 SET TIME_ZONE='+00:00' */;$eol");
        $this->write("/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;$eol");
        $this->write("/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;$eol");
        $this->write("/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;$eol");
        $this->write("/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;$eol$eol");

        $this->write("ALTER TABLE `$table`$eol");
        $list = [];
        foreach($data['indexes'] as $name=>$indexes) {
            $cols = [];

            if (count((array)$indexes['data']) > 1) {
                foreach ($indexes['data'] as $index) {
                    $cols[] .= "`$index`";
                }
            }
            elseif (!empty($indexes['data'][1])) {
                $cols[] = "`".$indexes['data'][1]."`";
            }
            else {
                continue;
            }

            $cols = implode(',', $cols);

//            if ($name == 'PRIMARY') {
//                $sql = "  ADD PRIMARY KEY ($cols)";
//            } else {
                $key = $indexes['unique'] ? 'UNIQUE KEY' :'KEY';
                $sql = "  ADD $key `$name` ($cols)";
//            }

            if ($indexes['type'] = 'BTREE') {
                $sql .= " USING BTREE";
            }
            $list[] = $sql;
        }

        $sql = implode(','.$eol, $list) . ';' . $eol;
        $this->write($sql.$eol);

        if (!empty($data['foreigns'])) {
            $this->write("ALTER TABLE `$table`$eol");
            $list = [];
            foreach($data['foreigns'] as $name=>$foreigns) {
                $sql = "  ADD CONSTRAINT `$name` FOREIGN KEY (`".$foreigns['column']."`) REFERENCES `".$foreigns['ref_table']."` (`".$foreigns['ref_column']."`)";

                if ($foreigns['update'] != 'RESTRICT') {
                    $sql .= ' ON UPDATE ' . $foreigns['update'];
                }
                if ($foreigns['delete'] != 'RESTRICT') {
                    $sql .= ' ON DELETE ' . $foreigns['delete'];
                }
                $list[] = $sql;
            }

            $sql = implode(','.$eol, $list) . ';' . $eol;
            $this->write($sql.$eol);
        }

        $this->write("/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;$eol");
        $this->write("/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;$eol");
        $this->write("/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;$eol");
        $this->write("/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;$eol");
        $this->write("/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;$eol");
        $this->write("/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;$eol");
        $this->write("/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;$eol$eol");

        return $this->sqlString;
    }

    public function getIndexAndForeignKeys() {
        // $indexes = \ETODB::select("SELECT `TABLE_NAME`, `INDEX_NAME`, `COLUMN_NAME`, `SEQ_IN_INDEX`, `INDEX_TYPE`, `NON_UNIQUE`
        //        FROM `INFORMATION_SCHEMA`.`STATISTICS`
        //        WHERE `TABLE_SCHEMA` = '". eto_env('DB_DATABASE') ."' AND `INDEX_NAME` != 'PRIMARY' ");
        $indexes = \ETODB::select("SELECT `TABLE_NAME`, `INDEX_NAME`, `COLUMN_NAME`, `SEQ_IN_INDEX`, `INDEX_TYPE`, `NON_UNIQUE`
                FROM `INFORMATION_SCHEMA`.`STATISTICS`
                WHERE `TABLE_SCHEMA` = '". eto_env('DB_DATABASE') ."' ");
        $tableKeys = [];

        if ($indexes['num_rows'] > 0) {
            $rows = $indexes['rows'];

            foreach ($rows as $row) {
                $tableKeys[$row->TABLE_NAME]['indexes'][$row->INDEX_NAME]['data'][$row->SEQ_IN_INDEX] = $row->COLUMN_NAME;
                $tableKeys[$row->TABLE_NAME]['indexes'][$row->INDEX_NAME]['type'] = $row->INDEX_TYPE;
                $tableKeys[$row->TABLE_NAME]['indexes'][$row->INDEX_NAME]['unique'] = (int)$row->NON_UNIQUE == 0;
            }

            $foreigns = \ETODB::select("SELECT
                  `a`.`TABLE_NAME`,
                  `a`.`COLUMN_NAME`,
                  `a`.`CONSTRAINT_NAME`,
                  `a`.`REFERENCED_TABLE_NAME`,
                  `a`.`REFERENCED_COLUMN_NAME`,
                  `b`.`UPDATE_RULE`,
                  `b`.`DELETE_RULE`
              FROM
                  `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` `a`
              LEFT JOIN `information_schema`.`REFERENTIAL_CONSTRAINTS` `b`
              ON
                  `a`.`CONSTRAINT_NAME` = `b`.`CONSTRAINT_NAME`
              WHERE
                  `a`.`REFERENCED_TABLE_SCHEMA` = '".eto_env('DB_DATABASE')."'
                  AND `a`.`TABLE_NAME` LIKE '".eto_env('DB_PREFIX')."%'
                  AND `a`.`REFERENCED_TABLE_NAME` LIKE '%';");

            if ($foreigns['num_rows'] > 0) {
                $rows = $foreigns['rows'];

                foreach ($rows as $row) {
                    $tableKeys[$row->TABLE_NAME]['foreigns'][$row->CONSTRAINT_NAME] = [
                        'column' => $row->COLUMN_NAME,
                        'ref_table' => $row->REFERENCED_TABLE_NAME,
                        'ref_column' => $row->REFERENCED_COLUMN_NAME,
                        'update' => $row->UPDATE_RULE,
                        'delete' => $row->DELETE_RULE,
                    ];
                }
            }
        }

        return $tableKeys;
    }

    protected function dump_table($table) {
        $eol = $this->eol;

        if (empty($this->include_tables_offset) || $this->include_tables_offset === 0) {
            $this->write("DROP TABLE IF EXISTS `$table`;$eol");
            $this->write($this->get_create_table_sql($table) . $eol . $eol);
        }

        $guery = "SELECT * FROM `$table`";

        if ($this->include_tables_offset !== null && !empty($this->include_tables_limit)) {
            $guery .= "LIMIT {$this->include_tables_offset}, {$this->include_tables_limit}";
        }

        $data = $this->db->query($guery);
        $insert = new ETOInsert_Statement($table);

        while ($row = $this->db->fetch_row($data)) {
            $row_values = array();
            foreach ($row as $value) {
                $row_values[] = $this->db->escape($value);
            }
            $insert->add_row( $row_values );

            if ($insert->get_length() > self::INSERT_THRESHOLD) {
                // The insert got too big: write the SQL and create
                // new insert statement
                $this->write($insert->get_sql() . $eol);
                $insert->reset();
            }
        }

        $sql = $insert->get_sql();
        if ($sql) {
            $this->write($insert->get_sql() . $eol);
        }
        $this->write($eol);
    }

    protected function get_create_table_sql($table) {
        $create_table_sql = $this->db->fetch('SHOW CREATE TABLE `'. $table .'`');
        $sql = [];

        foreach(preg_split("/((\r?\n)|(\r\n?))/", $create_table_sql[0]['Create Table']) as $line) {
            // do stuff with $line
            if (preg_match('#^\s+PRIMARY KEY#', $line)) {
                $line = preg_replace('#,$#', '', $line);
            }
            else if (preg_match('#^\s+(UNIQUE KEY|KEY|CONSTRAINT)#', $line)) {
                continue;
            } elseif (preg_match('#^\) ENGINE#', $line) && !preg_match('#^\s+PRIMARY KEY#', $sql[count($sql)-1])) {
                $sql[count($sql)-1] = preg_replace('#,$#', '', $sql[count($sql)-1]);
            }
            $sql[] = $line;
        }

        return implode($this->eol, $sql) . ';';
    }

    function write($string) {
        $this->sqlString = $this->sqlString . $string;
        return $this->sqlString;
    }
}

/**
 * MySQL insert statement builder.
 */
class ETOInsert_Statement {
    private $rows = array();
    private $length = 0;
    private $table;

    function __construct($table) {
        $this->table = $table;
    }

    function reset() {
        $this->rows = array();
        $this->length = 0;
    }

    function add_row($row) {
        $row = '(' . implode(",", $row) . ')';
        $this->rows[] = $row;
        $this->length += strlen($row);
    }

    function get_sql() {
        if (empty($this->rows)) {
            return false;
        }
        return 'INSERT INTO `'. $this->table .'` VALUES '. implode(",\n", $this->rows) .'; ';
    }

    function get_length() {
        return $this->length;
    }
}

class ETODBConn {
    public $host;
    public $username;
    public $password;
    public $name;

    protected $connection;

    function __construct($options) {
        $this->host = $options['host'];
        if (empty($this->host)) {
            $this->host = '127.0.0.1';
        }
        $this->username = $options['username'];
        $this->password = $options['password'];
        $this->name = $options['db_name'];
    }

    static function create($options) {
        if (class_exists('mysqli')) {
            $class_name = "ETODBConn_Mysqli";
        }
        else {
            $class_name = "ETODBConn_Mysql";
        }
        return new $class_name($options);
    }
}

class ETODBConn_Mysql extends ETODBConn {
    function connect() {
        $this->connection = @mysql_connect($this->host, $this->username, $this->password);
        if (!$this->connection) {
            throw new ETOException("Couldn't connect to the database: " . mysql_error());
        }

        $select_db_res = mysql_select_db($this->name, $this->connection);
        if (!$select_db_res) {
            throw new ETOException("Couldn't select database: " . mysql_error($this->connection));
        }
        return true;
    }

    function query($q) {
        if (!$this->connection) {
            $this->connect();
        }
        $res = mysql_query($q);
        if (!$res) {
            throw new ETOException("SQL error: " . mysql_error($this->connection));
        }
        return $res;
    }

    function fetch_numeric($query) {
        return $this->fetch($query, MYSQL_NUM);
    }

    function fetch($query, $result_type=MYSQL_ASSOC) {
        $result = $this->query($query, $this->connection);
        $return = array();
        while ( $row = mysql_fetch_array($result, $result_type) ) {
            $return[] = $row;
        }
        return $return;
    }

    function escape($value) {
        if (is_null($value)) {
            return "NULL";
        }
        return "'" . mysql_real_escape_string($value) . "'";
    }

    function escape_like($search) {
        return str_replace(array('_', '%'), array('\_', '\%'), $search);
    }

    function get_var($sql) {
        $result = $this->query($sql);
        $row = mysql_fetch_array($result);
        return $row[0];
    }

    function fetch_row($data) {
        return mysql_fetch_assoc($data);
    }
}

class ETODBConn_Mysqli extends ETODBConn {
    function connect() {
        $this->connection = @new MySQLi($this->host, $this->username, $this->password, $this->name);

        if ($this->connection->connect_error) {
            throw new ETOException("Couldn't connect to the database: " . $this->connection->connect_error);
        }
        return true;
    }

    function query($q) {
        if (!$this->connection) {
            $this->connect();
        }
        $res = $this->connection->query($q);

        if (!$res) {
            throw new ETOException("SQL error: " . $this->connection->error);
        }
        return $res;
    }

    function fetch_numeric($query) {
        return $this->fetch($query, MYSQLI_NUM);
    }

    function fetch($query, $result_type=MYSQLI_ASSOC) {
        $result = $this->query($query, $this->connection);
        $return = array();
        while ( $row = $result->fetch_array($result_type) ) {
            $return[] = $row;
        }
        return $return;
    }

    function escape($value) {
        if (is_null($value)) {
            return "NULL";
        }
        return "'" . $this->connection->real_escape_string($value) . "'";
    }

    function escape_like($search) {
        return str_replace(array('_', '%'), array('\_', '\%'), $search);
    }

    function get_var($sql) {
        $result = $this->query($sql);
        $row = $result->fetch_array($result, MYSQLI_NUM);
        return $row[0];
    }

    function fetch_row($data) {
        return $data->fetch_array(MYSQLI_ASSOC);
    }
}

class ETOException extends Exception {};
