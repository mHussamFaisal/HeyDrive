<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('feedback')) {
            Schema::create('feedback', function (Blueprint $table) {
                $table->increments('id');
                $table->morphs('parent');
                $table->string('type')->nullable();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->string('ref_number')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('params')->nullable();
                $table->tinyInteger('is_read')->default(0)->unsigned();
                $table->integer('order')->default(0)->unsigned();
                $table->string('status');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('feedback');
    }
}
