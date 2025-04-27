<?php
/**
 * Database Configuration
 * 
 * This file contains the database connection settings
 */

// Database credentials
define('DB_HOST', 'localhost');  // Database host (usually localhost)
define('DB_NAME', 'u286698691_mashhor');  // Database name
define('DB_USER', 'u286698691_mash');  // Database username
define('DB_PASS', 'Sw735586724@123');  // Database password

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");

// Set timezone (optional)
date_default_timezone_set('Asia/Riyadh');