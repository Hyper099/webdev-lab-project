<?php
define('DB_HOST', 'localhost');
define('DB_PORT', 3307);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'expense_tracker');

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);

if ($conn->connect_error) {
    die("Database connection failed! Please make sure XAMPP MySQL is running.");
}

// Check if database exists
$db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");

if ($db_check && $db_check->num_rows == 0) {
    if ($conn->query("CREATE DATABASE " . DB_NAME)) {
        $conn->select_db(DB_NAME);
        
        // Create tables
        $conn->query("
            CREATE TABLE IF NOT EXISTS users (
                id INT(11) PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $conn->query("
            CREATE TABLE IF NOT EXISTS expenses (
                id INT(11) PRIMARY KEY AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                category VARCHAR(50) NOT NULL,
                amount FLOAT(10,2) NOT NULL,
                date DATE NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        $conn->query("CREATE INDEX idx_user_id ON expenses(user_id)");
        $conn->query("CREATE INDEX idx_date ON expenses(date)");
        $conn->query("CREATE INDEX idx_category ON expenses(category)");
    } else {
        die("Could not create database!");
    }
} else {
    if (!$conn->select_db(DB_NAME)) {
        die("Could not select database!");
    }
}

$conn->set_charset("utf8");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>