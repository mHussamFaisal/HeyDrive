<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\DatabaseHelper;

class RebuildBookingTrackingTables extends Migration
{
    private $helperDB;

    public function __construct()
    {
        $this->helperDB = new DatabaseHelper('RebuildBookingTrackingTables');
    }

    public function up()
    {
        $prefix = get_db_prefix();

        // Drop exists keys
        $this->helperDB->dropIndexIfExist('booking_tracking_active', 'bta_booking_id', $prefix);
        $this->helperDB->dropIndexIfExist('booking_tracking_active', 'bta_driver_id', $prefix);

        if (!Schema::hasTable('booking_driver_tracking')) {

            if (!Schema::hasTable('booking_statuses')) {
                Schema::create('booking_statuses', function (Blueprint $table) use ($prefix) {
                    $table->engine = 'InnoDB';

                    $table->increments('id');
                    $table->integer('booking_id')->unsigned();
                    $table->foreign('booking_id', $prefix . 'bstat_booking_id')
                        ->references('id')
                        ->on('booking_route')
                        ->onDelete('cascade');
                    $table->integer('user_id')->unsigned();
                    $table->foreign('user_id', $prefix . 'bstat_user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                    $table->string('status', 30);
                    $table->double('lat', 10, 6)->nullable();
                    $table->double('lng', 10, 6)->nullable();
                    $table->timestamp('timestamp')->nullable();
                });
            }

            // Parse archive data and drop table booking_tracking
            if (Schema::hasTable('booking_tracking')) {
                if (Schema::hasColumn('booking_tracking', 'params')) {
                    $rows = [];
                    $tracking = \DB::table('booking_tracking')->get();

                    foreach ($tracking as $item) {
                        $dir = 'bookings' . DIRECTORY_SEPARATOR
                            . $item->booking_id . DIRECTORY_SEPARATOR
                            . 'drivers' . DIRECTORY_SEPARATOR
                            . $item->driver_id;
                        $coordinates = [];

                        if ($params = json_decode($item->params)) {
                            foreach ($params as $status => $param) {
                                foreach ($param as $k => $p) {
                                    if ((int)$k === 0) {
                                        $rows[] = [
                                            'booking_id' => $item->booking_id,
                                            'user_id' => $item->driver_id,
                                            'status' => $status,
                                            'lat' => !empty($p[0]) ? $p[0] : null,
                                            'lng' => !empty($p[1]) ? $p[1] : null,
                                            'timestamp' => date('Y-m-d H:i:s', (int)$p[2]),
                                        ];
                                    }
                                    if (!empty($p[0]) && !empty($p[1])) {
                                        $coordinates[] = $p;
                                    }
                                }
                            }

                            if (count($coordinates) > 0) {
                                if (!is_dir(asset_path('archive', $dir))) {
                                    \Storage::disk('archive')->makeDirectory($dir, 0755, true, true);
                                }
                                \Storage::disk('archive')->put($dir . DIRECTORY_SEPARATOR . 'tracking.json', json_encode($coordinates));
                            }
                        }
                    }

                    $rows_chunk = array_chunk($rows, 50);

                    foreach ($rows_chunk as $key => $value) {
                        DB::table('booking_statuses')->insert($value);
                    }

                    DB::table('booking_tracking')->truncate();
                }
            }

            if (Schema::hasTable('booking_tracking_active')) {
                // Schema::table('booking_tracking_active', function (Blueprint $table) {
                //     $table->string('timestamp')->nullable()->default(null)->change();
                // });

                // Schema::table('booking_tracking_active', function (Blueprint $table) {
                //     if (Schema::hasColumn('booking_tracking_active', 'timestamp')
                //         && !Schema::hasColumn('booking_tracking_active', 'timestamp_tmp')
                //     ) {
                //         $table->renameColumn('timestamp', 'timestamp_tmp');
                //     }
                // });

                DB::statement("ALTER TABLE `{$prefix}booking_tracking_active` CHANGE `timestamp` `timestamp_tmp` VARCHAR(255) DEFAULT NULL;");

                Schema::table('booking_tracking_active', function (Blueprint $table) {
                    if (!Schema::hasColumn('booking_tracking_active', 'timestamp')
                        && Schema::hasColumn('booking_tracking_active', 'timestamp_tmp')
                    ) {
                        $table->unsignedInteger('timestamp');
                    }
                });

                Schema::rename('booking_tracking_active', 'booking_driver_tracking');

                DB::statement("UPDATE `{$prefix}booking_driver_tracking` SET `timestamp` = UNIX_TIMESTAMP(`timestamp_tmp`);");

                Schema::table('booking_driver_tracking', function (Blueprint $table) use ($prefix) {
                    if (Schema::hasColumn('booking_driver_tracking', 'id')) {
                        $table->dropColumn('id');
                    }
                    if (Schema::hasColumn('booking_driver_tracking', 'timestamp_tmp')) {
                        $table->dropColumn('timestamp_tmp');
                    }
                    if (Schema::hasColumn('booking_driver_tracking', 'status')) {
                        $table->dropColumn('status');
                    }

                    $table->foreign('booking_id', $prefix . 'bdtrac_booking_id')
                        ->references('id')
                        ->on('booking_route')
                        ->onDelete('cascade');

                    $table->foreign('driver_id', $prefix . 'bdtrac_user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                });
            }
        }

        if (file_exists(public_path('.htaccess'))) {
            $htaccessFile = file_get_contents(public_path('.htaccess'));
            $htaccessFile = str_replace(
                '<FilesMatch "(?i)(^artisan$|\\.env|\\.env\\.example|config\\.php|\\.log)">',
                '<FilesMatch "(?i)(^artisan$|\\.env|\\.env\\.example|config\\.php|\\.log|tracking.json)">',
                $htaccessFile
            );
            file_put_contents(public_path('.htaccess'), $htaccessFile);
        }

        if (Schema::hasTable('users')) {
            DB::statement("ALTER TABLE `{$prefix}users` ENGINE = InnoDB");
        }

        if (!Schema::hasTable('user_params')) {
            Schema::create('user_params', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';

                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id', $prefix . 'upar_user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                $table->string('param', 120);
                $table->text('value');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_params');
        Schema::dropIfExists('booking_driver_tracking');
        Schema::dropIfExists('booking_statuses');
    }
}
