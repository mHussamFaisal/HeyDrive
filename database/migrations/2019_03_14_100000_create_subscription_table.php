<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('unique_id')->unique();
                $table->string('domain')->unique();
                $table->text('license');
                $table->tinyInteger('status')->default(0)->unsigned();
                $table->text('params');
                $table->string('hash');
                $table->dateTime('expire_at')->nullable();
                $table->dateTime('support_at')->nullable();
                $table->dateTime('update_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('profile')) {
            Schema::table('profile', function (Blueprint $table) {
                if (!Schema::hasColumn('profile', 'subscription_id')) {
                    $table->integer('subscription_id')->default(0)->unsigned()->after('id');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(
                ['parent_id' => 0, 'parent_type' => 'system', 'param' => 'cron_update', 'value' => '{"auto":1,"time":"00:00:00","interval":1}']
            );
            DB::table('settings')->insert(
                ['parent_id' => 0, 'parent_type' => 'system', 'param' => 'last_verify', 'value' => date('Y-m-d')]
            );
        }
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');

        if (Schema::hasTable('profile')) {
            Schema::table('profile', function (Blueprint $table) {
                if (Schema::hasColumn('profile', 'subscription_id')) {
                    $table->dropColumn('subscription_id');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('parent_id', 0)->where('parent_type', 'system')->whereIn('param', ['cron_update', 'last_verify'])->delete();
        }
    }
}
