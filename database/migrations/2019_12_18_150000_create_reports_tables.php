<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTables extends Migration
{
    public function up()
    {
        $prefix = get_db_prefix();

        Schema::table('users', function(Blueprint $table) {
            $table->increments('id')->unsigned()->change();
        });

        Schema::table('booking_route', function(Blueprint $table) {
            $table->increments('id')->unsigned()->change();
        });

        DB::statement("ALTER TABLE `{$prefix}booking_route` ENGINE = InnoDB");
        DB::statement("ALTER TABLE `{$prefix}payment` ENGINE = InnoDB");

        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->enum('type', ['driver', 'customer', 'payment']);
                $table->timestamp('from_date')->nullable();
                $table->timestamp('to_date')->nullable();
                $table->text('filters')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('report_payments')) {
            Schema::create('report_payments', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('report_id')->unsigned();
                $table->foreign('report_id', $prefix .'rp_report_id')
                    ->references('id')->on('reports')
                    ->onDelete('cascade');
                $table->integer('payment_id')->nullable()->unsigned();
                $table->foreign('payment_id', $prefix .'rp_payment_id')
                    ->references('id')->on('payment')
                    ->onDelete('set null');
                $table->string('payment_name');
                $table->string('transaction_status');
                $table->string('booking_status');
                $table->double('total', 8, 2)->default('0.00');
            });
        }

        if (!Schema::hasTable('report_payment_bookings')) {
            Schema::create('report_payment_bookings', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('report_payment_id')->unsigned();
                $table->foreign('report_payment_id', $prefix .'rpb_payment_id')
                    ->references('id')->on('report_payments')
                    ->onDelete('cascade');
                $table->integer('booking_id')->nullable()->unsigned();
                $table->foreign('booking_id', $prefix .'rpb_booking_id')
                    ->references('id')->on('booking_route')
                    ->onDelete('set null');
                $table->string('booking_ref');
                $table->double('amount', 8, 2)->default('0.00');
                $table->timestamp('date')->nullable();
            });
        }

        if (!Schema::hasTable('report_drivers')) {
            Schema::create('report_drivers', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('report_id')->unsigned();
                $table->foreign('report_id', $prefix .'rd_report_id')
                    ->references('id')->on('reports')
                    ->onDelete('cascade');
                $table->integer('user_id')->nullable()->unsigned();
                $table->foreign('user_id', $prefix .'rd_user_id')
                    ->references('id')->on('users')
                    ->onDelete('set null');
                $table->string('name');
                $table->string('email');
                $table->decimal('percent', 8,2)->default(0);
                $table->tinyInteger('is_notified')->default(0)->unsigned();
            });
        }

        if (!Schema::hasTable('report_driver_bookings')) {
            Schema::create('report_driver_bookings', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('report_driver_id')->unsigned();
                $table->foreign('report_driver_id', $prefix .'rdb_report_driver_id')
                    ->references('id')->on('report_drivers')
                    ->onDelete('cascade');
                $table->integer('booking_id')->nullable()->unsigned();
                $table->foreign('booking_id', $prefix .'rdb_booking_id')
                    ->references('id')->on('booking_route')
                    ->onDelete('set null');
                $table->string('booking_ref');
                $table->double('commission', 8, 2)->default('0.00');
                $table->double('cash', 8, 2)->default('0.00');
                $table->timestamp('date')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('report_driver_bookings');
        Schema::dropIfExists('report_drivers');
        Schema::dropIfExists('report_payment_bookings');
        Schema::dropIfExists('report_payments');
        Schema::dropIfExists('reports');
    }
}
