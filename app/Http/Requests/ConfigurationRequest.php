<?php

namespace App\Http\Requests;

use Config;
use App\Http\Requests\Request;

class ConfigurationRequest extends Request
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
                'c_key'         => "required |  unique:".Config::get('databaseconstants.TBL_MT_C_CONFIGURATION').",c_key," . $this->get('id'),
                'c_value'         => 'required',
            ];
        } else {
            return [
                'id'=> 'required',
                'c_key'         => "required |  unique:".Config::get('databaseconstants.TBL_MT_C_CONFIGURATION').",c_key," . $this->get('id'),
                'c_value'=> 'required',
            ];
        }
    }

    public function messages() {
        return [
            'c_key.required' => trans('validation.c_keyrequiredfield'),
            'c_key.unique' => trans('validation.ckeyrepeat'),
            'c_value.required' => trans('validation.c_valuerequiredfield'),
        ];
    }
}
