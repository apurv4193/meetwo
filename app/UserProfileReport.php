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

class UserProfileReport extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_upr_user_profile_report';
    protected $fillable = ['id', 'upr_viewer_id', 'upr_viewed_id', 'upr_report_reason','created_at', 'updated_at', 'deleted'];

    public function saveUserProfileReportDetail($saveUserReportData) {
        $userData = DB::table(config::get('databaseconstants.TBL_UPR_USER_PROFILE_REPORT'))->where('upr_viewer_id', $saveUserReportData['upr_viewer_id'])->where('upr_viewed_id', $saveUserReportData['upr_viewed_id'])->where('deleted', '1')->first();
        if (count($userData) > 0) {
            $return = DB::table(config::get('databaseconstants.TBL_UPR_USER_PROFILE_REPORT'))->where('upr_viewer_id', $saveUserReportData['upr_viewer_id'])->where('upr_viewed_id', $saveUserReportData['upr_viewed_id'])->update($saveUserReportData);
        } else {
            $return = DB::table(config::get('databaseconstants.TBL_UPR_USER_PROFILE_REPORT'))->insert($saveUserReportData);
        }
        return $return;
    }

    public function getAllReportedUsers() {
        $result = DB::table(config::get('databaseconstants.TBL_UPR_USER_PROFILE_REPORT'))
                ->select('*')
                ->whereRaw('deleted IN (1,2)')
                ->orderBy('created_at', 'desc')
                ->get();
        return $result;
    }
}