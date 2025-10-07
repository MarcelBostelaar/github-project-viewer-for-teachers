<?php
include_once __DIR__ . "/ConfigProvider.php";
include_once __DIR__ . "/../util/UtilFuncs.php";
require_once __DIR__ . '/../util/caching/CacheRules.php';
require_once __DIR__ . '/../util/caching/CourseAPIKeyRestricted.php';
class UncachedGroupingProvider{

    public function getSectionGroupings(): AllSectionGroupings {
        global $providers;
        $unlinkedGroupings = $providers->configProvider->getRawConfig()->sectionGroupings;//discard
        $sectionData = $providers->canvasReader->fetchSections();//reuse
        $indexedSections = [];
        foreach($sectionData as $section){
            $indexedSections[$section["name"]] = $section["id"];
        }
        foreach($unlinkedGroupings->getAllSections() as $section){
            if(!isset($indexedSections[$section->name])){
                throw new Exception("Section " . $section->name . " not found in Canvas");
            }
            $section->canvasID = $indexedSections[$section->name];
            $studentsInSection = $providers->canvasReader->fetchStudentsInSection($section->canvasID);//reuse, just save the ids, use as lookup table
            foreach($studentsInSection as $student){
                $section->addStudent($student["id"], $student["name"]);
            }
        }
        return $unlinkedGroupings;
    }
}

class GroupingProvider extends UncachedGroupingProvider{
    public function getSectionGroupings(): AllSectionGroupings{
        global $providers;
        global $sharedCacheTimeout;
        //Maximally restricted to single api keys, so that each teacher only gets the sections and students they are allowed to see.
        $data = cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getSectionGroupings(), "getSectionGroupings");

        //pre-whitelist this api key for access to data for these students, to enable sharing of data between users
        foreach($data->getAllSections() as $section){
            foreach($section->getStudents() as $student){
                whitelist_current_request_for_student_id_in_course($student->id);
            }
        }
        return $data;
    }
}