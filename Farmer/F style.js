document.addEventListener('DOMContentLoaded', () => {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const toggleIcon = document.getElementById('toggle-icon');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        toggleIcon.classList.toggle('fa-chevron-left');
        toggleIcon.classList.toggle('fa-chevron-right');
    });

    // Chart.js - Harvest Performance
    const ctx = document.getElementById('harvestChart').getContext('2d');
    const harvestChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mar 18', 'Mar 24', 'Mar 30', 'Apr 5', 'Apr 10', 'Apr 15'],
            datasets: [{
                label: 'Harvest (kg)',
                data: [500, 800, 600, 700, 950, 1050],
                backgroundColor: 'rgba(67, 160, 71, 0.2)',
                borderColor: '#43a047',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
