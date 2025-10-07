<?php

class StudentSectionLookup{
    private $map;
    public function __construct($map){
        $this->map = $map;
    }

    /**
     * Summary of getStudentSections
     * @param int $studentId
     * @return string[]
     */
    public function getStudentSections(int $studentId): array {
        return $this->map[$studentId] ?? [];
    }
}