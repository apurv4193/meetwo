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

class UserPersonalityMatch extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_pm_personality_match';
    protected $fillable = ['id', 'pm_answerer_id', 'pm_questioner_id', 'pm_is_match', 'created_at', 'updated_at', 'deleted'];

    public function savePersonalityMatchData($savePersonalityDetail) {
        $data = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))->where('pm_answerer_id', $savePersonalityDetail['pm_answerer_id'])->where('pm_questioner_id', $savePersonalityDetail['pm_questioner_id'])->where('deleted', '1')->first();
        if (count($data) > 0) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))->where('pm_answerer_id', $savePersonalityDetail['pm_answerer_id'])->where('pm_questioner_id', $savePersonalityDetail['pm_questioner_id'])->update($savePersonalityDetail);
        } else {
            $return = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))->insert($savePersonalityDetail);
        }
        return $return;
    }

    public function getAllPersonalityMatchByDate($date) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->where('pm_is_match' ,'=', 1)
                        ->where('created_at', 'like', $date)
                        ->get();
        return $questionDetail;
    }

    public function getAllPersonalityMatch() {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->where('pm_is_match' ,'=', 1)
                        ->get();
        return $questionDetail;
    }

    public function getAllPersonalityTestByDate($date) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->where('created_at', 'like', $date)
                        ->get();
        return $questionDetail;
    }

    public function getAllPersonalityTest() {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->get();
        return $questionDetail;
    }

    public function getAllPersonalityMatchMonth($firstDay,$lastDay) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->where('pm_is_match' ,'=', 1)
                        ->whereBetween('created_at', array($firstDay,$lastDay))
                        ->get();
        return $questionDetail;
    }

    public function getAllPersonalityTestMonth($firstDay,$lastDay) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->whereBetween('created_at', array($firstDay,$lastDay))
                        ->get();
        return $questionDetail;
    }
}