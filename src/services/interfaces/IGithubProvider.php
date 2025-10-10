<?php

namespace GithubProjectViewer\Services\Interfaces;

use GithubProjectViewer\Models\GithublinkSubmission\SubmissionStatus;

interface IGithubProvider {
    public function validateUrl(string $url): SubmissionStatus;
    public function getCommitHistory(string $url): array;
}