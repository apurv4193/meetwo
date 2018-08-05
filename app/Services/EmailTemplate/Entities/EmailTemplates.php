<?php

namespace App\Services\EmailTemplate\Entities;
use Illuminate\Database\Eloquent\Model;

class EmailTemplates extends Model
{

    protected $table = 'mt_et_email_templates';
    protected $fillable = ['id', 'et_templatename', 'et_templatepseudoname', 'et_subject', 'et_body', 'deleted'];

}
