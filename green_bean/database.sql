
CREATE DATABASE IF NOT EXISTS green_bean;
USE green_bean;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    profile_photo LONGTEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    failed_attempts INT DEFAULT 0,
    lockout_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    category VARCHAR(50)
);

-- Seed Initial Data
INSERT INTO menu_items (name, price, description, category) VALUES
('Green Bean Espresso', 3.50, 'Double shot of our sustainable roast.', 'Coffee'),
('Matcha Garden Latte', 5.25, 'Premium ceremonial matcha with oat milk.', 'Specialty'),
('Forest Cold Brew', 4.80, 'Steeped for 24 hours in cold spring water.', 'Coffee'),
('Pistachio Croissant', 4.50, 'Flaky pastry with house-made pistachio cream.', 'Bakery');

-- Default Admin (Password: admin123)
-- Note: In production, never seed plain hashes. This is for demonstration.
INSERT INTO users (full_name, email, password_hash, role) VALUES 
('Head Barista', 'admin@greenbean.com', '$2y$10$89WjO.fW6I7t7/yI8K2Lh.vS8.mYm0q7z3j2k1l9o8p7q6r5s4t3u', 'admin');
