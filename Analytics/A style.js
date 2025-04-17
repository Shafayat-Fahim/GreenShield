
// LOSS BY CATEGORY - Doughnut Chart
const ctxLoss = document.getElementById('lossByCategoryChart').getContext('2d');
const lossByCategoryChart = new Chart(ctxLoss, {
type: 'doughnut',
data: {
    labels: ['Strawberries', 'Tomatoes', 'Bananas', 'Apples', 'Carrots', 'Other'],
    datasets: [{
        data: [28, 19, 15, 12, 8, 18],
        backgroundColor: [
            'rgba(244, 67, 54, 0.7)',
            'rgba(255, 152, 0, 0.7)',
            'rgba(255, 193, 7, 0.7)',
            'rgba(76, 175, 80, 0.7)',
            'rgba(33, 150, 243, 0.7)',
            'rgba(156, 39, 176, 0.7)'
        ],
        borderColor: [
            'rgba(244, 67, 54, 1)',
            'rgba(255, 152, 0, 1)',
            'rgba(255, 193, 7, 1)',
            'rgba(76, 175, 80, 1)',
            'rgba(33, 150, 243, 1)',
            'rgba(156, 39, 176, 1)'
        ],
        borderWidth: 1
    }]
},
options: {
    responsive: true,
    plugins: {
        legend: {
            position: 'right',
        },
        title: {
            display: true,
            text: 'Loss by Product Category'
        }
    }
}
});

// INVENTORY TRENDS - Line Chart (Mock Example)
const ctxInventory = document.getElementById('inventoryTrendsChart').getContext('2d');
const inventoryTrendsChart = new Chart(ctxInventory, {
type: 'line',
data: {
    labels: ['Apr 1', 'Apr 3', 'Apr 5', 'Apr 7', 'Apr 9', 'Apr 11', 'Apr 13', 'Apr 15'],
    datasets: [{
        label: 'Total Inventory (tons)',
        data: [120, 115, 130, 125, 135, 140, 138, 145],
        fill: true,
        backgroundColor: 'rgba(33, 150, 243, 0.2)',
        borderColor: 'rgba(33, 150, 243, 1)',
        tension: 0.4
    }]
},
options: {
    responsive: true,
    plugins: {
        legend: {
            position: 'top'
        },
        title: {
            display: true,
            text: 'Inventory Trends Over Time'
        }
    }
}
});

// STORAGE PERFORMANCE - Bar Chart (Mock Example)
const ctxStorage = document.getElementById('storagePerformanceChart').getContext('2d');
const storagePerformanceChart = new Chart(ctxStorage, {
type: 'bar',
data: {
    labels: ['Cold Storage A', 'Cold Storage B', 'Ripening Room B', 'Root Cellar A'],
    datasets: [{
        label: 'Avg. Temp (°C)',
        data: [3.2, 2.8, 19.5, 7.3],
        backgroundColor: 'rgba(255, 193, 7, 0.7)',
        borderColor: 'rgba(255, 193, 7, 1)',
        borderWidth: 1
    }]
},
options: {
    responsive: true,
    scales: {
        y: {
            beginAtZero: true,
            title: {
                display: true,
                text: 'Temperature (°C)'
            }
        }
    },
    plugins: {
        title: {
            display: true,
            text: 'Storage Area Performance'
        }
    }
}
});

// SEASONAL ANALYSIS - Line Chart (Mock Example)
const ctxSeasonal = document.getElementById('seasonalAnalysisChart').getContext('2d');
const seasonalAnalysisChart = new Chart(ctxSeasonal, {
type: 'line',
data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
        label: 'Loss Rate (%)',
        data: [5.2, 4.8, 5.6, 6.1, 5.9, 6.3],
        fill: false,
        borderColor: 'rgba(244, 67, 54, 1)',
        tension: 0.3
    }]
},
options: {
    responsive: true,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Seasonal Loss Rate Trend'
        }
    }
}
});

