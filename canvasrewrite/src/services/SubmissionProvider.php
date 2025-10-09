<?php
require_once __DIR__ . '/../models//GithublinkSubmission/IGithublinkSubmission.php';
require_once __DIR__ . '/../models//GithublinkSubmission/ConcreteGithublinkSubmission.php';
require_once __DIR__ . '/../models//GithublinkSubmission/CombinedGithublinkSubmission.php';
require_once __DIR__ . '/../util/UtilFuncs.php';
require_once __DIR__ . '/../util/caching/MaximumAPIKeyRestrictions.php';


class UncachedSubmissionProvider{

    /**
     * Gets all submissions without processing them into group submissions
     * @return ConcreteGithublinkSubmission[]
     */
    protected function getAllUnprocessedSubmissions(): array{
        global $providers;
        $data = $providers->canvasReader->fetchSubmissions();
        formatted_var_dump($data);
        // formatted_var_dump($data);
        $processed = array_map(fn($x) => new ConcreteGithublinkSubmission(
            $x["url"] ?? "",
            $x["id"],
            new Student($x["user"]["id"], $x["user"]["name"]),
            $x["submitted_at"] ? new DateTime($x["submitted_at"]) : null
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
                throw new Exception("Multiple groups for student found, should not be possible");
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

    public function getFeedbackForSubmission(int $submissionID): array{
        //TODO implement
        return [
            new SubmissionFeedback("Kim", new DateTime("2024-01-01 12:00:00"), "Good job!"),
            new SubmissionFeedback("Josh", new DateTime("2024-01-02 12:00:00"), "Please fix the bugs."),
        ];
    }

    /**
     * Summary of submitFeedback
     * @param string $feedback
     * @param int $submissionID A submission id for an existing individual submission, not a group submission id
     * @throws \Exception
     * @return never
     */
    public function submitFeedback(string $feedback, int $submissionID): void{
        //TODO implement
        throw new Exception("Not implemented");
    }
}

class SubmissionProvider extends UncachedSubmissionProvider{

    protected function getAllUnprocessedSubmissions(): array{
        global $sharedCacheTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getAllUnprocessedSubmissions(),
        "SubmissionProvider - getAllNormalSubmissions");
    }
    protected function getAllUngroupedSubmissions(): array{
        global $sharedCacheTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getAllUngroupedSubmissions(),
        "SubmissionProvider - getAllUngroupedSubmissions");
    }
    protected function getSubmissionsGroupLookup(): Lookup{
        global $sharedCacheTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getSubmissionsGroupLookup(),
        "SubmissionProvider - getSubmissionsGroupLookup");
    }
    protected function getGroupedSubmissions(): array{
        global $sharedCacheTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getGroupedSubmissions(),
        "SubmissionProvider - getGroupedSubmissions");
    }
    public function getAllSubmissions(): array{
        global $sharedCacheTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getAllSubmissions(),
        "SubmissionProvider - getAllSubmissions");
    }
    public function getSubmissionForGroupID(int $groupID): IGithublinkSubmission | null{
        global $sharedCacheTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getSubmissionForGroupID($groupID),
        "SubmissionProvider - getSubmissionForGroupID", $groupID);
    }
    public function getSubmissionForUserID(int $userID): ConcreteGithublinkSubmission | null{
        global $sharedCacheTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getSubmissionForUserID($userID),
        "SubmissionProvider - getSubmissionForUserID", $userID);
    }

    public function getFeedbackForSubmission(int $submissionID): array{
        global $sharedCacheTimeout;
        return cached_call(new MaximumAPIKeyRestrictions(), $sharedCacheTimeout,
        fn() => parent::getFeedbackForSubmission($submissionID),
        "SubmissionProvider - getFeedbackForSubmission", $submissionID);
    }
}