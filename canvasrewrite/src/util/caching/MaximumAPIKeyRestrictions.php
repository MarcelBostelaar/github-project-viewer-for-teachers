<?php
require_once __DIR__ . "/CacheRules.php";
class MaximumAPIKeyRestrictions extends AGeneralCacheRules{
    public function getKey(...$items): string{
        //Add the current course and API key to the cache key, so that different API keys in different courses get different cache entries.
        return parent::getKey(...$items) . "|" . $this->serializeCanvasReader();
    }

    protected function serializeCanvasReader(){
        global $providers;
        $canvasReader = $providers->canvasReader;
        return "CanvasReader" . $canvasReader->getBaseURL() . $canvasReader->getCourseURL() . $canvasReader->getApiKey();
    }
    public function getValidity(): bool{
        return true; //Generated key always valid
    }
    public function signalSuccesfullyCached(){}//do nothing.
    public function getMetaData(): array {return [];}//No metadata
}