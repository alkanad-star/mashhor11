<?php
// Database connection details
define('DB_HOST', 'localhost');
define('DB_NAME', 'u286698691_mashhor');
define('DB_USER', 'u286698691_mash');
define('DB_PASS', 'Sw735586724@123');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate hashed password
$password = "Sw123456@123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Update admin password
$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "Password updated successfully! The hashed password is: " . $hashed_password;
} else {
    echo "Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?>