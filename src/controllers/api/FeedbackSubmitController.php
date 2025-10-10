<?php
namespace GithubProjectViewer\Controllers\Api;

class FeedbackSubmitController extends APIController {
    protected $debug_keep_output = true;
    public function handle() {
        $submission = $this->getSubmissionFromRequest(false);
        $feedback = $_POST['feedback'];
        $submission->submitFeedback($feedback);
        // global $providers;
        // echo "Captured comments for debugging:<br>";
        // formatted_var_dump($providers->submissionProvider->captured);
        return "";
    }
}

$x = new FeedbackSubmitController();
$x->index();