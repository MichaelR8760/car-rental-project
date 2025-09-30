<?php
// create_payment_method_session.php
// Create a Stripe Checkout Session to add a payment method
// Requires Stripe PHP library: composer require stripe/stripe-php
// Based on create_customer_portal_session.php

session_start();
require_once 'vendor/autoload.php';
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo 'Error: User not logged in';
    exit;
}

\Stripe\Stripe::setApiKey('sk_test_51S5zySHAHJVoBuDNT2aH6MM6N2oCyVuSoJhhwIqQ1OoCU3L94TQ3t88QGd2Q8UqzRoX4FZye9khNrzi7x9RmDPrw00JSWeHwf2');

try {
    // Get user's email
    $userQuery = "SELECT email FROM users WHERE user_id = :user_id";
    $userStmt = $db->prepare($userQuery);
    $userStmt->bindParam(':user_id', $_SESSION['user_id']);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    // Find existing customer or create new one
    $customers = \Stripe\Customer::all(['email' => $user['email'], 'limit' => 1]);
    
    if (!empty($customers->data)) {
        // Use existing customer
        $customer = $customers->data[0];
    } else {
        // Create new customer
        $customer = \Stripe\Customer::create([
            'email' => $user['email'],
        ]);
    }
    
    // Create Checkout Session for adding payment method
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'mode' => 'setup',
        'currency' => 'usd',
        'customer' => $customer->id,
        'success_url' => 'http://localhost/checkout.html?setup_success=1',
        'cancel_url' => 'http://localhost/checkout.html',
    ]);

    header('Location: ' . $checkout_session->url);
    exit();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>