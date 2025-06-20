<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "student_projects";

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Connect to the selected database
$conn->select_db($db_name);

// Create tables if they don't exist
$sql = "CREATE TABLE IF NOT EXISTS projects (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    github_url VARCHAR(255) NOT NULL,
    class_code VARCHAR(50) NOT NULL,
    project_name VARCHAR(100) NOT NULL,
    year INT(4) NOT NULL,
    notes TEXT,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating projects table: " . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    project_id INT(11) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating students table: " . $conn->error);
}
?>
