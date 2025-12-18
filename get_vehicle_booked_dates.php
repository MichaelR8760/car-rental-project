<?php
// get_vehicle_booked_dates.php
// Retrieve all booked dates for a specific vehicle
// Returns JSON array of booked dates
// Input: vin via GET parameter

require_once 'db_connect.php';
header('Content-Type: application/json');

$carVin = $_GET['vin'] ?? '';

// If no VIN provided, return empty array
if (!$carVin) {
    echo json_encode([]);
    exit;
}

// Get all bookings for this vehicle
$query = "SELECT start_date, end_date FROM bookings WHERE car_vin = :vin";
$stmt = $db->prepare($query);
$stmt->execute(['vin' => $carVin]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$bookedDates = [];

// Expand each booking range into individual dates
foreach ($bookings as $b) {
    $current = strtotime($b['start_date']);
    $end = strtotime($b['end_date']);
    while ($current <= $end) {
        $bookedDates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
}

// Return JSON array of booked dates
echo json_encode($bookedDates);
