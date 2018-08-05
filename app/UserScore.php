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

class UserScore extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

  use Authenticatable,
      Authorizable,
      CanResetPassword;

    protected $table = 'mt_user_score';
	protected $fillable = ['id', 'us_user_id', 'us_point_section_id', 'us_point', 'us_phone_name', 'created_at', 'updated_at', 'deleted'];
	
	/**
	 * Update user score when store discription/job/school detail
	 */
	public function updateUserScoreDetailById($saveObj) {
		$scoreData =  $this->select('*')
			->where('deleted' , '<>' , '3')
			->where('us_user_id', '=' , $saveObj['us_user_id'])
			->where('us_point_section_id', '=' , $saveObj['us_point_section_id'])
			->get();

		if (count($scoreData) > 0) {
			$result = $this->where('us_user_id', '=' , $saveObj['us_user_id'])
				->where('us_point_section_id', '=' , $saveObj['us_point_section_id'])
				->update($saveObj);
		} else {
			$result = $this->insert($saveObj);	
		}

		return $result;
	}

	/**
	 * Update user profile update score
	 */
	public function updateUserPhotoScoreDetailById($saveObj) {
		$scoreData =  $this->select('*')
			->where('deleted' , '<>' , '3')
			->where('us_user_id', '=' , $saveObj['us_user_id'])
			->where('us_point_section_id', '=' , $saveObj['us_point_section_id'])
			->where('us_phone_name', '=' , $saveObj['us_phone_name'])
			->get();
		if (count($scoreData) > 0) {
			$result = $this->where('us_user_id', '=' , $saveObj['us_user_id'])
				->where('us_point_section_id', '=' , $saveObj['us_point_section_id'])
				->where('us_phone_name', '=' , $saveObj['us_phone_name'])
				->update($saveObj);
		} else {
			$result = $this->insert($saveObj);	
		}

		return $result;
	}

	/**
	 * Get total score for user
	 */
	public function getUserTotalScoreByUserId($userId) {
		$data =  $this->select(DB::raw('SUM(us_point) AS total'))
				->whereRaw('deleted IN (1,2)')
				->where('us_user_id', '=', $userId)
				->groupBy('us_user_id')
				->get();
		
		return $data;
	}

	/**
	 * delete user profile score when profile deleted by user
	 */
	public function deleteUserImageScore($userId, $photoName) {
		$result = $this->where('us_user_id', '=' , $userId)
				->where('us_phone_name', '=' , $photoName)
				->delete();
		return $result;
	}
}