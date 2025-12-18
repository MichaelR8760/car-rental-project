<?php
// manage_bookings.php
// Page to view and manage bookings
// Admin can view all bookings, users only their own
// Requires user to be logged in

require_once 'booking_functions.php';
require_once 'db_connect.php';

// Check the user role for dynamic pages
$userRole = $_SESSION['role'] ?? 'guest';
$isLoggedIn = isset($_SESSION['user_id']);
    updateExpiredRentals($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Ahom:wght@400;700&display=swap" rel="stylesheet">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Bookings</title>
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

  <!-- Bookings List -->
    <div class="container mt-4">
    <?php if ($userRole === 'admin'): ?>
        <h2>View Reservations</h2>
    <?php else: ?>
        <h2>My Reservations</h2>
    <?php endif; ?>
        
        <div id="bookingsContainer">
            <!-- Bookings will be populated here -->
        </div>
    </div>

    <!-- Modify Booking Modal -->
    <div class="modal fade" id="modifyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modify Reservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="bookingDetails"></div>
                    <form id="modifyForm">
                        <div class="mb-3">
                            <label class="form-label">New Start Date</label>
                            <input type="date" class="form-control" id="newStartDate">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New End Date</label>
                            <input type="date" class="form-control" id="newEndDate">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-save-cust" id="saveChanges">Save Changes</button>
                    <?php if ($userRole === 'admin'): ?>
                        <button type="button" class="btn btn-warning" id="updateCustomerBtn">Update Customer Details</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-cancel-change" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="manage_bookings.js"></script>
</body>
</html>