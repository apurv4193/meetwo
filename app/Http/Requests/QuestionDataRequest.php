<?php

namespace App\Http\Requests;

use Config;
use App\Http\Requests\Request;

class QuestionDataRequest extends Request
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
                'q_question_text'   => 'required',
                'qo_option'         => 'required',
                'deleted'           => 'required',
            ];
        } else {
            return [
                'q_question_text'    => 'required',
                'qo_option'          => 'required',
                'deleted'            => 'required',
            ];
        }
    }

    public function messages() {
        return [
            'q_question_text.required' => trans('validation.questiontextrequiredfield'),
            'qo_option.required' => trans('validation.questionoptionrequiredfield'),
            'deleted.required' => trans('validation.statusrequired'),
        ];
    }
}
