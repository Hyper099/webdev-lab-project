<?php
/**
 * Database Configuration File
 * Contains database connection settings and establishes connection
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_PORT', 3307); // Custom MySQL port
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'expense_tracker');

// Disable mysqli exception mode temporarily to handle errors gracefully
mysqli_report(MYSQLI_REPORT_OFF);

// First, connect to MySQL server without selecting a database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);

// Check connection to MySQL server
if ($conn->connect_error) {
    die("
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 2px solid #c62828; background: #ffebee;'>
        <h2 style='color: #c62828;'>❌ Database Connection Failed</h2>
        <p><strong>Error:</strong> " . $conn->connect_error . "</p>
        <h3>Quick Fix Steps:</h3>
        <ol>
            <li>Open XAMPP Control Panel</li>
            <li>Make sure <strong>MySQL/MariaDB</strong> is running (green highlight)</li>
            <li>If it's not running, click the <strong>Start</strong> button next to MySQL</li>
            <li>Refresh this page</li>
        </ol>
        <p>If the problem persists, check if MySQL is using a password. In XAMPP, the default is no password for 'root' user.</p>
    </div>
    ");
}

// Check if database exists, if not create it
$db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");

if ($db_check && $db_check->num_rows == 0) {
    // Database doesn't exist, create it
    if ($conn->query("CREATE DATABASE " . DB_NAME)) {
        // Database created, now select it
        $conn->select_db(DB_NAME);
        
        // Create tables
        $conn->query("
            CREATE TABLE IF NOT EXISTS users (
                id INT(11) PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
        
        $conn->query("CREATE INDEX idx_user_id ON expenses(user_id)");
        $conn->query("CREATE INDEX idx_date ON expenses(date)");
        $conn->query("CREATE INDEX idx_category ON expenses(category)");
    } else {
        die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 2px solid #c62828; background: #ffebee;'>
            <h2 style='color: #c62828;'>❌ Could Not Create Database</h2>
            <p><strong>Error:</strong> " . $conn->error . "</p>
            <h3>Manual Setup Required:</h3>
            <ol>
                <li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a></li>
                <li>Click on 'New' in the left sidebar</li>
                <li>Create a database named: <strong>expense_tracker</strong></li>
                <li>Go to the SQL tab and run the script from <code>database/schema.sql</code></li>
                <li>Refresh this page</li>
            </ol>
        </div>
        ");
    }
} else {
    // Database exists, select it
    if (!$conn->select_db(DB_NAME)) {
        die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 2px solid #c62828; background: #ffebee;'>
            <h2 style='color: #c62828;'>❌ Could Not Select Database</h2>
            <p><strong>Error:</strong> " . $conn->error . "</p>
            <p>Database exists but cannot be selected. Please check MySQL permissions.</p>
        </div>
        ");
    }
}

// Set charset to utf8
$conn->set_charset("utf8");

// Re-enable mysqli exception reporting for better error handling in application
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>

