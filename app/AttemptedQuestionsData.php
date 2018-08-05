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

class AttemptedQuestionsData extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_attempted_questions';
    protected $fillable = ['id', 'aq_user_id', 'aq_question_id','created_at', 'updated_at', 'deleted'];


    public function deleteAllAttemptedQuestionsByUserIds($userId) {
        $questionDetail = DB::table(Config::get('databaseconstants.TBL_MT_ATTEMPTED_QUESTIONS'))
                        ->where('aq_user_id', $userId)
                        ->delete();

        return $questionDetail;
    }
}
