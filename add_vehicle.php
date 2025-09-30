<!-- PHP script to handle adding a new vehicle to the database -->
<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

require_once 'db_connect.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

    //file upload
    $imageUrl = '';
    if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK){
        $uploadDir = 'uploads/vehicles/';

        // A directory will be created if one does not exist
        if(!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $_FILES['car_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // File type confirmation 
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (!in_array($fileExtension, $allowedTypes)) {
            echo "Invalid file type. Please upload JPG or PNG images only.";
            exit;
        }

        // Generates a unique filename
        $newFileName = $carVin . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;

        // Move uploaded file
        if (move_uploaded_file($_FILES['car_image']['tmp_name'], $uploadPath)) {
            $imageUrl = $uploadPath;
        } else {
            echo "Failed to upload image";
            exit;
        }
    }
   
    // Validates the required fields
    if (empty($carVin) || empty($make) || empty($model) || empty($year) ||
        empty($fuelType) || empty($seats) || empty($licensePlate) || empty($dailyRate)) {
        echo "Required fields cannot be empty";
        exit;
    }
    
    try {
        // check VIN to see if it's already in the database
        $checkQuery = "SELECT COUNT(*) as count FROM vehicles WHERE car_vin = :car_vin";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':car_vin', $carVin);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            echo "Vehicle with this VIN already exists";
            exit;
        }

        // Add new vehicle
        $insertQuery = "INSERT INTO vehicles (
            car_vin, make, model, year, color, type, location_ID, fuel_type, mpg,
            image_url, seats, license_plate, daily_rate, status, last_maintenance_date
        ) VALUES (
            :car_vin, :make, :model, :year, :color, :type, :location_ID, :fuel_type, :mpg,
            :image_url, :seats, :license_plate, :daily_rate, :status, :last_maintenance_date
        )";

        // Prepare statement
        $stmt = $db->prepare($insertQuery);

        // Bind parameters
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

        // Execute the statement
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Failed to add vehicle to database";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} else {
    echo "Invalid request method";
}
?>