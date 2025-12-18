<?php
// get_payment_methods.php
// Retrieve saved payment methods for logged-in user from Stripe
// Returns JSON data

session_start();

require_once 'vendor/autoload.php';
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Set your Stripe secret key
\Stripe\Stripe::setApiKey('sk_test_51S5zySHAHJVoBuDNT2aH6MM6N2oCyVuSoJhhwIqQ1OoCU3L94TQ3t88QGd2Q8UqzRoX4FZye9khNrzi7x9RmDPrw00JSWeHwf2');

header('Content-Type: application/json');

try {
    // Get user's email
    $userQuery = "SELECT email FROM users WHERE user_id = :user_id";
    $userStmt = $db->prepare($userQuery);
    $userStmt->bindParam(':user_id', $_SESSION['user_id']);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    // If user not found
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    // Find Stripe customer by email
    $customers = \Stripe\Customer::all(['email' => $user['email'], 'limit' => 1]);

    // If no customer found
    if (empty($customers->data)) {
        // No Stripe customer found
        echo json_encode(['success' => true, 'payment_methods' => []]);
        exit;
    }

    $customer = $customers->data[0];

    // Retrieve payment methods
    $paymentMethods = \Stripe\PaymentMethod::all([
        'customer' => $customer->id,
    ]);

    // Format payment methods for response
    $methods = [];
    foreach ($paymentMethods->data as $pm) {
        $methods[] = [
            'id' => $pm->id,
            'type' => $pm->type,
            'brand' => $pm->type === 'card' ? $pm->card->brand : ucfirst($pm->type),
            'last4' => $pm->type === 'card' ? $pm->card->last4 : ucfirst($pm->type)
        ];
    }

    // Return payment methods
    echo json_encode(['success' => true, 'payment_methods' => $methods]);

    // Handle non-POST requests
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>