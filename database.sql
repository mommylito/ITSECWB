DROP DATABASE IF EXISTS green_bean;
CREATE DATABASE green_bean;
USE green_bean;


-- USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_photo LONGTEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    failed_attempts INT DEFAULT 0,
    lockout_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- CATEGORIES
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;



-- MENU ITEMS
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_menu_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;



-- ORDERS
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_order_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;



-- ORDER ITEMS
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_orderitem_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_orderitem_menu
        FOREIGN KEY (menu_item_id)
        REFERENCES menu_items(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;



-- INSERT DATA

-- Categories
INSERT INTO categories (name) VALUES
('Coffee'),
('Specialty'),
('Bakery');


-- Menu Items
INSERT INTO menu_items (name, price, description, category_id) VALUES
('Green Bean Espresso', 3.50, 'Double shot of our sustainable roast.', 
    (SELECT id FROM categories WHERE name = 'Coffee')),
('Matcha Garden Latte', 5.25, 'Premium ceremonial matcha with oat milk.', 
    (SELECT id FROM categories WHERE name = 'Specialty')),
('Forest Cold Brew', 4.80, 'Steeped for 24 hours in cold spring water.', 
    (SELECT id FROM categories WHERE name = 'Coffee')),
('Pistachio Croissant', 4.50, 'Flaky pastry with house-made pistachio cream.', 
    (SELECT id FROM categories WHERE name = 'Bakery'));


-- Default Admin User
-- Password: admin123 (bcrypt hash for demo purposes only)
INSERT INTO users (full_name, email, password_hash, role)
VALUES (
    'Head Barista',
    'admin@greenbean.com',
    '$2y$10$89WjO.fW6I7t7/yI8K2Lh.vS8.mYm0q7z3j2k1l9o8p7q6r5s4t3u',
    'admin'
);

-- SAMPLE ORDER (Optional Demo Data)
-- Create sample order for admin
INSERT INTO orders (user_id, total_amount, status)
VALUES (
    (SELECT id FROM users WHERE email = 'admin@greenbean.com'),
    8.30,
    'completed'
);

-- Add items to the order
INSERT INTO order_items (order_id, menu_item_id, quantity, price)
VALUES
(
    (SELECT id FROM orders ORDER BY id DESC LIMIT 1),
    (SELECT id FROM menu_items WHERE name = 'Green Bean Espresso'),
    1,
    3.50
),
(
    (SELECT id FROM orders ORDER BY id DESC LIMIT 1),
    (SELECT id FROM menu_items WHERE name = 'Pistachio Croissant'),
    1,
    4.80
);
