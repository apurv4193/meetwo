<?php

namespace App\Services\Configurations\Repositories;

use DB;
use Auth;
use Config;
use App\Services\Configurations\Contracts\ConfigurationsRepository;
use App\Services\Repositories\Eloquent\EloquentBaseRepository;

class EloquentConfigurationsRepository extends EloquentBaseRepository implements ConfigurationsRepository {

    /**
     * @return array of all the active Configuration Data
      Parameters
    */
    public function getAllConfigurationData() {
        $configurationData = DB::table(Config::get('databaseconstants.TBL_MT_C_CONFIGURATION'))
                    ->select(['*'])
                    ->whereRaw('deleted IN (1,2)');
        return $configurationData;
    }

    /**
     * @return Configuration details object
      Parameters
      @$configurationDetail : Array of configuration detail from front
    */
    public function saveConfigurationDetail($configurationDetail) {
        if (isset($configurationDetail['id']) && $configurationDetail['id'] != '' && $configurationDetail['id'] > 0) {
            $return = $this->model->where('id', $configurationDetail['id'])->update($configurationDetail);
        } else {
            $return = $this->model->create($configurationDetail);
        }
        return $return;
    }
}