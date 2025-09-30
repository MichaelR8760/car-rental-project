<?php
// get_booking_details.php
// Retrieve details for a specific booking by ID
// Admins can access any booking, regular customers can only access their own bookings
// Returns JSON data

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

require_once 'db_connect.php';
header('Content-Type: application/json');

// Process booking detail retrieval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bookingId = $_POST['booking_id'] ?? '';

    // Validate required fields
    if (empty($bookingId)) {
        echo json_encode(['success' => false, 'error' => 'Booking ID is required']);
        exit;
    }

    try {
        if ($_SESSION['role'] === 'admin') {
            // Admin can access any booking
            $query = "SELECT cb.*, v.make, v.model, v.year, v.color, v.type, v.seats, v.daily_rate 
                  FROM customer_bookings cb
                  JOIN vehicles v ON cb.car_vin = v.car_vin
                  WHERE cb.booking_id = :booking_id";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':booking_id', $bookingId);
            $stmt->execute();
        } else {
            // Regular customer can only access their own bookings
            $customerQuery = "SELECT cd.customer_id FROM customer_details cd WHERE cd.user_id = :user_id";
            $customerStmt = $db->prepare($customerQuery);
            $customerStmt->bindParam(':user_id', $_SESSION['user_id']);
            $customerStmt->execute();
            $customerInfo = $customerStmt->fetch(PDO::FETCH_ASSOC);

            // If no customer found, return error
            if (!$customerInfo) {
                echo json_encode(['success' => false, 'error' => 'Customer not found']);
                exit;
            }

            // Get customer_id
            $customerId = $customerInfo['customer_id'];

            $query = "SELECT cb.*, v.make, v.model, v.year, v.color, v.type, v.seats, v.daily_rate 
                  FROM customer_bookings cb
                  JOIN vehicles v ON cb.car_vin = v.car_vin
                  WHERE cb.booking_id = :booking_id AND cb.customer_id = :customer_id";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':booking_id', $bookingId);
            $stmt->bindParam(':customer_id', $customerId);
            $stmt->execute();
        }

        // Fetch booking details
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return booking details as JSON
        if ($booking) {
            echo json_encode([
                'success' => true,
                'booking' => $booking
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
        }

        // Catch any database errors
    } catch (Exception $e) {
        error_log("Error retrieving booking details: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }

    // Handle non-POST requests
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>