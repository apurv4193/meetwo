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
use Log;
 
class UserLikeDislike extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_uld_user_like_dislike';
    protected $fillable = ['id', 'uld_viewer_id', 'uld_viewed_id', 'uld_is_liked', 'created_at', 'updated_at', 'deleted'];

    public function saveUserLikeDislikeDetail($saveUserLikeDislikeData) {
        $otherUserId = explode(",",$saveUserLikeDislikeData['uld_viewed_id']);
        $userLikeDislike = explode(",",$saveUserLikeDislikeData['uld_is_liked']);
        for ($i = 0; $i < count($otherUserId); $i++) {
            $data = [];
            $data['uld_viewer_id'] = $saveUserLikeDislikeData['uld_viewer_id'];
            $data['uld_viewed_id'] = $otherUserId[$i];
            $data['uld_is_liked'] = $userLikeDislike[$i];
            $likeDislikeData = $this->where('uld_viewer_id', $data['uld_viewer_id'])->where('uld_viewed_id', $data['uld_viewed_id'])->where('uld_is_liked', $data['uld_is_liked'])->where('deleted', '1')->first();
            if (!empty($likeDislikeData) && isset($likeDislikeData)) {
                $return = $this->where('uld_viewer_id', $data['uld_viewer_id'])->where('uld_viewed_id', $data['uld_viewed_id'])->where('uld_is_liked', $data['uld_is_liked'])->update($data);
            } else {
                Log::info($data['uld_viewer_id'] ." - ". $data['uld_viewed_id'] . " flip(like/dislike) #");
                $return = $this->insert($data);
            }
            //$return = DB::table(config::get('databaseconstants.TBL_MT_ULD_USER_LIKE_DISLIKE'))->insert($data);
        }
        return $return;
    }

    public function getAllLikeDisLikeDataTest() {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_ULD_USER_LIKE_DISLIKE'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->get();
        return $questionDetail;
    }

    public function getAllLikeDisLikeDataMonth($firstDay,$lastDay) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_ULD_USER_LIKE_DISLIKE'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->whereBetween('created_at', array($firstDay,$lastDay))
                        ->get();
        return $questionDetail;
    }

    public function getAllLikeDisLikeDataByDate($date) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_ULD_USER_LIKE_DISLIKE'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->where('created_at', 'like', $date)
                        ->get();
        return $questionDetail;
    }

    public function getUserLikeDislikeCountByUserId($userId) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_ULD_USER_LIKE_DISLIKE'))
                        ->select(DB::raw('COUNT(id) AS total'), 'uld_is_liked')
                        ->whereRaw('deleted IN (1,2)')
                        ->where('uld_viewer_id', $userId)
                        ->groupBy('uld_is_liked')
                        ->get();
        return $questionDetail;
    }

}