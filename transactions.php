<?php
// Transaction history page for admin users
// Displays all transactions with search/filter capabilities and refund actions

session_start();

// Check admin authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

require_once 'booking_functions.php';
require_once 'db_connect.php';

// Get user role for dynamic pages
$userRole = $_SESSION['role'] ?? 'guest';
$isLoggedIn = isset($_SESSION['user_id']);

// Update expired rentals
if (isset($_SESSION['user_id'])) {
    updateExpiredRentals($db);
}

// Get search and filter parameters
$searchTransaction = $_GET['search_transaction'] ?? '';
$searchEmail = $_GET['search_email'] ?? '';
$locationFilter = $_GET['location_filter'] ?? '';

// Build query with joins to get full transaction details
$query = "SELECT 
    t.transaction_id,
    t.booking_id,
    t.amount,
    t.status,
    t.created_at,
    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
    u.email as customer_email,
    cb.car_vin,
    cb.start_date,
    cb.end_date,
    v.make,
    v.model,
    v.year,
    v.location_id,
    CASE v.location_id
        WHEN 1 THEN 'Rochester, MN'
        WHEN 2 THEN 'Minneapolis, MN' 
        WHEN 3 THEN 'Chicago, IL'
        WHEN 4 THEN 'Milwaukee, WI'
        ELSE 'Unknown'
    END as location_name
FROM transactions t
LEFT JOIN customer_bookings cb ON t.booking_id = cb.booking_id
LEFT JOIN customer_details cd ON t.customer_id = cd.customer_id
LEFT JOIN users u ON cd.user_id = u.user_id
LEFT JOIN vehicles v ON cb.car_vin = v.car_vin
WHERE 1=1";

$params = [];

// Apply search filters
if (!empty($searchTransaction)) {
    $query .= " AND t.transaction_id LIKE :search_transaction";
    $params[':search_transaction'] = '%' . $searchTransaction . '%';
}

if (!empty($searchEmail)) {
    $query .= " AND u.email LIKE :search_email";
    $params[':search_email'] = '%' . $searchEmail . '%';
}

if (!empty($locationFilter)) {
    $query .= " AND v.location_id = :location_filter";
    $params[':location_filter'] = $locationFilter;
}

$query .= " ORDER BY t.created_at DESC";

// Execute query with bound parameters
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Ahom:wght@400;700&display=swap" rel="stylesheet">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Transactions - RevLink Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous">
    </script>

  <link href="main.css" rel="stylesheet">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>All Transactions</h2>
        
        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Search & Filter Transactions</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search_transaction" class="form-label">Search by Transaction ID</label>
                        <input type="text" class="form-control" id="search_transaction" name="search_transaction" 
                               placeholder="Enter transaction ID..." value="<?php echo htmlspecialchars($searchTransaction); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="search_email" class="form-label">Search by Customer Email</label>
                        <input type="email" class="form-control" id="search_email" name="search_email" 
                               placeholder="Enter customer email..." value="<?php echo htmlspecialchars($searchEmail); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="location_filter" class="form-label">Filter by Location</label>
                        <select class="form-select" id="location_filter" name="location_filter">
                            <option value="">All Locations</option>
                            <option value="1" <?php echo $locationFilter === '1' ? 'selected' : ''; ?>>Rochester, MN</option>
                            <option value="2" <?php echo $locationFilter === '2' ? 'selected' : ''; ?>>Minneapolis, MN</option>
                            <option value="3" <?php echo $locationFilter === '3' ? 'selected' : ''; ?>>Chicago, IL</option>
                            <option value="4" <?php echo $locationFilter === '4' ? 'selected' : ''; ?>>Milwaukee, WI</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="transactions.php" class="btn btn-clear-transaction">Clear All</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Transactions Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Customer</th>
                                <th>Booking ID</th>
                                <th>Vehicle</th>
                                <th>Location</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($transaction['customer_name'] ?? 'N/A'); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($transaction['customer_email'] ?? 'N/A'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($transaction['booking_id'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($transaction['make']): ?>
                                        <?php echo htmlspecialchars($transaction['year'] . ' ' . $transaction['make'] . ' ' . $transaction['model']); ?><br>
                                        <small class="text-muted">VIN: <?php echo htmlspecialchars($transaction['car_vin']); ?></small>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($transaction['location_name'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                                <td>
                                    <!-- Status badge with color coding -->
                                    <span class="badge bg-<?php 
                                        echo $transaction['status'] === 'approved' ? 'success' : 
                                             ($transaction['status'] === 'declined' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($transaction['created_at'])); ?></td>
                                <td>
                                    <!-- Refund button only for approved transactions -->
                                    <?php if ($transaction['status'] === 'approved'): ?>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="initiateRefund('<?php echo $transaction['transaction_id']; ?>')">
                                            Refund
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Empty state -->
                            <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No transactions found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Process refund for a transaction
        async function initiateRefund(transactionId) {
            if (confirm('Are you sure you want to initiate a refund for transaction ' + transactionId + '?')) {
                try {
                    const response = await fetch('process_refund.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `transaction_id=${encodeURIComponent(transactionId)}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Refund processed successfully!');
                        location.reload();
                    } else {
                        alert('Error processing refund: ' + result.error);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }
    </script>
</body>
</html>