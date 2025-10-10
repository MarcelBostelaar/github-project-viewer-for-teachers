<?php

interface IGroupProvider {
    public function getStudentsInGroup(int $groupID): array;
    public function getAllGroupsWithStudents(): array;
    public function getStudentGroupLookup(): Lookup;
}