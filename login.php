<?php
// login.php
// Process login form submission
// Starts the session and includes the database connection
// Returns JSON response for AJAX handling

session_start();
require 'db_connect.php';

// Set JSON header
header('Content-Type: application/json');

// Function to validate user credentials
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';

    // Trim and validate
    $identifier = trim($identifier);
    $password = trim($password);

    // Check for empty fields
    if (empty($identifier) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Please enter both fields.']);
        exit;
    }

    $user = loginUser($db, $identifier, $password);

    // If user found, set session variables
    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['success' => true, 'redirect' => 'homepage.php']);
        exit;
        
        // If user not found, return error
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid username/email or password.']);
        exit;
    }
    // If not a POST request, return error
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}
?>