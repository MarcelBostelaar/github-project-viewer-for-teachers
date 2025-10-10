<?php

namespace GithubProjectViewer\Services;

use Exception;
use GithubProjectViewer\Services\Interfaces\ICanvasReader;
use GithubProjectViewer\Util\curlCall;
use GithubProjectViewer\Util\putCurlCall;

class UncachedCanvasReader implements ICanvasReader{
    protected $apiKey;
    protected $courseURL;
    protected $courseID;
    protected $baseURL;
    protected $assignmentID;
    protected $assignmentURL;

    public function __construct($apiKey, $baseURL, $courseID, $assignmentID) {
        $this->apiKey = $apiKey;
        $this->courseID = $courseID;
        $this->baseURL = $baseURL;
        $this->assignmentID = $assignmentID;
        $this->courseURL = "$baseURL/courses/$courseID";
        $this->assignmentURL = "$baseURL/courses/$courseID/assignments/$assignmentID";

        if($this->apiKey == NULL || $this->baseURL == NULL || $courseID == NULL || $assignmentID == NULL){
            throw new Exception("Invalid canvas reader created!");
        }
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function getCourseURL(){
        return $this->courseURL;
    }

    public function getBaseURL(){
        return $this->baseURL;
    }

    public function getAssignmentID(){
        return $this->assignmentID;
    }

    public function getCourseID(){
        return $this->courseID;
    }

    public function fetchSections(){
        $url = "$this->courseURL/sections";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchStudentsInSection($sectionID){
        $url = "$this->baseURL/sections/$sectionID/enrollments?type[]=StudentEnrollment&per_page=100";
        $data = curlCall($url, $this->apiKey);
        $data = array_map(fn($x) => $x["user"], $data);
        return $data;
    }

    public function fetchSubmissions(){
        $url = "$this->assignmentURL/submissions?include[]=group&include[]=user&per_page=100";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchGroupUsers(int $groupID){
        $url = "$this->baseURL/groups/$groupID/users";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchAllGroupsInSet($groupSetID){
        $url = "$this->baseURL/group_categories/$groupSetID/groups";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchAssignmentDetails(){
        $url = $this->assignmentURL;
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function putCommentToSubmission(int $userID, string $commentText){
        $url = "$this->baseURL/courses/$this->courseID/assignments/$this->assignmentID/submissions/$userID";
        putCurlCall($url, $this->apiKey, "comment[text_comment]", $commentText);
    }

    public function fetchSubmissionComments(int $userID){
        $url = "$this->baseURL/courses/$this->courseID/assignments/$this->assignmentID/submissions/$userID?include[]=submission_comments";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }
}

class CanvasReader extends UncachedCanvasReader{

    /**
     * Currently no cached functions needed, as all other providers are cached. 
     * This saves unnecessary cached raw request results.
     */
}