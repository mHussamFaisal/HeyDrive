<?php

return [
    'url' => [
        // 'pricing' => 'https://easytaxioffice.co.uk/pricing/',
    ],
    'license_expiry_reminder' => 30, // days
    'license_support_reminder' => 14, // days
    'license_update_reminder' => 14, // days
    'last_verify' => null,
    'news' => [
        'count' => 0,
    ],
    'js' => [
        'init' => [
            'subscription' => [
                'backup', 'booking', 'bookingForm', 'dispatch', 'notifications', 'roles', 'update', 'translations'
            ],
            'system' => [
                'backup', 'booking', 'bookingForm', 'dispatch', 'notifications', 'roles', 'update', 'translations'
            ],
        ],
    ],
    'interval' => [
        'driver_refresh' => 20,
    ],
    'cache' => [
        'drivers' => [
            'subscription' => 'file',
        ],
    ],
    'allow_fleet_operator' => eto_config('ETO_ALLOW_FLEET_OPERATOR', 0),
    'allow_backups' => eto_config('ETO_ALLOW_BACKUPS', 0),
    'allow_export' => eto_config('ETO_ALLOW_EXPORT', 0),
    'allow_news' => eto_config('ETO_ALLOW_NEWS', 0),
    'allow_system_logs' => eto_config('ETO_ALLOW_SYSTEM_LOGS', 0),
    'allow_flightstats' => eto_config('ETO_ALLOW_FLIGHTSTATS', 0),
    'allow_translations' => eto_config('ETO_ALLOW_TRANSLATIONS', 0),
    'allow_roles' => eto_config('ETO_ALLOW_ROLES', 0),
    'allow_deactivation' => eto_config('ETO_ALLOW_DEACTIVATION', 0),
    'allow_reminders' => eto_config('ETO_ALLOW_REMINDERS', 0),
    'allow_cron' => eto_config('ETO_ALLOW_CRON', 0),
    'allow_auto_dispatch' => eto_config('ETO_ALLOW_AUTO_DISPATCH', 0),
    'allow_teams' => eto_config('ETO_ALLOW_TEAMS', 0),
    'modules_access' => eto_config('MODULES_ACCESS', 1),
    'multi_subscription' => eto_config('MULTI_SUBSCRIPTION', 0),
    'last_driver_count' => null,
    'stats_active_drivers_created' => false,
    'last_check_requirements' => null,
    'deactivate_system' => 0,
    'db_version' => null,
    'allowed_file_extensions' => 'jpg,jpeg,gif,png,pdf,rtf,odt,doc,docx,xlr,xls,xlsx,csv,mpg,mpeg,avi,wmv,mov,flv,swf,ogg,webm,3gp,mp4,mp3,wav,mid,midi,wma,aac,rm,ram,amr', // odp,pps,ppt,pptx,svg,zip,7z,tar.gz,rar
];
