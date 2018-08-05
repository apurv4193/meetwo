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

class UserPersonalityQuestions extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_upq_user_personality_questions';
    protected $fillable = ['id', 'upq_user_id', 'upq_questioner_id', 'upq_question_id', 'upq_option_id', 'created_at', 'updated_at', 'deleted'];

    public function saveUserPersonalityQuestionsDetail($saveQuestionDetail) {
        $questions = explode(",",$saveQuestionDetail['upq_question_id']);
        $options = explode(",",$saveQuestionDetail['upq_option_id']);
        for ($i = 0; $i < count($questions); $i++) {
            $questionDeatil = [];
            $questionDeatil['upq_user_id'] = $saveQuestionDetail['upq_user_id'];
            $questionDeatil['upq_questioner_id'] = $saveQuestionDetail['upq_questioner_id'];
            $questionDeatil['upq_question_id'] = $questions[$i];
            $questionDeatil['upq_option_id'] = $options[$i];
            $return = DB::table(config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS'))->insert($questionDeatil);
        }
        return $return;
    }
}
