<?php
require_once __DIR__ . "/CacheRules.php";
require_once __DIR__ . "/MaximumAPIKeyRestrictions.php";
class CourseAPIKeyRestricted extends MaximumAPIKeyRestrictions{
    public function serializeItem($item): string{
        if($item instanceof CanvasReader){
            return "CanvasReader" . $item->getBaseURL() . $item->getCourseURL();
        }
        return parent::serializeItem($item);
    }

    protected function serializeCanvasReader(){
        //Override to not include API key in the key, only base URL and course URL.
        global $providers;
        $canvasReader = $providers->canvasReader;
        return "CanvasReader" . $canvasReader->getBaseURL() . $canvasReader->getCourseURL();
    }
}