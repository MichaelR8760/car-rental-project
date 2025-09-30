<?php
// get_all_bookings.php
// Retrieve all bookings for admin or user's own bookings for regular customers
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    try {

        if ($_SESSION['role'] === 'admin') {
            // Admin can see ALL bookings from all customers
            $query = "SELECT cb.*, v.make, v.model, v.year, v.color, v.type, v.seats, v.daily_rate,
                    u.first_name, u.last_name, u.email, cd.dl_last_four, l.name as pickup_location
                    FROM customer_bookings cb
                    JOIN vehicles v ON cb.car_vin = v.car_vin
                    LEFT JOIN customer_details cd ON cb.customer_id = cd.customer_id
                    LEFT JOIN users u ON cd.user_id = u.user_id
                    LEFT JOIN locations l ON v.location_id = l.location_id
                    ORDER BY cb.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute();
        } else {

            // Regular customers can only see their own bookings
            // Get the correct customer_id from customer_details table
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

            $customerId = $customerInfo['customer_id'];
            
            // Get all bookings for this customer with vehicle information
            $query = "SELECT cb.*, v.make, v.model, v.year, v.color, v.type, v.seats, v.daily_rate,
                    u.first_name, u.last_name, cd.dl_last_four, l.name as pickup_location
                    FROM customer_bookings cb
                    JOIN vehicles v ON cb.car_vin = v.car_vin
                    JOIN customer_details cd ON cb.customer_id = cd.customer_id
                    JOIN users u ON cd.user_id = u.user_id
                    LEFT JOIN locations l ON v.location_id = l.location_id
                    WHERE cb.customer_id = :customer_id
                    ORDER BY cb.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':customer_id', $customerId);
            $stmt->execute();
        }
        
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return bookings as JSON
        echo json_encode([
            'success' => true,
            'bookings' => $bookings
        ]);
        
        
    } catch (Exception $e) {
        error_log("Error retrieving bookings: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>