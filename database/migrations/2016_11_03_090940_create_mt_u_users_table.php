<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtUUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         $sql = "CREATE TABLE IF NOT EXISTS `mt_u_users` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
                `u_firstname` varchar(100) NOT NULL,
                `u_lastname` varchar(100) DEFAULT NULL,
                `u_email` varchar(100) DEFAULT NULL,
                `u_gender` tinyint(1) unsigned NOT NULL COMMENT '1 - Male, 2 - Female',
                `u_social_provider` varchar(100) NOT NULL COMMENT 'Sociad Media provider name',
                `u_fb_identifier` varchar(100) NOT NULL COMMENT 'Unique Identifier',
                `u_fb_accesstoken` varchar(255) DEFAULT NULL COMMENT 'access token',
                `u_phone` varchar(15) NOT NULL,
                `u_birthdate` date DEFAULT NULL,
                `u_age` int(20) NOT NULL,
                `u_description` text NOT NULL,
                `u_school` varchar(255) NOT NULL,
                `u_current_work` varchar(255) NOT NULL,
                `u_looking_for` tinyint(1) unsigned NOT NULL COMMENT '1->Male, 2->Female, 3->Both',
                `u_looking_distance` int(11) unsigned NOT NULL DEFAULT '50',
                `u_looking_age_min` int(11) unsigned NOT NULL DEFAULT '18',
                `u_looking_age_max` int(11) unsigned NOT NULL DEFAULT '30',
                `u_compatibility_notification` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1->Receive, 0->Not receive',
                `u_newchat_notification` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1->Receive, 0->Not receive',
                `u_acceptance_notification` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1->Receive, 0->Not receive',
                `u_country` varchar(255) NOT NULL DEFAULT '0',
                `u_pincode` varchar(6) DEFAULT NULL,
                `u_location` varchar(255) DEFAULT NULL,
                `u_latitude` decimal(11,8) NOT NULL,
                `u_longitude` decimal(11,8) NOT NULL,
                `u_profile_active` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1-Yes, 0-No',
                `is_question_attempted` tinyint(1) NOT NULL DEFAULT '0',
                `remember_token` varchar(100) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp',
                `updated_at` timestamp NULL DEFAULT NULL COMMENT 'timestamp',
                `deleted` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active , 2 - Inactive, 3 - Deleted',
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
        Schema::drop('mt_u_users');
    }
}
