<?php
namespace GithubProjectViewer\Controllers\Api;
class CloneController extends APIController{
    protected $debug_keep_output = false;
    
    public function handle(){
        echo "start clone endpoint";
        $submission = $this->getSubmissionFromRequest(false);
        if(!$submission->getStatus() == SubmissionStatus::VALID_URL){
            echo "Not a valid URL";
            http_response_code(401);
            return ["error" => "Submission is not in a state that allows cloning. Current status: " . $submission->getStatus()->value];
        }
        echo "trying to clone";
        $response = $submission->clone();
        return $response;
    }
}

$x = new CloneController();
$x->index();