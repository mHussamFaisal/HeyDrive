<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\SettingsHelper;

class CreateFieldsTable extends Migration
{
    public function up()
    {
        if ( !Schema::hasTable('fields') ) {
            Schema::create('fields', function (Blueprint $table) {
                $table->increments('id');
                $table->morphs('parent');
                $table->string('section');
                $table->string('field_key');
                $table->string('type');
                $table->tinyInteger('is_core')->default(0)->unsigned();
                $table->tinyInteger('is_edit')->default(0)->unsigned();
                $table->tinyInteger('is_required')->default(0)->unsigned();
                $table->tinyInteger('status')->default(0)->unsigned();
                $table->integer('order')->default(0)->unsigned();
                $table->text('params')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            $file = realpath(__DIR__.'/../seeds/sql/2019_01_30_140000_create_fields_table_with_data.sql');
            if ( file_exists($file) ) {
                $content = \File::get($file);
                $content = str_replace('[DB_PREFIX]', config('database.connections.'. config('database.default') .'.prefix'), $content);
                \DB::unprepared(DB::raw($content));
            }
        }

        // Map items amount
        if ( !Schema::hasTable('booking_route') ) {
            $checkItems = ['child_seat','baby_seat','infant_seat','wheelchair'];
            $customFields = SettingsHelper::getItems();
            $customFieldsKeys = [];
            foreach($customFields as $field) {
            		$customFieldsKeys[] = $field->field_key;
            }
            $bookings = \App\Models\BookingRoute::select('id','child_seats','baby_seats','infant_seats','wheelchair','items')->orderBy('id', 'DESC')->where('id',25)->get();
            foreach($bookings As $b) {
            		$items = !empty($b->items) ? json_decode($b->items, true) : [];
            		$amounts = [];
            		foreach($items as $id=>$item) {
            				if (in_array($item['type'], $checkItems)) {
            						$name = $item['type'];
            						$colName = isset($b->$name) ? $name : $name.'s';
            						$amounts[$colName] += $item['amount'];
            				}
            				else if (!in_array($item['type'], $customFieldsKeys) ) {
            						if (in_array($item['type'], [
            								'journey', 'parking', 'meet_and_greet', 'baby_seat',
            								'child_seat', 'infant_seat', 'wheelchair', 'stopover', 'waiting_time', 'other'
            						])) {
            								// do nothing
            						}
            						else if ($item['type'] == 'waypoint') {
            								$items[$id]['type'] = 'stopover';
            						}
            						else if ($item['type'] == 'waiting') {
            								$items[$id]['type'] = 'waiting_time';
            						}
            						else if ($item['type'] == 'luggage') {
            								$items[$id]['name'] = 'Suitcase';
            								$items[$id]['type'] = 'other';
            						}
            						else if ($item['type'] == 'hand_luggage') {
            								$items[$id]['name'] = 'Carry-on';
            								$items[$id]['type'] = 'other';
            						}
            						else {
            								$items[$id]['name'] = !empty($item['name']) ? $item['name'] : 'Charge';
            								$items[$id]['type'] = 'other';
            						}
            				}
            		}

            		foreach($checkItems as $name) {
            				$colName = isset($b->$name) ? $name : $name.'s';
            				$amount = !empty($amounts[$colName]) ? (int)$amounts[$colName] : 0;
            				$amount = $b->$colName - $amount;
            				if ($amount > 0) {
            						$items[] = [
            								'type' => $name,
            								'name' => '',
            								'value' => '0.00',
            								'amount' => $amount,
            						];
            				}
            				else {
            						$b->$colName += abs($amount);
            				}
            		}

            		$items = array_values($items);
            		$b->items = json_encode($items);
            		$b->save();
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('fields');
    }
}
