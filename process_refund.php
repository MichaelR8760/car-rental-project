<?php
// Refund processing endpoint for admin users
// Processes Stripe refunds and updates transaction/booking status

session_start();

// Check admin authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

require_once 'db_connect.php';
require_once 'vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_51S5zySHAHJVoBuDNT2aH6MM6N2oCyVuSoJhhwIqQ1OoCU3L94TQ3t88QGd2Q8UqzRoX4FZye9khNrzi7x9RmDPrw00JSWeHwf2');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transactionId = $_POST['transaction_id'] ?? '';
    
    if (empty($transactionId)) {
        echo json_encode(['success' => false, 'error' => 'Transaction ID is required']);
        exit;
    }
    
    try {
        // Get transaction details from database
        $query = "SELECT * FROM transactions WHERE transaction_id = :transaction_id AND status = 'approved'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':transaction_id', $transactionId);
        $stmt->execute();
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$transaction) {
            echo json_encode(['success' => false, 'error' => 'Transaction not found or not eligible for refund']);
            exit;
        }
        
        // Process refund through Stripe
        try {
            // Uncomment when Stripe is configured:
            
            $refund = \Stripe\Refund::create([
                'payment_intent' => $transaction['payment_intent_id'],
                'amount' => $transaction['amount'] * 100, // Stripe uses cents
            ]);
            
            
            // Simulated refund for testing
            $refund = ['id' => 'ref_' . uniqid(), 'status' => 'succeeded'];
            
            if ($refund['status'] === 'succeeded') {
                // Update transaction status to refunded
                $updateQuery = "UPDATE transactions SET status = 'refunded' WHERE transaction_id = :transaction_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':transaction_id', $transactionId);
                $updateStmt->execute();
                
                // Cancel associated booking if exists
                if ($transaction['booking_id']) {
                    $cancelQuery = "UPDATE customer_bookings SET status = 'cancelled' WHERE booking_id = :booking_id";
                    $cancelStmt = $db->prepare($cancelQuery);
                    $cancelStmt->bindParam(':booking_id', $transaction['booking_id']);
                    $cancelStmt->execute();
                    
                    // Set vehicle back to available
                    $vehicleQuery = "UPDATE vehicles v 
                                     JOIN customer_bookings cb ON v.car_vin = cb.car_vin 
                                     SET v.status = 'available' 
                                     WHERE cb.booking_id = :booking_id";
                    $vehicleStmt = $db->prepare($vehicleQuery);
                    $vehicleStmt->bindParam(':booking_id', $transaction['booking_id']);
                    $vehicleStmt->execute();
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Refund processed successfully',
                    'refund_id' => $refund['id']
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Refund failed with Stripe']);
            }
            
        } catch (Exception $stripeError) {
            echo json_encode(['success' => false, 'error' => 'Stripe error: ' . $stripeError->getMessage()]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>