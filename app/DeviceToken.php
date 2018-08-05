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

class DeviceToken extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_udt_user_device_token';
    protected $fillable = ['id', 'udt_user_id',  'udt_device_token', 'udt_device_type','udt_device_id', 'udt_appversion', 'created_at', 'updated_at', 'deleted'];

    public function saveDeviceToken($tokenDetail) {
        $deviceToken = $this->where('udt_device_id', $tokenDetail['udt_device_id'])->where('udt_user_id', $tokenDetail['udt_user_id'])->where('deleted', '1')->first();
        if (count($deviceToken) > 0) {
            $data = $this->where('udt_device_id', $tokenDetail['udt_device_id'])->where('udt_user_id', $tokenDetail['udt_user_id'])->update($tokenDetail);
        } else {
            $data = $this->insert($tokenDetail);
        }
        return $data;
    }
}