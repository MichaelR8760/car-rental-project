<?php
// customer_support_form.php
// Customer Support Contact Form
// Simple form to collect user issues and send to support email
// Requires session management

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check the user role for dynamic pages
$userRole = $_SESSION['role'] ?? 'guest';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!-- Customer Support Contact Form -->
 
<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Ahom:wght@400;700&display=swap" rel="stylesheet">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Support - RevLink Rentals</title>
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
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Contact Support</h4>
                        <p class="mb-0 text-muted">We'll get back to you through the email provided</p>
                    </div>
                    <div class="card-body">
                        <form id="supportForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       placeholder="Brief description of your issue" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="issue" class="form-label">Issue Description *</label>
                                <textarea class="form-control" id="issue" name="issue" rows="5" 
                                          placeholder="Please describe your issue in detail..." required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Submit Support Request</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="customer_support_form.js"></script>
</body>
</html>