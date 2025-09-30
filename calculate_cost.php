<?php
// calculate_cost.php
// Calculate rental cost for a vehicle booking
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

require_once 'booking_functions.php';
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $carVin = $_POST['vehicle_vin'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    
    // Validate required fields
    if (empty($carVin) || empty($startDate) || empty($endDate)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    try {
        // Validate date range
        $dateValidation = validateDateRange($startDate, $endDate);
        if (!$dateValidation['valid']) {
            echo json_encode(['success' => false, 'error' => $dateValidation['error']]);
            exit;
        }
        
        // Calculate total cost
        $totalCost = calculateRentalCost($db, $carVin, $startDate, $endDate);
        if ($totalCost === false) {
            echo json_encode(['success' => false, 'error' => 'Unable to calculate rental cost']);
            exit;
        }
        
        // Return cost calculation
        echo json_encode([
            'success' => true,
            'total_cost' => number_format($totalCost, 2, '.', ''),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'vehicle_vin' => $carVin
        ]);
        
    } catch (Exception $e) {
        error_log("Cost calculation error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred while calculating cost']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>