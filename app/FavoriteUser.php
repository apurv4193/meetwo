<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Config;
use DB;

class FavoriteUser extends Model
{
    protected $table = 'mt_fu_favorite_user';
    protected $fillable = ['id', 'fu_from_user_id', 'fu_to_user_id','fu_is_favorite', 'created_at', 'updated_at'];

    public function saveFavoriteUserDetail($saveFavoriteUserData) {
        if ($saveFavoriteUserData['fu_is_favorite'] == 1) {
            $result = DB::table(config::get('databaseconstants.TBL_MT_FAVORITE_USER'))->insert($saveFavoriteUserData);
        } else {
            $result = DB::table(config::get('databaseconstants.TBL_MT_FAVORITE_USER'))->where('fu_from_user_id', $saveFavoriteUserData['fu_from_user_id'])->where('fu_to_user_id', $saveFavoriteUserData['fu_to_user_id'])->delete();
        }
        return $result;
    }
}
