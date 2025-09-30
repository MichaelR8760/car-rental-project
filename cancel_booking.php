<?php
// cancel_booking.php
// Cancel a vehicle booking
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

require_once 'booking_functions.php';
require_once 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    updateExpiredRentals($db);
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $bookingId = $_POST['booking_id'] ?? '';
    // Validate input
    if (empty($bookingId)) {
        echo json_encode(['success' => false, 'error' => 'Booking ID is required']);
        exit;
    }
    
    try {
        // Get the correct customer_id from customer_details table
        $customerQuery = "SELECT cd.customer_id FROM customer_details cd WHERE cd.user_id = :user_id";
        $customerStmt = $db->prepare($customerQuery);
        $customerStmt->bindParam(':user_id', $_SESSION['user_id']);
        $customerStmt->execute();
        $customerInfo = $customerStmt->fetch(PDO::FETCH_ASSOC);

        // If customer not found, return error
        if (!$customerInfo) {
            echo json_encode(['success' => false, 'error' => 'Customer not found']);
            exit;
        }

        $customerId = $customerInfo['customer_id'];
        
        // Verify booking belongs to this customer and can be cancelled
        $verifyQuery = "SELECT * FROM customer_bookings WHERE booking_id = :booking_id AND customer_id = :customer_id AND status = 'upcoming'";
        $verifyStmt = $db->prepare($verifyQuery);
        $verifyStmt->bindParam(':booking_id', $bookingId);
        $verifyStmt->bindParam(':customer_id', $customerId);
        $verifyStmt->execute();
        $booking = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        // If no booking found or cannot be cancelled, return error
        if (!$booking) {
            echo json_encode(['success' => false, 'error' => 'Booking not found or cannot be cancelled']);
            exit;
        }
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            $cancelQuery = "UPDATE customer_bookings 
                           SET status = 'canceled', 
                               updated_at = CURRENT_TIMESTAMP 
                           WHERE booking_id = :booking_id";
            
            $cancelStmt = $db->prepare($cancelQuery);
            $cancelStmt->bindParam(':booking_id', $bookingId);
            $cancelStmt->execute();
            
            $vehicleQuery = "UPDATE vehicles 
                            SET status = 'available' 
                            WHERE car_vin = :car_vin AND status = 'rented'";
            
            $vehicleStmt = $db->prepare($vehicleQuery);
            $vehicleStmt->bindParam(':car_vin', $booking['car_vin']);
            $vehicleStmt->execute();
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ]);
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Error cancelling booking: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>