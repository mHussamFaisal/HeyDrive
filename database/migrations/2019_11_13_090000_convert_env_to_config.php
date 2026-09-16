<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\DatabaseHelper;

class ConvertEnvToConfig extends Migration
{
    private $helperDB;

    public function __construct()
    {
        $this->helperDB = new DatabaseHelper('ConvertEnvToConfig');
    }

    public function up()
    {
        if (file_exists(public_path('.env'))) {
            $allowed = $this->helperDB->allowedConfigs();

            $envFileData = '<?php' . PHP_EOL . PHP_EOL . 'return [' . PHP_EOL;

            foreach ($_ENV as $key => $value) {
                if (in_array($key, $allowed)) {
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
                            $value = "'". $value ."'";
                        break;
                    }

                    $envFileData .= "\t\t'{$key}' => {$value}," . PHP_EOL;
                }
            }

            $envFileData .= "];";

            file_put_contents(public_path('config.php'), $envFileData);

            if (file_exists(public_path('config.php'))) {
                @chmod(public_path('config.php'), 0644);
                @unlink(public_path('.env'));
            }

            if (file_exists(public_path('.htaccess'))) {
                $htaccessFile = file_get_contents(public_path('.htaccess'));
                $htaccessFile = str_replace(
                    '<FilesMatch "(?i)(^artisan$|\\.env|\\.env\\.example|\\.log)">',
                    '<FilesMatch "(?i)(^artisan$|\\.env|\\.env\\.example|config\\.php|\\.log)">',
                    $htaccessFile
                );
                file_put_contents(public_path('.htaccess'), $htaccessFile);
            }
        }
    }

    public function down()
    {
        //
    }
}
