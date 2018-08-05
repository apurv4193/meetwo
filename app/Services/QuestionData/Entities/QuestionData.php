<?php

namespace App\Services\QuestionData\Entities;

use Illuminate\Database\Eloquent\Model;


class QuestionData extends Model {
    protected $table = 'mt_q_questions';
    protected $fillable = ['id', 'q_question_text', 'q_difficulty', 'q_importance','created_at', 'updated_at', 'deleted'];
}
