<?php
// use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
// $routeUri = Route::current()->uri();
// dd($routeUri);
// Installer
// Route::group(['prefix' => 'install', 'as' => 'etoInstall.'], function() {
//     Route::get('/license', ['as' => 'formLicense', 'uses' => 'Subscription\SubscriptionController@setETOLicense']);
//     Route::post('/setLicense', ['as' => 'setLicense', 'uses' => 'Subscription\SubscriptionController@installETO']);
//     Route::get('/', ['as' => 'index', 'uses' => 'InstallController@index']);
//     Route::post('/check-connection', ['as' => 'checkConnection', 'uses' => 'InstallController@checkConnection']);
//     Route::post('/setConfigFile', ['as' => 'setConfigFile', 'uses' => 'InstallController@setConfigFile']);
//     Route::post('/setDataToDB', ['as' => 'setDataToDB', 'uses' => 'InstallController@setDataToDB']);
//     Route::post('/final', ['as' => 'final', 'uses' => 'InstallController@final']);
//     Route::post('/loginAfterInstall', ['as' => 'loginAfterInstall', 'uses' => 'InstallController@loginAfterInstall']);
// });

// Route::get('license-expired', ['as' => 'licenseExpired', 'uses' => 'Subscription\SubscriptionController@expiredETOLicense']);
// Route::get('license-corrupted', ['as' => 'licenseCorrupted', 'uses' => 'Subscription\SubscriptionController@corruptedETOLicense']);
// Route::get('migrate', ['as' => 'etoMigrate', 'uses' => 'InstallController@checkMigrations']);
// Route::get('activation', ['as' => 'etoActivationView', 'uses' => 'InstallController@activationView']);
// Route::post('activation', ['as' => 'etoActivation', 'uses' => 'InstallController@activation']);
// Route::get('deactivation', ['as' => 'etoDeactivationView', 'uses' => 'InstallController@deactivationView', 'middleware' => ['auth', 'role:admin.*']]);
// Route::post('deactivation', ['as' => 'etoDeactivation', 'uses' => 'InstallController@deactivation', 'middleware' => ['auth', 'role:admin.*']]);
// Route::any('auto-update', ['as' => 'autoUpdate', 'uses' => 'Subscription\AutoUpdateController@index']);
// Route::any('auto-update/ping', ['as' => 'autoUpdatePing', 'uses' => 'Subscription\AutoUpdateController@ping']);
        //   dd(getRequestUri());
Route::group(['prefix' => 'debug', 'as' => 'debug.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'DebugController@index']);
});


// Required for version <= 3.25.3
Route::get('modules', function() { return redirect()->route('subscription.index'); });
Route::post('modules/migrate', ['as' => 'modules.migrate', 'uses' => 'Subscription\SubscriptionController@migrateAndClearAfterUpgrade']);
// Required for version <= 3.25.3 end


// Subscription
Route::post('subscription/migrate', ['as' => 'subscription.migrate', 'uses' => 'Subscription\SubscriptionController@migrateAndClearAfterUpgrade']);

Route::group(['prefix' => 'subscription', 'as' => 'subscription.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'Subscription\SubscriptionController@index']);
    Route::post('/check', ['as' => 'checkUpdate', 'uses' => 'Subscription\SubscriptionController@verify']);
    Route::post('/install', ['as' => 'new', 'uses' => 'Subscription\SubscriptionController@install']);
    Route::post('/uninstall', ['as' => 'uninstall', 'uses' => 'Subscription\SubscriptionController@uninstall']);
    Route::group(['prefix' => 'update', 'as' => 'update.'], function() {
        Route::post('/', ['as' => 'index', 'uses' => 'Subscription\UpdateController@getUpdateArchive']);
        Route::post('/extract', ['as' => 'extract', 'uses' => 'Subscription\UpdateController@extractUpdateArchive']);
        Route::post('/get-changes', ['as' => 'getChanges', 'uses' => 'Subscription\UpdateController@getListChanges']);
        Route::post('/backup', ['as' => 'getChanges', 'uses' => 'Subscription\UpdateController@generateBackupAllSteps']);
    });
    Route::get('/{idModule}/get', ['as' => 'module', 'uses' => 'Subscription\SubscriptionController@index']);
    Route::post('/status', ['as' => 'changeStatus', 'uses' => 'Subscription\SubscriptionController@changeStatus']);
});

// Reminder
Route::group(['prefix' => 'remind', 'as' => 'remind.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'ReminderController@getListJson']);
    Route::post('/not-remind', ['as' => 'notRemind', 'uses' => 'ReminderController@notRemind']);
    Route::post('/tomorow', ['as' => 'notRemind', 'uses' => 'ReminderController@reminTomorow']);
    Route::post('/week', ['as' => 'notRemind', 'uses' => 'ReminderController@remindWeek']);
});

// Backups
Route::group(['prefix' => 'backup', 'as' => 'backup.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'BackupController@index']);
    Route::post('/copy', ['as' => 'copy', 'uses' => 'BackupController@copyBackupToDisk']);
    Route::post('/move', ['as' => 'move', 'uses' => 'BackupController@moveBackupToDisk']);
    Route::post('/backup-db', ['as' => 'backupDB', 'uses' => 'BackupController@backupDB']);
    Route::post('/backup-app-files', ['as' => 'addFilesToZip', 'uses' => 'BackupController@addFilesToZip']);
    Route::post('/backup-vendor-files', ['as' => 'addVendorToZip', 'uses' => 'BackupController@addVendorToZip']);
    Route::post('/move-backup-zip', ['as' => 'moveBackupZip', 'uses' => 'BackupController@moveBackupZip']);
    Route::get('/download/{id}', ['as' => 'download', 'uses' => 'BackupController@download']);
    Route::get('/delete/{id}', ['as' => 'delete', 'uses' => 'BackupController@delete']);
    Route::post('/recovery/{id}', ['as' => 'recovery', 'uses' => 'BackupController@recoveryExtract']);
});

// Export
Route::group(['prefix' => 'export', 'as' => 'export.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'ExportController@index']);
    Route::post('/store', ['as' => 'store', 'uses' => 'ExportController@store']);
    Route::post('/format/{format}', ['as' => 'downloadFormat', 'uses' => 'ExportController@download']);
});

// Config
Route::group(['prefix' => 'get-config', 'as' => 'get-config.', 'middleware' => ['auth', 'role:admin.*|driver.*']], function() {
    Route::post('/sendTestEmail', ['as' => 'sendTestEmail', 'uses' => 'Admin\SettingsController@sendTestEmail']);
    Route::post('/fleet-search', ['as' => 'fleetSearch', 'uses' => 'User\UserController@getFleetList']);
    Route::post('/fleet/{fleet_id}', ['as' => 'fleet', 'uses' => 'User\UserController@getFleet']);
    Route::post('/driver-search', ['as' => 'driverSearch', 'uses' => 'User\DriverController@search']);
    Route::post('/driver-search/unavailable/{status}', ['as' => 'driverSearch', 'uses' => 'User\DriverController@search']);
    Route::post('/driver-vehicles', ['as' => 'driverVehicles', 'uses' => 'User\DriverController@vehicleList']);
    Route::post('/driver-vehicle/{vehicle_id}', ['as' => 'driverVehicles', 'uses' => 'User\DriverController@getVehicle']);
    Route::post('/driver/{driver_id}', ['as' => 'driver', 'uses' => 'User\DriverController@get']);
    Route::post('/passenger-search', ['as' => 'customerSearch', 'uses' => 'User\CustomerController@searchPassenger']);
    Route::post('/customer-search', ['as' => 'customerSearch', 'uses' => 'User\CustomerController@search']);
    Route::post('/customer-list', ['as' => 'customerList', 'uses' => 'User\CustomerController@getList']);
    Route::post('/customer/{customer_id}', ['as' => 'customer', 'uses' => 'User\CustomerController@getList']);
});

// Translations
Route::group(['prefix' => 'translations', 'as' => 'translations.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'TranslationsController@index']);
    Route::post('/list', ['as' => 'list', 'uses' => 'TranslationsController@getList']);
    Route::post('/search', ['as' => 'search', 'uses' => 'TranslationsController@search']);
    Route::post('/save', ['as' => 'save', 'uses' => 'TranslationsController@save']);
    Route::post('/get', ['as' => 'get', 'uses' => 'TranslationsController@get']);
    Route::post('/clear', ['as' => 'clear', 'uses' => 'TranslationsController@clear']);
    Route::post('/getFromLocale', ['getFromLocale' => 'save', 'uses' => 'TranslationsController@getFromLocale']);
    Route::post('/clearCache', ['clearCache' => 'save', 'uses' => 'TranslationsController@clearCache']);
    Route::get('/export', ['export' => 'save', 'uses' => 'TranslationsController@export']);
    Route::post('/clearTranslations', ['clearTranslations' => 'save', 'uses' => 'TranslationsController@clearTranslations']);
});

// News
Route::group(['prefix' => 'news', 'as' => 'news.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'NewsController@all']);
    Route::any('/search', ['as' => 'search', 'uses' => 'NewsController@search']);
    Route::get('/{slug}', ['as' => 'get', 'uses' => 'NewsController@get']);
});

// Activity
Route::group(['prefix' => 'activity', 'as' => 'activity.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'ActivityController@index']);
    Route::any('/search', ['as' => 'search', 'uses' => 'ActivityController@search']);
    // Route::get('/getList', ['as' => 'getList', 'uses' => 'ActivityController@getList']);
    Route::post('/list', ['as' => 'list', 'uses' => 'ActivityController@list']);
});

// Teams
Route::group(['middleware' => ['auth', 'role:admin.*']], function() {
    Route::resource('teams', 'TeamsController');
    Route::post('teams/datatables', ['as' => 'teams.datatables', 'uses' => 'TeamsController@datatables']);
    Route::get('teams/{id}/status/{status}', ['as' => 'teams.status', 'uses' => 'TeamsController@status']);
});


Route::group(['prefix' => 'set-new', 'as' => 'setNew.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::resource('booking', 'BookingController2');
});

Route::post('set-settings', ['as' => 'setSettings', 'middleware' => ['auth', 'role:admin.*'], 'uses' => 'Admin\SettingsController@updateSettings']);
Route::post('reset-settings', ['as' => 'resetSettings', 'middleware' => ['auth', 'role:admin.*'], 'uses' => 'Admin\SettingsController@resetSettings']);
Route::post('admin/saveDtState', ['as' => 'adminSaveDtState', 'middleware' => ['auth', 'role:admin.*'], 'uses' => 'Admin\SettingsController@saveDtState']);

Route::group(['prefix' => 'booking2', 'as' => 'booking2.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::post('/getDefaultFormObject/{bookingId}', ['as' => 'getDefaultFormObject', 'uses' => 'BookingController2@generateDefaultObject']);
    Route::post('/send-notifications/', ['as' => 'sendNotitications', 'uses' => 'BookingController2@sendNotitications']);
    Route::post('/markBooking/{id}', ['as' => 'markBookingRead', 'uses' => 'BookingController2@markBookingRead']);
    Route::post('/manageSources', ['as' => 'manageSources', 'uses' => 'BookingController2@manageSources']);
});


// Dispatch
Route::group(['prefix' => 'dispatch', 'as' => 'dispatch.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'DispatchController@index']);
    Route::get('/map-drivers', ['as' => 'map-drivers', 'uses' => 'DispatchController@mapDrivers']);
    Route::get('/check', ['as' => 'check', 'uses' => 'DispatchDriverController@check']);
});

// Booking Tracking
Route::group(['prefix' => 'booking-tracking', 'as' => 'bookingTracking.'], function() {
    Route::post('/{id}', ['as' => 'index', 'uses' => 'BookingTrackingController@show']);
    Route::post('/{id}/last/{timestamp}', ['as' => 'index', 'uses' => 'BookingTrackingController@show']);
});


Route::get('locale/{locale}', ['as' => 'locale.change', 'uses' => 'LocaleController@change']);


// Roles
Route::group(['prefix' => 'roles', 'as' => 'roles.', 'middleware' => ['auth', 'role:admin.root|services']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'RolesController@index']);
    Route::post('/list', ['as' => 'list', 'uses' => 'RolesController@index']);
    Route::match(['get', 'post'], '/trash', ['as' => 'trash', 'uses' => 'RolesController@index']);
    Route::get('/create', ['as' => 'create', 'uses' => 'RolesController@create']);
    Route::post('/', ['as' => 'store', 'uses' => 'RolesController@store']);
    Route::get('/{id}', ['as' => 'show', 'uses' => 'RolesController@show']);
    Route::get('/trash/{id}', ['as' => 'showDeleted', 'uses' => 'RolesController@show']);
    Route::get('/{id}/edit', ['as' => 'edit', 'uses' => 'RolesController@edit']);
    Route::patch('/{id}/update', ['as' => 'update', 'uses' => 'RolesController@update']);
    Route::delete('/{id}/delete', ['as' => 'delete', 'uses' => 'RolesController@trash']);
    Route::put('/{id}/restore', ['as' => 'restore', 'uses' => 'RolesController@restore']);
    Route::delete('/{id}/destroy', ['as' => 'destroy', 'uses' => 'RolesController@destroy']);
});


// Booking
Route::get('/', 'BookingController@booking');
Route::get('/home', 'BookingController@booking');

Route::group(['prefix' => 'booking', 'as' => 'booking.'], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'BookingController@booking']);
    Route::get('widget', ['as' => 'widget', 'uses' => 'BookingController@widget']);
    Route::get('availability/{id}', ['as' => 'availability', 'uses' => 'BookingController@availability']);
    Route::get('pay/{id}/{transaction_id?}', ['as' => 'pay', 'uses' => 'BookingController@pay']);
    Route::get('finish/{id}', ['as' => 'finish', 'uses' => 'BookingController@finish']);
    Route::get('cancel/{id?}', ['as' => 'cancel', 'uses' => 'BookingController@cancel']);
    Route::match(['get', 'post'], 'notify/{id?}', ['as' => 'notify', 'uses' => 'BookingController@notify']);
    Route::get('terms', ['as' => 'terms', 'uses' => 'BookingController@terms']);
    Route::get('details/{slug}', ['as' => 'details', 'uses' => 'BookingController@details']);
});


// Feedback
Route::resource('feedback', 'FeedbackController', ['only' => [
    'index', 'create', 'store'
]]);


// Pages
Route::get('payment-waiting', function() {
    return 'Loading... Please wait.';
});


// Customer
Route::group(['prefix' => 'customer', 'as' => 'customer.'], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'CustomerController@customer']);
});


Route::get('system-logs',  ['as' => 'logs', 'uses' =>'\Rap2hpoutre\LaravelLogViewer\LogViewerController@index', 'middleware' => ['auth', 'role:admin.*']]);


// Driver
Route::group(['prefix' => 'driver', 'as' => 'driver.', 'middleware' => ['auth', 'role:driver.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'Driver\DashboardController@index']);
    Route::get('dashboard', ['as' => 'dashboard.index', 'uses' => 'Driver\DashboardController@index']);
    Route::resource('jobs', 'Driver\JobsController');
    Route::get('jobs/{id}/status/{status}', ['as' => 'jobs.status', 'uses' => 'Driver\JobsController@status']);
    Route::post('jobs/datatables', ['as' => 'jobs.datatables', 'uses' => 'Driver\JobsController@datatables']);
    Route::get('jobs/{id}/meeting-board', ['as' => 'jobs.meeting-board', 'uses' => 'Driver\JobsController@meetingBoard']);
    Route::get('jobs/{id}/download/{file_id}', ['as' => 'jobs.download', 'uses' => 'Driver\JobsController@download']);
    Route::resource('calendar', 'Driver\CalendarController');
    Route::get('settings', ['as' => 'settings.index', 'uses' => 'Driver\SettingsController@index']);
    Route::get('account', ['as' => 'account.index', 'uses' => 'Driver\AccountController@index']);
    Route::get('account/edit', ['as' => 'account.edit', 'uses' => 'Driver\AccountController@edit']);
    Route::patch('account', ['as' => 'account.update', 'uses' => 'Driver\AccountController@update']);
    Route::get('license', ['as' => 'license', 'uses' => 'Driver\PagesController@license']);
    Route::get('mobile-app', ['as' => 'mobile-app', 'uses' => 'Driver\PagesController@mobileApp']);
    Route::post('get-status', ['as' => 'getStatus', 'uses' => 'Driver\SettingsController@getStatus']);
    Route::post('set-status', ['as' => 'setStatus', 'uses' => 'Driver\SettingsController@setStatus']);
});



// supplier
Route::group(['prefix' => 'supplier', 'as' => 'supplier.', 'middleware' => ['auth', 'role:supplier.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'Supplier\DashboardController@index']);
    Route::get('dashboard', ['as' => 'dashboard.index', 'uses' => 'Supplier\DashboardController@index']);
    Route::resource('jobs', 'Supplier\JobsController');
    Route::get('jobs/{id}/status/{status}', ['as' => 'jobs.status', 'uses' => 'Supplier\JobsController@status']);
    Route::post('jobs/datatables', ['as' => 'jobs.datatables', 'uses' => 'Supplier\JobsController@datatables']);
    Route::get('jobs/{id}/meeting-board', ['as' => 'jobs.meeting-board', 'uses' => 'Supplier\JobsController@meetingBoard']);
    Route::get('jobs/{id}/download/{file_id}', ['as' => 'jobs.download', 'uses' => 'Supplier\JobsController@download']);
    Route::resource('calendar', 'Supplier\CalendarController');
    Route::get('settings', ['as' => 'settings.index', 'uses' => 'Supplier\SettingsController@index']);
    Route::get('account', ['as' => 'account.index', 'uses' => 'Supplier\AccountController@index']);
    Route::get('account/edit', ['as' => 'account.edit', 'uses' => 'Supplier\AccountController@edit']);
    Route::patch('account', ['as' => 'account.update', 'uses' => 'Supplier\AccountController@update']);
    Route::get('license', ['as' => 'license', 'uses' => 'Supplier\PagesController@license']);
    Route::get('mobile-app', ['as' => 'mobile-app', 'uses' => 'Supplier\PagesController@mobileApp']);
    Route::post('get-status', ['as' => 'getStatus', 'uses' => 'Supplier\SettingsController@getStatus']);
    Route::post('set-status', ['as' => 'setStatus', 'uses' => 'Supplier\SettingsController@setStatus']);
});

// Reports
Route::group(['middleware' => ['auth', 'role:admin.*']], function () {
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function() {
        Route::get('/', ['as' => 'index', 'uses' => 'Report\ReportController@index']);
        Route::get('/trash', ['as' => 'trashIndex', 'uses' => 'Report\ReportController@index']);
        Route::any('/show/{id}', ['as' => 'show', 'uses' => 'Report\ReportController@show']);
        Route::any('/trash/show/{id}', ['as' => 'trashShow', 'uses' => 'Report\ReportController@show']);
        Route::post('/list', ['as' => 'listJson', 'uses' => 'Report\ReportController@index']);
        Route::post('/list_trash', ['as' => 'listTrash', 'uses' => 'Report\ReportController@index']);
        Route::post('/store', ['as' => 'store', 'uses' => 'Report\ReportController@store']);
        Route::delete('/trash/{id}', ['as' => 'trash', 'uses' => 'Report\ReportController@trash']);
        Route::post('/restore/{id}', ['as' => 'trash', 'uses' => 'Report\ReportController@restore']);
        Route::delete('/destroy/{id}', ['as' => 'destroy', 'uses' => 'Report\ReportController@destroy']);

        Route::post('/send_report/{driver}', ['as' => 'sendReport', 'uses' => 'Report\ReportController@sendReport']);
        Route::post('/send_saved_report/{report}/user/{userId}', ['as' => 'sendSavedReport', 'uses' => 'Report\ReportController@sendReport']);
        Route::post('/send_report_to_all', ['as' => 'sendReportToAll', 'uses' => 'Report\ReportController@sendReport']);
        Route::post('/send_saved_report_to_all/{report}', ['as' => 'sendSavedReportToAll', 'uses' => 'Report\ReportController@sendReport']);

        Route::get('/export/download/{fileName}', ['as' => 'exportDownload', 'uses' => 'Report\ReportExportController@downloadTempFile']);
        Route::post('/export_all/format/{format}', ['as' => 'exportAll', 'uses' => 'Report\ReportExportController@exportAll']);
        Route::post('/export/driver/{driver}/format/{format}', ['as' => 'exportDriver', 'uses' => 'Report\ReportExportController@exportDriver']);
        Route::get('/export_all/{id}/format/{format}', ['as' => 'exportAllSaved', 'uses' => 'Report\ReportExportController@exportAll']);
        Route::get('/export/{id}/driver/{driver}/format/{format}', ['as' => 'exportDriverSaved', 'uses' => 'Report\ReportExportController@exportDriver']);
        Route::post('/export_fleet/fleet/{driver}/format/{format}', ['as' => 'exportDriver', 'uses' => 'Report\ReportExportController@exportFleet']);
        Route::get('/export_fleet/{id}/fleet/{driver}/format/{format}', ['as' => 'exportDriverSaved', 'uses' => 'Report\ReportExportController@exportFleet']);

        // set on bottom
        Route::get('/{type}', ['as' => 'new', 'uses' => 'Report\ReportController@create']);
        Route::post('/{type}', ['as' => 'generateJson', 'uses' => 'Report\ReportController@create']);
    });
});

// Admin
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'Admin\BookingsController@index']);
    Route::resource('bookings', 'Admin\BookingsController');
    Route::get('bookings/{id}/transactions', ['as' => 'bookings.transactions', 'uses' => 'Admin\BookingsController@transactions']);
    Route::get('bookings/{id}/invoice', ['as' => 'bookings.invoice', 'uses' => 'Admin\BookingsController@invoice']);
    Route::get('bookings/{id}/sms', ['as' => 'bookings.sms', 'uses' => 'Admin\BookingsController@sms']);
    Route::match(['get', 'post'], 'bookings/{id}/copy', ['as' => 'bookings.copy', 'uses' => 'Admin\BookingsController@copy']);
    Route::get('bookings/inline-editing/{action}', ['as' => 'bookings.inline-editing', 'uses' => 'Admin\BookingsController@inlineEditing']);
    Route::get('bookings/{id}/meeting-board', ['as' => 'bookings.meeting-board', 'uses' => 'Admin\BookingsController@meetingBoard']);
    Route::get('bookings/{id}/download/{file_id}', ['as' => 'bookings.download', 'uses' => 'Admin\BookingsController@download']);

    Route::resource('transactions', 'Admin\TransactionsController');
    Route::get('transactions/{id}/add', ['as' => 'transactions.add', 'uses' => 'Admin\TransactionsController@add']);
    Route::get('transactions/{id}/copy', ['as' => 'transactions.copy', 'uses' => 'Admin\TransactionsController@copy']);
    Route::get('transactions/inline-editing/{action}', ['as' => 'transactions.inline-editing', 'uses' => 'Admin\TransactionsController@inlineEditing']);

    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function() {
        Route::get('/', ['as' => 'index', 'uses' => 'Admin\SettingsController@index']);
        Route::post('update', ['as' => 'update', 'uses' => 'Admin\SettingsController@update']);
        Route::match(['get', 'post'], 'charges', ['as' => 'charges', 'uses' => 'Admin\SettingsController@charges']);
        Route::match(['get', 'post'], 'notifications', ['as' => 'notifications', 'uses' => 'Admin\SettingsController@notifications']);
        Route::match(['get', 'post'], 'general', ['as' => 'general', 'uses' => 'Admin\SettingsController@general']);
    });

    Route::get('map', ['as' => 'map.index', 'uses' => 'Admin\MapController@index']);
    Route::get('map/drivers', ['as' => 'map.drivers', 'uses' => 'Admin\MapController@drivers']);
    Route::get('dashboard', ['as' => 'dashboard.index', 'uses' => 'Admin\CommonController@dashboard']);
    Route::get('calendar', ['as' => 'calendar.index', 'uses' => 'Admin\CalendarController@index']);
    Route::get('calendar/events', ['as' => 'calendar.events', 'uses' => 'Admin\CalendarController@events']);
    Route::get('discounts', ['as' => 'discounts.index', 'uses' => 'Admin\CommonController@discounts']);
    Route::get('fixed-prices', ['as' => 'fixed-prices.index', 'uses' => 'Admin\CommonController@fixedPrices']);
    Route::match(['get', 'post'], 'fixed-prices/import', ['as' => 'fixed-prices.import', 'uses' => 'Admin\FixedPricesController@import']);
    Route::match(['get', 'post'], 'fixed-prices/export', ['as' => 'fixed-prices.export', 'uses' => 'Admin\FixedPricesController@export']);
    Route::get('excluded-routes', ['as' => 'excluded-routes.index', 'uses' => 'Admin\CommonController@excludedRoutes']);
    Route::get('meeting-points', ['as' => 'meeting-points.index', 'uses' => 'Admin\CommonController@meetingPoints']);
    Route::get('categories', ['as' => 'categories.index', 'uses' => 'Admin\CommonController@categories']);
    Route::get('locations', ['as' => 'locations.index', 'uses' => 'Admin\CommonController@locations']);
    Route::get('customers', ['as' => 'customers.index', 'uses' => 'Admin\CommonController@customers']);
    Route::match(['get', 'post'], 'customers/import', ['as' => 'customers.import', 'uses' => 'User\CustomerController@import']);
    Route::get('vehicles-types', ['as' => 'vehicles-types.index', 'uses' => 'Admin\CommonController@vehiclesTypes']);
    Route::get('payments', ['as' => 'payments.index', 'uses' => 'Admin\CommonController@payments']);
    Route::get('config', ['as' => 'config.index', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/localization', ['as' => 'config.localization', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/booking', ['as' => 'config.booking', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/auto-dispatch', ['as' => 'config.auto-dispatch', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/web-booking-widget', ['as' => 'config.web-booking-widget', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/google', ['as' => 'config.google', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/mileage-time', ['as' => 'config.mileage-time', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/bases', ['as' => 'config.bases', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/deposit-payments', ['as' => 'config.deposit-payments', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/driver-income', ['as' => 'config.driver-income', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/night-surcharge', ['as' => 'config.night-surcharge', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/holiday-surcharge', ['as' => 'config.holiday-surcharge', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/additional-charges', ['as' => 'config.additional-charges', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/other-discounts', ['as' => 'config.other-discounts', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/tax', ['as' => 'config.tax', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/invoices', ['as' => 'config.invoices', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/styles', ['as' => 'config.styles', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/users', ['as' => 'config.users', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/integration', ['as' => 'config.integration', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/airport-detection', ['as' => 'config.airport-detection', 'uses' => 'Admin\CommonController@config']);
    Route::get('config/debug', ['as' => 'config.debug', 'uses' => 'Admin\CommonController@config']);
    Route::get('profiles', ['as' => 'profiles.index', 'uses' => 'Admin\CommonController@profiles']);
    Route::get('account', ['as' => 'account.index', 'uses' => 'Admin\AccountController@index']);
    Route::get('getting-started', ['as' => 'getting-started', 'uses' => 'Admin\PagesController@gettingStarted']);
    Route::get('web-widget', ['as' => 'web-widget', 'uses' => 'Admin\PagesController@webWidget']);
    Route::get('mobile-app', ['as' => 'mobile-app', 'uses' => 'Admin\PagesController@mobileApp']);
    Route::get('license', ['as' => 'license', 'uses' => 'Admin\PagesController@license']);
    Route::get('callerid', ['as' => 'callerid.index', 'uses' => 'Admin\CallerIDController@index']);

    Route::resource('users', 'Admin\UsersController');
    Route::get('users/download/{id}', ['as' => 'users.download', 'uses' => 'Admin\UsersController@download']);
    Route::get('users/{id}/status/{status}', ['as' => 'users.status', 'uses' => 'Admin\UsersController@status']);
    Route::post('users/datatables', ['as' => 'users.datatables', 'uses' => 'Admin\UsersController@datatables']);

    Route::resource('zones', 'Admin\ZonesController');
    Route::get('zones/{id}/copy', ['as' => 'zones.copy', 'uses' => 'Admin\ZonesController@copy']);
    Route::get('zones/{id}/status/{status}', ['as' => 'zones.status', 'uses' => 'Admin\ZonesController@status']);
    Route::post('zones/datatables', ['as' => 'zones.datatables', 'uses' => 'Admin\ZonesController@datatables']);

    Route::resource('vehicles', 'Admin\VehiclesController');
    Route::post('vehicles/datatables', ['as' => 'vehicles.datatables', 'uses' => 'Admin\VehiclesController@datatables']);
    Route::get('vehicles/{id}/status/{status}', ['as' => 'vehicles.status', 'uses' => 'Admin\VehiclesController@status']);
    Route::get('vehicles/{id}/selected/{selected}', ['as' => 'vehicles.selected', 'uses' => 'Admin\VehiclesController@selected']);

    Route::resource('services', 'Admin\ServicesController');
    Route::post('services/datatables', ['as' => 'services.datatables', 'uses' => 'Admin\ServicesController@datatables']);
    Route::get('services/{id}/status/{status}', ['as' => 'services.status', 'uses' => 'Admin\ServicesController@status']);
    Route::get('services/{id}/featured/{featured}', ['as' => 'services.featured', 'uses' => 'Admin\ServicesController@featured']);

    Route::resource('feedback', 'Admin\FeedbackController');
    Route::get('feedback/download/{id}', ['as' => 'feedback.download', 'uses' => 'Admin\FeedbackController@download']);
    Route::post('feedback/datatables', ['as' => 'feedback.datatables', 'uses' => 'Admin\FeedbackController@datatables']);
    Route::get('feedback/{id}/status/{status}', ['as' => 'feedback.status', 'uses' => 'Admin\FeedbackController@status']);

    Route::resource('scheduled-routes', 'Admin\ScheduledRoutesController');
    Route::post('scheduled-routes/datatables', ['as' => 'scheduled-routes.datatables', 'uses' => 'Admin\ScheduledRoutesController@datatables']);
    Route::get('scheduled-routes/{id}/status/{status}', ['as' => 'scheduled-routes.status', 'uses' => 'Admin\ScheduledRoutesController@status']);
    Route::get('scheduled-routes/{id}/featured/{featured}', ['as' => 'scheduled-routes.featured', 'uses' => 'Admin\ScheduledRoutesController@featured']);
});


// Flight Details
Route::get('/searchAirports', ['as' => 'searchAirports', 'uses' => 'FlightController@searchAirports']);
Route::get('/searchAirlines', ['as' => 'searchAirlines', 'uses' => 'FlightController@searchAirlines']);
Route::group(['middleware' => ['auth', 'role:admin.*|driver.*']], function() {
    Route::get('/refreshFlightDetails', ['as' => 'refreshFlightDetails', 'uses' => 'FlightController@refreshFlightDetails']);
});
Route::group(['middleware' => ['auth', 'role:admin.*']], function() {
    Route::get('/updateAirports', ['as' => 'updateAirports', 'uses' => 'FlightController@updateAirports']);
    Route::get('/updateAirlines', ['as' => 'updateAirlines', 'uses' => 'FlightController@updateAirlines']);
});


// ETOv2
Route::any('etov2', ['as' => 'etov2', 'uses' => 'ETOv2Controller@index']);
Route::any('cron', ['as' => 'cron', 'uses' => 'ETOv2Controller@cron']);


// User
Auth::routes();

Route::any('logout', function() {
    if (Auth::check()) {
        auth()->user()->setLastActivity('logout');
    }
    Auth::logout();
    return redirect()->route('login');
});

if( !config('auth.registration') ) {
    Route::any('register', function() {
        return redirect()->route('login');
    });
}


// Mobile App
Route::group(['prefix' => 'mobile', 'as' => 'mobile.'], function() {
    Route::post('push-token', ['as' => 'push-token', 'uses' => 'MobileAppController@pushToken']);
    Route::post('update-coordinates', ['as' => 'update-coordinates', 'uses' => 'MobileAppController@updateCoordinates']);
    Route::post('update-status', ['as' => 'update-status', 'uses' => 'MobileAppController@updateStatus']);
    Route::post('login', ['as' => 'login', 'uses' => 'MobileAppController@login']);
    Route::any('logout', ['as' => 'logout', 'uses' => 'MobileAppController@logout']);
    Route::get('host', ['as' => 'host', 'uses' => 'MobileAppController@host']);
});
