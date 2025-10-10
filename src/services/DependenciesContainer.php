<?php
require_once __DIR__ . '/CanvasReader.php';
require_once __DIR__ . '/GithubProvider.php';
require_once __DIR__ . '/GitProvider.php';
require_once __DIR__ . '/SubmissionProvider.php';
require_once __DIR__ . '/GroupProvider.php';
require_once __DIR__ . '/SectionsProvider.php';
require_once __DIR__ . '/VirtualIDsProvider.php';
require_once __DIR__ . '/interfaces/ICanvasReader.php';
require_once __DIR__ . '/interfaces/IGithubProvider.php';
require_once __DIR__ . '/interfaces/IGitProvider.php';
require_once __DIR__ . '/interfaces/ISubmissionProvider.php';
require_once __DIR__ . '/interfaces/IGroupProvider.php';
require_once __DIR__ . '/interfaces/ISectionsProvider.php';
require_once __DIR__ . '/interfaces/IVirtualIDsProvider.php';
require_once __DIR__ . '/../monkeypatch/MonkeyPatchedCanvasReader.php';
require_once __DIR__ . '/../debug/CaptureAndPreventSubmissionFeedback.php';


class DependenciesContainer
{
    public ICanvasReader $canvasReader;
    public IGithubProvider $githubProvider;
    public IGitProvider $gitProvider;
    public ISubmissionProvider $submissionProvider;
    public IGroupProvider $groupProvider;
    public ISectionsProvider $sectionsProvider;
    public IVirtualIDsProvider $virtualIDsProvider;
}

function readerFromEnv($courseID, $assignmentID): CanvasReader{
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $apiKey = $env['APIKEY'];
    $baseURL = $env['baseURL'];
    return new CanvasReader($apiKey, $baseURL, $courseID, $assignmentID);
}

function setupGlobalDependencies($courseID, $assignmentID): void
{
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $dependencies = new DependenciesContainer();

    $dependencies->canvasReader = readerFromEnv($courseID, $assignmentID);
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
    //Group set 1280 was removed, use 1300 instead for any assignments that used it
    $dependencies->canvasReader = MonkeyPatchedCanvasReader::FromCanvasReader($dependencies->canvasReader, [1280 => 1300]);
    
    //set global provider variable
    $GLOBALS["providers"] = $dependencies;
}