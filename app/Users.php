<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Config;
use DB;

class Users extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_u_users';
    protected $fillable = ['id', 'u_openfire_id', 'u_firstname', 'u_lastname', 'u_email', 'u_gender', 'u_social_provider', 'u_fb_identifier', 'u_fb_accesstoken', 'u_phone', 'u_birthdate', 'u_description', 'u_school', 'u_current_work', 'u_looking_for', 'u_looking_distance', 'u_looking_age', 'u_compatibility_notification', 'u_newchat_notification', 'u_acceptance_notification', 'u_country', 'u_pincode', 'u_location', 'u_latitude', 'u_longitude', 'u_profile_active', 'u_xmpp_user', 'remember_token', 'u_update_first_time', 'u_applozic_id', 'u_applozic_device_key', 'u_applozic_user_key', 'u_applozic_user_encryption_key', 'created_at', 'updated_at', 'deleted'];

    /**
     * @return UserDetail Object
      Parameters
      @$userId : user_id
    */
    public function getUserDetailByUserId($userId) {
        $userDetail = DB::select( DB::raw("SELECT user.* , GROUP_CONCAT(user_photo.up_photo_name) AS up_photo_name
                                          FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
                                            join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id
                                           where user.deleted IN (1,2) and user.id =". $userId ));
        return $userDetail;
    }

    public function getQuestionDetailWithOtherUserByUserId($userId) {
        $userDetail = DB::select( DB::raw("SELECT pm.*
                                          FROM  " . config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH') . " AS pm
                                           where pm.deleted IN (1,2) and pm.pm_answerer_id =". $userId ));
        $data = [];
        foreach ($userDetail as $key => $userId) {
            $id = $userId->pm_questioner_id;
            $otherUserDetail = DB::select( DB::raw("SELECT user.u_firstname,user.u_lastname, user.u_gender, GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url , GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_photo
                                          FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
                                            left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id
                                           where user.deleted IN (1,2) and user.id =". $id ." group by user.id"));
            foreach ($otherUserDetail as $key => $value) {
                $userData = [];
                $userData['first_name'] = $value->u_firstname;
                $userData['last_name'] = $value->u_lastname;
                $userData['gender'] = $value->u_gender;
                $userData['status']  = $userId->pm_is_match;
                $userData['profile_pic_url']  = $value->profile_pic_url;
                $userData['is_profile_photo']  = $value->is_profile_photo;
                $data[] = $userData;
            }
        }
        return $data;
    }

    /**
     * Update user score
     */
    public function updateUserScoreByUserId($userId, $total) {
        $result = $this->where('id', '=' , $userId)
                ->where('deleted', '<>', 3)
                ->update(['u_total_score' => $total]);
        return $result;
    }

    /**
     * Get all user detail for update score data
     */
    public function getAllUserDetail() {
        $userData = $this->select('id')
                ->where('deleted', '<>' , '3')
                ->get()
                ->toArray();

        return $userData;
    }
}