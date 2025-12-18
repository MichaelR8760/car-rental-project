<?php
// get_vehicles.php
// Retrieve all vehicles or available vehicles based on date range and location
// Returns JSON data
// Admins see all vehicles with all statuses, regular users see only available and rented vehicles

session_start(); // Added for role-based access

require_once 'booking_functions.php';
require_once 'db_connect.php';

header('Content-Type: application/json');

$userRole = $_SESSION['role'] ?? 'guest'; // Added for role-based filtering
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$location = $_GET['location'] ?? '';

// Verify date range if both dates are provided
try {
    if ($startDate && $endDate) {
        $vehicles = getAvailableVehicles($db, $startDate, $endDate);

        if ($location) {
            $vehicles = array_filter($vehicles, function($vehicle) use ($location) {
                return (int)$vehicle['location_id'] === (int)$location;
            });
            $vehicles = array_values($vehicles);
        }

        echo json_encode($vehicles);
        return;
    }

    $query = "SELECT * FROM vehicles";

    // Admins see ALL vehicles with all statuses for management
    if ($userRole !== 'admin') {
        $query .= " WHERE status IN ('available', 'rented')";
    }

    $query .= " ORDER BY make, model, year";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($vehicles);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>