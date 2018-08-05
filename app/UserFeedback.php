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

class UserFeedback extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_user_feedback';
	protected $fillable = ['id', 'uf_user_id', 'uf_feedback_text', 'created_at', 'updated_at', 'deleted'];
	
	/**
	 * save user feedback
	 */
	public function saveUserFeedbackDetail($saveObj) {
		$feedbackData =  $this->select('*')
			->where('deleted' , '<>' , '3')
			->where('uf_user_id', '=' , $saveObj['uf_user_id'])
			->get();

		if (count($feedbackData) > 0) {
			$result = $this->where('uf_user_id', '=' , $saveObj['uf_user_id'])
				->update($saveObj);
		} else {
			$result = $this->insert($saveObj);	
		}

		return $result;
	}
}