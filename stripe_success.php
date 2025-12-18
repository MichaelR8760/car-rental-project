<?php
// stripe_success.php
// Handles redirect from Stripe after adding a payment method
// Syncs payment methods from Stripe to local database

session_start();
require_once 'vendor/autoload.php';
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY') ?: 'sk_test_51S5zySHAHJVoBuDNT2aH6MM6N2oCyVuSoJhhwIqQ1OoCU3L94TQ3t88QGd2Q8UqzRoX4FZye9khNrzi7x9RmDPrw00JSWeHwf2');

// Get customer details
$custQuery = "SELECT customer_id, stripe_customer_id FROM customer_details WHERE user_id = :user_id";
$custStmt = $db->prepare($custQuery);
$custStmt->bindParam(':user_id', $_SESSION['user_id']);
$custStmt->execute();
$custData = $custStmt->fetch(PDO::FETCH_ASSOC);

if ($custData && $custData['stripe_customer_id']) {
    // Get payment methods from Stripe
    $paymentMethods = \Stripe\PaymentMethod::all([
        'customer' => $custData['stripe_customer_id'],
        'type' => 'card'
    ]);
    
    // Save each payment method
    foreach ($paymentMethods->data as $pm) {
        // Check if already exists
        $checkQuery = "SELECT stripe_payment_method_id FROM payment_methods WHERE stripe_payment_method_id = :pm_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':pm_id', $pm->id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            // Determine if this should be default (first one added)
            $countQuery = "SELECT COUNT(*) FROM payment_methods WHERE customer_id = :cust_id";
            $countStmt = $db->prepare($countQuery);
            $countStmt->bindParam(':cust_id', $custData['customer_id']);
            $countStmt->execute();
            $isDefault = ($countStmt->fetchColumn() == 0) ? 1 : 0;
            
            // Insert payment method
            $insertQuery = "INSERT INTO payment_methods (stripe_payment_method_id, customer_id, is_default) 
                           VALUES (:stripe_pm_id, :customer_id, :is_default)";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bindParam(':stripe_pm_id', $pm->id);
            $insertStmt->bindParam(':customer_id', $custData['customer_id']);
            $insertStmt->bindParam(':is_default', $isDefault);
            $insertStmt->execute();
        }
    }
}

// Redirect to checkout
header('Location: checkout.html');
exit();
?>