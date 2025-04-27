<?php
// add_image_column.php
// This file adds the profile_image column to the users table

include 'config/db.php';

// Check if the column already exists
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
if ($check_column->num_rows === 0) {
    // Add profile_image column if it doesn't exist
    $alter_query = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT 'images/default-profile.png'";
    
    if ($conn->query($alter_query) === TRUE) {
        echo "Column profile_image added successfully.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column profile_image already exists.";
}

$conn->close();
?>