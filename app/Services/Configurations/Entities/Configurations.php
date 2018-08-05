<?php

namespace App\Services\Configurations\Entities;
use Illuminate\Database\Eloquent\Model;

class Configurations extends Model
{
    protected $table = 'mt_c_configuration';

    protected $fillable = ['id', 'c_key', 'c_value', 'created_at', 'updated_at', 'deleted'];

}
