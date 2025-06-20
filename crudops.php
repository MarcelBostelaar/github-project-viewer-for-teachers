<?php
// Include database functions
require_once 'functions.php';

// Initialize response variables
$message = '';
$errorMessage = '';

/**
 * Handle CRUD operations for projects
 * @return array Contains message and errorMessage
 */
function handleCrudOperations() {
    $message = '';
    $errorMessage = '';
    
    // Handle updating notes for active project
    if (isset($_POST['action']) && $_POST['action'] === 'update_notes') {
        if (updateActiveProjectNotes($_POST['notes'])) {
            $message = "Notes updated successfully";
        } else {
            $errorMessage = "Error updating notes";
        }
    }
    
    // Handle delete operation
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
        if (deleteProject($_POST['id'])) {
            $message = "Project deleted successfully";
        } else {
            $errorMessage = "Error deleting project";
        }
    }

    // Handle add/edit project
    if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {        $data = [
            'github_url' => $_POST['github_url'],
            'class_code' => $_POST['class_code'],
            'project_name' => $_POST['project_name'],
            'year' => $_POST['year'],
            'students' => $_POST['students'],
            'notes' => $_POST['notes']
        ];
        
        if ($_POST['action'] === 'add') {
            if (addProject($data)) {
                $message = "Project added successfully";
            } else {
                $errorMessage = "Error adding project";
            }
        } else {
            if (updateProject($_POST['id'], $data)) {
                $message = "Project updated successfully";
            } else {
                $errorMessage = "Error updating project";
            }
        }
    }
    
    return [
        'message' => $message,
        'errorMessage' => $errorMessage
    ];
}