<?php

interface ISubmissionProvider {
    public function getAllSubmissions(): array;
    public function getSubmissionForGroupID(int $groupID): IGithublinkSubmission | null;
    public function getSubmissionForUserID(int $userID): ConcreteGithublinkSubmission | null;
    public function getFeedbackForSubmission(int $userID): array;
    public function submitFeedback(string $feedback, ConcreteGithublinkSubmission $submission): void;
}