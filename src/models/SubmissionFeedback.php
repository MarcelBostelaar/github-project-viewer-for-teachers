<?php

namespace GithubProjectViewer\Models;

class SubmissionFeedback{
    public string $feedbackGiver;
    public \DateTime $date;
    public string $comment;

    public function __construct(string $feedbackGiver, \DateTime $date, string $comment){
        $this->feedbackGiver = $feedbackGiver;
        $this->date = $date;
        $this->comment = $comment;
    }
}