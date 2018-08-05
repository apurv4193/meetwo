<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserScoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `mt_user_score` (
            `id` int(20) NOT NULL COMMENT 'Primary key',
            `us_user_id` int(20) NOT NULL,
            `us_point_section_id` int(20) NOT NULL COMMENT '1 - User Photo, 2 - Discription, 3 - Job, 4 - School',
            `us_point` int(20) NOT NULL,
            `us_phone_name` varchar(50) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
            `updated_at` timestamp NULL DEFAULT NULL COMMENT 'timestamp',
            `deleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
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
        Schema::drop('mt_user_score');
    }
}
