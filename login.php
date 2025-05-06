<?php
session_start();
ob_start();

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "greenshield";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = trim($_POST["login-email"]);
    $password_input = trim($_POST["login-password"]);
    $role = $_POST["login-service"];
    
    // Case-insensitive role matching
    $stmt = $conn->prepare("SELECT id, password, service_role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Verify password and role (case-insensitive)
        if (password_verify($password_input, $user["password"]) && 
            strtolower($role) === strtolower($user["service_role"])) {
            
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["service_role"] = $user["service_role"];
            
            // Handle warehouseManager redirect specifically
            if (strtolower($user["service_role"]) === 'warehousemanager') {
                $redirect_page = "w_index.php";
            } else {
                $redirect_page = strtolower($user["service_role"]) . ".php";
            }
            
            // Verify file exists before redirect
            if (file_exists($redirect_page)) {
                header("Location: $redirect_page");
                exit();
            } else {
                $login_error = "System error: Dashboard not available";
                error_log("Missing dashboard file: $redirect_page");
            }
        } else {
            $login_error = "Invalid credentials or role mismatch";
        }
    } else {
        $login_error = "User not found";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GreenShield - Perishable Inventory Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="login.css" />
  <style>
    .debug-info {
      background: #f0f0f0;
      padding: 10px;
      margin: 10px;
      border: 1px solid #ddd;
      color: #333;
    }
    .alert-danger {
      color: #721c24;
      background-color: #f8d7da;
      border-color: #f5c6cb;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 4px;
    }
  </style>
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
        <div class="tab active" onclick="switchToLogin()">Login</div>
        <div class="tab" onclick="switchToSignup()">Sign Up</div>
      </div>

      <div class="form-container active" id="login-form">
        <?php if (!empty($login_error)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
          <div class="form-group">
            <label for="login-email">Email</label>
            <input type="email" id="login-email" name="login-email" placeholder="warehouse@example.com" required>
          </div>

          <div class="form-group">
            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="login-password" placeholder="warehouse123" required>
          </div>

          <div class="form-group">
            <label for="login-service">Service Role</label>
            <select id="login-service" name="login-service" required>
              <option value="" disabled selected>Select your service role</option>
              <option value="farmer">Farmer</option>
              <option value="warehouseManager">Warehouse Manager</option>
              <option value="retailer">Retailer</option>
              <option value="distributor">Distributor</option>
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

      <div class="form-container" id="signup-form">
        <!-- Signup form content -->
      </div>
    </div>
  </div>

  <script>
    function switchToLogin() {
      document.getElementById('login-form').classList.add('active');
      document.getElementById('signup-form').classList.remove('active');
      document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.toggle('active', tab.textContent === 'Login');
      });
    }

    function switchToSignup() {
      document.getElementById('signup-form').classList.add('active');
      document.getElementById('login-form').classList.remove('active');
      document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.toggle('active', tab.textContent === 'Sign Up');
      });
    }
  </script>
</body>
</html>