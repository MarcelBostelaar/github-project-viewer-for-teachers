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