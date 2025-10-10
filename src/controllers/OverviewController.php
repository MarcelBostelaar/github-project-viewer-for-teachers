<?php

namespace GithubProjectViewer\Controllers;

class OverviewController extends BaseController {
    public function route() {
        $actionGet = $_GET['action'] ?? null;
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
        // RenderOverview($AllSubmissions, $this->getBaseURL());
        echo "<h1>GitHub Project Viewer</h1>";
        echo "<p>Autoloader working! Course ID: $this->courseID, Assignment ID: $this->assignmentID</p>";
        echo "<p>Found " . count($AllSubmissions) . " submissions.</p>";
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