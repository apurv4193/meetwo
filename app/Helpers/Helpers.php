<?php

namespace App\Helpers;

use DB;
use Config;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use App\Users;
use App\UserScore;
use Log;
use Storage;

Class Helpers {

    public static function status() {
        $status = array('1' => 'Active', '2' => 'In active');
        return $status;
    }

    public static function questionDifficulty() {
        $result = array('1' => 'Easy', '2' => 'Difficult');
        return $result;
    }

    public static function questionImportance() {
        $result = array('1' => '1', '2' => '2', '3' => '3');
        return $result;
    }

    public static function available() {
        $status = array('0' => 'No', '1' => 'Yes');
        return $status;
    }

    public static function getDistance($lat1, $lon1, $lat2, $lon2) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return ($miles * 1.609344);
    }

    public static function pushNotificationForAndroid($token, $userData = array(), $message) {
        $optionBuiler = new OptionsBuilder();
        $optionBuiler->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder('MeeTwo');
        $notificationBuilder->setBody($message)
                ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($userData);

        $option = $optionBuiler->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $downstreamResponse = FCM::sendTo($token, $option, NULL, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        $downstreamResponse->tokensToDelete();

        $downstreamResponse->tokensToModify();

        $downstreamResponse->tokensToRetry();
    }

    public static function pushNotificationForiPhone($token, $userData, $pathForCertificate, $message) {

        $deviceToken = $token;
        $passphrase = '123456';
        //$message = 'A push notification has been sent!';

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'certificate/MeeTwo.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 15, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);

        $body['aps'] = array(
            'alert' => $message,
            'action-loc-key' => 'View',
            'data' => $userData,
            'content-available' => 1,
            'badge' => 1,
            'sound' => 'push_notification.wav',
        );
        $payload = json_encode($body);
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($fp, $msg, strlen($msg));
        fclose($fp);



        // $payload['aps'] = array('alert' => $message,'action-loc-key' => 'View', 'data' => $userData);
        // $payload['aps']['badge'] = 1;
        // $payload['aps']['loc-args'] = '123';
        // $payload['aps']['sound'] = 'push_notification.wav';
        // $payload['aps']['content-available'] = 1;
        // $payload = json_encode($payload);
        // $deviceToken = $token;
        // $apnsHost = 'gateway.sandbox.push.apple.com'; // for development
        // //$apnsHost = 'gateway.push.apple.com'; // for production
        // $apnsPort = 2195;
        // $apnsCert = $pathForCertificate;
        // $streamContext = stream_context_create();
        // stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
        // $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $streamContext);
        // if(!$apns)
        // {
        //     exit("Failed to connect: $err $errstr" . PHP_EOL);
        // }
        // $apnsMessage = chr(0) . chr(32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        // $response = fwrite($apns, $apnsMessage);
        // if(!$response)
        // {
        //     fwrite($apns, $apnsMessage); //resending
        // }
        // fclose($apns);
    }

    public static function calculateAgeInYears($dob) {
        $interval = date_diff(date_create(), date_create($dob));
        return $interval->format("%y");
    }

    public static function createUserXmpp($username, $password, $name, $email) 
    {
        $postData = array('username' => $username, 'password' => $password, 'name' => $name, 'email' => $email);
        $jsonData = json_encode($postData);

        $curlObj = curl_init();

        curl_setopt($curlObj, CURLOPT_URL, Config::get('constant.SERVER_IP_ADDRESS') . '/plugins/restapi/v1/users');
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Authorization: ' . Config::get('constant.AUTHORIZATION_KEY')));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

        $result = curl_exec($curlObj);
        $json = json_decode($result);
        $httpCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            return 0;
        } else {
            return 1;
        }
    }
    
    public static function createUser($username, $password, $name, $email, $othersParams) 
    {        
        try 
        {
            $postData = [];
            $postData['userId'] = $othersParams['user_id'];
            $postData['applicationId'] = Config::get('constant.APPLOZIC_APPLICATION_ID');
            $postData['email'] = $othersParams['email'];
            $postData['password'] = Config::get('constant.APPLOZIC_USER_PASWORD');
            $postData['displayName'] = $othersParams['first_name'].' '.$othersParams['last_name'];            
            $jsonData = json_encode($postData);
            
            $curlObj = curl_init();
            
            curl_setopt($curlObj, CURLOPT_URL, Config::get('constant.APPLOZIC_URL').'register/client');
            curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlObj, CURLOPT_HEADER, 0);
            curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Application-Key: '.Config::get('constant.APPLOZIC_APPLICATION_ID')));
            curl_setopt($curlObj, CURLOPT_POST, 1);
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

            $result = curl_exec($curlObj);
            $httpCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
            curl_close($curlObj);
            
        //  json_decode($getJsonData, true, 512, JSON_BIGINT_AS_STRING);
            $jsonData = json_decode($result, true);
            if ($httpCode == 200 && !empty($jsonData)) 
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('appmessages.default_success_msg');
                $outputArray['data'] = $jsonData;
            } 
            else 
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('appmessages.default_error_msg');
                $outputArray['data'] = [];   
            }
            return $outputArray;
        } 
        catch (Exception $ex) 
        {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $outputArray['data'] = [];
            return $outputArray;
        }        
    }

    public static function registerUserForChat($japperId, $name, $userId) {
        $userName = $japperId . Config::get('constant.SERVER_NAME');

        $postData = array('jid' => $userName, 'nickname' => $name, 'subscriptionType' => '3');
        $jsonData = json_encode($postData);

        $curlObj = curl_init();

        curl_setopt($curlObj, CURLOPT_URL, Config::get('constant.SERVER_IP_ADDRESS') . '/plugins/restapi/v1/users/' . $userId . '/roster');
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Authorization: ' . Config::get('constant.AUTHORIZATION_KEY')));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

        $result = curl_exec($curlObj);
        $json = json_decode($result);

        $httpCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            return 0;
        } else {
            return 1;
        }
    }
    
    public static function applozicRegisterUserForChat($japperId, $name, $userId, $othersParams) 
    {
        try    
        { 
            $postData = [];
            $postData['userId'] = $othersParams['userId'];
            $postData['applicationId'] = Config::get('constant.APPLOZIC_APPLICATION_ID');
            $postData['email'] = $othersParams['u_email'];
            $postData['password'] = Config::get('constant.APPLOZIC_USER_PASWORD');
            $postData['displayName'] = $othersParams['u_firstname'].' '.$othersParams['u_lastname'];
            
            // Basic Base64Encode of "userId:deviceKey"
            $base64_encode = base64_encode($postData['userId'].':'.Config::get('constant.DEVICE_KEY'));
            $authorizationKey = 'Basic '.$base64_encode;
            $jsonData = json_encode($postData);
            
            $curlObj = curl_init();

            curl_setopt($curlObj, CURLOPT_URL, Config::get('constant.APPLOZIC_URL').'register/client');
            curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlObj, CURLOPT_HEADER, 0);
//          curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Application-Key: '.Config::get('constant.APPLOZIC_APPLICATION_ID'), 'Authorization: '.$authorizationKey, 'Device-Key: '.Config::get('constant.APPLOZIC_DEVICE_KEY')));
            curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Application-Key: '.Config::get('constant.APPLOZIC_APPLICATION_ID')));
            curl_setopt($curlObj, CURLOPT_POST, 1);
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

            $result = curl_exec($curlObj);
            $httpCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
            curl_close($curlObj);
            
//          json_decode($getJsonData, true, 512, JSON_BIGINT_AS_STRING);
            $jsonData = json_decode($result, true);    
            
            if ($httpCode == 200 && !empty($jsonData)) 
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('appmessages.default_success_msg');
                $outputArray['data'] = $jsonData;
            } 
            else 
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('appmessages.default_error_msg');
                $outputArray['data'] = [];   
            }
            return $outputArray;
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $outputArray['data'] = [];
            return $outputArray;
        }
    }

    public function applozicCommonMsgToUser($msgData) 
    {
        try    
        { 
            $postData = [];
            $postData['to'] = $msgData['otherUserId'];
            $postData['message'] = $msgData['message'];
            $postData['contentType'] = Config::get('constant.APPLOZIC_MSG_CONTENT_TYPE');
            
            // Basic Base64Encode of "userId:deviceKey"
            $base64_encode = base64_encode($postData['loginUserId'].':'.Config::get('constant.DEVICE_KEY'));
            $authorizationKey = 'Basic '.$base64_encode;
            $jsonData = json_encode($postData);
            
            $curlObj = curl_init();

            curl_setopt($curlObj, CURLOPT_URL, Config::get('constant.APPLOZIC_URL').'register/client');
            curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlObj, CURLOPT_HEADER, 0);
            curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Application-Key: '.Config::get('constant.APPLOZIC_APPLICATION_ID'), 'Authorization: '.$authorizationKey, 'Device-Key: '.Config::get('constant.APPLOZIC_DEVICE_KEY')));
            curl_setopt($curlObj, CURLOPT_POST, 1);
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

            $result = curl_exec($curlObj);
            $httpCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
            curl_close($curlObj);
            
//          json_decode($getJsonData, true, 512, JSON_BIGINT_AS_STRING);
            $jsonData = json_decode($result, true);            
            
            if ($httpCode == 404 && !empty($jsonData)) 
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('appmessages.default_error_msg');
                $outputArray['data'] = [];                
            } else {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('appmessages.default_success_msg');
                $outputArray['data'] = $jsonData;
            }
            return $outputArray;
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $outputArray['data'] = [];
            return $outputArray;
        }
    }
    
    public static function deleteUser($userName, $name) {
        $postData = array('username' => $userName);
        $jsonData = json_encode($postData);

        $curlObj = curl_init();

        curl_setopt($curlObj, CURLOPT_URL, Config::get('constant.SERVER_IP_ADDRESS') . '/plugins/restapi/v1/users/' . $userName);
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Authorization: ' . Config::get('constant.AUTHORIZATION_KEY')));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, "DELETE");

        $result = curl_exec($curlObj);
        $json = json_decode($result);

        $httpCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            return 0;
        } else {
            return 1;
        }
    }
    
    public static function applozicUserDelete($appLozicUserData) 
    {
        try    
        { 
            $postData = [];
            // $postData['userId'] = $appLozicUserData['id'];     
            
            // Basic Base64Encode of "userId:deviceKey" (@here u_applozic_id is userId and u_applozic_device_key is deviceKey)
            
            $base64_encode = base64_encode($appLozicUserData['u_applozic_id'].':'.$appLozicUserData['u_applozic_device_key']);
            $authorizationKey = 'Basic '.$base64_encode;
            $jsonData = json_encode($postData);
            
            $curlObj = curl_init();

            curl_setopt($curlObj, CURLOPT_URL, Config::get('constant.APPLOZIC_URL').'user/delete');
            curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlObj, CURLOPT_HEADER, 0);
            curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Application-Key: '.Config::get('constant.APPLOZIC_APPLICATION_ID'), 'Authorization: '.$authorizationKey, 'Device-Key: '.$appLozicUserData['u_applozic_device_key']));
            curl_setopt($curlObj, CURLOPT_POST, 1);
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

            $result = curl_exec($curlObj);
            $httpCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
            curl_close($curlObj);
            
//          json_decode($getJsonData, true, 512, JSON_BIGINT_AS_STRING);
            $jsonData = json_decode($result, true);            
            
            if ($httpCode == 200 && !empty($jsonData)) 
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('appmessages.default_success_msg');
                $outputArray['data'] = $jsonData;
            } 
            else 
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('appmessages.default_error_msg');
                $outputArray['data'] = [];   
            }
            return $outputArray;
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $outputArray['data'] = [];
            return $outputArray;
        }        
    }

    
    public static function deleteUserRoster($japperId, $name, $userId) 
    {
        $userName = $japperId . Config::get('constant.SERVER_NAME');
        $postData = array('jid' => $userName, 'nickname' => $name, 'subscriptionType' => '3');
        $jsonData = json_encode($postData);

        $curlObj = curl_init();

        curl_setopt($curlObj, CURLOPT_URL, Config::get('constant.SERVER_IP_ADDRESS') . '/plugins/restapi/v1/users/' . $userId . '/roster/' . $userName);
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Authorization: ' . Config::get('constant.AUTHORIZATION_KEY')));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, "DELETE");

        $result = curl_exec($curlObj);
        $json = json_decode($result);

        $httpCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            return 0;
        } else {
            return 1;
        }
    }
    
    public static function deleteUserRosterXmpp($japperId, $name, $userId) {
        $userName = $japperId . Config::get('constant.SERVER_NAME');
        $postData = array('jid' => $userName, 'nickname' => $name, 'subscriptionType' => '3');
        $jsonData = json_encode($postData);

        $curlObj = curl_init();

        curl_setopt($curlObj, CURLOPT_URL, Config::get('constant.SERVER_IP_ADDRESS') . '/plugins/restapi/v1/users/' . $userId . '/roster/' . $userName);
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Authorization: ' . Config::get('constant.AUTHORIZATION_KEY')));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, "DELETE");

        $result = curl_exec($curlObj);
        $json = json_decode($result);

        $httpCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Update user score when update Photo/Discription/Job/School detail
     */
    public static function updateUserScore($user_id) {
        $objUsers = new Users();
        $objUserScore = new UserScore();

        $userDetail = $objUsers->getUserDetailByUserId($user_id);
        if (!empty($userDetail)) {
            //Update score for update user discription
            if ($userDetail[0]->u_description != '' || $userDetail[0]->u_description != null) {
                $saveObj = [];
                $saveObj['us_user_id'] = $user_id;
                $saveObj['us_point_section_id'] = Config::get('constant.DISCRIPTION_ID');
                $saveObj['us_point'] = Config::get('constant.DISCRIPTION_POINT');
                $objUpdate = $objUserScore->updateUserScoreDetailById($saveObj);
            } else {
                $saveObj = [];
                $saveObj['us_user_id'] = $user_id;
                $saveObj['us_point_section_id'] = Config::get('constant.DISCRIPTION_ID');
                $saveObj['us_point'] = 0;
                $objUpdate = $objUserScore->updateUserScoreDetailById($saveObj);
            }

            //Update score for update user Job
            if ($userDetail[0]->u_current_work != '' || $userDetail[0]->u_current_work != null) {
                $saveObj = [];
                $saveObj['us_user_id'] = $user_id;
                $saveObj['us_point_section_id'] = Config::get('constant.JOB_ID');
                $saveObj['us_point'] = Config::get('constant.JOB_POINT');
                $objUpdate = $objUserScore->updateUserScoreDetailById($saveObj);
            } else {
                $saveObj = [];
                $saveObj['us_user_id'] = $user_id;
                $saveObj['us_point_section_id'] = Config::get('constant.JOB_ID');
                $saveObj['us_point'] = 0;
                $objUpdate = $objUserScore->updateUserScoreDetailById($saveObj);
            }

            //Update score for update user School detail
            if ($userDetail[0]->u_school != '' || $userDetail[0]->u_school != null) {
                $saveObj = [];
                $saveObj['us_user_id'] = $user_id;
                $saveObj['us_point_section_id'] = Config::get('constant.SCHOOL_ID');
                $saveObj['us_point'] = Config::get('constant.SCHOOL_POINT');
                $objUpdate = $objUserScore->updateUserScoreDetailById($saveObj);
            } else {
                $saveObj = [];
                $saveObj['us_user_id'] = $user_id;
                $saveObj['us_point_section_id'] = Config::get('constant.SCHOOL_ID');
                $saveObj['us_point'] = 0;
                $objUpdate = $objUserScore->updateUserScoreDetailById($saveObj);
            }

            //Update score for update user Photos
            if ($userDetail[0]->up_photo_name != '' || $userDetail[0]->up_photo_name != null) {
                $userPhotos = explode(",", $userDetail[0]->up_photo_name);
                for ($i = 0; $i < count($userPhotos); $i++) {
                    $saveObj = [];
                    $saveObj['us_user_id'] = $user_id;
                    $saveObj['us_point_section_id'] = Config::get('constant.PHOTO_ID');
                    $saveObj['us_point'] = Config::get('constant.PHOTO_POINT');
                    $saveObj['us_phone_name'] = $userPhotos[$i];
                    $objUpdate = $objUserScore->updateUserPhotoScoreDetailById($saveObj);
                }
            } else {
                $saveObj = [];
                $saveObj['us_user_id'] = $user_id;
                $saveObj['us_point_section_id'] = Config::get('constant.PHOTO_ID');
                $saveObj['us_point'] = 0;
                $objUpdate = $objUserScore->updateUserScoreDetailById($saveObj);
            }
        }

        return;
    }

    /**
     * Update user total sroce in user table
     */
    public static function updateUserTotalScoreById($user_id) {
        $objUserScore = new UserScore();
        $objUsers = new Users();

        $userScore = $objUserScore->getUserTotalScoreByUserId($user_id);
        if (!empty($userScore)) {
            $total = $userScore[0]->total;
            try {
                $updateUserScore = $objUsers->updateUserScoreByUserId($user_id, $total);
                Log::info($user_id . " # Score Updated successfully...! #");
            } catch (Exception $e) {
                Log::error($user_id . " # Score not Updated #");
            }
        }

        return;
    }

    /**
     * delete user photo score when user delete profile picture
     */
    public static function deleteUserImage($userId, $photoName) {
        $objUserScore = new UserScore();
        $userDeleteImage = $objUserScore->deleteUserImageScore($userId, $photoName);
//        return;
        return $userDeleteImage;
    }

    public static function updateExistingUserScore() {
        $objUsers = new Users();

        $userDetail = $objUsers->getAllUserDetail();

        return $userDetail;
    }
    
    public static function addFileToStorage($fileName, $folderName = "", $file, $storageName = "")
    {
        $url = "";
        if($file && $fileName != "")
        {
            $folderName = ($folderName != "") ? $folderName : "/";
            if($storageName != "" && strtolower($storageName) == "s3")
            {
                if(Storage::disk('s3')->put($folderName.$fileName, file_get_contents($file), 'public'))
                {
                    //$url = Storage::disk('s3')->url($folderName.$fileName);
                    $url = $fileName;
                }
            }
            else
            {
                if(Storage::put($folderName.$fileName, file_get_contents($file), 'public'))
                {
                    //$url = url(Storage::url($folderName.$fileName));
                    $url = $fileName;
                }
            }
        }
        return $url;
    }

    public static function deleteFileToStorage($fileName, $folderName = "", $storageName = "")
    {
        $return = false;
        if($fileName != "")
        {
            $folderName = ($folderName != "") ? $folderName : "/";

            if($storageName != "" && strtolower($storageName) == "s3")
            {
                if(Storage::disk('s3')->exists($folderName.$fileName))
                {
                    if(Storage::disk('s3')->delete($folderName.$fileName))
                    {
                        $return = true;
                    }
                }
            }
            else
            {
                if(Storage::exists($folderName.$fileName))
                {
                    if(Storage::delete($folderName.$fileName))
                    {
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }

}

?>