<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Input;
use Config;
use Helpers;
use Redirect;
use App\CMS;
use Datatables;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\CMSRequest;
use App\Services\CMS\Contracts\CMSRepository;

class CMSManagementController extends Controller
{
    public function __construct(CMSRepository $CMSRepository) {
        $this->middleware('auth.admin');
        $this->objCMS                = new CMS();
        $this->CMSRepository         = $CMSRepository;
        $this->controller = 'CMSManagementController';
        $this->loggedInUser = Auth::user();
    }

    public function index() {
        $controller = $this->controller;
        return view('Admin.ListCMS', compact('controller'));
    }

    public function CMSListingDataTable() {
        $cmsData = $this->CMSRepository->getAllCMS();

        return Datatables::of($cmsData)
            ->editColumn('id', '<input type="checkbox" name="id[]" value="{{$id}}">')
            ->editColumn('deleted', '@if ($deleted == 1) <i class="s_active fa fa-square"></i> @elseif($deleted == 2) <i class="s_inactive fa fa-square"></i> @endif')
            ->add_column('actions', '
                            <a href="{{ url("/admin/editcms") }}/{{$id}}"><i class="fa fa-edit"></i> &nbsp;&nbsp;</a>
                            <a href="{{ url("/admin/deletecms") }}/{{$id}}" onclick="return confirm(&#39<?php echo trans("labels.confirmdelete"); ?>&#39;)" ><i class="i_delete fa fa-trash"></i></a>')
            ->make(true);
    }

    public function add() {
        $controller = $this->controller;
        $cmsDetail = [];
        return view('Admin.EditCMS',compact('cmsDetail', 'controller'));
    }

    public function edit($id) {
        $cmsDetail = $this->objCMS->find($id);
        $controller = $this->controller;
        return view('Admin.EditCMS', compact('cmsDetail', 'controller'));
    }

    public function save(CMSRequest $CMSRequest) {
        $cmsDetail = [];

        $cmsDetail['id']  = e(input::get('id'));
        $cmsDetail['cms_subject']   = e(input::get('cms_subject'));
        $cmsDetail['cms_slug']   = e(input::get('cms_slug'));
        $cmsDetail['cms_body']  = input::get('cms_body');
        $cmsDetail['deleted']  = e(input::get('deleted'));

        $response = $this->CMSRepository->saveCMSDetail($cmsDetail);
        if ($response) {
            if ($cmsDetail['id'] > 0) {
                return Redirect::to("admin/cms")->with('success',trans('labels.cmsupdatesuccess'));
            } else {
                return Redirect::to("admin/cms")->with('success',trans('labels.cmssuccess'));
            }
        } else {
            return Redirect::to("admin/cms")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function delete($id) {
        $return = $this->CMSRepository->deleteCMS($id);
        if ($return) {
            return Redirect::to("admin/cms")->with('success', trans('labels.cmsdeletesuccess'));
        } else {
            return Redirect::to("admin/cms")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function deleteCMSRow() {
        $getDeleteId = $_REQUEST['id'];
        if (isset($getDeleteId) && !empty($getDeleteId) && $getDeleteId != 0) {
            foreach ($getDeleteId as $id) {
                $deletedQuestionData = $this->objCMS->find($id);
                $deletedQuestionData->deleted = Config::get('constant.DELETED_FLAG');
                $response = $deletedQuestionData->save();
            }
            if ($response) {
               return Redirect::to("admin/cms")->with('success', trans('labels.cmsdeletesuccess'));
            } else {
               return Redirect::to("admin/cms")->with('error', trans('labels.commonerrormessage'));
            }
        } else {
            return Redirect::to("admin/cms")->with('error', trans('labels.commonerrormessage'));
        }
    }
}