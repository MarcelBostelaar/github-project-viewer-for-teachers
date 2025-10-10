<?php

// Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';


// Set up global dependencies function
function setupGlobalDependencies($courseID, $assignmentID): void
{
    $env = parse_ini_file(__DIR__ . '/../.env');
    $dependencies = new GithubProjectViewer\Services\DependenciesContainer();

    $dependencies->canvasReader = readerFromEnv($courseID, $assignmentID);
    $dependencies->githubProvider = new \GithubProjectViewer\Services\GithubProvider();
    $dependencies->groupProvider = new \GithubProjectViewer\Services\GroupProvider();
    $cloneToFolder = $env['clonetofolder'] ?? null;
    if ($cloneToFolder === null) {
        throw new RuntimeException("clonetofolder not set in .env.");
    }
    $dependencies->gitProvider = new \GithubProjectViewer\Services\GitProvider($cloneToFolder);
    $dependencies->submissionProvider = new \GithubProjectViewer\Services\SubmissionProvider();
    $dependencies->sectionsProvider = new \GithubProjectViewer\Services\SectionsProvider();
    $dependencies->virtualIDsProvider = new \GithubProjectViewer\Services\VirtualIDsProvider();
    
    //Debug
    // $dependencies->submissionProvider = new CaptureAndPreventSubmissionFeedback();

    //Money patch
    //Group set 1280 was removed, use 1300 instead for any assignments that used it
    $dependencies->canvasReader = \GithubProjectViewer\MonkeyPatch\MonkeyPatchedCanvasReader::FromCanvasReader($dependencies->canvasReader, [1280 => 1300]);
    
    //set global provider variable
    $GLOBALS["providers"] = $dependencies;
}

function readerFromEnv($courseID, $assignmentID): \GithubProjectViewer\Services\CanvasReader{
    $env = parse_ini_file(__DIR__ . '/../.env');
    $apiKey = $env['APIKEY'];
    $baseURL = $env['baseURL'];
    return new \GithubProjectViewer\Services\CanvasReader($apiKey, $baseURL, $courseID, $assignmentID);
}

// Route to the appropriate controller
$route = $_GET['route'] ?? 'overview';

switch ($route) {
    case 'overview':
        $controller = new OverviewController();
        $controller->route();
        break;
    case 'clone':
        $controller = new CloneController();
        $controller->route();
        break;
    case 'feedback':
        $controller = new FeedbackSubmitController();
        $controller->route();
        break;
    case 'api':
        $controller = new APIController();
        $controller->route();
        break;
    case 'clear-cache':
        $controller = new ClearCacheController();
        $controller->route();
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}