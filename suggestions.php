<?php
// Include database configuration and functions
// Function to get unique field values for suggestions
require_once 'functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check for field parameter
if (isset($_GET['field'])) {
    $field = $_GET['field'];
    $suggestions = getFieldSuggestions($field);
    echo json_encode($suggestions);
} else {
    echo json_encode(['error' => 'Field parameter required']);
}
?>
