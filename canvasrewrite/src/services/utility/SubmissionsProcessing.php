<?php

class AssessmentStruct{
    public $score;
    public $learning_outcome_id;
    public function __construct($score, $learning_outcome_id)
    {
        $this->score = $score;
        $this->learning_outcome_id = $learning_outcome_id;
    }
}

class SubmissionStruct{
    public $assignmentName;
    public $gradedAt;
    /**
     * @var AssessmentStruct[]
     */
    public array $Assessment;

    public function __construct(string $assignmentName, int $gradedAt, array $Assessment) {
        $this->assignmentName = $assignmentName;
        $this->gradedAt = $gradedAt;
        $this->Assessment = $Assessment;
    }
}