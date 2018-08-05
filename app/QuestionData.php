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

class QuestionData extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_q_questions';
    protected $fillable = ['id', 'q_question_text', 'q_difficulty', 'q_importance','created_at', 'updated_at', 'deleted'];

    /**
     * Get all  Attempted QuestionData by $userId
    */
    public function getQuestionDetailByUserId($userId) {
        $questionDetail = DB::select( DB::raw("SELECT question_answer.qa_question_id,question_answer.qa_option_id, question_option.qo_option, question.q_question_text
                                          FROM  " . config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . " AS question_answer
                                            join " . config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question on question.id = question_answer.qa_question_id
                                            join " . config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS question_option on question_option.id = question_answer.qa_option_id
                                           where question_answer.deleted IN (1,2) and question_answer.qa_user_id =". $userId ." group by question.id"));

        return $questionDetail;
    }
}
