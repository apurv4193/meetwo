<?php

namespace App\Http\Requests;

use Config;
use App\Http\Requests\Request;

class CMSRequest extends Request
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
                'cms_subject'         => 'required',
                'cms_slug'         => 'required',
                'cms_body'    => 'required',
                'deleted' => 'required',
                 ];
    }

    public function messages() {
        return [
            'cms_subject.required' => trans('validation.cmssubjectrequiredfield'),
            'cms_slug.required' => trans('validation.cmsslugrequiredfield'),
            'cms_body.required' => trans('validation.cmsbodyrequired'),
            'deleted.required' => trans('validation.statusrequired'),
           
        ];
    }
}
