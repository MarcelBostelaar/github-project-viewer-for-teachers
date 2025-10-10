<?php
namespace GithubProjectViewer\Util\Caching;
class CourseAPIKeyRestricted extends MaximumAPIKeyRestrictions{
    protected function serializeCanvasReader(){
        //Override to include API key in the key but not the assignment id, so that data shared between different assignments in the same course are shared.
        global $providers;
        $canvasReader = $providers->canvasReader;
        return "CanvasReader" . $canvasReader->getBaseURL() . $canvasReader->getCourseURL() . $canvasReader->getApiKey();
    }
}