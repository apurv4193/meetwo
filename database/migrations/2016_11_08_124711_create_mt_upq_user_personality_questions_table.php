<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtUpqUserPersonalityQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `mt_upq_user_personality_questions` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
                `upq_user_id` bigint(20) unsigned NOT NULL,
                `upq_question_id` bigint(11) unsigned NOT NULL,
                `upq_option_id` int(11) unsigned NOT NULL,
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
        Schema::drop('mt_upq_user_personality_questions');
    }
}
