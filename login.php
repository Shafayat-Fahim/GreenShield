<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "greenshield";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['login-email'];
    $password = $_POST['login-password'];
    $role = $_POST['login-service'];
    
    // Validate credentials
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password (assuming passwords are hashed)
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Redirect based on role
            if ($user['role'] == 'warehouse-manager') {
                header("Location: warehouse_dashboard.php");
                exit();
            } else {
                // Redirect other roles to their respective dashboards
                header("Location: ".strtolower($user['role'])."_dashboard.php");
                exit();
            }
        } else {
            $login_error = "Invalid email or password";
        }
    } else {
        $login_error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>HarvestTrack - Perishable Inventory Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="login.css" />
</head>
<body>
  <div class="auth-container">
    <div class="left-panel">  
      <div class="branding-content">
        <h1>HarvestTrack Pro</h1>
        <p>Inventory Management and Post Harvest Loss Management System for Perishable Goods</p>
        <img src="Capture.PNG" alt="Fresh produce management" class="hero-image">
      </div>
    </div>

    <div class="right-panel">
      <div class="tabs">
        <div class="tab active" id="login-tab" onclick="switchToLogin()">Login</div>
        <div class="tab" id="signup-tab" onclick="switchToSignup()">Sign Up</div>
      </div>

      <!-- Login Form -->
      <div class="form-container active" id="login-form">
        <?php if (isset($login_error)): ?>
          <div class="alert alert-danger"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
        <form id="loginForm" method="POST" action="login.php">
          <div class="form-group">
            <label for="login-email">Email</label>
            <input type="email" id="login-email" name="login-email" placeholder="2222029@gmail.com" required>
          </div>

          <div class="form-group">
            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="login-password" placeholder="••••••••" required>
          </div>

          <div class="form-group">
            <label for="login-service">Service Role</label>
            <select id="login-service" name="login-service" required>
              <option value="" disabled selected>Select your service role</option>
              <option value="farmer">Farmer</option>
              <option value="warehouse-manager">Warehouse Manager</option>
              <option value="retailer">Retailer</option>
              <option value="distributor">Distributor</option>
              <option value="driver">Driver</option>
            </select>
          </div>

          <div class="remember-forgot">
            <label class="remember-me">
              <input type="checkbox"> Remember me
            </label>
            <a href="#" class="forgot-password">Forgot Password?</a>
          </div>
          
          <button type="submit" name="login" class="submit-btn">Login</button>
        </form>

        <div class="or-divider">OR</div>
        <div class="social-login">
          <button class="social-btn"><i class="fab fa-facebook-f"></i></button>
          <button class="social-btn"><i class="fab fa-google"></i></button>
          <button class="social-btn"><i class="fab fa-apple"></i></button>
        </div>
      </div>

      <!-- Signup Form (remain the same) -->
      <div class="form-container" id="signup-form">
        <!-- ... existing signup form ... -->
      </div>
    </div>
  </div>

  <script src="login.js"></script>
</body>
</html>