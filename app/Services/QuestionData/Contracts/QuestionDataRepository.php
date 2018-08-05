<?php

namespace App\Services\QuestionData\Contracts;

use App\Services\Repositories\BaseRepository;
use App\Services\QuestionData\Entities\QuestionData;

interface QuestionDataRepository extends BaseRepository
{
    /**
     * @return array of all active QuestionData in the application
     */
    public function getAllQuestionData();

    /**
     * Save Teenager detail passed in $questionDetail array
    */
    public function saveQuestionDetail($questionDetail);

    /**
     * Delete QuestionData by $id
    */
    public function deleteQuestionData($id);

    /**
     * Save Skipped QuestionData by $saveSkippedQuestion
    */
    public function saveSkippedPersonalityQuestion($saveSkippedQuestion);

    /**
     * Get not Attempted QuestionData by $userId
    */
    public function getNotAttemptedPersonalityQuestion($userId);

    /**
     * Save Attempted QuestionData by $saveQuestion
    */
    public function saveAttemptedPersonalityQuestion($saveQuestion);

    /**
     * Get all  Attempted QuestionData by $userId
    */
    public function getAllAttemptedPersonalityQuestion($userId,$lang);
}
