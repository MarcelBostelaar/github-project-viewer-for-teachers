<?php
// Include required functions
require_once 'functions.php';
require_once 'util.php';

// Security check - only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if github_url parameter exists
if (!isset($_POST['github_url']) || empty($_POST['github_url'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'GitHub URL is required']);
    exit;
}

// Set the project directory path
$projectDir = __DIR__ . DIRECTORY_SEPARATOR . 'project';
$githubUrl = $_POST['github_url'];

// Set content type to JSON
header('Content-Type: application/json');

rmdir_recursive($projectDir);

// Create the project directory
if (!mkdir($projectDir, 0755, true) && !is_dir($projectDir)) {
    echo json_encode(['success' => false, 'message' => 'Failed to create project directory']);
    exit;
}

// Execute git clone command
$command = "git clone " . escapeshellarg($githubUrl) . " " . escapeshellarg($projectDir) . " 2>&1";
exec($command, $output, $returnCode);

// Check if the command was successful
if ($returnCode !== 0) {
    // Delete the directory if the clone failed
    deleteDirectory($projectDir);
    echo json_encode([
        'success' => false, 
        'message' => 'Git clone failed', 
        'output' => implode("\n", $output),
        'command' => $command
    ]);
    exit;
}

// Set this project as active
$projectId = findProjectByGithubUrl($githubUrl);
if ($projectId) {
    setProjectActive($projectId);
}

// Success response
echo json_encode([
    'success' => true, 
    'message' => 'Repository cloned successfully',
    'project_url' => 'project/',
    'project_id' => $projectId
]);
?>