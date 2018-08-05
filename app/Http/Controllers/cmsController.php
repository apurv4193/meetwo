<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Image;
use Config;
use Helpers;
use Input;
use Response;
use Mail;
use Redirect;
use Illuminate\Http\Request;
use App\CMS;


class cmsController extends Controller {

    public function __construct()
    {
        $this->cmsObj = new CMS();
    }

    public function help()
    {
        $cmsHelp = $this->cmsObj->getCmsForHelp();
        if(!empty($cmsHelp)){
            $help = $cmsHelp->toArray();
        }
        return view('help',compact('help'));
    }
}
