<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use DB;
use Config;
use Helpers;
use Input;
use Redirect;
use Session;
use Request;
use Datatables;
use App\Configurations;
use App\Http\Requests\ConfigurationRequest;
use App\Services\Configurations\Contracts\ConfigurationsRepository;

class ConfigurationManagementController extends Controller
{
    public function __construct(ConfigurationsRepository $ConfigurationsRepository) {
        $this->middleware('auth.admin');
        $this->controller = 'ConfigurationManagementController';
        $this->objConfigurations = new Configurations();
        $this->ConfigurationsRepository = $ConfigurationsRepository;
        $this->loggedInUser = Auth::user();
    }

    public function index() {
        $controller = $this->controller;
        return view('Admin.ListingConfiguration', compact('controller'));
    }

    public function ConfigurationListingDataTable() {
        $configurationData = $this->ConfigurationsRepository->getAllConfigurationData();

        return Datatables::of($configurationData)
            ->editColumn('id', '<input type="checkbox" name="id[]" value="{{$id}}">')
            ->add_column('actions', '
                            <a href="{{ url("/admin/editConfigurationData") }}/{{$id}}"><i class="fa fa-edit"></i> &nbsp;&nbsp;</a>')
            ->make(true);
    }

    public function add() {
        $controller = $this->controller;
        $configurationDetail = [];
        return view('Admin.EditConfigurationData',compact('configurationDetail', 'controller'));
    }

    public function save(ConfigurationRequest $ConfigurationRequest) {
        $configurationDetail = [];
        $configurationDetail['id'] = e(Input::get('id'));
        $configurationDetail['c_key'] = e(Input::get('c_key'));
        $configurationDetail['c_value'] = e(Input::get('c_value'));
        $response = $this->ConfigurationsRepository->saveConfigurationDetail($configurationDetail);
        if ($response) {
            if ($configurationDetail['id'] > 0) {
                return Redirect::to("admin/configuration")->with('success',trans('labels.configurationupdatesuccess'));
            } else {
                return Redirect::to("admin/configuration")->with('success',trans('labels.configurationsuccess'));
            }
        }
    }

    public function edit($id) {
        $controller = $this->controller;
        $configurationDetail = $this->objConfigurations->find($id);
        return view('Admin.EditConfigurationData', compact('configurationDetail', 'controller'));
    }
}