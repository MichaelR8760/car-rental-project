<?php
// login_form.php
// Login Screen

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check the user role for dynamic pages
$userRole = $_SESSION['role'] ?? 'guest';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Ahom:wght@400;700&display=swap" rel="stylesheet">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - RevLink Rentals</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"crossorigin="anonymous"></script>
  <link href="main.css" rel="stylesheet">
</head>
<body>

  <!--Navigation Bar-->
  <?php include 'navbar.php'; ?>
  
  <!-- Login Form -->
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card p-4">
        <h4 class="text-center mb-3">Login</h4>
        <div id="alertPlaceholder" class="mt-3"></div>
        <form id="loginForm">
          <div class="mb-3">
            <label for="identifier" class="form-label">Username or Email</label>
            <input type="text" class="form-control" id="identifier" placeholder="Enter your username or email" name="identifier" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" placeholder="Enter your password" name="password" required>
          </div>

          <div class="d-grid gap-2">
          <button type="submit" class="btn btn-login">Login</button>
          <a href="register_form.php" class="btn btn-outline-secondary">Create Account</a>
      </div>
        </form>
    </div>
    </div>
    </div>
  </div>

<script src="login.js"></script>
</body>
</html>