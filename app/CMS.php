<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class CMS extends Model
{
    protected $table = 'mt_cms';
    protected $fillable = ['id', 'cms_slug', 'cms_subject', 'cms_body', 'created_at', 'updated_at', 'deleted'];

    public function getCmsForHelp() {
       $cmsDetailsForHelp = CMS::Select('mt_cms.cms_body')
                ->where("mt_cms.cms_slug", "help")
                ->first();
        return $cmsDetailsForHelp;
    }

}
