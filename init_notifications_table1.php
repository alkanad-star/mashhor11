<?php
// init_notifications_table.php
// This file creates or updates the notifications table structure
include_once 'config/db.php';

// Check if notifications table exists
$check_table_query = "SHOW TABLES LIKE 'notifications'";
$table_exists = $conn->query($check_table_query)->num_rows > 0;

if (!$table_exists) {
    // Create notifications table with a more flexible foreign key setup
    $create_table_query = "CREATE TABLE notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notification_type VARCHAR(50) DEFAULT 'general',
        icon VARCHAR(50) DEFAULT 'fas fa-bell',
        action_url VARCHAR(255) NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_table_query)) {
        echo "Notifications table created successfully";
    } else {
        // If creating with foreign key fails, try without foreign key constraint
        // This might happen if there are compatibility issues with the users table
        $create_simple_table_query = "CREATE TABLE notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notification_type VARCHAR(50) DEFAULT 'general',
            icon VARCHAR(50) DEFAULT 'fas fa-bell',
            action_url VARCHAR(255) NULL
        )";
        
        if ($conn->query($create_simple_table_query)) {
            echo "Notifications table created successfully (without foreign key constraint)";
        } else {
            echo "Error creating notifications table: " . $conn->error;
        }
    }
} else {
    // Table exists, check if we need to modify it
    
    // First, check if foreign key is causing problems
    $check_foreign_key = "SHOW CREATE TABLE notifications";
    $result = $conn->query($check_foreign_key);
    
    if ($result && $row = $result->fetch_assoc()) {
        $create_table_sql = $row['Create Table'];
        
        // If there's a foreign key constraint and it's causing problems, we might need to drop it
        if (strpos($create_table_sql, 'FOREIGN KEY') !== false && isset($_GET['drop_fk']) && $_GET['drop_fk'] == 'true') {
            // This is a dangerous operation and should only be done if absolutely necessary
            // We include a safety parameter in the URL to prevent accidental execution
            $alter_query = "ALTER TABLE notifications DROP FOREIGN KEY notifications_ibfk_1";
            if ($conn->query($alter_query)) {
                echo "Foreign key constraint removed successfully";
            } else {
                echo "Error removing foreign key constraint: " . $conn->error;
            }
        }
    }
    
    // Check if we need to add new columns
    $check_columns = $conn->query("SHOW COLUMNS FROM notifications LIKE 'notification_type'");
    if ($check_columns->num_rows == 0) {
        // Add new columns if they don't exist
        $alter_queries = [
            "ALTER TABLE notifications ADD COLUMN notification_type VARCHAR(50) DEFAULT 'general'",
            "ALTER TABLE notifications ADD COLUMN icon VARCHAR(50) DEFAULT 'fas fa-bell'",
            "ALTER TABLE notifications ADD COLUMN action_url VARCHAR(255) NULL"
        ];
        
        foreach ($alter_queries as $query) {
            if (!$conn->query($query)) {
                echo "Error adding columns: " . $conn->error;
                break;
            }
        }
        
        echo "Notifications table updated successfully";
    } else {
        echo "Notifications table already up to date";
    }
}
?>