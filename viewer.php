<?php
// Include database configuration, functions, crud operations and utilities
require_once 'functions.php';
require_once 'crudops.php';
require_once 'util.php';

// Process form submissions
$crudResponse = handleCrudOperations();
$message = $crudResponse['message'];
$errorMessage = $crudResponse['errorMessage'];

// Handle filters and save to cookies
$filterFields = ['student_name', 'class_code', 'project_name', 'year', 'github_url'];
$cookieExpiration = time() + (86400 * 30); // Set cookie expiration (30 days)
$filters = handleFilterCookies($filterFields, $cookieExpiration);

// Get projects based on filters
$projects = getAllProjects($filters);

// Get project to edit if requested
$editProject = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editProject = getProjectById($_GET['edit']);
}

// Get active project
$activeProject = getActiveProject();

// Get suggestions for all fields
$classCodes = getFieldSuggestions('class_code');
$projectNames = getFieldSuggestions('project_name');
$years = getFieldSuggestions('year');
$studentNames = getFieldSuggestions('student_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Project Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        .suggestions {
            position: absolute;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 150px;
            overflow-y: auto;
            width: 100%;
            z-index: 1000;
            display: none;
        }
        .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
        }
        .suggestion-item:hover {
            background-color: #f0f0f0;
        }
        #active-notes {
            resize: vertical;
            min-height: 100px;
            border: none;
            background-color: #f8f9fa;
        }
        #active-notes:focus {
            background-color: #fff;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div class="container mt-4">        <h1 class="mb-4">Student Project Viewer</h1>
        
        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <!-- Active Project -->
        <?php if ($activeProject): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title h5 m-0">Active Project: <?php echo htmlspecialchars($activeProject['project_name']); ?></h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <dl class="row">
                            <dt class="col-sm-3">Project Name:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($activeProject['project_name']); ?></dd>
                            
                            <dt class="col-sm-3">Students:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($activeProject['students']); ?></dd>
                            
                            <dt class="col-sm-3">Class Code:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($activeProject['class_code']); ?></dd>
                            
                            <dt class="col-sm-3">Year:</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($activeProject['year']); ?></dd>
                            
                            <dt class="col-sm-3">GitHub URL:</dt>
                            <dd class="col-sm-9">
                                <a href="<?php echo htmlspecialchars($activeProject['github_url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($activeProject['github_url']); ?>
                                </a>
                            </dd>
                        </dl>
                          <div class="mt-3">
                            <form method="post" id="notes-form">
                                <input type="hidden" name="action" value="update_notes">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0">Notes:</h6>
                                    <button type="submit" class="btn btn-sm btn-primary" id="save-notes-btn">
                                        <i class="bi bi-save"></i> Save Notes
                                    </button>
                                </div>
                                <div class="border rounded">
                                    <textarea class="form-control" id="active-notes" autocomplete="off" name="notes" rows="4"><?php echo htmlspecialchars($activeProject['notes'] ?? ''); ?></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-end">
                        <div class="btn-group">
                            <a href="viewer.php?edit=<?php echo $activeProject['id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <button class="btn btn-outline-success clone-repo-btn" 
                                data-github-url="<?php echo htmlspecialchars($activeProject['github_url']); ?>">
                                <i class="bi bi-git"></i> Clone Repository
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Add/Edit Project Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title h5 m-0"><?php echo $editProject ? 'Edit Project' : 'Add New Project'; ?></h2>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="<?php echo $editProject ? 'edit' : 'add'; ?>">
                    <?php if ($editProject): ?>
                        <input type="hidden" name="id" value="<?php echo $editProject['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="github_url" class="form-label">GitHub URL</label>
                            <input type="url" class="form-control" id="github_url" name="github_url" required
                                value="<?php echo $editProject ? htmlspecialchars($editProject['github_url']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="class_code" class="form-label">Class Code</label>
                            <div class="position-relative">
                                <input type="text" class="form-control suggestion-input" id="class_code" 
                                    name="class_code" required data-field="class_code"
                                    value="<?php echo $editProject ? htmlspecialchars($editProject['class_code']) : ''; ?>">
                                <div class="suggestions" id="class_code-suggestions"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_name" class="form-label">Project Name</label>
                            <div class="position-relative">
                                <input type="text" class="form-control suggestion-input" id="project_name" 
                                    name="project_name" required data-field="project_name"
                                    value="<?php echo $editProject ? htmlspecialchars($editProject['project_name']) : ''; ?>">
                                <div class="suggestions" id="project_name-suggestions"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="year" class="form-label">Year</label>
                            <div class="position-relative">
                                <input type="number" class="form-control suggestion-input" id="year" 
                                    name="year" required min="2000" max="2100" data-field="year"
                                    value="<?php echo $editProject ? htmlspecialchars($editProject['year']) : date('Y'); ?>">
                                <div class="suggestions" id="year-suggestions"></div>
                            </div>
                        </div>
                    </div>
                      <div class="mb-3">
                        <label for="students" class="form-label">Student Names (comma-separated)</label>
                        <div class="position-relative">
                            <input type="text" class="form-control suggestion-input" id="students" 
                                name="students" required data-field="student_name"
                                value="<?php echo $editProject ? htmlspecialchars(implode(', ', $editProject['students'])) : ''; ?>">
                            <div class="suggestions" id="students-suggestions"></div>
                        </div>
                        <div class="form-text">Enter multiple student names separated by commas</div>
                    </div>
                      <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo $editProject ? htmlspecialchars($editProject['notes']) : ''; ?></textarea>
                    </div>
                    
                    <div class="text-end">
                        <?php if ($editProject): ?>
                            <a href="viewer.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editProject ? 'Update Project' : 'Add Project'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title h5 m-0">Filter Projects</h2>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="filter" value="1">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="student_name_filter" class="form-label">Student Name</label>
                            <div class="position-relative">
                                <input type="text" class="form-control suggestion-input" id="student_name_filter" 
                                    name="student_name" data-field="student_name"
                                    value="<?php echo isset($filters['student_name']) ? htmlspecialchars($filters['student_name']) : ''; ?>">
                                <div class="suggestions" id="student_name_filter-suggestions"></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="class_code_filter" class="form-label">Class Code</label>
                            <div class="position-relative">
                                <input type="text" class="form-control suggestion-input" id="class_code_filter" 
                                    name="class_code" data-field="class_code"
                                    value="<?php echo isset($filters['class_code']) ? htmlspecialchars($filters['class_code']) : ''; ?>">
                                <div class="suggestions" id="class_code_filter-suggestions"></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="project_name_filter" class="form-label">Project Name</label>
                            <div class="position-relative">
                                <input type="text" class="form-control suggestion-input" id="project_name_filter" 
                                    name="project_name" data-field="project_name"
                                    value="<?php echo isset($filters['project_name']) ? htmlspecialchars($filters['project_name']) : ''; ?>">
                                <div class="suggestions" id="project_name_filter-suggestions"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="year_filter" class="form-label">Year</label>
                            <div class="position-relative">
                                <input type="text" class="form-control suggestion-input" id="year_filter" 
                                    name="year" data-field="year"
                                    value="<?php echo isset($filters['year']) ? htmlspecialchars($filters['year']) : ''; ?>">
                                <div class="suggestions" id="year_filter-suggestions"></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="github_url_filter" class="form-label">GitHub URL</label>
                            <input type="text" class="form-control" id="github_url_filter" name="github_url"
                                value="<?php echo isset($filters['github_url']) ? htmlspecialchars($filters['github_url']) : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <button type="submit" name="clear_filters" class="btn btn-secondary">Clear Filters</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Projects List -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="card-title h5 m-0">Student Projects</h2>
                    <span class="badge bg-secondary"><?php echo count($projects); ?> Projects</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($projects)): ?>
                    <div class="alert alert-info">No projects found. Please add a new project or adjust your filters.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>                                <tr>
                                    <th>Project Name</th>
                                    <th>Student(s)</th>
                                    <th>Class Code</th>
                                    <th>Year</th>
                                    <th>Notes</th>
                                    <th>GitHub URL</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>                                <tr<?php echo ($project['is_active'] == TRUE) ? ' class="table-primary"' : ''; ?>>                                    <td>
                                        <?php if ($project['is_active'] == TRUE): ?>
                                            <span class="badge bg-primary me-1" title="Active Project"><i class="bi bi-star-fill"></i></span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($project['project_name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($project['students']); ?></td>
                                    <td><?php echo htmlspecialchars($project['class_code']); ?></td>
                                    <td><?php echo htmlspecialchars($project['year']); ?></td>
                                    <td>
                                        <?php if (!empty($project['notes'])): ?>
                                            <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo htmlspecialchars($project['notes']); ?>">
                                                <?php echo htmlspecialchars(substr($project['notes'], 0, 50)) . (strlen($project['notes']) > 50 ? '...' : ''); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($project['github_url']); ?>" 
                                           target="_blank" class="text-truncate d-inline-block" style="max-width: 150px;">
                                            <?php echo htmlspecialchars($project['github_url']); ?>
                                        </a>
                                    </td>                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="viewer.php?edit=<?php echo $project['id']; ?>" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-outline-success clone-repo-btn" 
                                               title="Clone Repository" 
                                               data-github-url="<?php echo htmlspecialchars($project['github_url']); ?>">
                                                <i class="bi bi-git"></i>
                                            </button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this project?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle notes form submission
            const notesForm = document.getElementById('notes-form');
            if (notesForm) {
                notesForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Get form data
                    const formData = new FormData(notesForm);
                    
                    // Show loading state on button
                    const saveButton = document.getElementById('save-notes-btn');
                    const originalButtonHtml = saveButton.innerHTML;
                    saveButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
                    saveButton.disabled = true;
                    
                    // Submit form via AJAX
                    fetch('viewer.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Extract the success message from the HTML
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        const successMessage = tempDiv.querySelector('.alert-success');
                        
                        if (successMessage) {
                            // Show a temporary success message
                            const notesForm = document.getElementById('notes-form');
                            const messageDiv = document.createElement('div');
                            messageDiv.className = 'alert alert-success mt-2';
                            messageDiv.textContent = 'Notes saved successfully!';
                            notesForm.insertAdjacentElement('afterend', messageDiv);
                            
                            // Remove the message after 3 seconds
                            setTimeout(() => {
                                messageDiv.remove();
                            }, 3000);
                        }
                        
                        // Reset button state
                        saveButton.innerHTML = originalButtonHtml;
                        saveButton.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error saving notes:', error);
                        // Reset button state
                        saveButton.innerHTML = originalButtonHtml;
                        saveButton.disabled = false;
                        
                        // Show error message
                        alert('An error occurred while saving the notes.');
                    });
                });
            }
            
            // Handle suggestions for form fields
            const suggestionInputs = document.querySelectorAll('.suggestion-input');
            
            suggestionInputs.forEach(input => {
                const field = input.dataset.field;
                const suggestionsContainer = document.getElementById(`${input.id}-suggestions`);
                
                // Show suggestions on focus
                input.addEventListener('focus', function() {
                    fetchSuggestions(field, input.value, suggestionsContainer);
                });
                
                // Show suggestions when typing
                input.addEventListener('input', function() {
                    fetchSuggestions(field, input.value, suggestionsContainer);
                });
                
                // Hide suggestions when clicking outside
                document.addEventListener('click', function(e) {
                    if (e.target !== input && e.target !== suggestionsContainer) {
                        suggestionsContainer.style.display = 'none';
                    }
                });
                
                // Handle special case for student names (comma-separated)
                if (field === 'student_name' && input.id === 'students') {
                    input.addEventListener('input', function() {
                        const lastCommaIndex = input.value.lastIndexOf(',');
                        const searchTerm = lastCommaIndex !== -1 ? 
                            input.value.substring(lastCommaIndex + 1).trim() : 
                            input.value.trim();
                        fetchSuggestions(field, searchTerm, suggestionsContainer);
                    });
                }
            });
            
            // Function to fetch suggestions from the server
            function fetchSuggestions(field, query, container) {
                fetch(`suggestions.php?field=${field}`)
                    .then(response => response.json())
                    .then(data => {
                        // Filter suggestions based on input
                        const filteredData = data.filter(item => 
                            item.toLowerCase().includes(query.toLowerCase())
                        );
                        
                        // Display suggestions
                        container.innerHTML = '';
                        
                        if (filteredData.length > 0) {
                            filteredData.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'suggestion-item';
                                div.textContent = item;
                                div.addEventListener('click', () => {
                                    const input = container.parentNode.querySelector('input');
                                    
                                    // Handle special case for student names (comma-separated)
                                    if (field === 'student_name' && input.id === 'students') {
                                        const lastCommaIndex = input.value.lastIndexOf(',');
                                        if (lastCommaIndex !== -1) {
                                            input.value = input.value.substring(0, lastCommaIndex + 1) + ' ' + item;
                                        } else {
                                            input.value = item;
                                        }
                                    } else {
                                        input.value = item;
                                    }
                                    
                                    container.style.display = 'none';
                                });
                                container.appendChild(div);
                            });
                            container.style.display = 'block';
                        } else {
                            container.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Error fetching suggestions:', error));
            }
            
            // Handle clone repository buttons
            const cloneButtons = document.querySelectorAll('.clone-repo-btn');
            cloneButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const githubUrl = this.getAttribute('data-github-url');
                    if (!githubUrl) {
                        alert('GitHub URL not found');
                        return;
                    }
                    
                    // Show loading state
                    const originalInnerHTML = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
                    this.disabled = true;
                    
                    // Create form data
                    const formData = new FormData();
                    formData.append('github_url', githubUrl);
                      // Send AJAX request to clone.php
                    fetch('clone.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Reset button state
                        this.innerHTML = originalInnerHTML;
                        this.disabled = false;
                          if (data.success) {
                            alert('Repository cloned successfully! Project set as active.');
                            // Refresh the page by redirecting instead of reload to ensure a fresh page with data
                            window.location.href = 'viewer.php';
                            
                            // Or redirect to the cloned project (optional)
                            //if (confirm('Open the cloned project?')) {
                            //    window.open('project/', '_blank');
                            //}
                        } else {
                            alert('Failed to clone repository: ' + data.message);
                            console.error(data);
                        }
                    })
                    .catch(error => {
                        // Reset button state
                        this.innerHTML = originalInnerHTML;
                        this.disabled = false;
                        
                        alert('An error occurred while cloning the repository');
                        console.error('Clone error:', error);
                    });
                });
            });
        });
    </script>
</body>
</html>