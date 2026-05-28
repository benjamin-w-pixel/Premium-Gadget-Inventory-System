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

-- Insert 32 premium dummy items
INSERT INTO items (name, category, quantity, price, image_url) VALUES 
('MacBook Pro 16 M3 Max', 'Laptops', 12, 2499.00, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Dell XPS 15 OLED', 'Laptops', 8, 1899.00, 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('ASUS ROG Zephyrus G14', 'Laptops', 6, 1599.00, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Lenovo ThinkPad X1 Carbon', 'Laptops', 15, 1749.00, 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('HP Spectre x360 14', 'Laptops', 10, 1449.00, 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('iPhone 15 Pro Max', 'Phones', 25, 1199.00, 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Samsung Galaxy S24 Ultra', 'Phones', 20, 1299.00, 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Google Pixel 8 Pro', 'Phones', 18, 999.00, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('OnePlus 12 Titan', 'Phones', 15, 799.00, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Sony WH-1000XM5 ANC', 'Audio', 15, 399.00, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Bose QuietComfort Ultra', 'Audio', 10, 429.00, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Apple AirPods Max', 'Audio', 12, 549.00, 'https://images.unsplash.com/photo-1613040809024-b4ef7ba99bc3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Sennheiser Momentum 4', 'Audio', 8, 349.00, 'https://images.unsplash.com/photo-1484704849700-f032a568e944?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('iPad Pro 12.9 M2', 'Tablets', 8, 1099.00, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Samsung Galaxy Tab S9', 'Tablets', 10, 899.00, 'https://images.unsplash.com/photo-1561154464-82e9adf32764?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Apple Watch Ultra 2', 'Wearables', 12, 799.00, 'https://images.unsplash.com/photo-1434493907317-a46b53b81846?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Garmin Fenix 7X Pro', 'Wearables', 7, 899.00, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Sony Alpha 7 IV Mirrorless', 'Cameras', 4, 2499.00, 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('GoPro HERO12 Black', 'Cameras', 14, 399.00, 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('DJI Osmo Pocket 3', 'Cameras', 9, 519.00, 'https://images.unsplash.com/photo-1542751371-adc38448a05e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('DJI Mini 4 Pro Drone', 'Drones', 5, 759.00, 'https://images.unsplash.com/photo-1508614589041-895b88991e3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('DJI Mavic 3 Pro Cine', 'Drones', 3, 2199.00, 'https://images.unsplash.com/photo-1527977966376-1c8408f9f108?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('PlayStation 5 Slim 1TB', 'Gaming', 16, 499.00, 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Nintendo Switch OLED', 'Gaming', 22, 349.00, 'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Meta Quest 3 VR 512GB', 'Gaming', 8, 649.00, 'https://images.unsplash.com/photo-1622979135225-d2ba269cf1ac?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Steam Deck OLED 1TB', 'Gaming', 12, 649.00, 'https://images.unsplash.com/photo-1605901309584-818e25960a8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Philips Hue Starter Pack', 'Smart Home', 14, 199.00, 'https://images.unsplash.com/photo-1558002038-1055907df827?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Apple HomePod 2nd Gen', 'Smart Home', 10, 299.00, 'https://images.unsplash.com/photo-1545454675-3531b543be5d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Keychron Q1 Pro Mechanical', 'Accessories', 12, 199.00, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Logitech MX Master 3S', 'Accessories', 25, 99.00, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Anker 3-in-1 Cube MagSafe', 'Accessories', 30, 149.00, 'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'),
('Kindle Paperwhite Signature', 'E-Readers', 30, 189.00, 'https://images.unsplash.com/photo-1594980596271-e33f69957be0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80');
