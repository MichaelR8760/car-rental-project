<?php
// edit_customer_details.php
// Allow admin to edit customer details associated with a booking
// Contains HTML form and PHP processing logic

session_start();

// Only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

require_once 'db_connect.php';

$message = '';
$customerData = null;
$booking_id = $_GET['booking_id'] ?? '';

// Validate booking_id
if (empty($booking_id)) {
    die("No booking ID provided");
}

// Get customer DL info from booking
$query = "SELECT cd.*, cb.booking_id 
          FROM customer_bookings cb
          JOIN customer_details cd ON cb.customer_id = cd.customer_id
          WHERE cb.booking_id = :booking_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':booking_id', $booking_id);
$stmt->execute();
$customerData = $stmt->fetch(PDO::FETCH_ASSOC);

// If no customer data found, show error
if (!$customerData) {
    die("Customer data not found");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dlNumber = $_POST['dl_number'] ?? '';
    $dlState = $_POST['dl_state'] ?? '';
    $licenseExpirationDate = $_POST['license_expiration_date'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    
    if (empty($dlNumber) || empty($dlState) || empty($licenseExpirationDate) || empty($dateOfBirth)) {
        $message = 'All fields are required.';
    } elseif (strlen($dlNumber) < 4) {
        $message = 'Driver license number must be at least 4 characters.';
    } elseif (strtotime($licenseExpirationDate) <= time()) {
        $message = 'License expiration date must be in the future.';
    } else {
        try {
            // Extract last 4 digits from full DL number
            $dlLastFour = substr($dlNumber, -4);
            
            // Update customer details
            $updateQuery = "UPDATE customer_details 
                            SET dl_last_four = :dl_last_four, 
                                dl_state = :dl_state, 
                                license_expiration_date = :license_expiration_date, 
                                date_of_birth = :date_of_birth
                            WHERE customer_id = :customer_id";
            
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':dl_last_four', $dlLastFour);
            $updateStmt->bindParam(':dl_state', $dlState);
            $updateStmt->bindParam(':license_expiration_date', $licenseExpirationDate);
            $updateStmt->bindParam(':date_of_birth', $dateOfBirth);
            $updateStmt->bindParam(':customer_id', $customerData['customer_id']);
            
            // Execute update
            if ($updateStmt->execute()) {
                $message = 'Customer details updated successfully!';
                // Refresh customer data
                $stmt->execute();
                $customerData = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = 'Failed to update customer details.';
            }
        } catch (Exception $e) {
            error_log("Error updating customer details: " . $e->getMessage());
            $message = 'Database error occurred.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Customer Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="vehicle-card">
                    <?php if ($message): ?>
                        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    
                    <h3 class="text-center mb-4">Update Customer Details</h3>
                    <p class="text-muted text-center mb-4">Update the customer's driver license information</p>
                    
                    <form method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($customerData['booking_id']); ?>">
                        
                        <div class="mb-3">
                            <label for="dlNumber" class="form-label">Driver License Number *</label>
                            <input type="text" class="form-control" id="dlNumber" name="dl_number" placeholder="Enter full license number" required>
                            <div class="form-text">Full license number required (only last 4 digits will be stored)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="dlState" class="form-label">State *</label>
                            <select class="form-control" id="dlState" name="dl_state" required>
                                <option value="">Select State</option>
                                <option value="AL" <?php echo ($customerData['dl_state'] == 'AL') ? 'selected' : ''; ?>>AL</option>
                                <option value="AK" <?php echo ($customerData['dl_state'] == 'AK') ? 'selected' : ''; ?>>AK</option>
                                <option value="AZ" <?php echo ($customerData['dl_state'] == 'AZ') ? 'selected' : ''; ?>>AZ</option>
                                <option value="AR" <?php echo ($customerData['dl_state'] == 'AR') ? 'selected' : ''; ?>>AR</option>
                                <option value="CA" <?php echo ($customerData['dl_state'] == 'CA') ? 'selected' : ''; ?>>CA</option>
                                <option value="CO" <?php echo ($customerData['dl_state'] == 'CO') ? 'selected' : ''; ?>>CO</option>
                                <option value="CT" <?php echo ($customerData['dl_state'] == 'CT') ? 'selected' : ''; ?>>CT</option>
                                <option value="DE" <?php echo ($customerData['dl_state'] == 'DE') ? 'selected' : ''; ?>>DE</option>
                                <option value="FL" <?php echo ($customerData['dl_state'] == 'FL') ? 'selected' : ''; ?>>FL</option>
                                <option value="GA" <?php echo ($customerData['dl_state'] == 'GA') ? 'selected' : ''; ?>>GA</option>
                                <option value="HI" <?php echo ($customerData['dl_state'] == 'HI') ? 'selected' : ''; ?>>HI</option>
                                <option value="ID" <?php echo ($customerData['dl_state'] == 'ID') ? 'selected' : ''; ?>>ID</option>
                                <option value="IL" <?php echo ($customerData['dl_state'] == 'IL') ? 'selected' : ''; ?>>IL</option>
                                <option value="IN" <?php echo ($customerData['dl_state'] == 'IN') ? 'selected' : ''; ?>>IN</option>
                                <option value="IA" <?php echo ($customerData['dl_state'] == 'IA') ? 'selected' : ''; ?>>IA</option>
                                <option value="KS" <?php echo ($customerData['dl_state'] == 'KS') ? 'selected' : ''; ?>>KS</option>
                                <option value="KY" <?php echo ($customerData['dl_state'] == 'KY') ? 'selected' : ''; ?>>KY</option>
                                <option value="LA" <?php echo ($customerData['dl_state'] == 'LA') ? 'selected' : ''; ?>>LA</option>
                                <option value="ME" <?php echo ($customerData['dl_state'] == 'ME') ? 'selected' : ''; ?>>ME</option>
                                <option value="MD" <?php echo ($customerData['dl_state'] == 'MD') ? 'selected' : ''; ?>>MD</option>
                                <option value="MA" <?php echo ($customerData['dl_state'] == 'MA') ? 'selected' : ''; ?>>MA</option>
                                <option value="MI" <?php echo ($customerData['dl_state'] == 'MI') ? 'selected' : ''; ?>>MI</option>
                                <option value="MN" <?php echo ($customerData['dl_state'] == 'MN') ? 'selected' : ''; ?>>MN</option>
                                <option value="MS" <?php echo ($customerData['dl_state'] == 'MS') ? 'selected' : ''; ?>>MS</option>
                                <option value="MO" <?php echo ($customerData['dl_state'] == 'MO') ? 'selected' : ''; ?>>MO</option>
                                <option value="MT" <?php echo ($customerData['dl_state'] == 'MT') ? 'selected' : ''; ?>>MT</option>
                                <option value="NE" <?php echo ($customerData['dl_state'] == 'NE') ? 'selected' : ''; ?>>NE</option>
                                <option value="NV" <?php echo ($customerData['dl_state'] == 'NV') ? 'selected' : ''; ?>>NV</option>
                                <option value="NH" <?php echo ($customerData['dl_state'] == 'NH') ? 'selected' : ''; ?>>NH</option>
                                <option value="NJ" <?php echo ($customerData['dl_state'] == 'NJ') ? 'selected' : ''; ?>>NJ</option>
                                <option value="NM" <?php echo ($customerData['dl_state'] == 'NM') ? 'selected' : ''; ?>>NM</option>
                                <option value="NY" <?php echo ($customerData['dl_state'] == 'NY') ? 'selected' : ''; ?>>NY</option>
                                <option value="NC" <?php echo ($customerData['dl_state'] == 'NC') ? 'selected' : ''; ?>>NC</option>
                                <option value="ND" <?php echo ($customerData['dl_state'] == 'ND') ? 'selected' : ''; ?>>ND</option>
                                <option value="OH" <?php echo ($customerData['dl_state'] == 'OH') ? 'selected' : ''; ?>>OH</option>
                                <option value="OK" <?php echo ($customerData['dl_state'] == 'OK') ? 'selected' : ''; ?>>OK</option>
                                <option value="OR" <?php echo ($customerData['dl_state'] == 'OR') ? 'selected' : ''; ?>>OR</option>
                                <option value="PA" <?php echo ($customerData['dl_state'] == 'PA') ? 'selected' : ''; ?>>PA</option>
                                <option value="RI" <?php echo ($customerData['dl_state'] == 'RI') ? 'selected' : ''; ?>>RI</option>
                                <option value="SC" <?php echo ($customerData['dl_state'] == 'SC') ? 'selected' : ''; ?>>SC</option>
                                <option value="SD" <?php echo ($customerData['dl_state'] == 'SD') ? 'selected' : ''; ?>>SD</option>
                                <option value="TN" <?php echo ($customerData['dl_state'] == 'TN') ? 'selected' : ''; ?>>TN</option>
                                <option value="TX" <?php echo ($customerData['dl_state'] == 'TX') ? 'selected' : ''; ?>>TX</option>
                                <option value="UT" <?php echo ($customerData['dl_state'] == 'UT') ? 'selected' : ''; ?>>UT</option>
                                <option value="VT" <?php echo ($customerData['dl_state'] == 'VT') ? 'selected' : ''; ?>>VT</option>
                                <option value="VA" <?php echo ($customerData['dl_state'] == 'VA') ? 'selected' : ''; ?>>VA</option>
                                <option value="WA" <?php echo ($customerData['dl_state'] == 'WA') ? 'selected' : ''; ?>>WA</option>
                                <option value="WV" <?php echo ($customerData['dl_state'] == 'WV') ? 'selected' : ''; ?>>WV</option>
                                <option value="WI" <?php echo ($customerData['dl_state'] == 'WI') ? 'selected' : ''; ?>>WI</option>
                                <option value="WY" <?php echo ($customerData['dl_state'] == 'WY') ? 'selected' : ''; ?>>WY</option>
                                <!-- Add more states as needed -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="dlExpiration" class="form-label">License Expiration Date *</label>
                            <input type="date" class="form-control" id="dlExpiration" name="license_expiration_date" value="<?php echo htmlspecialchars($customerData['license_expiration_date']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="dateOfBirth" class="form-label">Date of Birth *</label>
                            <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" value="<?php echo htmlspecialchars($customerData['date_of_birth']); ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-cust-det">Update Customer Details</button>
                            <a href="manage_bookings.php" class="btn btn-outline-secondary">Back to Reservations</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>