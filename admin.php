<?php
// admin.php
session_start();
$page_title = "لوحة الإدارة - متجر مشهور";

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'config/db.php';

// Get active section based on URL parameter
$section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

// Include notification functions file
include_once 'notification_functions.php';

// Include referral functions
include_once 'referral_functions.php';

// Process user management - adding a new user
if (isset($_POST['add_user'])) {
    $new_username = filter_input(INPUT_POST, 'new_username', FILTER_SANITIZE_STRING);
    $new_full_name = filter_input(INPUT_POST, 'new_full_name', FILTER_SANITIZE_STRING);
    $new_email = filter_input(INPUT_POST, 'new_email', FILTER_SANITIZE_EMAIL);
    $new_phone = filter_input(INPUT_POST, 'new_phone', FILTER_SANITIZE_STRING);
    $new_country = filter_input(INPUT_POST, 'new_country', FILTER_SANITIZE_STRING);
    $new_balance = filter_input(INPUT_POST, 'new_balance', FILTER_VALIDATE_FLOAT) ?: 0;
    $new_role = filter_input(INPUT_POST, 'new_role', FILTER_SANITIZE_STRING);
    $new_password = $_POST['new_user_password'] ?? '';
    
    // Validate required fields
    $errors = [];
    
    if (empty($new_username)) {
        $errors[] = "اسم المستخدم مطلوب";
    }
    
    if (empty($new_email)) {
        $errors[] = "البريد الإلكتروني مطلوب";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صالح";
    }
    
    if (empty($new_full_name)) {
        $errors[] = "الاسم الكامل مطلوب";
    }
    
    if (empty($new_password)) {
        $errors[] = "كلمة المرور مطلوبة";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "يجب أن تكون كلمة المرور 6 أحرف على الأقل";
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $new_username, $new_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $existing_user = $result->fetch_assoc();
            if ($existing_user['username'] === $new_username) {
                $errors[] = "اسم المستخدم مستخدم بالفعل";
            }
            if ($existing_user['email'] === $new_email) {
                $errors[] = "البريد الإلكتروني مستخدم بالفعل";
            }
        }
    }
    
    // Insert new user
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Insert user
        $insert_query = "INSERT INTO users (username, email, password, full_name, phone, country, balance, role, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssssids", $new_username, $new_email, $hashed_password, $new_full_name, $new_phone, 
                                     $new_country, $new_balance, $new_role);
        
        if ($stmt->execute()) {
            $new_user_id = $conn->insert_id;
            
            // Generate and set referral code
            $referral_code = generateReferralCode($new_user_id);
            $update_code = $conn->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
            $update_code->bind_param("si", $referral_code, $new_user_id);
            $update_code->execute();
            
            // Log the action
            $admin_id = $_SESSION['user_id'];
            $log_action = "إضافة مستخدم جديد: " . $new_username;
            logAdminAction($conn, $admin_id, 'add_user', $log_action);
            
            $success_message = "تم إضافة المستخدم بنجاح.";
        } else {
            $error_message = "حدث خطأ أثناء إضافة المستخدم: " . $conn->error;
        }
    } else {
        $error_message = "يرجى تصحيح الأخطاء التالية:<br>" . implode("<br>", $errors);
    }
}

// Process editing a user
if (isset($_POST['edit_user'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $balance = filter_input(INPUT_POST, 'balance', FILTER_VALIDATE_FLOAT);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $referral_percentage = filter_input(INPUT_POST, 'referral_percentage', FILTER_VALIDATE_FLOAT);
    $new_password = $_POST['new_password'] ?? '';
    
    // Get original user data for comparison
    $original_user_query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($original_user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $original_user = $stmt->get_result()->fetch_assoc();
    
    // Check if email is being changed and if it already exists
    $errors = [];
    if ($email !== $original_user['email']) {
        $check_email_query = "SELECT * FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($check_email_query);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "البريد الإلكتروني مستخدم بالفعل";
        }
    }
    
    // Check if username is being changed and if it already exists
    if ($username !== $original_user['username']) {
        $check_username_query = "SELECT * FROM users WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($check_username_query);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "اسم المستخدم مستخدم بالفعل";
        }
    }
    
    // Update user if no errors
    if (empty($errors)) {
        // Start with basic user data update
        $update_query = "UPDATE users SET 
                         username = ?, 
                         full_name = ?, 
                         email = ?, 
                         phone = ?, 
                         country = ?, 
                         role = ?, 
                         balance = ?";
        
        // Track parameter types and values
        $types = "ssssssd";
        $params = [$username, $full_name, $email, $phone, $country, $role, $balance];
        
        // Add referral percentage if provided
        if ($referral_percentage !== false) {
            $update_query .= ", referral_percentage = ?";
            $types .= "d";
            $params[] = $referral_percentage;
        }
        
        // Add new password if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query .= ", password = ?";
            $types .= "s";
            $params[] = $hashed_password;
        }
        
        // Complete the query
        $update_query .= " WHERE id = ?";
        $types .= "i";
        $params[] = $user_id;
        
        // Execute update
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            // Track balance change
            if ($balance != $original_user['balance']) {
                $balance_diff = $balance - $original_user['balance'];
                
                // Add transaction record if balance changed
                if ($balance_diff != 0) {
                    $transaction_type = $balance_diff > 0 ? 'deposit' : 'withdraw';
                    $transaction_amount = abs($balance_diff);
                    $transaction_description = $balance_diff > 0 
                                              ? "تعديل الرصيد بواسطة الإدارة (إضافة)" 
                                              : "تعديل الرصيد بواسطة الإدارة (خصم)";
                    
                    $transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) 
                                        VALUES (?, ?, ?, 'completed', ?)";
                    $stmt = $conn->prepare($transaction_query);
                    $stmt->bind_param("idss", $user_id, $transaction_amount, $transaction_type, $transaction_description);
                    $stmt->execute();
                    
                    // Send notification about balance change
                    $notification_title = $balance_diff > 0 ? "تمت إضافة رصيد لحسابك" : "تم خصم رصيد من حسابك";
                    $notification_message = $balance_diff > 0 
                                           ? "تمت إضافة $" . number_format($transaction_amount, 2) . " إلى رصيدك بواسطة الإدارة."
                                           : "تم خصم $" . number_format($transaction_amount, 2) . " من رصيدك بواسطة الإدارة.";
                    
                    // Check if notifications table exists
                    $check_table_query = "SHOW TABLES LIKE 'notifications'";
                    $table_exists = $conn->query($check_table_query)->num_rows > 0;
                    
                    if ($table_exists) {
                        $insert_notification_query = "INSERT INTO notifications (user_id, title, message, icon) 
                                                    VALUES (?, ?, ?, ?)";
                        $icon = $balance_diff > 0 ? "fas fa-wallet" : "fas fa-money-bill-wave";
                        $stmt = $conn->prepare($insert_notification_query);
                        $stmt->bind_param("isss", $user_id, $notification_title, $notification_message, $icon);
                        $stmt->execute();
                    }
                }
            }
            
            // Log the action
            $admin_id = $_SESSION['user_id'];
            $log_action = "تعديل المستخدم: " . $username;
            logAdminAction($conn, $admin_id, 'edit_user', $log_action);
            
            $success_message = "تم تحديث بيانات المستخدم بنجاح.";
        } else {
            $error_message = "حدث خطأ أثناء تحديث بيانات المستخدم: " . $conn->error;
        }
    } else {
        $error_message = "يرجى تصحيح الأخطاء التالية:<br>" . implode("<br>", $errors);
    }
}

// Process user suspension/activation
if (isset($_POST['toggle_user_status'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $new_status = filter_input(INPUT_POST, 'new_status', FILTER_VALIDATE_INT);
    
    $update_query = "UPDATE users SET is_active = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $new_status, $user_id);
    
    if ($stmt->execute()) {
        // Get user info for notification
        $user_query = "SELECT username FROM users WHERE id = ?";
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        // Log the action
        $admin_id = $_SESSION['user_id'];
        $action_type = $new_status == 1 ? 'activate_user' : 'suspend_user';
        $log_action = $new_status == 1 
                     ? "تفعيل المستخدم: " . $user['username'] 
                     : "تعليق المستخدم: " . $user['username'];
        logAdminAction($conn, $admin_id, $action_type, $log_action);
        
        // Send notification to user
        $notification_title = $new_status == 1 ? "تم تفعيل حسابك" : "تم تعليق حسابك";
        $notification_message = $new_status == 1 
                              ? "تم تفعيل حسابك بنجاح. يمكنك الآن استخدام جميع خدمات الموقع." 
                              : "تم تعليق حسابك. يرجى التواصل مع الدعم الفني لمزيد من المعلومات.";
        $icon = $new_status == 1 ? "fas fa-check-circle" : "fas fa-ban";
        
        // Check if notifications table exists
        $check_table_query = "SHOW TABLES LIKE 'notifications'";
        $table_exists = $conn->query($check_table_query)->num_rows > 0;
        
        if ($table_exists) {
            $insert_notification_query = "INSERT INTO notifications (user_id, title, message, icon) 
                                        VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_notification_query);
            $stmt->bind_param("isss", $user_id, $notification_title, $notification_message, $icon);
            $stmt->execute();
        }
        
        $success_message = $new_status == 1 
                         ? "تم تفعيل المستخدم بنجاح." 
                         : "تم تعليق المستخدم بنجاح.";
    } else {
        $error_message = "حدث خطأ أثناء تحديث حالة المستخدم: " . $conn->error;
    }
}

if ($section === 'notifications') {
    include_once 'admin_notification_handler.php';
}

// Process order status update
if (isset($_POST['update_order_status'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $remains = isset($_POST['remains']) ? filter_input(INPUT_POST, 'remains', FILTER_SANITIZE_NUMBER_INT) : 0;
    
    // Get current order
    $order_query = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if ($order) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update order status
            $update_query = "UPDATE orders SET status = ?, remains = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sii", $new_status, $remains, $order_id);
            $stmt->execute();
            
            // If status is completed, update spent and in_use in a single query
            if ($new_status === 'completed' && $order['status'] !== 'completed') {
                // Update user's spent amount and decrease in_use
                $update_spent_query = "UPDATE users SET spent = spent + ?, in_use = in_use - ? WHERE id = ?";
                $stmt = $conn->prepare($update_spent_query);
                $stmt->bind_param("ddi", $order['amount'], $order['amount'], $order['user_id']);
                $stmt->execute();
                
                // Process pending referral reward
                completePendingReferralReward($order_id);
            }
            // For cancelled orders
            else if ($new_status === 'cancelled' && $order['status'] !== 'cancelled') {
                // Release in_use balance
                $update_balance_query = "UPDATE users SET in_use = in_use - ? WHERE id = ?";
                $stmt = $conn->prepare($update_balance_query);
                $stmt->bind_param("di", $order['amount'], $order['user_id']);
                $stmt->execute();
                
                // Refund amount to user balance
                $refund_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
                $stmt = $conn->prepare($refund_query);
                $stmt->bind_param("di", $order['amount'], $order['user_id']);
                $stmt->execute();
                
                // Create refund transaction
                $description = "استرداد المبلغ لإلغاء الطلب #" . $order_id;
                $insert_transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) VALUES (?, ?, 'refund', 'completed', ?)";
                $stmt = $conn->prepare($insert_transaction_query);
                $stmt->bind_param("ids", $order['user_id'], $order['amount'], $description);
                $stmt->execute();
            }
            
            // For partial delivery, adjust remaining balance
            if ($new_status === 'partial' && $order['status'] !== 'partial') {
                $delivered_percentage = ($order['quantity'] - $remains) / $order['quantity'];
                $used_amount = $order['amount'] * $delivered_percentage;
                $refund_amount = $order['amount'] - $used_amount;
                
                // Release in_use balance
                $update_inuse_query = "UPDATE users SET in_use = in_use - ? WHERE id = ?";
                $stmt = $conn->prepare($update_inuse_query);
                $stmt->bind_param("di", $order['amount'], $order['user_id']);
                $stmt->execute();
                
                // Refund the unused amount
                $refund_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
                $stmt = $conn->prepare($refund_query);
                $stmt->bind_param("di", $refund_amount, $order['user_id']);
                $stmt->execute();
                
                // Create partial refund transaction
                $description = "استرداد جزئي للطلب #" . $order_id;
                $insert_transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) VALUES (?, ?, 'refund', 'completed', ?)";
                $stmt = $conn->prepare($insert_transaction_query);
                $stmt->bind_param("ids", $order['user_id'], $refund_amount, $description);
                $stmt->execute();
            }
            
            $conn->commit();
            $success_message = "تم تحديث حالة الطلب بنجاح.";
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "حدث خطأ أثناء تحديث حالة الطلب: " . $e->getMessage();
        }
    } else {
        $error_message = "الطلب غير موجود.";
    }
}

// Process payment approval
if (isset($_POST['approve_payment'])) {
    $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Get transaction details
    $transaction_query = "SELECT * FROM transactions WHERE id = ? AND status = 'pending'";
    $stmt = $conn->prepare($transaction_query);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $transaction = $stmt->get_result()->fetch_assoc();
    
    if ($transaction) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update transaction status
            $update_query = "UPDATE transactions SET status = 'completed' WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("i", $transaction_id);
            $stmt->execute();
            
            // Add amount to user's balance
            $update_balance_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $stmt = $conn->prepare($update_balance_query);
            $stmt->bind_param("di", $transaction['amount'], $transaction['user_id']);
            $stmt->execute();
            
            // Send notification to user about payment approval
            send_payment_notification($transaction['user_id'], $transaction_id, 'completed', $transaction['amount']);
            
            $conn->commit();
            $success_message = "تم اعتماد عملية الدفع بنجاح.";
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "حدث خطأ أثناء اعتماد عملية الدفع: " . $e->getMessage();
        }
    } else {
        $error_message = "عملية الدفع غير موجودة أو تم اعتمادها بالفعل.";
    }
}

// Process payment rejection
if (isset($_POST['reject_payment'])) {
    $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Get transaction details
    $transaction_query = "SELECT * FROM transactions WHERE id = ? AND status = 'pending'";
    $stmt = $conn->prepare($transaction_query);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $transaction = $stmt->get_result()->fetch_assoc();
    
    if ($transaction) {
        // Update transaction status
        $update_query = "UPDATE transactions SET status = 'failed' WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $transaction_id);
        
        if ($stmt->execute()) {
            // Send notification to user about payment rejection
            send_payment_notification($transaction['user_id'], $transaction_id, 'failed', $transaction['amount']);
            
            $success_message = "تم رفض عملية الدفع.";
        } else {
            $error_message = "حدث خطأ أثناء رفض عملية الدفع.";
        }
    } else {
        $error_message = "عملية الدفع غير موجودة أو تم اعتمادها بالفعل.";
    }
}

// Process add funds to user
if (isset($_POST['add_funds'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    
    if ($username && $amount > 0) {
        // Get user details
        $user_query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user) {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Add amount to user's balance
                $update_balance_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
                $stmt = $conn->prepare($update_balance_query);
                $stmt->bind_param("di", $amount, $user['id']);
                $stmt->execute();
                
                // Create transaction record
                $insert_transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) VALUES (?, ?, 'deposit', 'completed', ?)";
                $stmt = $conn->prepare($insert_transaction_query);
                
                // Create description if not provided
                if (empty($description)) {
                    $description = "إضافة رصيد بواسطة الإدارة - " . $payment_method;
                }
                
                $stmt->bind_param("ids", $user['id'], $amount, $description);
                $stmt->execute();
                $transaction_id = $conn->insert_id;
                
                // Send notification about manual fund addition
                send_balance_added_notification($user['id'], $amount, $payment_method);
                
                $conn->commit();
                $success_message = "تم إضافة الرصيد بنجاح للمستخدم " . $username;
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "حدث خطأ أثناء إضافة الرصيد: " . $e->getMessage();
            }
        } else {
            $error_message = "المستخدم غير موجود.";
        }
    } else {
        $error_message = "الرجاء التأكد من صحة البيانات المدخلة.";
    }
}

// Process sending notification
if (isset($_POST['send_notification'])) {
    $target_users = filter_input(INPUT_POST, 'target_users', FILTER_SANITIZE_STRING);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $icon = filter_input(INPUT_POST, 'icon', FILTER_SANITIZE_STRING) ?: 'fas fa-bell';
    $action_url = filter_input(INPUT_POST, 'action_url', FILTER_SANITIZE_STRING);
    $notification_type = filter_input(INPUT_POST, 'notification_type', FILTER_SANITIZE_STRING) ?: 'general';
    
    if ($title && $message) {
        // Check if notifications table exists
        $check_table_query = "SHOW TABLES LIKE 'notifications'";
        $table_exists = $conn->query($check_table_query)->num_rows > 0;
        
        if (!$table_exists) {
            // Create notifications table
            $create_table_query = "CREATE TABLE notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                icon VARCHAR(50) DEFAULT 'fas fa-bell',
                notification_type VARCHAR(50) DEFAULT 'general',
                action_url VARCHAR(255) DEFAULT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $conn->query($create_table_query);
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            if ($target_users === 'all') {
                // Send to all users
                $users_query = "SELECT id FROM users";
                $users = $conn->query($users_query);
                
                while ($user = $users->fetch_assoc()) {
                    $insert_query = "INSERT INTO notifications (user_id, title, message, icon, notification_type, action_url) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_query);
                    $stmt->bind_param("isssss", $user['id'], $title, $message, $icon, $notification_type, $action_url);
                    $stmt->execute();
                }
            } else if ($target_users === 'specific' && isset($_POST['user_id'])) {
                // Send to specific user
                $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
                $insert_query = "INSERT INTO notifications (user_id, title, message, icon, notification_type, action_url) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("isssss", $user_id, $title, $message, $icon, $notification_type, $action_url);
                $stmt->execute();
            } else {
                // Send global notification (user_id = NULL)
                $insert_query = "INSERT INTO notifications (user_id, title, message, icon, notification_type, action_url) VALUES (NULL, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("sssss", $title, $message, $icon, $notification_type, $action_url);
                $stmt->execute();
            }
            
            $conn->commit();
            $success_message = "تم إرسال الإشعار بنجاح.";
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "حدث خطأ أثناء إرسال الإشعار: " . $e->getMessage();
        }
    } else {
        $error_message = "الرجاء إدخال عنوان ونص الإشعار.";
    }
}

// Process adding gift to user
if (isset($_POST['add_gift'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $amount = filter_input(INPUT_POST, 'gift_amount', FILTER_VALIDATE_FLOAT);
    $reason = filter_input(INPUT_POST, 'gift_reason', FILTER_SANITIZE_STRING);
    
    if ($username && $amount > 0) {
        // Get user details
        $user_query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user) {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Add amount to user's balance
                $update_balance_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
                $stmt = $conn->prepare($update_balance_query);
                $stmt->bind_param("di", $amount, $user['id']);
                $stmt->execute();
                
                // Create transaction record
                $description = "هدية من الإدارة: " . $reason;
                $insert_transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) VALUES (?, ?, 'deposit', 'completed', ?)";
                $stmt = $conn->prepare($insert_transaction_query);
                $stmt->bind_param("ids", $user['id'], $amount, $description);
                $stmt->execute();
                
                // Send gift notification to user
                send_gift_notification($user['id'], $amount, $reason);
                
                $conn->commit();
                $success_message = "تم إضافة الهدية بنجاح للمستخدم " . $username;
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "حدث خطأ أثناء إضافة الهدية: " . $e->getMessage();
            }
        } else {
            $error_message = "المستخدم غير موجود.";
        }
    } else {
        $error_message = "الرجاء التأكد من صحة البيانات المدخلة.";
    }
}

// Helper function to log admin actions
function logAdminAction($conn, $admin_id, $action_type, $description) {
    // Check if admin_logs table exists
    $check_table_query = "SHOW TABLES LIKE 'admin_logs'";
    $table_exists = $conn->query($check_table_query)->num_rows > 0;
    
    if (!$table_exists) {
        // Create admin_logs table
        $create_table_query = "CREATE TABLE admin_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            action_type VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->query($create_table_query);
    }
    
    // Get client IP
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Insert log
    $insert_log_query = "INSERT INTO admin_logs (admin_id, action_type, description, ip_address) 
                        VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_log_query);
    $stmt->bind_param("isss", $admin_id, $action_type, $description, $ip_address);
    $stmt->execute();
}

// Get stats for dashboard
$total_orders_query = "SELECT COUNT(*) as total FROM orders";
$pending_orders_query = "SELECT COUNT(*) as pending FROM orders WHERE status = 'pending'";
$processing_orders_query = "SELECT COUNT(*) as processing FROM orders WHERE status = 'processing'";
$completed_orders_query = "SELECT COUNT(*) as completed FROM orders WHERE status = 'completed'";
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_revenue_query = "SELECT SUM(amount) as total FROM transactions WHERE type = 'deposit' AND status = 'completed'";
$pending_payments_query = "SELECT COUNT(*) as pending FROM transactions WHERE type = 'deposit' AND status = 'pending'";

$total_orders = $conn->query($total_orders_query)->fetch_assoc()['total'];
$pending_orders = $conn->query($pending_orders_query)->fetch_assoc()['pending'];
$processing_orders = $conn->query($processing_orders_query)->fetch_assoc()['processing'];
$completed_orders = $conn->query($completed_orders_query)->fetch_assoc()['completed'];
$total_users = $conn->query($total_users_query)->fetch_assoc()['total'];
$total_revenue = $conn->query($total_revenue_query)->fetch_assoc()['total'] ?? 0;
$pending_payments = $conn->query($pending_payments_query)->fetch_assoc()['pending'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/png" href="/images/logo.png" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --primary-color: #2196F3;
            --secondary-color: #F44336;
            --background-color: #f8f9fa;
            --text-color: #333;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding-bottom: 20px;
        }
        
        /* Admin Layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: var(--sidebar-width);
            background-color: #212529;
            color: #fff;
            position: fixed;
            top: 0;
            right: 0;
            height: 100%;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
        }
        
        .admin-content {
            flex: 1;
            margin-right: var(--sidebar-width);
            padding: 20px;
            overflow-x: hidden; /* Prevent horizontal scrolling */
        }
        
        .admin-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            background-color: #343a40;
            border-bottom: 1px solid #495057;
        }
        
        .admin-logo {
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .admin-logo img {
            width: 30px;
            height: 30px;
            margin-left: 10px;
        }
        
        .admin-menu {
            padding: 15px 0;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #adb5bd;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border-right: 3px solid transparent;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: #343a40;
            color: #fff;
            border-right-color: var(--primary-color);
        }
        
        .menu-item i {
            margin-left: 10px;
            width: 20px;
            text-align: center;
        }
        
        .admin-footer {
            padding: 15px 20px;
            font-size: 0.8rem;
            color: #6c757d;
            text-align: center;
            border-top: 1px solid #495057;
        }
        
        /* Dashboard Stats */
        .stats-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .stats-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 1.5rem;
            color: #fff;
        }
        
        .stats-card-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stats-card-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        /* Data Tables Customization */
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
        }
        
        /* Specific fixes for users section */
        .users-section table {
            width: 100% !important;
            table-layout: fixed !important;
            display: table !important;
            direction: rtl !important;
        }
        
        .users-section table tr {
            display: table-row !important;
            width: 100% !important;
        }
        
        .users-section table th,
        .users-section table td {
            display: table-cell !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .users-section .dataTables_wrapper {
            width: 100% !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 60px;
                overflow: visible;
            }
            
            .admin-content {
                margin-right: 60px;
            }
            
            .menu-text {
                display: none;
            }
            
            .admin-header {
                justify-content: center;
                padding: 10px;
            }
            
            .admin-logo span {
                display: none;
            }
            
            .menu-item {
                justify-content: center;
                padding: 12px;
            }
            
            .menu-item i {
                margin: 0;
            }
            
            .admin-footer {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="admin-header">
                <a href="admin.php" class="admin-logo">
                    <img src="/images/logo.png" alt="Logo">
                    <span>لوحة الإدارة</span>
                </a>
            </div>
            
            <div class="admin-menu">
                <a href="admin.php?section=dashboard" class="menu-item <?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">لوحة التحكم</span>
                </a>
                
                <a href="admin.php?section=orders" class="menu-item <?php echo $section === 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="menu-text">إدارة الطلبات</span>
                </a>
                
                <a href="admin.php?section=payments" class="menu-item <?php echo $section === 'payments' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span class="menu-text">إدارة المدفوعات</span>
                </a>
                
                <a href="admin.php?section=users" class="menu-item <?php echo $section === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">إدارة المستخدمين</span>
                </a>
                
                <a href="admin.php?section=services" class="menu-item <?php echo $section === 'services' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i>
                    <span class="menu-text">إدارة الخدمات</span>
                </a>
                
                <a href="admin.php?section=support" class="menu-item <?php echo $section === 'support' ? 'active' : ''; ?>">
                    <i class="fas fa-headset"></i>
                    <span class="menu-text">الدعم الفني</span>
                </a>
                
                <a href="admin.php?section=notifications" class="menu-item <?php echo $section === 'notifications' ? 'active' : ''; ?>">
                    <i class="fas fa-bell"></i>
                    <span class="menu-text">الإشعارات</span>
                </a>
                
                <a href="admin.php?section=referrals" class="menu-item <?php echo $section === 'referrals' ? 'active' : ''; ?>">
                    <i class="fas fa-share-alt"></i>
                    <span class="menu-text">نظام الإحالة</span>
                </a>
                
                <a href="admin.php?section=gifts" class="menu-item <?php echo $section === 'gifts' ? 'active' : ''; ?>">
                    <i class="fas fa-gift"></i>
                    <span class="menu-text">الهدايا والمكافآت</span>
                </a>
                
                <a href="admin.php?section=settings" class="menu-item <?php echo $section === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span class="menu-text">إعدادات النظام</span>
                </a>
                
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="menu-text">تسجيل الخروج</span>
                </a>
            </div>
            
            <div class="admin-footer">
                &copy; <?php echo date('Y'); ?> متجر مشهور
            </div>
        </div>
        
        <!-- Content -->
        <div class="admin-content">
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php 
            // Display different sections based on selected section
            switch ($section) {
                case 'dashboard':
                    include 'admin/dashboard.php';
                    break;
                case 'orders':
                    include 'admin/orders.php';
                    break;
                case 'payments':
                    include 'admin/payments.php';
                    break;
                case 'users':
                    echo '<div class="section-users-container w-100">';
                    include 'admin/users.php';
                    echo '</div>';
                    break;
                case 'services':
                    include 'admin/services.php';
                    break;
                case 'support':
                    include 'admin/support.php';
                    break;
                case 'notifications':
                    include 'admin/notifications.php';
                    break;
                case 'referrals':
                    include 'admin/referral_settings.php';
                    break;
                case 'gifts':
                    include 'admin/gifts.php';
                    break;
                case 'settings':
                    include 'admin/settings.php';
                    break;
                default:
                    include 'admin/dashboard.php';
            }
            ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Additional CSS and fixes for users section only
            if (window.location.href.includes('section=users')) {
                $('<style>')
                    .prop('type', 'text/css')
                    .html(`
                        .section-users-container table {
                            width: 100% !important;
                            table-layout: fixed !important;
                            display: table !important;
                        }
                        .section-users-container table tr {
                            display: table-row !important;
                            width: 100% !important;
                        }
                        .section-users-container table th,
                        .section-users-container table td {
                            display: table-cell !important;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }
                        .section-users-container .dataTables_wrapper {
                            width: 100% !important;
                        }
                        #allUsersTable {
                            table-layout: fixed !important;
                            width: 100% !important;
                        }
                    `)
                    .appendTo('head');
                    
                // Force redraw of tables in users section
                setTimeout(function() {
                    if ($.fn.DataTable.isDataTable('#allUsersTable')) {
                        $('#allUsersTable').DataTable().columns.adjust().draw();
                    }
                }, 200);
            }
        
            // Initialize DataTables
            $('.datatable').DataTable({
                "ordering": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
                },
                "order": [[0, "desc"]]
            });
            
            // User search for add funds
            $('#userSearch').on('input', function() {
                let username = $(this).val();
                if (username.length >= 3) {
                    $.ajax({
                        url: 'admin/search_user.php',
                        method: 'POST',
                        data: { username: username },
                        dataType: 'json',
                        success: function(response) {
                            let results = '';
                            if (response.length > 0) {
                                response.forEach(function(user) {
                                    results += `<div class="user-result" data-username="${user.username}">
                                                ${user.username} - ${user.email}
                                            </div>`;
                                });
                            } else {
                                results = '<div>لا توجد نتائج</div>';
                            }
                            $('#searchResults').html(results).show();
                        }
                    });
                } else {
                    $('#searchResults').hide();
                }
            });
            
            // Select user from search results
            $(document).on('click', '.user-result', function() {
                let username = $(this).data('username');
                $('#userSearch').val(username);
                $('#searchResults').hide();
            });
            
            // View payment receipt
            $('.view-receipt').on('click', function() {
                let receiptUrl = $(this).data('receipt');
                $('#receiptImage').attr('src', receiptUrl);
                $('#receiptModal').modal('show');
            });
        });
    </script>
</body>
</html>