<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\DatabaseHelper;

class ClearConfigFile extends Migration
{
    private $helperDB;

    public function __construct()
    {
        $this->helperDB = new DatabaseHelper('ClearConfigFile');
    }

    public function up()
    {
        if (file_exists(public_path('config.php'))) {
            $oldConfig = include(public_path('config.php'));
            $allowed = $this->helperDB->allowedConfigs();

            $envFileData = '<?php' . PHP_EOL . PHP_EOL . 'return [' . PHP_EOL;

            foreach ($oldConfig as $key => $value) {
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
            @chmod(public_path('config.php'), 0644);
        }
    }

    public function down()
    {
        //
    }
}
