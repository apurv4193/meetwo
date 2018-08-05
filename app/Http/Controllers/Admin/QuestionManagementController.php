<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\QuestionData;
use App\QuestionOptionData;
use App\Services\QuestionData\Contracts\QuestionDataRepository;
use App\Http\Requests\QuestionDataRequest;
use PHPExcel_Worksheet_Drawing;
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
use Excel;

class QuestionManagementController extends Controller
{
    public function __construct(QuestionDataRepository $QuestionDataRepository) {
        $this->middleware('auth.admin');
        $this->objQuestionData = new QuestionData();
        $this->objQuestionOption = new QuestionOptionData();
        $this->QuestionDataRepository = $QuestionDataRepository;
        $this->controller = 'QuestionManagementController';
        $this->loggedInUser = Auth::user();
    }

    public function index() {
       $controller = $this->controller;
       return view('Admin.ListingQuestionData', compact('controller'));
    }

    public function QuestionsListingDataTable() {
        $questionsData = $this->QuestionDataRepository->getAllQuestionData();

        $questions = $this->QuestionDataRepository->getAllAttemptedQuestionCount();

        foreach ($questionsData as $key => $value )  {
            $yes = '';
            $no = '';
            $total = 0;
            foreach ($questions AS $k => $v) {
                if ($value->id == $v['Qid']) {
                    $total = $v['total_question'];
                    if ($total != 0) {
                        $yes = ($v['yes'] * 100)/$total;
                        $no = ($v['no'] * 100)/$total;
                    }
                }
            }
            $value->total_question = $total;
            $value->yes = (isset($yes) && $yes != '') ? round($yes)  : 0;
            $value->no = (isset($no) && $no != '') ? "/". round($no) ." %": '/0 %';
            if(round($yes) > 60 || round($no > 60))
            {
                $value->q_difficulty = 1;
            }
            else
            {
                $value->q_difficulty = 2;
            }
        }

        $data = collect($questionsData);

        return Datatables::of($data)
            ->editColumn('id', '<input type="checkbox" name="id[]" value="{{$id}}">')
            ->editColumn('q_question_text', '@if ($q_question_type == 1) <img src="{{ asset("/backend/images/sexual.png")}}" width="20px" height="20px"> @endif &nbsp;&nbsp;@if ($q_fr_question_text != "") <img src="{{ asset("/backend/images/fr_flag.png")}}" width="15px" height="15px"> @endif &nbsp;&nbsp;<?php echo $q_question_text; ?>')
            ->editColumn('qo_option', '<?php $newOption = explode(",", $qo_option); foreach($newOption as $data){ echo $data."<br/>";}?>')
            ->editColumn('q_difficulty', '@if ($q_difficulty == 1) {{trans("labels.formlbleasy")}} @elseif($q_difficulty == 2) {{trans("labels.formlbldifficult")}}  @endif')

            ->editColumn('total_question', '<?php echo $total_question; ?>')
            ->addColumn('ratio', '<?php  { echo $yes.$no; }?>')
            ->editColumn('deleted', '@if ($deleted == 1) <i class="s_active fa fa-square"></i> @elseif($deleted == 2) <i class="s_inactive fa fa-square"></i> @endif')
            ->add_column('actions', '
                            <a href="{{ url("/admin/editQuestionData") }}/{{$id}}"><i class="fa fa-edit"></i> &nbsp;&nbsp;</a>
                            <a href="{{ url("/admin/deleteQuestionData") }}/{{$id}}" onclick="return confirm(&#39<?php echo trans("labels.confirmdelete"); ?>&#39;)" ><i class="i_delete fa fa-trash"></i></a>')

        ->make(true);
    }

    public function add() {
        $controller = $this->controller;
        $questionDetail = [];
        $questionOptionDetail = [];
        return view('Admin.EditQuestionData',compact('questionDetail', 'questionOptionDetail', 'controller'));
    }

    public function save(QuestionDataRequest $QuestionDataRequest) {
        $questionDetail = [];
        $questionOptionDetail = [];
        $questionFROptionDetail = [];
        $questionDetail['id']   = e(input::get('id'));
        $questionDetail['q_question_text']   = input::get('q_question_text');
        $questionDetail['q_fr_question_text']   = input::get('q_fr_question_text');
        $questionOptionDetail = input::get('qo_option');
        $questionFROptionDetail = input::get('qo_fr_option');
        $questionDetail['q_difficulty'] = e(input::get('q_difficulty'));
        $questionDetail['q_importance'] = e(input::get('q_importance'));
        $questionDetail['q_question_type'] = e(input::get('q_question_type'));
        $questionDetail['deleted'] = e(input::get('deleted'));

        $lastInsertedId = $this->QuestionDataRepository->savequestionDetail($questionDetail);
        if (isset($questionDetail['id']) && $questionDetail['id'] > 0) {
            $questionId = $lastInsertedId;
            $response = $this->QuestionDataRepository->saveQuestionOptionDetail($questionId,$questionOptionDetail,$questionFROptionDetail);
        } else {
            for ($i = 0; $i < count($questionOptionDetail); $i++) {
                $questionId = '';
                $data['qo_question_id'] = $lastInsertedId;
                $data['qo_option'] = $questionOptionDetail[$i];
                $data['qo_fr_option'] = $questionFROptionDetail[$i];
                $response = $this->QuestionDataRepository->saveQuestionOptionDetail($questionId,$data,$questionFROptionDetail);
            }
        }
        if ($lastInsertedId > 0) {
            if ($questionDetail['id'] > 0) {
                return Redirect::to("admin/question")->with('success',trans('labels.questionupdatesuccess'));
            } else{
                return Redirect::to("admin/question")->with('success',trans('labels.questionsuccess'));
            }
        } else {
           return Redirect::to("admin/question")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function edit($id) {
        $controller = $this->controller;
        $questionDetail = $this->objQuestionData->find($id);
        $questionOptionDetail = $this->objQuestionOption->where('qo_question_id', $id)->get();
        return view('Admin.EditQuestionData', compact('questionDetail', 'questionOptionDetail', 'controller'));
    }

    public function delete($id) {
        $return = $this->QuestionDataRepository->deleteQuestionData($id);
        if ($return) {
           return Redirect::to("admin/question")->with('success', trans('labels.questiondeletesuccess'));
        } else {
           return Redirect::to("admin/question")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function deleteQuestionDataRow() {
        $getDeleteId = $_REQUEST['id'];
        if (isset($getDeleteId) && !empty($getDeleteId) && $getDeleteId != 0) {
            foreach ($getDeleteId as $id) {
                $return = DB::table(Config::get('databaseconstants.TBL_MT_QO_QUESTION_OPTIONS'))
                        ->where('qo_question_id', $id)
                        ->update(['deleted' => Config::get('constant.DELETED_FLAG')]);

                $deletedQuestionData = $this->objQuestionData->find($id);
                $deletedQuestionData->deleted = Config::get('constant.DELETED_FLAG');
                $response = $deletedQuestionData->save();
            }
            if ($response && $return) {
               return Redirect::to("admin/question")->with('success', trans('labels.questiondeletesuccess'));
            } else {
               return Redirect::to("admin/question")->with('error', trans('labels.commonerrormessage'));
            }
        } else {
            return Redirect::to("admin/question")->with('error', trans('labels.commonerrormessage'));
        }
    }

    public function importExcel() {
        return view('Admin.AddQuestionsData');
    }

    // Import data for question using excel file upload
    public function addimportExcel() {
        $filename = Input::file('importfile');
        Excel::load($filename, function($reader) {

            // Getting all results
            $results = $reader->get();

            $results = $reader->all();
            $results = $results->toArray();

            foreach ($results AS $key1 => $value) {
                $questionDetail = [];
                $questionDetail['q_question_text']   = $value['question_text'];
                $questionDetail['q_fr_question_text']   = $value['question_text_french'];
                $difficuly = 0;
                if (intval($value['difficulty']) > 0) {
                    $difficuly = $value['difficulty'];
                } else {
                    if ($value['difficulty'] == 'Easy' || $value['difficulty'] == 'easy') {
                        $difficuly = 1;
                    } else if ($value['difficulty'] == 'Difficulty' || $value['difficulty'] == 'difficulty') {
                        $difficuly = 2;
                    }
                }
                $questionDetail['q_difficulty'] = $difficuly;
                $questionDetail['q_importance'] = $value['importance'];
                if($value['sexual'] == '') {
                    $questionDetail['q_question_type'] = 0;    
                }
                else {
                    $questionDetail['q_question_type'] = $value['sexual'];
                }

                $lastInsertedId = $this->QuestionDataRepository->savequestionDetail($questionDetail);
                $questionOptionDetail = [];
                $questionFROptionDetail = [];
                $questionOptionDetail[0] = strtoupper($value['option1']);
                $questionOptionDetail[1] = strtoupper($value['option2']);
                $questionFROptionDetail[0] = strtoupper($value['fr_option1']);
                $questionFROptionDetail[1] = strtoupper($value['fr_option2']);
                for ($i = 0; $i < count($questionOptionDetail); $i++) {
                    $questionId = '';
                    $data['qo_question_id'] = $lastInsertedId;
                    $data['qo_option'] = $questionOptionDetail[$i];
                    $data['qo_fr_option'] = $questionFROptionDetail[$i];
                    $response = $this->QuestionDataRepository->saveQuestionOptionDetail($questionId,$data,$questionFROptionDetail);
                }
            }
        });
        return Redirect::to("admin/question")->with('success', trans('labels.questionaddsuccess'));
    }

    // Export Question Data in Excel file
    public function ExportQuestionsData() {

         $questionsData = $this->QuestionDataRepository->getAllQuestionData();

         $allQuestion = [];
         foreach ($questionsData AS $key => $value) {
            $questionArray = [];
            $questionArray['Sexual'] = $value->q_question_type;
            $questionArray['Question Text'] = $value->q_question_text;
            $options = explode(",",$value->qo_option);
            if (!empty($options)) {
                $questionArray['Option1'] = $options[0];
                $questionArray['Option2'] = $options[1];
            }
            $questionArray['Question Text French'] = $value->q_fr_question_text;
            $questionArray['Fr Option1'] = '';
            $questionArray['Fr Option2'] = '';
            $optionsFr = explode(",",$value->qo_fr_option);

            if (!empty($optionsFr) && $optionsFr[0] != '') {
                $questionArray['Fr Option1'] = $optionsFr[0];
                $questionArray['Fr Option2'] = $optionsFr[1];
            }
            $difficulty = '';
            if ($value->q_difficulty == 1) {
                $difficulty = 'Easy';
            } else if ($value->q_difficulty == 2) {
                $difficulty = 'Difficulty';
            }
            $questionArray['Difficulty'] = $difficulty;
            $questionArray['Importance'] = $value->q_importance;
            $allQuestion[] = $questionArray;
         }

         Excel::create('QuestionData', function($excel) use($allQuestion,$questionsData) {
            $excel->sheet('QuestionData', function($sheet) use($allQuestion,$questionsData) {

                // foreach ($questionsData as $key => $value) 
                // {
                //     if($value->q_question_type == 1)
                //     {
                //         $objDrawing = new PHPExcel_Worksheet_Drawing;
                //         $objDrawing->setPath(public_path('/backend/images/sexual.png')); //your image path
                //         $objDrawing->setCoordinates('A'.($key+2));
                //         $objDrawing->setWorksheet($sheet);
                //     }

                //     if($value->q_fr_question_text != '')
                //     {
                //         $objDraw = new PHPExcel_Worksheet_Drawing;
                //         $objDraw->setPath(public_path('/backend/images/frFlag.png')); //your image path
                //         $objDraw->setCoordinates('B'.($key+2));
                //         $objDraw->setWorksheet($sheet);
                //     } 
                // }
                $sheet->fromArray($allQuestion);
            });
        })->export('xlsx');
    }
}