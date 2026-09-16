<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable('profiles') ) {
            Schema::create('profiles', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->default(0);
                $table->string('title')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('mobile_no')->nullable();
                $table->string('telephone_no')->nullable();
                $table->string('emergency_no')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('postcode')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->string('profile_type')->nullable();
                $table->string('company_name')->nullable();
                $table->string('company_number')->nullable();
                $table->string('company_tax_number')->nullable();
                $table->string('national_insurance_no')->nullable();
                $table->string('bank_account')->nullable();
                $table->string('unique_id')->nullable();
                $table->double('commission', 8, 2)->default('0.00');
                $table->text('availability')->nullable();
                $table->string('insurance')->nullable();
                $table->dateTime('insurance_expiry_date')->nullable();
                $table->string('driving_licence')->nullable();
                $table->date('driving_licence_expiry_date')->nullable();
                $table->string('pco_licence')->nullable();
                $table->date('pco_licence_expiry_date')->nullable();
                $table->string('phv_licence')->nullable();
                $table->date('phv_licence_expiry_date')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('user_id');
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
        Schema::dropIfExists('profiles');
    }
}
