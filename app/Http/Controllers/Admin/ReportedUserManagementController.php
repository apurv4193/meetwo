<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Input;
use Config;
use Helpers;
use Redirect;
use App\UserProfileReport;
use Datatables;
use App\Users;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Services\Users\Contracts\UsersRepository;

class ReportedUserManagementController extends Controller
{
    public function __construct(UsersRepository $UsersRepository) {
        $this->middleware('auth.admin');
        $this->objUserProfileReport  = new UserProfileReport();
        $this->objUsersData = new Users();
        $this->UsersRepository = $UsersRepository;
        $this->controller = 'ReportedUserManagementController';
        $this->loggedInUser = Auth::user();
    }

    public function index() {
        $controller = $this->controller;
        return view('Admin.ListReportedUser', compact('controller'));
    }

    public function ReportedUserListingDataTable() {
        $userArray = $this->objUserProfileReport->getAllReportedUsers();
        $userData = [];

        foreach ($userArray AS $key => $value) {
            $userArray[$key]->reported_to = '';
            $userArray[$key]->reported_by = '';
            $userArray[$key]->reported_id = '';
        }
        foreach ($userArray AS $key => $value) {
            $userDetail = $this->objUsersData->getUserDetailByUserId($value->upr_viewed_id);
            if (!empty($userDetail)) {
                $userArray[$key]->reported_id = $userDetail[0]->id;
                if($userDetail[0]->u_firstname != '')
                {
                    $userArray[$key]->reported_to = $userDetail[0]->u_firstname . " " .$userDetail[0]->u_lastname;    
                }
                
            }
            $userDetail1 = $this->objUsersData->getUserDetailByUserId($value->upr_viewer_id);
            if (!empty($userDetail1)) {
                if($userDetail1[0]->u_firstname != '')
                {
                    $userArray[$key]->reported_by = $userDetail1[0]->u_firstname . " " .$userDetail1[0]->u_lastname;    
                }
            }
        }

        foreach ($userArray AS $k => $val) 
        {
            if($userArray[$k]->reported_by != "" && $userArray[$k]->reported_to != "")
            {
                $userData[] = $userArray[$k];    
            }
        }

        $data = collect($userData);
        return Datatables::of($data)
            ->editColumn('upr_viewed_id', '<?php  { echo $reported_to; }?>')
            ->editColumn('upr_viewer_id', '<?php  { echo $reported_by; }?>')
            ->editColumn('upr_report_reason', '@if ($upr_report_reason == 1) Inapropriate Photos @elseif($upr_report_reason == 2) Spamming or Robot @elseif($upr_report_reason == 3) Underage User @elseif($upr_report_reason == 4) Other Reason @if ($upr_report_reason_text != "") (<?php { echo $upr_report_reason_text; }?>) @endif @else Other Reason @endif')
            ->editColumn('created_at', '<?php echo date("d M Y", strtotime($created_at)); ?>')
            ->add_column('viewUserDetails', '<a href="" onClick="fetch_user_details({{$reported_id}});" data-toggle="modal" id="#UserAllDetailModel" data-target="#UserAllDetailModel"><i class="fa fa-eye" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;
                            <a href="" onClick="fetch_question_details({{$reported_id}});" data-toggle="modal" id="#UserAllQuestionsDetailModel" data-target="#UserAllQuestionsDetailModel"><i class="fa fa-question-circle" aria-hidden="true"></i></a>')
            ->make(true);
    }
}