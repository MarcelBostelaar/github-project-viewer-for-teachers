<?php
namespace GithubProjectViewer\Monkeypatch;
use GithubProjectViewer\Services\CanvasReader;
use GithubProjectViewer\Services\Interfaces\ICanvasReader;

class MonkeyPatchedCanvasReader extends CanvasReader{
    private array $rerouteGroups = [];
    /**
     * Fix for removed groupset in assignment.
     * @param mixed $groupSetID
     * @return array
     */
    public function fetchAllGroupsInSet($groupSetID){
        if(isset($this->rerouteGroups[$groupSetID])){
            $groupSetID = $this->rerouteGroups[$groupSetID];
        }
        return parent::fetchAllGroupsInSet($groupSetID);
    }

    public static function FromCanvasReader(ICanvasReader $canvasReader, array $rerouteGroups = []){
        $patched = new MonkeyPatchedCanvasReader($canvasReader->getApiKey(), $canvasReader->getBaseURL(), $canvasReader->getCourseID(), $canvasReader->getAssignmentID());
    
        $patched->rerouteGroups = $rerouteGroups;

        return $patched;
    }
}