<?php

namespace App\Http\Controllers\Webservice;

use File;
use Image;
use Input;
use Helpers;
use Config;
use Mail;
use Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Users;
use App\UserLikeDislike;
use App\UserPhotos;
use App\UserPersonalityQuestions;
use App\UserPersonalityMatch;
use App\UserNotification;
use App\UserProfileReport;
use App\Configurations;
use App\Services\Users\Contracts\UsersRepository;
use App\Services\QuestionData\Contracts\QuestionDataRepository;
use App\Services\EmailTemplate\Contracts\EmailTemplatesRepository;
use App\FavoriteUser;
use App\DeviceToken;
use App\UserDeleteCompatability;
use App\AttemptedQuestionsData;
use JWTAuth;
use JWTAuthException;
use App\ChatMessages;
use App\MessageCount;
use Log;
use App\UserFeedback;
use Storage;
use \stdClass;

class WebserviceController extends Controller {

    public function __construct(UsersRepository $UsersRepository, QuestionDataRepository $QuestionDataRepository,EmailTemplatesRepository $TemplatesRepository) {
        $this->objUsers = new Users();
        $this->UsersRepository = $UsersRepository;
        $this->QuestionDataRepository = $QuestionDataRepository;
        $this->EmailTemplatesRepository = $TemplatesRepository;
        $this->uploadUserProfileOriginalPath = Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->uploadUserProfileThumbPath = Config::get('constant.USER_PROFILE_THUMB_IMAGE_UPLOAD_PATH');
        $this->userProfileThumbHeight = Config::get('constant.USER_PROFILE_THUMB_IMAGE_HEIGHT');
        $this->userProfileThumbWidth = Config::get('constant.USER_PROFILE_THUMB_IMAGE_WIDTH');
        $this->userCerfificatePath = Config::get('constant.CERTIFICATE_PATH');
    }

    public function index(Request $request) {
        $methodName = $request->input('methodName');

        if(strpos(Route::current()->uri(),'api/') !== false) {
            if($methodName == 'login' || $methodName == 'logout'){
                $response = [];
                $response['status'] = 0;
                $response['message'] = trans('appmessages.default_error_msg');
                echo json_encode($response);
                exit;
            }
        }
        $body = $request->all();
        $this->$methodName($body);
    }

    /*
     * Save device token to send push notification
    */
    public function gcm_token($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $saveData['udt_user_id'] = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '0';
            $saveData['udt_device_token'] = (isset($body['token']) && $body['token'] != '') ? $body['token'] : '';
            $saveData['udt_device_type'] = (isset($body['device_type']) && $body['device_type'] != '') ? $body['device_type'] : 2;
            $saveData['udt_device_id'] = (isset($body['device_id']) && $body['device_id'] != '') ? $body['device_id'] : '';
            $saveData['udt_appversion'] = (isset($body['appVersion']) && $body['appVersion'] != '') ? $body['appVersion'] : '';
            //Save device token in database
            $objDeviceToken = new DeviceToken();
            //$this->UsersRepository->saveDeviceToken($saveData);
            $objDeviceToken->saveDeviceToken($saveData);
            $response['status'] = 1;
            $response['message'] = trans('appmessages.default_success_msg');
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     * Logout with JWT auth token for new API version
    */
    public function _logout(Request $request) {
        $this->user_logout($request->all());
    }

    /*
     * Delete all existing device tokens for particular users when logout
    */
    public function user_logout($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            if(strpos(Route::current()->uri(),'api/') !== false) {
                if( isset($body['token']) && !empty($body['token']) ) {
                    try {
                        JWTAuth::invalidate($body['token']);
                    } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
                        $this->UsersRepository->deleteDeviceToken($body['user_id'],$body['device_id']);
                        $response['status'] = 1;
                        $response['message'] = trans('appmessages.default_success_msg');
                        echo json_encode($response);
                        exit;
                    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                        $this->UsersRepository->deleteDeviceToken($body['user_id'],$body['device_id']);
                        $response['status'] = 1;
                        $response['message'] = trans('appmessages.default_success_msg');
                        echo json_encode($response);
                        exit;
                    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                        $this->UsersRepository->deleteDeviceToken($body['user_id'],$body['device_id']);
                        $response['status'] = 1;
                        $response['message'] = trans('appmessages.default_success_msg');
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response['message'] = 'Invalid input parameter.';
                    echo json_encode($response);
                    exit;
                }
            }
            $this->UsersRepository->deleteDeviceToken($body['user_id'],$body['device_id']);
            $response['status'] = 1;
            $response['message'] = trans('appmessages.default_success_msg');
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     * Get all data with JWT auth token for new API version
    */
    public function _login(Request $request) {
        $this->login($request->all());
    }

    /*
     * Get all data after login in MeeTwo
    */
    public function login($body) 
    {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        if(isset($body['appVersion']) && $body['appVersion'] != '')
        {
            if(isset($body['facebook_id']) && $body['facebook_id'] != '') {
                $saveData['u_fb_identifier'] = (isset($body['facebook_id']) && $body['facebook_id'] != '') ? $body['facebook_id'] : '';
                $saveData['u_firstname'] = (isset($body['first_name']) && $body['first_name'] != '') ? $body['first_name'] : '';
                $saveData['u_lastname'] = (isset($body['last_name']) && $body['last_name'] != '') ? $body['last_name'] : '';
                $saveData['u_email'] = (isset($body['email']) && $body['email'] != '') ? $body['email'] : '';
                $saveData['u_gender'] = (isset($body['gender']) && $body['gender'] != '') ? $body['gender'] : '';
                $saveData['u_description'] = (isset($body['description']) && $body['description'] != '') ? $body['description'] : '';
                $saveData['u_school'] = (isset($body['school']) && $body['school'] != '') ? $body['school'] : '';
                $saveData['u_current_work'] = (isset($body['work']) && $body['work'] != '') ? $body['work'] : '';
                $saveData['u_birthdate'] = (isset($body['birth_date']) && $body['birth_date'] != '') ? $body['birth_date'] : '';
                //$saveData['u_appversion'] = (isset($body['appVersion']) && $body['appVersion'] != '') ? $body['appVersion'] : '';
                $saveData['checkActionType'] = 1;
                //$saveData['u_xmpp_user'] = 2;

                if (isset($body['gender']) && $body['gender'] != '') {
                    if ($body['gender'] == 1) {
                        $saveData['u_looking_for'] = 2;
                    } else if ($body['gender'] == 2) {
                        $saveData['u_looking_for'] = 1;
                    } else {
                        $saveData['u_looking_for'] = $body['gender'];
                    }
                }
                $age = 0;
                if (isset($body['birth_date']) && $body['birth_date'] != '') {
                    $dob = $body['birth_date'];
                    $dobDate = str_replace('/', '-', $dob);
                    $userBirthdate = date("Y-m-d", strtotime($dobDate));
                    $age = Helpers::calculateAgeInYears($userBirthdate);
                    $saveData['u_age'] = (isset($age) && $age != '') ? $age : '';
                    $saveData['u_looking_age_min'] = Config::get('constant.MIN_AGE');
                    $saveData['u_looking_age_max'] = Config::get('constant.MAX_AGE');
                    if (isset($age) && $age != '') {
                        $min = $age - 5;
                        $max = $age + 5;

                        if ($min > Config::get('constant.MIN_AGE')) {
                            $saveData['u_looking_age_min'] = $min;
                        }

                        if ($max < Config::get('constant.MAX_AGE')) {
                            $saveData['u_looking_age_max'] = $max;
                        }
                    }

                }
                if ($age < Config::get('constant.MIN_AGE') && $body['birth_date'] != '') {
                    $response['status'] = 0;
                    $response['message'] = trans('appmessages.min_age_required_msg');;
                } else {
                    $checkUserExit = $this->UsersRepository->checkUserExistByFacebookId($body['facebook_id']);
                    $saveUserPhotosData = [];
                    if (Input::file()) {
                        $file = Input::file('profile_pic_url');
                        if (!empty($file)) {
                            if (empty($checkUserExit)) {
                                $time = time();
                                $fileName = 'user_'.$time.'.'.$file->getClientOriginalExtension();
                                $path1 = public_path($this->uploadUserProfileOriginalPath . $fileName);
                                $path2 = public_path($this->uploadUserProfileThumbPath . $fileName);
                                Image::make($file->getRealPath())->save($path1);
                                Image::make($file->getRealPath())->resize($this->userProfileThumbWidth, $this->userProfileThumbHeight)->save($path2);
                                //Uploading on AWS
                                $originalImage = Helpers::addFileToStorage($fileName, $this->uploadUserProfileOriginalPath, $path1, "s3");
                                $thumbImage = Helpers::addFileToStorage($fileName, $this->uploadUserProfileThumbPath, $path2, "s3");

                                //Deleting Local Files
                                \File::delete($this->uploadUserProfileOriginalPath . $fileName);
                                \File::delete($this->uploadUserProfileThumbPath . $fileName);
                                
                                $saveUserPhotosData['up_photo_name'] = (isset($fileName) && $fileName != '') ? $fileName : '';
                            }
                        }
                    }
                    if ($checkUserExit === null) {
                        $user = $this->UsersRepository->saveUserDetail($saveData,$saveUserPhotosData);
                        if(strpos(Route::current()->uri(),'/auth/token') !== false ) {
                            $data = $this->UsersRepository->checkUserExitById($user);
                            $token = $this->get_token($data);
                            if($token['status'] == 0) {
                                $response['status'] = $token['status'];
                                $response['message'] = $token['message'];
                                echo json_encode($response);
                                exit;
                            }
                        }
                    } else {
                        if(strpos(Route::current()->uri(),'/auth/token') !== false ) {
                            $token = $this->get_token($checkUserExit);
                            if($token['status'] == 0) {
                                $response['status'] = $token['status'];
                                $response['message'] = $token['message'];
                                echo json_encode($response);
                                exit;
                            }
                        }
                        $user = $this->UsersRepository->saveUserDetail($saveData,$saveUserPhotosData);
                    }
                    $userDetail = $this->UsersRepository->getUserDetailByFacebookId($body['facebook_id']);

                    if (!empty($userDetail)) 
                    {
//                        if ($userDetail[0]->xmpp_user > 0) 
//                        {
//                            if ($userDetail[0]->xmpp_user != 1) 
//                            {
//                                $openfireId = $userDetail[0]->facebook_id.'_'.$userDetail[0]->user_id.'_'.mt_rand(1000, 999999);
//                                $UserOpenfireId = $this->UsersRepository->updateUserOpenfireDetail($userDetail[0]->user_id,$openfireId);
//                                $password = 123456;
//                                $name = $userDetail[0]->first_name;
//                                $email = $userDetail[0]->email;
//                                $userDetail[0]->jabber_id = $openfireId;
//                                $createUser = Helpers::createUser($openfireId, $password, $name, $email);
//                               
//                                if(!$createUser)
//                                {
//                                    $createUser = Helpers::createUser($openfireId, $password, $name, $email);
//                                    if(!$createUser)
//                                    {
//                                        $data = $this->UsersRepository->updateXMPPUserDetail($userDetail[0]->user_id, 2);
//                                        
//                                        Log::error($userDetail[0]->first_name ." - ". $userDetail[0]->user_id . " trying to login in openfireserver but fail because of XMPP server down #");
//                                        
//                                        $response['status'] = 0;
//                                        $response['message'] = trans('appmessages.login_fail');
//                                        echo json_encode($response);
//                                        exit;
//                                    } else {
//                                        Log::info($userDetail[0]->first_name ." - ". $userDetail[0]->user_id . " login in openfireserver sucessfully #");
//                                        $data = $this->UsersRepository->updateXMPPUserDetail($userDetail[0]->user_id, 1);
//                                    }
//                                } else {
//                                    Log::info($userDetail[0]->first_name ." - ". $userDetail[0]->user_id . " login in openfire server successfully #");
//                                    $data = $this->UsersRepository->updateXMPPUserDetail($userDetail[0]->user_id, 1);
//                                }
//                            } 
//                        }
                        
                        $userId = $userDetail[0]->user_id;                        
                        $userChatListData = $this->UsersRepository->getChatUserListDetail($userId);
                        
                        if ($userDetail[0]->u_applozic_id == 0 && count($userChatListData) > 0)
                        {
                            $othersParams = [];
                            $openfireId = $userDetail[0]->facebook_id.'_'.$userDetail[0]->user_id.'_'.mt_rand(1000, 999999);
                            $UserOpenfireId = $this->UsersRepository->updateUserOpenfireDetail($userDetail[0]->user_id,$openfireId);
                            $name = $userDetail[0]->first_name;
                            $email = $userDetail[0]->email;
                            $userDetail[0]->jabber_id = $openfireId;   
                            $password = Config::get('constant.APPLOZIC_USER_PASWORD');
                            
                            $othersParams['first_name'] = $userDetail[0]->first_name;
                            $othersParams['last_name'] = $userDetail[0]->last_name;
                            $othersParams['email'] = $userDetail[0]->email;
                            $othersParams['user_id'] = $userDetail[0]->user_id;
                            
                            $createUser = Helpers::createUser($openfireId, $password, $name, $email, $othersParams);
                            if($createUser && $createUser['status'] == 1 && !empty($createUser['data']))
                            {
                               Log::info($userDetail[0]->first_name." - ".$userDetail[0]->user_id." login in openfireserver sucessfully #");    
                                $userDetail[0]->u_applozic_id = $createUser['data']['userId'];
                                $saveUserDetail = [];
                                $saveUserDetail['u_applozic_id'] = $createUser['data']['userId'];
                                $saveUserDetail['u_applozic_device_key'] = $createUser['data']['deviceKey'];
                                $saveUserDetail['u_applozic_user_key'] = $createUser['data']['userKey'];
                                $saveUserDetail['u_applozic_user_encryption_key'] = $createUser['data']['userEncryptionKey'];
                                $savedUserDetail = Users::where('id', $userDetail[0]->user_id)->update($saveUserDetail);
                            }
                            else
                            {
                                $saveUserDetail['u_applozic_id'] = 0;
                                $savedUserDetail = Users::where('id', $userDetail[0]->user_id)->update($saveUserDetail);
                                Log::error($userDetail[0]->first_name ." - ". $userDetail[0]->user_id . " trying to login in openfireserver but fail because of XMPP server down #");
                                $response['status'] = 0;
                                $response['message'] = trans('appmessages.login_fail');
                                echo json_encode($response);
                                exit;
                            }
                        }
                        foreach ($userDetail as $key => $photos) 
                        {
                            $url = '';
                            $image = explode(",", $photos->profile_pic_url);
                            $IsProfile = explode(",", $photos->is_profile_photo);
                            for ($i = 0; $i < count($image); $i++) {
                                if ($IsProfile[$i] == 1) {
                                    $photo = $image[$i];
//                                  if ($photo != '' && file_exists($this->uploadUserProfileOriginalPath . $photo))
                                    if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1) 
                                    {
//                                      $url = asset($this->uploadUserProfileOriginalPath . $photo);
                                        $url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo;
                                    } else {
                                        $url = asset("/backend/images/logo.png");
                                    }
                                }
                            }
                            if (empty($url)) {
                                $photo = $image[0];
//                              if ($photo != '' && file_exists($this->uploadUserProfileOriginalPath . $photo)) 
                                if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1) 
                                {
                                    $url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo;
                                } else {
                                    $url = asset("/backend/images/logo.png");
                                }
                            }
                            $photos->profile_pic_url = $url;
                            $photos->birth_date = date('d/m/Y',strtotime($photos->birth_date));
                            $photos->password = '123456';
                            unset($photos->is_profile_photo);
                        }
                    }
                    
                    if (isset($userDetail) && !empty($userDetail)) {
                        $userScoreUpdate = Helpers::updateUserScore($userDetail[0]->user_id);
                        $scoreUpdate = Helpers::updateUserTotalScoreById($userDetail[0]->user_id);
                        $totalQuestion = $this->QuestionDataRepository->getTotlaNumberQuestion();
                        $str = $totalQuestion/(config::get('constant.PAGINATION_LIMIT'));
                        $data = [];
                        for($i = 1; $i <= intval($str); $i++)
                        {
                            $data[] = $i;
                        }
                        shuffle($data);
                        $questionStr = implode(",",$data);
                        $userDetail[0]->user_id = (string)$userDetail[0]->user_id;
                        $userDetail[0]->question_page = $questionStr;
                        $userDetail[0]->applozic_user = ($userDetail && isset($userDetail[0]->u_applozic_id) && $userDetail[0]->u_applozic_id > 0) ? 1 : 0;
                        $userDetail[0]->gender = (string)$userDetail[0]->gender;
                        $userDetail[0]->is_question_attempted = (string)$userDetail[0]->is_question_attempted;
                        $userDetail[0]->is_active = (string)$userDetail[0]->is_active;
                        if(strpos(Route::current()->uri(),'/auth/token') !== false ) {
                            $userDetail[0]->auth_token = $token['message'];
                        }
                        $response['status'] = 1;
                        $response['message'] = trans('appmessages.default_success_msg');
                        $response['data'] = $userDetail[0];
                    } else {
                        $response['message'] = trans('appmessages.default_error_msg');
                    }
                }
            } else{
                $response['message'] = trans('appmessages.default_error_msg');
            }
        }else{
            $response['status'] = 4;
            $response['message'] = trans('appmessages.update_application_msg');
        }
        echo json_encode($response);
    }

    /*
     * Get 10 personality question when user first time register with MeeTwo
    */
    public function login_get_question($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $slot = (isset($body['page_no']) && $body['page_no'] != '') ? $body['page_no'] : 0;
                $lang = (isset($body['language']) && $body['language'] != '') ? $body['language'] : 0;
                $questionType = (isset($body['questionType']) && $body['questionType'] != '') ? $body['questionType'] : 0;

                if ($slot > 0) {
                    $slot = $slot * config::get('constant.PAGE_LIMIT');
                    $numberOfPage = $slot+1;
                } else {
                    $objAttemptedQuestion = new AttemptedQuestionsData();
                    $result = $objAttemptedQuestion->deleteAllAttemptedQuestionsByUserIds($userId);
                    $numberOfPage = 1;
                }

                $questions = $this->QuestionDataRepository->getALlNotAttemptedPersonalityQuestion($slot,$lang, $userId,$questionType);
                shuffle($questions);

                foreach ($questions as $key => $value) {
                    $value->question_id = (string)$value->question_id;
                    $value->question_number = $numberOfPage+$key;
                }
                $allQuestions['questions'] = $questions;
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $allQuestions;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    //  Check that user have applozic account or not
    public function checkUserInApplozic($body)
    {
        try
        {
            $outputArray = [];
            $rules = [
                'methodName' => 'required',
                'user_id' => 'required',
            ];

            $validator = Validator::make($body, $rules);
            if ($validator->fails())
            {
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }            
            $userData = $this->objUsers->find($body['user_id']);
            
            if($userData && !empty($userData))
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = '';
                $outputArray['data'] = array();
                $statusCode = 200;                

                if(isset($userData->u_applozic_id) && $userData->u_applozic_id > 0)
                {
                    $outputArray['message'] = trans('appmessages.user_exist_in_applozic_account');
                    $outputArray['data']['applozic_user'] = 1;
                }
                else
                {
                    $outputArray['message'] = trans('appmessages.user_does_not_exist_in_applozic_account');
                    $outputArray['data']['applozic_user'] = 0;
                }
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('appmessages.user_not_found');
                $statusCode = 400;
                $outputArray['data'] = array();
            }
           
//            return response()->json($outputArray, $statusCode);
            echo json_encode($outputArray);
        }
        catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
//          return response()->json($outputArray, $statusCode);
            echo json_encode($outputArray);
        }
    }
    
    /*
     * Save 10 personality question when user first time register with MeeTwo
    */
    public function login_save_question_answer($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $objAttemptedQuestion = new AttemptedQuestionsData();
                $result = $objAttemptedQuestion->deleteAllAttemptedQuestionsByUserIds($userId);
                $saveQuestion = [];
                $saveQuestion['qa_user_id'] = $userId;
                $saveQuestion['qa_question_id'] = (isset($body['question_id']) && $body['question_id'] != '') ? $body['question_id'] : '';
                $saveQuestion['qa_option_id'] = (isset($body['answer']) && $body['answer'] != '') ? $body['answer'] : '';

                $return = $this->QuestionDataRepository->saveAttemptedPersonalityQuestion($saveQuestion);
                $questions = $this->QuestionDataRepository->getNotAttemptedPersonalityQuestion($userId);
                if (empty($questions)) {
                    $return = $this->QuestionDataRepository->deleteSkippedPersonalityQuestion($userId);
                    $questions = $this->QuestionDataRepository->getNotAttemptedPersonalityQuestion($userId);
                }
                if ((isset($questions) && !empty($questions))) {
                    shuffle($questions);
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.default_success_msg');
                    $response['data'] = $questions[0];
                } else {
                    $response['message'] = trans('appmessages.nomorequestions');
                }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     * Get 10 personality question when user can attempted with register
     */
    public function get_all_questions($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $lang = '';
                $questions = $this->QuestionDataRepository->getAllAttemptedPersonalityQuestion($userId,$lang);
                $allQuestion = [];
                $allQuestion['questions'] = $questions;
                if (isset($allQuestion) && !empty($allQuestion)) {
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.default_success_msg');
                    $response['data'] = $allQuestion;
                } else {
                    $response['message'] = trans('appmessages.default_error_msg');
                }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Get match profile detail by user_id
    */
    public function get_match_profile($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $slot = (isset($body['page_no']) && $body['page_no'] != '') ? $body['page_no'] : 0;
                $lang = (isset($body['language']) && $body['language'] != '') ? $body['language'] : 'en';
                $profile = $this->UsersRepository->getMatchProfile($userId,$slot,$lang);
                $allUserProfiles = [];
                if (isset($profile) && !empty($profile)) {
                    foreach ($profile as $key => $photos) {
                        $photo = $photos->profile_picture;
                        $questions = $photos->questions;
                        $photos->id = (string)$photos->id;
                        $photos->gender = (string)$photos->gender;
                        $photos->age = (string)$photos->age;
                        foreach($questions as $que => $question)
                        {
                            $question->question_id = (string)$question->question_id;
                        }
                        foreach ($photo as $key1 => $image) {
                            $photoName = $image['url'];
//                          if ($photoName != '' && file_exists($this->uploadUserProfileOriginalPath . $photoName)) 
                            if ($photoName != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName) == 1) 
                            {
//                              $photos->profile_picture[$key1]['url'] = asset($this->uploadUserProfileOriginalPath . $photoName);
                                $photos->profile_picture[$key1]['url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName;
                            } else {
                                $photos->profile_picture[$key1]['url'] = asset("/backend/images/logo.png");
                            }
                        }
                    }
                }
                //shuffle($profile);
                $allUserProfiles['profiles'] = $profile;
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $allUserProfiles;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Get other profile detail by user_id
    */
    public function get_other_profile($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 && isset($body['other_user_id']) && $body['other_user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $userDetail = $this->UsersRepository->getOtherProfileDetail($userId,$body['other_user_id']);
                if (isset($userDetail) && !empty($userDetail)) {
                    $userProfileDetail = $userDetail['profile'];
                    foreach ($userProfileDetail as $key => $photos) {
                        $photo = $photos->profile_picture;
                        if (!empty($photo)) {
                            foreach ($photo as $key1 => $image) {
                                if ($image['pic_id'] != '') {
                                    $photoName = $image['url'];
//                                  if ($photoName != '' && file_exists($this->uploadUserProfileOriginalPath . $photoName))
                                    if ($photoName != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1)
                                    {
//                                        $photos->profile_picture[$key1]['url'] = asset($this->uploadUserProfileOriginalPath . $photoName);
                                        $photos->profile_picture[$key1]['url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName;
                                    } else {
                                        $photos->profile_picture[$key1]['url'] = asset("/backend/images/logo.png");
                                    }
                                } else {
                                    $photos->profile_picture = [];
                                }
                            }
                        }
                    }
                    $userDetail['profile'] = (!empty($userProfileDetail) && $userProfileDetail != '') ? $userProfileDetail[0] : [];
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.default_success_msg');
                    $response['data'] = $userDetail;
                } else {
                    $response['message'] = trans('appmessages.default_error_msg');
                }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Save user like or dislike profile data
    */
    public function user_like_dislike($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 && isset($body['other_user_id']) && $body['other_user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveUserLikeDislikeData = [];
                $saveUserLikeDislikeData['uld_viewer_id'] = $userId;
                $saveUserLikeDislikeData['uld_viewed_id'] = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                $saveUserLikeDislikeData['uld_is_liked'] = (isset($body['like']) && $body['like'] != '') ? $body['like'] : '';
                $objUserLikeDislike = new UserLikeDislike();
                $userLikeDislikeDetail = $objUserLikeDislike->saveUserLikeDislikeDetail($saveUserLikeDislikeData);
                $userLikeDislike = $objUserLikeDislike->getUserLikeDislikeCountByUserId($body['user_id']);
                $disLikeCount = -1;
                $ifFirstLike = false;
                if (count($userLikeDislike) > 0) {
                    foreach ($userLikeDislike as $key => $value) {
                        if ($value->uld_is_liked == 1 && $value->total == 1) {
                            $ifFirstLike = true;
                        }

                        if ($value->uld_is_liked == 0) {
                            $disLikeCount = intval($value->total);
                        }
                    }
                }
                $otherUserId = [];
                $otherUserId['other_user_id'] = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                if ($ifFirstLike) {
                    $otherUserId['dislike_count'] =  intval($disLikeCount);
                } else {
                    $otherUserId['dislike_count'] =  -1;
                }
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $otherUserId;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Save user location data
    */
    public function user_location($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveUserLocationData = [];
                $saveUserLocationData['id'] = $userId;
                $saveUserLocationData['u_latitude'] = (isset($body['latitude']) && $body['latitude'] != '') ? $body['latitude'] : '';
                $saveUserLocationData['u_longitude'] = (isset($body['longitude']) && $body['longitude'] != '') ? $body['longitude'] : '';
                $location_lanlat = $body['latitude'] .",".$body['longitude'];
                $geocode = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $location_lanlat . '&sensor=false&libraries=places');

                $output  = json_decode($geocode);

                $country = '';
                $address = '';
                if (!empty($output->results)) {
                    for ($j = 0; $j < count($output->results[0]->address_components); $j++) {
                        if ($output->results[0]->address_components[$j]->types[0] == 'country') {
                            $country = $output->results[0]->address_components[$j]->long_name;
                            $address = $output->results[0]->formatted_address;
                        }
                    }
                }
                $saveUserLocationData['u_country'] = $country;
                $saveUserLocationData['u_location'] = $address;
                $userLocationDetail = $this->UsersRepository->saveUserLocationDetail($saveUserLocationData);
                if (isset($userLocationDetail) && !empty($userLocationDetail)) {
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.default_success_msg');
                    $response['data'] = $userLocationDetail[0];
                } else {
                    $response['message'] = trans('appmessages.default_error_msg');
                }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Save edit profile data for user
    */
    public function profile_edit_data($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveUserProfileData = [];
                $saveUserProfileData['id'] = $userId;
                $saveUserProfileData['field_id'] = (isset($body['field_id']) && $body['field_id'] != '') ? $body['field_id'] : '';
                $saveUserProfileData['text'] = (isset($body['text']) && $body['text'] != '') ? $body['text'] : '';
                $userProfileDetail = $this->UsersRepository->saveUserProfileDetail($saveUserProfileData);
                if (!empty($userProfileDetail)) {
                    $userProfileDetail[0]->field_id = (isset($body['field_id']) && $body['field_id'] != '') ? $body['field_id'] : '';
                    if ($body['field_id'] == 1) {
                        $userProfileDetail[0]->text = $userProfileDetail[0]->u_description;
                    }
                    if ($body['field_id'] == 2) {
                        $userProfileDetail[0]->text = $userProfileDetail[0]->u_school;
                    }
                    if ($body['field_id'] == 3) {
                        $userProfileDetail[0]->text = $userProfileDetail[0]->u_current_work;
                    }
                    unset($userProfileDetail[0]->u_school);
                    unset($userProfileDetail[0]->u_current_work);
                    unset($userProfileDetail[0]->u_description);
                    $userScoreUpdate = Helpers::updateUserScore($userId);
                    $scoreUpdate = Helpers::updateUserTotalScoreById($userId);
                }
                if (isset($userProfileDetail) && !empty($userProfileDetail)) {
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.default_success_msg');
                    $response['data'] = $userProfileDetail[0];
                } else {
                    $response['message'] = trans('appmessages.default_error_msg');
                }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Save profile image for user
    */
    public function profile_edit_image($body) 
    {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) 
        {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) 
            {
                $saveProfileImageData = [];
                $saveProfileImageData['id'] = (isset($body['image_id']) && $body['image_id'] != '') ? $body['image_id'] : '';
                $saveProfileImageData['up_user_id'] = $userId;
                $imageRemove = (isset($body['is_removed']) && $body['is_removed'] != '') ? $body['is_removed'] : '';
                $objUserPhotos = new UserPhotos();
                if ($imageRemove) 
                {
                    $tempFileName = $objUserPhotos->getExistingUserPhotosDetail($userId, $body['image_id']);
                    if (isset($tempFileName) && !empty($tempFileName)) 
                    {
                        $file1 = $this->uploadUserProfileOriginalPath . $tempFileName[0]->up_photo_name;
                        $file2 = $this->uploadUserProfileThumbPath . $tempFileName[0]->up_photo_name;
                        File::delete($file1, $file2);
                        $userImageDelete = Helpers::deleteUserImage($userId, $tempFileName[0]->up_photo_name);
                        Helpers::deleteFileToStorage($tempFileName[0]->up_photo_name, $this->uploadUserProfileOriginalPath, "s3");
                        Helpers::deleteFileToStorage($tempFileName[0]->up_photo_name, $this->uploadUserProfileThumbPath, "s3");
                    }
                    $userPhotoDetail = $objUserPhotos->deleteUserPhotosDetail($saveProfileImageData);
                    $userScoreUpdate = Helpers::updateUserScore($userId);
                    $scoreUpdate = Helpers::updateUserTotalScoreById($userId);
                    $data = [];
                    $user = $this->UsersRepository->checkUserExitById($userId);
          
                    if (count($user) > 0) 
                    {
                        if ($user['u_total_score'] == Config::get('constant.USER_PROFILE_SCORE')) {
                            $data['is_profile_completed'] = 1;
                        } else {
                            $data['is_profile_completed'] = 0;
                        }
                        if ($user['u_update_first_time'] == 0) {
                            $userData = $this->UsersRepository->updateUserFirstTimeUpdateFlagById($userId);  
                            $data['first_time_update'] = 1;
                        } else {
                            $data['first_time_update'] = 0;
                        }
                    }
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.default_success_msg');
                    $response['data'] = (object)$data;
                } else {
                    $userPhotoDetail = $objUserPhotos->getAllPhotosDetail($userId);
                    if (count($userPhotoDetail) < Config::get('constant.USER_PROFILE_COUNT')) {
                        if (Input::file()) 
                        {
                            $file = Input::file('image');
                            if (!empty($file)) {
                                $time = time();
                                $fileName = 'user_'.$time.'.'.$file->getClientOriginalExtension();
                                $path1 = public_path($this->uploadUserProfileOriginalPath . $fileName);
                                $path2 = public_path($this->uploadUserProfileThumbPath . $fileName);
                                Image::make($file->getRealPath())->save($path1);
                                Image::make($file->getRealPath())->resize($this->userProfileThumbWidth, $this->userProfileThumbHeight)->save($path2);
                                //Uploading on AWS
                                $originalImage = Helpers::addFileToStorage($fileName, $this->uploadUserProfileOriginalPath, $path1, "s3");
                                $thumbImage = Helpers::addFileToStorage($fileName, $this->uploadUserProfileThumbPath, $path2, "s3");

                                //Deleting Local Files
                                \File::delete($this->uploadUserProfileOriginalPath.$fileName);
                                \File::delete($this->uploadUserProfileThumbPath.$fileName);
                                
                                $objUserPhotos = new UserPhotos();
                                if ($body['image_id'] > 0) 
                                {
                                    $tempFileName = $objUserPhotos->getExistingUserPhotosDetail($body['user_id'], $body['image_id']);
                                    if (isset($tempFileName) && !empty($tempFileName)) 
                                    {
                                        $file1 = $this->uploadUserProfileOriginalPath . $tempFileName[0]->up_photo_name;
                                        $file2 = $this->uploadUserProfileThumbPath . $tempFileName[0]->up_photo_name;
                                        File::delete($file1, $file2);
//                                      echo  $tempFileName[0]->up_photo_name;
                                        $userImageDelete = Helpers::deleteUserImage($userId, $tempFileName[0]->up_photo_name);
                                        Helpers::deleteFileToStorage($tempFileName[0]->up_photo_name, $this->uploadUserProfileOriginalPath, "s3");
                                        Helpers::deleteFileToStorage($tempFileName[0]->up_photo_name, $this->uploadUserProfileThumbPath, "s3");
                                    }
                                }
                                $saveProfileImageData['up_photo_name'] = $fileName;
                                $userPhotoDetail = $objUserPhotos->saveUserPhotosDetail($saveProfileImageData);
                                $userScoreUpdate = Helpers::updateUserScore($userId);
                                $scoreUpdate = Helpers::updateUserTotalScoreById($userId);
                            }                            
                            foreach ($userPhotoDetail as $key => $photos) 
                            {
                                $photo = $photos->image_url;
//                              if($photo != '' && file_exists($this->uploadUserProfileOriginalPath.$photo))
                                if($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1)
                                {
//                                  $photos->image_url = asset($this->uploadUserProfileOriginalPath.$photo);
                                    $photos->image_url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo;
                                } 
                                else
                                {
                                    $photos->image_url = asset("/backend/images/logo.png");
                                }
                            }
                            $user = $this->UsersRepository->checkUserExitById($userId);
                            
                            if (count($user) > 0) {
                                if ($user['u_total_score'] == Config::get('constant.USER_PROFILE_SCORE')) {
                                    $userPhotoDetail[0]->is_profile_completed = 1;
                                } else {
                                    $userPhotoDetail[0]->is_profile_completed = 0;
                                } 

                                if ($user['u_update_first_time'] == 0) {
                                    $userData = $this->UsersRepository->updateUserFirstTimeUpdateFlagById($userId);  
                                    $userPhotoDetail[0]->first_time_update = 1;
                                } else {
                                    $userPhotoDetail[0]->first_time_update = 0;
                                }
                            }

                            $userPhotoDetail = (isset($userPhotoDetail) && $userPhotoDetail != '') ? $userPhotoDetail[0] : '';
                            $response['status'] = 1;
                            $response['message'] = trans('appmessages.default_success_msg');
                            $response['data'] = $userPhotoDetail;
                        } 
                    } else {
                        $response['status'] = 0;
                        $response['message'] = trans('labels.moreimage');
                    }     
                   
                }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Save four questions
    */
    public function save_four_question($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveQuestionData = [];
                $saveQuestionData['upq_user_id'] = $userId;
                $saveQuestionData['upq_questioner_id'] = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                $saveQuestionData['upq_question_id'] = (isset($body['question_ids']) && $body['question_ids'] != '') ? $body['question_ids'] : '';
                $saveQuestionData['upq_option_id'] = (isset($body['answer_ids']) && $body['answer_ids'] != '') ? $body['answer_ids'] : '';
                $objQuestionObj = new UserPersonalityQuestions();
                $userDetail = $objQuestionObj->saveUserPersonalityQuestionsDetail($saveQuestionData);
                $data = []; 
                if (isset($userDetail)) {
                    $otheruserId  = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                    if (isset($otheruserId)) {
                        $userQuestionDetail = $this->QuestionDataRepository->getAllAttemptedQuestionByUserId($otheruserId);
                        $questionId = explode(",",$body['question_ids']);
                        $optionId = explode(",",$body['answer_ids']);
                        $count = 0;
                        for ($i = 0; $i < count($questionId); $i++) {
                            foreach ($userQuestionDetail as $key => $result) {
                                if (($questionId[$i] == $result->qa_question_id) && ($optionId[$i] == $result->qa_option_id)) {
                                    $count++;
                                }
                            }
                        }
                    }
                    $savePersonality = [];
                    $savePersonality['pm_answerer_id'] = $userId;
                    $savePersonality['pm_questioner_id'] = $otheruserId;
                    if ($count >= 3) {
                        $data['result'] = 1;
                        $savePersonality['pm_is_match'] = 1;

                        $saveNotification = [];
                        $saveNotification['un_sender_id'] = $userId;
                        $saveNotification['un_receiver_id'] = $otheruserId;
                        $saveNotification['un_notification_text'] = trans('labels.message');
                        $saveNotification['un_is_read'] = 0;
                        $saveNotification['un_action'] = 0;
                        $objUserNotification = new UserNotification();
                        $alertData = [];
                        $title = '';
                        $userData = $objUserNotification->saveUserNotification($saveNotification);
                        if (isset($userData) && !empty($userData)) {
                            $profile[0] = $userData['profile'];
                            if (isset($profile) && !empty($profile)) {
                                foreach ($profile as $k => $photos) {
                                    $alertData[] = $photos->first_name;
                                    $alertData[] = $photos->age;
                                    $title = $photos->first_name . ", ".$photos->age;
                                    $photo = $photos->profile_picture;
                                    foreach ($photo as $key1 => $image) {
                                        $photoName = $image['url'];
//                                      if ($photoName != '' && file_exists($this->uploadUserProfileOriginalPath . $photoName))
                                        if ($photoName != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName) == 1) 
                                        {
                                            $photos->profile_picture[$key1]['url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName;
                                        } else {
                                            $photos->profile_picture[$key1]['url'] = asset("/backend/images/logo.png");
                                        }
                                    }
                                }
                            }
                            $userData['profile'] = $profile[0];
                        }

                        $notificationArray = [];
                        if (!empty($userData)) {
                            $notificationArray['notification_id'] = $userData['notification_id'];
                            //$notificationArray['notification_text'] = $userData['notification_text'];
                            $notificationArray['notification_status'] = $userData['notification_status'];
                        }
                        $userDetail = $this->UsersRepository->checkUserExitById($otheruserId);
                        if ($userDetail['u_compatibility_notification'] == 1) {
                            $tokenDetail = $this->UsersRepository->getTokenUserId($otheruserId);
                            if (isset($tokenDetail) && !empty($tokenDetail)) {
                                foreach ($tokenDetail AS $k => $tokenValue) {
                                    $token = $tokenValue->udt_device_token;
                                    $deviceType = $tokenValue->udt_device_type;
                                    $message = trans('labels.message');
                                    if ($deviceType == 2) {
                                        $messageData = [];
                                        $messageData['loc-key'] = 'Passed_chat';
                                        $messageData['loc-args'] = $alertData;
                                        $messageData['title'] = $title;
                                        $message = $userData['profile']->first_name." ".$message;
                                        $certificatePath = $this->userCerfificatePath;
                                        $return = Helpers::pushNotificationForiPhone($token,$notificationArray,$certificatePath,$messageData);
                                    } else if ($deviceType == 1) {
                                        $return = Helpers::pushNotificationForAndroid($token,$userData,$message);
                                    }
                                }
                            }
                        }

                        /*$loginUserDetail = $this->UsersRepository->checkUserExitById($userId);
                        $otherUserDetail = $this->UsersRepository->checkUserExitById($otheruserId);
                        if (!empty($loginUserDetail)) {
                            $japperId = $otherUserDetail[0]['u_openfire_id'];
                            $name = $otherUserDetail[0]['u_firstname'];
                            $restUserId = $loginUserDetail[0]['u_openfire_id'];
                            $registerOtherUser = Helpers::registerUserForChat($japperId, $name, $restUserId);

                            $japperId = $loginUserDetail[0]['u_openfire_id'];
                            $name = $loginUserDetail[0]['u_firstname'];
                            $restUserId = $otherUserDetail[0]['u_openfire_id'];
                            $registerLoginUser = Helpers::registerUserForChat($japperId, $name, $restUserId);
                        }*/
                    } else {
                        $data['result'] = 0;
                        $savePersonality['pm_is_match'] = 0;
                        $userData = $this->UsersRepository->getUserUserNotification($userId, $otheruserId);
                        $alertData = [];
                        
                        if (isset($userData) && !empty($userData)) {
                            $profile[0] = $userData['profile'];
                            if (isset($profile) && !empty($profile)) {
                                foreach ($profile as $k => $photos) {
                                    $alertData[] = $photos->first_name;
                                    $alertData[] = $photos->age;
                                    $title = $photos->first_name . ", ".$photos->age;
                                    $photo = $photos->profile_picture;
                                    foreach ($photo as $key1 => $image) {
                                        $photoName = $image['url'];
//                                      if ($photoName != '' && file_exists($this->uploadUserProfileOriginalPath . $photoName))
                                        if ($photoName != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName) == 1)
                                        {
//                                            $photos->profile_picture[$key1]['url'] = asset($this->uploadUserProfileOriginalPath . $photoName);
                                            $photos->profile_picture[$key1]['url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName;
                                        } else {
                                            $photos->profile_picture[$key1]['url'] = asset("/backend/images/logo.png");
                                        }
                                    }
                                }
                            }
                            $userData['profile'] = $profile[0];
                        }

                        $notificationArray = [];
                        if (!empty($userData)) {
                            $notificationArray['notification_id'] = rand(1,100);
                            //$notificationArray['notification_text'] = $userData['notification_text'];
                            $notificationArray['notification_status'] = 4;
                            $userData['notification_status'] = 5;
                            $userData['notification_id'] = rand(1,100);
                        }

                        $userDetail = $this->UsersRepository->checkUserExitById($otheruserId);
                        $tokenDetail = $this->UsersRepository->getTokenUserId($otheruserId);
                        $testArray = array('Failed_chat', 'Failed_chat_fire');
                        if (isset($tokenDetail) && !empty($tokenDetail) && !empty($userData)) {
                            foreach ($tokenDetail AS $k => $tokenValue) {
                                $token = $tokenValue->udt_device_token;
                                $deviceType = $tokenValue->udt_device_type;
                                $version = $tokenValue->udt_appversion;
                                $message = trans('labels.message');
                                if ($deviceType == 2) {
                                    if($version >= Config::get('constant.IOS_VERSION'))
                                    {
                                        $messageData = [];
                                        $messageData['loc-key'] = $testArray[array_rand($testArray)];
                                        $messageData['loc-args'] = $alertData;
                                        //$messageData['title'] = $title;
                                        $message = $userData['profile']->first_name." ".$message;
                                        $certificatePath = $this->userCerfificatePath;
                                        $return = Helpers::pushNotificationForiPhone($token,$notificationArray,$certificatePath,$messageData);
                                    }
                                } else if ($deviceType == 1) {
                                    if($version >= Config::get('constant.ANDROID_VERSION'))
                                    {
                                        $return = Helpers::pushNotificationForAndroid($token,$userData,$message);
                                    }
                                }
                            }
                        }
                    }
                    $objPersonalityMatch = new UserPersonalityMatch();
                    $resultPersonalityMatch = $objPersonalityMatch->savePersonalityMatchData($savePersonality);
                }
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Save setting detail for user
    */
    public function user_save_settings($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveSettingData = [];
                $age = (isset($body['age_range']) && $body['age_range'] != '') ? $body['age_range'] : '';
                if (!empty($age)) {
                    $ageRange = explode(",", $age);
                }
                $saveSettingData['id'] = $userId;
                $saveSettingData['u_looking_for'] = (isset($body['looking_for']) && $body['looking_for'] != '') ? $body['looking_for'] : '';
                $saveSettingData['u_looking_distance'] = (isset($body['distance']) && $body['distance'] != '') ? $body['distance'] : 50;
                $saveSettingData['u_profile_active'] = (isset($body['is_active']) && $body['is_active'] != '') ? $body['is_active'] : '';
                $saveSettingData['u_compatibility_notification'] = (isset($body['noti_compatibility']) && $body['noti_compatibility'] != '') ? $body['noti_compatibility'] : 1;
                $saveSettingData['u_newchat_notification'] = (isset($body['noti_new_chat']) && $body['noti_new_chat'] != '') ? $body['noti_new_chat'] : 1;
                $saveSettingData['u_acceptance_notification'] = (isset($body['noti_acceptance']) && $body['noti_acceptance'] != '') ? $body['noti_acceptance'] : 1;
                if (!empty($ageRange)) {
                    $saveSettingData['u_looking_age_min'] = $ageRange[0];
                    $saveSettingData['u_looking_age_max'] = $ageRange[1];
                } else {
                    $saveSettingData['u_looking_age_min'] = Config::get('constant.LOOKIN_FOR_MIN');
                    $saveSettingData['u_looking_age_max'] = Config::get('constant.LOOKIN_FOR_MAX');
                }
                $userSettingData = $this->UsersRepository->saveUserSettingDetail($saveSettingData);
                if (isset($userSettingData)) {
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.setting_update_message');
                } else {
                    $response['message'] = trans('appmessages.default_error_msg');
                }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Get setting detail for user
    */
    public function user_get_settings($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $userSettingData = $this->UsersRepository->getUserSettingDetail($userId);
                $userSettingData = (isset($userSettingData) && $userSettingData != '') ? $userSettingData[0] : '';
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $userSettingData;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Delete User account
    */
    public function user_delete_account($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $loginUserDetail = $this->UsersRepository->checkUserExitById($userId);
                if (!empty($loginUserDetail)) {
                    $japperId = $loginUserDetail['u_openfire_id'];
                    $name = $loginUserDetail['u_firstname'];
                    $registerOtherUser = Helpers::deleteUser($japperId, $name);
                    if (!$registerOtherUser)
                    {
                        $registerOtherUser = Helpers::deleteUser($japperId, $name);
                    }
                }
                $userData = $this->UsersRepository->deleteUserAllData($userId);
                if (isset($userData)) {
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.default_success_msg');
                } else {
                    $response['message'] = trans('appmessages.default_error_msg');
                }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     * Save 10 personality question when user first time register with MeeTwo
    */
    public function login_save_answer($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $objAttemptedQuestion = new AttemptedQuestionsData();
                $result = $objAttemptedQuestion->deleteAllAttemptedQuestionsByUserIds($userId);
                $saveQuestion = [];
                $saveQuestion['qa_user_id'] = $userId;
                $saveQuestion['qa_question_id'] = (isset($body['question_id']) && $body['question_id'] != '') ? $body['question_id'] : '';
                $saveQuestion['qa_option_id'] = (isset($body['answer']) && $body['answer'] != '') ? $body['answer'] : '';
                $return = $this->QuestionDataRepository->saveAttemptedPersonalityQuestion($saveQuestion);
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }
    
    /*
     *Delete User all data
    */
    public function user_delete($body) 
    {
        $response = [];
        $response['status'] = 0;  
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) 
        {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) 
            {
                $loginUserDetail = $this->UsersRepository->checkUserExitById($userId);
                if (!empty($loginUserDetail) && $loginUserDetail['u_applozic_id'] > 0 && !empty($loginUserDetail['u_applozic_device_key'])) 
                {
                    $japperId = $loginUserDetail['u_openfire_id'];
                    $name = $loginUserDetail['u_firstname'];
                    $applozicParam = [];                    
                    $applozicParam['id'] = $loginUserDetail['id'];
                    $applozicParam['u_openfire_id'] = $loginUserDetail['u_openfire_id'];
                    $applozicParam['u_firstname'] = $loginUserDetail['u_firstname'];
                    $applozicParam['u_lastname'] = $loginUserDetail['u_lastname'];
                    $applozicParam['u_applozic_id'] = $loginUserDetail['u_applozic_id'];
                    $applozicParam['u_applozic_device_key'] = $loginUserDetail['u_applozic_device_key'];
                    $registerOtherUser = Helpers::applozicUserDelete($applozicParam);
                    if($registerOtherUser && $registerOtherUser['status'] == 1)
                    {
                        $loginUserDetail->u_applozic_id = 0;
                        $loginUserDetail->u_applozic_device_key = NULL;
                        $loginUserDetail->u_applozic_user_key = NULL;
                        $loginUserDetail->u_applozic_user_encryption_key = NULL;
                        $loginUserDetail->save();
                    }
                }
                $userData = $this->UsersRepository->deleteUserAllData($userId);
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
            } 
            else 
            {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Delete User all data
    */
    public function user_delete_xmpp($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $loginUserDetail = $this->UsersRepository->checkUserExitById($userId);
                if (!empty($loginUserDetail)) {
                    $japperId = $loginUserDetail['u_openfire_id'];
                    $name = $loginUserDetail['u_firstname'];
                    $registerOtherUser = Helpers::deleteUser($japperId, $name);
                }
                $userData = $this->UsersRepository->deleteUserAllData($userId);
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }
    
    /*
     *Update notification status
    */
    public function user_accept_decline($body) 
    {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) 
        {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) 
            {
                $saveNotification = [];
                $saveNotification['id'] = (isset($body['notification_id']) && $body['notification_id'] != '') ? $body['notification_id'] : 0;
                $saveNotification['un_action'] = (isset($body['accepted']) && $body['accepted'] != '') ? $body['accepted'] : 0;
                $objUserNotification = new UserNotification();
                $data = $objUserNotification->updateUserNotificationStatus($saveNotification);
                $data = (isset($data) && $data != '') ? $data[0] : '';
                if ($body['accepted'] == 1) 
                {
                    $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : 0;
                    $otheruserId = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : 0;
                    $loginUserDetail = $this->UsersRepository->checkUserExitById($userId);
                    $otherUserDetail = $this->UsersRepository->checkUserExitById($otheruserId);
                    
                    $adminUserDetail = $this->UsersRepository->checkUserExistByFacebookId(Config::get('constant.ADMIN_USER_ID'));
                  
                    if (!empty($loginUserDetail)) 
                    {
                        $othersParams = [];
                        $othersParams['loginUserId'] = $userId;
                        $othersParams['otherUserId'] = $otheruserId;
                        if($otherUserDetail && !empty($otherUserDetail) && $otherUserDetail['u_applozic_id'] == 0)
                        {
                            $japperId = (!empty($otherUserDetail['u_openfire_id'])) ? $otherUserDetail['u_openfire_id'] : 0;
                            $name = $otherUserDetail['u_firstname'];
                            $restUserId = (!empty($loginUserDetail['u_openfire_id'])) ? $loginUserDetail['u_openfire_id'] : 0;
                            $othersParams['u_firstname'] = $otherUserDetail['u_firstname'];
                            $othersParams['u_lastname'] = $otherUserDetail['u_lastname'];
                            $othersParams['u_email'] = $otherUserDetail['u_email'];
                            $othersParams['userId'] = $otherUserDetail['id'];                 
                            $registerOtherUser = Helpers::applozicRegisterUserForChat($japperId, $name, $restUserId, $othersParams);
                            if($registerOtherUser && $registerOtherUser['status'] == 1 && !empty($registerOtherUser['data']))
                            {
                                Log::info($otherUserDetail['u_firstname'] ." add ". $loginUserDetail['u_firstname'] . " to as chat user(Roster) in openfireserver successfully #");
                                $otherUserDetail->u_applozic_id = $registerOtherUser['data']['userId'];
                                $otherUserDetail->u_applozic_device_key = $registerOtherUser['data']['deviceKey'];
                                $otherUserDetail->u_applozic_user_key = $registerOtherUser['data']['userKey'];
                                $otherUserDetail->u_applozic_user_encryption_key = $registerOtherUser['data']['userEncryptionKey'];
                                $otherUserDetail->save();
                            } 
                            else 
                            {                                
                                $saveNotification['un_action'] = 0;
                                $data = $objUserNotification->updateUserNotificationStatus($saveNotification);
                                Log::error($otherUserDetail['u_firstname'] ." add ". $loginUserDetail['u_firstname'] . " to as chat user in openfireserver but fail because of XMPP server down #");
                                $response['status'] = 0;
                                $response['message'] = trans('appmessages.default_error_msg');
                                echo json_encode($response);
                                exit;
                            }
                        }                        
                        if($loginUserDetail['u_applozic_id'] == 0)
                        {
                            $japperId = (!empty($loginUserDetail['u_openfire_id'])) ? $loginUserDetail['u_openfire_id'] : 0;
                            $name = $loginUserDetail['u_firstname'];
                            $restUserId = (!empty($otherUserDetail['u_openfire_id'])) ? $otherUserDetail['u_openfire_id'] : 0;
                            $othersParams['u_firstname'] = $loginUserDetail['u_firstname'];
                            $othersParams['u_lastname'] = $loginUserDetail['u_lastname'];
                            $othersParams['u_email'] = $loginUserDetail['u_email'];
                            $othersParams['userId'] = $loginUserDetail['id'];
                            $registerLoginUser = Helpers::applozicRegisterUserForChat($japperId, $name, $restUserId, $othersParams);
                            if($registerLoginUser && $registerLoginUser['status'] == 1 && !empty($registerLoginUser['data']))
                            {
                                Log::info($loginUserDetail['u_firstname'] ." add ". $otherUserDetail['u_firstname'] . " to as chat user(Roster) in openfireserver successfully #");
                                $loginUserDetail->u_applozic_id = $registerLoginUser['data']['userId'];
                                $loginUserDetail->u_applozic_device_key = $registerLoginUser['data']['deviceKey'];
                                $loginUserDetail->u_applozic_user_key = $registerLoginUser['data']['userKey'];
                                $loginUserDetail->u_applozic_user_encryption_key = $registerLoginUser['data']['userEncryptionKey'];
                                $loginUserDetail->save();
                            } else {
                                
                                $saveNotification['un_action'] = 0;
                                $data = $objUserNotification->updateUserNotificationStatus($saveNotification);
                                Log::error($loginUserDetail['u_firstname'] ." add ". $otherUserDetail['u_firstname'] . " to as chat user in applozic but fail because of applozic server down #");
                                $response['status'] = 0;
                                $response['message'] = trans('appmessages.default_error_msg');
                                echo json_encode($response);
                                exit;
                            }
                        }                        
                    }     
                    
                    if(!empty($loginUserDetail) && $loginUserDetail->u_applozic_id > 0 && !empty($otherUserDetail) && $otherUserDetail->u_applozic_id > 0)
                    {
//                        $sendMsgParams = [];
//                        $sendMsgParams['loginUserId'] = $loginUserDetail->u_applozic_id;
//                        $sendMsgParams['otherUserId'] = $otherUserDetail->u_applozic_id;
//                        $sendMsgParams['message'] = Config::get('constant.APPLOZIC_MSG_CONTENT_TYPE').' '.$otherUserDetail['u_applozic_id'];
//                        $sendMsgParams['contentType'] = Config::get('constant.APPLOZIC_MSG_CONTENT_TYPE');
//                        $sendCommonMsg = Helpers::applozicCommonMsgToUser($sendMsgParams);
                    }
                    $userData = $objUserNotification->getNotificationsData($body['notification_id']);
                    $alertData = [];
                    $title = '';
                    if (!empty($userData)) 
                    {
                        $alertData[] = $userData['user_first_name'];
                        $alertData[] = $userData['age'];
                        $title = $userData['user_first_name'] . ", " .$userData['age'];
                        $url = '';
                        $userData['user_profile_url'] = (isset($userData['user_profile_url']) && !empty($userData['user_profile_url'])) ? $userData['user_profile_url'] : '';
                        $userData['is_profile_photo'] = (isset($userData['is_profile_photo']) && !empty($userData['is_profile_photo'])) ? $userData['is_profile_photo'] : '';
                        $image = explode(",", $userData['user_profile_url']);
                        $IsProfile = explode(",", $userData['is_profile_photo']);
                        for ($i = 0; $i < count($image); $i++) 
                        {
                            if ($IsProfile[$i] == 1) 
                            {
                                $photo = $image[$i];
//                              if ($photo != '' && file_exists($this->uploadUserProfileOriginalPath . $photo)) 
                                if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1) 
                                {
//                                  $url = asset($this->uploadUserProfileOriginalPath . $photo);
                                    $url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo;
                                } else {
                                    $url = asset("/backend/images/logo.png");
                                }
                            }
                        }
                        if (empty($url)) 
                        {
                            $photo = $image[0];
//                          if ($photo != '' && file_exists($this->uploadUserProfileOriginalPath . $photo))
                            if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1) 
                            {
//                              $url = asset($this->uploadUserProfileOriginalPath . $photo);
                                $url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo;
                            } else {
                                $url = asset("/backend/images/logo.png");
                            }
                        }
                        $userData['user_profile_url'] = $url;
                        $userData['notification_status'] = 2;
                        unset($userData['is_profile_photo']);
                    }
                    $notificationArray = [];
                    if (!empty($userData)) 
                    {
                     //   $notificationArray['notification_id'] = $userData['notification_id'];
                        $notificationArray['notification_status'] = $userData['notification_status'];
                    }

                    if ($otherUserDetail['u_acceptance_notification'] == 1) 
                    {
                        $tokenDetail = $this->UsersRepository->getTokenUserId($otheruserId);
                        if (isset($tokenDetail) && !empty($tokenDetail)) 
                        {
                            foreach ($tokenDetail AS $k => $tokenValue) 
                            {
                                $token = $tokenValue->udt_device_token;
                                $deviceType = $tokenValue->udt_device_type;
                                $message = $loginUserDetail['u_firstname'].trans('labels.accept_msg');
                                $userData['message'] = $message;
                                $userData['notification_text'] = $message;
                                $messageData = [];
                                $messageData['loc-key'] = 'Accept_Request';
                                $messageData['loc-args'] = $alertData;
                                $messageData['title'] = $title;
                             // $notificationArray['notification_text'] = $message;
                                if ($deviceType == 2) 
                                {
                                    $certificatePath = $this->userCerfificatePath;
                                    $return = Helpers::pushNotificationForiPhone($token,$notificationArray,$certificatePath,$messageData);
                                } 
                                elseif ($deviceType == 1) 
                                {
                                    $return = Helpers::pushNotificationForAndroid($token,$userData,$message);
                                }
                            }
                        }
                    }                   
                }
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Update notification status
    */
    public function user_accept_decline_xmpp($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveNotification = [];
                $saveNotification['id'] = (isset($body['notification_id']) && $body['notification_id'] != '') ? $body['notification_id'] : 0;
                $saveNotification['un_action'] = (isset($body['accepted']) && $body['accepted'] != '') ? $body['accepted'] : 0;
                $objUserNotification = new UserNotification();
                $data = $objUserNotification->updateUserNotificationStatus($saveNotification);
                $data = (isset($data) && $data != '') ? $data[0] : '';
                if ($body['accepted'] == 1) {
                    $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : 0;
                    $otheruserId = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : 0;
                    $loginUserDetail = $this->UsersRepository->checkUserExitById($userId);
                    $otherUserDetail = $this->UsersRepository->checkUserExitById($otheruserId);

                    $adminUserDetail = $this->UsersRepository->checkUserExistByFacebookId(Config::get('constant.ADMIN_USER_ID'));

                    if (!empty($loginUserDetail)) {
                        $japperId = $otherUserDetail['u_openfire_id'];
                        $name = $otherUserDetail['u_firstname'];
                        $restUserId = $loginUserDetail['u_openfire_id'];
                        $registerOtherUser = Helpers::registerUserForChat($japperId, $name, $restUserId);
                        if(!$registerOtherUser)
                        {
                            $registerOtherUser = Helpers::registerUserForChat($japperId, $name, $restUserId);
                            if(!$registerOtherUser)
                            {
                                $saveNotification['un_action'] = 0;
                                $data = $objUserNotification->updateUserNotificationStatus($saveNotification);
                                Log::error($otherUserDetail['u_firstname'] ." add ". $loginUserDetail['u_firstname'] . " to as chat user(Roster) in openfireserver but fail because of XMPP server down #");
                                $response['status'] = 0;
                                echo json_encode($response);
                                exit;
                            } else {
                                Log::info($otherUserDetail['u_firstname'] ." add ". $loginUserDetail['u_firstname'] . " to as chat user(Roster) in openfireserver successfully #");
                            }
                        } else {
                            Log::info($otherUserDetail['u_firstname'] ." add ". $loginUserDetail['u_firstname'] . " to as chat user(Roster) in openfireserver successfully #");
                        }

                        $japperId = $loginUserDetail['u_openfire_id'];
                        $name = $loginUserDetail['u_firstname'];
                        $restUserId = $otherUserDetail['u_openfire_id'];
                        $registerLoginUser = Helpers::registerUserForChat($japperId, $name, $restUserId);
                        if(!$registerLoginUser)
                        {
                            $registerLoginUser = Helpers::registerUserForChat($japperId, $name, $restUserId);
                            if(!$registerLoginUser)
                            {
                                $saveNotification['un_action'] = 0;
                                $data = $objUserNotification->updateUserNotificationStatus($saveNotification);
                                Log::error($loginUserDetail['u_firstname'] ." add ". $otherUserDetail['u_firstname'] . " to as chat user(Roster) in openfireserver but fail because of XMPP server down #");
                                $response['status'] = 0;
                                echo json_encode($response);
                                exit;
                            } else {
                                Log::info($loginUserDetail['u_firstname'] ." add ". $otherUserDetail['u_firstname'] . " to as chat user(Roster) in openfireserver successfully #");
                            }
                        } else {
                            Log::info($loginUserDetail['u_firstname'] ." add ". $otherUserDetail['u_firstname'] . " to as chat user(Roster) in openfireserver successfully #");
                        }

                        //add admin account
                        // if (($adminUserDetail['id'] != $loginUserDetail['id']) || ($adminUserDetail['id'] != $otherUserDetail['id'])) {
                        //     $japperId = $adminUserDetail['u_openfire_id'];
                        //     $name = $adminUserDetail['u_firstname'];
                        //     $restUserId = $loginUserDetail['u_openfire_id'];
                        //     $registerOtherUser = Helpers::registerUserForChat($japperId, $name, $restUserId);

                        //     $japperId = $loginUserDetail['u_openfire_id'];
                        //     $name = $loginUserDetail['u_firstname'];
                        //     $restUserId = $adminUserDetail['u_openfire_id'];
                        //     $registerOtherUser = Helpers::registerUserForChat($japperId, $name, $restUserId);

                        //     $japperId = $otherUserDetail['u_openfire_id'];
                        //     $name = $otherUserDetail['u_firstname'];
                        //     $restUserId = $adminUserDetail['u_openfire_id'];
                        //     $registerOtherUser = Helpers::registerUserForChat($japperId, $name, $restUserId);

                        //     $japperId = $adminUserDetail['u_openfire_id'];
                        //     $name = $adminUserDetail['u_firstname'];
                        //     $restUserId = $otherUserDetail['u_openfire_id'];
                        //     $registerOtherUser = Helpers::registerUserForChat($japperId, $name, $restUserId);
                        // }
                    }
                    $userData = $objUserNotification->getNotificationsData($body['notification_id']);
                    $alertData = [];
                    $title = '';
                    if (!empty($userData)) {
                        $alertData[] = $userData['user_first_name'];
                        $alertData[] = $userData['age'];
                        $title = $userData['user_first_name'] . ", " .$userData['age'];
                        $url = '';
                        $userData['user_profile_url'] = (isset($userData['user_profile_url']) && !empty($userData['user_profile_url'])) ? $userData['user_profile_url'] : '';
                        $userData['is_profile_photo'] = (isset($userData['is_profile_photo']) && !empty($userData['is_profile_photo'])) ? $userData['is_profile_photo'] : '';
                        $image = explode(",", $userData['user_profile_url']);
                        $IsProfile = explode(",", $userData['is_profile_photo']);
                        for ($i = 0; $i < count($image); $i++) {
                            if ($IsProfile[$i] == 1) {
                                $photo = $image[$i];
//                              if ($photo != '' && file_exists($this->uploadUserProfileOriginalPath . $photo)) {
                                if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1) {
//                                  $url = asset($this->uploadUserProfileOriginalPath . $photo);
                                    $url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo;
                                } else {
                                    $url = asset("/backend/images/logo.png");
                                }
                            }
                        }
                        if (empty($url)) {
                            $photo = $image[0];
//                          if ($photo != '' && file_exists($this->uploadUserProfileOriginalPath . $photo)) {
                            if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1) {
//                                $url = asset($this->uploadUserProfileOriginalPath . $photo);
                                $url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo;
                            } else {
                                $url = asset("/backend/images/logo.png");
                            }
                        }
                        $userData['user_profile_url'] = $url;
                        $userData['notification_status'] = 2;
                        unset($userData['is_profile_photo']);
                    }
                    $notificationArray = [];
                    if (!empty($userData)) {
                     //   $notificationArray['notification_id'] = $userData['notification_id'];
                        $notificationArray['notification_status'] = $userData['notification_status'];
                    }

                    if ($otherUserDetail['u_acceptance_notification'] == 1) {
                        $tokenDetail = $this->UsersRepository->getTokenUserId($otheruserId);
                        if (isset($tokenDetail) && !empty($tokenDetail)) {
                            foreach ($tokenDetail AS $k => $tokenValue) {
                                $token = $tokenValue->udt_device_token;
                                $deviceType = $tokenValue->udt_device_type;
                                $message = $loginUserDetail['u_firstname'].trans('labels.accept_msg');
                                $userData['message'] = $message;
                                $userData['notification_text'] = $message;
                                $messageData = [];
                                $messageData['loc-key'] = 'Accept_Request';
                                $messageData['loc-args'] = $alertData;
                                $messageData['title'] = $title;
                             //   $notificationArray['notification_text'] = $message;
                                if ($deviceType == 2) {
                                    $certificatePath = $this->userCerfificatePath;
                                    $return = Helpers::pushNotificationForiPhone($token,$notificationArray,$certificatePath,$messageData);
                                } else if ($deviceType == 1) {
                                    $return = Helpers::pushNotificationForAndroid($token,$userData,$message);
                                }
                            }
                        }
                    }
                }
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

     /*
     *User get all notifications
    */
    public function user_get_notification($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $slot = (isset($body['page_no']) && $body['page_no'] != '') ? $body['page_no'] : 0;
                $userData = $this->UsersRepository->getAllUserNotificationData($userId,$slot);

                if (isset($userData) && !empty($userData)) {
                    foreach ($userData AS $key => $value) {
                        $userData[$key]['notification_id'] = (string)$value['notification_id'];
                        $userData[$key]['other_user_id'] = (string)$value['other_user_id'];
                        $userData[$key]['notification_type'] = (string)$value['notification_type'];
                        $userData[$key]['status'] = (string)$value['status'];
                        
                        $userData[$key]['profile']->id = (string)$value['profile']->id;
                        $userData[$key]['profile']->age = (string)$value['profile']->age;
                        $userData[$key]['profile']->gender = (string)$value['profile']->gender;
                        
                        $profile[0] = $value['profile'];
                        if (isset($profile) && !empty($profile)) {
                            foreach ($profile as $k => $photos) {
                                $photo = $photos->profile_picture;
                                foreach ($photo as $key1 => $image) {
                                    $photoName = $image['url'];
//                                    if ($photoName != '' && file_exists($this->uploadUserProfileOriginalPath . $photoName)) {
                                    if ($photoName != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName) == 1) {
//                                        $photos->profile_picture[$key1]['url'] = asset($this->uploadUserProfileOriginalPath . $photoName);
                                        $photos->profile_picture[$key1]['url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName;
                                    } else {
                                        $photos->profile_picture[$key1]['url'] = asset("/backend/images/logo.png");
                                    }
                                }
                            }
                        }
                       $userData[$key]['profile'] = $profile[0];
                    }
                }
                $data['page_no'] = $slot;
                $data['notifications'] = $userData;

                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Save user profile report detail
    */
    public function report_user($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                    $saveUserReportData = [];
                    $saveUserReportData['upr_viewer_id'] = $userId;
                    $saveUserReportData['upr_viewed_id'] = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                    $saveUserReportData['upr_report_reason'] = (isset($body['report_reason']) && $body['report_reason'] != '') ? $body['report_reason'] : '';
                    $saveUserReportData['upr_report_reason_text'] = (isset($body['reason_text']) && $body['reason_text'] != '') ? $body['reason_text'] : '';
                    $objUserReport = new UserProfileReport();
                    $return = $objUserReport->saveUserProfileReportDetail($saveUserReportData);
                    if (isset($return)) {
                        $userData = $this->UsersRepository->getAllUserDataForSendMail($userId,$body['other_user_id']);
                        if (isset($userData)) {
                            $objConfigration = new Configurations();
                            $mail_id = $objConfigration->getMailIdforAdmin();
                            $replaceArray = array();
                            $replaceArray['FROMUSER'] = $userData['user_name'];
                            $replaceArray['TOUSER'] = $userData['other_user_name'];
                            $reason = '';
                            if ($body['report_reason'] == 1) { $reason = 'Inapropriate Photos';} else if ($body['report_reason'] == 2) { $reason = 'Spamming or Robot';}else if ($body['report_reason'] == 3) { $reason = 'Underage User';} else if ($body['report_reason'] == 4) { $reason = $body['reason_text']; } else { $reason = 'Other Reason'; };
                            $replaceArray['REASON'] =  $reason;
                            $emailTemplateContent = $this->EmailTemplatesRepository->getEmailTemplateDataByName(Config::get('constant.USER_REPORT_TEMPLATE'));
                            $content = $this->EmailTemplatesRepository->getEmailContent($emailTemplateContent->et_body, $replaceArray);

                            $data = array();
                            $data['subject'] = Config::get('constant.MAIL_SUBJECT');
                            $data['toEmail'] = $mail_id[0]['c_value'];
                            $data['toName'] = Config::get('constant.ADMIN_NAME');
                            $data['content'] = $content;

                            Mail::send(['html' => 'emails.Template'], $data , function ($m) use ($data) {
                                $m->from(Config::get('constant.FROM_MAIL_ID'), Config::get('constant.MAIL_SUBJECT'));
                                $m->subject($data['subject']);
                                $m->to($data['toEmail'], $data['toName']);
                            });
                            $data = [];
                            $data['result'] = 1;
                            $response['status'] = 1;
                            $response['message'] = trans('appmessages.default_success_msg');
                            $response['data'] = $data;
                        }
                    }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Get question that is passed by user
    */
    public function user_test_passed_question($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $questionData = $this->QuestionDataRepository->GetUserPassedQuestionByUserId($userId);
                $data =  [];
                $data['questions'] = $questionData;
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Set image as profile image
    */
    public function set_profile_pic($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $objPhotos = new UserPhotos();
                $imageData = $objPhotos->SetProfilePicByUserId($userId,$body['image_id']);
                $data = [];
                if (isset($imageData)) {
                    $data['image_id'] = $imageData->id;
                    $photo = $imageData->up_photo_name;
//                  if ($photo != '' && file_exists($this->uploadUserProfileOriginalPath . $photo)) {
                    if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1) {
//                        $data['image_url'] = asset($this->uploadUserProfileOriginalPath . $photo);
                        $data['image_url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo;
                    } else {
                        $data['image_url'] = asset("/backend/images/logo.png");
                    }
                }
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /*
     *Save edit profile data for user
    */
    public function user_profile_edit_data($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveUserProfileData = [];
                $saveUserProfileData['id'] = $userId;
                $saveUserProfileData['u_school'] = (isset($body['text_school']) && $body['text_school'] != '') ? $body['text_school'] : '';
                $saveUserProfileData['u_description'] = (isset($body['text_desc']) && $body['text_desc'] != '') ? $body['text_desc'] : '';
                $saveUserProfileData['u_current_work'] = (isset($body['text_work']) && $body['text_work'] != '') ? $body['text_work'] : '';
                $userProfileDetail = $this->UsersRepository->updateUserProfileDetail($saveUserProfileData);
                
                if (!empty($userProfileDetail)) {
                    unset($userProfileDetail[0]->score);
                }
                //update user score
                $userScoreUpdate = Helpers::updateUserScore($userId);
                $scoreUpdate = Helpers::updateUserTotalScoreById($userId);
                $user = $this->UsersRepository->checkUserExitById($userId);
                     
                if (count($user) > 0) {
                    if ($user['u_total_score'] == Config::get('constant.USER_PROFILE_SCORE')) {
                        $userProfileDetail[0]->is_profile_completed = 1;
                    } else {
                        $userProfileDetail[0]->is_profile_completed = 0;
                    }
                    if ($user['u_update_first_time'] == 0) {
                        $user = $this->UsersRepository->updateUserFirstTimeUpdateFlagById($userId);  
                        $userProfileDetail[0]->first_time_update = 1;
                    } else {
                        $userProfileDetail[0]->first_time_update = 0;
                    }
                }
                $userProfileDetail = (isset($userProfileDetail) && $userProfileDetail != '') ? $userProfileDetail[0] : '';
 
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $userProfileDetail;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    public function set_user_active($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveUserProfileData = [];
                $saveUserProfileData['id'] = $userId;
                $saveUserProfileData['u_profile_active'] = (isset($body['is_active']) && $body['is_active'] != '') ? $body['is_active'] : '';
                $userProfileDetail = $this->UsersRepository->saveUserSettingDetail($saveUserProfileData);
                $data = [];
                $data['is_active'] = (isset($body['is_active']) && $body['is_active'] != '') ? $body['is_active'] : '';
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    public function get_user_all_info($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if(isset($body['appVersion']) && $body['appVersion'] != '')
        {
            if (isset($body['user_id']) && $body['user_id'] > 0) {
                $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
                $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
                if (isset($checkuserexist) && $checkuserexist) {
                    $lang = (isset($body['language']) && $body['language'] != '') ? $body['language'] : '';
                    $userDetail = $this->UsersRepository->getAllUserProfileDetail($userId);
                    if (!empty($userDetail)) {
                        if ($userDetail['profile'][0]->score == Config::get('constant.USER_PROFILE_SCORE')) {
                            $userDetail['profile'][0]->is_profile_completed = 1;
                        } else {
                            $userDetail['profile'][0]->is_profile_completed = 0; 
                        }
                        unset($userDetail['profile'][0]->score);
                    }
                    $userDetail['profile'][0]->gender = (string)$userDetail['profile'][0]->gender;
                    $userProfileDetail = $userDetail['profile'];
                   
                    foreach ($userProfileDetail as $key => $photos) {
                        $photo = $photos->profile_picture;
                        $photos->age = (string)$photos->age;
                        if (!empty($photo)) {
                            foreach ($photo as $key1 => $image) {
                                if ($image['pic_id'] != '') {
                                    $photoName = $image['url'];
//                                  if ($photoName != '' && file_exists($this->uploadUserProfileOriginalPath . $photoName)) {
                                    if ($photoName != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName) == 1) {
//                                        $photos->profile_picture[$key1]['url'] = asset($this->uploadUserProfileOriginalPath . $photoName);
                                        $photos->profile_picture[$key1]['url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName;
                                    } else {
                                        $photos->profile_picture[$key1]['url'] = asset("/backend/images/logo.png");
                                    }
                                } else {
                                    $photos->profile_picture = [];
                                }
                            }
                        }
                    }
                    $userSettingData = $this->UsersRepository->getUserSettingDetail($userId);
                    $userSettingData = (isset($userSettingData) && $userSettingData != '') ? $userSettingData[0] : '';
                    $questions = $this->QuestionDataRepository->getAllAttemptedPersonalityQuestion($userId,$lang);
                    $questions = (isset($questions) && $questions != '') ? $questions : '';
                    $userSettingData->is_active = (string)$userSettingData->is_active;
                    $userSettingData->looking_for = (string)$userSettingData->looking_for;
                    $userSettingData->distance = (string)$userSettingData->distance;
                    $userSettingData->noti_compatibility = (string)$userSettingData->noti_compatibility;
                    $userSettingData->noti_new_chat = (string)$userSettingData->noti_new_chat;
                    $userSettingData->noti_acceptance = (string)$userSettingData->noti_acceptance;
                    $userDetail['settings'] = $userSettingData;
                    $userDetail['questions'] = $questions;
                    $userDetail['profile'] = (!empty($userProfileDetail) && $userProfileDetail != '') ? $userProfileDetail[0] : [];
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.default_success_msg');
                    $response['data'] = $userDetail;
                } else {
                    $response['status'] = 3;
                    $response['message'] = trans('appmessages.invalid_userid_msg');
                }
            } else {
                $response['message'] = trans('appmessages.default_error_msg');
            }
        }else{
            $response['status'] = 3;
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    public function sendNotificationForMatchPersonality($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $otheruserId = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                if ($body['match'] == 1) {
                    $saveNotification = [];
                    $saveNotification['un_sender_id'] = $userId;
                    $saveNotification['un_receiver_id'] = $otheruserId;
                    $saveNotification['un_notification_text'] = trans('labels.message');
                    $saveNotification['un_is_read'] = 0;
                    $saveNotification['un_action'] = 0;
                    $objUserNotification = new UserNotification();
                    $userData = $objUserNotification->saveUserNotification($saveNotification);
                    $data = [];
                    $alertData = [];
                    $title = '';
                    if (isset($userData) && !empty($userData)) {
                        $profile[0] = $userData['profile'];
                        if (isset($profile) && !empty($profile)) {
                            foreach ($profile as $k => $photos) {
                                $alertData[] = $photos->first_name;
                                $alertData[] = $photos->age;
                                $title = $photos->first_name .", " .$photos->age;
                                $photo = $photos->profile_picture;
                                foreach ($photo as $key1 => $image) {
                                    $photoName = $image['url'];
//                                    if ($photoName != '' && file_exists($this->uploadUserProfileOriginalPath . $photoName)) {
                                    if ($photoName != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName) == 1) {
//                                        $photos->profile_picture[$key1]['url'] = asset($this->uploadUserProfileOriginalPath . $photoName);
                                        $photos->profile_picture[$key1]['url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName;
                                    } else {
                                        $photos->profile_picture[$key1]['url'] = asset("/backend/images/logo.png");
                                    }
                                }
                            }
                        }
                        $userData['profile'] = $profile[0];
                    }
                    $notificationArray = [];
                    if (!empty($userData)) {
                        $notificationArray['notification_id'] = $userData['notification_id'];
                        //$notificationArray['notification_text'] = $userData['notification_text'];
                        $notificationArray['notification_status'] = $userData['notification_status'];
                    }

                    $tokenDetail = $this->UsersRepository->getTokenUserId($otheruserId);

                    if (isset($tokenDetail) && !empty($tokenDetail)) {
                        foreach ($tokenDetail AS $k => $tokenValue) {
                            $token = $tokenValue->udt_device_token;
                            $deviceType = $tokenValue->udt_device_type;
                            $message = trans('labels.message');
                            if ($deviceType == 2) {
                                $messageData = [];
                                $messageData['loc-key'] = 'Passed_chat';
                                $messageData['loc-args'] = $alertData;
                                $messageData['title'] = $title;
                                $certificatePath = $this->userCerfificatePath;
                                $return = Helpers::pushNotificationForiPhone($token,$notificationArray,$certificatePath,$messageData);
                            } else if ($deviceType == 1) {
                                $return = Helpers::pushNotificationForAndroid($token,$userData,$message);
                            }
                        }
                    }
                    $data['result'] = 1;
                } else {
                    $data['result'] = 0;
                }
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    public function get_chat_list($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $lang = (isset($body['language']) && $body['language'] != '') ? $body['language'] : 0; 
                //$userData = $this->UsersRepository->getChatUserList($userId,$lang);
                $userData = $this->UsersRepository->getChatUserListDetail($userId,$lang);
                
                if (isset($userData) && !empty($userData)) {
                    foreach ($userData as $key => $photos) 
                    {
                        $photos->id = (string)$photos->id;
                        $photo = $photos->profile_picture;
                        foreach ($photo as $key1 => $image) {
                            $photoName = $image['url'];
//                          if ($photoName != '' && file_exists($this->uploadUserProfileOriginalPath . $photoName)) {
                            if ($photoName != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName) == 1) {
//                                $photos->profile_picture[$key1]['url'] = asset($this->uploadUserProfileOriginalPath . $photoName);
                                $photos->profile_picture[$key1]['url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName;
                            } else {
                                $photos->profile_picture[$key1]['url'] = asset("/backend/images/logo.png");
                            }
                        }
                    }
                }
                               
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $userData;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    public function add_favorite_user($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $otherUserId = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                $isfavorite = (isset($body['is_favorite']) && $body['is_favorite'] != '') ? $body['is_favorite'] : '';
                $saveFavoriteUserData = [];
                $saveFavoriteUserData['fu_from_user_id'] = $userId;
                $saveFavoriteUserData['fu_to_user_id'] = $otherUserId;
                $saveFavoriteUserData['fu_is_favorite'] = $isfavorite;
                $objFavoriteUser = new FavoriteUser();
                $data['result'] = $body['is_favorite'];
                $result = $objFavoriteUser->saveFavoriteUserDetail($saveFavoriteUserData);
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    public function get_passed_personality_questions($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $otherUserId = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';

                $QuestionData = $this->QuestionDataRepository->getPassedPersonalityQuestion($userId,$otherUserId);
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $QuestionData;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    public function delete_compatibility($body) 
    {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) 
        {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) 
            {
                $otheruserId = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                $result = $this->UsersRepository->deleteUserCompatibility($userId,$otheruserId);
                $objUserDelete = new UserDeleteCompatability();
                $saveUserDeleteData = [];
                $saveUserDeleteData['udc_user_id'] = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
                $saveUserDeleteData['udc_other_user_id'] = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                $saveUserDeleteData['udc_delete_reason'] = (isset($body['delete_reason']) && $body['delete_reason'] != '') ? $body['delete_reason'] : '';
                $userDeleteResult = $objUserDelete->saveUserDeleteCompatibilityDetail($saveUserDeleteData);

                $loginUserDetail = $this->UsersRepository->checkUserExitById($userId);
                $otherUserDetail = $this->UsersRepository->checkUserExitById($otheruserId);
                if (!empty($loginUserDetail)) 
                {
                    $japperId = $otherUserDetail['u_openfire_id'];
                    $name = $otherUserDetail['u_firstname'];
                    $restUserId = $loginUserDetail['u_openfire_id'];
                    $registerOtherUser = Helpers::deleteUserRoster($japperId, $name, $restUserId);
                    if (!$registerOtherUser)
                    {
                        $registerOtherUser = Helpers::deleteUserRoster($japperId, $name, $restUserId);
                    }

                    $japperId = $loginUserDetail['u_openfire_id'];
                    $name = $loginUserDetail['u_firstname'];
                    $restUserId = $otherUserDetail['u_openfire_id'];
                    $registerLoginUser = Helpers::deleteUserRoster($japperId, $name, $restUserId);
                    if (!$registerLoginUser)
                    {
                        $registerLoginUser = Helpers::deleteUserRoster($japperId, $name, $restUserId);
                    }
                }
                $data = [];
                $data['result'] = 1;
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } 
            else 
            {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } 
        else 
        {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }
    
    public function delete_compatibility_xmpp($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $otheruserId = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';

                $result = $this->UsersRepository->deleteUserCompatibility($userId,$otheruserId);
                $objUserDelete = new UserDeleteCompatability();
                $saveUserDeleteData = [];
                $saveUserDeleteData['udc_user_id'] = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
                $saveUserDeleteData['udc_other_user_id'] = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                $saveUserDeleteData['udc_delete_reason'] = (isset($body['delete_reason']) && $body['delete_reason'] != '') ? $body['delete_reason'] : '';
                $userDeleteResult = $objUserDelete->saveUserDeleteCompatibilityDetail($saveUserDeleteData);

                $loginUserDetail = $this->UsersRepository->checkUserExitById($userId);
                $otherUserDetail = $this->UsersRepository->checkUserExitById($otheruserId);
                if (!empty($loginUserDetail)) {
                    $japperId = $otherUserDetail['u_openfire_id'];
                    $name = $otherUserDetail['u_firstname'];
                    $restUserId = $loginUserDetail['u_openfire_id'];
                    $registerOtherUser = Helpers::deleteUserRoster($japperId, $name, $restUserId);
                    if (!$registerOtherUser)
                    {
                        $registerOtherUser = Helpers::deleteUserRoster($japperId, $name, $restUserId);
                    }

                    $japperId = $loginUserDetail['u_openfire_id'];
                    $name = $loginUserDetail['u_firstname'];
                    $restUserId = $otherUserDetail['u_openfire_id'];
                    $registerLoginUser = Helpers::deleteUserRoster($japperId, $name, $restUserId);
                    if (!$registerLoginUser)
                    {
                        $registerLoginUser = Helpers::deleteUserRoster($japperId, $name, $restUserId);
                    }
                }
                $data = [];
                $data['result'] = 1;
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $data;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    public function add_to_admin_chat($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $adminId = $checkuserexist = $this->UsersRepository->getAdminUserId();
                $saveNotification = [];
                $saveNotification['un_sender_id'] = $userId;
                $saveNotification['un_receiver_id'] = $adminId;
                $saveNotification['un_notification_text'] = trans('labels.message');
                $saveNotification['un_is_read'] = 1;
                $saveNotification['un_action'] = 1;
                $objUserNotification = new UserNotification();
                $userData = $objUserNotification->saveUserNotificationForAdmin($saveNotification);

                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    public function get_chat_user_personality_questions($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $otherUserId = (isset($body['other_user_id']) && $body['other_user_id'] != '') ? $body['other_user_id'] : '';
                $lang = (isset($body['language']) && $body['language'] != '') ? $body['language'] : 0; 

                $QuestionData = $this->QuestionDataRepository->getPersonalityQuestionForChatUser($userId,$otherUserId,$lang);
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $QuestionData;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response); 
    }

    public function get_chat_user_list($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $lang = (isset($body['language']) && $body['language'] != '') ? $body['language'] : 0; 
                $userData = $this->UsersRepository->getChatUserListDetail($userId,$lang);
                if (isset($userData) && !empty($userData)) {
                    foreach ($userData as $key => $photos) {
                        $photo = $photos->profile_picture;
                        foreach ($photo as $key1 => $image) {
                            $photoName = $image['url'];
//                            if ($photoName != '' && file_exists($this->uploadUserProfileOriginalPath . $photoName)) {
                            if ($photoName != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName) == 1) {
//                                $photos->profile_picture[$key1]['url'] = asset($this->uploadUserProfileOriginalPath . $photoName);
                                $photos->profile_picture[$key1]['url'] = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photoName;
                            } else {
                                $photos->profile_picture[$key1]['url'] = asset("/backend/images/logo.png");
                            }
                        }
                    }
                }
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
                $response['data'] = $userData;
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }
    
    /**
     * To generate JWT Auth token from given data
     * @param [object] $data
     * @return array
     */
    public function get_token($data) {
        try {
            Config::set('auth.model', \App\Users::class);
            if (!$token = JWTAuth::fromUser($data)) {
                return [
                    'status' => 0,
                    'message' => 'User does not exists.'
                ];
            }
        } catch (JWTAuthException $e) {
            return [
                'status' => 0,
                'message' => 'Failed to create token.'
            ];
        }
        return [
            'status' => 1,
            'message' => $token
        ];
    }
    public function update_user_message_count($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveData['cm_user_id'] = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '0';
                $saveData['cm_message_count'] = (isset($body['message_count']) && $body['message_count'] != '') ? $body['message_count'] : '';
                $saveData['cm_message_date'] = date("Y-m-d");

                $objChatMessages = new ChatMessages();

                $result = $objChatMessages->storeChatMessageCountForUser($saveData);
                
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }


    public function update_notification_message_count($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveData['fmc_user_id'] = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '0';
                $saveData['fmc_message_type'] = (isset($body['message_type']) && $body['message_type'] != '') ? $body['message_type'] : '';

                $objMessageCount = new MessageCount();

                $result = $objMessageCount->saveMessageCountData($saveData);
                if ($body['message_type'] == 1)
                {
                    Log::info($body['user_id'] . " clicked fail notifiction type 1 #");
                } else {
                    Log::info($body['user_id'] . " clicked fail notifiction type 2 #");
                }
                $response['status'] = 1;
                $response['message'] = trans('appmessages.default_success_msg');
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }

    /**
     * Store user feedback for app in database
     */
    public function save_user_feedback($body) {
        $response = [];
        $response['status'] = 0;
        $response['message'] = trans('appmessages.default_error_msg');
        $saveData = array();
        if (isset($body['user_id']) && $body['user_id'] > 0 ) {
            $userId = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '';
            $checkuserexist = $this->UsersRepository->checkActiveUser($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $saveData = [];
                $saveData['uf_user_id'] = (isset($body['user_id']) && $body['user_id'] != '') ? $body['user_id'] : '0';
                $saveData['uf_feedback_text'] = (isset($body['feedback_text']) && $body['feedback_text'] != '') ? $body['feedback_text'] : '';

                if ($body['feedback_text'] != '') {
                    $objUserFeedback = new UserFeedback();

                    try {
                        $result = $objUserFeedback->saveUserFeedbackDetail($saveData);
                        Log::info($body['user_id'] . " # Save user feedback for app successfully#");
                    } catch (Exception $e) {
                        Log::error($body['user_id'] . " # Error on save user feedback for app #");
                    }
                    
                    $response['status'] = 1;
                    $response['message'] = trans('appmessages.default_success_msg');
                } else {
                    $response['message'] = trans('appmessages.update_user_feedback');
                }
            } else {
                $response['status'] = 3;
                $response['message'] = trans('appmessages.invalid_userid_msg');
            }
        } else {
            $response['message'] = trans('appmessages.default_error_msg');
        }
        echo json_encode($response);
    }
}