<?php
// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "greenshield";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Before your HTML starts, add this PHP code to prepare sales chart data
$sales_data = [];
$sales_result = $conn->query("
    SELECT 
        DATE_FORMAT(sale_date, '%m/%d/%Y') as date,
        SUM(total_amount) as total_sales,
        SUM(quantity_kg) as total_quantity
    FROM sales_records
    GROUP BY DATE(sale_date)
    ORDER BY sale_date
");

while ($row = $sales_result->fetch_assoc()) {
    $sales_data[] = [
        'date' => $row['date'],
        'amount' => (float)$row['total_sales'],
        'quantity' => (float)$row['total_quantity']
    ];
}

// Prepare sales by product data
$product_sales = [];
$product_result = $conn->query("
    SELECT 
        product_name,
        SUM(total_amount) as total_sales,
        SUM(quantity_kg) as total_quantity
    FROM sales_records
    GROUP BY product_name
");

while ($row = $product_result->fetch_assoc()) {
    $product_sales[] = [
        'product' => $row['product_name'],
        'amount' => (float)$row['total_sales'],
        'quantity' => (float)$row['total_quantity']
    ];
}

// Before your HTML starts, add this PHP code to prepare chart data
$loss_data = [];
$result = $conn->query("
    SELECT 
        DATE_FORMAT(loss_date, '%m/%d/%Y') as date,
        SUM(quantity_kg) as total_quantity
    FROM loss_records
    GROUP BY DATE(loss_date)
    ORDER BY loss_date
");

while ($row = $result->fetch_assoc()) {
    $loss_data[] = [
        'date' => $row['date'],
        'quantity' => (float)$row['total_quantity']
    ];
}

// Add Sales Record
if (isset($_POST['add_sales'])) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Sanitize inputs
        $batch_id = mysqli_real_escape_string($conn, $_POST['batch_id']);
        $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
        $quantity_kg = (float)$_POST['quantity_kg'];
        $price_per_kg = (float)$_POST['price_per_kg'];
        $total_amount = $quantity_kg * $price_per_kg;
        $retailer_id = (int)$_POST['retailer_id'];
        $sale_date = mysqli_real_escape_string($conn, $_POST['sale_date']);
        $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

        // Validate inputs
        $errors = [];
        
        // Check if batch exists and has sufficient quantity
        $batch_check = $conn->query("SELECT quantity_kg FROM harvests WHERE batch_id = '$batch_id'");
        if ($batch_check->num_rows == 0) {
            $errors[] = "Batch ID does not exist";
        } else {
            $batch_data = $batch_check->fetch_assoc();
            $current_quantity = (float)$batch_data['quantity_kg'];
            
            if ($quantity_kg <= 0) {
                $errors[] = "Quantity must be greater than 0";
            } elseif ($quantity_kg > $current_quantity) {
                $errors[] = "Not enough quantity in inventory (Available: $current_quantity kg)";
            }
        }

        if (empty($product_name)) {
            $errors[] = "Product name is required";
        }

        if ($price_per_kg <= 0) {
            $errors[] = "Price per kg must be greater than 0";
        }

        if (empty($retailer_id)) {
            $errors[] = "Retailer is required";
        }

        if (empty($sale_date)) {
            $errors[] = "Sale date is required";
        }

        // If no errors, proceed with insertion
        if (empty($errors)) {
            // 1. Add sales record
            $stmt = $conn->prepare("INSERT INTO sales_records 
                                  (batch_id, product_name, quantity_kg, price_per_kg, total_amount, retailer_id, sale_date, payment_method) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddssss", $batch_id, $product_name, $quantity_kg, $price_per_kg, $total_amount, $retailer_id, $sale_date, $payment_method);
            
            if (!$stmt->execute()) {
                throw new Exception("Error adding sales record: " . $stmt->error);
            }
            $stmt->close();

            // 2. Update inventory quantity
            $update_stmt = $conn->prepare("UPDATE harvests SET quantity_kg = quantity_kg - ? WHERE batch_id = ?");
            $update_stmt->bind_param("ds", $quantity_kg, $batch_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Error updating inventory: " . $update_stmt->error);
            }
            $update_stmt->close();

            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Sales record added successfully and inventory updated!";
        } else {
            $_SESSION['error'] = implode("<br>", $errors);
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Sales Record
if (isset($_POST['update_sales'])) {
    $sale_id = $_POST['sale_id'];
    $product_name = $_POST['product_name'];
    $quantity_kg = $_POST['quantity_kg'];
    $price_per_kg = $_POST['price_per_kg'];
    $total_amount = $quantity_kg * $price_per_kg;
    $retailer_id = $_POST['retailer_id'];
    $sale_date = $_POST['sale_date'];
    $payment_method = $_POST['payment_method'];

    $stmt = $conn->prepare("UPDATE sales_records SET 
                          product_name = ?,
                          quantity_kg = ?, 
                          price_per_kg = ?, 
                          total_amount = ?, 
                          retailer_id = ?, 
                          sale_date = ?, 
                          payment_method = ? 
                          WHERE sale_id = ?");
    $stmt->bind_param("sddssssi", $product_name, $quantity_kg, $price_per_kg, $total_amount, $retailer_id, $sale_date, $payment_method, $sale_id);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// Add Transportation
if (isset($_POST['add_transportation'])) {
    // Get the form data
    $driver_id = $_POST['driver_id'];
    $pickup_date = $_POST['pickup_date'];
    $delivery_date = $_POST['delivery_date'];
    $destination = $_POST['destination'];
    $vehicle = $_POST['vehicle'];
    $delivery_id = $_POST['delivery_id'];

    // Prepare the SQL insert statement
    $stmt = $conn->prepare("INSERT INTO transportation (driver_id, pickup_date, delivery_date, destination, vehicle, delivery_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $driver_id, $pickup_date, $delivery_date, $destination, $vehicle, $delivery_id);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to the same page to refresh the data
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Handle the error if insertion fails
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}





// Add Inventory
if (isset($_POST['add_inventory'])) {
    // Sanitize inputs
    $batch_id = mysqli_real_escape_string($conn, $_POST['batch_id']);
    $produce = mysqli_real_escape_string($conn, $_POST['produce']);
    $harvest_date = mysqli_real_escape_string($conn, $_POST['harvest_date']);
    $quantity_kg = (float)$_POST['quantity_kg'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $farmer_id = mysqli_real_escape_string($conn, $_POST['farmer_id']);

    // Validate
    $errors = [];
    if (empty($batch_id)) $errors[] = "Batch ID is required";
    if (empty($produce)) $errors[] = "Produce is required";
    if (empty($harvest_date)) $errors[] = "Harvest date is required";
    if ($quantity_kg <= 0) $errors[] = "Quantity must be positive";
    if (empty($status)) $errors[] = "Status is required";
    if (empty($farmer_id)) $errors[] = "Farmer ID is required";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO harvests(batch_id, produce, harvest_date, quantity_kg, status, farmer_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdss", $batch_id, $produce, $harvest_date, $quantity_kg, $status, $farmer_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Inventory added successfully!";
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}



// Add Distributor
if (isset($_POST['add_distributor'])) {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("INSERT INTO distributors_list (name, location, contact) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $location, $contact);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Distributor
if (isset($_POST['update_distributor'])) {
    $distributor_id = $_POST['distributor_id'];
    $name = $_POST['name'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("UPDATE distributors_list SET name=?, location=?, contact=? WHERE distributor_id=?");
    $stmt->bind_param("sssi", $name, $location, $contact, $distributor_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete Distributor - Fixed the parameter name to match the database column
if (isset($_POST['delete_distributor'])) {
    $distributor_id = $_POST['distributor_id'];
    
    $stmt = $conn->prepare("DELETE FROM distributors_list WHERE distributor_id = ?");
    $stmt->bind_param("i", $distributor_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}




// Add Retailer
if (isset($_POST['add_retailer'])) {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];
    $recent_orders = $_POST['recent_orders'];

    $stmt = $conn->prepare("INSERT INTO retailers (name, location, contact, recent_orders) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $location, $contact, $recent_orders);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Retailer
if (isset($_POST['update_retailer'])) {
    $retailer_id = $_POST['retailer_id'];
    $name = $_POST['name'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];
    $recent_orders = $_POST['recent_orders'];

    $stmt = $conn->prepare("UPDATE retailers SET name=?, location=?, contact=?, recent_orders=? WHERE retailer_id=?");
    $stmt->bind_param("ssssi", $name, $location, $contact, $recent_orders, $retailer_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// [Keep all your existing PHP code for other functions here...]

$farmers_result = $conn->query("SELECT * FROM farmers");
$harvests_result = $conn->query("SELECT * FROM harvests ORDER BY harvest_date DESC");
$storage_result = $conn->query("SELECT * FROM storage");
$loss_result = $conn->query("SELECT * FROM loss_records ORDER BY loss_date DESC");
$retailers_result = $conn->query("SELECT * FROM retailers");

// Add Farmer
if (isset($_POST['add_farmer'])) {
    $farm_name = $_POST['farm_name'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];
    $status = 'Active';

    $stmt = $conn->prepare("INSERT INTO farmers (Farm_Name, Location, Contact, Status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $farm_name, $location, $contact, $status);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Farmer
if (isset($_POST['update_farmer'])) {
    $farmer_id = $_POST['Farmer_ID'];
    $farm_name = $_POST['Farm_Name'];
    $location = $_POST['Location'];
    $contact = $_POST['Contact'];

    $stmt = $conn->prepare("UPDATE farmers SET Farm_Name=?, Location=?, Contact=? WHERE Farmer_ID=?");
    $stmt->bind_param("sssi", $farm_name, $location, $contact, $farmer_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Add Storage
if (isset($_POST['add_storage'])) {
    $location_name = $_POST['location_name'];
    $current_temp_c = $_POST['current_temp_c'];
    $current_humidity = $_POST['current_humidity'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO storage (location_name, current_temp_c, current_humidity, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdds", $location_name, $current_temp_c, $current_humidity, $status);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}



// Add Loss Record
if (isset($_POST['add_loss'])) {
    $batch_id = $_POST['batch_id'];
    $loss_date = $_POST['loss_date'];
    $quantity_kg = $_POST['quantity_kg'];
    $stage = $_POST['stage'];
    $cause = $_POST['cause'];
    $recorded_by = $_POST['recorded_by'];

    $stmt = $conn->prepare("INSERT INTO loss_records (batch_id, loss_date, quantity_kg, stage, cause, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsss", $batch_id, $loss_date, $quantity_kg, $stage, $cause, $recorded_by);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Loss Record
if (isset($_POST['update_loss'])) {
    $loss_id = $_POST['loss_id'];
    $batch_id = $_POST['batch_id'];
    $loss_date = $_POST['loss_date'];
    $quantity_kg = $_POST['quantity_kg'];
    $stage = $_POST['stage'];
    $cause = $_POST['cause'];
    $recorded_by = $_POST['recorded_by'];

    $stmt = $conn->prepare("UPDATE loss_records SET batch_id=?, loss_date=?, quantity_kg=?, stage=?, cause=?, recorded_by=? WHERE loss_id=?");
    $stmt->bind_param("ssdsssi", $batch_id, $loss_date, $quantity_kg, $stage, $cause, $recorded_by, $loss_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Add Improvement Record
if (isset($_POST['add_improvement'])) {
    $batch_id = $_POST['batch_id'];
    $produce_name = $_POST['produce_name'];
    $farmer_id = $_POST['farmer_id'];
    $description = $_POST['description'];
    $target_issue = $_POST['target_issue'];
    $implementation_date = $_POST['implementation_date'];

    $stmt = $conn->prepare("INSERT INTO improvements (batch_id, produce_name, farmer_id, description, target_issue, implementation_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $batch_id, $produce_name, $farmer_id, $description, $target_issue, $implementation_date);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Improvement Record
if (isset($_POST['update_improvement'])) {
    $improvement_id = $_POST['improvement_id'];
    $batch_id = $_POST['batch_id'];
    $produce_name = $_POST['produce_name'];
    $farmer_id = $_POST['farmer_id'];
    $description = $_POST['description'];
    $target_issue = $_POST['target_issue'];
    $implementation_date = $_POST['implementation_date'];

    $stmt = $conn->prepare("UPDATE improvements SET batch_id=?, produce_name=?, farmer_id=?, description=?, target_issue=?, implementation_date=? WHERE improvement_id=?");
    $stmt->bind_param("ssssssi", $batch_id, $produce_name, $farmer_id, $description, $target_issue, $implementation_date, $improvement_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


$farmers_result = $conn->query("SELECT * FROM farmers");
$harvests_result = $conn->query("SELECT * FROM harvests ORDER BY harvest_date DESC");
$storage_result = $conn->query("SELECT * FROM storage");
$loss_result = $conn->query("SELECT * FROM loss_records ORDER BY loss_date DESC");
$distributors_result = $conn->query("SELECT * FROM distributors_list");
$improvement_result = $conn->query("SELECT * FROM improvements ");
$transportations_result = $conn->query("SELECT * FROM transportation");
$sales_result = $conn->query("SELECT * FROM sales_records")

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 95%;
            margin: 0 auto;

            padding: 20px 0;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tab-container {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 20px;
            padding: 20px;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
        }
        .tab.active {
            border-bottom: 3px solid #4CAF50;
            font-weight: bold;
            color: #4CAF50;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .button.delete {
            background-color: #f44336;
        }
        .button.view {
            background-color: #2196F3;
        }
        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .modal {
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            display: none;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            width: 300px;
            border-radius: 8px;
        }
        .close {
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Warehouse Management System</h1>
        <div>
            <span>Welcome, Warehouse Manager</span>
            <button class="button" style="margin-left: 10px;">Logout</button>
        </div>
    </div>

    <div class="container">
        <div class="tab-container">
            <div class="tabs">
                <button class="tab active" onclick="openTab('inventory')">Inventory</button>
                <button class="tab" onclick="openTab('farmers')">Farmers</button>
                <button class="tab" onclick="openTab('storage')">Storage</button>
                <button class="tab" onclick="openTab('transport')">Transportation</button>
                <button class="tab" onclick="openTab('retailers')">Retailers</button>
                <button class="tab" onclick="openTab('loss')">Loss Records</button>
                <button class="tab" onclick="openTab('distributors')">Distributors</button>
                <button class="tab" onclick="openTab('Sales Records')">Sales Records</button>
                <button class="tab" onclick="openTab('Improvement')">Improvement</button>
                
            </div>

<!-- Inventory Tab -->
<div id="inventory" class="tab-content active">
    <div class="alert">
        <strong>Alert:</strong> 5 batches are expiring within 2 days!
    </div>
    <button class="button" id="addInventoryBtn" style="margin-bottom: 20px;">Add Inventory</button>
    
    <!-- Search Bar -->
    <div class="search-container" style="margin: 20px 0;">
        <input type="text" id="inventorySearch" placeholder="Search inventory..." 
               oninput="searchTable('inventorySearch', 'inventoryTable')"
               style="padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    
    <table id="inventoryTable">
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Produce</th>
                <th>Harvest Date</th>
                <th>Quantity (kg)</th>
                <th>Status</th>
                
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $harvests_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['batch_id']) ?></td>
                    <td><?= htmlspecialchars($row['produce']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['harvest_date'])) ?></td>
                    <td><?= htmlspecialchars($row['quantity_kg']) ?></td>
                    <td>
                        <span class="status-badge <?= strtolower($row['status']) ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    
                    <td>
                        
                        <button class="button delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Inventory Modal -->
<div id="addInventoryModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddInventory">&times;</span>
        <h2>Add New Inventory</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Batch ID -->
            <label for="batch_id">Batch ID:</label>
            <input type="text" id="batch_id" name="batch_id" required
                   value="<?= isset($_POST['batch_id']) ? htmlspecialchars($_POST['batch_id']) : '' ?>">
            
            <!-- Produce -->
            <label for="produce">Produce:</label>
            <select id="produce" name="produce" required>
                <option value="">Select Produce</option>
                <?php
                $produces = ['Tomatoes', 'Potatoes', 'Carrots', 'Onions', 'Lettuce', 'Other'];
                foreach ($produces as $item) {
                    $selected = (isset($_POST['produce']) && $_POST['produce'] === $item) ? 'selected' : '';
                    echo "<option value='$item' $selected>$item</option>";
                }
                ?>
            </select>
            
            <!-- Harvest Date -->
            <label for="harvest_date">Harvest Date:</label>
            <input type="date" id="harvest_date" name="harvest_date" required
                   value="<?= isset($_POST['harvest_date']) ? htmlspecialchars($_POST['harvest_date']) : '' ?>">
            
            <!-- Quantity -->
            <label for="quantity_kg">Quantity (kg):</label>
            <input type="number" id="quantity_kg" name="quantity_kg" min="0" step="0.01" required
                   value="<?= isset($_POST['quantity_kg']) ? htmlspecialchars($_POST['quantity_kg']) : '' ?>">
            
            <!-- Status -->
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="">Select Status</option>
                <?php
                $statuses = ['Fresh', 'Processing', 'Packaged',  'Expired'];
                foreach ($statuses as $state) {
                    $selected = (isset($_POST['status']) && $_POST['status'] === $state) ? 'selected' : '';
                    echo "<option value='$state' $selected>$state</option>";
                }
                ?>
            </select>
            
           
            
            <button type="submit" name="add_inventory" class="button">Add Inventory</button>
        </form>
    </div>
</div>

<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}
.status-badge.fresh { background-color: #d4edda; color: #155724; }
.status-badge.processing { background-color: #fff3cd; color: #856404; }
.status-badge.packaged { background-color: #cce5ff; color: #004085; }
.status-badge.shipped { background-color: #e2e3e5; color: #383d41; }
.status-badge.expired { background-color: #f8d7da; color: #721c24; }
.alert.error { color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; }
.alert.success { color: #155724; background-color: #d4edda; padding: 10px; margin-bottom: 15px; }
</style>

<script>
// Search function
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// Modal handling
document.getElementById('addInventoryBtn').addEventListener('click', function() {
    document.getElementById('addInventoryModal').style.display = 'block';
});

document.getElementById('closeAddInventory').addEventListener('click', function() {
    document.getElementById('addInventoryModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    if (event.target == document.getElementById('addInventoryModal')) {
        document.getElementById('addInventoryModal').style.display = 'none';
    }
});
</script>

           <!-- Farmers Tab -->
<div id="farmers" class="tab-content">
    <button class="button" id="addFarmerBtn" style="margin-bottom: 20px;">Add New Farmer</button>
    
    <!-- Search Bar -->
    <div class="search-container" style="margin: 20px 0;">
        <input type="text" id="farmersSearch" placeholder="Search farmers..." 
               oninput="searchTable('farmersSearch', 'farmersTable')"
               style="padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <table id="farmersTable">
        <thead>
            <tr>
                <th>Farmer ID</th>
                <th>Farm Name</th>
                <th>Location</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $farmers_result->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['Farmer_ID']) ?></td>
                    <td><?= htmlspecialchars($row['Farm_Name']) ?></td>
                    <td><?= htmlspecialchars($row['Location']) ?></td>
                    <td><?= htmlspecialchars($row['Contact']) ?></td>
                    <td>
                        <span class="status-badge <?= strtolower($row['Status']) ?>">
                            <?= htmlspecialchars($row['Status']) ?>
                        </span>
                    </td>
                    <td>
                        <button class="button editFarmerBtn"
                            data-id="<?= $row['Farmer_ID'] ?>"
                            data-name="<?= $row['Farm_Name'] ?>"
                            data-location="<?= $row['Location'] ?>"
                            data-contact="<?= $row['Contact'] ?>"
                            data-status="<?= $row['Status'] ?>">Edit</button>
                        <button class="button delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Farmer Modal -->
<div id="addFarmerModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddFarmer">&times;</span>
        <h2>Add New Farmer</h2>
        
        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert error"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php } ?>
        
        <form method="POST" action="">
            <label>Farm Name:</label>
            <input type="text" name="farm_name" required
                   value="<?php echo isset($_POST['farm_name']) ? htmlspecialchars($_POST['farm_name']) : ''; ?>">
            
            <label>Location:</label>
            <input type="text" name="location" required
                   value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
            
            <label>Contact:</label>
            <input type="text" name="contact" required
                   value="<?php echo isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>">
            
            <label>Status:</label>
            <select name="status" required>
                <option value="Active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                <option value="Pending" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
            </select>
            
            <button type="submit" name="add_farmer" class="button">Add Farmer</button>
        </form>
    </div>
</div>

<!-- Edit Farmer Modal -->
<div id="editFarmerModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditFarmer">&times;</span>
        <h2>Edit Farmer</h2>
        
        <?php if (isset($_SESSION['edit_error'])): ?>
            <div class="alert error"><?= $_SESSION['edit_error'] ?></div>
            <?php unset($_SESSION['edit_error']); ?>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="Farmer_ID" id="editFarmerId">
            
            <label>Farm Name:</label>
            <input type="text" name="Farm_Name" id="editFarmName" required>
            
            <label>Location:</label>
            <input type="text" name="Location" id="editLocation" required>
            
            <label>Contact:</label>
            <input type="text" name="Contact" id="editContact" required>
            
            <label>Status:</label>
            <select name="Status" id="editStatus" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Pending">Pending</option>
            </select>
            
            <button type="submit" name="update_farmer" class="button">Update Farmer</button>
        </form>
    </div>
</div>

<script>
// Search function for farmers table
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        
        for (let j = 0; j < cells.length - 1; j++) { // Skip actions column
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// Edit farmer modal population
document.querySelectorAll('.editFarmerBtn').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('editFarmerId').value = this.dataset.id;
        document.getElementById('editFarmName').value = this.dataset.name;
        document.getElementById('editLocation').value = this.dataset.location;
        document.getElementById('editContact').value = this.dataset.contact;
        document.getElementById('editStatus').value = this.dataset.status;
        document.getElementById('editFarmerModal').style.display = 'block';
    });
});

// Modal open/close handlers
document.getElementById('addFarmerBtn').addEventListener('click', function() {
    document.getElementById('addFarmerModal').style.display = 'block';
});

document.getElementById('closeAddFarmer').addEventListener('click', function() {
    document.getElementById('addFarmerModal').style.display = 'none';
});

document.getElementById('closeEditFarmer').addEventListener('click', function() {
    document.getElementById('editFarmerModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    if (event.target == document.getElementById('addFarmerModal')) {
        document.getElementById('addFarmerModal').style.display = 'none';
    }
    if (event.target == document.getElementById('editFarmerModal')) {
        document.getElementById('editFarmerModal').style.display = 'none';
    }
});
</script>

<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}
.status-badge.active { background-color: #d4edda; color: #155724; }
.status-badge.inactive { background-color: #f8d7da; color: #721c24; }
.status-badge.pending { background-color: #fff3cd; color: #856404; }
.alert.error { color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; }
</style>

           <!-- Storage Tab -->
<div id="storage" class="tab-content">
    <button class="button" id="addStorageBtn" style="margin-bottom: 20px;">Add Storage Unit</button>
    <!-- Search Bar -->
    <div class="search-container" style="margin: 20px 0;">
        <input type="text" id="storageSearch" placeholder="Search storage..." 
               oninput="searchTable('storageSearch', 'storageTable')"
               style="padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <table id="storageTable">
        <thead>
            <tr>
                <th>Storage ID</th>
                <th>Location Name</th>
                <th>Current Temperature</th>
                <th>Current Humidity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $storage_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['storage_id'] ?></td>
                    <td><?= $row['location_name'] ?></td>
                    <td><?= $row['current_temp_c'] ?>°C</td>
                    <td><?= $row['current_humidity'] ?>%</td>
                    <td><?= $row['status'] ?></td>
                    <td>
                        <button class="button delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Storage Modal -->
<div id="addStorageModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddStorage">&times;</span>
        <h2>Add New Storage Unit</h2>
        <form method="POST">
            <label>Location Name:</label><br>
            <input type="text" name="location_name" required><br><br>
            <label>Current Temperature (°C):</label><br>
            <input type="number" step="0.1" name="current_temp_c" required><br><br>
            <label>Current Humidity (%):</label><br>
            <input type="number" step="0.1" name="current_humidity" required><br><br>
            <label>Status:</label><br>
            <input type="text" name="status" required><br><br>
            <button type="submit" name="add_storage" class="button">Add Storage</button>
        </form>
    </div>
</div>

<script>
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        
        // Search through all columns except the last one (Actions column)
        for (let j = 0; j < cells.length - 1; j++) {
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}
</script>


        

           <!-- Loss Records Tab -->
<div id="loss" class="tab-content">
    <button class="button" id="addLossBtn" style="margin-bottom: 20px;">Record New Loss</button>
    <!-- Search Bar -->
    <div class="search-container" style="margin: 20px 0;">
        <input type="text" id="lossSearch" placeholder="Search loss records..." 
               oninput="searchTable('lossSearch', 'lossTable')"
               style="padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
   
    <table id="lossTable">
        <thead>
            <tr>
                <th>Loss ID</th>
                <th>Batch ID</th>
                <th>Date</th>
                <th>Quantity (kg)</th>
                <th>Stage</th>
                <th>Cause</th>
                <th>Recorded By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $loss_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['loss_id'] ?></td>
                    <td><?= $row['batch_id'] ?></td>
                    <td><?= $row['loss_date'] ?></td>
                    <td><?= $row['quantity_kg'] ?></td>
                    <td><?= $row['stage'] ?></td>
                    <td><?= $row['cause'] ?></td>
                    <td><?= $row['recorded_by'] ?></td>
                    <td>
                        <button class="button editLossBtn"
                            data-id="<?= $row['loss_id'] ?>"
                            data-batch="<?= $row['batch_id'] ?>"
                            data-date="<?= $row['loss_date'] ?>"
                            data-quantity="<?= $row['quantity_kg'] ?>"
                            data-stage="<?= $row['stage'] ?>"
                            data-cause="<?= $row['cause'] ?>"
                            data-recorded="<?= $row['recorded_by'] ?>">Edit</button>
                        <button class="button delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <div id="chart" style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"></div>
    <div id="causeChart" style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"></div>
</div>

<!-- Add Loss Record Modal (remains exactly the same) -->
<div id="addLossModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddLoss">&times;</span>
        <h2>Record New Loss</h2>
        <form method="POST">
            <label>Batch ID:</label><br>
            <input type="text" name="batch_id" required><br><br>
            <label>Date:</label><br>
            <input type="date" name="loss_date" required><br><br>
            <label>Quantity (kg):</label><br>
            <input type="number" step="0.1" name="quantity_kg" required><br><br>
            <label>Stage:</label><br>
            <select name="stage" required>
                <option value="Harvest">Harvest</option>
                <option value="Storage">Storage</option>
                <option value="Transport">Transport</option>
                <option value="Processing">Processing</option>
            </select><br><br>
            <label>Cause:</label><br>
            <input type="text" name="cause" required><br><br>
            <label>Recorded By:</label><br>
            <input type="text" name="recorded_by" required><br><br>
            <button type="submit" name="add_loss" class="button">Record Loss</button>
        </form>
    </div>
</div>

<!-- Edit Loss Record Modal (remains exactly the same) -->
<div id="editLossModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditLoss">&times;</span>
        <h2>Edit Loss Record</h2>
        <form method="POST">
            <input type="hidden" name="loss_id" id="editLossId">
            <label>Batch ID:</label><br>
            <input type="text" name="batch_id" id="editBatchId" required><br><br>
            <label>Date:</label><br>
            <input type="date" name="loss_date" id="editDate" required><br><br>
            <label>Quantity (kg):</label><br>
            <input type="number" step="0.1" name="quantity_kg" id="editQuantity" required><br><br>
            <label>Stage:</label><br>
            <select name="stage" id="editStage" required>
                <option value="Harvest">Harvest</option>
                <option value="Storage">Storage</option>
                <option value="Transport">Transport</option>
                <option value="Processing">Processing</option>
            </select><br><br>
            <label>Cause:</label><br>
            <input type="text" name="cause" id="editCause" required><br><br>
            <label>Recorded By:</label><br>
            <input type="text" name="recorded_by" id="editRecordedBy" required><br><br>
            <button type="submit" name="update_loss" class="button">Update Loss Record</button>
        </form>
    </div>
</div>



          <!-- Transportation Tab -->
          <div id="transport" class="tab-content">
          <button class="button" id="addTransportationBtn" style="margin-bottom: 20px;">Add New Transportation</button>

<!-- Search Bar -->
<div class="search-container" style="margin: 20px 0;">
    <input type="text" id="transportationSearch" placeholder="Search transportation..." 
           oninput="searchTable('transportationSearch', 'transportationTable')"
           style="padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
</div>

<table id="transportationTable">
    <thead>
        <tr>
            <th>Driver ID</th>
            <th>Pickup Date</th>
            <th>Delivery Date</th>
            <th>Destination</th>
            <th>Vehicle</th>
            <th>Delivery ID</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $transportations_result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['driver_id'] ?></td>
                <td><?= $row['pickup_date'] ?></td>
                <td><?= $row['delivery_date'] ?></td>
                <td><?= $row['destination'] ?></td>
                <td><?= $row['vehicle'] ?></td>
                <td><?= $row['delivery_id'] ?></td>
                <td>
                    
                       
                    <button class="button delete">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</div>

<!-- Add Transportation Modal -->
<div id="addTransportationModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddTransportation">&times;</span>
        <h2>Add New Transportation</h2>
        <form method="POST">
            <label>Driver ID:</label><br>
            <input type="text" name="driver_id" required><br><br>

            <label>Pickup Date:</label><br>
            <input type="date" name="pickup_date" required><br><br>

            <label>Delivery Date:</label><br>
            <input type="date" name="delivery_date" required><br><br>

            <label>Destination:</label><br>
            <input type="text" name="destination" required><br><br>

            <label>Vehicle:</label><br>
            <input type="text" name="vehicle" required><br><br>

            <label>Delivery ID:</label><br>
            <input type="text" name="delivery_id" required><br><br>

            <button type="submit" name="add_transportation" class="button">Add Transportation</button>
        </form>
    </div>
</div>

<script>
// Open Add Transportation Modal
document.getElementById('addTransportationBtn').onclick = function() {
    document.getElementById('addTransportationModal').style.display = 'block';
};

// Close Add Transportation Modal
document.getElementById('closeAddTransportation').onclick = function() {
    document.getElementById('addTransportationModal').style.display = 'none';
};



// Close modal if user clicks outside the modal
window.onclick = function(event) {
    const addModal = document.getElementById('addTransportationModal');
    const editModal = document.getElementById('editTransportationModal');
    if (event.target == addModal) {
        addModal.style.display = "none";
    }
    if (event.target == editModal) {
        editModal.style.display = "none";
    }
}

// Search function
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}
</script>



<!-- Sales Records Tab -->
<div id="Sales Records" class="tab-content">
    <button class="button" id="addSalesBtn" style="margin-bottom: 20px;">Add New Sales Record</button>

    <!-- Search Bar -->
    <div class="search-container" style="margin: 20px 0;">
        <input type="text" id="salesSearch" placeholder="Search sales records..." 
               oninput="searchTable('salesSearch', 'salesTable')"
               style="padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">

                  
    </div>

    <table id="salesTable">
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Batch ID</th>
                <th>Product</th>
                <th>Quantity (kg)</th>
                <th>Unit Price</th>
                <th>Total Amount</th>
                <th>Retailer</th>
                <th>Sale Date</th>
                <th>Payment Method</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sales_result = $conn->query("
                SELECT s.*, r.name as retailer_name 
                FROM sales_records s
                JOIN retailers r ON s.retailer_id = r.retailer_id
                ORDER BY s.sale_date DESC
            ");
            while ($row = $sales_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['sale_id'] ?></td>
                    <td><?= $row['batch_id'] ?></td>
                    <td><?= $row['product_name'] ?></td>
                    <td><?= number_format($row['quantity_kg'], 2) ?></td>
                    <td>$<?= number_format($row['price_per_kg'], 2) ?></td>
                    <td>$<?= number_format($row['total_amount'], 2) ?></td>
                    <td><?= $row['retailer_name'] ?></td>
                    <td><?= date('M d, Y', strtotime($row['sale_date'])) ?></td>
                    <td><?= $row['payment_method'] ?></td>
                    <td>
                        <button class="button editSalesBtn"
                            data-id="<?= $row['sale_id'] ?>"
                            data-batch="<?= $row['batch_id'] ?>"
                            data-product="<?= $row['product_name'] ?>"
                            data-quantity="<?= $row['quantity_kg'] ?>"
                            data-price="<?= $row['price_per_kg'] ?>"
                            data-retailer="<?= $row['retailer_id'] ?>"
                            data-date="<?= $row['sale_date'] ?>"
                            data-payment="<?= $row['payment_method'] ?>">Edit</button>
                        <button class="button delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

     <!-- Add these chart containers -->
     <div id="salesChart" style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"></div>
    <div id="productChart" style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"></div>
</div>
<script>
// Convert PHP sales data to JavaScript
const salesData = <?php echo json_encode($sales_data); ?>;
const productSales = <?php echo json_encode($product_sales); ?>;

// Prepare data for ApexCharts
const salesDates = salesData.map(item => item.date);
const salesAmounts = salesData.map(item => item.amount);
const salesQuantities = salesData.map(item => item.quantity);

// Daily Sales Chart
var salesOptions = {
    series: [{
        name: 'Sales Amount ($)',
        data: salesAmounts
    }, {
        name: 'Quantity Sold (kg)',
        data: salesQuantities
    }],
    chart: {
        height: 350,
        type: 'line',
        toolbar: {
            show: true,
            tools: {
                download: true,
                selection: true,
                zoom: true,
                zoomin: true,
                zoomout: true,
                pan: true,
                reset: true
            }
        }
    },
    stroke: {
        width: 3,
        curve: 'smooth'
    },
    xaxis: {
        type: 'datetime',
        categories: salesDates,
        labels: {
            formatter: function(value) {
                return new Date(value).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            }
        }
    },
    yaxis: [{
        title: {
            text: 'Amount ($)'
        },
        min: 0
    }, {
        opposite: true,
        title: {
            text: 'Quantity (kg)'
        },
        min: 0
    }],
    title: {
        text: 'Daily Sales Performance',
        align: 'left',
        style: {
            fontSize: "16px",
            color: '#666'
        }
    },
    tooltip: {
        y: [{
            formatter: function(value) {
                return '$' + value.toFixed(2);
            }
        }, {
            formatter: function(value) {
                return value + " kg";
            }
        }]
    },
    markers: {
        size: 5,
        hover: {
            size: 7
        }
    },
    colors: ['#4CAF50', '#2196F3']
};

var salesChart = new ApexCharts(document.querySelector("#salesChart"), salesOptions);
salesChart.render();

// Product Sales Pie Chart
var productOptions = {
    series: productSales.map(item => item.amount),
    chart: {
        type: 'pie',
        height: 350
    },
    labels: productSales.map(item => item.product),
    title: {
        text: 'Sales by Product',
        align: 'left'
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
    }],
    tooltip: {
        y: {
            formatter: function(value) {
                return '$' + value.toFixed(2);
            }
        }
    },
    colors: ['#4CAF50', '#2196F3', '#FFC107', '#FF5722', '#9C27B0']
};

var productChart = new ApexCharts(document.querySelector("#productChart"), productOptions);
productChart.render();

// Re-render charts when sales tab is clicked
document.querySelector('.tab[onclick="openTab(\'Sales Records\')"]').addEventListener('click', function() {
    setTimeout(function() {
        salesChart.updateSeries([{
            data: salesAmounts
        }, {
            data: salesQuantities
        }]);
        productChart.updateSeries(productSales.map(item => item.amount));
    }, 100);
});
</script>

<!-- Add Sales Record Modal -->
<div id="addSalesModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddSales">&times;</span>
        <h2>Add New Sales Record</h2>
        <form method="POST">
            <label>Batch ID:</label>
            <input type="text" name="batch_id" required placeholder="Enter Batch ID">
            
            <label>Product Name:</label>
            <input type="text" name="product_name" required>
            
            <label>Quantity (kg):</label>
            <input type="number" step="0.01" name="quantity_kg" required>
            
            <label>Price per kg:</label>
            <input type="number" step="0.01" name="price_per_kg" required>
            
            <label>Retailer:</label>
            <select name="retailer_id" required>
                <option value="">Select Retailer</option>
                <?php 
                $retailers = $conn->query("SELECT retailer_id, name FROM retailers");
                while ($retailer = $retailers->fetch_assoc()): ?>
                    <option value="<?= $retailer['retailer_id'] ?>">
                        <?= $retailer['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <label>Sale Date:</label>
            <input type="date" name="sale_date" required value="<?= date('Y-m-d') ?>">
            
            <label>Payment Method:</label>
            <select name="payment_method" required>
                <option value="Cash">Cash</option>
                <option value="Credit">Credit</option>
                <option value="Card">Card</option>
            </select>
            
            <button type="submit" name="add_sales" class="button">Add Sales Record</button>
        </form>
    </div>
</div>

<!-- Edit Sales Record Modal -->
<div id="editSalesModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditSales">&times;</span>
        <h2>Edit Sales Record</h2>
        <form method="POST">
            <input type="hidden" name="sale_id" id="editSaleId">
            
            <label>Batch ID:</label>
            <input type="text" id="editBatchId" readonly>
            
            <label>Product Name:</label>
            <input type="text" name="product_name" id="editProductName" required>
            
            <label>Quantity (kg):</label>
            <input type="number" step="0.01" name="quantity_kg" id="editQuantity" required>
            
            <label>Price per kg:</label>
            <input type="number" step="0.01" name="price_per_kg" id="editPrice" required>
            
            <label>Retailer:</label>
            <select name="retailer_id" id="editRetailerId" required>
                <?php 
                $retailers = $conn->query("SELECT retailer_id, name FROM retailers");
                while ($retailer = $retailers->fetch_assoc()): ?>
                    <option value="<?= $retailer['retailer_id'] ?>">
                        <?= $retailer['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <label>Sale Date:</label>
            <input type="date" name="sale_date" id="editSaleDate" required>
            
            <label>Payment Method:</label>
            <select name="payment_method" id="editPaymentMethod" required>
                <option value="Cash">Cash</option>
                <option value="Credit">Credit</option>
                <option value="Card">Card</option>
            </select>
            
            <button type="submit" name="update_sales" class="button">Update Record</button>
        </form>
    </div>
</div>


<script>
// Sales Records JavaScript
document.getElementById('addSalesBtn').onclick = function() {
    document.getElementById('addSalesModal').style.display = 'block';
};

document.querySelectorAll('.editSalesBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editSaleId').value = this.dataset.id;
        document.getElementById('editBatchId').value = this.dataset.batch;
        document.getElementById('editProductName').value = this.dataset.product;
        document.getElementById('editQuantity').value = this.dataset.quantity;
        document.getElementById('editPrice').value = this.dataset.price;
        document.getElementById('editRetailerId').value = this.dataset.retailer;
        document.getElementById('editSaleDate').value = this.dataset.date;
        document.getElementById('editPaymentMethod').value = this.dataset.payment;
        document.getElementById('editSalesModal').style.display = 'block';
    });
});

document.getElementById('closeAddSales').onclick = function() {
    document.getElementById('addSalesModal').style.display = 'none';
};

document.getElementById('closeEditSales').onclick = function() {
    document.getElementById('editSalesModal').style.display = 'none';
};

window.onclick = function(event) {
    if (event.target == document.getElementById('addSalesModal')) {
        document.getElementById('addSalesModal').style.display = 'none';
    }
    if (event.target == document.getElementById('editSalesModal')) {
        document.getElementById('editSalesModal').style.display = 'none';
    }
};
</script>



           <!-- Retailers Tab -->
<div id="retailers" class="tab-content">
    <button class="button" id="addRetailerBtn" style="margin-bottom: 20px;">Add New Retailer</button>

    <!-- Search Bar -->
    <div class="search-container" style="margin: 20px 0;">
        <input type="text" id="retailersSearch" placeholder="Search retailers..." 
               oninput="searchTable('retailersSearch', 'retailersTable')"
               style="padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <table id="retailersTable">
        <thead>
            <tr>
                <th>Retailer ID</th>
                <th>Name</th>
                <th>Location</th>
                <th>Contact</th>
                <th>Recent Orders</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $retailers_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['retailer_id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['location'] ?></td>
                    <td><?= $row['contact'] ?></td>
                    <td><?= $row['recent_orders'] ?></td>
                    <td>
                        <button class="button editRetailerBtn"
                            data-id="<?= $row['retailer_id'] ?>"
                            data-name="<?= $row['name'] ?>"
                            data-location="<?= $row['location'] ?>"
                            data-contact="<?= $row['contact'] ?>"
                            data-orders="<?= $row['recent_orders'] ?>">Edit</button>
                        <button class="button delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Retailer Modal (remains exactly the same) -->
<div id="addRetailerModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddRetailer">&times;</span>
        <h2>Add New Retailer</h2>
        <form method="POST">
            <label>Name:</label><br>
            <input type="text" name="name" required><br><br>
            <label>Location:</label><br>
            <input type="text" name="location" required><br><br>
            <label>Contact:</label><br>
            <input type="text" name="contact" required><br><br>
            <label>Recent Orders:</label><br>
            <input type="text" name="recent_orders"><br><br>
            <button type="submit" name="add_retailer" class="button">Add Retailer</button>
        </form>
    </div>
</div>

<!-- Edit Retailer Modal (remains exactly the same) -->
<div id="editRetailerModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditRetailer">&times;</span>
        <h2>Edit Retailer</h2>
        <form method="POST">
            <input type="hidden" name="retailer_id" id="editRetailerId">
            <label>Name:</label><br>
            <input type="text" name="name" id="editRetailerName" required><br><br>
            <label>Location:</label><br>
            <input type="text" name="location" id="editRetailerLocation" required><br><br>
            <label>Contact:</label><br>
            <input type="text" name="contact" id="editRetailerContact" required><br><br>
            <label>Recent Orders:</label><br>
            <input type="text" name="recent_orders" id="editRetailerOrders"><br><br>
            <button type="submit" name="update_retailer" class="button">Update Retailer</button>
        </form>
    </div>
</div>

<!-- Search script -->
<script>
// Search function
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}
</script>



<!-- Improvement Tab -->
<div id="Improvement" class="tab-content">
    <button class="button" id="addImprovementBtn" style="margin-bottom: 20px;">Add New Improvement</button>
    
    <!-- Search Bar -->
    <div class="search-container" style="margin: 20px 0;">
        <input type="text" id="improvementSearch" placeholder="Search improvements..." 
               oninput="searchTable('improvementSearch', 'improvementTable')"
               style="padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <table id="improvementTable">
        <thead>
            <tr>
                <th>Improvement ID</th>
                <th>Batch ID</th>
                <th>Produce Name</th>
                <th>Farmer ID</th>
                <th>Description</th>
                <th>Target Issue</th>
                <th>Implementation Date</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $improvement_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['improvement_id'] ?></td>
                    <td><?= $row['batch_id'] ?></td>
                    <td><?= $row['produce_name'] ?></td>
                    <td><?= $row['farmer_id'] ?></td>
                    <td><?= $row['description'] ?></td>
                    <td><?= $row['target_issue'] ?></td>
                    <td><?= $row['implementation_date'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <button class="button editImprovementBtn"
                            data-id="<?= $row['improvement_id'] ?>"
                            data-batch="<?= $row['batch_id'] ?>"
                            data-produce="<?= $row['produce_name'] ?>"
                            data-farmer="<?= $row['farmer_id'] ?>"
                            data-description="<?= $row['description'] ?>"
                            data-target="<?= $row['target_issue'] ?>"
                            data-implementation="<?= $row['implementation_date'] ?>"
                            data-created="<?= $row['created_at'] ?>">Edit</button>
                        <button class="button delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Improvement Modal -->
<div id="addImprovementModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddImprovement">&times;</span>
        <h2>Add New Improvement</h2>
        <form method="POST">
            <label>Batch ID:</label><br>
            <input type="text" name="batch_id" required><br><br>

            <label>Produce Name:</label><br>
            <input type="text" name="produce_name" required><br><br>

            <label>Farmer ID:</label><br>
            <input type="text" name="farmer_id" required><br><br>

            <label>Description:</label><br>
            <textarea name="description" rows="4" required></textarea><br><br>

            <label>Target Issue:</label><br>
            <input type="text" name="target_issue" required><br><br>

            <label>Implementation Date:</label><br>
            <input type="date" name="implementation_date" required><br><br>

            <button type="submit" name="add_improvement" class="button">Add Improvement</button>
        </form>
    </div>
</div>

<!-- Edit Improvement Modal -->
<div id="editImprovementModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditImprovement">&times;</span>
        <h2>Edit Improvement</h2>
        <form method="POST">
            <input type="hidden" name="improvement_id" id="editImprovementId">

            <label>Batch ID:</label><br>
            <input type="text" name="batch_id" id="editBatchId" required><br><br>

            <label>Produce Name:</label><br>
            <input type="text" name="produce_name" id="editProduceName" required><br><br>

            <label>Farmer ID:</label><br>
            <input type="text" name="farmer_id" id="editFarmerId" required><br><br>

            <label>Description:</label><br>
            <textarea name="description" id="editDescription" rows="4" required></textarea><br><br>

            <label>Target Issue:</label><br>
            <input type="text" name="target_issue" id="editTargetIssue" required><br><br>

            <label>Implementation Date:</label><br>
            <input type="date" name="implementation_date" id="editImplementationDate" required><br><br>

            <button type="submit" name="update_improvement" class="button">Update Improvement</button>
        </form>
    </div>
</div>

<script>
// Open Add Improvement Modal
document.getElementById('addImprovementBtn').onclick = function() {
    document.getElementById('addImprovementModal').style.display = 'block';
};

// Close Add Improvement Modal
document.getElementById('closeAddImprovement').onclick = function() {
    document.getElementById('addImprovementModal').style.display = 'none';
};

// Open Edit Improvement Modal when clicking Edit button
document.querySelectorAll('.editImprovementBtn').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('editImprovementModal').style.display = 'block';

        // Fill the form fields with the data attributes from the clicked button
        document.getElementById('editImprovementId').value = this.dataset.id;
        document.getElementById('editBatchId').value = this.dataset.batch;
        document.getElementById('editProduceName').value = this.dataset.produce;
        document.getElementById('editFarmerId').value = this.dataset.farmer;
        document.getElementById('editDescription').value = this.dataset.description;
        document.getElementById('editTargetIssue').value = this.dataset.target;
        document.getElementById('editImplementationDate').value = this.dataset.implementation;
    });
});

// Close Edit Improvement Modal
document.getElementById('closeEditImprovement').onclick = function() {
    document.getElementById('editImprovementModal').style.display = 'none';
};

// Close modal if user clicks outside the modal
window.onclick = function(event) {
    const addModal = document.getElementById('addImprovementModal');
    const editModal = document.getElementById('editImprovementModal');
    if (event.target == addModal) {
        addModal.style.display = "none";
    }
    if (event.target == editModal) {
        editModal.style.display = "none";
    }
}

// Search function
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}
</script>





                
           
            <!-- Distributors Tab -->
<div id="distributors" class="tab-content">
    <button class="button" id="addDistributorBtn" style="margin-bottom: 20px;">Add New Distributor</button>
    <!-- Search Bar -->
    <div class="search-container" style="margin: 20px 0;">
        <input type="text" id="distributorsSearch" placeholder="Search distributors..." 
               oninput="searchTable('distributorsSearch', 'distributorsTable')"
               style="padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <table id="distributorsTable">
        <thead>
            <tr>
                <th>Distributor ID</th>
                <th>Name</th>
                <th>Location</th>
                <th>Contact</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $distributors_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['dristibutor_id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['location'] ?></td>
                    <td><?= $row['contact'] ?></td>
                    <td>
                        <button class="button editDistributorBtn"
                            data-id="<?= $row['dristibutor_id'] ?>"
                            data-name="<?= $row['name'] ?>"
                            data-location="<?= $row['location'] ?>"
                            data-contact="<?= $row['contact'] ?>">Edit</button>
                        <button class="button delete deleteDistributorBtn" 
                            data-id="<?= $row['dristibutor_id'] ?>">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Distributor Modal (remains exactly the same) -->
<div id="addDistributorModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddDistributor">&times;</span>
        <h2>Add New Distributor</h2>
        <form method="POST">
            <label>Name:</label><br>
            <input type="text" name="name" required><br><br>
            <label>Location:</label><br>
            <input type="text" name="location" required><br><br>
            <label>Contact:</label><br>
            <input type="text" name="contact" required><br><br>
            <button type="submit" name="add_distributor" class="button">Add Distributor</button>
        </form>
    </div>
</div>

<!-- Edit Distributor Modal (remains exactly the same) -->
<div id="editDistributorModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditDistributor">&times;</span>
        <h2>Edit Distributor</h2>
        <form method="POST">
            <input type="hidden" name="distributor_id" id="editDistributorId">
            <label>Name:</label><br>
            <input type="text" name="name" id="editDistributorName" required><br><br>
            <label>Location:</label><br>
            <input type="text" name="location" id="editDistributorLocation" required><br><br>
            <label>Contact:</label><br>
            <input type="text" name="contact" id="editDistributorContact" required><br><br>
            <button type="submit" name="update_distributor" class="button">Update Distributor</button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal (fixed structure) -->
<div id="deleteDistributorModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeDeleteDistributor">&times;</span>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this distributor?</p>
        <form method="POST" id="deleteDistributorForm">
            <input type="hidden" name="distributor_id" id="deleteDistributorId">
            <button type="submit" name="delete_distributor" class="button delete">Delete</button>
            <button type="button" id="cancelDeleteDistributor" class="button">Cancel</button>
        </form>
    </div>
</div>

<script>
// JavaScript for delete confirmation
document.querySelectorAll('.deleteDistributorBtn').forEach(button => {
    button.addEventListener('click', function() {
        const distributorId = this.getAttribute('data-id');
        document.getElementById('deleteDistributorId').value = distributorId;
        document.getElementById('deleteDistributorModal').style.display = 'block';
    });
});

document.getElementById('cancelDeleteDistributor').addEventListener('click', function() {
    document.getElementById('deleteDistributorModal').style.display = 'none';
});

document.getElementById('closeDeleteDistributor').addEventListener('click', function() {
    document.getElementById('deleteDistributorModal').style.display = 'none';
});
</script>

              

    <script>
        function openTab(tabName) {
            // Hide all tab contents
            var tabContents = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove("active");
            }
            
            // Remove active class from all tabs
            var tabs = document.getElementsByClassName("tab");
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }
            
            // Show the selected tab content and mark tab as active
            document.getElementById(tabName).classList.add("active");
            
            // Find and activate the button for this tab
            var buttons = document.getElementsByClassName("tab");
            for (var i = 0; i < buttons.length; i++) {
                if (buttons[i].textContent.toLowerCase().includes(tabName.toLowerCase())) {
                    buttons[i].classList.add("active");
                    break;
                }
            }
        }

        // Farmer Modals
        const addFarmerBtn = document.getElementById("addFarmerBtn");
        const addFarmerModal = document.getElementById("addFarmerModal");
        const closeAddFarmer = document.getElementById("closeAddFarmer");

        const editFarmerModal = document.getElementById("editFarmerModal");
        const closeEditFarmer = document.getElementById("closeEditFarmer");
        const editFarmerBtns = document.querySelectorAll(".editFarmerBtn");

        // Storage Modals
        const addStorageBtn = document.getElementById("addStorageBtn");
        const addStorageModal = document.getElementById("addStorageModal");
        const closeAddStorage = document.getElementById("closeAddStorage");

       

        // Loss Records Modals
        const addLossBtn = document.getElementById("addLossBtn");
        const addLossModal = document.getElementById("addLossModal");
        const closeAddLoss = document.getElementById("closeAddLoss");

        const editLossModal = document.getElementById("editLossModal");
        const closeEditLoss = document.getElementById("closeEditLoss");
        const editLossBtns = document.querySelectorAll(".editLossBtn");

        // Retailer Modals
        const addRetailerBtn = document.getElementById("addRetailerBtn");
        const addRetailerModal = document.getElementById("addRetailerModal");
        const closeAddRetailer = document.getElementById("closeAddRetailer");

        const editRetailerModal = document.getElementById("editRetailerModal");
        const closeEditRetailer = document.getElementById("closeEditRetailer");
        const editRetailerBtns = document.querySelectorAll(".editRetailerBtn");

       

        // Farmer Modal Handlers
        if (addFarmerBtn) {
            addFarmerBtn.onclick = () => addFarmerModal.style.display = "block";
        }
        if (closeAddFarmer) {
            closeAddFarmer.onclick = () => addFarmerModal.style.display = "none";
        }
        if (editFarmerBtns) {
            editFarmerBtns.forEach(btn => {
                btn.onclick = () => {
                    document.getElementById("editFarmerId").value = btn.dataset.id;
                    document.getElementById("editFarmName").value = btn.dataset.name;
                    document.getElementById("editLocation").value = btn.dataset.location;
                    document.getElementById("editContact").value = btn.dataset.contact;
                    editFarmerModal.style.display = "block";
                };
            });
        }
        if (closeEditFarmer) {
            closeEditFarmer.onclick = () => editFarmerModal.style.display = "none";
        }
        

        // Storage Modal Handlers
        if (addStorageBtn) {
            addStorageBtn.onclick = () => addStorageModal.style.display = "block";
        }
        if (closeAddStorage) {
            closeAddStorage.onclick = () => addStorageModal.style.display = "none";
        }
        

        // Loss Records Modal Handlers
        if (addLossBtn) {
            addLossBtn.onclick = () => addLossModal.style.display = "block";
        }
        if (closeAddLoss) {
            closeAddLoss.onclick = () => addLossModal.style.display = "none";
        }
        if (editLossBtns) {
            editLossBtns.forEach(btn => {
                btn.onclick = () => {
                    document.getElementById("editLossId").value = btn.dataset.id;
                    document.getElementById("editBatchId").value = btn.dataset.batch;
                    document.getElementById("editDate").value = btn.dataset.date;
                    document.getElementById("editQuantity").value = btn.dataset.quantity;
                    document.getElementById("editStage").value = btn.dataset.stage;
                    document.getElementById("editCause").value = btn.dataset.cause;
                    document.getElementById("editRecordedBy").value = btn.dataset.recorded;
                    editLossModal.style.display = "block";
                };
            });
        }
        if (closeEditLoss) {
            closeEditLoss.onclick = () => editLossModal.style.display = "none";
        }

        // Retailer Modal Handlers
        if (addRetailerBtn) {
            addRetailerBtn.onclick = () => addRetailerModal.style.display = "block";
        }
        if (closeAddRetailer) {
            closeAddRetailer.onclick = () => addRetailerModal.style.display = "none";
        }
        if (editRetailerBtns) {
            editRetailerBtns.forEach(btn => {
                btn.onclick = () => {
                    document.getElementById("editRetailerId").value = btn.dataset.id;
                    document.getElementById("editRetailerName").value = btn.dataset.name;
                    document.getElementById("editRetailerLocation").value = btn.dataset.location;
                    document.getElementById("editRetailerContact").value = btn.dataset.contact;
                    document.getElementById("editRetailerOrders").value = btn.dataset.orders;
                    editRetailerModal.style.display = "block";
                };
            });
        }
        if (closeEditRetailer) {
            closeEditRetailer.onclick = () => editRetailerModal.style.display = "none";
        }


        // Distributor Modals
       const addDistributorBtn = document.getElementById("addDistributorBtn");
        const addDistributorModal = document.getElementById("addDistributorModal");
        const closeAddDistributor = document.getElementById("closeAddDistributor");

        const editDistributorModal = document.getElementById("editDistributorModal");
        const closeEditDistributor = document.getElementById("closeEditDistributor");
        const editDistributorBtns = document.querySelectorAll(".editDistributorBtn");

       // Distributor Delete Modals
const deleteDistributorBtns = document.querySelectorAll(".deleteDistributorBtn");
const deleteDistributorModal = document.getElementById("deleteDistributorModal");
const closeDeleteDistributor = document.getElementById("closeDeleteDistributor");
const cancelDeleteDistributor = document.getElementById("cancelDeleteDistributor");
        // Add Distributor Modal
        if (addDistributorBtn) {
            addDistributorBtn.onclick = () => addDistributorModal.style.display = "block";
        }
        if (closeAddDistributor) {
            closeAddDistributor.onclick = () => addDistributorModal.style.display = "none";
        }

        // Edit Distributor Modal
        if (editDistributorBtns) {
            editDistributorBtns.forEach(btn => {
                btn.onclick = () => {
                    document.getElementById("editDistributorId").value = btn.dataset.id;
                    document.getElementById("editDistributorName").value = btn.dataset.name;
                    document.getElementById("editDistributorLocation").value = btn.dataset.location;
                    document.getElementById("editDistributorContact").value = btn.dataset.contact;
                    editDistributorModal.style.display = "block";
                };
            });
        }
        if (closeEditDistributor) {
            closeEditDistributor.onclick = () => editDistributorModal.style.display = "none";
        }

        // Delete Distributor Handlers
if (deleteDistributorBtns) {
    deleteDistributorBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById("deleteDistributorId").value = btn.dataset.id;
            deleteDistributorModal.style.display = "block";
        });
    });
}

if (closeDeleteDistributor) {
    closeDeleteDistributor.addEventListener('click', () => {
        deleteDistributorModal.style.display = "none";
    });
}

if (cancelDeleteDistributor) {
    cancelDeleteDistributor.addEventListener('click', () => {
        deleteDistributorModal.style.display = "none";
    });
}

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == addFarmerModal) addFarmerModal.style.display = "none";
            if (event.target == editFarmerModal) editFarmerModal.style.display = "none";
            if (event.target == addStorageModal) addStorageModal.style.display = "none";
            if (event.target == editStorageModal) editStorageModal.style.display = "none";
            if (event.target == addLossModal) addLossModal.style.display = "none";
            if (event.target == editLossModal) editLossModal.style.display = "none";
            if (event.target == addRetailerModal) addRetailerModal.style.display = "none";
            if (event.target == editRetailerModal) editRetailerModal.style.display = "none";
          
            if (event.target == addDistributorModal) addDistributorModal.style.display = "none";
            if (event.target == editDistributorModal) editDistributorModal.style.display = "none";
            if (event.target == deleteDistributorModal) deleteDistributorModal.style.display = "none";
        };

       

       
    </script>
   <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
// Convert PHP data to JavaScript
const lossData = <?php echo json_encode($loss_data); ?>;

// Prepare data for ApexCharts
const dates = lossData.map(item => item.date);
const quantities = lossData.map(item => item.quantity);

var options = {
    series: [{
        name: 'Loss Quantity (kg)',
        data: quantities
    }],
    chart: {
        height: 350,
        type: 'line',
        toolbar: {
            show: true,
            tools: {
                download: true,
                selection: true,
                zoom: true,
                zoomin: true,
                zoomout: true,
                pan: true,
                reset: true
            }
        }
    },
    stroke: {
        width: 3,
        curve: 'smooth'
    },
    xaxis: {
        type: 'datetime',
        categories: dates,
        labels: {
            formatter: function(value) {
                return new Date(value).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            }
        }
    },
    yaxis: {
        title: {
            text: 'Quantity (kg)'
        },
        min: 0
    },
    title: {
        text: 'Daily Product Loss',
        align: 'left',
        style: {
            fontSize: "16px",
            color: '#666'
        }
    },
    tooltip: {
        y: {
            formatter: function(value) {
                return value + " kg";
            }
        }
    },
    markers: {
        size: 5,
        hover: {
            size: 7
        }
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'dark',
            gradientToColors: ['#FDD835'],
            shadeIntensity: 1,
            type: 'horizontal',
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100]
        }
    },
    colors: ['#4CAF50']
};

var chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();

// Re-render chart when loss tab is clicked
document.querySelector('.tab[onclick="openTab(\'loss\')"]').addEventListener('click', function() {
    setTimeout(function() {
        chart.updateSeries([{
            data: quantities
        }]);
    }, 100);
});
</script>
</script>
<script>
// Prepare cause data
const causeData = <?php 
    $cause_result = $conn->query("SELECT cause, SUM(quantity_kg) as total FROM loss_records GROUP BY cause");
    $cause_data = [];
    while ($row = $cause_result->fetch_assoc()) {
        $cause_data[] = [
            'cause' => $row['cause'],
            'total' => (float)$row['total']
        ];
    }
    echo json_encode($cause_data);
?>;

var causeOptions = {
    series: causeData.map(item => item.total),
    chart: {
        type: 'pie',
        height: 350
    },
    labels: causeData.map(item => item.cause),
    title: {
        text: 'Loss by Cause',
        align: 'left'
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
    }],
    tooltip: {
        y: {
            formatter: function(value) {
                return value + " kg";
            }
        }
    }
};

var causeChart = new ApexCharts(document.querySelector("#causeChart"), causeOptions);
causeChart.render();
</script>
    
</body>
</html>



