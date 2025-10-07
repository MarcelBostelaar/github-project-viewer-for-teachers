<?php

enum SubmissionStatus : string{
    case MISSING = "missing";
    case NOTFOUND = "not_found";
    case VALID_URL = "valid_url";
}


interface IGithublinkSubmission{
    /**
     * @return Student[]
     */
    public function getStudents(): array;
    /**
     * @return SubmissionFeedback[]
     */
    public function getFeedback(): array;
    /**
     * @param string $feedback
     * @return void
     */
    public function submitFeedback(string $feedback): void;
    /**
     * 
     * @return CommitHistoryEntry[]
     */
    public function getCommitHistory(): array;
    public function clone(): string;
    public function getStatus(): SubmissionStatus;
    public function getSubmissionDate(): ?DateTime;
    public function getGroup(): ?Group;
}