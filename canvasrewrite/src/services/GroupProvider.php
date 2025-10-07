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

    /**
     * Summary of getGroups
     * @return int[]
     */
    public function getAllGroups() : array{
        global $providers;
        $assignmentDetails = $providers->canvasReader->fetchAssignmentDetails();
        if(!$assignmentDetails["group_category_id"]){
            throw new Exception("This assignment does not use groups!");
        }
        $groupSetID = $assignmentDetails["group_category_id"];
        $data = $providers->canvasReader->fetchAllGroupsInSet($groupSetID);
        return array_map(fn($x) => $x["id"], $data);
    }
}