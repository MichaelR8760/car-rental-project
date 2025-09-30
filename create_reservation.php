<?php
// create_reservation.php
// Create a new vehicle reservation (booking)
// Requires booking_functions.php for helper functions

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

// Check if user's driver's license is verified
$customerQuery = "SELECT customer_id, dl_verified FROM customer_details WHERE user_id = :user_id AND dl_verified = 1";
$customerStmt = $db->prepare($customerQuery);
$customerStmt->bindParam(':user_id', $_SESSION['user_id']);
$customerStmt->execute();
$customerInfo = $customerStmt->fetch(PDO::FETCH_ASSOC);

// If no verified customer found, return error
if (!$customerInfo) {
    echo json_encode(['success' => false, 'error' => 'DL_VERIFICATION_REQUIRED']);
    exit;
}

header('Content-Type: application/json');

// Process reservation creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $carVin = $_POST['vehicle_vin'] ?? '';
    $startDate = $_POST['pickup_date'] ?? '';
    $endDate = $_POST['dropoff_date'] ?? '';
    $customerId = $_SESSION['user_id'];
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $paymentIntentId = $_POST['payment_intent_id'] ?? '';
    $paymentMethodId = $_POST['payment_method'] ?? '';
    $totalCost = $_POST['total_cost'] ?? '';

    // Validate required fields
    if (
        empty($carVin) || empty($startDate) || empty($endDate) ||
        empty($firstName) || empty($lastName) || empty($email) || empty($phone)
    ) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }

    // Validate payment fields
    $paymentMethod = $_POST['payment_method'] ?? '';
    if (empty($paymentMethod)) {
        echo json_encode(['success' => false, 'error' => 'Payment method is required']);
        exit;
    }

    try {
        // Validate date range
        $dateValidation = validateDateRange($startDate, $endDate);
        if (!$dateValidation['valid']) {
            echo json_encode(['success' => false, 'error' => $dateValidation['error']]);
            exit;
        }

        // Check vehicle availability
        if (!isVehicleAvailable($db, $carVin, $startDate, $endDate)) {
    
            // Before looking for substitutes, check if the original vehicle is free for the requested dates
            $dateCheckQuery = "SELECT COUNT(*) as conflicts 
                               FROM customer_bookings 
                               WHERE car_vin = :vin 
                               AND status = 'active'
                               AND NOT (end_date < :start_date OR start_date > :end_date)";
            
            $dateStmt = $db->prepare($dateCheckQuery);
            $dateStmt->bindParam(':vin', $carVin);
            $dateStmt->bindParam(':start_date', $startDate);
            $dateStmt->bindParam(':end_date', $endDate);
            $dateStmt->execute();
            $dateResult = $dateStmt->fetch(PDO::FETCH_ASSOC);
            
            // Get the original vehicle details
            $vehicleQuery = "SELECT type, make, model, daily_rate, location_id FROM vehicles WHERE car_vin = :vin";
            $vehicleStmt = $db->prepare($vehicleQuery);
            $vehicleStmt->bindParam(':vin', $carVin);
            $vehicleStmt->execute();
            $vehicleInfo = $vehicleStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($vehicleInfo) {
                $availableVehicle = getAvailableVehicleInCategory(
                    $db, 
                    $vehicleInfo['type'], 
                    $startDate, 
                    $endDate, 
                    $vehicleInfo['location_id'],
                    $vehicleInfo['make'],
                    $vehicleInfo['model'],
                    $carVin
                );
                
                if ($availableVehicle) {
                    $carVin = $availableVehicle['car_vin'];
                } else {
                    echo json_encode(['success' => false, 'error' => 'No vehicles available in this category']);
                    exit;
                }
            }
        }

        // Calculate total cost if not provided
        if (empty($totalCost)) {
            $totalCost = calculateRentalCost($db, $carVin, $startDate, $endDate);
            if ($totalCost === false) {
                echo json_encode(['success' => false, 'error' => 'Unable to calculate rental cost']);
                exit;
            }
        }

        // Get customer details
        $debugQuery = "SELECT cd.customer_id, u.user_id, u.username 
               FROM users u 
               LEFT JOIN customer_details cd ON u.user_id = cd.user_id 
               WHERE u.user_id = :user_id";
        $debugStmt = $db->prepare($debugQuery);
        $debugStmt->bindParam(':user_id', $_SESSION['user_id']);
        $debugStmt->execute();
        $customerInfo = $debugStmt->fetch(PDO::FETCH_ASSOC);

        if (!$customerInfo || !$customerInfo['customer_id']) {
            // Auto-create customer_details record for testing
            $insertCustomerQuery = "INSERT INTO customer_details (user_id) VALUES (:user_id)";
            $insertStmt = $db->prepare($insertCustomerQuery);
            $insertStmt->bindParam(':user_id', $_SESSION['user_id']);

            if ($insertStmt->execute()) {
                $customerId = $db->lastInsertId();
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to create customer profile']);
                exit;
            }
        } else {
            $customerId = $customerInfo['customer_id'];
        }

        // Start transaction
        $db->beginTransaction();

        // Create the booking
        $bookingId = createBooking($db, $customerId, $carVin, $startDate, $endDate, $totalCost);

        if (!$bookingId) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'error' => 'Failed to create booking']);
            exit;
        }

        // Create transaction record if payment was processed
        if (!empty($paymentIntentId)) {
            $transactionQuery = "INSERT INTO transactions 
                                (transaction_id, booking_id, customer_id, stripe_payment_method_id, amount, status, payment_token, payment_intent_id) 
                                VALUES (:transaction_id, :booking_id, :customer_id, :payment_method_id, :amount, :status, :payment_token, :payment_intent_id)";
            
            $transactionStmt = $db->prepare($transactionQuery);
            $transactionStmt->bindParam(':transaction_id', $paymentIntentId);
            $transactionStmt->bindParam(':booking_id', $bookingId);
            $transactionStmt->bindParam(':customer_id', $customerId);
            $transactionStmt->bindParam(':payment_method_id', $paymentMethodId);
            $transactionStmt->bindParam(':amount', $totalCost);
            $transactionStmt->bindValue(':status', 'approved');
            $transactionStmt->bindParam(':payment_token', $paymentIntentId);
            $transactionStmt->bindParam(':payment_intent_id', $paymentIntentId);

            if (!$transactionStmt->execute()) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                echo json_encode(['success' => false, 'error' => 'Failed to create transaction record']);
                exit;
            }
        }
        
        // Commit transaction
        $db->commit();

        echo json_encode([
            'success' => true,
            'booking_id' => $bookingId,
            'total_cost' => $totalCost,
            'transaction_id' => $paymentIntentId,
            'message' => 'Reservation created successfully'
        ]);

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'error' => 'An error occurred while processing your reservation']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>