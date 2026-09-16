<?php

namespace App\Helpers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class InstallHelper
{
    /**
     * Save the form content to the config.php file.
     *
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function setConfigFile($data) {
        $configFile = '<?php' . PHP_EOL . PHP_EOL . 'return [' . PHP_EOL;

        foreach ($data as $key => $value) {
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    $value = "true";
                break;
                case 'false':
                case '(false)':
                    $value = "false";
                break;
                case 'empty':
                case '(empty)':
                    $value = "";
                break;
                case 'null':
                case '(null)':
                    $value = "null";
                break;
                default:
                    $value = "'" . $value . "'";
                break;
            }

            $configFile .= "\t'{$key}' => {$value}," . PHP_EOL;
        }

        $configFile .= "];";
        \Storage::disk('root')->put('config.php', $configFile);

        if (\Storage::disk('root')->exists('config.php')) {
            return true;
        }

        return false;
    }

    /**
     * Save .htaccess file
     *
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function saveHtaccess()
    {
        $appRoot = get_string_between(url()->current(), url('/'), 'install');
        $fileSystem = new Filesystem();
        $pathHtaccess = parse_path('.htaccess');
        $contentHtaccess = '';
        $newContentHtaccess = '';
        $htaccessString = '
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase '.$appRoot.'

    Options -Indexes

    # Prevent Direct Access to Protected Files
    <FilesMatch "(?i)(^artisan$|\.log)">
        Order deny,allow
        Deny from all
    </FilesMatch>

    # Prevent Direct Access To Protected Folders
    RewriteRule ^(app|bootstrap|config|database|resources|routes|storage|tests|backups|tmp|uploads/safe)/(.*) / [L,R=301]

    # Prevent Direct Access To modules/vendor Folders Except Assets
    RewriteRule ^(modules|vendor)/(.*)\.((?!ico|gif|jpg|jpeg|png|js|css|less|sass|font|woff|woff2|eot|ttf|svg).)*$ / [L,R=301]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)/$ /$1 [L,R=301]

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
</IfModule>
';
        if ($fileSystem->exists($pathHtaccess)) {
            $contentHtaccess = $fileSystem->get($pathHtaccess);
            if (stripos(strtolower($contentHtaccess), '### ETO configurations, do not change') !== false) {
                $newContentHtaccess = replace_string_between(
                        $contentHtaccess,
                        '### ETO configurations, do not change',
                        '### END ETO configurations, do not change',
                        $htaccessString
                    );
            }
            else {
                $newContentHtaccess = "### ETO configuration, do not change\n"
                    . $htaccessString
                    . "\n### END ETO configuration, do not change\n";
            }
        }

        try {
            $fileSystem->put($pathHtaccess, $newContentHtaccess);
        }
        catch (\Exception $e) {
            $fileSystem->put($pathHtaccess, $contentHtaccess);
        }

        return true;
    }

    /**
     * Migrate and seed the database.
     *
     * @return bool
     */
    public static function migrateAndSeed()
    {
        $outputLog = new BufferedOutput;

        try {
            Artisan::call('db:seed', ['--class' => 'ETOSeeder', '--force' => true], $outputLog);
        }
        catch (Exception $e){
            \Log::debug([self::response($e->getMessage())]);
            return false;
        }

        try {
            Artisan::call('migrate', ['--force' => true], $outputLog);
        }
        catch (Exception $e){
            \Log::debug([self::response($e->getMessage())]);
            return false;
        }

        return true;
    }

    /**
     * Return a formatted error messages.
     *
     * @param $message
     * @param string $status
     * @return array
     */
    private static function response($message, $status = 'danger')
    {
        return ['status' => $status, 'message' => $message];
    }
}
