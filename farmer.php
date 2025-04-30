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

// Handle form submission for new harvest
if (isset($_POST['submit'])) {
    $batch_id = $conn->real_escape_string($_POST['batch_id']);
    $produce = $conn->real_escape_string($_POST['produce']);
    $harvest_date = $conn->real_escape_string($_POST['harvest_date']);
    $quantity_kg = $conn->real_escape_string($_POST['quantity_kg']);
    $status = $conn->real_escape_string($_POST['status']);
    $farmer_id = $conn->real_escape_string($_POST['farmer_id']); // ✅ Added

    $sql = "INSERT INTO harvests (batch_id, produce, harvest_date, quantity_kg, status, farmer_id)
            VALUES ('$batch_id', '$produce', '$harvest_date', '$quantity_kg', '$status', '$farmer_id')"; // ✅ Modified

    if ($conn->query($sql) === TRUE) {
        header("Location: farmer.php");
        exit();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Handle update
if (isset($_POST['update'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $batch_id = $conn->real_escape_string($_POST['batch_id']);
    $produce = $conn->real_escape_string($_POST['produce']);
    $harvest_date = $conn->real_escape_string($_POST['harvest_date']);
    $quantity_kg = $conn->real_escape_string($_POST['quantity_kg']);
    $status = $conn->real_escape_string($_POST['status']);
    $farmer_id = $conn->real_escape_string($_POST['farmer_id']); // ✅ Added

    $update_sql = "UPDATE harvests SET batch_id='$batch_id', produce='$produce', harvest_date='$harvest_date',
                    quantity_kg='$quantity_kg', status='$status', farmer_id='$farmer_id' WHERE id='$id'"; // ✅ Modified

    if ($conn->query($update_sql) === TRUE) {
        header("Location: farmer.php");
        exit();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Handle delete
if (isset($_POST['delete'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $delete_sql = "DELETE FROM harvests WHERE id = '$id'";

    if ($conn->query($delete_sql) === TRUE) {
        header("Location: farmer.php");
        exit();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Fetch all harvests
$result = $conn->query("SELECT * FROM harvests ORDER BY harvest_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">
    <h2 class="mb-4">Farmer Dashboard</h2>
    <button class="btn btn-success mb-3" onclick="document.getElementById('form-section').style.display='block'">Add New Harvest</button>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <!-- New Harvest Form -->
    <div id="form-section" style="display:none;">
        <form action="" method="POST" class="border p-4 rounded bg-light">
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label>Batch ID</label>
                    <input type="number" name="batch_id" class="form-control" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Farmer ID</label> <!-- ✅ Added -->
                    <input type="number" name="farmer_id" class="form-control" required> <!-- ✅ Added -->
                </div>
                <div class="form-group col-md-3">
                    <label>Produce</label>
                    <input type="text" name="produce" class="form-control" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Harvest Date</label>
                    <input type="date" name="harvest_date" class="form-control" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Quantity (kg)</label>
                    <input type="number" step="0.01" name="quantity_kg" class="form-control" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="In Storage">In Storage</option>
                        <option value="Distributed">Distributed</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Save Harvest</button>
        </form>
    </div>

    <!-- Harvest Table -->
    <table class="table table-bordered">
        <thead class="thead-dark">
        <tr>
            <th>Batch ID</th>
            <th>Farmer ID</th> <!-- ✅ Added -->
            <th>Produce</th>
            <th>Harvest Date</th>
            <th>Quantity (kg)</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['batch_id'] ?></td>
                <td><?= $row['farmer_id'] ?></td> <!-- ✅ Added -->
                <td><?= $row['produce'] ?></td>
                <td><?= $row['harvest_date'] ?></td>
                <td><?= $row['quantity_kg'] ?></td>
                <td><?= $row['status'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Edit Form if ID is set -->
    <?php
    if (isset($_GET['id'])):
        $id = $conn->real_escape_string($_GET['id']);
        $edit_result = $conn->query("SELECT * FROM harvests WHERE id='$id'");
        if ($edit_result && $edit_result->num_rows > 0):
            $edit = $edit_result->fetch_assoc();
    ?>
        <hr>
        <h4>Edit Harvest</h4>
        <form action="" method="POST" class="border p-4 bg-light">
            <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label>Batch ID</label>
                    <input type="number" name="batch_id" class="form-control" value="<?= $edit['batch_id'] ?>" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Farmer ID</label> <!-- ✅ Added -->
                    <input type="number" name="farmer_id" class="form-control" value="<?= $edit['farmer_id'] ?>" required> <!-- ✅ Added -->
                </div>
                <div class="form-group col-md-3">
                    <label>Produce</label>
                    <input type="text" name="produce" class="form-control" value="<?= $edit['produce'] ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Harvest Date</label>
                    <input type="date" name="harvest_date" class="form-control" value="<?= $edit['harvest_date'] ?>" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Quantity (kg)</label>
                    <input type="number" name="quantity_kg" class="form-control" step="0.01" value="<?= $edit['quantity_kg'] ?>" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="In Storage" <?= $edit['status'] === 'In Storage' ? 'selected' : '' ?>>In Storage</option>
                        <option value="Distributed" <?= $edit['status'] === 'Distributed' ? 'selected' : '' ?>>Distributed</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="update" class="btn btn-primary">Update</button>
        </form>

        <form method="POST" class="mt-2">
            <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <button type="submit" name="delete" class="btn btn-danger">Delete</button>
        </form>
    <?php endif; endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>

