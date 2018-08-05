<?php

namespace App\Services\Configurations\Contracts;
use App\Services\Repositories\BaseRepository;
use App\Services\Configurations\Entities\Configurations;

interface ConfigurationsRepository extends BaseRepository
{
    /**
     * Save Configuartion detail passed in $configurationDetail array
    */
    public function saveConfigurationDetail($configurationDetail);

    public function getAllConfigurationData();

}
