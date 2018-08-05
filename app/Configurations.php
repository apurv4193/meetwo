<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Configurations extends Model
{
    protected $table = 'mt_c_configuration';
    protected $fillable = ['id', 'c_key', 'c_value', 'created_at', 'updated_at', 'deleted'];

    public function getMailIdforAdmin() {
        $result = Configurations::select('c_value')
                        ->where('deleted' ,'1')
                        ->where('c_key', 'ADMIN_MAIL_ID')
                        ->get()->toArray();
        return $result;
    }
}
