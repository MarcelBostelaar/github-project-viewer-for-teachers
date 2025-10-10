<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/api/FeedbackSubmitController.php';
require_once __DIR__ . '/../views/Overview.php';

class OverviewController extends BaseController {
    public function route() {
        $actionGet = $_GET['action'] ?? null;
        $actionPost = $_POST['action'] ?? null;
        switch($actionPost ) {
            case 'addfeedback':
                (new FeedbackSubmitController())->handle();
                $this->index();
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
            case 'submissionrow':
                $this->submissionRow();
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
        RenderOverview($AllSubmissions, $this->getBaseURL());
    }

    public function feedback(){
        renderFeedback($this->getSubmissionFromRequest()->getFeedback());
    }

    public function commitHistory(){
        $limit = $_GET["limit"] ?? 8;
        $submission = $this->getSubmissionFromRequest();
        renderCommitHistory($submission->getCommitHistory(), $limit, $submission->getId());
    }

    public function submissionRow(){
        global $providers;
        $submission = $this->getSubmissionFromRequest();
        RenderSubmissionRow($submission, $this->getBaseURL());
    }

    private function getBaseURL(): string {
        return "/controllers/OverviewController.php?course=$this->courseID&assignment=$this->assignmentID";
    }
}

$x = new OverviewController();
$x->route();