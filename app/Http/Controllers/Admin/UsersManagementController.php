<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Users;
use App\Services\Users\Contracts\UsersRepository;
use App\Services\QuestionData\Contracts\QuestionDataRepository;
use App\Http\Requests\UsersRequest;
use App\QuestionData;
use Auth;
use DB;
use Config;
use Helpers;
use Input;
use Image;
use File;
use Mail;
use Redirect;
use Session;
use Request;
use Datatables;
use App\UserPhotos;

class UsersManagementController extends Controller
{
    public function __construct(UsersRepository $UsersRepository, QuestionDataRepository $QuestionDataRepository) {
        $this->middleware('auth.admin');
        $this->objUsersData = new Users();
        $this->UsersRepository = $UsersRepository;
        $this->controller = 'UsersManagementController';
        $this->QuestionDataRepository = $QuestionDataRepository;
        $this->loggedInUser = Auth::user();
        $this->uploadUserProfileOriginalPath = Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->uploadUserProfileThumbPath = Config::get('constant.USER_PROFILE_THUMB_IMAGE_UPLOAD_PATH');
        $this->userProfileThumbHeight = Config::get('constant.USER_PROFILE_THUMB_IMAGE_HEIGHT');
        $this->userProfileThumbWidth = Config::get('constant.USER_PROFILE_THUMB_IMAGE_WIDTH');

    }

    public function index() {
        $controller = $this->controller;
        return view('Admin.ListingUsers', compact('controller'));
    }

    public function UsersListingDataTable() {
        $usersData = $this->UsersRepository->getAllUsersData();

        return Datatables::of($usersData)
                ->editColumn('id', '<input type="checkbox" name="id[]" value="{{$id}}">')
                ->editColumn('u_gender', '@if ($u_gender == 1) <span>Male</span> @elseif($u_gender == 2) <span>Female</span> @endif')
                 // ->editColumn('created_at', function ($usersData) {
                 //       return [
                 //          'display' => e(
                 //             $usersData->created_at->format('m/d/Y')
                 //          ),
                 //          'timestamp' => $usersData->created_at->timestamp
                 //       ];
                 //    })
                ->editColumn('deleted', '@if ($deleted == 1) <i class="s_active fa fa-square"></i> @elseif($deleted == 2) <i class="s_inactive fa fa-square"></i> @endif')
                ->add_column('actions', '
                            <a href="{{ url("/admin/editUserDetail") }}/{{$id}}"><i class="fa fa-edit"></i> &nbsp;&nbsp;</a>
                            @if ($u_fb_identifier != Config::get("constant.ADMIN_USER_ID")) <a href="{{ url("/admin/deleteUserDetail") }}/{{$id}}" onclick="return confirm(&#39<?php echo trans("labels.confirmdelete"); ?>&#39;)" ><i class="i_delete fa fa-trash"></i></a> @endif
                            ')
                ->add_column('viewUserDetails', '<a href="" onClick="fetch_user_details({{$id}});" data-toggle="modal" id="#UserAllDetailModel" data-target="#UserAllDetailModel"><i class="fa fa-eye" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;
                            <a href="" onClick="fetch_question_details({{$id}});" data-toggle="modal" id="#UserAllQuestionsDetailModel" data-target="#UserAllQuestionsDetailModel"><i class="fa fa-question-circle" aria-hidden="true"></i></a>')
                //->orderColumn('created_at $1', '')
                ->orderColumn('created_at', '-created_at $1')
                ->make(true);
    }

    public function add() {
        $controller = $this->controller;
        $userDetail = [];
        return view('Admin.EditUserDetail',compact('userDetail', 'controller'));
    }

    public function edit($id) {
        $controller = $this->controller;
        $userDetail = $this->objUsersData->find($id);
        return view('Admin.EditUserDetail', compact('userDetail', 'controller'));
    }

    public function save(UsersRequest $UsersRequest) {
        $userDetail = [];
        $saveUserPhotosDetail = [];
        $id = e(input::get('id'));
        $userDetail['checkActionType']  = 3;
        $userDetail['id']   = e(input::get('id'));
        $userDetail['u_firstname'] = Input::get('u_firstname');
        $userDetail['u_lastname'] = Input::get('u_lastname');
        //$userDetail['u_email'] = e(Input::get('u_email'));
        $userDetail['u_gender'] = e(Input::get('u_gender'));
        $userDetail['u_phone'] = e(Input::get('u_phone'));
        $userDetail['u_age'] = e(Input::get('u_age'));
        //$userDetail['u_birthdate'] = date('Y-m-d', strtotime(e(input::get('u_birthdate'))));
        $userDetail['u_description'] = Input::get('u_description');
        $userDetail['u_school'] = Input::get('u_school');
        $userDetail['u_current_work'] = Input::get('u_current_work');
        //$userDetail['u_looking_for'] = e(Input::get('u_looking_for'));
        //$userDetail['u_country'] = e(Input::get('u_country'));
        //$userDetail['u_pincode'] = e(Input::get('u_pincode'));
        //$userDetail['u_location'] = e(Input::get('u_location'));
        $userDetail['u_profile_active'] = e(Input::get('u_profile_active'));
        $userDetail['u_fb_identifier'] = e(Input::get('u_fb_identifier'));

        $userDetail['deleted'] = e(input::get('deleted'));
        if ($id == 0) {
            $userDetail['u_social_provider'] = 'facebook';
            $userDetail['u_fb_identifier'] = mt_rand(10000000, 9999999999);
            $userDetail['u_fb_accesstoken'] = '';
            $userDetail['u_looking_distance'] = 200;
            $userDetail['u_looking_age_min'] = 18;
            $userDetail['u_looking_age_max'] = 50;
            $email = mt_rand(1000, 9999999);
            $userDetail['u_email'] = $email.'@meetwo.com';
            $userDetail['u_latitude'] = 0;
            $userDetail['u_longitude'] = 0;
        }

        if (Input::file()) {
            $file = Input::file('up_photo_name');
            if (!empty($file)) 
            {
                $fileName = 'user_'.time().'.'.$file->getClientOriginalExtension();
                $path1 = public_path($this->uploadUserProfileOriginalPath.$fileName);
                $path2 = public_path($this->uploadUserProfileThumbPath.$fileName);
                Image::make($file->getRealPath())->save($path1);
                Image::make($file->getRealPath())->resize($this->userProfileThumbWidth, $this->userProfileThumbHeight)->save($path2);
                
                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileName, $this->uploadUserProfileOriginalPath, $path1, "s3");
                $thumbImage = Helpers::addFileToStorage($fileName, $this->uploadUserProfileThumbPath, $path2, "s3");
                
                //Deleting Local Files
                \File::delete($this->uploadUserProfileOriginalPath . $fileName);
                \File::delete($this->uploadUserProfileThumbPath . $fileName);
                                
                $saveUserPhotosDetail['up_photo_name'] = (isset($fileName) && $fileName != '') ? $fileName : '';
            }
        }
        $userEmailExist = '';
        if (isset($userDetail['u_email']) && $userDetail['u_email'] != '') {
            $userEmailExist = $this->UsersRepository->checkActiveEmailExist($userDetail['u_email'], $userDetail['id']);
        }

        $userId = ($userEmailExist == '') ? $this->UsersRepository->saveUserDetail($userDetail, $saveUserPhotosDetail) : '0';
        $questionsDetail = $this->QuestionDataRepository->getNotAttemptedPersonalityQuestion($userId);
        shuffle($questionsDetail);
        array_splice($questionsDetail, 10);
        foreach ($questionsDetail as $key => $value) {
            $saveQuestionsData = [];
            $saveQuestionsData['qa_user_id'] = $userId;
            $saveQuestionsData['qa_question_id'] = $value->question_id;
            $saveQuestionsData['qa_option_id'] = (isset($value->options) && !empty($value->options)) ? $value->options[0]['optionId'] : '';
            $return = $this->QuestionDataRepository->saveAttemptedPersonalityQuestion($saveQuestionsData);
        }
        if ($userId != '') {
            return Redirect::to("admin/usersManagement")->with('success',trans('labels.userdetailupdatesuccess'));
        } else {
            return Redirect::to("admin/usersManagement")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function delete($id) {
        $return = $this->UsersRepository->deleteUserAllData($id);
        if ($return) {
           return Redirect::to("admin/usersManagement")->with('success', trans('labels.userdetaildeletesuccess'));
        } else {
           return Redirect::to("admin/usersManagement")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function manageMedia($id) 
    {
        $objPhoto = new UserPhotos();
        $PhotosDetail = $objPhoto->getUserPhotosDetailById($id);
        $controller = $this->controller;
        $uploadProfilePath = $this->uploadUserProfileThumbPath;
        return view('Admin.ManageUserProfileDeatil',compact('PhotosDetail', 'controller', 'uploadProfilePath','id'));

    }

    public function deleteUserRow() {
        $getDeleteId = $_REQUEST['id'];
        if (isset($getDeleteId) && !empty($getDeleteId) && $getDeleteId != 0) {
            foreach ($getDeleteId as $id) {
                $deletedQuestionData = $this->objUsersData->find($id);
                $deletedQuestionData->deleted = Config::get('constant.DELETED_FLAG');
                $response = $deletedQuestionData->save();
            }
            if ($response) {
               return Redirect::to("admin/usersManagement")->with('success', trans('labels.userdetaildeletesuccess'));
            } else {
               return Redirect::to("admin/usersManagement")->with('error', trans('labels.commonerrormessage'));
            }
        } else {
            return Redirect::to("admin/usersManagement")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function getAllUserDetails() {
        $UserId = $_REQUEST['id'];
        $getUserDetials = $this->objUsersData->getUserDetailByUserId($UserId);
        $data = [];
        if (isset($getUserDetials)) {
            $data = $getUserDetials[0];
        }
        $uploadProfilePath = $this->uploadUserProfileThumbPath;
        $uploadProfileOriginalPath = $this->uploadUserProfileOriginalPath;
        return view('Admin.UserpopupDetails',compact('data','uploadProfilePath','uploadProfileOriginalPath'));
    }

    public function getAllQuestionsDetails() {
        $UserId = $_REQUEST['id'];
        $objQuestionData = new QuestionData();
        $getQuestionsDetials = $objQuestionData->getQuestionDetailByUserId($UserId);
        $userData = $this->objUsersData->getQuestionDetailWithOtherUserByUserId($UserId);
        if (isset($userData) && !empty($userData)) {
            foreach ($userData as $key => $value) {
                $url = '';
                $image = explode(",", $value['profile_pic_url']);
                $IsProfile = explode(",", $value['is_profile_photo']);
                for ($i = 0; $i < count($image); $i++) {
                    if ($IsProfile[$i] == 1) {
                        $url = $image[$i];
                    }
                }
                if (empty($url)) {
                    $url = $image[0];
                }
                $userData[$key]['profile_pic_url'] = $url;
                unset($userData[$key]['is_profile_photo']);
            }
        }
        $data = [];
        if (isset($getQuestionsDetials) && !empty($getQuestionsDetials)) {
            $data = $getQuestionsDetials;
        }
        $uploadProfilePath = $this->uploadUserProfileThumbPath;
        return view('Admin.QuestionspopupDetails',compact('data','userData', 'uploadProfilePath'));
    }

    public function deleteUserProfilePhotoById() 
    {
        $postData = Input::all();
        if (isset($postData) && !empty($postData)) {
            $photo = [];
            $photo['id'] = $postData['id'];
            $objPhoto = new UserPhotos();
            $result = $objPhoto->deleteUserPhotosDetail($photo);
            if ($result) 
            {
                Helpers::deleteFileToStorage($postData['media_name'], $this->uploadUserProfileOriginalPath, "s3");
                Helpers::deleteFileToStorage($postData['media_name'], $this->uploadUserProfileThumbPath, "s3");
                unlink($this->uploadUserProfileOriginalPath.$postData['media_name']);
                unlink($this->uploadUserProfileThumbPath.$postData['media_name']);
            }
        }
    }

    public function saveUserPhotosDetail() 
    {
        $saveUserPhotosDetail = [];
        $saveUserPhotosDetail['id'] = '';
        $saveUserPhotosDetail['up_user_id']   = e(input::get('user_id'));
        if (Input::file()) 
        {
            $fileDetail = Input::file('up_photo_name');
            if (!empty($fileDetail)) 
            {
                foreach ($fileDetail As $key => $file )  
                {
                    if (!empty($file)) 
                    {
                        $time = time();
                        $time = $time+$key;
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
                        
                        $saveUserPhotosDetail['up_photo_name'] = (isset($fileName) && $fileName != '') ? $fileName : '';
                        $objPhoto = new UserPhotos();
                        $PhotosDetail = $objPhoto->saveUserPhotosDetail($saveUserPhotosDetail);
                    }
                }
            }
        }
        return Redirect::to("admin/editUserDetail/".$saveUserPhotosDetail['up_user_id'])->with('success', 'Profile Pic has been updated successfully');
    }

    public function setProfilePic() {
        $postData = Input::all();
        if(isset($postData) && !empty($postData))
        {
            $photo = [];
            $photo['id'] = $postData['id'];
            $photo['up_user_id'] = $postData['user_id'];
            $objPhoto = new UserPhotos();
            $result = $objPhoto->setProfilePicByUser($photo);
        }
    }
}