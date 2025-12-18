<?php
// booking_functions.php
// Check if a specific vehicle is available for given dates
function isVehicleAvailable($db, $carVin, $startDate, $endDate) {
    $query = "SELECT status FROM vehicles WHERE car_vin = :vin";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':vin', $carVin);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result && $result['status'] === 'available';
}

// Get all vehicles available for a date range
function getAvailableVehicles($db, $startDate, $endDate, $location = 0)
{
    // First get all available vehicles
    $query = "SELECT * FROM vehicles WHERE status = 'available' ORDER BY make, model, year";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $allVehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter out vehicles with conflicting bookings
    $availableVehicles = [];
    foreach ($allVehicles as $vehicle) {
        if (isVehicleAvailable($db, $vehicle['car_vin'], $startDate, $endDate)) {
            if ($location === 0 || (int) $vehicle['location_id'] === (int) $location) {
                $availableVehicles[] = $vehicle;
            }
        }
    }

    return $availableVehicles;
}

// Get an available vehicle in the same category, prioritizing preferred make/model and original vehicle
function getAvailableVehicleInCategory($db, $vehicleType, $startDate, $endDate, $location = null, $preferredMake = null, $preferredModel = null, $originalVin = null) {
    $query = "SELECT * FROM vehicles WHERE type = :type";
    if ($location) {
        $query .= " AND location_id = :location";
    }
    
    // Priority order: 1) Perfect match, 2) Make + Model match, 3) Daily rate
    $query .= " ORDER BY 
                CASE WHEN car_vin = :original_vin THEN 1 ELSE 4 END,
                CASE WHEN make = :preferred_make AND model = :preferred_model THEN 2 ELSE 4 END,
                daily_rate ASC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':type', $vehicleType);
    $stmt->bindParam(':original_vin', $originalVin);
    $stmt->bindParam(':preferred_make', $preferredMake);
    $stmt->bindParam(':preferred_model', $preferredModel);
    if ($location) {
        $stmt->bindParam(':location', $location);
    }
    $stmt->execute();
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check each vehicle for date conflicts
    foreach ($vehicles as $vehicle) {
        // Check if this vehicle has any booking conflicts for the requested dates
        $conflictQuery = "SELECT COUNT(*) as conflicts 
                          FROM customer_bookings 
                          WHERE car_vin = :vin 
                          AND status = 'active'
                          AND NOT (DATE_ADD(end_date, INTERVAL 1 DAY) < :start_date OR start_date > :end_date)";
        
        // Prepare and execute conflict check
        $conflictStmt = $db->prepare($conflictQuery);
        $conflictStmt->bindParam(':vin', $vehicle['car_vin']);
        $conflictStmt->bindParam(':start_date', $startDate);
        $conflictStmt->bindParam(':end_date', $endDate);
        $conflictStmt->execute();
        $result = $conflictStmt->fetch(PDO::FETCH_ASSOC);
        
        // If no conflicts, this vehicle is available for the requested dates
        if ($result['conflicts'] == 0) {
            return $vehicle;
        }
    }
    return false;
}

// Calculate total cost for a rental period
function calculateRentalCost($db, $carVin, $startDate, $endDate)
{
    // Get the vehicle's daily rate
    $query = "SELECT daily_rate FROM vehicles WHERE car_vin = :vin";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':vin', $carVin);
    $stmt->execute();
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    // If vehicle not found, return false
    if (!$vehicle) {
        return false;
    }

    // Calculate number of days
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    $days = $interval->days;

    // Calculate total cost
    $totalCost = $vehicle['daily_rate'] * $days;

    return $totalCost;
}

// Create a new booking/reservation
function createBooking($db, $customerId, $carVin, $startDate, $endDate, $totalCost)
{
    try {
        error_log("createBooking called with: customerId=$customerId, carVin=$carVin, startDate=$startDate, endDate=$endDate, totalCost=$totalCost");

        // Generate a unique confirmation code
        $confirmationCode = 'RV' . strtoupper(substr(uniqid(), -6));
        error_log("Generated confirmation code: $confirmationCode");

        // Create the booking
        $query = "INSERT INTO customer_bookings (customer_id, car_vin, start_date, end_date, total_cost, confirmation_code, status) 
                  VALUES (:customer_id, :car_vin, :start_date, :end_date, :total_cost, :confirmation_code, 'active')";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':customer_id', $customerId);
        $stmt->bindParam(':car_vin', $carVin);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->bindParam(':total_cost', $totalCost);
        $stmt->bindParam(':confirmation_code', $confirmationCode);

        if ($stmt->execute()) {
            $bookingId = $db->lastInsertId();
            error_log("Booking created successfully with ID: $bookingId");

            // Update vehicle status to rented
            $updateQuery = "UPDATE vehicles SET status = 'rented' WHERE car_vin = :vin";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':vin', $carVin);
            $updateStmt->execute();

            return $bookingId;
        } else {
            error_log("Booking INSERT failed: " . print_r($stmt->errorInfo(), true));
            return false;
        }

    } catch (Exception $e) {
        error_log("createBooking exception: " . $e->getMessage());
        return false;
    }
}

// Validate date range (start before end, not in past, etc.)
function validateDateRange($startDate, $endDate)
{
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Set to beginning of day

    // Check if start date is in the past
    if ($start < $today) {
        return ['valid' => false, 'error' => 'Start date cannot be in the past'];
    }

    // Check if start date is before end date
    if ($start >= $end) {
        return ['valid' => false, 'error' => 'End date must be after start date'];
    }

    // Check minimum rental period (at least 1 day)
    $interval = $start->diff($end);
    if ($interval->days < 1) {
        return ['valid' => false, 'error' => 'Minimum rental period is 1 day'];
    }

    // Check maximum rental period (optional - e.g., 30 days)
    if ($interval->days > 30) {
        return ['valid' => false, 'error' => 'Maximum rental period is 30 days'];
    }

    return ['valid' => true, 'error' => null];
}

// Cancel a booking
function cancelBooking($db, $bookingId, $customerId = null)
{
    try {
        $db->beginTransaction();

        // Get booking details first
        $query = "SELECT * FROM customer_bookings WHERE booking_id = :booking_id";
        if ($customerId) {
            $query .= " AND customer_id = :customer_id"; // Security check for customers
        }

        $stmt = $db->prepare($query);
        $stmt->bindParam(':booking_id', $bookingId);
        if ($customerId) {
            $stmt->bindParam(':customer_id', $customerId);
        }
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            $db->rollback();
            return false; // Booking not found or doesn't belong to customer
        }

        // Update booking status to canceled
        $cancelQuery = "UPDATE customer_bookings SET status = 'canceled' WHERE booking_id = :booking_id";
        $cancelStmt = $db->prepare($cancelQuery);
        $cancelStmt->bindParam(':booking_id', $bookingId);
        $cancelStmt->execute();

        // Update vehicle status back to available
        $vehicleQuery = "UPDATE vehicles SET status = 'available' WHERE car_vin = :vin";
        $vehicleStmt = $db->prepare($vehicleQuery);
        $vehicleStmt->bindParam(':vin', $booking['car_vin']);
        $vehicleStmt->execute();

        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

// Modify booking dates
function modifyBookingDates($db, $bookingId, $newStartDate, $newEndDate, $customerId = null)
{
    // Validate new dates
    $dateValidation = validateDateRange($newStartDate, $newEndDate);
    if (!$dateValidation['valid']) {
        return ['success' => false, 'error' => $dateValidation['error']];
    }

    // Get current booking
    $query = "SELECT * FROM customer_bookings WHERE booking_id = :booking_id";
    if ($customerId) {
        $query .= " AND customer_id = :customer_id";
    }

    $stmt = $db->prepare($query);
    $stmt->bindParam(':booking_id', $bookingId);
    if ($customerId) {
        $stmt->bindParam(':customer_id', $customerId);
    }
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        return ['success' => false, 'error' => 'Booking not found'];
    }

    // Check if vehicle is available for new dates (excluding current booking)
    $availQuery = "SELECT COUNT(*) as conflict_count 
                   FROM customer_bookings 
                   WHERE car_vin = :vin 
                   AND booking_id != :booking_id
                   AND status IN ('pending', 'active')
                   AND NOT (DATE_ADD(end_date, INTERVAL 1 DAY) < :start_date OR start_date > :end_date)";

    $availStmt = $db->prepare($availQuery);
    $availStmt->bindParam(':vin', $booking['car_vin']);
    $availStmt->bindParam(':booking_id', $bookingId);
    $availStmt->bindParam(':start_date', $newStartDate);
    $availStmt->bindParam(':end_date', $newEndDate);
    $availStmt->execute();
    $result = $availStmt->fetch(PDO::FETCH_ASSOC);

    if ($result['conflict_count'] > 0) {
        return ['success' => false, 'error' => 'Vehicle not available for new dates'];
    }

    // Calculate new cost
    $newCost = calculateRentalCost($db, $booking['car_vin'], $newStartDate, $newEndDate);

    // Update booking
    $updateQuery = "UPDATE customer_bookings 
                    SET start_date = :start_date, end_date = :end_date, total_cost = :total_cost 
                    WHERE booking_id = :booking_id";

    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':start_date', $newStartDate);
    $updateStmt->bindParam(':end_date', $newEndDate);
    $updateStmt->bindParam(':total_cost', $newCost);
    $updateStmt->bindParam(':booking_id', $bookingId);

    if ($updateStmt->execute()) {
        return ['success' => true, 'new_cost' => $newCost];
    } else {
        return ['success' => false, 'error' => 'Failed to update booking'];
    }
}

// Update vehicle status when rental ends
function updateExpiredRentals($db)
{
    try {
        $db->beginTransaction();

        // Find all bookings that have ended (end_date < today) and are still active
        $today = date('Y-m-d');
        $query = "SELECT booking_id, car_vin FROM customer_bookings 
                  WHERE end_date < :today 
                  AND status = 'active'";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        $expiredBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($expiredBookings as $booking) {
            // Update booking status to completed
            $updateBookingQuery = "UPDATE customer_bookings 
                                   SET status = 'completed' 
                                   WHERE booking_id = :booking_id";
            $bookingStmt = $db->prepare($updateBookingQuery);
            $bookingStmt->bindParam(':booking_id', $booking['booking_id']);
            $bookingStmt->execute();

            // Update vehicle status to pending_maintenance
            $updateVehicleQuery = "UPDATE vehicles 
                                   SET status = 'available' 
                                   WHERE car_vin = :vin";
            $vehicleStmt = $db->prepare($updateVehicleQuery);
            $vehicleStmt->bindParam(':vin', $booking['car_vin']);
            $vehicleStmt->execute();
        }

        $db->commit();
        return count($expiredBookings);

    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

// Check if a vehicle needs inspection (in pending_maintenance status)
function getVehiclesPendingInspection($db)
{
    $query = "SELECT v.*, cb.end_date 
              FROM vehicles v
              LEFT JOIN customer_bookings cb ON v.car_vin = cb.car_vin 
              WHERE v.status = 'pending_maintenance'
              AND (cb.status = 'completed' OR cb.status IS NULL)
              ORDER BY cb.end_date ASC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Admin function to mark vehicle as available after inspection
function markVehicleAvailable($db, $carVin)
{
    $query = "UPDATE vehicles SET status = 'available' WHERE car_vin = :vin";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':vin', $carVin);
    return $stmt->execute();
}

// Admin function to mark vehicle as under maintenance for repairs
function markVehicleUnderMaintenance($db, $carVin)
{
    $query = "UPDATE vehicles SET status = 'under_maintenance' WHERE car_vin = :vin";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':vin', $carVin);
    return $stmt->execute();
}

?>