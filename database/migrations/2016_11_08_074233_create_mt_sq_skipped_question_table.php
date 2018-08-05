<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtSqSkippedQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `mt_sq_skipped_question` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
                `sq_user_id` bigint(20) unsigned NOT NULL,
                `sq_question_id` bigint(20) unsigned NOT NULL,
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
        Schema::drop('mt_sq_skipped_question');
    }
}
