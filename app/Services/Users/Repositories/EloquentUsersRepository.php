<?php

namespace App\Services\Users\Repositories;

use DB;
use Auth;
use Config;
use Helpers;
use App\Services\Users\Contracts\UsersRepository;
use App\Services\Repositories\Eloquent\EloquentBaseRepository;


class EloquentUsersRepository extends EloquentBaseRepository implements UsersRepository {


    public function getAllUsersData() {
        $usersData = DB::table(Config::get('databaseconstants.TBL_MT_U_USERS'))
                    ->select(['*'])
                    ->whereRaw('deleted IN (1,2)')
                    ->orderBy('created_at', 'desc');
        return $usersData;
    }

    /**
     * @return array of all the device tokens for user
      Parameters
      @$userId : Int userId
    */
    public function getUserDeviceTokens($userId) {

    }

    /**
     * @return token details object
      Parameters
      @$tokenDetail : Array of device token detail
    */
    public function saveDeviceToken($tokenDetail) {
        $deviceToken = DB::table(config::get('databaseconstants.TBL_MT_U_USER_DEVICE_TOKEN'))->where('udt_device_token', $tokenDetail['udt_device_token'])->where('deleted', '1')->first();
        if (count($deviceToken) > 0) {
            $data = DB::table(config::get('databaseconstants.TBL_MT_U_USER_DEVICE_TOKEN'))->where('udt_device_token', $tokenDetail['udt_device_token'])->update($tokenDetail);
        } else {
            $data = DB::table(config::get('databaseconstants.TBL_MT_U_USER_DEVICE_TOKEN'))->insert($tokenDetail);
        }
        return $data;
    }

    /*
     * Delete device token
    */
    public function deleteDeviceToken($userId,$deviceId) {
        $data = DB::table(config::get('databaseconstants.TBL_MT_U_USER_DEVICE_TOKEN'))->where('udt_user_id', $userId)->where('udt_device_id', $deviceId)->delete();
    }

    /**
     * @return Boolean True/False
      Parameters
      @$userId : userId
    */
    public function checkActiveUser($userId) {
        $user = $this->model->where('deleted', '1')->where('id', $userId)->get();
        if ($user->count() > 0) {
            return true;
        } else {
           return false;
        }
    }

    /**
     * @return UserDetail Object
      Parameters
      @$userDetail : userDetail
    */
    public function saveUserDetail($userDetail,$saveUserPhotosDetail) {
        if ($userDetail['checkActionType'] == 3) {
            unset($userDetail['checkActionType']);
            $userData = $this->model->whereIn('deleted', [1,2])->where('u_fb_identifier', $userDetail['u_fb_identifier'])->get()->toArray();
        } else {
            unset($userDetail['checkActionType']);
            $userData = $this->model->where('deleted', 1)->where('u_fb_identifier', $userDetail['u_fb_identifier'])->get()->toArray();
            if (count($userData) > 0) {
                unset($userDetail['u_description']);
                unset($userDetail['u_school']);
                unset($userDetail['u_current_work']);
                //unset($userDetail['u_fb_identifier']);
                unset($userDetail['u_email']);
                unset($userDetail['u_looking_for']);
            }
        }
        if (!empty($userDetail['u_birthdate'])) {
            $dob = $userDetail['u_birthdate'];
            $dobDate = str_replace('/', '-', $dob);
            $userBirthdate = date("Y-m-d", strtotime($dobDate));
        }

        $userId = '';
        $userDetail['u_birthdate'] = (isset($userBirthdate) && $userBirthdate != '') ? $userBirthdate : '';
        if (count($userData) > 0) {
            $userId = $userData[0]['id'];
            $data = $this->model->where('u_fb_identifier', $userDetail['u_fb_identifier'])->update($userDetail);
            /*if (!empty($saveUserPhotosDetail)) {
                $saveUserPhotosDetail['up_user_id'] =  $userData[0]['id'];
                $saveUserPhotosDetail['up_is_profile_photo'] = 1;
                $photosData = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->where('up_user_id', $userData[0]['id'])->where('up_is_profile_photo', 1)->update($saveUserPhotosDetail);
            }*/
        } else {
            $userDetail['u_profile_active'] = 1;
            $userDetail['u_xmpp_user'] = 2;
            $data = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))->insert($userDetail);
            $userId = DB::getPdo()->lastInsertId();
            if (!empty($saveUserPhotosDetail)) {
                $userPhotosDetail = [];
                $userPhotosDetail['up_user_id'] = $userId;
                $userPhotosDetail['up_photo_name'] = $saveUserPhotosDetail['up_photo_name'];
                $userPhotosDetail['up_is_profile_photo'] = 1;
                $respons = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->insert($userPhotosDetail);
            }
        }
        return $userId;
    }

    /**
     * @return UserDetail Object
      Parameters
      @$facebook_id : facebook_id
    */
    public function getUserDetailByFacebookId($facebook_id) {
        $userDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS') . " AS user ")
                    ->leftjoin(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo ", 'user_photo.up_user_id', '=', 'user.id')
                    ->selectRaw('user.id AS user_id, user.u_applozic_id, user.u_applozic_device_key, user.u_openfire_id AS jabber_id, user.u_fb_identifier AS facebook_id , user.u_xmpp_user AS xmpp_user  , GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url , GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_photo, user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_email AS email , user.u_gender AS gender , user.u_description AS description , user.u_school AS school , user.u_current_work AS work , user.is_question_attempted , user.u_birthdate AS birth_date ,user.is_question_attempted,u_profile_active AS is_active')
                    ->where('user.u_fb_identifier', '=' ,$facebook_id)
                    ->where('user.deleted','=',1)
                    ->groupBy('user.id')
                    ->get();
        return $userDetail;
    }

    /**
     * @return UserDetail Object
      Parameters
      @$userId : userId
    */
    public function getMatchProfile($userId,$slot,$lang) { 
        $userData = $this->model->where('deleted', '1')->where('id', $userId)->get()->toArray();
        $gender = $userData[0]['u_gender'];
        $lookingfor = $userData[0]['u_looking_for'];
        $lat1 = $userData[0]['u_latitude'];
        $lon1 = $userData[0]['u_longitude'];
        $min_age = $userData[0]['u_looking_age_min'];
        $max_age = $userData[0]['u_looking_age_max'];
        $looking_distance = $userData[0]['u_looking_distance'];
        if ($slot > 0) {
            $slot = $slot * config::get('constant.PAGINATION_LIMIT');
        }

        $users = $this->model->whereBetween('u_age', [$min_age, $max_age])->where('u_looking_for','!=', 0)->get()->toArray();
        $userDataByAge = [];
        if (!empty ($users)) {
            foreach ($users AS $k => $user_data) {
                $userDataByAge[] = $user_data['id'];
            }
        }
        $user_str = '';
        $user_str = implode(",",$userDataByAge);
        if ($user_str != '') {
            $user_str = ' AND user.id IN('. $user_str.')';
        }

        $userArrayData = [];
        $allUserData = $this->model->where('deleted', '1')->where('id','!=', $userId)->where('u_looking_for','!=', 0)->get()->toArray();
        foreach ($allUserData AS $k => $u_data) {
            $lat2 = $u_data['u_latitude'];
            $lon2 = $u_data['u_longitude'];
            $distance = Helpers::getDistance($lat1,$lon1,$lat2,$lon2);
            if ($distance > $looking_distance) {
                $userArrayData[] = $u_data['id'];
            }
        }
        $u_str = '';
        $u_str = implode(",",$userArrayData);
        if ($u_str != '') {
            $u_str = ' AND user.id NOT IN('. $u_str.')';
        }

        $data = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))
                        ->select('*',DB::raw('GROUP_CONCAT(pm_questioner_id) AS questionerId'))
                        ->where('pm_answerer_id', '=' ,$userId)
                        ->orWhere('pm_questioner_id', '=' ,$userId)
                        ->where('pm_is_match', '=' ,1)
                        ->where('deleted','=',1)
                        ->groupBy('pm_answerer_id')
                        ->get();
        $whereUser = '';
        if (!empty($data)) {
            $whereUser = 'AND user.id NOT IN('. $data[0]->questionerId . ',' . $data[0]->pm_answerer_id.') ';
        }

        $whereStr = '';
        $finalArray = [];
//        if ($lookingfor != 0) {
//            $userArray = $this->model->where('deleted', '1')->where('u_looking_for','!=', 0)->where('u_gender','!=', 0)->get()->toArray();
//            if ($lookingfor == 1 || $lookingfor == 2) {
//                $whereStr = 'AND user.u_gender = '.$lookingfor .' AND (user.u_looking_for = '.$gender . ' OR user.u_looking_for = 3) ';
//            } else {
//                $whereStr = 'AND (user.u_looking_for = '.$gender . ' OR user.u_looking_for = '.$lookingfor . ')';
//            }
//        } else {
//            $whereStr = 'AND user.u_gender != '.$gender.' AND user.id != '.$userData[0]['id'];
//        }
        if ($lookingfor > 0) 
        {
            $userArray = $this->model->where('deleted', '1')->where('u_looking_for','>', 0)->where('u_gender','>', 0)->get()->toArray();
            if ($lookingfor == 1 || $lookingfor == 2) {
                $whereStr = 'AND user.u_gender = '.$lookingfor .' AND ( user.u_looking_for = '.$gender . ' OR user.u_looking_for = 3 )';
            } else {
                $whereStr = 'AND (user.u_looking_for = '.$gender . ' OR user.u_looking_for = '.$lookingfor . ')';
            }
        } else {
            $whereStr = 'AND user.u_gender != '.$gender.' AND user.id != '.$userData[0]['id'];
        }

        $return = DB::table(config::get('databaseconstants.TBL_MT_ULD_USER_LIKE_DISLIKE'))->where('uld_viewer_id', '=', $userId)->get();
        $response = DB::table(config::get('databaseconstants.TBL_MT_ULD_USER_LIKE_DISLIKE'))->where('uld_viewed_id', '=', $userId)->get();

        $Ids = [];
        if (!empty($return)) {
            foreach ($return AS $k => $val) {
                $Ids[] = $val->uld_viewed_id;
            }
        }
        if (!empty($response)) {
            foreach ($response AS $k => $val) {
                $Ids[] = $val->uld_viewer_id;
            }
        }

        $returnNotificationData = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('un_sender_id', '=', $userId)->get();
        $responseNotificationData = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('un_receiver_id', '=', $userId)->get();
        if (!empty($returnNotificationData)) {
            foreach ($returnNotificationData AS $k => $val) {
                $Ids[] = $val->un_receiver_id;
            }
        }
        if (!empty($responseNotificationData)) {
            foreach ($responseNotificationData AS $k => $val) {
                $Ids[] = $val->un_sender_id;
            }
        }

        $userIds = implode(",",$Ids);
        $whereU = '';
        if ($userIds != '') {
            $whereU = ' AND user.id NOT IN('. $userIds.')';
        }

//        $inactiveUserData = $this->model->where('deleted' ,'!=', '1')->where('u_looking_for','=', 0)->get()->toArray();
//        $whereInUser = '';
//        $whereInUserArray = [];                
//        if(!empty($inactiveUserData) && count($inactiveUserData) > 0)
//        {
//            foreach($inactiveUserData as $k => $in_user)
//            {
//                $whereInUserArray[] = $in_user['id'];
//            }
//        }
//        
//        $inactiveUserIds = '';
//        if(!empty($whereInUserArray)){
//            $inactiveUserIds = implode(",",$whereInUserArray);
//        }
//        if ($inactiveUserIds != '') {
//            $whereInUser = ' AND user.id NOT IN('. $inactiveUserIds.')';
//        }
        
//        $userDetail = DB::select( DB::raw("SELECT u_total_score, user.id AS id , user.u_looking_for , GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url, GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_pic, user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_birthdate AS birth_date, user.u_age As age, user.u_gender AS gender , user.u_country AS address, user.u_latitude AS location_latitude, user.u_longitude AS location_longitude, user.u_description AS description , user.u_school AS school , user.u_current_work AS work
//                                          FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
//                                          left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id
//                                               where user.deleted = 1 and user.u_fb_identifier != ".Config::get('constant.ADMIN_USER_ID')." and user.u_gender != 0 and user.u_latitude != 0 and user.u_longitude != 0 and user.u_profile_active = 1 ". $whereStr . " ". $whereUser . " " . $u_str . " " . $user_str . " " . $whereU . " and user.id != " . $userId ."  OR (user.u_looking_for = 0 and user.u_gender != 0 " . $whereU . $whereInUser . ") group by user.id order by user.u_total_score DESC LIMIT ".$slot.",". config::get('constant.PAGINATION_LIMIT')));
        
//      $whereInUser = '';
        
        $userDetail = DB::select( DB::raw("SELECT u_total_score, user.id AS id , user.u_looking_for , GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url, GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_pic, user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_birthdate AS birth_date, user.u_age As age, user.u_gender AS gender , user.u_country AS address, user.u_latitude AS location_latitude, user.u_longitude AS location_longitude, user.u_description AS description , user.u_school AS school , user.u_current_work AS work
        FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
        left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id
                                               where user.deleted = 1 and user.u_fb_identifier != ".Config::get('constant.ADMIN_USER_ID')." and user.u_gender > 0 and user.u_latitude > 0 and user.u_longitude > 0 and user.u_profile_active = 1 ". $whereStr . " ". $whereUser . " " . $u_str . " " . $user_str . " " . $whereU . " and user.id != " . $userId ." group by user.id order by user.u_total_score DESC LIMIT ".$slot.",". config::get('constant.PAGINATION_LIMIT')));
        
        /*$userDetailArray = DB::select( DB::raw("SELECT user.id AS id , user.u_looking_for , GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url, GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_pic, user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_birthdate AS birth_date, user.u_age As age, user.u_gender AS gender , user.u_country AS address, user.u_latitude AS location_latitude, user.u_longitude AS location_longitude, user.u_description AS description , user.u_school AS school , user.u_current_work AS work
                                          FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
                                          left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id
                                               where user.deleted = 1 and user.u_fb_identifier != ".Config::get('constant.ADMIN_USER_ID')." and user.u_looking_for = 0 and user.u_profile_active = 1 and user.id != " . $userId ." group by user.id order by user.created_at DESC LIMIT ".$slot.",". config::get('constant.PAGINATION_LIMIT')));*/
        //$userDetail = array_merge($userDetail,$userDetailArray);
        
        foreach ($userDetail as $key => $value) {
            $userDetail[$key]->birth_date = date('d/m/Y',strtotime($value->birth_date));
            $lat2 = $value->location_latitude;
            $lon2 = $value->location_longitude;
            $distance = Helpers::getDistance($lat1,$lon1,$lat2,$lon2);
            if ($value->u_looking_for == 0) {
                $userDetail[$key]->distance_away = mt_rand(1, 15);;
            } else {
                $userDetail[$key]->distance_away = $distance;
            }
//          unset($value->u_looking_for);
            unset($value->u_total_score);
            $url = explode(",",$value->profile_pic_url);
            $id = explode(",",$value->pic_id);
            $profile_pic = explode(",",$value->is_profile_pic);
            $allProfilePhotos = [];
            for ($i = 0; $i < count($url); $i++) {
                $profile = [];
                $profile['url'] = $url[$i];
                $profile['pic_id'] = $id[$i];
                $profile['is_profile_pic'] = ($profile_pic[$i] == 1) ? true : false;
                $allProfilePhotos[] = $profile;
            }
            $userDetail[$key]->profile_picture = $allProfilePhotos;
            unset($value->profile_pic_url);
            unset($value->pic_id);
            unset($value->is_profile_pic);
            $otherUserId = $value->id;
            $questionId1 = '';
            $questionId = '';
            $questionId = [];
            $questionId1 = [];
            $questionDetail = DB::table(Config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS'))
                        ->select('qa_question_id')
                        ->where('deleted','=',1)
                        ->where('qa_user_id','=',$userId)
                        ->get();
            foreach ($questionDetail AS $k => $qvalue) {
                $questionId[] = $qvalue->qa_question_id;
            }
            $questionsData = DB::select( DB::raw("SELECT qa_question_id
                                          FROM  " . config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . "
                                           where deleted = 1 and qa_user_id =". $otherUserId ));
            foreach ($questionsData AS $kk => $qvalue1) {
                $questionId1[] = $qvalue1->qa_question_id;
            }
            $result=array_diff($questionId1,$questionId);
            if (empty($result)) {
                $result = $questionId1;
            }
            $question_Id = '';
            $question_IdNew = '';
            for ($i = 0; $i < count($questionId1); $i++) {
                foreach ($result AS $kkk => $qvalue2 ) {
                    if ($qvalue2 == $questionId1[$i]) {
                        unset($questionId1[$i]);
                        break;
                    }
                }
            }
            if (count($result) == 1) {
                shuffle($questionId1);
                array_splice($questionId1, 3);
                $question_IdNew = implode(",",$questionId1);
            } else if (count($result) == 2) {
                shuffle($questionId1);
                array_splice($questionId1, 2);
                $question_IdNew = implode(",",$questionId1);
            } else if (count($result) == 3) {
                shuffle($questionId1);
                array_splice($questionId1, 1);
                $question_IdNew = implode(",",$questionId1);
            }
            $question_Id = implode(",",$result);
            $whereStr = '';
            if ($question_Id != '' || $question_IdNew != '') {
                $question_Id = (isset($question_Id) && $question_Id != '') ? $question_Id : '';
                $question_IdNew = (isset($question_IdNew) && $question_IdNew != '') ? ",".$question_IdNew : '';
                $whereStr = 'AND question_answer.qa_question_id IN('.$question_Id.$question_IdNew.')';
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
                                                    GROUP_CONCAT(qo_fr_option) AS fr_options
                                                FROM
                                                	" . config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question
                                                INNER JOIN " . config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS') . " AS question_option ON question_option.qo_question_id = question.id
                                                WHERE q_fr_question_text != ''
                                                GROUP BY
                                                	question.id) AS temp
                                                LEFT JOIN " . config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . " AS question_answer ON temp.question_id = question_answer.qa_question_id AND question_answer.qa_user_id = ". $otherUserId ."
                                                WHERE temp.deleted = 1 ".$whereStr." AND question_answer.qa_user_id =". $otherUserId), array());
            foreach ($allQuestions as $key2 => $val) {
                if ($lang == 'fr' && $val->fr_question != '') {
                    $val->question = $val->fr_question;
                    $val->options = $val->fr_options;
                    unset($val->fr_options);
                } else if ($lang == 'en'){
                    unset($val->fr_options);
                } else {
                   unset($val->fr_options);
                }
                $optionIds = explode(",", $val->optionIds);
                $options = explode(",", $val->options);
                unset($val->optionIds);
                unset($val->options);

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
                } else if ($val->fr_question == '') {
                    for ($i = 0; $i < count($options); $i++) {
                        if (strtolower($options[$i]) == 'no') {
                            $no = $options[$i];
                            $noId = $optionIds[$i];
                        } else {
                            $yes = $options[$i];
                            $yesId = $optionIds[$i];
                        }
                    }
                }else if (($lang == 'fr')  && $val->fr_question != ''){
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
                unset($val->fr_question);
                $oprionsArray[0] = (isset($no) && $no != '') ? $no : '';
                $oprionsArray[1] = (isset($yes) && $yes != '') ? $yes : '';
                $oprionsIdsArray[0] = (isset($noId) && $noId != '') ? $noId : '';
                $oprionsIdsArray[1] = (isset($yesId) && $yesId != '') ? $yesId : '';
                $optionsWithId = [];
                foreach ($oprionsArray as $key3 => $option) {
                    $temp = [];
                    $temp['optionId'] = $oprionsIdsArray[$key3];
                    $temp['optionText'] = $option;
                    if ($temp['optionId'] != '') {
                        $optionsWithId[] = $temp;
                    }
                }
                $allQuestions[$key2]->options = $optionsWithId;
                unset($value->deleted);
            }
            shuffle($allQuestions);
            array_splice($allQuestions, 4);
            $userDetail[$key]->questions = $allQuestions;
        }
        return $userDetail;
    }

    public function getOtherProfileDetail($userId,$otherUserId) {
        $userData = $this->model->where('deleted', '1')->where('id', $userId)->get()->toArray();
        $lat1 = $userData[0]['u_latitude'];
        $lon1 = $userData[0]['u_longitude'];
        $questionId1 = '';
        $questionId = '';
        $questionId = [];
        $questionId1 = [];
        $userDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS') . " AS user ")
                    ->leftjoin(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo ", 'user_photo.up_user_id', '=', 'user.id')
                    ->selectRaw('user.id AS id ,  user.u_looking_for, user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_birthdate AS birth_date, user.u_age As age, user.u_gender AS gender , user.u_country AS address, user.u_latitude AS location_latitude, user.u_longitude AS location_longitude, user.u_description AS description , user.u_school AS school , user.u_current_work AS work, GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url , GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo)  AS is_profile_pic')
                    ->where('user.id', '=' ,$otherUserId)
                    ->where('user.deleted','=',1)
                    ->groupBy('user.id')
                    ->get();
        $questionDetail = DB::table(Config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS'))
                        ->select('qa_question_id')
                        ->where('deleted','=',1)
                        ->where('qa_user_id','=',$userId)
                        ->get();
        foreach ($questionDetail AS $key => $value) {
            $questionId[] = $value->qa_question_id;
        }
        $questionsData = DB::select( DB::raw("SELECT qa_question_id
                                          FROM  " . config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . "
                                           where deleted = 1 and qa_user_id =". $otherUserId ));
        foreach ($questionsData AS $key => $value) {
            $questionId1[] = $value->qa_question_id;
        }
        $result=array_diff($questionId1,$questionId);
        $question_Id = '';
        $question_IdNew = '';
        for ($i = 0; $i < count($questionId1); $i++) {
            foreach ($result AS $key => $value ) {
                if ($value == $questionId1[$i]) {
                    unset($questionId1[$i]);
                    break;
                }
            }
        }
        if (count($result) == 1) {
            shuffle($questionId1);
            array_splice($questionId1, 3);
            $question_IdNew = implode(",",$questionId1);
        } else if (count($result) == 2) {
            shuffle($questionId1);
            array_splice($questionId1, 2);
            $question_IdNew = implode(",",$questionId1);
        } else if (count($result) == 3) {
            shuffle($questionId1);
            array_splice($questionId1, 1);
            $question_IdNew = implode(",",$questionId1);
        }
        $question_Id = implode(",",$result);

        $whereStr = '';
        if ($question_Id != '' || $question_IdNew != '') {
            $question_Id = (isset($question_Id) && $question_Id != '') ? $question_Id : '';
            $question_IdNew = (isset($question_IdNew) && $question_IdNew != '') ? ",".$question_IdNew : '';
            $whereStr = 'AND question_answer.qa_question_id IN('.$question_Id.$question_IdNew.')';
        }
        $allQuestions = DB::select(DB::raw("SELECT
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
                                            LEFT JOIN " . config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS') . " AS question_answer ON temp.question_id = question_answer.qa_question_id AND question_answer.qa_user_id = $otherUserId
                                            WHERE temp.deleted = 1 ".$whereStr." AND question_answer.qa_user_id =". $otherUserId), array());
        $allQuestionData = [];
        foreach ($allQuestions as $key => $value) {
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
        foreach ($userDetail as $key => $value) {
            $url = explode(",",$value->profile_pic_url);
            $id = explode(",",$value->pic_id);
            $profile_pic = explode(",",$value->is_profile_pic);
            $allProfilePhotos = [];
            for ($i = 0; $i < count($url); $i++) {
                $profile = [];
                $profile['url'] = $url[$i];
                $profile['pic_id'] = $id[$i];
                $profile['is_profile_pic'] = ($profile_pic[$i] == 1) ? true : false;
                $allProfilePhotos[] = $profile;
            }
            $userDetail[$key]->profile_picture = $allProfilePhotos;
            unset($value->profile_pic_url);
            unset($value->pic_id);
            unset($value->is_profile_pic);
            $userDetail[$key]->birth_date = date('d/m/Y',strtotime($value->birth_date));
            $lat2 = $value->location_latitude;
            $lon2 = $value->location_longitude;
            $distance = Helpers::getDistance($lat1,$lon1,$lat2,$lon2);
            if ($value->u_looking_for == 0) {
                $userDetail[$key]->distance_away = mt_rand(1, 15);;
            } else {
                $userDetail[$key]->distance_away = $distance;
            }
            unset($value->u_looking_for);
            //$userDetail[$key]->distance_away = $distance;
        }
        shuffle($allQuestions);
        array_splice($allQuestions, 4);
        $UserData = [];
        $UserData['profile'] = $userDetail;
        $UserData['questions'] = $allQuestions;

        return $UserData;
    }

    /**
     * @return user location details object
      Parameters
      @$saveUserLocationDetail : Array of user location detail
    */
    public function saveUserLocationDetail($saveUserLocationDetail) {
        $userLocatioData = $this->model->where('deleted', '1')->where('id', $saveUserLocationDetail['id'])->get()->toArray();
        if (count($userLocatioData) > 0) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))->where('id', $saveUserLocationDetail['id'])->update($saveUserLocationDetail);
            $userDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS') . " AS user ")
                        ->selectRaw('user.u_latitude AS latitude, user.u_longitude AS longitude')
                        ->where('user.id', '=' ,$saveUserLocationDetail['id'])
                        ->where('user.deleted','=',1)
                        ->get();
            return $userDetail;
        }
    }

    /**
     * @return user Profile details object
      Parameters
      @$saveUserProfileData : Array of user Profile detail
    */
    public function saveUserProfileDetail($saveUserProfileData) {
        $userProfileData = $this->model->where('deleted', '1')->where('id', $saveUserProfileData['id'])->get()->toArray();
        if (count($userProfileData) > 0) {
            if ($saveUserProfileData['field_id'] == 1) {
                $return = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))->where('id', $saveUserProfileData['id'])->update(['u_description'=>$saveUserProfileData['text']]);
            }
            if ($saveUserProfileData['field_id'] == 2) {
                $return = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))->where('id', $saveUserProfileData['id'])->update(['u_school'=>$saveUserProfileData['text']]);
            }
            if ($saveUserProfileData['field_id'] == 3) {
                $return = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))->where('id', $saveUserProfileData['id'])->update(['u_current_work'=>$saveUserProfileData['text']]);
            }
            $userDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))
                        ->selectRaw('u_current_work, u_school, u_description')
                        ->where('id', '=' ,$saveUserProfileData['id'])
                        ->where('deleted','=',1)
                        ->get();
            return $userDetail;
        }
    }

    /* @return user setting details object
      Parameters
      @$saveSettingDetail : Array of user Setting detail
    */
    public function saveUserSettingDetail($saveSettingDetail) {
        $userSettingData = $this->model->where('deleted', '1')->where('id', $saveSettingDetail['id'])->get()->toArray();
        if (count($userSettingData) > 0) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))->where('id', $saveSettingDetail['id'])->update($saveSettingDetail);
            return $return;
        }
    }

    /* @return user setting details object
      Parameters
      @$userId : user_id
    */
    public function getUserSettingDetail($userId) {
        $userSettingDetail = DB::select( DB::raw("SELECT u_looking_for AS looking_for , u_looking_distance AS distance , CONCAT(u_looking_age_min, ',' , u_looking_age_max) AS age_range , u_profile_active AS is_active , u_compatibility_notification AS noti_compatibility ,u_newchat_notification AS noti_new_chat , u_acceptance_notification AS noti_acceptance
                                          FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . "
                                               where deleted = 1 and id =".$userId));
        return $userSettingDetail;
    }

    public function deleteUserData($id) {
        $flag = true;
        $userDelete = $this->model->find($id);
        $userDelete->deleted = Config::get('constant.DELETED_FLAG');
        $response = $userDelete->save();
        if ($response) {
            return true;
        } else {
            return false;
        }
    }

    public function getTokenUserId($userId) {
        $tokenDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USER_DEVICE_TOKEN'))
                    ->selectRaw('udt_device_token , udt_device_type, udt_appversion')
                    ->where('udt_user_id', '=' ,$userId)
                    ->where('deleted','=',1)
                    ->get(); 
       return $tokenDetail;
    }

    public function deleteUserAllData($id) {
        $response = $this->model->where('id', '=', $id)->delete();
        if ($response) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_QA_QUESTION_ANSWERS'))->where('qa_user_id', '=', $id)->delete();

            $return = DB::table(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS'))->where('up_user_id', '=', $id)->delete();

            $return = DB::table(config::get('databaseconstants.TBL_MT_U_USER_DEVICE_TOKEN'))->where('udt_user_id', '=', $id)->delete();

            $return = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('un_sender_id', '=', $id)->orWhere('un_receiver_id', '=', $id)->delete();

            $return = DB::table(config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS'))->where('upq_user_id', '=', $id)->delete();

            $return = DB::table(config::get('databaseconstants.TBL_MT_ULD_USER_LIKE_DISLIKE'))->where('uld_viewer_id', '=', $id)->where('uld_viewed_id', '=', $id)->delete();

            $return = DB::table(config::get('databaseconstants.TBL_MT_PM_PERSONALITY_MATCH'))->where('pm_answerer_id', '=', $id)->orWhere('pm_questioner_id', '=', $id)->delete();

            $return = DB::table(config::get('databaseconstants.TBL_MT_USER_SCORE'))->where('us_user_id', '=', $id)->delete();

            if (isset($return)) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function getAllUserNotificationData($userId,$slot) {
        if ($slot > 0) {
            $slot = $slot * config::get('constant.PAGINATION_LIMIT');
        }
        $userData = $this->model->where('deleted', '1')->where('id', $userId)->get()->toArray();
        $lat1 = $userData[0]['u_latitude'];
        $lon1 = $userData[0]['u_longitude'];
        $notificationDetail = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('un_receiver_id', '=', $userId)->where('un_action', '=', 0)->skip($slot)->take(config::get('constant.PAGINATION_LIMIT'))->get();
        $notificatonData = [];
        foreach ($notificationDetail as $key => $notification) {
            $notificationArray = [];
            $notificationArray['notification_id'] = $notification->id;
            $notificationArray['notification_type'] = $notification->un_type;
            $notificationArray['notification_text'] = $notification->un_notification_text;
            $notificationArray['other_user_id'] = $notification->un_sender_id;

            $other_user_id = $notification->un_sender_id;
            $userDetail = DB::select( DB::raw("SELECT user.id AS id , GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url, GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_pic, user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_birthdate AS birth_date, user.u_age As age, user.u_gender AS gender , user.u_country AS address, user.u_latitude AS location_latitude, user.u_longitude AS location_longitude, user.u_description AS description , user.u_school AS school , user.u_current_work AS work
                                          FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
                                            left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id
                                           where user.deleted = 1 and user.id =". $other_user_id ." group by user.id"));

            if (isset($userDetail) && !empty($userDetail)) {
                /*$notificationArray['user_profile_url'] = $userDetail[0]->up_photo_name;
                $notificationArray['is_profile_photo'] = $userDetail[0]->up_is_profile_photo;
                $notificationArray['user_first_name'] = $userDetail[0]->u_firstname;
                $notificationArray['user_last_name'] = $userDetail[0]->u_lastname;
                $notificationArray['user_birth_date'] = date('d/m/Y',strtotime($userDetail[0]->u_birthdate));
                $notificationArray['age'] = $userDetail[0]->u_age;
                $notificationArray['gender'] = $userDetail[0]->u_gender;*/
                $notificationArray['status'] = $notification->un_action;
                foreach ($userDetail AS $key => $value) {
                    $userDetail[$key]->birth_date = date('d/m/Y',strtotime($value->birth_date));
                    $lat2 = $value->location_latitude;
                    $lon2 = $value->location_longitude;
                    $distance = Helpers::getDistance($lat1,$lon1,$lat2,$lon2);
                    $userDetail[$key]->distance_away = $distance;
                    $url = explode(",",$value->profile_pic_url);
                    $id = explode(",",$value->pic_id);
                    $profile_pic = explode(",",$value->is_profile_pic);
                    $allProfilePhotos = [];
                    for ($i = 0; $i < count($url); $i++) {
                        $profile = [];
                        $profile['url'] = $url[$i];
                        $profile['pic_id'] = $id[$i];
                        $profile['is_profile_pic'] = ($profile_pic[$i] == 1) ? true : false;
                        $allProfilePhotos[] = $profile;
                    }
                    $userDetail[$key]->profile_picture = $allProfilePhotos;
                    unset($value->profile_pic_url);
                    unset($value->pic_id);
                    unset($value->is_profile_pic);

                    $notificationArray['profile'] = $userDetail[$key];
                }
                $notificatonData[] = $notificationArray;
            }
        }
        return $notificatonData;
    }

    public function getAllUserDataForSendMail($userId, $otherUserId) {
        $userData = $this->model->where('id', '=', $userId)->get()->toArray();

        $usersDetail = $this->model->where('id', '=', $otherUserId)->get()->toArray();
        if (isset($userData) && isset($usersDetail)) {
            $data = [];
            $data['user_name'] = $userData[0]['u_firstname']." ".$userData[0]['u_lastname'];
            $data['other_user_name'] = $usersDetail[0]['u_firstname']." ".$usersDetail[0]['u_lastname'];
        }
        return $data;
    }

    public function checkActiveEmailExist($email, $id = '') {
        if ($id != '') {
            $user = $this->model->where('deleted', '1')->where('u_email', $email)->where('id', '!=', $id)->get();
        } else {
            $user = $this->model->where('deleted', '1')->where('u_email', $email)->get();
        }
        if ($user->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

     public function updateUserProfileDetail($saveUserProfileData) {
        $userSettingData = $this->model->where('deleted', '1')->where('id', $saveUserProfileData['id'])->get()->toArray();
        if (count($userSettingData) > 0) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))->where('id', $saveUserProfileData['id'])->update($saveUserProfileData);

            $userDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))
                    ->selectRaw('u_school AS text_school, u_description AS text_desc, u_current_work AS text_work, u_total_score AS score')
                    ->where('id', '=' ,$saveUserProfileData['id'])
                    ->where('deleted','=',1)
                    ->get();
            return $userDetail;
        }
    }

    public function getAllUserByDate($date) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->where('created_at', 'like', $date)
                        ->get();
        return $questionDetail;
    }

    public function getAllUsers() {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->get();
        return $questionDetail;
    }

    public function getAllUserProfileDetail($userId) {
        $userDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS') . " AS user ")
                    ->leftjoin(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo ", 'user_photo.up_user_id', '=', 'user.id')
                    ->selectRaw('user.id AS id , user.u_total_score AS score,  user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_birthdate AS birth_date, user.u_age As age, user.u_gender AS gender , user.u_country AS address, user.u_latitude AS location_latitude, user.u_longitude AS location_longitude, user.u_description AS description , user.u_school AS school , user.u_current_work AS work, GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url , GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo)  AS is_profile_pic')
                    ->where('user.id', '=' ,$userId)
                    ->where('user.deleted','=',1)
                    ->groupBy('user.id')
                    ->get();
 
        foreach ($userDetail as $key => $value) {
            $url = explode(",",$value->profile_pic_url);
            $id = explode(",",$value->pic_id);
            $profile_pic = explode(",",$value->is_profile_pic);
            $allProfilePhotos = [];
            for ($i = 0; $i < count($url); $i++) {
                $profile = [];
                $profile['url'] = $url[$i];
                $profile['pic_id'] = $id[$i];
                $profile['is_profile_pic'] = ($profile_pic[$i] == 1) ? true : false;
                $allProfilePhotos[] = $profile;
            }
            $userDetail[$key]->profile_picture = $allProfilePhotos;
            unset($value->profile_pic_url);
            unset($value->pic_id);
            unset($value->is_profile_pic);
            $userDetail[$key]->birth_date = date('d/m/Y',strtotime($value->birth_date));
            $userDetail[$key]->distance_away = 0;
        }
        $UserData = [];
        $UserData['profile'] = $userDetail;
        return $UserData;
    }

    public function checkUserExistByFacebookId($facebookId) {
        $userData = $this->model->where('deleted', '1')->where('u_fb_identifier', $facebookId)->first();
        return $userData;
    }
    public function checkUserExitById($id) {
        $userData = $this->model->where('deleted', '1')->where('id', $id)->first();
        return $userData;
    }

    public function updateUserOpenfireDetail($id,$openfireId) {
        $userData = $this->model->where('deleted', '1')->where('id', $id)->update(['u_openfire_id'=>$openfireId]);
        return $userData;
    }

    public function getChatUserList($userId,$lang) {
        $userData = $this->model->where('deleted', '1')->where('id', $userId)->get()->toArray();
        $gender = $userData[0]['u_gender'];
        $lat1 = $userData[0]['u_latitude'];
        $lon1 = $userData[0]['u_longitude'];
        $user_id = $userId;
        $admin_flag = 0;
        $userDetail = DB::select( DB::raw("SELECT user.u_fb_identifier , user.id AS id , user.u_openfire_id AS jabber_id, GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url, GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_pic, user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_birthdate AS birth_date, user.u_gender AS gender , user.u_country AS address, user.u_latitude AS location_latitude, user.u_longitude AS location_longitude, user.u_description AS description , user.u_school AS school , user.u_current_work AS work ,fav_user.fu_is_favorite AS is_favorite ,user.u_age As age
                                          FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
                                          left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id

                                          left join " . config::get('databaseconstants.TBL_MT_USER_NOTIFICATION') . " AS user_notification on user_notification.un_sender_id = $userId and  user_notification.un_receiver_id = user.id or user_notification.un_sender_id = user.id and  user_notification.un_receiver_id = $userId

                                          left join " . config::get('databaseconstants.TBL_MT_FAVORITE_USER') . " AS fav_user on fav_user.fu_to_user_id = user.id and fav_user.fu_from_user_id = $userId
                                               where user.deleted = 1 and user_notification.un_sender_id != '' and  user_notification.un_action = 1 and user.id !=". $userData[0]['id'] ."  group by user.id order by user.created_at DESC"));
        foreach ($userDetail as $key => $value) {
            $userId = $user_id;
            if ($value->u_fb_identifier == Config::get('constant.ADMIN_USER_ID')) {
                $userDetail[$key]->is_admin = 1;
            } else {
                $userDetail[$key]->is_admin = 0;
            }

            $value->is_favorite = intval($value->is_favorite);
            $userDetail[$key]->birth_date = date('d/m/Y',strtotime($value->birth_date));
            $lat2 = $value->location_latitude;
            $lon2 = $value->location_longitude;
            $distance = Helpers::getDistance($lat1,$lon1,$lat2,$lon2);
            $userDetail[$key]->distance_away = $distance;
            $url = explode(",",$value->profile_pic_url);
            $id = explode(",",$value->pic_id);
            $profile_pic = explode(",",$value->is_profile_pic);
            $allProfilePhotos = [];
            for ($i = 0; $i < count($url); $i++) {
                $profile = [];
                $profile['url'] = $url[$i];
                $profile['pic_id'] = $id[$i];
                $profile['is_profile_pic'] = ($profile_pic[$i] == 1) ? true : false;
                $allProfilePhotos[] = $profile;
            }
            $userDetail[$key]->profile_picture = $allProfilePhotos;
            unset($value->profile_pic_url);
            unset($value->pic_id);
            unset($value->is_profile_pic);
            $otherUserId = $value->id;

            $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS') . " AS per_question ")
                        ->join(config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question ", 'question.id', '=', 'per_question.upq_question_id')
                        ->selectRaw('per_question.upq_question_id,per_question.upq_option_id,question.q_question_text')
                        ->where('per_question.upq_user_id', '=' ,$userId)
                        ->where('per_question.upq_questioner_id', '=' ,$otherUserId)
                        ->where('per_question.deleted','=',1)
                        ->get();
            $questionId = [];
            $flag = 0;
            $pass_flag = 0;
            if (empty($questionDetail)) {
                $flag = 1;
                $pass_flag = 1;
                $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_UPQ_USER_PERSONALITY_QUESTIONS') . " AS per_question ")
                        ->join(config::get('databaseconstants.TBL_MT_Q_QUESTIONS') . " AS question ", 'question.id', '=', 'per_question.upq_question_id')
                        ->selectRaw('per_question.upq_question_id,per_question.upq_option_id,question.q_question_text')
                        ->where('per_question.upq_user_id', '=' ,$otherUserId)
                        ->where('per_question.upq_questioner_id', '=' ,$userId)
                        ->where('per_question.deleted','=',1)
                        ->get();
                $otherUserId = $userId;
                $userId = $value->id;
            }
            if ($pass_flag == 0) {
                $value->test_passed = 0;
            } else {
                $value->test_passed = 1;
            }
            if ($value->u_fb_identifier == Config::get('constant.ADMIN_USER_ID') && ($value->id != $userId) || ($value->id != $otherUserId)) {
                $value->test_passed = 0;
            }
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
            $allQuestionData = [];
            foreach ($allQuestions as $kk => $val1) {
                if ($lang == 'fr' && $val1->fr_question != '') {
                    $val1->question = $val1->fr_question;
                    $val1->options = $val1->fr_options;
                    unset($val1->fr_options);
                } else if ($lang == 'en'){
                    unset($val1->fr_options);
                } else {
                   unset($val1->fr_options);
                }
                if ($flag == 1) {
                    $val1->user_answer = $val1->qa_option_id;
                    $val1->other_user_answer = $val1->upq_option_id;
                } else {
                    $val1->other_user_answer = $val1->qa_option_id;
                    $val1->user_answer = $val1->upq_option_id;
                }
                unset($val1->qa_option_id);
                unset($val1->upq_option_id);
                $val1->question_no = $kk+1;
                $optionIds = explode(",", $val1->optionIds);
                $options = explode(",", $val1->options);
                unset($val1->optionIds);
                unset($val1->options);

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
                } else if ($val1->fr_question == '') {
                    for ($i = 0; $i < count($options); $i++) {
                        if (strtolower($options[$i]) == 'no') {
                            $no = $options[$i];
                            $noId = $optionIds[$i];
                        } else {
                            $yes = $options[$i];
                            $yesId = $optionIds[$i];
                        }
                    }
                }else if (($lang == 'fr')  && $val1->fr_question != ''){
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
                unset($val1->fr_question);
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
                $allQuestions[$kk]->options = $optionsWithId;
                unset($val1->deleted);
            }
            $userDetail[$key]->compatibility_question = $allQuestions;
        }
        return $userDetail;
    }

    public function deleteUserCompatibility($userId,$otherUserId) {
            $return = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('un_sender_id', '=', $userId)->where('un_receiver_id', '=', $otherUserId)->where('un_action', '=', 1)->delete();

            $return = DB::table(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION'))->where('un_receiver_id', '=', $userId)->where('un_sender_id', '=', $otherUserId)->where('un_action', '=', 1)->delete();

            return true;
    }


    public function getAllUserByMonth($firstDay,$lastDay) {
        $questionDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))
                        ->select(DB::raw('COUNT(id) AS total'))
                        ->whereRaw('deleted IN (1,2)')
                        ->whereBetween('created_at', array($firstDay,$lastDay))
                        ->get();
        return $questionDetail;
    }

     public function getAdminUserId() {
        $user = $this->model->where('deleted', '1')->where('u_fb_identifier',Config::get('constant.ADMIN_USER_ID'))->get();
        if ($user->count() > 0) {
            return $user[0]['id'];
        } else {
           return false;
        }
    }

    public function checkUserExitByJabberId($id) {
        $userData = $this->model->where('deleted', '1')->where('u_openfire_id', $id)->first();
        return $userData;
    }

    public function getChatUserListDetail($userId, $lang = NULL)
    {
        $userData = $this->model->where('deleted', '1')->where('id', $userId)->get()->toArray();
        if(!empty($userData))
        {
            $admin_flag = 0;

        //     $userDetail = DB::table(config::get('databaseconstants.TBL_MT_U_USERS') . " AS user ")

        //             ->leftjoin(config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo ", 'user.id', '=', 'user_photo.up_user_id')

        //             ->leftjoin(config::get('databaseconstants.TBL_MT_FAVORITE_USER') . " AS fav_user ", function ($join) use ($userId) {
        //                 $join->on('user.id', '=', 'fav_user.fu_to_user_id')
        //                      ->where('fav_user.fu_from_user_id', '=', $userId);
        //             })

        //             ->leftjoin(config::get('databaseconstants.TBL_MT_USER_NOTIFICATION') . " AS user_notification ", function ($join) use ($userId) {
        //                 $join->where('user.id', '=', 'user_notification.un_receiver_id')
        //                      ->Where('user_notification.un_sender_id', '=', $userId)
        //                      ->orWhere('user.id', '=', 'user_notification.un_sender_id')
        //                      ->orWhere('user_notification.un_receiver_id', '=', $userId)
        //                      ->where('user_notification.un_action', '=', 1);
        //             })
                    
        //             ->selectRaw('user.u_fb_identifier')
        //             ->where('user_notification.un_sender_id', '!=' ,'')
        //             ->where('user.id', '!=' ,$userData[0]['id'])
        //             ->where('user.deleted','=',1)
        //             ->get();

        // echo "<pre>";
        // print_r($questionDetail);
        // exit;


            $userDetail = DB::select( DB::raw("SELECT user.u_fb_identifier , user.id AS id , user.u_openfire_id AS jabber_id, GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url, GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_pic, user.u_firstname AS first_name , user.u_lastname AS last_name, fav_user.fu_is_favorite AS is_favorite

                    FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
                    left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id

                    left join " . config::get('databaseconstants.TBL_MT_USER_NOTIFICATION') . " AS user_notification on user_notification.un_sender_id = $userId and  user_notification.un_receiver_id = user.id or user_notification.un_sender_id = user.id and  user_notification.un_receiver_id = $userId

                        left join " . config::get('databaseconstants.TBL_MT_FAVORITE_USER') . " AS fav_user on fav_user.fu_to_user_id = user.id and fav_user.fu_from_user_id = $userId

                            where user.deleted = 1 and user_notification.un_sender_id != '' and  user_notification.un_action = 1 and user.id !=". $userData[0]['id'] ." group by user.id order by user.created_at DESC")); 
            foreach ($userDetail as $key => $value) 
            {
                if ($value->u_fb_identifier == Config::get('constant.ADMIN_USER_ID')) {
                    $userDetail[$key]->is_admin = 1;
                } else {
                    $userDetail[$key]->is_admin = 0;
                }

                $value->is_favorite = intval($value->is_favorite);
                $url = explode(",",$value->profile_pic_url);
                $id = explode(",",$value->pic_id);
                $profile_pic = explode(",",$value->is_profile_pic);
                $allProfilePhotos = [];
                for ($i = 0; $i < count($url); $i++) 
                {
                    if($profile_pic[$i] == 1)
                    {
                        $profile = [];
                        $profile['url'] = $url[$i];
                        $profile['pic_id'] = $id[$i];
                        $profile['is_profile_pic'] = ($profile_pic[$i] == 1) ? true : false;
                        $allProfilePhotos[] = $profile;
                    }
                }
                $userDetail[$key]->profile_picture = $allProfilePhotos;
                unset($value->profile_pic_url);
                unset($value->pic_id);
                unset($value->is_profile_pic);
                unset($value->u_fb_identifier);
                $userDetail[$key]->compatibility_question = [];
            }
            return $userDetail;
        }   
    }


    public function getUserUserNotification($id, $other_user_id) {
        $userData = DB::table(config::get('databaseconstants.TBL_MT_U_USERS'))->where('deleted', '1')->where('id', $other_user_id)->first();
        if (isset($userData) && !empty($userData)) {        
            $lat1 = $userData->u_latitude;
            $lon1 = $userData->u_longitude;
        $notificationArray = [];
        $userDetail = DB::select( DB::raw("SELECT user.id AS id,user.id AS id , GROUP_CONCAT(user_photo.up_photo_name)  AS profile_pic_url, GROUP_CONCAT(user_photo.id)  AS pic_id, GROUP_CONCAT(user_photo.up_is_profile_photo) AS is_profile_pic, user.u_firstname AS first_name , user.u_lastname AS last_name , user.u_birthdate AS birth_date, user.u_age As age, user.u_gender AS gender , user.u_country AS address, user.u_latitude AS location_latitude, user.u_longitude AS location_longitude, user.u_description AS description , user.u_school AS school , user.u_current_work AS work
                                            FROM  " . config::get('databaseconstants.TBL_MT_U_USERS') . " AS user
                                            left join " . config::get('databaseconstants.TBL_MT_UP_USER_PHOTOS') . " AS user_photo on user_photo.up_user_id = user.id
                                            where user.deleted = 1 and user.id =". $id ." group by user.id"));
         if (isset($userDetail) && !empty($userDetail)) {
                foreach ($userDetail AS $key => $value) {
                    $userDetail[$key]->birth_date = date('d/m/Y',strtotime($value->birth_date));
                    $lat2 = $value->location_latitude;
                    $lon2 = $value->location_longitude;
                    $distance = Helpers::getDistance($lat1,$lon1,$lat2,$lon2);
                    $userDetail[$key]->distance_away = $distance;
                    $url = explode(",",$value->profile_pic_url);
                    $id = explode(",",$value->pic_id);
                    $profile_pic = explode(",",$value->is_profile_pic);
                    $allProfilePhotos = [];
                    for ($i = 0; $i < count($url); $i++) {
                        $profile = [];
                        $profile['url'] = $url[$i];
                        $profile['pic_id'] = $id[$i];
                        $profile['is_profile_pic'] = ($profile_pic[$i] == 1) ? true : false;
                        $allProfilePhotos[] = $profile;
                    }
                    $userDetail[$key]->profile_picture = $allProfilePhotos;
                    unset($value->profile_pic_url);
                    unset($value->pic_id);
                    unset($value->is_profile_pic);
                    $notificationArray['profile'] = $userDetail[$key];
                }
            }
            return $notificationArray;
        }
    }

    public function updateXMPPUserDetail($id,$status) {
        $userData = $this->model->where('deleted', '1')->where('id', $id)->update(['u_xmpp_user'=>$status]);
        return $userData;
    }

    public function updateUserFirstTimeUpdateFlagById($id) {
        $userData = $this->model->where('deleted', '1')->where('id', $id)->update(['u_update_first_time' => 1]);
        return $userData;
    }

}
