<?php
require_once __DIR__ . '/../models//GithublinkSubmission/IGithublinkSubmission.php';
require_once __DIR__ . '/../models//GithublinkSubmission/ConcreteGithublinkSubmission.php';
require_once __DIR__ . '/../models//GithublinkSubmission/CombinedGithublinkSubmission.php';
require_once __DIR__ . '/../util/UtilFuncs.php';


class SubmissionProvider{

    /**
     * Gets all submissions without processing them into group submissions
     * @return ConcreteGithublinkSubmission[]
     */
    protected function getAllNormalSubmissions(): array{
        global $providers;
        $data = $providers->canvasReader->fetchSubmissions();
        // formatted_var_dump($data);
        $processed = array_map(fn($x) => new ConcreteGithublinkSubmission(
            $x["url"] ?? "",
            $x["id"],
            new Student($x["user"]["id"], $x["user"]["name"]),
            $x["group"]["id"] ? new Group($x["group"]["id"], $x["group"]["name"]) : null,
            $x["submitted_at"] ? new DateTime($x["submitted_at"]) : null
        ), $data);
        return $processed;
    }

    /**
     * Provides a list of submissions, including group submissions (one per group)
     * @return IGithublinkSubmission[]
     */
    public function getAllSubmissions(): array{
        global $providers;

        //getting all current groups
        $groups = $providers->groupProvider->getAllGroupsWithStudents();
        $studentLookup = new Lookup();
        foreach($groups as $group){
            foreach($group->students as $student){
                $studentLookup->add($student->id, $group);
            }
        }

        $submissions = $this->getAllNormalSubmissions();
        $as_is = [];

        //matching all submissions to the group based on the student who made the submission
        $toGroupLookup = new Lookup();
        foreach($submissions as $submission){
            $studentId = $submission->getStudent()->id;
            $group = $studentLookup->getItem($studentId);
            if(count($group) == 0){
                $as_is[] = $submission; //Not in any group, return as is.
            }
            else if(count($group) > 1){
                throw new Exception("Multiple groups for student found, should not be possible");
            }
            else{
                //Assuming names are unique
                $toGroupLookup->add($group[0], $submission);
            }
        }
        $groupedSubmissions = array_map(
            fn($groupvals) => new CombinedGithublinkSubmission($groupvals["key"], ...$groupvals["value"]),
            $toGroupLookup->getKeyvalueList());
        return array_merge($as_is, $groupedSubmissions);
    }

    public function getFeedbackForSubmission(int $submissionID): array{
        //TODO implement
        return [
            new SubmissionFeedback("Kim", new DateTime("2024-01-01 12:00:00"), "Good job!"),
            new SubmissionFeedback("Josh", new DateTime("2024-01-02 12:00:00"), "Please fix the bugs."),
        ];
    }

    public function submitFeedback(string $feedback, int $submissionID): void{
        //TODO implement
        throw new Exception("Not implemented");
    }
}
