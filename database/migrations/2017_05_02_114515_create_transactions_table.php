<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable('transactions') ) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ref_type')->nullable();
                $table->unsignedInteger('ref_id')->default(0);
                $table->string('unique_key');
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('payment_id')->default(0);
                $table->string('payment_method')->nullable();
                $table->string('payment_name')->nullable();
                $table->double('payment_charge', 8, 2)->default('0.00');
                $table->unsignedInteger('currency_id')->default(0);
                $table->double('amount', 8, 2)->default('0.00');
                $table->string('ip')->nullable();
                $table->text('response')->nullable();
                $table->string('status')->nullable();
                $table->dateTime('requested_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();

                $table->index('ref_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
