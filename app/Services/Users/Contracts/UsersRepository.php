<?php

namespace App\Services\Users\Contracts;

use App\Services\Repositories\BaseRepository;
use App\Services\Users\Entities\Users;

interface UsersRepository extends BaseRepository
{
    public function getAllUsersData();
    /**
     * @return array of all device tokens of user
    */
    public function getUserDeviceTokens($userId);

    /**
     * Save user device token
    */
    public function saveDeviceToken($tokenDetail);

    /**
     * Delete device token by $id
    */
    public function deleteDeviceToken($userId,$deviceId);

    /**
     * GetUser Detail BY $facebook_id
    */
    public function getUserDetailByFacebookId($facebook_id);

    /**
     * Save User Detail BY $userDetail
    */
    public function saveUserDetail($userDetail,$saveUserPhotosDetail);
}
