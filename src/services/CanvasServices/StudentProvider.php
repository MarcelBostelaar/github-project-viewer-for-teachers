<?php

namespace GithubProjectViewer\Services\Canvas;
use GithubProjectViewer\Models as Models;

class StudentProvider{
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
}