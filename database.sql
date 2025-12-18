-- MySQL Database Schema for Car Rental Service

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    role ENUM('customer', 'admin', 'tech') NOT NULL DEFAULT 'customer'
);

-- Vehicles Table
CREATE TABLE vehicles (
    car_vin VARCHAR(30) PRIMARY KEY,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    color VARCHAR(30),
    type VARCHAR(30),
    location_id INT,
    fuel_type ENUM('gasoline', 'diesel', 'hybrid', 'electric') NOT NULL,
    mpg INT,
    image_url VARCHAR(255),
    seats INT NOT NULL,
    license_plate VARCHAR(20) NOT NULL,
    daily_rate DECIMAL(8,2) NOT NULL,
    description TEXT,
    features TEXT,
    status ENUM('pending_maintenance', 'available', 'rented', 'under_maintenance') DEFAULT 'available',
    last_maintenance_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Locations Table
CREATE TABLE locations (
    location_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    address VARCHAR(255)
);

-- Customer Details Table
CREATE TABLE customer_details (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    stripe_customer_id VARCHAR(255) UNIQUE,
    user_id INT NOT NULL UNIQUE,
    dl_last_four VARCHAR(4) NOT NULL,
    license_expiration_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_of_birth DATE NOT NULL,
    dl_verified BOOLEAN DEFAULT FALSE,
    dl_verification_token VARCHAR(255),
    dl_state VARCHAR(10),
    verification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Payment Methods Table
CREATE TABLE payment_methods (
    stripe_payment_method_id VARCHAR(255) PRIMARY KEY,
    customer_id INT NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customer_details(customer_id) ON DELETE CASCADE
);

-- Customer Bookings Table
CREATE TABLE customer_bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    car_vin VARCHAR(30) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    stripe_payment_method_id VARCHAR(255),
    status ENUM('upcoming', 'active', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    confirmation_code VARCHAR(20) UNIQUE,
    transaction_id VARCHAR(50),
    FOREIGN KEY (customer_id) REFERENCES customer_details(customer_id) ON DELETE RESTRICT,
    FOREIGN KEY (car_vin) REFERENCES vehicles(car_vin) ON DELETE RESTRICT,
    FOREIGN KEY (stripe_payment_method_id) REFERENCES payment_methods(stripe_payment_method_id) ON DELETE SET NULL
);

-- Transactions Table
CREATE TABLE transactions (
    transaction_id VARCHAR(50) PRIMARY KEY,
    booking_id INT,
    customer_id INT NOT NULL,
    stripe_payment_method_id VARCHAR(255),
    payment_intent_id VARCHAR(255),
    charge_id VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('approved', 'declined', 'refunded') NOT NULL,
    authorization_code VARCHAR(20),
    payment_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES customer_bookings(booking_id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customer_details(customer_id),
    FOREIGN KEY (stripe_payment_method_id) REFERENCES payment_methods(stripe_payment_method_id)
);

-- Refunds Table
CREATE TABLE refunds (
    refund_id INT PRIMARY KEY AUTO_INCREMENT,
    rental_id INT NOT NULL,
    admin_id INT NOT NULL,
    refund_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES customer_bookings(booking_id) ON DELETE RESTRICT,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE RESTRICT
);

-- Maintenance Table
CREATE TABLE maintenance (
    maintenance_id INT PRIMARY KEY AUTO_INCREMENT,
    car_vin VARCHAR(30) NOT NULL,
    technician_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_vin) REFERENCES vehicles(car_vin) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(user_id) ON DELETE RESTRICT
);

-- Support Tickets Table
CREATE TABLE support_tickets (
    ticket_id VARCHAR(50) PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    issue_description TEXT NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_vehicles_status ON vehicles(status);
CREATE INDEX idx_vehicles_type ON vehicles(type);
CREATE INDEX idx_bookings_dates ON customer_bookings(start_date, end_date);
CREATE INDEX idx_bookings_status ON customer_bookings(status);