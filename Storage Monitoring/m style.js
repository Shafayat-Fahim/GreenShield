// DOM Elements
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');
const sidebarToggle = document.getElementById('sidebar-toggle');
const toggleIcon = document.getElementById('toggle-icon');
const storageZones = document.querySelectorAll('.storage-zone-block');
const thresholdsModal = document.getElementById('thresholds-modal');
const editThresholdsBtn = document.getElementById('edit-thresholds-btn');
const cancelThresholdsBtn = document.getElementById('cancel-thresholds');
const saveThresholdsBtn = document.getElementById('save-thresholds');
const closeModalBtn = document.querySelector('.close-modal');
const tempTimeRange = document.getElementById('temp-time-range');
const humidityTimeRange = document.getElementById('humidity-time-range');
const alertPriorityFilter = document.getElementById('alert-priority');

// Toggle sidebar
sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('sidebar-collapsed');
    mainContent.classList.toggle('main-content-expanded');
    
    if (sidebar.classList.contains('sidebar-collapsed')) {
        toggleIcon.classList.remove('fa-chevron-left');
        toggleIcon.classList.add('fa-chevron-right');
    } else {
        toggleIcon.classList.remove('fa-chevron-right');
        toggleIcon.classList.add('fa-chevron-left');
    }
});

// Zone data
const zoneData = {
    'cold-storage-a': {
        name: 'Cold Storage A',
        type: 'Refrigerated',
        size: '50 m²',
        capacity: '5,000 kg',
        currentLoad: '3,750 kg (75%)',
        lastMaintenance: '2025-03-15',
        temperature: '4.2°C',
        humidity: '82%',
        airflow: '120 m³/h',
        doorOpenTime: '35 min',
        optimalTemp: '2°C to 6°C',
        optimalHumidity: '80% to 90%',
        optimalAirflow: '100 to 150 m³/h',
        inventory: [
            { product: 'Apples (Gala)', batchId: 'AP-1025', quantity: '800 kg', harvestDate: '2025-04-01', freshness: 'Excellent' },
            { product: 'Pears (Anjou)', batchId: 'PR-0892', quantity: '650 kg', harvestDate: '2025-04-05', freshness: 'Good' },
            { product: 'Cabbage', batchId: 'CB-0578', quantity: '450 kg', harvestDate: '2025-04-10', freshness: 'Excellent' },
            { product: 'Carrots', batchId: 'CR-0733', quantity: '720 kg', harvestDate: '2025-04-08', freshness: 'Good' },
            { product: 'Broccoli', batchId: 'BC-0412', quantity: '380 kg', harvestDate: '2025-04-12', freshness: 'Good' }
        ]
    },
    'ripening-room-b': {
        name: 'Ripening Room B',
        type: 'Ethylene Control',
        size: '30 m²',
        capacity: '3,000 kg',
        currentLoad: '2,100 kg (70%)',
        lastMaintenance: '2025-03-28',
        temperature: '16.8°C',
        humidity: '72%',
        airflow: '90 m³/h',
        doorOpenTime: '28 min',
        optimalTemp: '14°C to 16°C',
        optimalHumidity: '70% to 80%',
        optimalAirflow: '80 to 100 m³/h',
        inventory: [
            { product: 'Bananas (Cavendish)', batchId: 'BN-0721', quantity: '1,200 kg', harvestDate: '2025-04-05', freshness: 'Ripening' },
            { product: 'Mangoes', batchId: 'MG-0342', quantity: '450 kg', harvestDate: '2025-04-08', freshness: 'Ripening' },
            { product: 'Avocados', batchId: 'AV-0189', quantity: '380 kg', harvestDate: '2025-04-10', freshness: 'Ripening' }
        ]
    },
    'ripening-room-c': {
        name: 'Ripening Room C',
        type: 'Ethylene Control',
        size: '30 m²',
        capacity: '3,000 kg',
        currentLoad: '2,400 kg (80%)',
        lastMaintenance: '2025-03-22',
        temperature: '15.5°C',
        humidity: '65%',
        airflow: '85 m³/h',
        doorOpenTime: '42 min',
        optimalTemp: '14°C to 16°C',
        optimalHumidity: '70% to 80%',
        optimalAirflow: '80 to 100 m³/h',
        inventory: [
            { product: 'Bananas (Cavendish)', batchId: 'BN-0735', quantity: '1,600 kg', harvestDate: '2025-04-02', freshness: 'Nearly Ready' },
            { product: 'Kiwi', batchId: 'KW-0211', quantity: '350 kg', harvestDate: '2025-04-07', freshness: 'Ripening' },
            { product: 'Papaya', batchId: 'PP-0142', quantity: '450 kg', harvestDate: '2025-04-09', freshness: 'Ripening' }
        ]
    },
    'cold-storage-b': {
        name: 'Cold Storage B',
        type: 'Refrigerated',
        size: '40 m²',
        capacity: '4,000 kg',
        currentLoad: '2,800 kg (70%)',
        lastMaintenance: '2025-01-20',
        temperature: '3.8°C',
        humidity: '88%',
        airflow: '110 m³/h',
        doorOpenTime: '25 min',
        optimalTemp: '2°C to 6°C',
        optimalHumidity: '85% to 95%',
        optimalAirflow: '100 to 150 m³/h',
        inventory: [
            { product: 'Lettuce', batchId: 'LT-0524', quantity: '320 kg', harvestDate: '2025-04-15', freshness: 'Excellent' },
            { product: 'Kale', batchId: 'KL-0317', quantity: '280 kg', harvestDate: '2025-04-14', freshness: 'Excellent' },
            { product: 'Celery', batchId: 'CL-0489', quantity: '350 kg', harvestDate: '2025-04-12', freshness: 'Good' },
            { product: 'Strawberries', batchId: 'SB-0723', quantity: '450 kg', harvestDate: '2025-04-16', freshness: 'Excellent' },
            { product: 'Blueberries', batchId: 'BB-0211', quantity: '280 kg', harvestDate: '2025-04-15', freshness: 'Good' }
        ]
    },
    'freezer-1': {
        name: 'Freezer 1',
        type: 'Deep Freeze',
        size: '25 m²',
        capacity: '2,500 kg',
        currentLoad: '1,750 kg (70%)',
        lastMaintenance: '2025-02-15',
        temperature: '-15.2°C',
        humidity: '90%',
        airflow: '80 m³/h',
        doorOpenTime: '15 min',
        optimalTemp: '-18°C to -22°C',
        optimalHumidity: 'N/A',
        optimalAirflow: '70 to 90 m³/h',
        inventory: [
            { product: 'Frozen Berries', batchId: 'FB-0128', quantity: '620 kg', harvestDate: '2025-01-15', freshness: 'Preserved' },
            { product: 'Frozen Peas', batchId: 'FP-0315', quantity: '480 kg', harvestDate: '2025-02-10', freshness: 'Preserved' },
            { product: 'Frozen Corn', batchId: 'FC-0242', quantity: '550 kg', harvestDate: '2025-01-30', freshness: 'Preserved' }
        ]
    },
    'root-cellar-a': {
        name: 'Root Cellar A',
        type: 'Cool Storage',
        size: '35 m²',
        capacity: '7,000 kg',
        currentLoad: '5,600 kg (80%)',
        lastMaintenance: '2025-03-10',
        temperature: '12.5°C',
        humidity: '85%',
        airflow: '60 m³/h',
        doorOpenTime: '30 min',
        optimalTemp: '10°C to 15°C',
        optimalHumidity: '80% to 90%',
        optimalAirflow: '50 to 70 m³/h',
        inventory: [
            { product: 'Potatoes', batchId: 'PT-0827', quantity: '1,800 kg', harvestDate: '2025-03-25', freshness: 'Good' },
            { product: 'Onions', batchId: 'ON-0634', quantity: '1,200 kg', harvestDate: '2025-03-20', freshness: 'Good' },
            { product: 'Sweet Potatoes', batchId: 'SP-0417', quantity: '950 kg', harvestDate: '2025-03-28', freshness: 'Excellent' },
            { product: 'Turnips', batchId: 'TN-0215', quantity: '780 kg', harvestDate: '2025-04-01', freshness: 'Good' },
            { product: 'Garlic', batchId: 'GR-0127', quantity: '320 kg', harvestDate: '2025-03-15', freshness: 'Good' }
        ]
    },
    'room-temp-d': {
        name: 'Room Temp D',
        type: 'Ambient',
        size: '30 m²',
        capacity: '3,000 kg',
        currentLoad: '2,100 kg (70%)',
        lastMaintenance: '2025-03-05',
        temperature: '20.1°C',
        humidity: '55%',
        airflow: '75 m³/h',
        doorOpenTime: '50 min',
        optimalTemp: '18°C to 22°C',
        optimalHumidity: '50% to 60%',
        optimalAirflow: '70 to 90 m³/h',
        inventory: [
            { product: 'Tomatoes', batchId: 'TM-0923', quantity: '850 kg', harvestDate: '2025-04-12', freshness: 'Good' },
            { product: 'Cucumbers', batchId: 'CC-0718', quantity: '620 kg', harvestDate: '2025-04-14', freshness: 'Good' },
            { product: 'Bell Peppers', batchId: 'BP-0512', quantity: '480 kg', harvestDate: '2025-04-13', freshness: 'Excellent' }
        ]
    },
    'dry-storage': {
        name: 'Dry Storage',
        type: 'Low Humidity',
        size: '40 m²',
        capacity: '6,000 kg',
        currentLoad: '4,800 kg (80%)',
        lastMaintenance: '2025-02-28',
        temperature: '22.0°C',
        humidity: '30%',
        airflow: '50 m³/h',
        doorOpenTime: '45 min',
        optimalTemp: '20°C to 25°C',
        optimalHumidity: '25% to 35%',
        optimalAirflow: '40 to 60 m³/h',
        inventory: [
            { product: 'Rice', batchId: 'RC-0421', quantity: '1,200 kg', harvestDate: '2024-09-15', freshness: 'Good' },
            { product: 'Beans', batchId: 'BN-0319', quantity: '950 kg', harvestDate: '2024-10-20', freshness: 'Good' },
            { product: 'Lentils', batchId: 'LN-0217', quantity: '780 kg', harvestDate: '2024-11-10', freshness: 'Good' },
            { product: 'Oats', batchId: 'OT-0128', quantity: '850 kg', harvestDate: '2024-09-25', freshness: 'Good' },
            { product: 'Nuts', batchId: 'NT-0517', quantity: '620 kg', harvestDate: '2024-12-05', freshness: 'Good' }
        ]
    }
};

// Handle storage zone selection
let selectedZone = null;

storageZones.forEach(zone => {
    zone.addEventListener('click', () => {
        const zoneId = zone.getAttribute('data-zone');
        selectZone(zoneId);
    });
});

function selectZone(zoneId) {
    // Highlight selected zone
    storageZones.forEach(z => z.classList.remove('selected'));
    document.querySelector(`[data-zone="${zoneId}"]`).classList.add('selected');
    
    // Update selected zone data
    selectedZone = zoneId;
    updateZoneDetails(zoneId);
}

function updateZoneDetails(zoneId) {
    const zone = zoneData[zoneId];
    
    // Update zone info
    document.getElementById('selected-zone-title').textContent = zone.name;
    document.getElementById('zone-type').textContent = zone.type;
    document.getElementById('zone-size').textContent = zone.size;
    document.getElementById('zone-capacity').textContent = zone.capacity;
    document.getElementById('zone-load').textContent = zone.currentLoad;
    document.getElementById('zone-maintenance').textContent = zone.lastMaintenance;
    
    // Update readings
    document.getElementById('current-temp').textContent = zone.temperature;
    document.getElementById('current-humidity').textContent = zone.humidity;
    document.getElementById('current-airflow').textContent = zone.airflow;
    document.getElementById('door-open-time').textContent = zone.doorOpenTime;
    
    document.getElementById('optimal-temp').textContent = `Optimal: ${zone.optimalTemp}`;
    document.getElementById('optimal-humidity').textContent = `Optimal: ${zone.optimalHumidity}`;
    document.getElementById('optimal-airflow').textContent = `Optimal: ${zone.optimalAirflow}`;
    
    // Update inventory table
    const inventoryTable = document.getElementById('zone-inventory-table');
    const tableBody = inventoryTable.querySelector('tbody');
    tableBody.innerHTML = '';
    
    zone.inventory.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.product}</td>
            <td>${item.batchId}</td>
            <td>${item.quantity}</td>
            <td>${item.harvestDate}</td>
            <td>${item.freshness}</td>
        `;
        tableBody.appendChild(row);
    });
    
    // Update modal
    document.getElementById('modal-zone-title').textContent = zone.name;
}

// Handle threshold modal
function openThresholdsModal() {
    if (!selectedZone) {
        alert('Please select a storage zone first.');
        return;
    }
    
    thresholdsModal.style.display = 'flex';
    
    // Parse and set current thresholds
    const zone = zoneData[selectedZone];
    const tempRange = zone.optimalTemp.match(/(-?\d+(\.\d+)?)/g);
    document.getElementById('temp-min').value = tempRange[0];
    document.getElementById('temp-max').value = tempRange[1];
    
    const humidityRange = zone.optimalHumidity.match(/(\d+)/g);
    if (humidityRange) {
        document.getElementById('humidity-min').value = humidityRange[0];
        document.getElementById('humidity-max').value = humidityRange[1];
    }
    
    const airflowRange = zone.optimalAirflow.match(/(\d+)/g);
    document.getElementById('airflow-min').value = airflowRange[0];
    document.getElementById('airflow-max').value = airflowRange[1];
    
    document.getElementById('door-open-max').value = zone.doorOpenTime.match(/(\d+)/)[0];
}

function closeThresholdsModal() {
    thresholdsModal.style.display = 'none';
}

function saveThresholds() {
    const tempMin = document.getElementById('temp-min').value;
    const tempMax = document.getElementById('temp-max').value;
    const humidityMin = document.getElementById('humidity-min').value;
    const humidityMax = document.getElementById('humidity-max').value;
    const airflowMin = document.getElementById('airflow-min').value;
    const airflowMax = document.getElementById('airflow-max').value;
    const doorOpenMax = document.getElementById('door-open-max').value;
    
    // Update zone data
    const zone = zoneData[selectedZone];
    zone.optimalTemp = `${tempMin}°C to ${tempMax}°C`;
    zone.optimalHumidity = `${humidityMin}% to ${humidityMax}%`;
    zone.optimalAirflow = `${airflowMin} to ${airflowMax} m³/h`;
    
    // Update displayed values
    document.getElementById('optimal-temp').textContent = `Optimal: ${zone.optimalTemp}`;
    document.getElementById('optimal-humidity').textContent = `Optimal: ${zone.optimalHumidity}`;
    document.getElementById('optimal-airflow').textContent = `Optimal: ${zone.optimalAirflow}`;
    
    // Show success message
    alert(`Thresholds updated for ${zone.name}`);
    closeThresholdsModal();
}

// Bind modal buttons
editThresholdsBtn.addEventListener('click', openThresholdsModal);
closeModalBtn.addEventListener('click', closeThresholdsModal);
cancelThresholdsBtn.addEventListener('click', closeThresholdsModal);
saveThresholdsBtn.addEventListener('click', saveThresholds);

// Filter alerts by priority
alertPriorityFilter.addEventListener('change', function() {
    const priority = this.value;
    const alertItems = document.querySelectorAll('.alert-item');
    
    alertItems.forEach(item => {
        if (priority === 'all' || item.classList.contains(priority)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});

// Initialize charts
function initCharts() {
    // Temperature chart
    const tempCtx = document.getElementById('temperatureChart').getContext('2d');
    const temperatureChart = new Chart(tempCtx, {
        type: 'line',
        data: {
            labels: generateTimeLabels(24),
            datasets: [
                {
                    label: 'Cold Storage A',
                    data: generateTemperatureData(4.2, 0.5, 24),
                    borderColor: '#43a047',
                    backgroundColor: 'rgba(67, 160, 71, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Ripening Room B',
                    data: generateTemperatureData(16.8, 0.8, 24),
                    borderColor: '#f9a825',
                    backgroundColor: 'rgba(249, 168, 37, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Freezer 1',
                    data: generateTemperatureData(-15.2, 1.2, 24),
                    borderColor: '#e53935',
                    backgroundColor: 'rgba(229, 57, 53, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Temperature (°C)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Time'
                    }
                }
            }
        }
    });
    
    // Humidity chart
    const humidityCtx = document.getElementById('humidityChart').getContext('2d');
    const humidityChart = new Chart(humidityCtx, {
        type: 'line',
        data: {
            labels: generateTimeLabels(24),
            datasets: [
                {
                    label: 'Cold Storage A',
                    data: generateHumidityData(82, 3, 24),
                    borderColor: '#43a047',
                    backgroundColor: 'rgba(67, 160, 71, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Ripening Room B',
                    data: generateHumidityData(72, 4, 24),
                    borderColor: '#f9a825',
                    backgroundColor: 'rgba(249, 168, 37, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Freezer 1',
                    data: generateHumidityData(90, 2, 24),
                    borderColor: '#e53935',
                    backgroundColor: 'rgba(229, 57, 53, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Humidity (%)'
                    },
                    min: 0,
                    max: 100
                },
                x: {
                    title: {
                        display: true,
                        text: 'Time'
                    }
                }
            }
        }
    });
    
    // Handle time range changes
    tempTimeRange.addEventListener('change', () => {
        const hours = parseInt(tempTimeRange.value);
        temperatureChart.data.labels = generateTimeLabels(hours);
        temperatureChart.data.datasets.forEach((dataset, index) => {
            let baseValue = index === 0 ? 4.2 : (index === 1 ? 16.8 : -15.2);
            let variation = index === 0 ? 0.5 : (index === 1 ? 0.8 : 1.2);
            dataset.data = generateTemperatureData(baseValue, variation, hours);
        });
        temperatureChart.update();
    });
    
    humidityTimeRange.addEventListener('change', () => {
        const hours = parseInt(humidityTimeRange.value);
        humidityChart.data.labels = generateTimeLabels(hours);
        humidityChart.data.datasets.forEach((dataset, index) => {
            let baseValue = index === 0 ? 82 : (index === 1 ? 72 : 90);
            let variation = index === 0 ? 3 : (index === 1 ? 4 : 2);
            dataset.data = generateHumidityData(baseValue, variation, hours);
        });
        humidityChart.update();
    });
}

// Generate time labels (e.g., "10:00", "11:00", etc.)
function generateTimeLabels(hours) {
    const labels = [];
    const now = new Date();
    
    for (let i = hours; i >= 0; i--) {
        const time = new Date(now.getTime() - i * 60 * 60 * 1000);
        labels.push(time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
    }
    
    return labels;
}

// Generate random temperature data points
function generateTemperatureData(baseValue, variation, count) {
    const data = [];
    for (let i = 0; i <= count; i++) {
        data.push(baseValue + (Math.random() * variation * 2 - variation));
    }
    return data;
}

// Generate random humidity data points
function generateHumidityData(baseValue, variation, count) {
    const data = [];
    for (let i = 0; i <= count; i++) {
        let value = baseValue + (Math.random() * variation * 2 - variation);
        // Keep humidity values between 0-100%
        value = Math.max(0, Math.min(100, value));
        data.push(value);
    }
    return data;
}

// Initialize the app
document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    // Select Cold Storage A by default
    selectZone('cold-storage-a');
    
    // Simulate refreshing data
    setInterval(() => {
        const selectedRefreshRate = parseInt(document.getElementById('refresh-rate').value);
        console.log(`Data refreshed (interval: ${selectedRefreshRate}s)`);
        // Here you would fetch fresh data from your API
    }, 5000); // Just for demo purposes, refresh logging every 5s
});