<?php

namespace App\Services\QuestionData\Repositories;

use DB;
use Auth;
use Config;
use App\Services\QuestionData\Contracts\QuestionDataRepository;
use App\Services\Repositories\Eloquent\EloquentBaseRepository;

class EloquentQuestionDataRepository extends EloquentBaseRepository implements QuestionDataRepository 
{
    /**
     * @return array of all the active Question Data
      Parameters
     */

    public function getAllQuestionData() {
        $questionsData = DB::table('mt_q_questions AS q')
                        ->leftJoin('mt_qo_question_options AS qo', 'q.id', '=', 'qo.qo_question_id')
                        ->select('q.*', DB::raw('GROUP_CONCAT(DISTINCT qo.qo_option) AS qo_option'),DB::raw('GROUP_CONCAT(DISTINCT qo.qo_fr_option) AS qo_fr_option'))
                        ->whereRaw('q.deleted IN (1,2)')
                        ->groupBy('q.id')
                        ->get();
        return $questionsData;
    }

    /**
     * @return Save Question Detail
      Parameters
      @$questionDetail : Array of Question Detail from Admin
    */
    public function saveQuestionDetail($questionDetail) {
        if (isset($questionDetail['id']) && $questionDetail['id'] != '' && $questionDetail['id'] > 0) {
            $return = $this->model->where('id', $questionDetail['id'])->update($questionDetail);
            return $questionDetail['id'];
        } else {
            $lastInsertId = DB::table(config::get('databaseconstants.TBL_MT_Q_QUESTIONS'))->insertGetId($questionDetail);
            return $lastInsertId;
        }
    }

    public function saveQuestionOptionDetail($questionId ,$data, $frData) {
        $questionData = DB::table(config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS'))->where('qo_question_id', $questionId)->get();
        if (isset($questionData) && !empty($questionData)) {
            for ($i = 0 ; $i < count($questionData) ; $i++) {
                $insertData['id'] = $questionData[$i]->id;
                $insertData['qo_question_id'] = $questionId;
                $insertData['qo_option'] = $data[$i];
                $insertData['qo_fr_option'] = $frData[$i];
                $return = DB::table(config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS'))->where('id', $questionData[$i]->id)->update($insertData);
            }
        } else {
            $return = DB::table(config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS'))->insert($data);
        }
        return $return;
    }

    /**
     * @return Delete Question Detail
      Parameters
      @$questionDetail : Array of Question Detail from Admin
    */
    public function deleteQuestionData($id) {
        $flag = true;
        $return = DB::table(Config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS'))
                 ->where('qo_question_id', $id)
                 ->update(['deleted' => Config::get('constant.DELETED_FLAG')]);
        $questionDelete = $this->model->find($id);
        $questionDelete->deleted = Config::get('constant.DELETED_FLAG');
        $response = $questionDelete->save();
        if ($response) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Get 10 personality question for user which are not attempted randomly
    */
    public function saveSkippedPersonalityQuestion($saveSkippedQuestion) {
        if (!empty($saveSkippedQuestion['sq_question_id'])) {
          $skippedQuestionData = DB::table(config::get('databaseconstants.TBL_MT_SQ_SKIPPED_QUESTION'))->where('sq_user_id', $saveSkippedQuestion['sq_user_id'])->where('sq_question_id', $saveSkippedQuestion['sq_question_id'])->get();
          if (count($skippedQuestionData) > 0) {
              $return = DB::table(config::get('databaseconstants.TBL_MT_SQ_SKIPPED_QUESTION'))->where('sq_question_id', $saveSkippedQuestion['sq_question_id'])->update($saveSkippedQuestion);
          } else {
              $return = DB::table(config::get('databaseconstants.TBL_MT_SQ_SKIPPED_QUESTION'))->insert($saveSkippedQuestion);
          }
          return $return;
        }
    }

    public function deleteSkippedPersonalityQuestion($userId) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_SQ_SKIPPED_QUESTION'))->where('sq_user_id', $userId)->delete();
            return $return;
    }

    /*
     * Get 10 personality question for user which are not attempted randomly
    */
    public function getNotAttemptedPersonalityQuestion($userId) {
        $questionDetail = DB::select( DB::raw("SELECT question_answer.qa_question_id,GROUP_CONCAT(skipped_question.sq_question_id) AS sq_question_id
                                          FROM  " . config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . " AS question_answer
                                            left join " . config::get('databaseconstants.TBL_MT_SQ_SKIPPED_QUESTION') . " AS skipped_question on question_answer.qa_user_id = skipped_question.sq_user_id
                                           where question_answer.deleted = 1 and question_answer.qa_user_id =". $userId ." group by question_answer.id" ));
        if (count($questionDetail) == Config::get('constant.QUESTION_LENGTH')) {
            $return = DB::table(Config::get('databaseconstants.TBL_MT_U_USERS'))
                        ->where('id', $userId)
                        ->update(['is_question_attempted' => Config::get('constant.QUESTION_ATTEMPTED_FLAG')]);
        }
        $question_id = [];
        foreach ($questionDetail as $key => $value) {
            if (!empty($value->qa_question_id)) {
                $question_id[] = $value->qa_question_id;
            }
            if (!empty($value->sq_question_id)) {
                $question_id[] = $value->sq_question_id;
            }
        }
        $question_id = array_unique($question_id);
        $questionId = implode(",",$question_id);
        $whereStr = '';
        if (!empty($questionId)) {
            $whereStr = 'AND temp.question_id NOT IN('.$questionId.')';
        }
        $questions = DB::select(DB::raw("SELECT
                                            	temp.*
                                            FROM (SELECT
                                            	question.id AS question_id,
                                            	q_question_text AS question,
                                                question.deleted,
                                            	GROUP_CONCAT(question_option.id) AS optionIds,
                                            	GROUP_CONCAT(qo_option) AS options

                                            FROM
                                            	" . config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question
                                            INNER JOIN " . config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS question_option ON question_option.qo_question_id = question.id
                                            GROUP BY
                                            	question.id) AS temp
                                            WHERE temp.deleted = 1 ".$whereStr), array());

        foreach ($questions as $key => $value) {
            $value->total_questions = 10;
            $value->attempted_questions = count($questionDetail);
            if (count($questionDetail) == 10) {
                $value->is_question_attempted = 1;
            } else {
                $value->is_question_attempted = 0;
            }
            $optionIds = explode(",", $value->optionIds);
            $options = explode(",", $value->options);
            unset($value->optionIds);
            unset($value->options);

            $optionsWithId = [];
            foreach ($options as $key1 => $option) {
                $temp = [];
                $temp['optionId'] = $optionIds[$key1];
                $temp['optionText'] = $option;
                $optionsWithId[] = $temp;
            }
            $questions[$key]->options = $optionsWithId;
            unset($value->deleted);
        }
        return $questions;
    }

    /*
     * Svae 10 personality question for user which are  attempted randomly
    */
    public function saveAttemptedPersonalityQuestion($saveQuestion) {
        $questions = explode(",",$saveQuestion['qa_question_id']);
        $options = explode(",",$saveQuestion['qa_option_id']);
        for ($i = 0; $i < count($questions); $i++) {
            $questionDeatil = [];
            $questionDeatil['qa_user_id'] = $saveQuestion['qa_user_id'];
            $questionDeatil['qa_question_id'] = $questions[$i];
            $questionDeatil['qa_option_id'] = $options[$i];

            $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_Q_QUESTIONS'))->where('id', $questions[$i])->get();
            $count = 0;
            if(!empty($questionDetail) && isset($questionDetail))
            {
                $count = $questionDetail[0]->q_total_answer + 1;
                $return = DB::table(config::get('databaseconstants.TBL_MT_Q_QUESTIONS'))->where('id', $questions[$i])->update(['q_total_answer' => $count]);
            }
            $questionData = DB::table(config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS'))->where('qa_user_id', $saveQuestion['qa_user_id'])->where('qa_question_id', $questions[$i])->get();
            if (!empty($questionData)) {
                $return = DB::table(config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS'))->where('qa_user_id', $saveQuestion['qa_user_id'])->where('qa_question_id', $questions[$i])->update($questionDeatil);
            } else{
                $return = DB::table(config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS'))->insert($questionDeatil);
            }
        }
        if (isset($return)) {
            $return = DB::table(Config::get('databaseconstants.TBL_MT_U_USERS'))
                        ->where('id', $saveQuestion['qa_user_id'])
                        ->update(['is_question_attempted' => Config::get('constant.QUESTION_ATTEMPTED_FLAG')]);
        }
        return $return;
    }

    /**
     * Get all  Attempted QuestionData by $userId
    */
    public function getAllAttemptedPersonalityQuestion($userId,$lang) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . " AS question_answer ")
                        ->join(config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question ", 'question.id', '=', 'question_answer.qa_question_id')
                        ->selectRaw('question_answer.qa_question_id,question_answer.qa_option_id,question.q_question_text')
                        ->where('question_answer.qa_user_id', '=' ,$userId)
                        ->where('question_answer.deleted','=',1)
                        ->get();
        $questionId = [];
        foreach ($questionDetail AS $key => $value) {
            $questionId[] = $value->qa_question_id;
        }
        $question_Id = '';
        $question_Id = implode(",",$questionId);
        $whereStr = '';
        if ($question_Id != '') {
            $question_Id = (isset($question_Id) && $question_Id != '') ? $question_Id : '';
            $whereStr = 'AND question_answer.qa_question_id IN('.$question_Id.')';
        }
        $allQuestions = DB::select(DB::raw("SELECT
                                            	temp.*,question_answer.qa_option_id AS correct_answer
                                            FROM (SELECT
                                            	question.id AS question_id,
                                            	q_question_text AS question,
                                                q_fr_question_text AS fr_question,
                                                question.deleted,
                                            	GROUP_CONCAT(question_option.id) AS optionIds,
                                            	GROUP_CONCAT(qo_option) AS options,
                                                GROUP_CONCAT(qo_fr_option) AS fr_options
                                            FROM
                                            	" . config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question
                                            INNER JOIN " . config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS question_option ON question_option.qo_question_id = question.id
                                            WHERE q_fr_question_text != ''
                                            GROUP BY
                                            	question.id) AS temp
                                            LEFT JOIN " . config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . " AS question_answer ON temp.question_id = question_answer.qa_question_id AND question_answer.qa_user_id = $userId
                                            WHERE temp.deleted = 1 ".$whereStr." AND question_answer.qa_user_id =". $userId), array()); 
        $allQuestionData = [];
        foreach ($allQuestions as $key => $value) {
            if (($lang == 'fr') && $value->fr_question != '') {
                $value->question = $value->fr_question;
                $value->options = $value->fr_options;
                unset($value->fr_options);
            } else if ($lang == 'en'){
                unset($value->fr_options);
            } else {
               unset($value->fr_options);
            }
            $value->question_no = $key+1;
            $optionIds = explode(",", $value->optionIds);
            $options = explode(",", $value->options);
            unset($value->optionIds);
            unset($value->options);

            $oprionsArray = [];
            $oprionsIdsArray = [];
            if ($lang == 'en') {
                for ($i = 0; $i < count($options); $i++) {
                    if (strtolower($options[$i]) == 'no') {
                        $no = $options[$i];
                        $noId = $optionIds[$i];
                    } else {
                        $yes = $options[$i];
                        $yesId = $optionIds[$i];
                    }
                }
            } else if ($value->fr_question == '') {
                for ($i = 0; $i < count($options); $i++) {
                    if (strtolower($options[$i]) == 'no') {
                        $no = $options[$i];
                        $noId = $optionIds[$i];
                    } else {
                        $yes = $options[$i];
                        $yesId = $optionIds[$i];
                    }
                }
            }else if (($lang == 'fr' || $lang == 'Fr')  && $value->fr_question != ''){
                for ($i = 0; $i < count($options); $i++) {
                    if (strtolower($options[$i]) == 'non') {
                        $no = $options[$i];
                        $noId = $optionIds[$i];
                    } else {
                        $yes = $options[$i];
                        $yesId = $optionIds[$i];
                    }
                }
            }
            unset($value->fr_question);
            $oprionsArray[0] = (isset($no) && $no != '') ? $no : '';
            $oprionsArray[1] = (isset($yes) && $yes != '') ? $yes : '';
            $oprionsIdsArray[0] = (isset($noId) && $noId != '') ? $noId : '';
            $oprionsIdsArray[1] = (isset($yesId) && $yesId != '') ? $yesId : '';
            $optionsWithId = [];
            foreach ($oprionsArray as $key1 => $option) {
                $temp = [];
                $temp['optionId'] = $oprionsIdsArray[$key1];
                $temp['optionText'] = $option;
                if ($temp['optionId'] != '') {
                    $optionsWithId[] = $temp;
                }
            }
            $allQuestions[$key]->options = $optionsWithId;
            unset($value->deleted);
        }
        return $allQuestions;
    }

    /**
     * Get Attempted QuestionData by $userId
    */
    public function getAllAttemptedQuestionByUserId($userId) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS'))
                        ->selectRaw('qa_question_id,qa_option_id')
                        ->where('qa_user_id', '=' ,$userId)
                        ->where('deleted','=',1)
                        ->get();
        return $questionDetail;
    }

    /*
     * Get 10 personality question for user which are not attempted randomly
    */
    public function getALlNotAttemptedPersonalityQuestion($slot,$lang,$userId,$questionType) {
        if ($slot > 0) {
            $slot = $slot * config::get('constant.PAGE_LIMIT');
        } else {
            $numberOfPage = 1;
        }

        $whereArr = [];
        $whereStr = '';

        $allAttemptedQuestionData = DB::table(config::get('databaseconstants.TBL_MT_ATTEMPTED_QUESTIONS'))->where('aq_user_id',$userId)->get();
        foreach($allAttemptedQuestionData AS $k => $v)
        {
            $whereArr[] = $v->aq_question_id;
        }

        $whereArr = array_unique($whereArr);

        if(!empty($whereArr) && count($whereArr) > 0)
        {
            $questionId = implode(",",$whereArr);
            $whereStr = ' AND question.id NOT IN('.$questionId.')';
        }

        $whereQType = '';

        if($questionType == 1)
        {
            $whereQType = ' AND question.q_question_type != 1';
        }

        $allQuestions = DB::select(DB::raw("SELECT
                                            	temp.*
                                            FROM (SELECT
                                            	question.id AS question_id,
                                            	q_question_text AS question,
                                                q_fr_question_text AS fr_question,
                                                question.deleted,
                                            	GROUP_CONCAT(question_option.id) AS optionIds,
                                            	GROUP_CONCAT(qo_option) AS options,
                                            	GROUP_CONCAT(qo_fr_option) AS fr_options,
                                                question.q_total_answer AS total_question

                                            FROM
                                            	" . config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question
                                            INNER JOIN " . config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS question_option ON question_option.qo_question_id = question.id
                                            WHERE q_fr_question_text != '' ". $whereStr ." ". $whereQType ."
                                            GROUP BY
                                            	question.id
                                            ORDER BY rand() ) AS temp
                                            WHERE temp.deleted = 1 ORDER BY temp.total_question LIMIT 20"), array());                                    
        foreach ($allQuestions as $key => $value) {
            $saveData = [];
            $saveData['aq_user_id'] = $userId;
            $saveData['aq_question_id'] = $value->question_id;
            $attemptedQuestionData = DB::table(config::get('databaseconstants.TBL_MT_ATTEMPTED_QUESTIONS'))->where('aq_user_id',$userId)->where('aq_question_id', $value->question_id)->get();
            if (count($attemptedQuestionData) > 0) {
                $return = DB::table(config::get('databaseconstants.TBL_MT_ATTEMPTED_QUESTIONS'))->where('aq_user_id',$userId)->where('aq_question_id', $value->question_id)->update($saveData);
            } else {
                $return = DB::table(config::get('databaseconstants.TBL_MT_ATTEMPTED_QUESTIONS'))->insert($saveData);
            }
            
            if ($lang == 'fr' && $value->fr_question != '') {
                $value->question = $value->fr_question;
                $value->options = $value->fr_options;
                unset($value->fr_options);
            } else if ($lang == 'en'){
                unset($value->fr_options);
            } else {
               unset($value->fr_options);
            }
            $optionIds = explode(",", $value->optionIds);
            $options = explode(",", $value->options);
            unset($value->optionIds);
            unset($value->options);
            unset($value->total_question);
            $oprionsArray = [];
            $oprionsIdsArray = [];
            if ($lang == 'en') {
                for ($i = 0; $i < count($options); $i++) {
                    if (strtolower($options[$i]) == 'no') {
                        $no = $options[$i];
                        $noId = $optionIds[$i];
                    } else {
                        $yes = $options[$i];
                        $yesId = $optionIds[$i];
                    }
                }
            } else if ($value->fr_question == '') {
                for ($i = 0; $i < count($options); $i++) {
                    if (strtolower($options[$i]) == 'no') {
                        $no = $options[$i];
                        $noId = $optionIds[$i];
                    } else {
                        $yes = $options[$i];
                        $yesId = $optionIds[$i];
                    }
                }
            }else if (($lang == 'fr')  && $value->fr_question != ''){
                for ($i = 0; $i < count($options); $i++) {
                    if (strtolower($options[$i]) == 'non') {
                        $no = $options[$i];
                        $noId = $optionIds[$i];
                    } else {
                        $yes = $options[$i];
                        $yesId = $optionIds[$i];
                    }
                }
            }
            unset($value->fr_question);
            $oprionsArray[0] = (isset($no) && $no != '') ? $no : '';
            $oprionsArray[1] = (isset($yes) && $yes != '') ? $yes : '';
            $oprionsIdsArray[0] = (isset($noId) && $noId != '') ? $noId : '';
            $oprionsIdsArray[1] = (isset($yesId) && $yesId != '') ? $yesId : '';
            $optionsWithId = [];
            foreach ($oprionsArray as $key1 => $option) {
                $temp = [];
                $temp['optionId'] = $oprionsIdsArray[$key1];
                $temp['optionText'] = $option;
                if ($temp['optionId'] != '') {
                    $optionsWithId[] = $temp;
                }
            }
            $allQuestions[$key]->options = $optionsWithId;
            unset($value->deleted);
        }
        shuffle($allQuestions);
        return $allQuestions;
    }

    public function GetUserPassedQuestionByUserId($userId) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS') . " AS personality_question ")
                        ->join(config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question ", 'question.id', '=', 'personality_question.upq_question_id')
                        ->join(config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS question_option ", 'question_option.qo_question_id', '=', 'personality_question.upq_question_id')
                        ->selectRaw('personality_question.upq_question_id , personality_question.upq_option_id, question.q_question_text, question_option.qo_option')
                        ->where('personality_question.upq_user_id', '=' ,$userId)
                        ->where('personality_question.deleted','=',1)
                        ->get();
        $allQuestionData = [];
        foreach ($questionDetail as $key => $question) {
            $questionTemp = [];
            $questionTemp['question_no'] = $key+1;
            $questionTemp['question'] = $question->q_question_text;
            $questionTemp['answer'] = $question->upq_option_id;
            $allQuestionData[] = $questionTemp;
        }
        return $allQuestionData;
    }

    public function getAllAttemptedQuestionCount() {
        $questionDetail = DB::select( DB::raw("SELECT q.*,GROUP_CONCAT(op.qo_option) AS qo_option, COUNT(answer.qa_question_id) AS total_question
                                          FROM  " . config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS q
                                            left join " . config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . " AS answer on answer.qa_question_id  = q.id
                                            left join " . config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS op on op.id  = answer.qa_option_id
                                           where q.deleted IN (1,2) group by q.id" ));
        $questionData = [];
        foreach ($questionDetail As $key => $value) {
            $yes = 0 ;
            $no = 0;
            $option = explode(",", $value->qo_option);
            foreach ($option As $op) {
                if ($op == 'YES' || $op == 'Yes') {
                    $yes++;
                } else if ($op == 'NO' || $op == 'No') {
                    $no++;
                }
            }
            $data = [];
            $data['Qid'] = $value->id;
            $data['total_question'] = $value->total_question;
            $data['yes'] = $yes;
            $data['no'] =  $no;
            $questionData[] = $data;
        }
        return $questionData;
    }

     public function getPassedPersonalityQuestion($userId,$otherUserId) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS') . " AS per_question ")
                        ->join(config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question ", 'question.id', '=', 'per_question.upq_question_id')
                        ->selectRaw('per_question.upq_question_id,per_question.upq_option_id,question.q_question_text')
                        ->where('per_question.upq_user_id', '=' ,$userId)
                        ->where('per_question.upq_questioner_id', '=' ,$otherUserId)
                        ->where('per_question.deleted','=',1)
                        ->get();
        $questionId = [];
        foreach ($questionDetail AS $key => $value) {
            $questionId[] = $value->upq_question_id;
        }
        $question_Id = '';
        $question_Id = implode(",",$questionId);
        $whereStr = '';
        if ($question_Id != '') {
            $question_Id = (isset($question_Id) && $question_Id != '') ? $question_Id : '';
            $whereStr = 'AND question_answer.upq_question_id IN('.$question_Id.')';
        }

        $allQuestions = DB::select(DB::raw("SELECT
                                            	temp.*,question_answer.upq_option_id AS correct_answer
                                            FROM (SELECT
                                            	question.id AS question_id,
                                            	q_question_text AS question,
                                                question.deleted,
                                            	GROUP_CONCAT(question_option.id) AS optionIds,
                                            	GROUP_CONCAT(qo_option) AS options
                                            FROM
                                            	" . config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question
                                            INNER JOIN " . config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS question_option ON question_option.qo_question_id = question.id
                                            GROUP BY
                                            	question.id) AS temp
                                            LEFT JOIN " . config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS') . " AS question_answer ON temp.question_id = question_answer.upq_question_id AND question_answer.upq_user_id = $userId
                                            WHERE temp.deleted = 1 ".$whereStr." AND question_answer.upq_user_id =". $userId ." AND question_answer.upq_questioner_id =". $otherUserId), array());
        $allQuestionData = [];
        foreach ($allQuestions as $key => $value) {
            $value->question_no = $key+1;
            $optionIds = explode(",", $value->optionIds);
            $options = explode(",", $value->options);
            unset($value->optionIds);
            unset($value->options);

            $oprionsArray = [];
            $oprionsIdsArray = [];
            for ($i = 0; $i < count($options); $i++) {
                if ($options[$i] == 'NO' ||  $options[$i] == 'No') {
                    $no = $options[$i];
                    $noId = $optionIds[$i];
                } else {
                    $yes = $options[$i];
                    $yesId = $optionIds[$i];
                }
            }
            $oprionsArray[0] = (isset($no) && $no != '') ? $no : '';
            $oprionsArray[1] = (isset($yes) && $yes != '') ? $yes : '';
            $oprionsIdsArray[0] = (isset($noId) && $noId != '') ? $noId : '';
            $oprionsIdsArray[1] = (isset($yesId) && $yesId != '') ? $yesId : '';
            $optionsWithId = [];
            foreach ($oprionsArray as $key1 => $option) {
                $temp = [];
                $temp['optionId'] = $oprionsIdsArray[$key1];
                $temp['optionText'] = $option;
                if ($temp['optionId'] != '') {
                    $optionsWithId[] = $temp;
                }
            }
            $allQuestions[$key]->options = $optionsWithId;
            unset($value->deleted);
        }
        return $allQuestions;
    }

    public function getTotlaNumberQuestion()
    {
        $questionsData = DB::table('mt_q_questions AS q')
                        ->whereRaw('q.deleted IN (1,2)')
                        ->count();
        return $questionsData;
    }

    public function getPersonalityQuestionForChatUser($userId,$otherUserId,$lang)
    {

        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS') . " AS per_question ")
                ->join(config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question ", 'question.id', '=', 'per_question.upq_question_id')
                ->selectRaw('per_question.upq_question_id,per_question.upq_option_id,question.q_question_text')
                ->where('per_question.upq_user_id', '=' ,$userId)
                ->where('per_question.upq_questioner_id', '=' ,$otherUserId)
                ->where('per_question.deleted','=',1)
                ->get();
        if (empty($questionDetail)) 
        {
            $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS') . " AS per_question ")
                    ->join(config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question ", 'question.id', '=', 'per_question.upq_question_id')
                    ->selectRaw('per_question.upq_question_id,per_question.upq_option_id,question.q_question_text')
                    ->where('per_question.upq_user_id', '=' ,$otherUserId)
                    ->where('per_question.upq_questioner_id', '=' ,$userId)
                    ->where('per_question.deleted','=',1)
                    ->get();
            $user_id = $otherUserId;
            $otherUserId = $userId;
            $userId = $user_id;
        }

        $questionId = [];
        $flag = 0;

        if(!empty($questionDetail))
        {
            foreach ($questionDetail AS $k => $val) {
                $questionId[] = $val->upq_question_id;
            }
            $question_Id = '';
            $question_Id = implode(",",$questionId);
            $whereStr = '';
            if ($question_Id != '') {
                $question_Id = (isset($question_Id) && $question_Id != '') ? $question_Id : '';
                $whereStr = 'AND question_answer.upq_question_id IN('.$question_Id.')';
            }

            // $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question ")
            //         ->join(config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS question_option ", 'question.id', '=', 'question_option.qo_question_id')

            //         ->join(config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . " AS question_ans ", function ($join) use ($otherUserId){
            //             $join->on('question.id', '=', 'question_ans.qa_question_id')
            //                  ->where('question_ans.qa_user_id', '=', $otherUserId);
            //         })

            //         ->leftjoin(config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS') . " AS question_answer ", function ($join) use ($userId){
            //             $join->on('question.id', '=', 'question_answer.upq_question_id')
            //                 ->where('question_ans.qa_user_id', '=', $userId);
            //                  //->where('question_answer.upq_user_id' != '');
            //         })

            //         ->select('question.id AS question_id')
            //         ->where('question_answer.upq_questioner_id', '=' ,$otherUserId)
            //         ->where('question_answer.upq_user_id', '=' ,$userId)
            //         //->where('q_fr_question_text', '!=', '')
            //         ->where('question.deleted','=', '1')
            //         ->get();

            // echo "<pre>";
            // print_r($questionDetail);
            // exit;

            $allQuestions = DB::select(DB::raw("SELECT
                    temp.*,question_answer.upq_option_id
                FROM (SELECT
                    question.id AS question_id,
                    q_question_text AS question,
                    q_fr_question_text AS fr_question,
                    question.deleted,
                    question_ans.qa_option_id,
                    GROUP_CONCAT(question_option.id) AS optionIds,
                    GROUP_CONCAT(qo_option) AS options,
                    GROUP_CONCAT(qo_fr_option) AS fr_options
                FROM
                    " . config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question
                INNER JOIN " . config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS question_option ON question_option.qo_question_id = question.id
                INNER JOIN " . config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . " AS question_ans ON question_ans.qa_question_id = question.id where question_ans.qa_user_id = $otherUserId
                AND q_fr_question_text != ''
                GROUP BY
                    question.id) AS temp
                LEFT JOIN " . config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS') . " AS question_answer ON temp.question_id = question_answer.upq_question_id AND question_answer.upq_user_id = ". $userId ."
                WHERE temp.deleted = 1 ".$whereStr." AND question_answer.upq_user_id =". $userId ." AND question_answer.upq_questioner_id =". $otherUserId), array());
            foreach ($allQuestions as $key => $value) 
            {
                if ($lang == 'fr' && $value->fr_question != '') {
                    $value->question = $value->fr_question;
                    $value->options = $value->fr_options;
                    unset($value->fr_options);
                } else if ($lang == 'en'){
                    unset($value->fr_options);
                } else {
                   unset($value->fr_options);
                }
                if ($flag == 1) {
                    $value->user_answer = $value->qa_option_id;
                    $value->other_user_answer = $value->upq_option_id;
                } else {
                    $value->other_user_answer = $value->qa_option_id;
                    $value->user_answer = $value->upq_option_id;
                }
                unset($value->qa_option_id);
                unset($value->upq_option_id);
                $value->question_no = $key+1;
                $optionIds = explode(",", $value->optionIds);
                $options = explode(",", $value->options);
                unset($value->optionIds);
                unset($value->options);

                $oprionsArray = [];
                $oprionsIdsArray = [];
                if ($lang == 'en') {
                    for ($i = 0; $i < count($options); $i++) {
                        if (strtolower($options[$i]) == 'no') {
                            $no = $options[$i];
                            $noId = $optionIds[$i];
                        } else {
                            $yes = $options[$i];
                            $yesId = $optionIds[$i];
                        }
                    }
                } else if ($value->fr_question == '') {
                    for ($i = 0; $i < count($options); $i++) {
                        if (strtolower($options[$i]) == 'no') {
                            $no = $options[$i];
                            $noId = $optionIds[$i];
                        } else {
                            $yes = $options[$i];
                            $yesId = $optionIds[$i];
                        }
                    }
                }else if (($lang == 'fr')  && $value->fr_question != ''){
                    for ($i = 0; $i < count($options); $i++) {
                        if (strtolower($options[$i]) == 'non') {
                            $no = $options[$i];
                            $noId = $optionIds[$i];
                        } else {
                            $yes = $options[$i];
                            $yesId = $optionIds[$i];
                        }
                    }
                }
                unset($value->fr_question);
                $oprionsArray[0] = (isset($no) && $no != '') ? $no : '';
                $oprionsArray[1] = (isset($yes) && $yes != '') ? $yes : '';
                $oprionsIdsArray[0] = (isset($noId) && $noId != '') ? $noId : '';
                $oprionsIdsArray[1] = (isset($yesId) && $yesId != '') ? $yesId : '';
                $optionsWithId = [];
                foreach ($oprionsArray as $key1 => $option) {
                    $temp = [];
                    $temp['optionId'] = $oprionsIdsArray[$key1];
                    $temp['optionText'] = $option;
                    if ($temp['optionId'] != '') {
                        $optionsWithId[] = $temp;
                    }
                }
                $allQuestions[$key]->options = $optionsWithId;
                unset($value->deleted);
            }
            return $allQuestions;
        }
    }

}