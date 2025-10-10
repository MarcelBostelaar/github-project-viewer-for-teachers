<?php
namespace GithubProjectViewer\Services;
use GithubProjectViewer\Services\Interfaces\ISectionsProvider;
use GithubProjectViewer\Util\Lookup;
use GithubProjectViewer\Models as Models;

class UncachedSectionsProvider implements ISectionsProvider{
    
    /**
     * Summary of getSectionsForStudent
     * @param int $studentId
     * @throws \Exception
     * @return string[]
     */
    public function getSectionsForStudent(int $studentId) : array{
        return $this->getStudentSectionLookup()->getItem($studentId);
    }

    protected function getStudentSectionLookup(): Lookup {
        global $providers;
        $sectionData = $providers->canvasReader->fetchSections();//reuse
        $perSection = array_map(fn($section) => [
            "name" => $section["name"],
            "students" => array_map(
                fn($student) => new Models\Student($student["id"], $student["name"]), 
                $providers->canvasReader->fetchStudentsInSection($section["id"]))
        ], $sectionData);

        $studentLookupTable = new Lookup();
        foreach($perSection as $section){
            foreach($section["students"] as $student){
                $studentLookupTable->add($student->id, $section["name"]);
            }
        }
        return $studentLookupTable;
    }
}

class SectionsProvider extends UncachedSectionsProvider{
    protected function getStudentSectionLookup(): Lookup{
        global $veryLongTimeout;
        //Maximally restricted to single api keys, so that each teacher only gets the sections and students they are allowed to see.
        $data = cached_call(new \CourseAPIKeyRestricted(), $veryLongTimeout,
        fn() => parent::getStudentSectionLookup(), "SectionProvider", "getStudentSectionLookup");
        return $data;
    }
}