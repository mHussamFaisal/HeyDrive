<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateHtaccessDirListing extends Migration
{
    public function up()
    {
        if (file_exists(public_path('.htaccess'))) {
            $htaccessFile = file_get_contents(public_path('.htaccess'));

            if (strpos($htaccessFile, '# Disable directory indexes') === false &&
                strpos($htaccessFile, 'Options -Indexes') !== false
            ) {
                $htaccessFile = str_replace(
                    'Options -Indexes',
                    "# Disable directory indexes\r\n".
                    "    Options -Indexes\r\n\r\n".
                    "    # Disable multi views\r\n".
                    "    <IfModule mod_negotiation.c>\r\n".
                    "        Options -MultiViews\r\n".
                    "    </IfModule>",
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
