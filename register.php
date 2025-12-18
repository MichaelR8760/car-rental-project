<?php
// User registration endpoint
// Validates input, checks for duplicates, creates new customer account

require_once 'db_connect.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    // Check if username or email already exists
    $query = "SELECT COUNT(*) as count FROM users WHERE username = :username OR email = :email"; 
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row['count'] > 0) {
        http_response_code(400);
        echo 'Username or email is already in use';
        exit;
    }

    // Get remaining form data
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $phoneNumber = $_POST['phone_number'] ?? '';

    // Validate all required fields are present
    if (empty($username) || empty($email) || empty($firstName) || empty($lastName) || empty($password) || empty($phoneNumber)) {
        http_response_code(400);
        echo "Error: Missing fields";
        exit;
    }

    // Hash password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user with customer role
    $insertQuery = "INSERT INTO users (username, password, email, first_name, last_name, phone_number, role)
                    VALUES (:username, :password, :email, :first_name, :last_name, :phone_number, 'customer')";

    $stmt = $db->prepare($insertQuery);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
    $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
    $stmt->bindParam(':phone_number', $phoneNumber, PDO::PARAM_STR);

    if($stmt->execute()) {
        // Redirect to login page on success
        header('Location: login.html');
        exit;
    } else {
        http_response_code(500);
        echo "Registration failed. Please try again.";
        exit;
    }
}
?>