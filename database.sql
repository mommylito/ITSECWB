DROP DATABASE IF EXISTS green_bean;
CREATE DATABASE green_bean;
USE green_bean;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(11) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_photo LONGTEXT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    failed_attempts INT NOT NULL DEFAULT 0,
    lockout_until DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_menu_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cafe_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(120) NOT NULL,
    notes TEXT NOT NULL,
    cups_count INT NOT NULL,
    spending_amount DECIMAL(10,2) NOT NULL,
    sweetness_level INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_orderitem_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_orderitem_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
('Coffee'),
('Specialty'),
('Bakery');

INSERT INTO menu_items (name, description, price, stock, category_id) VALUES
('Green Bean Espresso', 'Double shot of our sustainable roast.', 3.50, 40, (SELECT id FROM categories WHERE name = 'Coffee')),
('Matcha Garden Latte', 'Premium ceremonial matcha with oat milk.', 5.25, 18, (SELECT id FROM categories WHERE name = 'Specialty')),
('Forest Cold Brew', 'Steeped for 24 hours in cold spring water.', 4.80, 25, (SELECT id FROM categories WHERE name = 'Coffee')),
('Pistachio Croissant', 'Flaky pastry with house-made pistachio cream.', 4.50, 12, (SELECT id FROM categories WHERE name = 'Bakery'));

INSERT INTO users (full_name, email, phone_number, password_hash, role) VALUES
('Head Barista', 'admin@greenbean.com', '09173055555', '$2y$10$1P118CKjf0wahAP9hHGO0eoEAaYvXbP8o0Wqy4xB9gW4fmHykwvTu', 'admin'),
('Sample Customer', 'user@greenbean.com', '09171234567', '$2y$10$DyBeWwgLeYNW7BeOft.2LuzHHXJ/KnfEzB4GUNB7A7iFlK4LhbVFq', 'user');

INSERT INTO cafe_reviews (user_id, title, notes, cups_count, spending_amount, sweetness_level) VALUES
((SELECT id FROM users WHERE email = 'user@greenbean.com'), 'Best morning coffee stop', 'The cold brew was smooth and the staff served quickly before class.', 2, 9.60, 5),
((SELECT id FROM users WHERE email = 'user@greenbean.com'), 'Great pastries for study sessions', 'The croissant paired well with my latte and the shop was cozy for group work.', 3, 14.75, 4);

INSERT INTO orders (user_id, total_amount, status) VALUES
((SELECT id FROM users WHERE email = 'admin@greenbean.com'), 8.30, 'completed');

INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES
((SELECT id FROM orders ORDER BY id DESC LIMIT 1), (SELECT id FROM menu_items WHERE name = 'Green Bean Espresso'), 1, 3.50),
((SELECT id FROM orders ORDER BY id DESC LIMIT 1), (SELECT id FROM menu_items WHERE name = 'Pistachio Croissant'), 1, 4.80);
