<?php
// Include database configuration
require_once 'config.php';

// Function to get all projects with student names
function getAllProjects($filters = array()) {
    global $conn;
    
    // Start with base query
    $sql = "SELECT p.*, GROUP_CONCAT(s.student_name SEPARATOR ', ') as students 
            FROM projects p 
            LEFT JOIN students s ON p.id = s.project_id";
    
    // Add filters if provided
    $whereConditions = array();
    if (!empty($filters)) {
        // Filter by student name
        if (!empty($filters['student_name'])) {
            $studentName = $conn->real_escape_string($filters['student_name']);
            $sql .= " JOIN students s2 ON p.id = s2.project_id AND s2.student_name LIKE '%$studentName%'";
        }
        
        // Filter by class code
        if (!empty($filters['class_code'])) {
            $classCode = $conn->real_escape_string($filters['class_code']);
            $whereConditions[] = "p.class_code LIKE '%$classCode%'";
        }
        
        // Filter by project name
        if (!empty($filters['project_name'])) {
            $projectName = $conn->real_escape_string($filters['project_name']);
            $whereConditions[] = "p.project_name LIKE '%$projectName%'";
        }
        
        // Filter by year
        if (!empty($filters['year'])) {
            $year = $conn->real_escape_string($filters['year']);
            $whereConditions[] = "p.year = '$year'";
        }
        
        // Filter by GitHub URL
        if (!empty($filters['github_url'])) {
            $githubUrl = $conn->real_escape_string($filters['github_url']);
            $whereConditions[] = "p.github_url LIKE '%$githubUrl%'";
        }
        
        // Add WHERE clause if there are conditions
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
    }
      // Group by project ID to avoid duplicates
    $sql .= " GROUP BY p.id ORDER BY p.is_active DESC, p.year DESC, p.project_name";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        die("Error executing query: " . $conn->error);
    }
    
    $projects = array();
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    
    return $projects;
}

// Function to get a specific project by ID
function getProjectById($id) {
    global $conn;
    
    // Get project data
    $id = $conn->real_escape_string($id);
    $sql = "SELECT * FROM projects WHERE id = '$id'";
    $result = $conn->query($sql);
    
    if ($result === false) {
        die("Error executing query: " . $conn->error);
    }
    
    if ($result->num_rows == 0) {
        return null;
    }
    
    $project = $result->fetch_assoc();
    
    // Get student data
    $sql = "SELECT * FROM students WHERE project_id = '$id'";
    $result = $conn->query($sql);
    
    if ($result === false) {
        die("Error executing query: " . $conn->error);
    }
    
    $project['students'] = array();
    while ($row = $result->fetch_assoc()) {
        $project['students'][] = $row['student_name'];
    }
    
    return $project;
}

// Function to add a new project
function addProject($data) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert into projects table
        $githubUrl = $conn->real_escape_string($data['github_url']);
        $classCode = $conn->real_escape_string($data['class_code']);
        $projectName = $conn->real_escape_string($data['project_name']);
        $year = $conn->real_escape_string($data['year']);
        $notes = $conn->real_escape_string($data['notes']);
        
        $sql = "INSERT INTO projects (github_url, class_code, project_name, year, notes, is_active) VALUES ('$githubUrl', '$classCode', '$projectName', '$year', '$notes', FALSE)";
        
        if ($conn->query($sql) === FALSE) {
            throw new Exception("Error adding project: " . $conn->error);
        }
        
        $projectId = $conn->insert_id;
        
        // Insert students
        $students = explode(',', $data['students']);
        foreach ($students as $student) {
            $student = trim($student);
            if (!empty($student)) {
                $studentName = $conn->real_escape_string($student);
                $sql = "INSERT INTO students (project_id, student_name) VALUES ('$projectId', '$studentName')";
                if ($conn->query($sql) === FALSE) {
                    throw new Exception("Error adding student: " . $conn->error);
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die($e->getMessage());
        return false;
    }
}

// Function to update an existing project
function updateProject($id, $data) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $id = $conn->real_escape_string($id);
        $githubUrl = $conn->real_escape_string($data['github_url']);
        $classCode = $conn->real_escape_string($data['class_code']);
        $projectName = $conn->real_escape_string($data['project_name']);
        $year = $conn->real_escape_string($data['year']);
        $notes = $conn->real_escape_string($data['notes']);
        
        // Update project (preserve existing is_active state)
        $sql = "UPDATE projects SET github_url = '$githubUrl', class_code = '$classCode', project_name = '$projectName', year = '$year', notes = '$notes' WHERE id = '$id'";
        
        if ($conn->query($sql) === FALSE) {
            throw new Exception("Error updating project: " . $conn->error);
        }
        
        // Delete existing students
        $sql = "DELETE FROM students WHERE project_id = '$id'";
        if ($conn->query($sql) === FALSE) {
            throw new Exception("Error deleting students: " . $conn->error);
        }
        
        // Insert updated students
        $students = explode(',', $data['students']);
        foreach ($students as $student) {
            $student = trim($student);
            if (!empty($student)) {
                $studentName = $conn->real_escape_string($student);
                $sql = "INSERT INTO students (project_id, student_name) VALUES ('$id', '$studentName')";
                if ($conn->query($sql) === FALSE) {
                    throw new Exception("Error adding student: " . $conn->error);
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die($e->getMessage());
        return false;
    }
}

// Function to delete a project
function deleteProject($id) {
    global $conn;
    
    $id = $conn->real_escape_string($id);
    
    // Delete project (cascade will delete related students)
    $sql = "DELETE FROM projects WHERE id = '$id'";
    return $conn->query($sql);
}

// Function to set a project as active
function setProjectActive($id) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $id = $conn->real_escape_string($id);
        
        // First, clear any currently active projects
        $resetSql = "UPDATE projects SET is_active = FALSE";
        if ($conn->query($resetSql) === FALSE) {
            throw new Exception("Error resetting active projects: " . $conn->error);
        }
        
        // Then set the selected project as active
        $sql = "UPDATE projects SET is_active = TRUE WHERE id = '$id'";
        if ($conn->query($sql) === FALSE) {
            throw new Exception("Error setting project as active: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die($e->getMessage());
        return false;
    }
}

// Function to get the active project
function getActiveProject() {
    global $conn;
    
    $sql = "SELECT p.*, GROUP_CONCAT(s.student_name SEPARATOR ', ') as students 
            FROM projects p 
            LEFT JOIN students s ON p.id = s.project_id 
            WHERE p.is_active = TRUE
            GROUP BY p.id";
            
    $result = $conn->query($sql);
    
    if ($result === false) {
        return null;
    }
    
    if ($result->num_rows == 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

// Function to find a project by GitHub URL
function findProjectByGithubUrl($githubUrl) {
    global $conn;
    
    $githubUrl = $conn->real_escape_string($githubUrl);
    
    $sql = "SELECT id FROM projects WHERE github_url = '$githubUrl'";
    $result = $conn->query($sql);
    
    if ($result === false || $result->num_rows == 0) {
        return null;
    }
    
    $row = $result->fetch_assoc();
    return $row['id'];
}

function getFieldSuggestions($field) {
    global $conn;
    
    if ($field === 'student_name') {
        $sql = "SELECT DISTINCT student_name FROM students ORDER BY student_name";
    } elseif (in_array($field, ['class_code', 'project_name', 'year'])) {
        $field = $conn->real_escape_string($field);
        $sql = "SELECT DISTINCT $field FROM projects ORDER BY $field";
    } else {
        return array();
    }
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        return array();
    }
    
    $suggestions = array();
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row[$field];
    }
    
    return $suggestions;
}

// Function to update notes for the active project
function updateActiveProjectNotes($notes) {
    global $conn;
    
    // Get the active project ID first
    $sql = "SELECT id FROM projects WHERE is_active = TRUE";
    $result = $conn->query($sql);
    
    if ($result === false || $result->num_rows == 0) {
        return false;
    }
    
    $row = $result->fetch_assoc();
    $id = $row['id'];
    
    // Now update the notes for this project
    $notes = $conn->real_escape_string($notes);
    $sql = "UPDATE projects SET notes = '$notes' WHERE id = '$id'";
    
    if ($conn->query($sql) === FALSE) {
        return false;
    }
    
    return true;
}
?>
