<?php
// create_customer_portal_session.php
// Create a Stripe customer portal session for managing payment methods
// Requires Stripe PHP library: composer require stripe/stripe-php

session_start();
require_once 'vendor/autoload.php';
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo 'Error: User not logged in';
    exit;
}

// Stripe secret key
\Stripe\Stripe::setApiKey('sk_test_51S5zySHAHJVoBuDNT2aH6MM6N2oCyVuSoJhhwIqQ1OoCU3L94TQ3t88QGd2Q8UqzRoX4FZye9khNrzi7x9RmDPrw00JSWeHwf2');


try {
    // Get user's email
    $userQuery = "SELECT email FROM users WHERE user_id = :user_id";
    $userStmt = $db->prepare($userQuery);
    $userStmt->bindParam(':user_id', $_SESSION['user_id']);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    // If user not found, return error
    if (!$user) {
        echo 'Error: User not found';
        exit;
    }
    
    // Find their Stripe customer
    $customers = \Stripe\Customer::all(['email' => $user['email'], 'limit' => 1]);
    
    // If no customer found, return error
    if (empty($customers->data)) {
        echo 'No payment methods found. Please add a payment method first.';
        exit;
    }
    
    $customer = $customers->data[0];
    
    // Create portal session
    $portalSession = \Stripe\BillingPortal\Session::create([
        'customer' => $customer->id,
        'return_url' => 'http://localhost/homepage.php', // Change to your account page
    ]);
    
    header('Location: ' . $portalSession->url);
    exit();
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>