<?php

namespace App\Bootstrap;

use Illuminate\Log\Writer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\ConfigureLogging as BaseLoggingConfiguration;

class ConfigureLogging extends BaseLoggingConfiguration {
    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureSingleHandler(Application $app, Writer $log)
    {
        $log->useFiles(config('filesystems.disks.logs.root') .'/'. config('filesystems.disks.logs.file_name'));
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureDailyHandler(Application $app, Writer $log)
    {
        $log->useDailyFiles(config('filesystems.disks.logs.root') .'/'. config('filesystems.disks.logs.file_name'), 30);
    }
}
