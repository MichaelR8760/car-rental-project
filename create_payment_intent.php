<?php
// create_payment_intent.php
// Create a Stripe PaymentIntent for processing a payment
// Requires Stripe PHP library: composer require stripe/stripe-php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get the amount from the request
    $amount = $_POST['amount'] ?? 0;
    
    // Validate amount
    if (!$amount || $amount < 50) { // Minimum $0.50
        echo json_encode(['success' => false, 'error' => 'Invalid amount']);
        exit;
    }
    
    try {
        require_once 'vendor/autoload.php';

        \Stripe\Stripe::setApiKey('sk_test_51S5zySHAHJVoBuDNT2aH6MM6N2oCyVuSoJhhwIqQ1OoCU3L94TQ3t88QGd2Q8UqzRoX4FZye9khNrzi7x9RmDPrw00JSWeHwf2');

        // Get user's email to find customer
        $userQuery = "SELECT email FROM users WHERE user_id = :user_id";
        $userStmt = $db->prepare($userQuery);
        $userStmt->bindParam(':user_id', $_SESSION['user_id']);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        // Find Stripe customer
        $customers = \Stripe\Customer::all(['email' => $user['email'], 'limit' => 1]);
        $customerId = !empty($customers->data) ? $customers->data[0]->id : null;

        // Create PaymentIntent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount, // Amount in cents
            'currency' => 'usd',
            'customer' => $customerId,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => [
                'customer_id' => $_SESSION['user_id'],
                'integration_check' => 'accept_a_payment'
            ]
        ]);
        
        // Return client secret
        echo json_encode([
            'success' => true,
            'client_secret' => $paymentIntent->client_secret
        ]);
        
    // Handle Stripe API errors
    } catch (\Stripe\Exception\ApiErrorException $e) {
        error_log('Stripe API error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Payment processing error']);
        
    // Handle general errors
    } catch (Exception $e) {
        error_log('Payment intent creation error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred while processing payment']);
    }
    
    // Handle non-POST requests
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>