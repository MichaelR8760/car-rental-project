<?php
// Support ticket submission endpoint
// Creates new support ticket and returns ticket ID

session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $issue = $_POST['issue'] ?? '';
    
    // Validate required fields
    if (empty($email) || empty($subject) || empty($issue)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }
    
    try {
        // Generate unique ticket ID
        $ticketId = 'TICKET-' . strtoupper(uniqid());
        
        // Insert support ticket into database
        $query = "INSERT INTO support_tickets (ticket_id, email, subject, issue_description, status) 
                  VALUES (:ticket_id, :email, :subject, :issue, 'open')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':issue', $issue);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Support request submitted successfully',
                'ticket_id' => $ticketId
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to submit support request']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>