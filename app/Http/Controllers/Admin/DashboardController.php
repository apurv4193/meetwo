<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use App\Services\Users\Contracts\UsersRepository;
use Carbon\Carbon;
use App\UserPersonalityMatch;
use App\UserLikeDislike;
use App\ChatMessages;
use App\MessageCount;

class DashboardController extends Controller
{
    public function __construct(UsersRepository $UsersRepository)
    {
        $this->middleware('auth.admin');
        $this->UsersRepository = $UsersRepository;
        $this->loggedInUser = Auth::user();
    }

    public function index()
    {
        $firstDayofMonth = Carbon::now()->startOfMonth()->toDateString();
        $lastDayofMonth = Carbon::now()->endOfMonth()->toDateString();
        $firstDayofPreviousMonth = Carbon::now()->startOfMonth()->subMonth()->toDateString();
        $lastDayofPreviousMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $todayDate = Carbon::today()->format('Y-m-d').'%';
        $todayData = $this->UsersRepository->getAllUserByDate($todayDate);
        $todayTotalUser= (isset($todayData) && !empty($todayData)) ? $todayData[0]->total : '0';

        $yesterdayDate = Carbon::yesterday()->format('Y-m-d').'%';
        $yesterdayData = $this->UsersRepository->getAllUserByDate($yesterdayDate);
        $yesterdayTotalUser= (isset($yesterdayData) && !empty($yesterdayData)) ? $yesterdayData[0]->total : '0';

        $thisMonthData = $this->UsersRepository->getAllUserByMonth($firstDayofMonth,$lastDayofMonth);
        $thisMonthUser= (isset($thisMonthData) && !empty($thisMonthData)) ? $thisMonthData[0]->total : '0';

        $lastMonthData = $this->UsersRepository->getAllUserByMonth($firstDayofPreviousMonth,$lastDayofPreviousMonth);
        $lastMonthUser= (isset($lastMonthData) && !empty($lastMonthData)) ? $lastMonthData[0]->total : '0';

        $totalData = $this->UsersRepository->getAllUsers();
        $TotalUser = (isset($totalData) && !empty($totalData)) ? $totalData[0]->total : '0';

        $objPersonality = new UserPersonalityMatch();
        $todayPData = $objPersonality->getAllPersonalityMatchByDate($todayDate);
        $todayTotalPersonality= (isset($todayPData) && !empty($todayPData)) ? $todayPData[0]->total : '0';

        $yesterdayPData = $objPersonality->getAllPersonalityMatchByDate($yesterdayDate);
        $yesterdayTotalPersonality= (isset($yesterdayPData) && !empty($yesterdayPData)) ? $yesterdayPData[0]->total : '0';

        $thisMonthPData = $objPersonality->getAllPersonalityMatchMonth($firstDayofMonth,$lastDayofMonth);
        $thisMonthTotalPersonality= (isset($thisMonthPData) && !empty($thisMonthPData)) ? $thisMonthPData[0]->total : '0';

        $lastMonthPData = $objPersonality->getAllPersonalityMatchMonth($firstDayofPreviousMonth,$lastDayofPreviousMonth);
        $lastMonthTotalPersonality= (isset($lastMonthPData) && !empty($lastMonthPData)) ? $lastMonthPData[0]->total : '0';

        $totalPData = $objPersonality->getAllPersonalityMatch();
        $TotalPersonality= (isset($totalPData) && !empty($totalPData)) ? $totalPData[0]->total : '0';

        $objPersonality = new UserPersonalityMatch();
        $todayPTData = $objPersonality->getAllPersonalityTestByDate($todayDate);
        $todayTotalPersonalityTest= (isset($todayPTData) && !empty($todayPTData)) ? $todayPTData[0]->total : '0';

        $yesterdayPTData = $objPersonality->getAllPersonalityTestByDate($yesterdayDate);
        $yesterdayTotalPersonalityTest= (isset($yesterdayPTData) && !empty($yesterdayPTData)) ? $yesterdayPTData[0]->total : '0';

        $thisMonthPTData = $objPersonality->getAllPersonalityTestMonth($firstDayofMonth,$lastDayofMonth);
        $thisMonthTotalPersonalityTest= (isset($thisMonthPTData) && !empty($thisMonthPTData)) ? $thisMonthPTData[0]->total : '0';

        $lastMonthPTData = $objPersonality->getAllPersonalityTestMonth($firstDayofPreviousMonth,$lastDayofPreviousMonth);
        $lastMonthTotalPersonalityTest= (isset($lastMonthPTData) && !empty($lastMonthPTData)) ? $lastMonthPTData[0]->total : '0';

        $totalPTData = $objPersonality->getAllPersonalityTest();
        $TotalPersonalityTest= (isset($totalPTData) && !empty($totalPTData)) ? $totalPTData[0]->total : '0';


        $objUserLikeDislike = new UserLikeDislike();
        $todayLDData = $objUserLikeDislike->getAllLikeDisLikeDataByDate($todayDate);
        $todayTotalLD= (isset($todayLDData) && !empty($todayLDData)) ? $todayLDData[0]->total : '0';

        $thisMonthLDData = $objUserLikeDislike->getAllLikeDisLikeDataMonth($firstDayofMonth,$lastDayofMonth);
        $thisMonthTotalLD= (isset($thisMonthLDData) && !empty($thisMonthLDData)) ? $thisMonthLDData[0]->total : '0';

        $lastMonthLDData = $objUserLikeDislike->getAllLikeDisLikeDataMonth($firstDayofPreviousMonth,$lastDayofPreviousMonth);
        $lastMonthTotalLD= (isset($lastMonthLDData) && !empty($lastMonthLDData)) ? $lastMonthLDData[0]->total : '0';

        $totalLDData = $objUserLikeDislike->getAllLikeDisLikeDataTest();
        $TotalLD= (isset($totalLDData) && !empty($totalLDData)) ? $totalLDData[0]->total : '0';

        $objChatMessages = new ChatMessages();

        $todayMessageData = $objChatMessages->getAllMessageByDate($todayDate);
        $todayTotalMessage = 0;

        if(isset($todayMessageData) && !empty($todayMessageData))
        {
            foreach ($todayMessageData as $key => $value) 
            {
                $todayTotalMessage += $todayMessageData[$key]->cm_message_count;
            }   
        }

        $thisMonthMessageData = $objChatMessages->getAllMessageByMonth($firstDayofMonth,$lastDayofMonth);
        $thisMonthMessage = 0;

        if(isset($thisMonthMessageData) && !empty($thisMonthMessageData))
        {
            foreach ($thisMonthMessageData as $key => $value) 
            {
                $thisMonthMessage += $thisMonthMessageData[$key]->cm_message_count;
            }   
        }

        $lastMonthMessageData = $objChatMessages->getAllMessageByMonth($firstDayofPreviousMonth,$lastDayofPreviousMonth);
        $lastMonthMessage = 0;

        if(isset($lastMonthMessageData) && !empty($lastMonthMessageData))
        {
            foreach ($lastMonthMessageData as $key => $value) 
            {
                $lastMonthMessage += $lastMonthMessageData[$key]->cm_message_count;
            }   
        }


        $totalMessageData = $objChatMessages->getAllMessage();
        $totalMessage = 0;

        if(isset($totalMessageData) && !empty($totalMessageData))
        {
            foreach ($totalMessageData as $key => $value) 
            {
                $totalMessage += $totalMessageData[$key]->cm_message_count;
            }   
        }

        $objMessageCount = new MessageCount();

        $messageData = $objMessageCount->getAllMessageCountData();
        
        $messageType1 = 0;
        $messageType2 = 0;
        if (!empty($messageData))
        {
            foreach ($messageData as $key => $value)
            {
                if ($value['fmc_message_type'] == 1)
                {
                    $messageType1 = $value['total'];
                } else if ($value['fmc_message_type'] == 2){
                    $messageType2 = $value['total'];
                }
            }
        }

        $respone = [];
        $response['todayUser'] = $todayTotalUser;
        $response['thisMonthUser'] = $thisMonthUser;
        $response['lastMonthUser'] = $lastMonthUser;
        $response['yesterdayUser'] = $yesterdayTotalUser;
        $response['totalUser'] = $TotalUser;
        $response['todayChemistry'] = $todayTotalPersonality;
        $response['thisMonthChemistry'] = $thisMonthTotalPersonality;
        $response['lastMonthChemistry'] = $lastMonthTotalPersonality;
        $response['yesterdayChemistry'] = $yesterdayTotalPersonality;
        $response['totalChemistry'] = $TotalPersonality;
        $response['todayPersonalityTest'] = $todayTotalPersonalityTest;
        $response['thisMonthPersonalityTest'] = $thisMonthTotalPersonalityTest;
        $response['lastMonthPersonalityTest'] = $lastMonthTotalPersonalityTest;
        $response['yesterdayPersonalityTest'] = $yesterdayTotalPersonalityTest;
        $response['totalPersonalityTest'] = $TotalPersonalityTest;
        $response['todayLD'] = $todayTotalLD;
        $response['thisMonthLD'] = $thisMonthTotalLD;
        $response['lastMonthLD'] = $lastMonthTotalLD;
        $response['totalLD'] = $TotalLD;
        $response['todayMessage'] = $todayTotalMessage;
        $response['thisMonthMessage'] = $thisMonthMessage;
        $response['lastMonthMessage'] = $lastMonthMessage;
        $response['totalMessage'] = $totalMessage;
        $response['messageType1'] = $messageType1;
        $response['messageType2'] = $messageType2;

        return view('Admin.Dashboard', compact('response'));
    }
}
