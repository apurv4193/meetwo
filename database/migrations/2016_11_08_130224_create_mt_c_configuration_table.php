<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtCConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `mt_c_configuration` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
                `c_key` varchar(255) NOT NULL,
                `c_value` varchar(255) NOT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
                `updated_at` timestamp NULL DEFAULT NULL COMMENT 'timestamp',
                `deleted` tinyint(1) DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

        DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mt_c_configuration');
    }
}
