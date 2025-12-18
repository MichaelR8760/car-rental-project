<?php
// Vehicle update endpoint for admin users
// Handles vehicle data updates including image uploads

session_start();

// Check admin authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

header('Content-Type: application/json');

require_once 'booking_functions.php';
require_once 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    updateExpiredRentals($db);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $originalVin = $_POST['original_vin'] ?? '';
    $carVin = $_POST['car_vin'] ?? '';
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $year = $_POST['year'] ?? '';
    $color = $_POST['color'] ?? '';
    $type = $_POST['type'] ?? '';
    $locationId = $_POST['location_ID'] ?? '';
    $fuelType = $_POST['fuel_type'] ?? '';
    $mpg = $_POST['mpg'] ?? '';
    $seats = $_POST['seats'] ?? '';
    $licensePlate = $_POST['license_plate'] ?? '';
    $dailyRate = $_POST['daily_rate'] ?? '';
    $status = $_POST['status'] ?? 'available';
    $lastMaintenanceDate = $_POST['last_maintenance_date'] ?? null;
    $description = $_POST['description'] ?? '';
    $features = $_POST['features'] ?? '';

    // Handle image upload
    $imageUrl = '';
    if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK){
        $uploadDir = 'uploads/vehicles/';

        // Create directory if it doesn't exist
        if(!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $_FILES['car_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (!in_array($fileExtension, $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Please upload JPG or PNG images only.']);
            exit;
        }

        // Generate unique filename
        $newFileName = $carVin . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;

        // Move uploaded file to destination
        if (move_uploaded_file($_FILES['car_image']['tmp_name'], $uploadPath)) {
            $imageUrl = $uploadPath;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
            exit;
        }
    }

    // Validate required fields
    if (empty($carVin) || empty($make) || empty($model) || empty($year) ||
        empty($fuelType) || empty($seats) || empty($licensePlate) || empty($dailyRate)) {
        echo json_encode(['success' => false, 'error' => 'Required fields cannot be empty']);
        exit;
    }
    
    try {
        // Check if new VIN already exists (excluding current record)
        $checkQuery = "SELECT COUNT(*) as count FROM vehicles WHERE car_vin = :car_vin AND car_vin != :original_vin";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':car_vin', $carVin);
        $checkStmt->bindParam(':original_vin', $originalVin);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            echo json_encode(['success' => false, 'error' => 'Vehicle with this VIN already exists']);
            exit;
        }

        // Update vehicle record
        $updateQuery = "UPDATE vehicles SET 
            car_vin = :car_vin, make = :make, model = :model, year = :year, 
            color = :color, type = :type, location_ID = :location_ID, 
            fuel_type = :fuel_type, mpg = :mpg, seats = :seats, 
            license_plate = :license_plate, daily_rate = :daily_rate, 
            status = :status, last_maintenance_date = :last_maintenance_date,
            description = :description, features = :features, image_url = :image_url
            WHERE car_vin = :original_vin";

        $stmt = $db->prepare($updateQuery);

        // Bind all parameters
        $stmt->bindParam(':car_vin', $carVin);
        $stmt->bindParam(':make', $make);
        $stmt->bindParam(':model', $model);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':location_ID', $locationId, PDO::PARAM_INT);
        $stmt->bindParam(':fuel_type', $fuelType);
        $stmt->bindParam(':mpg', $mpg, PDO::PARAM_INT);
        $stmt->bindParam(':image_url', $imageUrl);
        $stmt->bindParam(':seats', $seats, PDO::PARAM_INT);
        $stmt->bindParam(':license_plate', $licensePlate);
        $stmt->bindParam(':daily_rate', $dailyRate);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':last_maintenance_date', $lastMaintenanceDate);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':features', $features);
        $stmt->bindParam(':original_vin', $originalVin);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vehicle updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update vehicle in database']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>