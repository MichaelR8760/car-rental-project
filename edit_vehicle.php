<?php
// edit_vehicle.php
// Admin page to edit vehicle details
// Ensure only admins can access this page
// Start session and check authentication

session_start();

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

// Get user role for dynamic pages
$userRole = $_SESSION['role'] ?? 'guest';
$isLoggedIn = isset($_SESSION['user_id']);

// Load vehicle data if VIN provided
$vehicle = null;
if (isset($_GET['vin'])) {
    require_once 'db_connect.php';
    $vin = $_GET['vin'];
    
    $query = "SELECT * FROM vehicles WHERE car_vin = :vin";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':vin', $vin);
    $stmt->execute();
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        die("Vehicle not found");
    }
} else {
    $vehicle = null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Ahom:wght@400;700&display=swap" rel="stylesheet">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Vehicle - RevLink Admin</title>
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
            <div class="col-md-8">
                <div class="card p-4">
                    <h4 class="text-center mb-4">Edit Vehicle</h4>
                    <div id="alertPlaceholder"></div>

                    <form id="vehicleForm" action="save_vehicle.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="original_vin" value="<?php echo htmlspecialchars($vehicle['car_vin'] ?? ''); ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="carVin" class="form-label">VIN *</label>
                                <input type="text" class="form-control" id="carVin" name="car_vin" value="<?php echo htmlspecialchars($vehicle['car_vin'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="licensePlate" class="form-label">License Plate *</label>
                                <input type="text" class="form-control" id="licensePlate" name="license_plate" value="<?php echo htmlspecialchars($vehicle['license_plate'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="make" class="form-label">Make *</label>
                                <input type="text" class="form-control" id="make" name="make" placeholder="Honda, Toyota, Ford, etc" value="<?php echo htmlspecialchars($vehicle['make'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="model" class="form-label">Model *</label>
                                <input type="text" class="form-control" id="model" name="model" placeholder="Civic, Camry, Focus, etc" value="<?php echo htmlspecialchars($vehicle['model'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="year" class="form-label">Year *</label>
                                <input type="number" class="form-control" id="year" name="year" min="1990" max="2025" value="<?php echo htmlspecialchars($vehicle['year'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="color" name="color" placeholder="Grey, White, Blue, etc" value="<?php echo htmlspecialchars($vehicle['color'] ?? ''); ?>">
                            </div>

                           <div class="col-md-4 mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="">Select Type</option>
                                    <option value="economy" <?php echo ($vehicle['type'] ?? '') == 'economy' ? 'selected' : ''; ?>>Economy</option>
                                    <option value="compact" <?php echo ($vehicle['type'] ?? '') == 'compact' ? 'selected' : ''; ?>>Compact</option>
                                    <option value="sports" <?php echo ($vehicle['type'] ?? '') == 'sports' ? 'selected' : ''; ?>>Sports</option>
                                    <option value="luxury" <?php echo ($vehicle['type'] ?? '') == 'luxury' ? 'selected' : ''; ?>>Luxury</option>
                                    <option value="suvs" <?php echo ($vehicle['type'] ?? '') == 'suvs' ? 'selected' : ''; ?>>SUVs</option>
                                    <option value="trucks" <?php echo ($vehicle['type'] ?? '') == 'trucks' ? 'selected' : ''; ?>>Trucks</option>
                                    <option value="movingTrucks" <?php echo ($vehicle['type'] ?? '') == 'movingTrucks' ? 'selected' : ''; ?>>Moving Trucks</option>
                                    <option value="vans" <?php echo ($vehicle['type'] ?? '') == 'vans' ? 'selected' : ''; ?>>Vans</option>
                                    <option value="electric" <?php echo ($vehicle['type'] ?? '') == 'electric' ? 'selected' : ''; ?>>Electric</option>
                                    <option value="hybrid" <?php echo ($vehicle['type'] ?? '') == 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="seats" class="form-label">Seats *</label>
                                <input type="number" class="form-control" id="seats" name="seats" min="2" max="15" value="<?php echo htmlspecialchars($vehicle['seats'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fuelType" class="form-label">Fuel Type *</label>
                                <select class="form-control" id="fuelType" name="fuel_type" required>
                                    <option value="">Select Fuel Type</option>
                                    <option value="gasoline" <?php echo ($vehicle['fuel_type'] ?? '') == 'gasoline' ? 'selected' : ''; ?>>Gasoline</option>
                                    <option value="diesel" <?php echo ($vehicle['fuel_type'] ?? '') == 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                                    <option value="hybrid" <?php echo ($vehicle['fuel_type'] ?? '') == 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                                    <option value="electric" <?php echo ($vehicle['fuel_type'] ?? '') == 'electric' ? 'selected' : ''; ?>>Electric</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="mpg" class="form-label">MPG</label>
                                <input type="number" class="form-control" id="mpg" name="mpg" min="1" max="200" value="<?php echo htmlspecialchars($vehicle['mpg'] ?? ''); ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="dailyRate" class="form-label">Daily Rate *</label>
                                <input type="number" class="form-control" id="dailyRate" name="daily_rate" step="0.01" min="0" value="<?php echo htmlspecialchars($vehicle['daily_rate'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="locationId" class="form-label">Location</label>
                                <select class="form-control" id="locationId" name="location_ID">
                                    <option value="">Select Location</option>
                                    <option value="1" <?php echo ($vehicle['location_ID'] ?? '') == 1 ? 'selected' : ''; ?>>Rochester, MN</option>
                                    <option value="2" <?php echo ($vehicle['location_ID'] ?? '') == 2 ? 'selected' : ''; ?>>Minneapolis, MN</option>
                                    <option value="3" <?php echo ($vehicle['location_ID'] ?? '') == 3 ? 'selected' : ''; ?>>Chicago, IL</option>
                                    <option value="4" <?php echo ($vehicle['location_ID'] ?? '') == 4 ? 'selected' : ''; ?>>Milwaukee, WI</option>
                                </select> 
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="carImage" class="form-label">Vehicle Image</label>
                                <input type="file" class="form-control" id="carImage" name="car_image" accept="image/*">
                                <div class="form-text">Upload a new photo to replace current image.</div>
                                <?php if (!empty($vehicle['image_url'])): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Current image: <?php echo basename($vehicle['image_url']); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of the vehicle..."><?php echo htmlspecialchars($vehicle['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="features" class="form-label">Features</label>
                                <textarea class="form-control" id="features" name="features" rows="3" placeholder="Bluetooth, backup camera, etc..."><?php echo htmlspecialchars($vehicle['features'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="available" <?php echo ($vehicle['status'] ?? '') == 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="pending_maintenance" <?php echo ($vehicle['status'] ?? '') == 'pending_maintenance' ? 'selected' : ''; ?>>Pending Maintenance</option>
                                    <option value="under_maintenance" <?php echo ($vehicle['status'] ?? '') == 'under_maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
                                    <option value="rented" <?php echo ($vehicle['status'] ?? '') == 'rented' ? 'selected' : ''; ?>>Rented</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="lastMaintenance" class="form-label">Last Maintenance Date</label>
                                <input type="date" class="form-control" id="lastMaintenance" name="last_maintenance_date" value="<?php echo htmlspecialchars($vehicle['last_maintenance_date'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Vehicle</button>
                            <a href="inventory.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="edit_vehicle.js"></script>
</body>
</html>