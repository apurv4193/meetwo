<?php

namespace App\Http\Requests;

use Config;
use App\Http\Requests\Request;

class TemplateRequest extends Request
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
    public function rules()
    {

            return [
                'et_templatename'         => 'required',
                'et_templatepseudoname'         => 'required',
                'et_subject'     => 'required',
                'et_body'    => 'required',
                'deleted' => 'required',
                 ];
    }

    public function messages() {
        return [
            'et_templatename.required' => trans('validation.templatenamerequiredfield'),
            'et_tempatepseudoname.required' => trans('validation.templatepseudonamerequiredfield'),
            'et_subject.required' => trans('validation.templatesubjectrequiredfield'),
            'et_body.required' => trans('validation.templatebobyrequiredfield'),
            'deleted.required' => trans('validation.templatestatusrequiredfield'),
           
        ];
    }
}
