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

// Handle form submission for new order
if (isset($_POST['submit_order'])) {
    $produce = $_POST['produce'];
    $quantity_kg = $_POST['quantity_kg'];
    $order_date = $_POST['order_date'];
    $status = $_POST['status'];
    $expected_delivery = $_POST['expected_delivery'];

    $sql = "INSERT INTO orders (produce, quantity_kg, order_date, status, expected_delivery)
            VALUES ('$produce', '$quantity_kg', '$order_date', '$status', '$expected_delivery')";

    if ($conn->query($sql) === TRUE) {
        header("Location: retailer.php"); // Refresh the page to show updated data
        exit();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Fetch all orders
$result = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retailer Dashboard</title>
    <link rel="stylesheet" href="Retailer.css">
</head>
<body>
    <div class="header">
        <h2>Retailer Dashboard - FreshMart Stores</h2>
    </div>
    
    <div class="nav">
        <a class="active" href="#orders">Orders</a>
       
    </div>
    
    <div class="container">
        <div class="action-bar">
            <button class="button" onclick="document.getElementById('newOrderForm').style.display='block'">Place New Order</button>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <!-- New Order Form -->
        <div id="newOrderForm" style="display:none;">
            <form action="" method="POST" class="border p-4 rounded bg-light">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Produce</label>
                        <input type="text" name="produce" class="form-control" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Quantity (kg)</label>
                        <input type="number" name="quantity_kg" class="form-control" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Order Date</label>
                        <input type="date" name="order_date" class="form-control" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="In Transit">In Transit</option>
                            <option value="Processing">Processing</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Expected Delivery</label>
                        <input type="date" name="expected_delivery" class="form-control" required>
                    </div>
                </div>
                <button type="submit" name="submit_order" class="btn btn-primary">Place Order</button>
            </form>
        </div>

        <!-- Orders Table -->
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Produce</th>
                    <th>Quantity (kg)</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Expected Delivery</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['order_id'] ?></td>
                        <td><?= $row['produce'] ?></td>
                        <td><?= $row['quantity_kg'] ?></td>
                        <td><?= $row['order_date'] ?></td>
                        <td class="<?= strtolower($row['status']) ?>"><?= $row['status'] ?></td>
                        <td><?= $row['expected_delivery'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="Retailer.js"></script>
</body>
</html>

<?php $conn->close(); ?>
