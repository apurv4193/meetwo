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

class UserDeleteCompatability extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_udc_user_delete_compatibility';
    protected $fillable = ['id', 'udc_user_id', 'udc_other_user_id', 'udc_delete_reason','created_at', 'updated_at', 'deleted'];

    public function saveUserDeleteCompatibilityDetail($saveUserDeleteData) {
        $userData = DB::table(config::get('databaseconstants.TBL_UDC_USER_DELETE_COMPATIBILITY'))->where('udc_user_id', $saveUserDeleteData['udc_user_id'])->where('udc_other_user_id', $saveUserDeleteData['udc_other_user_id'])->where('deleted', '1')->first();
        if (count($userData) > 0) {
            $return = DB::table(config::get('databaseconstants.TBL_UDC_USER_DELETE_COMPATIBILITY'))->where('udc_user_id', $saveUserDeleteData['udc_user_id'])->where('udc_other_user_id', $saveUserDeleteData['udc_other_user_id'])->update($saveUserDeleteData);
        } else {
            $return = DB::table(config::get('databaseconstants.TBL_UDC_USER_DELETE_COMPATIBILITY'))->insert($saveUserDeleteData);
        }
        return $return;
    }

    public function getAllReportedUsers() {
        $result = DB::table(config::get('databaseconstants.TBL_UPR_USER_PROFILE_REPORT'))
                ->select('*')
                ->whereRaw('deleted IN (1,2)')
                ->get();

        return $result;
    }
}