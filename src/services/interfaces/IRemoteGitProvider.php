<?php

interface IGithubProvider {
    public function validateUrl(string $url): SubmissionStatus;
    public function getCommitHistory(string $url): array;
}