// DOM Elements
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebar-toggle');
const toggleIcon = document.getElementById('toggle-icon');
const mainContent = document.getElementById('main-content');
const filterToggleBtn = document.getElementById('filter-toggle-btn');
const filterControls = document.getElementById('filter-controls');
const addInventoryBtn = document.getElementById('add-inventory-btn');
const inventoryTable = document.getElementById('inventory-table');
const pagination = document.getElementById('pagination');
const modalBackdrop = document.getElementById('inventory-modal-backdrop');
const modalClose = document.getElementById('modal-close');
const modalTitle = document.getElementById('modal-title');
const inventoryForm = document.getElementById('inventory-form');
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-btn');
const categoryFilter = document.getElementById('category-filter');
const statusFilter = document.getElementById('status-filter');
const supplierFilter = document.getElementById('supplier-filter');
const logoutLink = document.getElementById('logout-link');
const userDropdown = document.getElementById('user-dropdown');

// Sample inventory data
let inventoryData = [
{
id: 1,
name: 'Organic Strawberries',
category: 'fruits',
quantity: 250.5,
location: 'cold-storage-1',
harvestDate: '2025-04-10',
expiryDate: '2025-04-17',
supplier: 'farmfresh',
price: 4.99,
notes: 'Premium quality, locally sourced.',
image: '/api/placeholder/40/40'
},
{
id: 2,
name: 'Kale Bunches',
category: 'vegetables',
quantity: 120.0,
location: 'cold-storage-2',
harvestDate: '2025-04-12',
expiryDate: '2025-04-19',
supplier: 'greenharvest',
price: 2.49,
notes: 'Organic, pesticide-free.',
image: '/api/placeholder/40/40'
},
{
id: 3,
name: 'Grass-Fed Ground Beef',
category: 'meat',
quantity: 80.5,
location: 'freezer-1',
harvestDate: '2025-04-08',
expiryDate: '2025-05-08',
supplier: 'localfarms',
price: 7.99,
notes: 'Vacuum sealed for freshness.',
image: '/api/placeholder/40/40'
},
{
id: 4,
name: 'Fresh Whole Milk',
category: 'dairy',
quantity: 45.0,
location: 'cold-storage-1',
harvestDate: '2025-04-13',
expiryDate: '2025-04-18',
supplier: 'organicvalley',
price: 3.29,
notes: 'Non-homogenized, pasteurized.',
image: '/api/placeholder/40/40'
},
{
id: 5,
name: 'Atlantic Salmon Fillets',
category: 'seafood',
quantity: 35.2,
location: 'freezer-2',
harvestDate: '2025-04-11',
expiryDate: '2025-04-25',
supplier: 'localfarms',
price: 12.99,
notes: 'Wild-caught, sustainably sourced.',
image: '/api/placeholder/40/40'
},
{
id: 6,
name: 'Organic Carrots',
category: 'vegetables',
quantity: 180.0,
location: 'cold-storage-2',
harvestDate: '2025-04-09',
expiryDate: '2025-04-30',
supplier: 'farmfresh',
price: 1.79,
notes: 'Freshly harvested, unwashed.',
image: '/api/placeholder/40/40'
},
{
id: 7,
name: 'Red Delicious Apples',
category: 'fruits',
quantity: 320.5,
location: 'cold-storage-1',
harvestDate: '2025-04-05',
expiryDate: '2025-04-26',
supplier: 'greenharvest',
price: 2.29,
notes: 'Medium sized, premium quality.',
image: '/api/placeholder/40/40'
},
{
id: 8,
name: 'Greek Yogurt',
category: 'dairy',
quantity: 15.5,
location: 'cold-storage-1',
harvestDate: '2025-04-12',
expiryDate: '2025-04-16',
supplier: 'organicvalley',
price: 4.49,
notes: 'Plain, full-fat variety.',
image: '/api/placeholder/40/40'
},
{
id: 9,
name: 'Chicken Thighs',
category: 'meat',
quantity: 60.0,
location: 'freezer-1',
harvestDate: '2025-04-10',
expiryDate: '2025-05-10',
supplier: 'localfarms',
price: 5.99,
notes: 'Free-range, antibiotic-free.',
image: '/api/placeholder/40/40'
},
{
id: 10,
name: 'Fresh Shrimp',
category: 'seafood',
quantity: 12.5,
location: 'freezer-2',
harvestDate: '2025-04-11',
expiryDate: '2025-04-16',
supplier: 'localfarms',
price: 15.99,
notes: 'Medium-sized, deveined.',
image: '/api/placeholder/40/40'
},
{
id: 11,
name: 'Organic Spinach',
category: 'vegetables',
quantity: 85.0,
location: 'cold-storage-2',
harvestDate: '2025-04-12',
expiryDate: '2025-04-17',
supplier: 'greenharvest',
price: 3.49,
notes: 'Pre-washed, ready to eat.',
image: '/api/placeholder/40/40'
},
{
id: 12,
name: 'Bananas',
category: 'fruits',
quantity: 175.0,
location: 'dry-storage',
harvestDate: '2025-04-08',
expiryDate: '2025-04-18',
supplier: 'farmfresh',
price: 0.79,
notes: 'Premium Cavendish variety.',
image: '/api/placeholder/40/40'
}
];

// Pagination settings
let currentPage = 1;
const itemsPerPage = 5;

// Sidebar toggle functionality
sidebarToggle.addEventListener('click', () => {
sidebar.classList.toggle('collapsed');
mainContent.classList.toggle('expanded');
sidebarToggle.classList.toggle('collapsed');

if (sidebar.classList.contains('collapsed')) {
toggleIcon.classList.remove('fa-chevron-left');
toggleIcon.classList.add('fa-chevron-right');
} else {
toggleIcon.classList.remove('fa-chevron-right');
toggleIcon.classList.add('fa-chevron-left');
}
});

// Filter controls toggle
filterToggleBtn.addEventListener('click', () => {
filterControls.style.display = filterControls.style.display === 'none' ? 'flex' : 'none';
});

// Add inventory button click
addInventoryBtn.addEventListener('click', () => {
modalTitle.textContent = 'Add New Inventory';
resetForm();
modalBackdrop.classList.add('active');

// Set default dates
const today = new Date().toISOString().split('T')[0];
document.getElementById('item-harvest-date').value = today;

const nextWeek = new Date();
nextWeek.setDate(nextWeek.getDate() + 7);
document.getElementById('item-expiry-date').value = nextWeek.toISOString().split('T')[0];
});

// Modal close button
modalClose.addEventListener('click', () => {
modalBackdrop.classList.remove('active');
});

// Close modal when clicking outside
modalBackdrop.addEventListener('click', (e) => {
if (e.target === modalBackdrop) {
modalBackdrop.classList.remove('active');
}
});

// Form submission
inventoryForm.addEventListener('submit', (e) => {
e.preventDefault();

const id = document.getElementById('inventory-id').value;
const name = document.getElementById('item-name').value;
const category = document.getElementById('item-category').value;
const quantity = parseFloat(document.getElementById('item-quantity').value);
const location = document.getElementById('item-location').value;
const harvestDate = document.getElementById('item-harvest-date').value;
const expiryDate = document.getElementById('item-expiry-date').value;
const supplier = document.getElementById('item-supplier').value;
const price = parseFloat(document.getElementById('item-price').value);
const notes = document.getElementById('item-notes').value;

// If id exists, update existing item
if (id) {
const index = inventoryData.findIndex(item => item.id.toString() === id);
if (index !== -1) {
    inventoryData[index] = {
        ...inventoryData[index],
        name,
        category,
        quantity,
        location,
        harvestDate,
        expiryDate,
        supplier,
        price,
        notes
    };
}
} else {
// Add new inventory item
const newId = inventoryData.length > 0 ? Math.max(...inventoryData.map(item => item.id)) + 1 : 1;

inventoryData.push({
    id: newId,
    name,
    category,
    quantity,
    location,
    harvestDate,
    expiryDate,
    supplier,
    price,
    notes,
    image: '/api/placeholder/40/40'
});
}

// Close modal and refresh table
modalBackdrop.classList.remove('active');
filterInventory();

// Show success toast
showToast(id ? 'Inventory updated successfully!' : 'New inventory added successfully!');
});

// Filter functionality
function filterInventory() {
const categoryValue = categoryFilter.value;
const statusValue = statusFilter.value;
const supplierValue = supplierFilter.value;
const searchValue = searchInput.value.toLowerCase();

let filteredData = [...inventoryData];

// Apply category filter
if (categoryValue) {
filteredData = filteredData.filter(item => item.category === categoryValue);
}

// Apply supplier filter
if (supplierValue) {
filteredData = filteredData.filter(item => item.supplier === supplierValue);
}

// Apply search
if (searchValue) {
filteredData = filteredData.filter(item => 
    item.name.toLowerCase().includes(searchValue) ||
    item.category.toLowerCase().includes(searchValue) ||
    item.supplier.toLowerCase().includes(searchValue) ||
    item.notes.toLowerCase().includes(searchValue)
);
}

// Apply status filter
if (statusValue) {
const today = new Date();

switch (statusValue) {
    case 'fresh':
        filteredData = filteredData.filter(item => {
            const harvestDate = new Date(item.harvestDate);
            const daysSinceHarvest = Math.floor((today - harvestDate) / (1000 * 60 * 60 * 24));
            return daysSinceHarvest <= 2;
        });
        break;
        
    case 'good':
        filteredData = filteredData.filter(item => {
            const harvestDate = new Date(item.harvestDate);
            const expiryDate = new Date(item.expiryDate);
            const daysSinceHarvest = Math.floor((today - harvestDate) / (1000 * 60 * 60 * 24));
            const daysUntilExpiry = Math.floor((expiryDate - today) / (1000 * 60 * 60 * 24));
            return daysSinceHarvest > 2 && daysUntilExpiry > 3;
        });
        break;
        
    case 'expiring':
        filteredData = filteredData.filter(item => {
            const expiryDate = new Date(item.expiryDate);
            const daysUntilExpiry = Math.floor((expiryDate - today) / (1000 * 60 * 60 * 24));
            return daysUntilExpiry >= 0 && daysUntilExpiry <= 3;
        });
        break;
        
    case 'expired':
        filteredData = filteredData.filter(item => {
            const expiryDate = new Date(item.expiryDate);
            return expiryDate < today;
        });
        break;
        
    case 'low':
        filteredData = filteredData.filter(item => item.quantity < 20);
        break;
}
}

renderTable(filteredData);
}

// Search button click handler
searchBtn.addEventListener('click', filterInventory);
searchInput.addEventListener('keyup', (e) => {
if (e.key === 'Enter') {
filterInventory();
}
});

// Filter change event handlers
categoryFilter.addEventListener('change', filterInventory);
statusFilter.addEventListener('change', filterInventory);
supplierFilter.addEventListener('change', filterInventory);

// Render table with data
function renderTable(data) {
// Calculate total pages
const totalPages = Math.ceil(data.length / itemsPerPage);

// Adjust current page if it exceeds the new total
if (currentPage > totalPages) {
currentPage = totalPages > 0 ? totalPages : 1;
}

// Get current page data
const start = (currentPage - 1) * itemsPerPage;
const end = start + itemsPerPage;
const currentPageData = data.slice(start, end);

// Clear existing table rows
const tableBody = inventoryTable.querySelector('tbody');
tableBody.innerHTML = '';

if (currentPageData.length === 0) {
const emptyRow = document.createElement('tr');
emptyRow.innerHTML = `
    <td colspan="7" style="text-align: center; padding: 2rem;">
        <i class="fas fa-search" style="font-size: 2rem; color: #ddd; margin-bottom: 1rem; display: block;"></i>
        <p>No inventory items found. Try adjusting your filters.</p>
    </td>
`;
tableBody.appendChild(emptyRow);
} else {
// Add data rows
currentPageData.forEach(item => {
    const row = document.createElement('tr');
    
    // Calculate status
    const today = new Date();
    const expiryDate = new Date(item.expiryDate);
    const daysUntilExpiry = Math.floor((expiryDate - today) / (1000 * 60 * 60 * 24));
    const harvestDate = new Date(item.harvestDate);
    const daysSinceHarvest = Math.floor((today - harvestDate) / (1000 * 60 * 60 * 24));
    
    let status, statusClass;
    
    if (daysUntilExpiry < 0) {
        status = 'Expired';
        statusClass = 'status-expired';
    } else if (daysUntilExpiry <= 3) {
        status = 'Expiring Soon';
        statusClass = 'status-expiring';
    } else if (daysSinceHarvest <= 2) {
        status = 'Fresh';
        statusClass = 'status-fresh';
    } else {
        status = 'Good';
        statusClass = 'status-good';
    }
    
    if (item.quantity < 20) {
        status = 'Low Stock';
        statusClass = 'status-low';
    }
    
    row.innerHTML = `
        <td>
            <div class="item-name">
                <img src="${item.image}" alt="${item.name}" class="item-image">
                <div class="item-details">
                    <h4>${item.name}</h4>
                    <p>$${item.price.toFixed(2)}/kg</p>
                </div>
            </div>
        </td>
        <td>${getCategoryName(item.category)}</td>
        <td>${item.quantity.toFixed(1)}</td>
        <td>${getLocationName(item.location)}</td>
        <td>${formatDate(item.expiryDate)}</td>
        <td><span class="status-badge ${statusClass}">${status}</span></td>
        <td>
            <div class="table-actions">
                <button class="action-btn edit" data-id="${item.id}" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="action-btn delete" data-id="${item.id}" title="Delete">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </td>
    `;
    
    tableBody.appendChild(row);
});
}

// Render pagination
renderPagination(totalPages);

// Add event listeners to edit and delete buttons
document.querySelectorAll('.action-btn.edit').forEach(btn => {
btn.addEventListener('click', () => {
    const id = btn.getAttribute('data-id');
    editInventoryItem(id);
});
});

document.querySelectorAll('.action-btn.delete').forEach(btn => {
btn.addEventListener('click', () => {
    const id = btn.getAttribute('data-id');
    deleteInventoryItem(id);
});
});
}

// Render pagination controls
function renderPagination(totalPages) {
pagination.innerHTML = '';

if (totalPages <= 1) {
return;
}

// Previous button
const prevBtn = document.createElement('button');
prevBtn.className = `page-btn ${currentPage === 1 ? 'disabled' : ''}`;
prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
prevBtn.disabled = currentPage === 1;
prevBtn.addEventListener('click', () => {
if (currentPage > 1) {
    currentPage--;
    filterInventory();
}
});
pagination.appendChild(prevBtn);

// Page buttons
let startPage = Math.max(1, currentPage - 2);
let endPage = Math.min(totalPages, startPage + 4);

if (endPage - startPage < 4) {
startPage = Math.max(1, endPage - 4);
}

for (let i = startPage; i <= endPage; i++) {
const pageBtn = document.createElement('button');
pageBtn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
pageBtn.textContent = i;
pageBtn.addEventListener('click', () => {
    currentPage = i;
    filterInventory();
});
pagination.appendChild(pageBtn);
}

// Next button
const nextBtn = document.createElement('button');
nextBtn.className = `page-btn ${currentPage === totalPages ? 'disabled' : ''}`;
nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
nextBtn.disabled = currentPage === totalPages;
nextBtn.addEventListener('click', () => {
if (currentPage < totalPages) {
    currentPage++;
    filterInventory();
}
});
pagination.appendChild(nextBtn);
}

// Edit inventory item
function editInventoryItem(id) {
const item = inventoryData.find(item => item.id.toString() === id);

if (item) {
modalTitle.textContent = 'Edit Inventory Item';

// Populate form fields
document.getElementById('inventory-id').value = item.id;
document.getElementById('item-name').value = item.name;
document.getElementById('item-category').value = item.category;
document.getElementById('item-quantity').value = item.quantity;
document.getElementById('item-price').value = item.price;
document.getElementById('item-location').value = item.location;
document.getElementById('item-supplier').value = item.supplier;
document.getElementById('item-harvest-date').value = item.harvestDate;
document.getElementById('item-expiry-date').value = item.expiryDate;
document.getElementById('item-notes').value = item.notes;

// Show modal
modalBackdrop.classList.add('active');
}
}

// Delete inventory item
function deleteInventoryItem(id) {
if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
inventoryData = inventoryData.filter(item => item.id.toString() !== id);
filterInventory();
showToast('Inventory item deleted successfully!');
}
}

// Reset form fields
function resetForm() {
document.getElementById('inventory-id').value = '';
document.getElementById('item-name').value = '';
document.getElementById('item-category').value = '';
document.getElementById('item-quantity').value = '';
document.getElementById('item-price').value = '';
document.getElementById('item-location').value = '';
document.getElementById('item-supplier').value = '';
document.getElementById('item-harvest-date').value = '';
document.getElementById('item-expiry-date').value = '';
document.getElementById('item-notes').value = '';
}

// Helper function to get category display name
function getCategoryName(category) {
const categories = {
'fruits': 'Fruits',
'vegetables': 'Vegetables',
'dairy': 'Dairy',
'meat': 'Meat',
'seafood': 'Seafood'
};

return categories[category] || category;
}

// Helper function to get location display name
function getLocationName(location) {
const locations = {
'cold-storage-1': 'Cold Storage 1',
'cold-storage-2': 'Cold Storage 2',
'dry-storage': 'Dry Storage',
'freezer-1': 'Freezer 1',
'freezer-2': 'Freezer 2'
};

return locations[location] || location;
}

// Format date for display
function formatDate(dateString) {
const date = new Date(dateString);
return new Intl.DateTimeFormat('en-US', { 
month: 'short', 
day: 'numeric', 
year: 'numeric' 
}).format(date);
}

// Show toast notification
function showToast(message) {
// Create toast element if it doesn't exist
let toast = document.getElementById('toast-notification');

if (!toast) {
toast = document.createElement('div');
toast.id = 'toast-notification';
toast.style.position = 'fixed';
toast.style.bottom = '20px';
toast.style.right = '20px';
toast.style.backgroundColor = '#4CAF50';
toast.style.color = 'white';
toast.style.padding = '12px 20px';
toast.style.borderRadius = '4px';
toast.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
toast.style.zIndex = '1000';
toast.style.transition = 'all 0.3s ease';
toast.style.transform = 'translateY(100px)';
toast.style.opacity = '0';
document.body.appendChild(toast);
}

// Set message and show toast
toast.textContent = message;
toast.style.transform = 'translateY(0)';
toast.style.opacity = '1';

// Hide toast after 3 seconds
setTimeout(() => {
toast.style.transform = 'translateY(100px)';
toast.style.opacity = '0';
}, 3000);
}

// Logout functionality
logoutLink.addEventListener('click', (e) => {
e.preventDefault();
if (confirm('Are you sure you want to log out?')) {
// In a real application, this would handle actual logout
showToast('Logged out successfully!');
setTimeout(() => {
    window.location.href = '#'; // Would redirect to login page
}, 1500);
}
});

// User dropdown (placeholder)
userDropdown.addEventListener('click', () => {
alert('User profile functionality would be implemented here.');
});

// Initialize the application
function initialize() {
// Set default filter controls display
filterControls.style.display = 'flex';

// Initial table render
filterInventory();
}

// Start the app
initialize();