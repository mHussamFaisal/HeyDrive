<?php

namespace App\Http\Middleware;

use Closure;

class CheckDBConnection
{
    public function handle($request, Closure $next)
    {
        try {
            $db = \DB::connection();
            $pdo = $db->getPdo();

            if ($db->getDatabaseName()) {
                $prefix = get_db_prefix();
                $tables = $db->getDoctrineSchemaManager()->listTableNames();

                if (in_array($prefix.'sessions', $tables)) {
                    define('ETO_SESSIONS_TABLE_EXISTS', 1);
                }

                if (in_array($prefix.'language_lines', $tables)) {
                    define('ETO_LANGUAGE_LINES_TABLE_EXISTS', 1);
                }

                if (in_array($prefix.'reminders', $tables)) {
                    define('ETO_REMINDERS_TABLE_EXISTS', 1);
                }
            }
        }
        catch (\Exception $e) {
            // \Log::warning('Could not connect to the database: '. $e->getMessage());
        }

        if (!defined('ETO_SESSIONS_TABLE_EXISTS')) {
            if ($request->is('install') || $request->is('install/*')) {
                if (config('session.driver') == 'database') {
                    config(['session.driver' => 'file']);
                }
            }
            else {
                return response()->view('errors.db');
            }
        }

        return $next($request);
    }
}
