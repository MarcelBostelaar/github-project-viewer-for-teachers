<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../views/Overview.php';
class OverviewController extends BaseController {
    public function route() {
        switch($_GET['action'] ?? 'index') {
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
        }
    }
    public function index(){
        global $providers;
        $AllSubmissions = $providers->submissionProvider->getAllSubmissions();
        RenderOverview($AllSubmissions, "./OverviewController.php");
    }

    private function getSubmissionFromRequest(){
        global $providers;
        $groupID = $_GET['groupid'] ?? null;
        $userID = $_GET['userid'] ?? null;
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