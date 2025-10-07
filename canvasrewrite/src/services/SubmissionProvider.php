<?php
require_once __DIR__ . '/../models/GithublinkSubmission.php';
require_once __DIR__ . '/../util/UtilFuncs.php';

class SubmissionProvider{

    /**
     * Summary of getAllSubmissions
     * @return GithublinkSubmission[]
     */
    public function getAllSubmissions(): array{
        global $providers;
        $data = $providers->canvasReader->fetchSubmissions();
        // formatted_var_dump($data);
        $processed = array_map(fn($x) => new GithublinkSubmission(
            $x["url"] ?? "",
            $x["id"],
            new Student($x["user"]["id"], $x["user"]["name"]),
            $x["group"]["id"] ?? null,
            $x["submitted_at"] ? new DateTime($x["submitted_at"]) : null
        ), $data);
        $noGroup = array_filter($processed, fn($x) => $x->getGroupID() == null);
        $group = array_filter($processed, fn($x) => $x->getGroupID() != null);

        //filter to unique groups only, so each group submission is only shown once
        $group = array_unique_predicate(fn($x) => $x->getGroupID(), $group);

        return array_merge($noGroup, $group);
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
