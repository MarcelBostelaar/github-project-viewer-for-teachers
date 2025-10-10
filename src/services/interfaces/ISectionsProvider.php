<?php

interface ISectionsProvider {
    public function getSectionsForStudent(int $studentId): array;
}