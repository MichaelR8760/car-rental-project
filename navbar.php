<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user role and login status
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
  <title>RevLink Rentals - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous">
    </script>

  <link href="main.css" rel="stylesheet">
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
      <a class="navbar-brand" href="homepage.php">
        RevLink
        <img src="car_icon.png" alt="Car icon" class="brand-icon">
      </a>
      <div class="container">
        <div class="d-flex justify-content-between align-items-center w-100">
          <div class="navbar-left d-flex align-items-center">

            <?php if ($userRole === 'admin'): ?>
              <!-- Admin Navigation -->
              
              <!-- Fleet Management -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Fleet</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="add_vehicle_form.php">Add Vehicle</a></li>
                  <li><a class="dropdown-item" href="inventory.php">Manage Inventory</a></li>
                </ul>
              </div>

              <!-- Location Management -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Locations</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="inventory.php?location=1">Rochester, MN</a></li>
                  <li><a class="dropdown-item" href="inventory.php?location=2">Minneapolis, MN</a></li>
                  <li><a class="dropdown-item" href="inventory.php?location=3">Chicago, IL</a></li>
                  <li><a class="dropdown-item" href="inventory.php?location=4">Milwaukee, WI</a></li>
                </ul>
              </div>

              <!-- Customer Management -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Customers</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="manage_bookings.php">Manage Reservation Details</a></li>
                </ul>
              </div>

              <!-- Payment Management -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Payments</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="transactions.php">Transaction History</a></li>
                </ul>
              </div>

              <!-- Staff Management -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Staff</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="staff_management.php">Manage Staff</a></li>
                </ul>
              </div>

              <!-- Account Settings -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Manage Account</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="edit_account_settings.php">Account Settings</a></li>
                  <li><a class="dropdown-item" href="change_password.php">Change Password</a></li>
                </ul>
              </div>

              <a class="nav-link" href="view_support_tickets.php">Support</a>

            <?php elseif ($userRole === 'customer'): ?>
              <!-- Customer Navigation -->
              
              <!-- Vehicle Rental -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Rent</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="homepage.php">Vehicle Reservation</a></li>
                </ul>
              </div>

              <!-- Browse Fleet by Category -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Fleet</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="inventory.php">View All Inventory</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item" href="inventory.php?type=economy">Economy</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=compact">Compact</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=luxury">Luxury</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=sports">Sports</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=suvs">SUVs</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=trucks">Trucks</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=movingTrucks">Moving Trucks</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=vans">Vans</a></li>
                  <li><a class="dropdown-item" href="inventory.php?fuel_type=electric">Electric</a></li>
                  <li><a class="dropdown-item" href="inventory.php?fuel_type=hybrid">Hybrid</a></li>
                </ul>
              </div>

              <!-- Browse by Location -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Locations</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="inventory.php?location=1">Rochester, MN</a></li>
                  <li><a class="dropdown-item" href="inventory.php?location=2">Minneapolis, MN</a></li>
                  <li><a class="dropdown-item" href="inventory.php?location=3">Chicago, IL</a></li>
                  <li><a class="dropdown-item" href="inventory.php?location=4">Milwaukee, WI</a></li>
                </ul>
              </div>

              <!-- Customer Reservations -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#">My Reservations</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="manage_bookings.php">Manage Reservations</a></li>
                </ul>
              </div>

              <!-- Account Management -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Manage Account</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="create_payment_method_session.php">Add Payment Method</a></li>
                  <li><a class="dropdown-item" href="create_customer_portal_session.php">Manage Payment Methods</a></li>
                  <li><a class="dropdown-item" href="edit_account_settings.php">Account Settings</a></li>
                  <li><a class="dropdown-item" href="change_password.php">Change Password</a></li>
                </ul>
              </div>

              <a class="nav-link" href="customer_support_form.php">Support</a>

            <?php else: ?>
              <!-- Guest Navigation (Not Logged In) -->
              
              <!-- Vehicle Browsing -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Rent</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="homepage.php">Vehicle Reservation</a></li>
                </ul>
              </div>

              <!-- Browse Fleet -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Fleet</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="inventory.php">View All Inventory</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item" href="inventory.php?type=economy">Economy</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=compact">Compact</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=luxury">Luxury</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=sports">Sports</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=suvs">SUVs</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=trucks">Trucks</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=movingTrucks">Moving Trucks</a></li>
                  <li><a class="dropdown-item" href="inventory.php?type=vans">Vans</a></li>
                  <li><a class="dropdown-item" href="inventory.php?fuel_type=electric">Electric</a></li>
                  <li><a class="dropdown-item" href="inventory.php?fuel_type=hybrid">Hybrid</a></li>
                </ul>
              </div>

              <!-- Browse by Location -->
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Locations</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="inventory.php?location=1">Rochester, MN</a></li>
                  <li><a class="dropdown-item" href="inventory.php?location=2">Minneapolis, MN</a></li>
                  <li><a class="dropdown-item" href="inventory.php?location=3">Chicago, IL</a></li>
                  <li><a class="dropdown-item" href="inventory.php?location=4">Milwaukee, WI</a></li>
                </ul>
              </div>

              <a class="nav-link" href="#">Support</a>

            <?php endif; ?>
          </div>

          <!-- Right Side: Authentication -->
          <div class="navbar-right d-flex align-items-center">
            <div class="navbar-placeholder"></div>

            <?php if ($userRole === 'admin'): ?>
              <a class="nav-link" href="logout.php">Sign Out</a>
            <?php elseif ($userRole === 'customer'): ?>
              <a class="nav-link" href="logout.php">Sign Out</a>
            <?php else: ?>
              <a class="nav-link" href="login_form.php">Sign In/Create Account</a>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </nav>