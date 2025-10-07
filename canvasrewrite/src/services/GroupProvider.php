<?php
require_once __DIR__ . "/../models/Student.php";

class GroupProvider{
    /**
     * Summary of getGroupName
     * @param int $groupID
     * @throws \Exception
     * @return Student[]
     */
    public function getStudentsInGroup(int $groupID): array{
        global $providers;
        $data = $providers->canvasReader->fetchGroupUsers($groupID);
        return array_map(fn($x) => new Student($x["id"], $x["name"]), $data);
    }
}