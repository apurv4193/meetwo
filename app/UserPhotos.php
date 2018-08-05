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

class UserPhotos extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_up_user_photos';
    protected $fillable = ['id', 'up_user_id', 'up_photo_name', 'up_is_profile_photo', 'created_at', 'updated_at', 'deleted'];

    public function getExistingUserPhotosDetail($userId, $imageId) {
        $userDetail = DB::select( DB::raw("SELECT up_photo_name
                                          FROM  " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . "
                                               where deleted = 1 and id =". $imageId));
        return $userDetail;
    }

    public function saveUserPhotosDetail($saveProfileImageData) {
        $userData = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->where('id', $saveProfileImageData['id'])->where('deleted', '1')->first();
        if (count($userData) > 0) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->where('id', $saveProfileImageData['id'])->update($saveProfileImageData);
        } else {
            $return = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->insert($saveProfileImageData);
            $saveProfileImageData['id'] = DB::getPdo()->lastInsertId();
        }
        $userDetail = DB::select( DB::raw("SELECT id AS image_id, up_photo_name AS image_url
                                          FROM  " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . "
                                               where deleted = 1 and id = ".$saveProfileImageData['id']));
        return $userDetail;
    }

    public function deleteUserPhotosDetail($saveProfileImageData) {
        $return = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->where('id', $saveProfileImageData['id'])->delete();
        return $return;
    }

    public function getExistingUserPhotosDetailByFacebbokId($facebookId) {
        $profileDetail = DB::select( DB::raw("SELECT up.up_photo_name
                                          FROM  " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS up
                                            join " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user on user.id = up.up_user_id and up.up_is_profile_photo = 1
                                            where up.deleted = 1 and user.u_fb_identifier =". $facebookId));
        return $profileDetail;
    }

    public function SetProfilePicByUserId($userId, $imageId) {
        $return = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->where('up_user_id', $userId)->where('up_is_profile_photo', 1)->where('deleted', '1')->update(['up_is_profile_photo'=>0]);

        $response = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->where('up_user_id', $userId)->where('id', $imageId)->where('deleted', '1')->update(['up_is_profile_photo'=>1]);

        $imageData = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))
                    ->where('id', $imageId)
                    ->where('deleted', '1')
                    ->first();
        return $imageData;
    }

    public function getUserPhotosDetailById($userId) {
        $userDetail = DB::select( DB::raw("SELECT *,GROUP_CONCAT(id)  AS p_id, GROUP_CONCAT(up_photo_name)  AS up_photo_name , GROUP_CONCAT(up_is_profile_photo) AS up_is_profile_photo
                                          FROM  " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . "
                                               where deleted = 1 and up_user_id =". $userId ." group by up_user_id"));
        return $userDetail;
    }

    public function setProfilePicByUser($userDetail) {
        $response = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->where('up_user_id', $userDetail['up_user_id'])->where('deleted', '1')->update(['up_is_profile_photo'=>0]);

        $response = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->where('id', $userDetail['id'])->where('deleted', '1')->update(['up_is_profile_photo'=>1]);

        return $response;
    }

    public function getAllPhotosDetail($userId) {
        $userDetail = DB::select( DB::raw("SELECT id
                                          FROM  " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . "
                                               where deleted = 1 and up_user_id =". $userId));
        return $userDetail;
    }
}