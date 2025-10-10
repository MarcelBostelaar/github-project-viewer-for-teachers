<?php
namespace GithubProjectViewer\Services;
use GithubProjectViewer\Services\Interfaces as Interfaces;


class DependenciesContainer
{
    public Interfaces\ICanvasReader $canvasReader;
    public Interfaces\IGithubProvider $githubProvider;
    public Interfaces\ILocalGitProvider $gitProvider;
    public Interfaces\ISubmissionProvider $submissionProvider;
    public Interfaces\IGroupProvider $groupProvider;
    public Interfaces\ISectionsProvider $sectionsProvider;
    public Interfaces\IVirtualIDsProvider $virtualIDsProvider;
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
        throw new \RuntimeException("clonetofolder not set in .env.");
    }
    $dependencies->gitProvider = new GitProvider($cloneToFolder);
    $dependencies->submissionProvider = new SubmissionProvider();
    $dependencies->sectionsProvider = new SectionsProvider();
    $dependencies->virtualIDsProvider = new VirtualIDsProvider();
    
    //Debug
    // $dependencies->submissionProvider = new CaptureAndPreventSubmissionFeedback();

    //Money patch
    //Group set 1280 was removed, use 1300 instead for any assignments that used it
    $dependencies->canvasReader = \GithubProjectViewer\Monkeypatch\MonkeyPatchedCanvasReader::FromCanvasReader($dependencies->canvasReader, [1280 => 1300]);
    
    //set global provider variable
    $GLOBALS["providers"] = $dependencies;
}