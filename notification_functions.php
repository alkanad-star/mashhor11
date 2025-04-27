<?php
/**
 * Notification utility functions for sending notifications from anywhere in the application
 */

/**
 * Send a notification to a user
 * 
 * @param int|null $user_id The user ID to send to, or null for global notification
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $notification_type Type of notification (general, order, payment, system, promotion)
 * @param string $icon Font Awesome icon class
 * @param string $action_url Optional URL to navigate to when clicking the notification
 * @return bool True on success, false on failure
 */
function send_notification($user_id, $title, $message, $notification_type = 'general', $icon = 'fas fa-bell', $action_url = '') {
    global $conn;
    
    // Validate notification type
    $valid_types = ['general', 'order', 'payment', 'system', 'promotion', 'referral'];
    if (!in_array($notification_type, $valid_types)) {
        $notification_type = 'general';
    }
    
    // If user_id is provided, verify the user exists and only send if the user exists
    if ($user_id !== null) {
        $check_user = $conn->prepare("SELECT id, created_at FROM users WHERE id = ?");
        $check_user->bind_param("i", $user_id);
        $check_user->execute();
        $user_result = $check_user->get_result();
        
        if ($user_result->num_rows === 0) {
            error_log("Failed to send notification: User ID $user_id does not exist");
            return false;
        }
    }
    
    // Create notifications table if it doesn't exist
    ensure_notifications_table_exists();
    
    // Prepare query based on whether user_id is null (global) or not
    if ($user_id === null) {
        $insert_query = "INSERT INTO notifications (user_id, title, message, notification_type, icon, action_url) 
                        VALUES (NULL, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssss", $title, $message, $notification_type, $icon, $action_url);
    } else {
        $insert_query = "INSERT INTO notifications (user_id, title, message, notification_type, icon, action_url) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("isssss", $user_id, $title, $message, $notification_type, $icon, $action_url);
    }
    
    // Execute and return result
    if (!$stmt->execute()) {
        error_log("Error sending notification: " . $stmt->error);
        return false;
    }
    return true;
}

/**
 * Send a notification to all users
 * 
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $notification_type Type of notification (general, order, payment, system, promotion)
 * @param string $icon Font Awesome icon class
 * @param string $action_url Optional URL to navigate to when clicking the notification
 * @return bool True on success, false on failure
 */
function send_notification_to_all($title, $message, $notification_type = 'general', $icon = 'fas fa-bell', $action_url = '') {
    global $conn;
    
    // Ensure notifications table exists
    ensure_notifications_table_exists();
    
    // Get all users
    $users_query = "SELECT id FROM users WHERE id > 0";
    $users = $conn->query($users_query);
    
    if (!$users || $users->num_rows === 0) {
        // No users found, send a global notification instead
        return send_notification(null, $title, $message, $notification_type, $icon, $action_url);
    }
    
    // Start transaction
    $conn->begin_transaction();
    $success = true;
    
    try {
        while ($user = $users->fetch_assoc()) {
            $result = send_notification($user['id'], $title, $message, $notification_type, $icon, $action_url);
            if (!$result) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error sending notifications: " . $e->getMessage());
        return false;
    }
}

/**
 * Send an order notification to a user
 * 
 * @param int $user_id The user ID
 * @param int $order_id The order ID
 * @param string $status The order status
 * @return bool True on success, false on failure
 */
function send_order_notification($user_id, $order_id, $status) {
    // Verify the user exists first
    global $conn;
    
    $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_user->bind_param("i", $user_id);
    $check_user->execute();
    $user_result = $check_user->get_result();
    
    if ($user_result->num_rows === 0) {
        error_log("Failed to send order notification: User ID $user_id does not exist");
        return false;
    }
    
    $title = "تحديث حالة الطلب #$order_id";
    $action_url = "orders.php?id=$order_id";
    
    switch ($status) {
        case 'pending':
            $message = "تم استلام طلبك #$order_id وهو الآن قيد الانتظار.";
            break;
        case 'processing':
            $message = "تم بدء تنفيذ طلبك #$order_id.";
            break;
        case 'completed':
            $message = "تم اكتمال طلبك #$order_id بنجاح.";
            break;
        case 'partial':
            $message = "تم تنفيذ طلبك #$order_id بشكل جزئي. يرجى مراجعة التفاصيل.";
            break;
        case 'cancelled':
            $message = "تم إلغاء طلبك #$order_id. تم استرداد المبلغ إلى رصيدك.";
            break;
        case 'failed':
            $message = "عذراً، فشل تنفيذ طلبك #$order_id. تم استرداد المبلغ إلى رصيدك.";
            break;
        default:
            $message = "تم تحديث حالة طلبك #$order_id.";
    }
    
    return send_notification($user_id, $title, $message, 'order', 'fas fa-shopping-cart', $action_url);
}

/**
 * Send a payment notification to a user
 * 
 * @param int $user_id The user ID
 * @param int $transaction_id The transaction ID
 * @param string $status The payment status
 * @param float $amount The payment amount
 * @return bool True on success, false on failure
 */
function send_payment_notification($user_id, $transaction_id, $status, $amount) {
    // Verify the user exists first
    global $conn;
    
    $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_user->bind_param("i", $user_id);
    $check_user->execute();
    $user_result = $check_user->get_result();
    
    if ($user_result->num_rows === 0) {
        error_log("Failed to send payment notification: User ID $user_id does not exist");
        return false;
    }
    
    $formatted_amount = number_format($amount, 2);
    $title = "تحديث حالة الدفع #$transaction_id";
    $action_url = "balance.php";
    
    switch ($status) {
        case 'pending':
            $message = "تم استلام طلب الدفع الخاص بك بقيمة $$formatted_amount وهو قيد المراجعة.";
            break;
        case 'completed':
            $message = "تم اعتماد عملية الدفع الخاصة بك بنجاح بقيمة $$formatted_amount. تمت إضافة المبلغ إلى رصيدك.";
            break;
        case 'failed':
            $message = "عذراً، فشلت عملية الدفع الخاصة بك بقيمة $$formatted_amount.";
            break;
        default:
            $message = "تم تحديث حالة عملية الدفع الخاصة بك بقيمة $$formatted_amount.";
    }
    
    return send_notification($user_id, $title, $message, 'payment', 'fas fa-wallet', $action_url);
}

/**
 * Send a gift notification to a user
 * 
 * @param int $user_id The user ID
 * @param float $amount The gift amount
 * @param string $reason The reason for the gift
 * @return bool True on success, false on failure
 */
function send_gift_notification($user_id, $amount, $reason) {
    $formatted_amount = number_format($amount, 2);
    $title = "لقد تلقيت هدية!";
    $message = "تهانينا! لقد تلقيت هدية بقيمة $$formatted_amount من الإدارة. سبب الهدية: $reason";
    $action_url = "balance.php";
    
    return send_notification($user_id, $title, $message, 'promotion', 'fas fa-gift', $action_url);
}

/**
 * Send a referral earnings notification to a user
 * 
 * @param int $user_id The user ID
 * @param float $amount The earned amount
 * @param string $type The type of referral (signup/order)
 * @return bool True on success, false on failure
 */
function send_referral_notification($user_id, $amount, $type = 'order') {
    $formatted_amount = number_format($amount, 2);
    $title = "مكافأة إحالة";
    $action_url = "earnings.php";
    
    if ($type === 'signup') {
        $message = "لقد حصلت على $$formatted_amount كمكافأة لإحالة مستخدم جديد.";
    } else {
        $message = "لقد حصلت على $$formatted_amount كمكافأة من طلب تم بواسطة أحد المستخدمين الذين قمت بإحالتهم.";
    }
    
    return send_notification($user_id, $title, $message, 'referral', 'fas fa-hand-holding-usd', $action_url);
}

/**
 * Send a notification about a new balance addition
 * 
 * @param int $user_id The user ID
 * @param float $amount The amount added
 * @param string $method The payment method
 * @return bool True on success, false on failure
 */
function send_balance_added_notification($user_id, $amount, $method) {
    $formatted_amount = number_format($amount, 2);
    $title = "تمت إضافة رصيد لحسابك";
    $message = "تمت إضافة $$formatted_amount إلى رصيدك عبر $method.";
    $action_url = "balance.php";
    
    return send_notification($user_id, $title, $message, 'payment', 'fas fa-wallet', $action_url);
}

/**
 * Send a notification about a new order placement
 * 
 * @param int $user_id The user ID
 * @param int $order_id The order ID
 * @param float $amount The order amount
 * @param string $service_name The service name
 * @return bool True on success, false on failure
 */
function send_order_placed_notification($user_id, $order_id, $amount, $service_name) {
    $formatted_amount = number_format($amount, 2);
    $title = "تم إنشاء طلب جديد";
    $message = "تم إنشاء طلبك #$order_id لخدمة $service_name بقيمة $$formatted_amount بنجاح.";
    $action_url = "orders.php?id=$order_id";
    
    return send_notification($user_id, $title, $message, 'order', 'fas fa-shopping-cart', $action_url);
}

/**
 * Ensure the notifications table exists in the database
 * This helper function checks and creates the notifications table if it doesn't exist
 */
function ensure_notifications_table_exists() {
    global $conn;
    
    // Check if notifications table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    
    if ($table_check->num_rows === 0) {
        // Create notifications table
        $create_table_query = "CREATE TABLE notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            notification_type VARCHAR(50) DEFAULT 'general',
            icon VARCHAR(50) DEFAULT 'fas fa-bell',
            action_url VARCHAR(255) DEFAULT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->query($create_table_query);
    }
}
?>