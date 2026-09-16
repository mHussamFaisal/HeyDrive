<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V30 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $db = \DB::connection();
        $prefix = get_db_prefix();

        // update_datetime_column_in_all_tables --------------------------------

        // Bookings
        DB::table('booking')->where('created_date', '0000-00-00 00:00:00')->update(['created_date' => '1000-01-01 00:00:00']);
        Schema::table('booking', function (Blueprint $table) {
            $table->dateTime('created_date')->nullable()->default(null)->change();
        });
        DB::table('booking')->where('created_date', '1000-01-01 00:00:00')->update(['created_date' => null]);

        // Routes
        DB::table('booking_route')->where('date', '0000-00-00 00:00:00')->update(['date' => '1000-01-01 00:00:00']);
        DB::table('booking_route')->where('modified_date', '0000-00-00 00:00:00')->update(['modified_date' => '1000-01-01 00:00:00']);
        Schema::table('booking_route', function (Blueprint $table) {
            $table->dateTime('date')->nullable()->default(null)->change();
            $table->dateTime('modified_date')->nullable()->default(null)->change();
        });
        DB::table('booking_route')->where('date', '1000-01-01 00:00:00')->update(['date' => null]);
        DB::table('booking_route')->where('modified_date', '1000-01-01 00:00:00')->update(['modified_date' => null]);

        // Charges
        DB::table('charge')->where('start_date', '0000-00-00 00:00:00')->update(['start_date' => '1000-01-01 00:00:00']);
        DB::table('charge')->where('end_date', '0000-00-00 00:00:00')->update(['end_date' => '1000-01-01 00:00:00']);
        Schema::table('charge', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->default(null)->change();
            $table->dateTime('end_date')->nullable()->default(null)->change();
        });
        DB::table('charge')->where('start_date', '1000-01-01 00:00:00')->update(['start_date' => null]);
        DB::table('charge')->where('end_date', '1000-01-01 00:00:00')->update(['end_date' => null]);

        $sql = "SELECT * FROM `{$prefix}profile`";

        $query = $db->select($sql);

        foreach($query as $key => $value) {
            $sql = "
                INSERT INTO `{$prefix}charge` (`profile_id`, `note`, `note_published`, `type`, `params`, `value`, `start_date`, `end_date`, `published`) VALUES
                ({$value->id}, 'Infant seat', 1, 'infant_seats', '', 0.00, NULL, NULL, 1),
                ({$value->id}, 'Wheelchair', 1, 'wheelchair', '', 0.00, NULL, NULL, 1);
            ";

            if ( $sql ) {
                DB::statement($sql);
            }
        }

        // Discounts
        DB::table('discount')->where('start_date', '0000-00-00 00:00:00')->update(['start_date' => '1000-01-01 00:00:00']);
        DB::table('discount')->where('end_date', '0000-00-00 00:00:00')->update(['end_date' => '1000-01-01 00:00:00']);
        DB::table('discount')->where('created_date', '0000-00-00 00:00:00')->update(['created_date' => '1000-01-01 00:00:00']);
        Schema::table('discount', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->default(null)->change();
            $table->dateTime('end_date')->nullable()->default(null)->change();
            $table->dateTime('created_date')->nullable()->default(null)->change();
        });
        DB::table('discount')->where('start_date', '1000-01-01 00:00:00')->update(['start_date' => null]);
        DB::table('discount')->where('end_date', '1000-01-01 00:00:00')->update(['end_date' => null]);
        DB::table('discount')->where('created_date', '1000-01-01 00:00:00')->update(['created_date' => null]);

        // Restricted Areas
        DB::table('excluded_routes')->where('start_date', '0000-00-00 00:00:00')->update(['start_date' => '1000-01-01 00:00:00']);
        DB::table('excluded_routes')->where('end_date', '0000-00-00 00:00:00')->update(['end_date' => '1000-01-01 00:00:00']);
        DB::table('excluded_routes')->where('modified_date', '0000-00-00 00:00:00')->update(['modified_date' => '1000-01-01 00:00:00']);
        Schema::table('excluded_routes', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->default(null)->change();
            $table->dateTime('end_date')->nullable()->default(null)->change();
            $table->dateTime('modified_date')->nullable()->default(null)->change();
        });
        DB::table('excluded_routes')->where('start_date', '1000-01-01 00:00:00')->update(['start_date' => null]);
        DB::table('excluded_routes')->where('end_date', '1000-01-01 00:00:00')->update(['end_date' => null]);
        DB::table('excluded_routes')->where('modified_date', '1000-01-01 00:00:00')->update(['modified_date' => null]);

        // Fixed prices
        DB::table('fixed_prices')->where('start_date', '0000-00-00 00:00:00')->update(['start_date' => '1000-01-01 00:00:00']);
        DB::table('fixed_prices')->where('end_date', '0000-00-00 00:00:00')->update(['end_date' => '1000-01-01 00:00:00']);
        DB::table('fixed_prices')->where('modified_date', '0000-00-00 00:00:00')->update(['modified_date' => '1000-01-01 00:00:00']);
        Schema::table('fixed_prices', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->default(null)->change();
            $table->dateTime('end_date')->nullable()->default(null)->change();
            $table->dateTime('modified_date')->nullable()->default(null)->change();
        });
        DB::table('fixed_prices')->where('start_date', '1000-01-01 00:00:00')->update(['start_date' => null]);
        DB::table('fixed_prices')->where('end_date', '1000-01-01 00:00:00')->update(['end_date' => null]);
        DB::table('fixed_prices')->where('modified_date', '1000-01-01 00:00:00')->update(['modified_date' => null]);

        // Loyalty program
        if ( Schema::hasTable('loyalty_program') ) {
            DB::table('loyalty_program')->where('created_date', '0000-00-00 00:00:00')->update(['created_date' => '1000-01-01 00:00:00']);
            DB::table('loyalty_program')->where('modified_date', '0000-00-00 00:00:00')->update(['modified_date' => '1000-01-01 00:00:00']);
            Schema::table('loyalty_program', function (Blueprint $table) {
                $table->dateTime('created_date')->nullable()->default(null)->change();
                $table->dateTime('modified_date')->nullable()->default(null)->change();
            });
            DB::table('loyalty_program')->where('created_date', '1000-01-01 00:00:00')->update(['created_date' => null]);
            DB::table('loyalty_program')->where('modified_date', '1000-01-01 00:00:00')->update(['modified_date' => null]);
        }

        // Meeting point
        DB::table('meeting_point')->where('modified_date', '0000-00-00 00:00:00')->update(['modified_date' => '1000-01-01 00:00:00']);
        Schema::table('meeting_point', function (Blueprint $table) {
            $table->dateTime('modified_date')->nullable()->default(null)->change();
        });
        DB::table('meeting_point')->where('modified_date', '1000-01-01 00:00:00')->update(['modified_date' => null]);

        // User
        DB::table('user')->where('last_visit_date', '0000-00-00 00:00:00')->update(['last_visit_date' => '1000-01-01 00:00:00']);
        DB::table('user')->where('created_date', '0000-00-00 00:00:00')->update(['created_date' => '1000-01-01 00:00:00']);
        Schema::table('user', function (Blueprint $table) {
            $table->dateTime('last_visit_date')->nullable()->default(null)->change();
            $table->dateTime('created_date')->nullable()->default(null)->change();
        });
        DB::table('user')->where('last_visit_date', '1000-01-01 00:00:00')->update(['last_visit_date' => null]);
        DB::table('user')->where('created_date', '1000-01-01 00:00:00')->update(['created_date' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
