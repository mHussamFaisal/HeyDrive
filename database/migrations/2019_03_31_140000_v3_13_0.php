<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3130 extends Migration
{
    public function _dropIndexIfExist($tableName, $indexName, $prefix = null)
    {
        if ($prefix == null) {
            $prefix = get_db_prefix();
        }

        try {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexName, $prefix) {
                $indexes = collect(\DB::select("SHOW INDEXES FROM `{$prefix}{$tableName}`"))->pluck('Key_name')->toArray();
                $matches = preg_grep('/(.*)'. $indexName .'$/i', $indexes);

                foreach ($matches as $key => $value) {
                    $table->dropIndex($value);
                }
            });
        }
        catch (\Exception $e) {
            \Log::error('Migration V3130 (dropIndex): '. $e->getMessage());
        }
    }

    public function up()
    {
        $prefix = get_db_prefix();

        $this->_dropIndexIfExist('bases', 'ref_id', $prefix);
        $this->_dropIndexIfExist('bases', 'bases_ref_id_index', $prefix);
        $this->_dropIndexIfExist('booking', 'profile_id', $prefix);
        $this->_dropIndexIfExist('category', 'profile_id', $prefix);
        $this->_dropIndexIfExist('charge', 'profile_id', $prefix);
        $this->_dropIndexIfExist('config', 'profile_id', $prefix);
        $this->_dropIndexIfExist('events', 'ref_id', $prefix);
        $this->_dropIndexIfExist('events', 'events_ref_id_index', $prefix);
        $this->_dropIndexIfExist('feedback', 'feedback_parent_id_parent_type_index', $prefix);
        $this->_dropIndexIfExist('file', 'file_type', $prefix);
        $this->_dropIndexIfExist('fields', 'fields_parent_id_parent_type_index', $prefix);
        $this->_dropIndexIfExist('location', 'profile_id', $prefix);
        $this->_dropIndexIfExist('locations', 'locations_parent_id_parent_type_index', $prefix);
        $this->_dropIndexIfExist('payment', 'profile_id', $prefix);
        $this->_dropIndexIfExist('scheduled_routes', 'scheduled_routes_parent_id_parent_type_index', $prefix);
        $this->_dropIndexIfExist('services', 'services_parent_id_parent_type_index', $prefix);
        $this->_dropIndexIfExist('settings', 'settings_parent_id_parent_type_index', $prefix);
        $this->_dropIndexIfExist('transactions', 'ref_id', $prefix);
        $this->_dropIndexIfExist('transactions', 'transactions_ref_id_index', $prefix);
        $this->_dropIndexIfExist('vehicle', 'profile_id', $prefix);

        $sql = "
            ALTER TABLE `{$prefix}bases` CHANGE `ref_type` `relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `ref_id` `relation_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}bases` ADD INDEX `relation_id` (`relation_id`) USING BTREE;
            ALTER TABLE `{$prefix}booking` CHANGE `profile_id` `site_id` SMALLINT(5) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}booking` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}category` CHANGE `profile_id` `site_id` SMALLINT(5) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}category` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}charge` CHANGE `profile_id` `site_id` SMALLINT(5) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}charge` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}config` CHANGE `profile_id` `site_id` SMALLINT(5) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}config` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}discount` CHANGE `profile_id` `site_id` TINYINT(5) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}discount` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}events` CHANGE `ref_type` `relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `ref_id` `relation_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}events` ADD INDEX `relation_id` (`relation_id`) USING BTREE;
            ALTER TABLE `{$prefix}excluded_routes` CHANGE `profile_id` `site_id` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}excluded_routes` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}feedback` CHANGE `parent_id` `relation_id` INT(10) UNSIGNED NOT NULL, CHANGE `parent_type` `relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
            ALTER TABLE `{$prefix}feedback` ADD INDEX `feedback_relation_id_relation_type_index` (`relation_id`, `relation_type`) USING BTREE;
            ALTER TABLE `{$prefix}fields` CHANGE `parent_id` `relation_id` INT(10) UNSIGNED NOT NULL DEFAULT '0', CHANGE `parent_type` `relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
            ALTER TABLE `{$prefix}fields` ADD INDEX `fields_relation_id_relation_type_index` (`relation_id`, `relation_type`) USING BTREE;
            ALTER TABLE `{$prefix}file` CHANGE `file_profile_id` `file_site_id` INT(10) UNSIGNED NOT NULL DEFAULT '0', CHANGE `file_type` `file_relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'category', CHANGE `file_ref_id` `file_relation_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}file` ADD INDEX `file_relation_type` (`file_relation_type`) USING BTREE;
            ALTER TABLE `{$prefix}fixed_prices` CHANGE `profile_id` `site_id` INT(10) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}fixed_prices` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}location` CHANGE `profile_id` `site_id` INT(10) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}location` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}locations` CHANGE `parent_id` `relation_id` INT(10) UNSIGNED NOT NULL, CHANGE `parent_type` `relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
            ALTER TABLE `{$prefix}locations` ADD INDEX `locations_relation_id_relation_type_index` (`relation_id`, `relation_type`) USING BTREE;
            ALTER TABLE `{$prefix}meeting_point` CHANGE `profile_id` `site_id` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}meeting_point` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}payment` CHANGE `profile_id` `site_id` SMALLINT(5) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}payment` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}scheduled_routes` CHANGE `parent_id` `relation_id` INT(10) UNSIGNED NOT NULL, CHANGE `parent_type` `relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
            ALTER TABLE `{$prefix}scheduled_routes` ADD INDEX `scheduled_routes_relation_id_relation_type_index` (`relation_id`, `relation_type`) USING BTREE;
            ALTER TABLE `{$prefix}services` CHANGE `parent_id` `relation_id` INT(10) UNSIGNED NOT NULL, CHANGE `parent_type` `relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
            ALTER TABLE `{$prefix}services` ADD INDEX `services_relation_id_relation_type_index` (`relation_id`, `relation_type`) USING BTREE;
            ALTER TABLE `{$prefix}settings` CHANGE `parent_id` `relation_id` INT(10) UNSIGNED NOT NULL, CHANGE `parent_type` `relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
            ALTER TABLE `{$prefix}settings` ADD INDEX `settings_relation_id_relation_type_index` (`relation_id`, `relation_type`) USING BTREE;
            ALTER TABLE `{$prefix}transactions` CHANGE `ref_type` `relation_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `ref_id` `relation_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}transactions` ADD INDEX `relation_id` (`relation_id`) USING BTREE;
            ALTER TABLE `{$prefix}user` CHANGE `profile_id` `site_id` TINYINT(5) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}user` ADD INDEX `site_id` (`site_id`) USING BTREE;
            ALTER TABLE `{$prefix}vehicle` CHANGE `profile_id` `site_id` SMALLINT(5) NOT NULL DEFAULT '0';
            ALTER TABLE `{$prefix}vehicle` ADD INDEX `site_id` (`site_id`) USING BTREE;
        ";

        if ( $sql ) {
            DB::unprepared($sql);
        }
    }

    public function down()
    {
        //
    }
}
