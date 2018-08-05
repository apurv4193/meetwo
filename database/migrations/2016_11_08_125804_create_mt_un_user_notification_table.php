<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtUnUserNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `mt_un_user_notification` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
                `un_sender_id` bigint(11) unsigned NOT NULL,
                `un_receiver_id` bigint(11) unsigned NOT NULL,
                `un_notification_text` text NOT NULL,
                `un_is_read` tinyint(1) unsigned NOT NULL COMMENT '1->read, 0->unread',
                `un_action` tinyint(1) unsigned NOT NULL COMMENT '0->Pending, 1->Accept, 2->Decline',
                `un_type` tinyint(1) NOT NULL COMMENT '1->Accept/Decline notification  2->Information',
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
        Schema::drop('mt_un_user_notification');
    }
}
