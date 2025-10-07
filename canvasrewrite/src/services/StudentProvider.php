<?php
require_once __DIR__ . '/../util/caching/StudentIDRightsAPIKeyRestricted.php';
require_once __DIR__ . '/../util/caching/Caching.php';
require_once __DIR__ . '/../util/caching/CacheRules.php';
require_once __DIR__ . '/CanvasReader.php';

class UncachedStudentProvider{

    public function getByID(int $studentID) : Student{
        throw new Exception("Not implemented");
        // global $providers;
        // return $providers->groupingProvider
        //                 ->getSectionGroupings()
        //                 ->getStudent($studentID);
    }
}

//Caching is done here
class StudentProvider extends UncachedStudentProvider{
    public function getByID($studentID): Student{
        global $studentDataCacheTimeout;
        return cached_call(new StudentIDRightsAPIKeyRestricted($studentID), $studentDataCacheTimeout,
        fn() => parent::getByID($studentID),
         "getByID", $studentID);
    }
}