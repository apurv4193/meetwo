<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Input;
use Config;
use Helpers;
use Redirect;
use Datatables;
use App\EmailTemplates;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\TemplateRequest;
use App\Services\EmailTemplate\Contracts\EmailTemplatesRepository;

class EmailTemplateManagementController extends Controller
{
    public function __construct(EmailTemplatesRepository $TemplatesRepository) {
        $this->middleware('auth.admin');
        $this->objTemplate                = new EmailTemplates();
        $this->TemplatesRepository         = $TemplatesRepository;
        $this->controller = 'EmailTemplateManagementController';
        $this->loggedInUser = Auth::user();
    }

    public function index() {
        return view('Admin.ListEmailTemplate')->with('controller',$this->controller);
    }

    public function  getdata() {

        $data = $this->TemplatesRepository->getAllTemplates();

        return Datatables::of($data)
                ->editColumn('id', '<input type="checkbox" name="id[]" value="{{$id}}">')
                ->editColumn('deleted', '@if ($deleted == 1) <i class="s_active fa fa-square"></i> @elseif($deleted == 2) <i class="s_inactive fa fa-square"></i> @endif')
                ->add_column('actions', '
                            <a href="{{ url("/admin/editemailtemplate") }}/{{$id}}"><i class="fa fa-edit"></i> &nbsp;&nbsp;</a>
                            <a href="{{ url("/admin/deleteemailtemplate") }}/{{$id}}" onclick="return confirm(&#39<?php echo trans("labels.confirmdelete"); ?>&#39;)" ><i class="i_delete fa fa-trash"></i></a>')
                ->make(true);

    }
    public function add() {
        $templateDetail =[];
        $controller = $this->controller;
        return view('Admin.EditTemplate', compact('templateDetail', 'controller'));
    }

    public function edit($id) {
        $controller = $this->controller;
        $templateDetail = $this->objTemplate->find($id);
        return view('Admin.EditTemplate', compact('templateDetail', 'controller'));
    }

    public function save(TemplateRequest $TemplateRequest) {
        $templateDetail = [];

        $templateDetail['id']  = e(input::get('id'));
        $templateDetail['et_templatename']   = e(input::get('et_templatename'));
        $templateDetail['et_templatepseudoname']   = e(input::get('et_templatepseudoname'));
        $templateDetail['et_subject']  = input::get('et_subject');
        $templateDetail['et_body']  = input::get('et_body');
        $templateDetail['deleted']  = e(input::get('deleted'));

        $response = $this->TemplatesRepository->saveTemplateDetail($templateDetail);
        if ($response) {
            if ($templateDetail['id'] > 0) {
                return Redirect::to("admin/emailtemplates")->with('success',trans('labels.templateupdatesuccess'));
            } else {
                return Redirect::to("admin/emailtemplates")->with('success',trans('labels.templatesuccess'));
            }

        } else {
            return Redirect::to("admin/emailtemplates")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function delete($id) {
        $return = $this->TemplatesRepository->deleteTemplate($id);
        if ($return) {
             return Redirect::to("admin/emailtemplates")->with('success', trans('labels.templatedeletesuccess'));
        } else {
           return Redirect::to("admin/emailtemplates")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function deleteEmailTemplateRow() {
        $getDeleteId = $_REQUEST['id'];
        if (isset($getDeleteId) && !empty($getDeleteId) && $getDeleteId != 0) {
            foreach ($getDeleteId as $id) {
                $deletedQuestionData = $this->objTemplate->find($id);
                $deletedQuestionData->deleted = Config::get('constant.DELETED_FLAG');
                $response = $deletedQuestionData->save();
            }
            if ($response) {
               return Redirect::to("admin/emailtemplates")->with('success', trans('labels.templatedeletesuccess'));
            } else {
               return Redirect::to("admin/emailtemplates")->with('error', trans('labels.commonerrormessage'));
            }
        } else {
            return Redirect::to("admin/emailtemplates")->with('error', trans('labels.commonerrormessage'));
        }
    }
}