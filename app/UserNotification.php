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
use Helpers;

class UserNotification extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

    use Authenticatable,
        Authorizable,
        CanResetPassword;

    protected $table = 'mt_un_user_notification';
    protected $fillable = ['id', 'un_sender_id', 'un_receiver_id', 'un_notification_text', 'un_is_read', 'un_action', 'created_at', 'updated_at', 'deleted'];

    public function saveUserNotification($saveNotificationDetail) {
        $return = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->insert($saveNotificationDetail);
        $id = DB::getPdo()->lastInsertId();
        $data = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('id', $id)->where('deleted', '1')->first();
        $userData = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))->where('deleted', '1')->where('id', $saveNotificationDetail['un_receiver_id'])->first();

        $notificationArray = [];
        if (isset($userData) && !empty($userData)) {        
            $lat1 = $userData->u_latitude;
            $lon1 = $userData->u_longitude;
            
            if (!empty($data)) {
                $notificationArray['notification_id'] = $data->id;
                $notificationArray['notification_type'] = $data->un_type;
                $notificationArray['notification_text'] = $data->un_notification_text;
                $notificationArray['other_user_id'] = $data->un_sender_id;
                $notificationArray['notification_status'] = 1;

                $id = $data->un_sender_id;
                $userDetail = DB::select( DB::raw("SELECT user.id AS id , GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url, GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_pic, user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_birthdate AS birth_date, user.u_age As age, user.u_gender AS gender , user.u_country AS address, user.u_latitude AS location_latitude, user.u_longitude AS location_longitude, user.u_description AS description , user.u_school AS school , user.u_current_work AS work
                                              FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
                                                left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id
                                               where user.deleted = 1 and user.id =". $id ." group by user.id"));

                if (isset($userDetail) && !empty($userDetail)) {
                    /*$notificationArray['user_profile_url'] = $userDetail[0]->up_photo_name;
                    $notificationArray['is_profile_photo'] = $userDetail[0]->up_is_profile_photo;
                    $notificationArray['user_first_name'] = $userDetail[0]->u_firstname;
                    $notificationArray['user_last_name'] = $userDetail[0]->u_lastname;
                    $notificationArray['user_birth_date'] = date('d/m/Y',strtotime($userDetail[0]->u_birthdate));
                    $notificationArray['age'] = $userDetail[0]->u_age;
                    $notificationArray['gender'] = $userDetail[0]->u_gender;*/
                    foreach ($userDetail AS $key => $value) {
                        $userDetail[$key]->birth_date = date('d/m/Y',strtotime($value->birth_date));
                        $lat2 = $value->location_latitude;
                        $lon2 = $value->location_longitude;
                        $distance = Helpers::getDistance($lat1,$lon1,$lat2,$lon2);
                        $userDetail[$key]->distance_away = $distance;
                        $url = explode(",",$value->profile_pic_url);
                        $id = explode(",",$value->pic_id);
                        $profile_pic = explode(",",$value->is_profile_pic);
                        $allProfilePhotos = [];
                        for ($i = 0; $i < count($url); $i++) {
                            $profile = [];
                            $profile['url'] = $url[$i];
                            $profile['pic_id'] = $id[$i];
                            $profile['is_profile_pic'] = ($profile_pic[$i] == 1) ? true : false;
                            $allProfilePhotos[] = $profile;
                        }
                        $userDetail[$key]->profile_picture = $allProfilePhotos;
                        unset($value->profile_pic_url);
                        unset($value->pic_id);
                        unset($value->is_profile_pic);
                        $notificationArray['profile'] = $userDetail[$key];
                    }
                }
                $notificationArray['status'] = $data->un_action;
            }
        }
        return $notificationArray;
    }

    public function updateUserNotificationStatus($saveNotificationStatus) {
        $data = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('id', $saveNotificationStatus['id'])->where('deleted', '1')->first();
        if (count($data) > 0) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('id', $saveNotificationStatus['id'])->update($saveNotificationStatus);
            $userDetail = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))
                    ->selectRaw('id AS notification_id, un_sender_id AS other_user_id, un_action AS accepted')
                    ->where('id', '=', $saveNotificationStatus['id'])
                    ->where('deleted', '=', 1)
                    ->get();
            return $userDetail;
        }
    }

    public function getNotificationsData($id) {
        $data = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('id', $id)->where('deleted', '1')->first();
        $notificationArray = [];
        if (!empty($data)) {
            $notificationArray['notification_id'] = $data->id;
            $notificationArray['notification_type'] = $data->un_type;
            $notificationArray['notification_text'] = $data->un_notification_text;
            $notificationArray['other_user_id'] = $data->un_receiver_id;
            $notificationArray['notification_status'] = 0;

            $id = $data->un_receiver_id;
            $userDetail = DB::select(DB::raw("SELECT GROUP_CONCAT(user_photo.up_photo_name) AS up_photo_name ,GROUP_CONCAT(user_photo.up_is_profile_photo) AS up_is_profile_photo, user.u_firstname , user.u_lastname , user.u_birthdate , user.u_gender, user.u_age
                                          FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
                                            left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id
                                           where user.deleted = 1 and user.id =" . $id . " group by user.id"));

            if (isset($userDetail) && !empty($userDetail)) {
                $notificationArray['user_profile_url'] = $userDetail[0]->up_photo_name;
                $notificationArray['is_profile_photo'] = $userDetail[0]->up_is_profile_photo;
                $notificationArray['user_first_name'] = $userDetail[0]->u_firstname;
                $notificationArray['user_last_name'] = $userDetail[0]->u_lastname;
                $notificationArray['user_birth_date'] = date('d/m/Y', strtotime($userDetail[0]->u_birthdate));
                $notificationArray['age'] = $userDetail[0]->u_age;
                $notificationArray['gender'] = $userDetail[0]->u_gender;
            }
            $notificationArray['status'] = $data->un_action;
        }
        return $notificationArray;
    }

    public function saveUserNotificationForAdmin($saveNotificationDetail) {
        $data = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('un_sender_id', $saveNotificationDetail['un_sender_id'])->where('un_receiver_id', $saveNotificationDetail['un_receiver_id'])->where('deleted', '1')->first();
        if (empty($data)) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->insert($saveNotificationDetail);
        } else {
            $return = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('un_sender_id', $saveNotificationDetail['un_sender_id'])->where('un_receiver_id', $saveNotificationDetail['un_receiver_id'])->update($saveNotificationDetail);
        }
        return $return;
    }

}
