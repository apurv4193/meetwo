<?php

namespace App\Services\EmailTemplate\Contracts;
use App\Services\Repositories\BaseRepository;
use App\Services\EmailTemplate\Entities\EmailTemplates;

interface EmailTemplatesRepository extends BaseRepository
{    
    /**     
     * @return array of all active Templates in the application
     */
    public function getAllTemplates();

    /**
     * Save Parent detail passed in $templateDetail array
    */
    public function saveTemplateDetail($templateDetail);

    /**
     * Delete Template by $id
    */
    public function deleteTemplate($id);

}
