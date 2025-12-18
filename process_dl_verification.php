<?php
// Driver's license verification endpoint
// Validates and stores customer DL information for rental eligibility

session_start();

// Check user authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

require_once 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $dlNumber = $_POST['dl_number'] ?? '';
    $dlState = $_POST['dl_state'] ?? '';
    $licenseExpiration = $_POST['license_expiration_date'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    
    // Validate required fields
    if (empty($dlNumber) || empty($dlState) || empty($licenseExpiration) || empty($dateOfBirth)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }
    
    try {
        // Check if customer verification already exists
        $customerQuery = "SELECT customer_id FROM customer_details WHERE user_id = :user_id";
        $customerStmt = $db->prepare($customerQuery);
        $customerStmt->bindParam(':user_id', $_SESSION['user_id']);
        $customerStmt->execute();
        $existingCustomer = $customerStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingCustomer) {
            echo json_encode(['success' => false, 'error' => 'Customer verification already exists']);
            exit;
        }
        
        // Store only last 4 digits of DL for security
        $dlLastFour = substr($dlNumber, -4);
        $verificationToken = 'verify_' . uniqid();
        $currentTimestamp = date('Y-m-d H:i:s');
        
        // Insert customer verification record
        $insertQuery = "INSERT INTO customer_details 
                        (user_id, dl_last_four, license_expiration_date, date_of_birth, 
                         dl_verified, dl_verification_token, dl_state, verification_date) 
                        VALUES 
                        (:user_id, :dl_last_four, :license_expiration_date, :date_of_birth, 
                         1, :dl_verification_token, :dl_state, :verification_date)";
        
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':user_id', $_SESSION['user_id']);
        $insertStmt->bindParam(':dl_last_four', $dlLastFour);
        $insertStmt->bindParam(':license_expiration_date', $licenseExpiration);
        $insertStmt->bindParam(':date_of_birth', $dateOfBirth);
        $insertStmt->bindParam(':dl_verification_token', $verificationToken);
        $insertStmt->bindParam(':dl_state', $dlState);
        $insertStmt->bindParam(':verification_date', $currentTimestamp);
        
        if ($insertStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Driver license verified successfully',
                'verification_token' => $verificationToken
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save verification data']);
        }
        
    } catch (Exception $e) {
        error_log("Error processing DL verification: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>