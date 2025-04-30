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

// Handle form submission for new delivery
if (isset($_POST['submit_delivery'])) {
    $batch_id = $_POST['batch_id'];
    $distributor_id = $_POST['distributor_id'];
    $quantity_kg = $_POST['quantity_kg'];
    $pickup_date = $_POST['pickup_date'];
    $delivery_date = $_POST['delivery_date'];
    $driver_id = $_POST['driver_id'];
    $destination = $_POST['destination'];
    $vehicle = $_POST['vehicle'];

    $sql = "INSERT INTO deliveries (batch_id, distributor_id, quantity_kg, pickup_date, delivery_date, driver_id, destination, vehicle)
            VALUES ('$batch_id', '$distributor_id', '$quantity_kg', '$pickup_date', '$delivery_date', '$driver_id', '$destination', '$vehicle')";

    if ($conn->query($sql) === TRUE) {
        header("Location: distributor.php"); // Refresh the page to show updated data
        exit();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Fetch all deliveries
$result = $conn->query("SELECT * FROM deliveries ORDER BY pickup_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distributor Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .nav {
            background-color: #333;
            overflow: hidden;
        }
        .nav a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .nav a.active {
            background-color: #4CAF50;
        }
        .container {
            padding: 20px;
        }
        .action-bar {
            margin-bottom: 20px;
        }
        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        .form-group {
            margin-bottom: 1rem;
            padding-right: 15px;
            padding-left: 15px;
            flex: 0 0 25%;
            max-width: 25%;
        }
        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        label {
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
            padding: 0.5rem 1rem;
            font-size: 1.25rem;
            line-height: 1.5;
            border-radius: 0.3rem;
            cursor: pointer;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .status-pending {
            color: #856404;
            background-color: #fff3cd;
        }
        .status-in-transit {
            color: #0c5460;
            background-color: #d1ecf1;
        }
        .status-delivered {
            color: #155724;
            background-color: #d4edda;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Distributor Dashboard - GreenShield Logistics</h2>
    </div>
    
    <div class="nav">
        <a class="active" href="#deliveries">Deliveries</a>
        
    </div>
    
    <div class="container">
        <div class="action-bar">
            <button class="button" onclick="document.getElementById('newDeliveryForm').style.display='block'">Schedule New Delivery</button>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <!-- New Delivery Form -->
        <div id="newDeliveryForm" style="display:none;">
            <form action="" method="POST" class="border p-4 rounded bg-light">
                <div class="form-row">
                    <div class="form-group">
                        <label>Batch ID</label>
                        <input type="text" name="batch_id" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Distributor ID</label>
                        <input type="text" name="distributor_id" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Quantity (kg)</label>
                        <input type="number" name="quantity_kg" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Pickup Date</label>
                        <input type="date" name="pickup_date" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Delivery Date</label>
                        <input type="date" name="delivery_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Driver ID</label>
                        <input type="text" name="driver_id" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Destination</label>
                        <input type="text" name="destination" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Vehicle</label>
                        <select name="vehicle" class="form-control" required>
                            <option value="Refrigerated Truck">Refrigerated Truck</option>
                            <option value="Van">Van</option>
                            <option value="Pickup Truck">Pickup Truck</option>
                            <option value="Large Truck">Large Truck</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="submit_delivery" class="btn-primary">Schedule Delivery</button>
            </form>
        </div>

        <!-- Deliveries Table -->
        <table>
            <thead>
                <tr>
                    <th>Delivery ID</th>
                    <th>Batch ID</th>
                    <th>Distributor ID</th>
                    <th>Quantity (kg)</th>
                    <th>Pickup Date</th>
                    <th>Delivery Date</th>
                    <th>Driver ID</th>
                    <th>Destination</th>
                    <th>Vehicle</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['delivery_id'] ?></td>
                        <td><?= $row['batch_id'] ?></td>
                        <td><?= $row['distributor_id'] ?></td>
                        <td><?= $row['quantity_kg'] ?></td>
                        <td><?= $row['pickup_date'] ?></td>
                        <td><?= $row['delivery_date'] ?></td>
                        <td><?= $row['driver_id'] ?></td>
                        <td><?= $row['destination'] ?></td>
                        <td><?= $row['vehicle'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Simple JavaScript to handle form display
        document.addEventListener('DOMContentLoaded', function() {
            // You can add more interactive functionality here
            // For example, close the form when clicking outside
            window.onclick = function(event) {
                const form = document.getElementById('newDeliveryForm');
                if (event.target == form) {
                    form.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>