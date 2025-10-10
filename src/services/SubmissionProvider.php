<?php

namespace GithubProjectViewer\Services;
use GithubProjectViewer\Services\Interfaces\ISubmissionProvider;
use GithubProjectViewer\Models\GithublinkSubmission\ConcreteGithublinkSubmission;
use GithubProjectViewer\Models\GithublinkSubmission\CombinedGithublinkSubmission;
use GithubProjectViewer\Models\GithublinkSubmission\IGithublinkSubmission;
use GithubProjectViewer\Models as Models;
use GithubProjectViewer\Util\Lookup;


class UncachedSubmissionProvider implements ISubmissionProvider{

    /**
     * Gets all submissions without processing them into group submissions
     * @return ConcreteGithublinkSubmission[]
     */
    protected function getAllUnprocessedSubmissions(): array{
        global $providers;
        $data = $providers->canvasReader->fetchSubmissions();
        $processed = array_map(fn($x) => new ConcreteGithublinkSubmission(
            $x["url"] ?? "",
            $x["id"],
            new Models\Student($x["user"]["id"], $x["user"]["name"]),
            $x["submitted_at"] ? new \DateTime($x["submitted_at"]) : null
        ), $data);
        return $processed;
    }

    protected function getAllUngroupedSubmissions(): array{
        global $providers;
        $studentLookup = $providers->groupProvider->getStudentGroupLookup();
        $submissions = $this->getAllUnprocessedSubmissions();
        $as_is = [];

        foreach($submissions as $submission){
            $student = $submission->getStudent();
            $group = $studentLookup->getItem($student);
            if(count($group) == 0){
                $as_is[] = $submission; //Not in any group, return as is.
            }
        }
        return $as_is;
    }

    protected function getSubmissionsGroupLookup(): Lookup{
        global $providers;
        $submissions = $this->getAllUnprocessedSubmissions();
        $studentLookup = $providers->groupProvider->getStudentGroupLookup();
        $toGroupLookup = new Lookup();
        foreach($submissions as $submission){
            $student = $submission->getStudent();
            $group = $studentLookup->getItem($student);
            if(count($group) == 0){
                //Not in any group
            }
            else if(count($group) > 1){
                throw new \Exception("Multiple groups for student found, should not be possible");
            }
            else{
                $toGroupLookup->add($group[0], $submission);
            }
        }
        return $toGroupLookup;
    }

    protected function getGroupedSubmissions(): array{
        $toGroupLookup = $this->getSubmissionsGroupLookup();
        return array_map(
            fn($groupvals) => new CombinedGithublinkSubmission($groupvals["key"], ...$groupvals["value"]),
            $toGroupLookup->getKeyvalueList());
    }

    /**
     * Provides a list of submissions, including group submissions (one per group)
     * @return IGithublinkSubmission[]
     */
    public function getAllSubmissions(): array{
        $without_groups = $this->getAllUngroupedSubmissions();
        $in_groups = $this->getGroupedSubmissions();
        return array_merge($without_groups, $in_groups);
    }

    public function getSubmissionForGroupID(int $groupID): IGithublinkSubmission | null{
        $all = $this->getAllSubmissions();
        foreach($all as $submission){
            if($submission->getGroup() !== null && $submission->getGroup()->id == $groupID){
                return $submission;
            }
        }
        return null;
    }

    public function getSubmissionForUserID(int $userID): ConcreteGithublinkSubmission | null{
        $all = $this->getAllSubmissions();
        foreach($all as $submission){
            if(array_any($submission->getStudents(), fn($x) => $x->id == $userID)){
                if($submission instanceof ConcreteGithublinkSubmission){
                    return $submission;
                }
            }
        }
        return null;
    }

    public function getFeedbackForSubmission(int $userID): array{
        global $providers;
        $data = $providers->canvasReader->fetchSubmissionComments($userID);
        $comments = $data["submission_comments"];
        return array_map(fn($x) => new Models\SubmissionFeedback(
            $x["author_name"],
            new DateTime($x["created_at"]),
            $x["comment"]
        ), $comments);
    }

    /**
     * Summary of submitFeedback
     * @param string $feedback
     * @param int $submissionID A submission id for an existing individual submission, not a group submission id
     * @throws \Exception
     * @return never
     */
    public function submitFeedback(string $feedback, ConcreteGithublinkSubmission $submission): void{
        global $providers;
        $providers->canvasReader->putCommentToSubmission($submission->getStudent()->id, $feedback);
    }
}

class SubmissionProvider extends UncachedSubmissionProvider{

    protected function getAllUnprocessedSubmissions(): array{
        global $veryLongTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getAllUnprocessedSubmissions(),
        "SubmissionProvider - getAllNormalSubmissions");
    }
    protected function getAllUngroupedSubmissions(): array{
        global $veryLongTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getAllUngroupedSubmissions(),
        "SubmissionProvider - getAllUngroupedSubmissions");
    }
    protected function getSubmissionsGroupLookup(): Lookup{
        global $veryLongTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getSubmissionsGroupLookup(),
        "SubmissionProvider - getSubmissionsGroupLookup");
    }
    protected function getGroupedSubmissions(): array{
        global $veryLongTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getGroupedSubmissions(),
        "SubmissionProvider - getGroupedSubmissions");
    }
    public function getAllSubmissions(): array{
        global $veryLongTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getAllSubmissions(),
        "SubmissionProvider - getAllSubmissions");
    }
    public function getSubmissionForGroupID(int $groupID): IGithublinkSubmission | null{
        global $veryLongTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getSubmissionForGroupID($groupID),
        "SubmissionProvider - getSubmissionForGroupID", $groupID);
    }
    public function getSubmissionForUserID(int $userID): ConcreteGithublinkSubmission | null{
        global $veryLongTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $veryLongTimeout,
        fn() => parent::getSubmissionForUserID($userID),
        "SubmissionProvider - getSubmissionForUserID", $userID);
    }

    public function getFeedbackForSubmission(int $userID): array{
        global $veryLongTimeout;
        return cached_call(
            //Set metadata so we can invalidate the submission feedback cache when we post new feedback
            new SetMetadata( new MaximumAPIKeyRestrictions(), ["comment_userID" => $userID]), 
            $veryLongTimeout,
        fn() => parent::getFeedbackForSubmission($userID),
        "SubmissionProvider - getFeedbackForSubmission", $userID);
    }

    public function submitFeedback(string $feedback, ConcreteGithublinkSubmission $submission): void{
        parent::submitFeedback($feedback, $submission);
        //Invalidate cache for feedback for this submission
        clearCacheForMetadata(fn($data) => isset($data["comment_userID"]) && $data["comment_userID"] == $submission->getStudent()->id);
    }
}