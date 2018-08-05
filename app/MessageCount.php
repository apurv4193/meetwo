<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Config;
use DB;

class MessageCount extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_failchat_message_click_count';
    protected $fillable = ['id', 'fmc_user_id', 'fmc_message_type', 'created_at', 'updated_at', 'deleted'];

    public function saveMessageCountData($messageCountData) {
        $data = $this->insert($messageCountData);
        return $data;
    }

    public function getAllMessageCountData() {
        $data =  $this->select(DB::raw('COUNT(fmc_message_type) AS total'), 'fmc_message_type')
                ->whereRaw('deleted IN (1,2)')
                ->groupBy('fmc_message_type')
                ->get();
        
        return $data;
    }
}