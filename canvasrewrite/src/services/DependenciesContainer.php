<?php
require_once __DIR__ . '/CanvasReader.php';
require_once __DIR__ . '/GithubProvider.php';
require_once __DIR__ . '/GitProvider.php';
require_once __DIR__ . '/SubmissionProvider.php';
require_once __DIR__ . '/GroupProvider.php';
require_once __DIR__ . '/SectionsProvider.php';
require_once __DIR__ . '/VirtualIDsProvider.php';
require_once __DIR__ . '/../monkeypatch/MonkeyPatchedCanvasReader.php';
require_once __DIR__ . '/../debug/CaptureAndPreventSubmissionFeedback.php';


class DependenciesContainer
{
    public CanvasReader $canvasReader;
    public GithubProvider $githubProvider;
    public GitProvider $gitProvider;
    public SubmissionProvider $submissionProvider;
    public GroupProvider $groupProvider;
    public SectionsProvider $sectionsProvider;
    public VirtualIDsProvider $virtualIDsProvider;
}

function readerFromEnv(): CanvasReader{
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $apiKey = $env['APIKEY'];
    $baseURL = $env['baseURL'];
    $courseID = $env['courseID'];
    $assignmentID = $env['assignmentID'];
    return new CanvasReader($apiKey, $baseURL, $courseID, $assignmentID);
}

function setupGlobalDependencies(): void
{
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $dependencies = new DependenciesContainer();

    $dependencies->canvasReader = readerFromEnv();
    $dependencies->githubProvider = new GithubProvider();
    $dependencies->groupProvider = new GroupProvider();
    $cloneToFolder = $env['clonetofolder'] ?? null;
    if ($cloneToFolder === null) {
        throw new RuntimeException("clonetofolder not set in .env.");
    }
    $dependencies->gitProvider = new GitProvider($cloneToFolder);
    $dependencies->submissionProvider = new SubmissionProvider();
    $dependencies->sectionsProvider = new SectionsProvider();
    $dependencies->virtualIDsProvider = new VirtualIDsProvider();
    
    //Debug
    // $dependencies->submissionProvider = new CaptureAndPreventSubmissionFeedback();

    //Money patch
    $dependencies->canvasReader = MonkeyPatchedCanvasReader::FromCanvasReader($dependencies->canvasReader);
    //set global provider variable
    $GLOBALS["providers"] = $dependencies;
}