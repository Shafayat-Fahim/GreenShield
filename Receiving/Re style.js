// Wait for the DOM to fully load
document.addEventListener('DOMContentLoaded', () => {
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleIcon = document.getElementById('toggle-icon');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');

        // Toggle icon direction
        if (sidebar.classList.contains('collapsed')) {
            toggleIcon.classList.remove('fa-chevron-left');
            toggleIcon.classList.add('fa-chevron-right');
        } else {
            toggleIcon.classList.remove('fa-chevron-right');
            toggleIcon.classList.add('fa-chevron-left');
        }
    });

    // Chart.js - Receiving Volume Chart
    const ctx = document.getElementById('receivingVolumeChart').getContext('2d');
    const receivingVolumeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['8 AM', '10 AM', '12 PM', '2 PM', '4 PM', '6 PM'],
            datasets: [{
                label: 'Shipments Received',
                data: [5, 9, 12, 7, 10, 6],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Time of Day'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Shipments'
                    }
                }
            }
        }
    });
});
