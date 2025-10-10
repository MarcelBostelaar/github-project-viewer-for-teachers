<?php

namespace GithubProjectViewer\Models\GithublinkSubmission;
use GithubProjectViewer\Models as Models;

interface IGithublinkSubmission{
    /**
     * @return Models\Student[]
     */
    public function getStudents(): array;
    /**
     * @return Models\SubmissionFeedback[]
     */
    public function getFeedback(): array;
    /**
     * @param string $feedback
     * @return void
     */
    public function submitFeedback(string $feedback): void;
    /**
     * 
     * @return Models\CommitHistoryEntry[]
     */
    public function getCommitHistory(): array;
    public function clone(): string;
    public function getStatus(): SubmissionStatus;
    public function getSubmissionDate(): ?\DateTime;
    public function getGroup(): ?Models\Group;

    public function getId(): string;
    public function getUrl(): ?string;
}