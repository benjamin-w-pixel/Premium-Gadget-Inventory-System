<div align="center">
  <h1>🚀 Premium Gadget Inventory System</h1>
  <p>A modern, full-stack PHP/MySQL Web Application with a glassmorphism UI, advanced fuzzy search, and robust Object-Oriented backend.</p>
</div>

---

## 🌟 Overview
This project was built for the **Advanced Internet Programming** final project. It is a complete e-commerce and inventory management system designed with professional, real-world constraints in mind. It separates standard consumer purchases from backend administrative stock management.

## ✨ Key Features
*   **Modern Glassmorphism UI**: High-end CSS styling with responsive design, smooth hover transitions, and a premium look-and-feel.
*   **Advanced Smart Search**: A custom-built PHP search engine utilizing `levenshtein()` and `similar_text()`. It automatically corrects user typos (e.g., searching "i phote" instantly finds "iphone").
*   **Robust OOP Backend**: Logic is neatly encapsulated in classes (`Database.php`, `Auth.php`, `Inventory.php`).
*   **Enterprise-Grade Security**: 
    *   **CSRF Protection**: Tokens generated and validated on all form submissions.
    *   **Password Hashing**: Secure `password_hash()` implementation.
    *   **SQL Injection Prevention**: 100% PDO prepared statements.
    *   **Role-Based Access Control**: Strict segregation between `admin` and `customer` roles (admins are blocked from placing consumer orders).
*   **Advanced Validation**: Strict username (no `@`, > 4 chars) and password (length, uppercase, lowercase, numbers, symbols) validation with smart, auto-generated secure password suggestions.

## 🛠️ Tech Stack
*   **Frontend**: HTML5, Vanilla CSS3 (Custom Design System), JavaScript
*   **Backend**: PHP 8+ (Object-Oriented)
*   **Database**: MySQL / MariaDB (via PDO)

---

## ⚙️ Installation & Database Setup

Follow these instructions to run the project on your local machine using XAMPP/WAMP.

### 1. Clone & Move Files
1. Download or clone this repository.
2. Move the entire folder into your local web server's root directory:
   *   **XAMPP**: `C:\xampp\htdocs\Ip Project`
   *   **WAMP**: `C:\wamp\www\Ip Project`

### 2. Database Setup (Crucial)
You must import the database structure and default data for the application to work.

1. Start **Apache** and **MySQL** from your XAMPP Control Panel.
2. Open your browser and go to: `http://localhost/phpmyadmin/`
3. Click on **New** in the left sidebar to create a new database.
4. Name the database **`inventory_db`** and click **Create**.
5. Select the `inventory_db` database you just created.
6. Click the **Import** tab at the top.
7. Click **Choose File** and select the `schema.sql` file located in the root of this project folder.
8. Scroll down and click **Import** (or **Go**). 

*Alternatively, you can run this via terminal:*
```bash
mysql -u root -p < schema.sql
```

### 3. Connect the Application
The `config.php` file is pre-configured for standard XAMPP setups. If your MySQL uses a different username or password, update it here:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Your MySQL Username
define('DB_PASS', '');     // Your MySQL Password
define('DB_NAME', 'inventory_db');
```

---

## 🚀 Running the App
1. Open your browser.
2. Navigate to: `http://localhost/Ip Project/index.php`

### 🔑 Default Credentials
A default Admin account is automatically created when you import `schema.sql`:

*   **Role:** Administrator
*   **Username:** `admin`
*   **Password:** `Admin@123`

---

## 📂 Project Structure
```text
📦 Ip Project
 ┣ 📜 index.php            # Main Shop Homepage & Smart Search
 ┣ 📜 admin_dashboard.php  # Admin Panel (CRUD Operations)
 ┣ 📜 dashboard.php        # User Dashboard
 ┣ 📜 add_item.php         # Create Inventory Item
 ┣ 📜 edit_item.php        # Update Inventory Item
 ┣ 📜 checkout.php         # Secure Checkout Interface
 ┣ 📜 register.php         # User Registration (Strict Validation)
 ┣ 📜 login.php            # Authentication
 ┣ 📜 style.css            # Custom Glassmorphism UI
 ┣ 📜 schema.sql           # Database Structure & Dummy Data
 ┣ ⚙️ Auth.php             # OOP Authentication & CSRF Class
 ┣ ⚙️ Inventory.php        # OOP Inventory Management Class
 ┗ ⚙️ Database.php         # OOP PDO Database Connection
```

---
*Developed for Advanced Internet Programming Final Project.*
