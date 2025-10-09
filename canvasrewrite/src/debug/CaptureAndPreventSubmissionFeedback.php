<?php

class CaptureAndPreventSubmissionFeedback extends SubmissionProvider {
    public array $captured = [];
    public function submitFeedback(string $feedback, ConcreteGithublinkSubmission $submissionID): void {
        global $providers;
        // formatted_var_dump($providers->submissionProvider->getAllUnprocessedSubmissions());
        $submissions = $providers->submissionProvider->getAllUnprocessedSubmissions();
        foreach ($submissions as $submission) {
            if($submission->getCanvasID() == $submissionID) {
                $this->captured[$submission->getStudent()->name] = [
                    "Feedback" => $feedback,
                    "groupID" => $submission->getGroup()?->id
                ];
                return;
            }
        }
        throw new Exception("Could not find submission with ID $submissionID to capture feedback for");
    }
}