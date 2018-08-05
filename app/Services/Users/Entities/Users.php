<?php

namespace App\Services\Users\Entities;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'mt_u_users';
    
    protected $fillable = ['id', 'u_openfire_id', 'u_xmpp_user', 'u_firstname','u_lastname','u_email','u_gender','u_social_provider','u_fb_identifier','u_fb_accesstoken','u_phone','u_birthdate', 'u_age', 'u_description','u_school','u_current_work','u_looking_for','u_looking_distance','u_looking_age','u_compatibility_notification','u_newchat_notification','u_acceptance_notification','u_country','u_pincode','u_location','u_latitude','u_longitude','u_profile_active','is_question_attempted','remember_token', 'u_update_first_time', 'created_at', 'updated_at', 'deleted'];
    
    
}
