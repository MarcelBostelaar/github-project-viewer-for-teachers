<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../views/Overview.php';
class OverviewController extends BaseController {
    public function index(){
        global $providers;
        $AllSubmissions = $providers->submissionProvider->getAllSubmissions();
        RenderOverview($AllSubmissions);
    }
}

$x = new OverviewController();
$x->index();