<?php
// Booking modification endpoint
// Allows customers to modify their booking dates, admins can modify any booking

session_start();

// Check user authentication
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
    
    // Get form data
    $bookingId = $_POST['booking_id'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    
    // Validate required fields
    if (empty($bookingId) || empty($startDate) || empty($endDate)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }
    
    // Validate date logic
    if (strtotime($startDate) >= strtotime($endDate)) {
        echo json_encode(['success' => false, 'error' => 'End date must be after start date']);
        exit;
    }
    
    try {
        // Check user role for authorization
        $userRole = $_SESSION['role'] ?? 'guest';

        if ($userRole === 'admin') {
            // Admin can modify any booking
            $verifyQuery = "SELECT * FROM customer_bookings WHERE booking_id = :booking_id AND status IN ('upcoming', 'active')";
            $verifyStmt = $db->prepare($verifyQuery);
            $verifyStmt->bindParam(':booking_id', $bookingId);
            $verifyStmt->execute();
        } else {
            // Regular users can only modify their own bookings
            $customerQuery = "SELECT cd.customer_id FROM customer_details cd WHERE cd.user_id = :user_id";
            $customerStmt = $db->prepare($customerQuery);
            $customerStmt->bindParam(':user_id', $_SESSION['user_id']);
            $customerStmt->execute();
            $customerInfo = $customerStmt->fetch(PDO::FETCH_ASSOC);

            if (!$customerInfo) {
                echo json_encode(['success' => false, 'error' => 'Customer not found']);
                exit;
            }

            $customerId = $customerInfo['customer_id'];
            
            $verifyQuery = "SELECT * FROM customer_bookings WHERE booking_id = :booking_id AND customer_id = :customer_id AND status IN ('upcoming', 'active')";
            $verifyStmt = $db->prepare($verifyQuery);
            $verifyStmt->bindParam(':booking_id', $bookingId);
            $verifyStmt->bindParam(':customer_id', $customerId);
            $verifyStmt->execute();
        }

        $booking = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            echo json_encode(['success' => false, 'error' => 'Booking not found or cannot be modified']);
            exit;
        }
        
        // Calculate new total cost based on new dates
        $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
        
        // Get vehicle daily rate
        $rateQuery = "SELECT daily_rate FROM vehicles WHERE car_vin = :car_vin";
        $rateStmt = $db->prepare($rateQuery);
        $rateStmt->bindParam(':car_vin', $booking['car_vin']);
        $rateStmt->execute();
        $vehicle = $rateStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vehicle) {
            echo json_encode(['success' => false, 'error' => 'Vehicle not found']);
            exit;
        }
        
        $newTotalCost = $days * $vehicle['daily_rate'];
        
        // Update booking with new dates and cost
        $updateQuery = "UPDATE customer_bookings 
                        SET start_date = :start_date, 
                            end_date = :end_date, 
                            total_cost = :total_cost, 
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE booking_id = :booking_id";
        
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':start_date', $startDate);
        $updateStmt->bindParam(':end_date', $endDate);
        $updateStmt->bindParam(':total_cost', $newTotalCost);
        $updateStmt->bindParam(':booking_id', $bookingId);
        
        if ($updateStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Booking updated successfully',
                'new_total_cost' => $newTotalCost
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update booking']);
        }
        
    } catch (Exception $e) {
        error_log("Error updating booking: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>