<?php
namespace GithubProjectViewer\Services;
use GithubProjectViewer\Models as Models;
use GithubProjectViewer\Util as Util;
use GithubProjectViewer\Services\Interfaces as Interfaces;

class UncachedGroupProvider implements Interfaces\IGroupProvider{
    /**
     * Summary of getGroupName
     * @param int $groupID
     * @throws \Exception
     * @return Models\Student[]
     */
    public function getStudentsInGroup(int $groupID): array{
        global $providers;
        $data = $providers->canvasReader->fetchGroupUsers($groupID);
        return array_map(fn($x) => new Models\Student($x["id"], $x["name"]), $data);
    }

    /**
     * Summary of getGroups
     * @return Models\Group[]
     */
    protected function getAllGroups() : array{
        global $providers;
        $assignmentDetails = $providers->canvasReader->fetchAssignmentDetails();
        if(!$assignmentDetails["group_category_id"]){
            throw new \Exception("This assignment does not use groups!");
        }
        $groupSetID = $assignmentDetails["group_category_id"];
        $data = $providers->canvasReader->fetchAllGroupsInSet($groupSetID);
        if(isset($data["status"]) && $data["status"] == "not found"){
            throw new \Exception("Groupset with id $groupSetID not found. Did you remove this group set?");
        }
        return array_map(fn($x) => new Models\Group($x["id"], $x["name"]), $data);
    }

    /**
     * Get all groups, including students in each group
     * @return Models\Group[]
     */
    public function getAllGroupsWithStudents(): array{
        $groups = $this->getAllGroups();
        foreach($groups as $group){
            $group->students = $this->getStudentsInGroup($group->id);
        }
        return $groups;
    }

    public function getStudentGroupLookup(): Util\Lookup{
        $groups = $this->getAllGroupsWithStudents();
        $lookup = new Util\Lookup();
        foreach($groups as $group){
            foreach($group->students as $student){
                $lookup->add($student, $group);
            }
        }
        return $lookup;
    }
}

class GroupProvider extends UncachedGroupProvider{
    public function getStudentsInGroup(int $groupID): array{
        global $veryLongTimeout;
        return cached_call(new \MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getStudentsInGroup($groupID),
        "GroupProvider - getStudentsInGroup", $groupID);
    }
    protected function getAllGroups(): array{
        global $veryLongTimeout;
        return cached_call(new \MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getAllGroups(),
        "GroupProvider - getAllGroups");
    }
    public function getAllGroupsWithStudents(): array{
        global $veryLongTimeout;
        return cached_call(new \MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getAllGroupsWithStudents(),
        "GroupProvider - getAllGroupsWithStudents");
    }

    // public function
}