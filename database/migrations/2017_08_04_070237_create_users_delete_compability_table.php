<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersDeleteCompabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `mt_udc_user_delete_compatibility` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
                `udc_user_id` bigint(11) unsigned NOT NULL,
                `udc_other_user_id` bigint(11) unsigned NOT NULL,
                `udc_delete_reason` varchar(20) NOT NULL COMMENT '1 - Inapropriate Messages ,2 - Inapropriate Photos,3 - Spamming or Robot, 4 - Underage User, 5 - Other Reason',
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
        Schema::drop('`mt_udc_user_delete_compatibility`');
    }
}
