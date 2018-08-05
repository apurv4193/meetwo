<?php

namespace App\Services\CMS\Repositories;

use DB;
use Config;
use App\Services\CMS\Contracts\CMSRepository;
use App\Services\Repositories\Eloquent\EloquentBaseRepository;

class EloquentCMSRepository extends EloquentBaseRepository
    implements CMSRepository
{
      /**
       * @return array of all the active CMS
       Parameters
       @$searchParamArray : Array of Searching and Sorting parameters
      */
      public function getAllCMS($searchParamArray = array()) {
            $cms = DB::table(Config::get('databaseconstants.TBL_MT_CMS'))
                    ->select(['*'])
                    ->whereRaw('deleted IN (1,2)');
            return $cms;
      }

     /**
     * @return CMS details object
       Parameters
       @$cmsDetail : Array of CMS detail from front
     */
     public function saveCMSDetail($cmsDetail) {
        if ($cmsDetail['id'] != '' && $cmsDetail['id'] > 0) {
            $return = $this->model->where('id', $cmsDetail['id'])->update($cmsDetail);
        } else {
            $return = $this->model->create($cmsDetail);
        }
        return $return;
    }

     /**
     * @return Boolean True/False
       Parameters
       @$id : CMS ID
     */
     public function deleteCMS($id) {
        $flag              = true;
        $cms          = $this->model->find($id);
        $cms->deleted = config::get('constant.DELETED_FLAG');
        $response          = $cms->save();
        if ($response) {
            return true;
        } else {
            return false;
        }
    }
}