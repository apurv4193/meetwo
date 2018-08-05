<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtUdtUserDeviceTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `mt_udt_user_device_token` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
                `udt_user_id` bigint(20) unsigned NOT NULL,
                `udt_device_token` varchar(255) DEFAULT NULL,
                `udt_device_type` tinyint(1) NOT NULL COMMENT '1=>Android, 2=>iPhone',
                `udt_device_id` varchar(20) NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
                `updated_at` timestamp NOT NULL COMMENT 'timestamp',
                `deleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;";

        DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mt_udt_user_device_token');
    }
}
