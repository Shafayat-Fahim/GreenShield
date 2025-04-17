// Sidebar toggle functionality
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.getElementById('sidebar');
const toggleIcon = document.getElementById('toggle-icon');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    toggleIcon.classList.toggle('fa-chevron-left');
    toggleIcon.classList.toggle('fa-chevron-right');
});

// Inventory search functionality
const searchInput = document.querySelector('.search-input');
const tableRows = document.querySelectorAll('tbody tr');

searchInput.addEventListener('keyup', function () {
    const filter = searchInput.value.toLowerCase();

    tableRows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        if (rowText.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
