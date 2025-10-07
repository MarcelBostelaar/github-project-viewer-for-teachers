<?php
require_once __DIR__ . '/CourseAPIKeyRestricted.php';
/**
 * Used to restrict cache entries to those with rights to access data of a specific student ID. 
 */
class StudentIDRightsAPIKeyRestricted extends CourseAPIKeyRestricted{
    private $id;
    private $encounteredAPIKey = null;
    private $registerAccessOnSuccess;
    /**
     * 
     * @param mixed $studentID
     * @param mixed $registerAccessOnSuccess Set this to false to avoid registering access to further data by this student if the request resolves correctly. Use this when requesting (possible) low or non-protected data about a student.
     */
    public function __construct($studentID, $registerAccessOnSuccess=true){
        $this->id = $studentID;
        $this->registerAccessOnSuccess = $registerAccessOnSuccess;
    }

    public function getValidity(): bool{
        //Return true if this api key has been whitelisted for this student ID
        return canSeeStudentInfo($this->id);
    }

    public function signalSuccesfullyCached(){
        if($this->registerAccessOnSuccess){
            //whitelist this api key for this student ID, so that future requests for this student ID by this api key will succeed.
            whitelist_current_request_for_student_id_in_course($this->id);
        }
    }

    public function getMetaData(): array {
        //Used to be able to clear cache entries for a specific student ID
        return [
            'studentID'=>$this->id,
            'date' => new DateTime()
        ];
    }
}