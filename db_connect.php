<?php
// db_connect.php
// Database connection and core functions for users, vehicles, and rentals

// Database configuration
    $host = 'localhost';
    $dbname = 'revlink_rental';
    $username = 'root';
    $password = '';

try {
    // Create MySQL connection
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set error mode to exceptions
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Connection Failed: ". $e->getMessage();
    exit;
}


// User Functions
// Authenticate user login
function loginUser($db, $identifier, $password) {
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :identifier OR email = :identifier");
    $stmt->bindParam(':identifier', $identifier);
    $stmt->execute();

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return false;
}


// Register new customer account
function registerCustomer($db, $userData): bool {
    
    try {
        $db->beginTransaction();
        
        // Check for duplicate username or email
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $userData['username']);
        $stmt->bindParam(':email', $userData['email']);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $db->rollBack();
            return false;
        }

        // Insert user record
        $stmt = $db->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, role) 
                              VALUES (:username, :password, :email, :first_name, :last_name, :phone, 'customer')");
        
        $stmt->bindParam(':username', $userData['username']);
        $stmt->bindParam(':password', $userData['password']);
        $stmt->bindParam(':email', $userData['email']);
        $stmt->bindParam(':first_name', $userData['first_name']);
        $stmt->bindParam(':last_name', $userData['last_name']);
        $stmt->bindParam(':phone_number', $userData['phone_number']);
        $stmt->execute();

        $userId = $db->lastInsertId();

        // Insert customer details
        $stmt = $db->prepare("INSERT INTO customer_details (user_id, drivers_license_number, license_expiration_date, date_of_birth) 
                              VALUES (:user_id, :license_number, :license_expiration_date, :date_of_birth)");

        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':drivers_license_number', $userData['drivers_license_number']);
        $stmt->bindParam(':license_expiration_date', $userData['license_expiration_date']);
        $stmt->bindParam(':date_of_birth', $userData['date_of_birth']);
        $stmt->execute();

        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}


// Add payment method for customer
function addPaymentMethod($db, $paymentData) {
    try {
        $stmt = $db->prepare("INSERT INTO payment_methods (customer_id, card_type, last_four, expire_date) 
                               VALUES (:customer_id, :card_type, :last_four, :expire_date)");
        
        $stmt->bindParam(':customer_id', $paymentData['customer_id']);
        $stmt->bindParam(':card_type', $paymentData['card_type']);
        $stmt->bindParam(':last_four', $paymentData['last_four']);
        $stmt->bindParam(':expire_date', $paymentData['expire_date']);
        $stmt->execute();
        
        return $db->lastInsertId();
        
    } catch (Exception $e) {
        return false;
    }
}


// Vehicle Functions
// Get available vehicles with optional filters
function getAvailableCars($db, $filters = []) {
    $sql = "SELECT * FROM vehicles WHERE status = 'available'";
    
    // Apply filters if provided
    if (!empty($filters['year'])) {
        $sql .= " AND year = :year";
    }
    if (!empty($filters['type'])) {
        $sql .= " AND type = :type";
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND daily_rate <= :max_price";
    }
    
    $stmt = $db->prepare($sql);
    
    // Bind filter parameters
    if (!empty($filters['year'])) {
        $stmt->bindParam(':year', $filters['year'], PDO::PARAM_INT);
    }
    if (!empty($filters['category'])) {
        $stmt->bindParam(':category', $filters['category']);
    }
    if (!empty($filters['max_price'])) {
        $stmt->bindParam(':max_price', $filters['max_price']);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}


// Update vehicle status after inspection
function updateCarStatus($db, $carId, $status, $inspectorId = null) {
    try {
        $db->beginTransaction();
        
        // Update vehicle status
        $stmt = $db->prepare("UPDATE cars SET status = :status WHERE car_id = :car_id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':car_id', $carId);
        $stmt->execute();
        
        // Record maintenance if inspection passed
        if ($inspectorId && $status == 'available') {
            $stmt = $db->prepare("INSERT INTO maintenance (car_id, inspector_id, maintenance_date, notes) 
                                   VALUES (:car_id, :inspector_id, date('now'), 'Inspection completed')");
            
            $stmt->bindParam(':car_id', $carId);
            $stmt->bindParam(':inspector_id', $inspectorId);
            $stmt->execute();
            
            // Update last maintenance date
            $stmt = $db->prepare("UPDATE cars SET last_maintence_date = date('now') WHERE car_id = :car_id");
            $stmt->bindParam(':car_id', $carId);
            $stmt->execute();
        }
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}


// Add new vehicle to inventory
function addCar($db, $carData) {
    try {
        $stmt = $db->prepare("INSERT INTO cars (make, model, year, color, license_plate, daily_rate, status, category) 
                               VALUES (:make, :model, :year, :color, :license_plate, :daily_rate, 'pending_inspection', :category)");
        
        $stmt->bindParam(':make', $carData['make']);
        $stmt->bindParam(':model', $carData['model']);
        $stmt->bindParam(':year', $carData['year']);
        $stmt->bindParam(':color', $carData['color']);
        $stmt->bindParam(':license_plate', $carData['license_plate']);
        $stmt->bindParam(':daily_rate', $carData['daily_rate']);
        $stmt->bindParam(':category', $carData['category']);
        $stmt->execute();
        
        return $db->lastInsertId();
        
    } catch (Exception $e) {
        return false;
    }
}


// Rental Functions
// Create new rental and update vehicle status
function createRental($db, $rentalData) {
    try {
        $db->beginTransaction();
        
        // Insert rental record
        $stmt = $db->prepare("INSERT INTO rentals (customer_id, car_id, start_date, end_date, total_cost, payment_id, status) 
                               VALUES (:customer_id, :car_id, :start_date, :end_date, :total_cost, :payment_id, 'active')");
        
        $stmt->bindParam(':customer_id', $rentalData['customer_id']);
        $stmt->bindParam(':car_id', $rentalData['car_id']);
        $stmt->bindParam(':start_date', $rentalData['start_date']);
        $stmt->bindParam(':end_date', $rentalData['end_date']);
        $stmt->bindParam(':total_cost', $rentalData['total_cost']);
        $stmt->bindParam(':payment_id', $rentalData['payment_id']);
        $stmt->execute();
        
        // Update vehicle status to rented
        $stmt = $db->prepare("UPDATE cars SET status = 'rented' WHERE car_id = :car_id");
        $stmt->bindParam(':car_id', $rentalData['car_id']);
        $stmt->execute();
        
        $db->commit();
        return $db->lastInsertId();
        
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}


// Process refund for cancelled rental
function processRefund($db, $rentalId, $adminId) {
    try {
        $db->beginTransaction();
        
        // Get rental total cost
        $stmt = $db->prepare("SELECT total_cost FROM rentals WHERE rental_id = :rental_id");
        $stmt->bindParam(':rental_id', $rentalId);
        $stmt->execute();
        $rental = $stmt->fetch();
        
        if (!$rental) {
            $db->rollBack();
            return false;
        }
        
        // Create refund record
        $stmt = $db->prepare("INSERT INTO refunds (rental_id, admin_id, amount) 
                              VALUES (:rental_id, :admin_id, :amount)");
        $stmt->bindParam(':rental_id', $rentalId);
        $stmt->bindParam(':admin_id', $adminId);
        $stmt->bindParam(':amount', $rental['total_cost']);
        $stmt->execute();
        
        // Update rental status
        $stmt = $db->prepare("UPDATE rentals SET status = 'refunded', updated_at = CURRENT_TIMESTAMP 
                              WHERE rental_id = :rental_id");
        $stmt->bindParam(':rental_id', $rentalId);
        $stmt->execute();
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}