<?php

namespace App\Http\Controllers;

use File;
use Image;
use Input;
use Helpers;
use Auth;
use DB;
use Config;
use Mail;
use App\Http\Controllers\Controller;
use App\Services\Users\Contracts\UsersRepository;
use App\DeviceToken;

class MessageController extends Controller {

    public function __construct(UsersRepository $UsersRepository) {
        $this->UsersRepository = $UsersRepository;
        $this->userCerfificatePath = Config::get('constant.CERTIFICATE_PATH');
    }

    public function get_offline_msg() {
         $userId = $_REQUEST['to'];
         $fromUserId = $_REQUEST['from'];
         $msg = $_REQUEST['msg'];
         if (strpos($userId, '@') !== false) {
            $userId = substr($userId, 0, strpos($userId, "@"));
         }
         if (strpos($fromUserId, '@') !== false) {
            $fromUserId = substr($fromUserId, 0, strpos($fromUserId, "@"));
         }
         if (isset($userId) && $userId > 0) {
            $checkuserexist = $this->UsersRepository->checkUserExitByJabberId($userId);
            if (isset($checkuserexist) && $checkuserexist) {
                $userDetail = $this->UsersRepository->checkUserExitByJabberId($userId);
                $fromUserDetail = $this->UsersRepository->checkUserExitByJabberId($fromUserId);
                if ($userDetail['u_newchat_notification'] == 1) {
                    $tokenDetail = $this->UsersRepository->getTokenUserId($userDetail['id']);
                    if (isset($tokenDetail) && !empty($tokenDetail)) {
                        $notificationArray = [];
                        foreach ($tokenDetail AS $k => $tokenValue) {
                            $token = $tokenValue->udt_device_token;
                            $deviceType = $tokenValue->udt_device_type;
                            $messageData = [];
                            if ($msg != '') {
                                $messageData['title'] = $fromUserDetail['u_firstname'];
                                $messageData['body'] = $msg;
                                if ($deviceType == 2) {
                                    $messageData['from'] = $fromUserDetail['id'];
                                } else if($deviceType == 1){
                                    $messageData['fromId'] = $fromUserDetail['id'];
                                    $messageData['notification_status'] = 3;
                                }
                            } else {
                                if (empty($messageData)) {
                                    $messageData = "You may have a message from ".$fromUserDetail['u_firstname'];
                                }
                            }
                            if ($deviceType == 2) {
                                $certificatePath = $this->userCerfificatePath;
                                $return = Helpers::pushNotificationForiPhone($token,$notificationArray,$certificatePath,$messageData);
                                echo 'Success';
                            } else if ($deviceType == 1) {
                                $message = "You may have a message from ".$fromUserDetail['u_firstname'];
                                $return = Helpers::pushNotificationForAndroid($token,$messageData,$message);
                                echo 'Success';
                            }
                        }
                    }
                }
            }
        }
    }
}