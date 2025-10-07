<?php
require_once __DIR__ . '/CanvasReader.php';
require_once __DIR__ . '/GithubProvider.php';
require_once __DIR__ . '/GitProvider.php';
require_once __DIR__ . '/SubmissionProvider.php';
require_once __DIR__ . '/GroupProvider.php';
require_once __DIR__ . '/SectionsProvider.php';


class DependenciesContainer
{
    public CanvasReader $canvasReader;
    public GithubProvider $githubProvider;
    public GitProvider $gitProvider;
    public SubmissionProvider $submissionProvider;
    public GroupProvider $groupProvider;
    public SectionsProvider $sectionsProvider;
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
    $dependencies = new DependenciesContainer();

    $dependencies->canvasReader = readerFromEnv();
    $dependencies->githubProvider = new GithubProvider();
    $dependencies->groupProvider = new GroupProvider();
    $dependencies->gitProvider = new GitProvider();
    $dependencies->submissionProvider = new SubmissionProvider();
    $dependencies->sectionsProvider = new SectionsProvider();
    
    //Debug

    //set global provider variable
    $GLOBALS["providers"] = $dependencies;
}