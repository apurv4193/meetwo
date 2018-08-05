<?php

namespace App\Http\Requests;

use Config;
use App\Http\Requests\Request;

class UsersRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        if ($this->get('id') == 0) {
            return [
                'u_firstname'        => 'required | min : 3 | max : 30',
                'u_lastname'         => 'required | min : 3 | max : 30',
                //'u_email'            => 'required | email ',
                'u_gender'           => 'required',
                //'u_phone'            => 'required | numeric | min : 10',
                //'u_birthdate'        => 'required',
                'deleted'            => 'required',
            ];
        } else {
            return [
                'u_firstname'        => 'required | min : 3 | max : 30',
                'u_lastname'         => 'required | min : 3 | max : 30',
                //'u_email'            => 'required | email ',
                'u_gender'           => 'required',
                //'u_phone'            => 'required | numeric | min : 10',
                //'u_birthdate'        => 'required',
                'deleted'            => 'required',
            ];
        }
    }

    public function messages() {
          return [
              'u_firstname.required' => trans('validation.firstnamerequiredfield'),
              'u_lastname.required' => trans('validation.lastnamerequiredfield'),
              'u_email.required' => trans('validation.emailidrequiredfield'),
              'u_email.email' => trans('validation.validemail'),
              'u_gender.required' => trans('validation.genderrequiredfield'),
              'u_phone.required' => trans('validation.phonerequiredfield'),
              'u_phone.numeric' => trans('validation.digitsonly'),
              'u_birthdate.required' => trans('validation.birthdaterequiredfield'),
              'deleted.required' => trans('validation.statusrequired'),
          ];
    }
}
