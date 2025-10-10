<?php

namespace GithubProjectViewer\Services\Interfaces;

interface ICanvasReader {
    public function getApiKey();
    public function getCourseURL();
    public function getBaseURL();
    public function getAssignmentID();
    public function getCourseID();
    public function fetchSections();
    public function fetchStudentsInSection($sectionID);
    public function fetchSubmissions();
    public function fetchGroupUsers(int $groupID);
    public function fetchAllGroupsInSet($groupSetID);
    public function fetchAssignmentDetails();
    public function putCommentToSubmission(int $userID, string $commentText);
    public function fetchSubmissionComments(int $userID);
}