function filterTable() {
    const studentFilter = document.getElementById('student-filter').value.toLowerCase();
    const sectionFilter = document.getElementById('section-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    const table = document.getElementById('submissions-table');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) { // Skip header row
        const row = rows[i];
        const students = row.getAttribute('data-students').toLowerCase() || '';
        const sections = row.getAttribute('data-sections').toLowerCase() || '';
        const status = row.getAttribute('data-status').toLowerCase() || '';

        const matchesStudent = studentFilter === '' || students.includes(studentFilter);
        const matchesSection = sectionFilter === '' || sections.includes(sectionFilter);
        const matchesStatus = statusFilter === '' || status === statusFilter;

        if (matchesStudent && matchesSection && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

/**
 * Submits feedback via AJAX without reloading the page
 * @param {HTMLFormElement} form - The form element
 * @param {number} submissionId - The submission ID
 * @param {Event} event - The form submission event
 * @returns {boolean} - Always returns false to prevent default form submission
 */
async function submitFeedback(button, submissionId) {
    let container = button.parentNode;
    const textArea = container.querySelector('textarea[name="feedback"]');
    const originalButtonText = button.textContent;
    
    // Disable form during submission
    button.disabled = true;
    button.textContent = 'Submitting...';
    textArea.disabled = true;
    const feedbackText = textArea.value.trim();
    if (!feedbackText) {
        alert('Please enter feedback before submitting.');
        return false;
    }
    
    try {
        // Create FormData from the form
        const formData = new FormData();
        formData.append('action', 'addfeedback');
        formData.append('id', submissionId);
        formData.append('feedback', feedbackText);
        
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        
        let currentCourse = urlParams.get('course');
        let currentAssignment = urlParams.get('assignment');
        // Refresh the feedback section to show the new feedback
        const feedbackDiv = container.parentNode.querySelector('.feedback-container');
    
        let tempdiv = document.createElement('div');
        tempdiv.innerHTML = 'Reloading feedback...';
        tempdiv.setAttribute('postload', `?course=${currentCourse}&assignment=${currentAssignment}&action=feedback&id=${submissionId}`);
        feedbackDiv.replaceWith(tempdiv);
        // Trigger postloading for this specific element
    
        
        // Submit the feedback
        const response = await fetch(`/controllers/api/FeedbackSubmitController.php?course=${currentCourse}&assignment=${currentAssignment}`, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Clear the textarea on successful submission
        textArea.value = '';
        findMarkedForPostLoading(document);
        button.textContent = originalButtonText;
        
    } catch (error) {
        console.error('Error submitting feedback:', error);
    } finally {
        // Re-enable form
        button.disabled = false;
        textArea.disabled = false;
    }
    
    return false; // Prevent default form submission
}

function refreshActiveRepoVisuals(){
    let lastClonedRepo = localStorage.getItem('lastClonedRepo');
    if(!lastClonedRepo) return;

    const rows = document.querySelectorAll('#submissions-table tbody tr');
    rows.forEach(row => {
        if(row.getAttribute('data-id') == lastClonedRepo){
            row.classList.add('active-repo');
        } else {
            row.classList.remove('active-repo');
        }
    });
}

function setActiveRepo(id){
    localStorage.setItem('lastClonedRepo', id);
    refreshActiveRepoVisuals();
}

function clone(cloneButton, id){
    // Get currentCourse from URL (assuming ?course=123 or similar)
    const urlParams = new URLSearchParams(window.location.search);
    let currentCourse = urlParams.get('course');
    let currentAssignment = urlParams.get('assignment');
    let oldText = cloneButton.textContent;
    cloneButton.textContent = 'Cloning...';
    cloneButton.disabled = true;
    const formData = new FormData();
    formData.append('id', id);
    fetch(`/controllers/api/CloneController.php?course=${currentCourse}&assignment=${currentAssignment}`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        setActiveRepo(id);
        alert('Clone successful!');
    })
    .catch(error => {
        console.error('Error cloning:', error);
        alert('Failed to clone.');
    })
    .finally(() => {
        cloneButton.textContent = oldText;
        cloneButton.disabled = false;
    });
}

function clearCache(type = 'all'){
    if(!confirm(`Are you sure you want to clear the ${type} cache? Reload may take time.`)){
        return;
    }
    //fetch clear cache endpoint
    let appendix = "";
    if(type != "all"){
        appendix = `?type=${type}`;
    }
    fetch(`/controllers/ClearCacheController.php` + appendix)
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(data => {
        window.location.reload();
    })
    .catch(error => {
        console.error('Error clearing cache:', error);
        alert('Failed to clear cache.');
    });
}

function LoadMoreCommits(callingElement, newLimit, id){
    let parent = callingElement.parentNode;
    let baseURL = window.location.href;
    parent.setAttribute("postload", `${baseURL}&action=commithistory&id=${id}&limit=${newLimit}`);
    callingElement.innerHTML = "Loading more commits";
    findMarkedForPostLoading(document);
}

// Initialize filtering on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set up real-time filtering
    const filters = ['student-filter', 'section-filter', 'status-filter'];
    filters.forEach(filterId => {
        const element = document.getElementById(filterId);
        if (element) {
            element.addEventListener('input', filterTable);
            element.addEventListener('change', filterTable);
        }
    });
});

document.addEventListener('PostloadingFinished', refreshActiveRepoVisuals);