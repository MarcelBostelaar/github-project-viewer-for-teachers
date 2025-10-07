<?php
class UncachedSectionsProvider{
    
    /**
     * Summary of getSectionsForStudent
     * @param int $studentId
     * @throws \Exception
     * @return string[]
     */
    public function getSectionsForStudent(int $studentId) : array{
        return $this->getStudentSectionLookup()->getStudentSections($studentId);
    }

    protected function getStudentSectionLookup(): StudentSectionLookup {
        global $providers;
        $sectionData = $providers->canvasReader->fetchSections();//reuse
        $perSection = array_map(fn($section) => [
            "name" => $section["name"],
            "students" => array_map(
                fn($student) => new Student($student["id"], $student["name"]), 
                $providers->canvasReader->fetchStudentsInSection($section["id"]))
        ], $sectionData);

        $studentLookupTable = [];
        foreach($perSection as $section){
            foreach($section["students"] as $student){
                if(!array_key_exists($student->id, $studentLookupTable)){
                    $studentLookupTable[$student->id] = [];
                }
                $studentLookupTable[$student->id][] = $section["name"];
            }
        }
        return new StudentSectionLookup($studentLookupTable);
    }
}

class SectionsProvider extends UncachedSectionsProvider{
    protected function getStudentSectionLookup(): StudentSectionLookup{
        global $sharedCacheTimeout;
        //Maximally restricted to single api keys, so that each teacher only gets the sections and students they are allowed to see.
        $data = cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getStudentSectionLookup(), "getStudentSectionLookup");
        return $data;
    }
}