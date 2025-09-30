<?php
// Staff management page for admin users
// Allows admins to promote users to staff roles, update roles, and remove staff

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

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        
        // Update existing staff member's role
        if ($_POST['action'] === 'update_role') {
            $userId = $_POST['user_id'];
            $newRole = $_POST['new_role'];
            
            try {
                $updateQuery = "UPDATE users SET role = :role WHERE user_id = :user_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':role', $newRole);
                $updateStmt->bindParam(':user_id', $userId);
                
                if ($updateStmt->execute()) {
                    $message = "User role updated successfully!";
                } else {
                    $error = "Failed to update user role.";
                }
            } catch (Exception $e) {
                $error = "Error updating role: " . $e->getMessage();
            }
        }
        
        // Promote user to staff role
        if ($_POST['action'] === 'promote_user') {
            $searchTerm = $_POST['search_user'];
            $newRole = $_POST['promote_role'];
            
            try {
                // Find user by username or email
                $userQuery = "SELECT user_id, username, email, role FROM users 
                             WHERE username = :search OR email = :search";
                $userStmt = $db->prepare($userQuery);
                $userStmt->bindParam(':search', $searchTerm);
                $userStmt->execute();
                $foundUser = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($foundUser) {
                    $updateQuery = "UPDATE users SET role = :role WHERE user_id = :user_id";
                    $updateStmt = $db->prepare($updateQuery);
                    $updateStmt->bindParam(':role', $newRole);
                    $updateStmt->bindParam(':user_id', $foundUser['user_id']);
                    
                    if ($updateStmt->execute()) {
                        $message = "User {$foundUser['username']} promoted to {$newRole} successfully!";
                    } else {
                        $error = "Failed to promote user.";
                    }
                } else {
                    $error = "User not found. Please check the username or email.";
                }
            } catch (Exception $e) {
                $error = "Error promoting user: " . $e->getMessage();
            }
        }
        
        // Remove staff member (demote to customer)
        if ($_POST['action'] === 'remove_staff') {
            $userId = $_POST['user_id'];
            
            try {
                $updateQuery = "UPDATE users SET role = 'customer' WHERE user_id = :user_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':user_id', $userId);
                
                if ($updateStmt->execute()) {
                    $message = "Staff member removed successfully!";
                } else {
                    $error = "Failed to remove staff member.";
                }
            } catch (Exception $e) {
                $error = "Error removing staff: " . $e->getMessage();
            }
        }
    }
}

// Get all staff members (admin and tech roles)
$staffQuery = "SELECT user_id, username, email, role 
               FROM users 
               WHERE role IN ('admin', 'tech') 
               ORDER BY role DESC, username";
$staffStmt = $db->prepare($staffQuery);
$staffStmt->execute();
$staffMembers = $staffStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Ahom:wght@400;700&display=swap" rel="stylesheet">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Management - RevLink Admin</title>
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
        <h2>Staff Management</h2>
        
        <!-- Success Message -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add Employee Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Add New Employee</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="promote_user">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="search_user" class="form-label">Username or Email</label>
                            <input type="text" class="form-control" name="search_user" id="search_user" 
                                   placeholder="Enter username or email..." required>
                        </div>
                        <div class="col-md-6">
                            <label for="promote_role" class="form-label">Role</label>
                            <select class="form-select" name="promote_role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="tech">Tech</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-success d-block">Add Employee</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Staff List -->
        <div class="card">
            <div class="card-header">
                <h5>Current Staff Members</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staffMembers as $staff): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($staff['username']); ?></td>
                                <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                <td>
                                    <!-- Role selector with auto-submit -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_role">
                                        <input type="hidden" name="user_id" value="<?php echo $staff['user_id']; ?>">
                                        <select name="new_role" class="form-select form-select-sm d-inline-block" 
                                                style="width: auto;" onchange="this.form.submit()">
                                            <option value="admin" <?php echo $staff['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="tech" <?php echo $staff['role'] === 'tech' ? 'selected' : ''; ?>>Tech</option>
                                            <option value="customer">Demote to Customer</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <!-- Prevent removing yourself -->
                                    <?php if ($staff['user_id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to remove this staff member?')">
                                            <input type="hidden" name="action" value="remove_staff">
                                            <input type="hidden" name="user_id" value="<?php echo $staff['user_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Empty state -->
                            <?php if (empty($staffMembers)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No staff members found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>