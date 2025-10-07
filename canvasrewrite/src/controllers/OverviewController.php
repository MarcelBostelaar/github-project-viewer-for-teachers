<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../views/Overview.php';
class OverviewController extends BaseController {
    public function route() {
        $actionGet = $_GET['action'] ?? null;
        $actionPost = $_POST['action'] ?? null;
        switch($actionPost ) {
            case 'addfeedback':
                $this->addFeedback();
                return;
            case null:
                break;
            default:
                http_response_code(404);
                echo "404 not found - Unknown action: " . htmlspecialchars($actionPost);
                exit();
        }
        switch($actionGet ?? 'index') {
            case 'feedback':
                $this->feedback();
                return;
            case 'commithistory':
                $this->commitHistory();
                return;
            case 'index':
                $this->index();
                return;
            default:
                http_response_code(404);
                echo "404 not found - Unknown action: " . htmlspecialchars($actionGet);
                exit();
        }
    }
    public function index(){
        global $providers;
        $AllSubmissions = $providers->submissionProvider->getAllSubmissions();
        RenderOverview($AllSubmissions);
    }

    public function addFeedback(){
        $submission = $this->getSubmissionFromRequest(false);
        $feedback = $_POST['feedback'];
        echo "Dummy addFeedback function.<br>Feedback: <pre>$feedback</pre><br> Processing feedback for students: " . htmlspecialchars(serialize($submission->getStudents()));
        $this->index();
    }

    private function getSubmissionFromRequest($fromGet = true){
        if($fromGet){
            $source = $_GET;
        } else {
            $source = $_POST;
        }
        global $providers;
        $groupID = $source['groupid'] ?? null;
        $userID = $source['userid'] ?? null;
        if($groupID !== null){
            return $providers->submissionProvider->getSubmissionForGroupID($groupID);
        }
        else if($userID !== null){
            return $providers->submissionProvider->getSubmissionForUserID($userID);
        }
        else{
            http_response_code(400);
            echo "Missing groupID or userID parameter.";
            exit();
        }
    }

    public function feedback(){
        renderFeedback($this->getSubmissionFromRequest()->getFeedback());
    }

    public function commitHistory(){
        renderCommitHistory($this->getSubmissionFromRequest()->getCommitHistory());
    }
}

$x = new OverviewController();
$x->route();