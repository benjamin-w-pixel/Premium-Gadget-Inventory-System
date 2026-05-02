CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    image_url VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- Insert a default admin user (password: Admin@123)
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$wUKsJkySM00EK0gGGd9r5erMJJZO5Y3Cnh3wiuGVc2iRN0pn5ZQX6', 'admin');

-- Insert 10 dummy items
INSERT INTO items (name, category, quantity, price, image_url) VALUES 
('MacBook Pro 16', 'Laptops', 10, 2499.00, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('iPhone 15 Pro', 'Phones', 25, 999.00, 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Sony WH-1000XM5', 'Audio', 15, 399.00, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('iPad Pro 12.9', 'Tablets', 8, 1099.00, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Apple Watch Ultra 2', 'Wearables', 12, 799.00, 'https://images.unsplash.com/photo-1434493907317-a46b53b81846?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Samsung Galaxy S24 Ultra', 'Phones', 20, 1299.00, 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Bose QuietComfort Ultra', 'Audio', 10, 429.00, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('GoPro HERO12 Black', 'Cameras', 5, 399.00, 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('DJI Mini 4 Pro', 'Drones', 4, 759.00, 'https://images.unsplash.com/photo-1508614589041-895b88991e3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Kindle Paperwhite', 'E-Readers', 30, 149.00, 'https://images.unsplash.com/photo-1594980596271-e33f69957be0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80');
