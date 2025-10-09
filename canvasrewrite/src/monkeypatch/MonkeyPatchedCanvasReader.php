<?php

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

    public static function FromCanvasReader(CanvasReader $canvasReader, array $rerouteGroups = []){
        $patched = new MonkeyPatchedCanvasReader($canvasReader->apiKey, $canvasReader->baseURL, $canvasReader->courseID, $canvasReader->assignmentID);
    
        $patched->rerouteGroups = $rerouteGroups;

        return $patched;
    }
}