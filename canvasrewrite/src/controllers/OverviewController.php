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
        RenderOverview($AllSubmissions);
    }

    public function feedback(){
        renderFeedback($this->getSubmissionFromRequest()->getFeedback());
    }

    public function commitHistory(){
        renderCommitHistory($this->getSubmissionFromRequest()->getCommitHistory(), 8);
    }

    public function submissionRow(){
        $submission = $this->getSubmissionFromRequest();
        RenderSubmissionRow($submission);
    }
}

$x = new OverviewController();
$x->route();