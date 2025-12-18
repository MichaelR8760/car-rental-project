<?php
// Support tickets management page for admin users
// View all customer support tickets and update their status

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

// Handle ticket status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $ticketId = $_POST['ticket_id'];
    $newStatus = $_POST['status'];
    
    $updateQuery = "UPDATE support_tickets SET status = :status WHERE ticket_id = :ticket_id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':status', $newStatus);
    $updateStmt->bindParam(':ticket_id', $ticketId);
    $updateStmt->execute();
}

// Get all support tickets
$query = "SELECT ticket_id, email, subject, issue_description, status, created_at 
          FROM support_tickets 
          ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Ahom:wght@400;700&display=swap" rel="stylesheet">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Support Tickets - RevLink Admin</title>
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
        <h2>Support Tickets</h2>
        
        <!-- Tickets Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['ticket_id']); ?></td>
                                <td>
                                    <!-- Email link for quick contact -->
                                    <a href="mailto:<?php echo htmlspecialchars($ticket['email']); ?>">
                                        <?php echo htmlspecialchars($ticket['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <!-- Clickable subject to toggle details -->
                                    <span style="cursor: pointer;" onclick="showDetails('<?php echo $ticket['ticket_id']; ?>')">
                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Status selector with auto-submit -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                                        <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                            <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                            <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="showDetails('<?php echo $ticket['ticket_id']; ?>')">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Hidden expandable details row -->
                            <tr id="details-<?php echo $ticket['ticket_id']; ?>" style="display: none;">
                                <td colspan="6">
                                    <div class="bg-light p-3">
                                        <strong>Issue Description:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($ticket['issue_description'])); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Empty state -->
                            <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No support tickets found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle ticket details visibility
        function showDetails(ticketId) {
            const detailsRow = document.getElementById('details-' + ticketId);
            if (detailsRow.style.display === 'none') {
                detailsRow.style.display = 'table-row';
            } else {
                detailsRow.style.display = 'none';
            }
        }
    </script>
</body>
</html>