<?php

require_once __DIR__ . '/APIController.php';

class FeedbackSubmitController extends APIController {
    public function handle() {
        $submission = $this->getSubmissionFromRequest(false);
        $feedback = $_POST['feedback'];
        $submission->submitFeedback($feedback);
        global $providers;
        formatted_var_dump($providers->submissionProvider->captured);
        return "";
    }
}