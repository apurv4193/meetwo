<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUApplozicIdToMtUUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mt_u_users', function($table) {
            $table->integer('u_applozic_id')->unsigned()->after('u_update_first_time');
            $table->string('u_applozic_device_key')->unique()->nullable()->after('u_applozic_id');
            $table->string('u_applozic_user_key')->unique()->nullable()->after('u_applozic_device_key');
            $table->string('u_applozic_user_encryption_key')->unique()->nullable()->after('u_applozic_user_key');
            $table->index(['u_applozic_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mt_u_users', function($table) {
            $table->dropColumn('u_applozic_id');
        });
    }
}
