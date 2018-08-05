<?php

namespace App\Services\CMS\Entities;
use Illuminate\Database\Eloquent\Model;

class CMS extends Model
{
    protected $table = 'mt_cms';
    protected $fillable = ['id', 'cms_slug', 'cms_subject', 'cms_body', 'created_at', 'updated_at', 'deleted'];
}
