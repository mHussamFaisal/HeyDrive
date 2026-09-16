<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DatabaseHelper
{
    private $migration;

    public function __construct($migration)
    {
        $this->migration = $migration;
    }

    public function dropIndexIfExist($tableName, $indexName, $prefix = null)
    {
        if ($prefix == null) {
            $prefix = get_db_prefix();
        }

        try {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexName, $prefix) {
                $indexes = collect(\DB::select("SHOW INDEXES FROM `{$prefix}{$tableName}`"))->pluck('Key_name')->toArray();
                $matches = preg_grep('/(.*)'. $indexName .'$/i', $indexes);

                foreach ($matches as $key => $value) {
                    $table->dropForeign($value);
                }
            });
        }
        catch (\Exception $e) {
            \Log::error('Migration ' . $this->migration . ' (dropForeign): '. $e->getMessage());
        }

        try {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexName, $prefix) {
                $indexes = collect(\DB::select("SHOW INDEXES FROM `{$prefix}{$tableName}`"))->pluck('Key_name')->toArray();
                $matches = preg_grep('/(.*)'. $indexName .'$/i', $indexes);

                foreach ($matches as $key => $value) {
                    $table->dropIndex($value);
                }
            });
        }
        catch (\Exception $e) {
            \Log::error('Migration ' . $this->migration . ' (dropIndex): '. $e->getMessage());
        }
    }

    public static function toSqlWithBindings($query = null, $clear = false) {
        // Example usage: \App\Helpers\DatabeseHelper::toSqlWithBindings($query, true)
        $string = '';

        if (!is_null($query)) {
            $string = str_replace(array('?'), array('\'%s\''), $query->toSql());
            $string = vsprintf($string, $query->getBindings());

            if ($clear) {
                $string = str_replace("\r\n", "", $string);
            }
        }

        return $string;
    }

    public function allowedConfigs()
    {
        return [
            'AUTO_UPDATE_ENABLE',
            'AUTO_UPDATE_SECRET_KEY',
            'FORCE_GOOGLE_API_KEY',
            'SITE_GOOGLE_MAPS_JAVASCRIPT_API_KEY',
            'SITE_GOOGLE_MAPS_EMBED_API_KEY',
            'SITE_GOOGLE_MAPS_DIRECTIONS_API_KEY',
            'SITE_GOOGLE_MAPS_GEOCODING_API_KEY',
            'SITE_GOOGLE_PLACES_API_KEY',
            'SITE_GOOGLE_ANALYTICS_TRACKING_ID',
            'SITE_GOOGLE_ADWORDS_CONVERSION_ID',
            'SITE_ALLOW_FIXED_PRICES_IMPORT',
            'APP_PUBLIC_PATH',
            'API_LICENSE_URL',
            'API_DOWNLOAD_URL',
            'APP_NAME',
            'APP_ENV',
            'APP_DEBUG',
            'APP_URL',
            'APP_TIMEZONE',
            'APP_LOCALE',
            'APP_KEY',
            'APP_LOG',
            'APP_LOG_LEVEL',
            'AUTH_REGISTRATION',
            'BROADCAST_DRIVER',
            'PUSHER_KEY',
            'PUSHER_SECRET',
            'PUSHER_APP_ID',
            'CACHE_DRIVER',
            'MEMCACHED_PERSISTENT_ID',
            'MEMCACHED_USERNAME',
            'MEMCACHED_PASSWORD',
            'MEMCACHED_HOST',
            'MEMCACHED_PORT',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'DB_PREFIX',
            'DB_STRICT',
            'REDIS_HOST',
            'REDIS_PASSWORD',
            'REDIS_PORT',
            'DEBUGBAR_ENABLED',
            'APP_BACKUP_PATH',
            'APP_LOG_PATH',
            'MAIL_DRIVER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_ENCRYPTION',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_SENDMAIL',
            'QUEUE_DRIVER',
            'MAILGUN_DOMAIN',
            'MAILGUN_SECRET',
            'SES_KEY',
            'SES_SECRET',
            'SPARKPOST_SECRET',
            'STRIPE_KEY',
            'STRIPE_SECRET',
            'SMS_SERVICE_TYPE',
            'TEXTLOCAL_KEY',
            'TEXTLOCAL_TEST_MODE',
            'TWILIO_SID',
            'TWILIO_TOKEN',
            'TWILIO_PHONE_NUMBER',
            'SMSGATEWAY_KEY',
            'SMSGATEWAY_DEVICE_ID',
            'PCAPREDICT_ENABLED',
            'PCAPREDICT_KEY',
            'RINGCENTRAL_ENVIRONMENT',
            'RINGCENTRAL_APP_KEY',
            'RINGCENTRAL_APP_SECRET',
            'RINGCENTRAL_WIDGET_OPEN',
            'RINGCENTRAL_POPUP_OPEN',
            'FLIGHTSTATS_ENABLED',
            'FLIGHTSTATS_APP_ID',
            'FLIGHTSTATS_APP_KEY',
            'SESSION_DRIVER',
            'SESSION_COOKIE_NAME',
            'SESSION_COOKIE_PATH',
            'SESSION_COOKIE_DOMAIN',
            'SESSION_COOKIE_SECURE',
            'SESSION_COOKIE_SAME_SITE',
            'ACTIVITY_LOGGER_ENABLED',
            'ETO_ALLOW_FLEET_OPERATOR',
            'ETO_ALLOW_BACKUPS',
            'ETO_ALLOW_EXPORT',
            'ETO_ALLOW_NEWS',
            'ETO_ALLOW_SYSTEM_LOGS',
            'ETO_ALLOW_FLIGHTSTATS',
            'ETO_ALLOW_TRANSLATIONS',
            'ETO_ALLOW_ROLES',
            'ETO_ALLOW_DEACTIVATION',
            'ETO_ALLOW_REMINDERS',
            'MULTI_SUBSCRIPTION',
            'MODULES_ACCESS',
            'SYSTEM_VERSION',
            'WAYNIUM_ENABLED',
            'WAYNIUM_LIMO_ID',
            'WAYNIUM_API_KEY',
            'WAYNIUM_SECRET_KEY',
        ];
    }
}
