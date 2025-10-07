<?php

class MonkeyPatchedCanvasReader extends CanvasReader{
    /**
     * Fix for removed groupset in assignment.
     * @param mixed $groupSetID
     * @return array
     */
    public function fetchAllGroupsInSet($groupSetID){
        if($groupSetID == 1280){
            $groupSetID = 1300;
        }
        return parent::fetchAllGroupsInSet($groupSetID);
    }

    public static function FromCanvasReader(CanvasReader $canvasReader){
        $patched = new MonkeyPatchedCanvasReader($canvasReader->apiKey, $canvasReader->baseURL, $canvasReader->courseID, $canvasReader->assignmentID);
        // formatted_var_dump($patched);
        return $patched;
    }
}