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

class ChatMessages extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

    use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_cm_chat_messages';
    protected $fillable = ['id', 'cm_user_id', 'cm_message_count', 'cm_message_date', 'created_at', 'updated_at', 'deleted'];

    /**
     * @return UserDetail Object
      Parameters
      @$userId : user_id
    */

    public function storeChatMessageCountForUser($saveChatData) 
    {
        $messageData = $this->where('cm_user_id', $saveChatData['cm_user_id'])->where('cm_message_date', $saveChatData['cm_message_date'])->where('deleted', '1')->first();
        if (count($messageData) > 0) {
            $saveChatData['cm_message_count'] += $messageData->cm_message_count;
            $data = $this->where('cm_user_id', $saveChatData['cm_user_id'])->where('cm_message_date', $saveChatData['cm_message_date'])->update($saveChatData);
        } else {
            $data = $this->insert($saveChatData);
        }
        return $data;
    }

    public function getAllMessage()
    {
        $messageData = $this->where('deleted','<>', 3)->get();
        return $messageData;    
    }

    public function getAllMessageByMonth($firstDay,$lastDay) {
        $messageData = $this->whereBetween('cm_message_date', array($firstDay,$lastDay))->where('deleted','<>', 3)->get();
        return $messageData;
    }

    public function getAllMessageByDate($date) {
        $messageData = $this->where('cm_message_date', $date)->where('deleted', '<>', 3)->get();
        return $messageData;
    }
}