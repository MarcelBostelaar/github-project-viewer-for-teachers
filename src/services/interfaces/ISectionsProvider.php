<?php

namespace GithubProjectViewer\Services\Interfaces;

interface ISectionsProvider {
    public function getSectionsForStudent(int $studentId): array;
}