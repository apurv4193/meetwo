<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailchatMessageCountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "CREATE TABLE `mt_failchat_message_click_count` (
            `id` int(50) NOT NULL COMMENT 'Primary key',
            `fmc_user_id` int(50) NOT NULL,
            `fmc_message_type` int(50) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT NULL,
            `deleted` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

        DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mt_failchat_message_click_count');
    }
}
