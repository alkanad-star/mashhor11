<?php
// Enable error reporting for debugging (you can remove this in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Send notification to user about order status change
 * 
 * @param int $user_id The user ID
 * @param int $order_id The order ID
 * @param string $status The new status
 * @return bool Success state
 */
function send_order_notification($user_id, $order_id, $status) {
    global $conn;
    
    // Prepare status-specific messages
    $title = "تحديث حالة الطلب #$order_id";
    $message = "";
    $type = "order_update";
    
    switch ($status) {
        case 'pending':
            $message = "تم استلام طلبك بنجاح وهو قيد المراجعة.";
            break;
        case 'processing':
            $message = "بدأنا في تنفيذ طلبك وسيتم تحديثك عند الانتهاء.";
            break;
        case 'completed':
            $message = "تم إكمال طلبك بنجاح. شكراً لثقتك بنا!";
            $type = "order_completed";
            break;
        case 'partial':
            $message = "تم تنفيذ طلبك بشكل جزئي وتم إعادة المبلغ المتبقي إلى رصيدك.";
            break;
        case 'cancelled':
            $message = "تم إلغاء طلبك وإعادة المبلغ إلى رصيدك.";
            $type = "order_cancelled";
            break;
        case 'failed':
            $message = "نأسف، حدث خطأ أثناء تنفيذ طلبك وتم إعادة المبلغ إلى رصيدك.";
            $type = "order_failed";
            break;
        default:
            $message = "تم تحديث حالة طلبك إلى: $status";
    }
    
    try {
        // Insert notification in database
        $insert_query = "INSERT INTO notifications (user_id, title, message, type, reference_id, is_read) 
                        VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("isssi", $user_id, $title, $message, $type, $order_id);
        $result = $stmt->execute();
        
        // Check if we need to send email notification as well
        send_email_notification($user_id, $title, $message, $order_id, $status);
        
        return $result;
    } catch (Exception $e) {
        // Log error but don't stop the main process
        error_log("Error sending notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email notification about order updates if user has enabled email notifications
 */
function send_email_notification($user_id, $title, $message, $order_id, $status) {
    global $conn;
    
    try {
        // Check if user has enabled email notifications
        $settings_query = "SELECT email, notification_preferences FROM users WHERE id = ?";
        $stmt = $conn->prepare($settings_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user_data = $result->fetch_assoc()) {
            // Check notification preferences (assuming JSON field or serialized array)
            $preferences = json_decode($user_data['notification_preferences'] ?? '{}', true);
            
            // If email notifications are enabled for order updates or this specific status
            if (isset($preferences['email_order_updates']) && $preferences['email_order_updates']) {
                $email = $user_data['email'];
                
                if (!empty($email)) {
                    // Prepare email content
                    $email_subject = $title;
                    $email_body = "
                    <html>
                    <head>
                        <title>$title</title>
                    </head>
                    <body dir='rtl' style='font-family: Arial, sans-serif;'>
                        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;'>
                            <h2 style='color: #333;'>$title</h2>
                            <p>$message</p>
                            <p>يمكنك متابعة تفاصيل طلبك من خلال <a href='https://yourdomain.com/orders/$order_id'>الرابط التالي</a>.</p>
                            <hr>
                            <p style='font-size: 12px; color: #777;'>هذه رسالة آلية، يرجى عدم الرد عليها.</p>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    // Set headers for HTML email
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= "From: Your Service <noreply@yourdomain.com>" . "\r\n";
                    
                    // Send email
                    mail($email, $email_subject, $email_body, $headers);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error sending email notification: " . $e->getMessage());
    }
}

// Process order status update
if (isset($_POST['update_order_status'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $remains = isset($_POST['remains']) ? filter_input(INPUT_POST, 'remains', FILTER_SANITIZE_NUMBER_INT) : 0;
    $start_count = isset($_POST['start_count']) ? filter_input(INPUT_POST, 'start_count', FILTER_SANITIZE_NUMBER_INT) : null;
    
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
            // If starting processing, update the start_count
            if ($new_status === 'processing' && $start_count !== null) {
                $update_query = "UPDATE orders SET status = ?, start_count = ?, remains = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("siii", $new_status, $start_count, $remains, $order_id);
            } else {
                // Update order status
                $update_query = "UPDATE orders SET status = ?, remains = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("sii", $new_status, $remains, $order_id);
            }
            $stmt->execute();
            
            // If status is completed, update spent and in_use in a single query
            if ($new_status === 'completed' && $order['status'] !== 'completed') {
                // Update user's spent amount and decrease in_use
                $update_spent_query = "UPDATE users SET spent = spent + ?, in_use = in_use - ? WHERE id = ?";
                $stmt = $conn->prepare($update_spent_query);
                $stmt->bind_param("ddi", $order['amount'], $order['amount'], $order['user_id']);
                $stmt->execute();
                
                // Process pending referral reward if the function exists
                if (function_exists('completePendingReferralReward')) {
                    completePendingReferralReward($order_id);
                }
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
            // For failed orders
            else if ($new_status === 'failed' && $order['status'] !== 'failed') {
                // Similar to cancelled, release in_use balance
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
                $description = "استرداد المبلغ لفشل الطلب #" . $order_id;
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
                
                // Add the used amount to spent
                $update_spent_query = "UPDATE users SET spent = spent + ? WHERE id = ?";
                $stmt = $conn->prepare($update_spent_query);
                $stmt->bind_param("di", $used_amount, $order['user_id']);
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
            
            // Record status change in order history table
            if ($new_status !== $order['status']) {
                $insert_history_query = "INSERT INTO order_history 
                    (order_id, old_status, new_status, changed_by, changed_at, notes) 
                    VALUES (?, ?, ?, ?, NOW(), ?)";
                $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'system';
                $notes = "تم تحديث الحالة بواسطة المدير";
                
                $stmt = $conn->prepare($insert_history_query);
                $stmt->bind_param("issss", $order_id, $order['status'], $new_status, $admin_id, $notes);
                $stmt->execute();
                
                // Send notification to user about status change
                send_order_notification($order['user_id'], $order_id, $new_status);
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
?>
<!-- Orders Management -->
<div class="orders-section">
    <h1 class="mb-4">إدارة الطلبات</h1>
    
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
    
    <!-- Search Box -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="input-group">
                        <input type="text" class="form-control" id="orderSearch" placeholder="ابحث باسم المستخدم أو الخدمة...">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-md-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshOrders">
                            <i class="fas fa-sync-alt"></i> تحديث
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="exportOrdersCSV">
                            <i class="fas fa-file-export"></i> تصدير CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <ul class="nav nav-tabs" id="ordersTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-orders" type="button" role="tab" aria-controls="all-orders" aria-selected="true">جميع الطلبات</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-orders" type="button" role="tab" aria-controls="pending-orders" aria-selected="false">قيد الانتظار</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing-orders" type="button" role="tab" aria-controls="processing-orders" aria-selected="false">قيد التنفيذ</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-orders" type="button" role="tab" aria-controls="completed-orders" aria-selected="false">مكتملة</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="partial-tab" data-bs-toggle="tab" data-bs-target="#partial-orders" type="button" role="tab" aria-controls="partial-orders" aria-selected="false">جزئية</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled-orders" type="button" role="tab" aria-controls="cancelled-orders" aria-selected="false">ملغية</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="failed-tab" data-bs-toggle="tab" data-bs-target="#failed-orders" type="button" role="tab" aria-controls="failed-orders" aria-selected="false">فاشلة</button>
                </li>
            </ul>
            
            <div class="tab-content mt-4" id="ordersTabContent">
                <!-- All Orders Tab -->
                <div class="tab-pane fade show active" id="all-orders" role="tabpanel" aria-labelledby="all-tab">
                    <?php
                    try {
                        $all_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                        FROM orders o 
                                        JOIN services s ON o.service_id = s.id 
                                        JOIN users u ON o.user_id = u.id 
                                        ORDER BY o.created_at DESC";
                        $all_orders = $conn->query($all_orders_query);
                        
                        if (!$all_orders) {
                            throw new Exception("Error executing orders query: " . $conn->error);
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</div>';
                        $all_orders = null;
                    }
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="allOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($all_orders && $all_orders->num_rows > 0): ?>
                                <?php while ($order = $all_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td>
                                        <?php if (isset($order['status']) && $order['status'] == 'partial'): ?>
                                        <span><?php echo number_format(isset($order['quantity']) && isset($order['remains']) ? ($order['quantity'] - $order['remains']) : 0); ?> / <?php echo number_format($order['quantity'] ?? 0); ?></span>
                                        <?php else: ?>
                                        <span><?php echo number_format($order['quantity'] ?? 0); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td>
                                        <?php 
                                        $status_class = '';
                                        $status_text = '';
                                        
                                        switch ($order['status'] ?? '') {
                                            case 'pending':
                                                $status_class = 'bg-warning';
                                                $status_text = 'قيد الانتظار';
                                                break;
                                            case 'processing':
                                                $status_class = 'bg-info';
                                                $status_text = 'قيد التنفيذ';
                                                break;
                                            case 'completed':
                                                $status_class = 'bg-success';
                                                $status_text = 'مكتمل';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'bg-secondary';
                                                $status_text = 'ملغي';
                                                break;
                                            case 'failed':
                                                $status_class = 'bg-danger';
                                                $status_text = 'فشل';
                                                break;
                                            case 'partial':
                                                $status_class = 'bg-primary';
                                                $status_text = 'جزئي';
                                                break;
                                            default:
                                                $status_class = 'bg-secondary';
                                                $status_text = 'غير معروف';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td><?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Order Update Modal -->
                                <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1" aria-labelledby="orderModalLabel<?php echo $order['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="orderModalLabel<?php echo $order['id']; ?>">تحديث حالة الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">المستخدم</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['username']); ?>" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">الخدمة</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['service_name']); ?>" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">الكمية</label>
                                                        <input type="text" class="form-control" value="<?php echo number_format($order['quantity'] ?? 0); ?>" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">الرابط المستهدف</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['target_url'] ?? ''); ?>" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="status<?php echo $order['id']; ?>" class="form-label">الحالة</label>
                                                        <select class="form-select" id="status<?php echo $order['id']; ?>" name="status" required>
                                                            <option value="pending" <?php echo ($order['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                                            <option value="processing" <?php echo ($order['status'] ?? '') === 'processing' ? 'selected' : ''; ?>>قيد التنفيذ</option>
                                                            <option value="completed" <?php echo ($order['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>مكتمل</option>
                                                            <option value="partial" <?php echo ($order['status'] ?? '') === 'partial' ? 'selected' : ''; ?>>جزئي</option>
                                                            <option value="cancelled" <?php echo ($order['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                                                            <option value="failed" <?php echo ($order['status'] ?? '') === 'failed' ? 'selected' : ''; ?>>فشل</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3 partial-remains-field" id="partialRemains<?php echo $order['id']; ?>" style="display: <?php echo ($order['status'] ?? '') === 'partial' ? 'block' : 'none'; ?>;">
                                                        <label for="remains<?php echo $order['id']; ?>" class="form-label">الكمية المتبقية</label>
                                                        <input type="number" class="form-control" id="remains<?php echo $order['id']; ?>" name="remains" value="<?php echo $order['remains'] ?? 0; ?>" min="1" max="<?php echo isset($order['quantity']) ? $order['quantity'] - 1 : 0; ?>">
                                                        <div class="form-text">يجب أن تكون الكمية المتبقية أقل من الكمية الإجمالية.</div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-primary">تحديث الحالة</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Order Details Modal -->
                                <div class="modal fade" id="orderDetailsModal<?php echo $order['id']; ?>" tabindex="-1" aria-labelledby="orderDetailsModalLabel<?php echo $order['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="orderDetailsModalLabel<?php echo $order['id']; ?>">تفاصيل الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>رقم الطلب:</strong> <?php echo $order['id']; ?></p>
                                                        <p><strong>المستخدم:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                                                        <p><strong>الخدمة:</strong> <?php echo htmlspecialchars($order['service_name']); ?></p>
                                                        <p><strong>الكمية:</strong> <?php echo number_format($order['quantity'] ?? 0); ?></p>
                                                        <p><strong>المبلغ:</strong> $<?php echo number_format($order['amount'] ?? 0, 2); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>الحالة:</strong> <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></p>
                                                        <p><strong>تاريخ الطلب:</strong> <?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></p>
                                                        <p><strong>آخر تحديث:</strong> <?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></p>
                                                        <p><strong>الرابط المستهدف:</strong> <a href="<?php echo htmlspecialchars($order['target_url'] ?? ''); ?>" target="_blank"><?php echo htmlspecialchars($order['target_url'] ?? ''); ?></a></p>
                                                        <p><strong>العدد الأولي:</strong> <?php echo number_format($order['start_count'] ?? 0); ?></p>
                                                        
                                                        <?php if (isset($order['status']) && $order['status'] === 'partial'): ?>
                                                        <p><strong>المتبقي:</strong> <?php echo number_format($order['remains'] ?? 0); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <?php if (isset($order['status']) && $order['status'] === 'partial'): ?>
                                                <div class="mt-4">
                                                    <h6>تقدم الطلب</h6>
                                                    <div class="progress">
                                                        <?php 
                                                        $progress = isset($order['quantity']) && isset($order['remains']) && $order['quantity'] > 0 
                                                            ? round((($order['quantity'] - $order['remains']) / $order['quantity']) * 100)
                                                            : 0; 
                                                        ?>
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progress; ?>%</div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <!-- Order History Section -->
                                                <div class="mt-4">
                                                    <h6>سجل التحديثات</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>التاريخ</th>
                                                                    <th>الحالة السابقة</th>
                                                                    <th>الحالة الجديدة</th>
                                                                    <th>بواسطة</th>
                                                                    <th>ملاحظات</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                // Fetch order history
                                                                $history_query = "SELECT * FROM order_history 
                                                                               WHERE order_id = ? 
                                                                               ORDER BY changed_at DESC 
                                                                               LIMIT 5";
                                                                $stmt = $conn->prepare($history_query);
                                                                $stmt->bind_param("i", $order['id']);
                                                                $stmt->execute();
                                                                $history_result = $stmt->get_result();
                                                                
                                                                if ($history_result && $history_result->num_rows > 0):
                                                                    while ($history = $history_result->fetch_assoc()):
                                                                        // Translate status codes to Arabic
                                                                        $old_status_text = '';
                                                                        $new_status_text = '';
                                                                        
                                                                        switch ($history['old_status']) {
                                                                            case 'pending': $old_status_text = 'قيد الانتظار'; break;
                                                                            case 'processing': $old_status_text = 'قيد التنفيذ'; break;
                                                                            case 'completed': $old_status_text = 'مكتمل'; break;
                                                                            case 'partial': $old_status_text = 'جزئي'; break;
                                                                            case 'cancelled': $old_status_text = 'ملغي'; break;
                                                                            case 'failed': $old_status_text = 'فشل'; break;
                                                                            default: $old_status_text = $history['old_status'];
                                                                        }
                                                                        
                                                                        switch ($history['new_status']) {
                                                                            case 'pending': $new_status_text = 'قيد الانتظار'; break;
                                                                            case 'processing': $new_status_text = 'قيد التنفيذ'; break;
                                                                            case 'completed': $new_status_text = 'مكتمل'; break;
                                                                            case 'partial': $new_status_text = 'جزئي'; break;
                                                                            case 'cancelled': $new_status_text = 'ملغي'; break;
                                                                            case 'failed': $new_status_text = 'فشل'; break;
                                                                            default: $new_status_text = $history['new_status'];
                                                                        }
                                                                ?>
                                                                <tr>
                                                                    <td><?php echo date('Y-m-d H:i', strtotime($history['changed_at'])); ?></td>
                                                                    <td><?php echo $old_status_text; ?></td>
                                                                    <td><?php echo $new_status_text; ?></td>
                                                                    <td><?php echo htmlspecialchars($history['changed_by']); ?></td>
                                                                    <td><?php echo htmlspecialchars($history['notes']); ?></td>
                                                                </tr>
                                                                <?php 
                                                                    endwhile;
                                                                else:
                                                                ?>
                                                                <tr>
                                                                    <td colspan="5" class="text-center">لا يوجد سجل تحديثات بعد</td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">لا توجد طلبات حتى الآن</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pending Orders Tab -->
                <div class="tab-pane fade" id="pending-orders" role="tabpanel" aria-labelledby="pending-tab">
                    <?php
                    try {
                        $pending_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                               FROM orders o 
                                               JOIN services s ON o.service_id = s.id 
                                               JOIN users u ON o.user_id = u.id 
                                               WHERE o.status = 'pending' 
                                               ORDER BY o.created_at DESC";
                        $pending_orders = $conn->query($pending_orders_query);
                        
                        if (!$pending_orders) {
                            throw new Exception("Error executing pending orders query: " . $conn->error);
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</div>';
                        $pending_orders = null;
                    }
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="pendingOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pending_orders && $pending_orders->num_rows > 0): ?>
                                <?php while ($order = $pending_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#startProcessingModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Start Processing Modal -->
                                <div class="modal fade" id="startProcessingModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">بدء تنفيذ الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد بدء تنفيذ هذا الطلب؟</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="processing">
                                                    
                                                    <div class="mb-3">
                                                        <label for="start_count<?php echo $order['id']; ?>" class="form-label">العدد الأولي</label>
                                                        <input type="number" class="form-control" id="start_count<?php echo $order['id']; ?>" name="start_count" min="0" value="0">
                                                        <div class="form-text">أدخل العدد الأولي للمتابعين/المشاهدات قبل بدء الخدمة.</div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-success">بدء التنفيذ</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cancel Order Modal -->
                                <div class="modal fade" id="cancelOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">إلغاء الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد إلغاء هذا الطلب؟ سيتم استرداد المبلغ للمستخدم.</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-danger">تأكيد الإلغاء</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد طلبات معلقة</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Processing Orders Tab -->
                <div class="tab-pane fade" id="processing-orders" role="tabpanel" aria-labelledby="processing-tab">
                    <!-- Similar structure with processing orders query -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="processingOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $processing_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                              FROM orders o 
                                                              JOIN services s ON o.service_id = s.id 
                                                              JOIN users u ON o.user_id = u.id 
                                                              WHERE o.status = 'processing' 
                                                              ORDER BY o.created_at DESC";
                                    $processing_orders = $conn->query($processing_orders_query);
                                    
                                    if (!$processing_orders) {
                                        throw new Exception("Error executing processing orders query: " . $conn->error);
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</div>';
                                    $processing_orders = null;
                                }
                                
                                if ($processing_orders && $processing_orders->num_rows > 0):
                                while ($order = $processing_orders->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#completeOrderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#partialOrderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-percentage"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#failOrderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Complete Order Modal -->
                                <div class="modal fade" id="completeOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">إكمال الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد تعيين حالة هذا الطلب كمكتمل؟</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-success">تأكيد الإكمال</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Partial Order Modal -->
                                <div class="modal fade" id="partialOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تسليم جزئي للطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>استخدم هذا الخيار إذا لم تتمكن من إكمال الطلب بالكامل. سيتم استرداد المبلغ المتبقي للمستخدم.</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="partial">
                                                    
                                                    <div class="mb-3">
                                                        <label for="partial_remains<?php echo $order['id']; ?>" class="form-label">الكمية المتبقية</label>
                                                        <input type="number" class="form-control" id="partial_remains<?php echo $order['id']; ?>" name="remains" min="1" max="<?php echo isset($order['quantity']) ? $order['quantity'] - 1 : 0; ?>" required>
                                                        <div class="form-text">أدخل عدد الوحدات التي لم يتم تسليمها. يجب أن تكون أقل من إجمالي الكمية (<?php echo number_format($order['quantity'] ?? 0); ?>).</div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-primary">تأكيد التسليم الجزئي</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Fail Order Modal -->
                                <div class="modal fade" id="failOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">فشل الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد تعيين حالة هذا الطلب كفاشل؟ سيتم استرداد المبلغ للمستخدم.</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="failed">
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-danger">تأكيد الفشل</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد طلبات قيد التنفيذ</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Completed Orders Tab -->
                <div class="tab-pane fade" id="completed-orders" role="tabpanel" aria-labelledby="completed-tab">
                    <!-- Similar structure with completed orders query -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="completedOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ الطلب</th>
                                    <th>تاريخ الإكمال</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $completed_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                              FROM orders o 
                                                              JOIN services s ON o.service_id = s.id 
                                                              JOIN users u ON o.user_id = u.id 
                                                              WHERE o.status = 'completed' 
                                                              ORDER BY o.created_at DESC";
                                    $completed_orders = $conn->query($completed_orders_query);
                                    
                                    if (!$completed_orders) {
                                        throw new Exception("Error executing completed orders query: " . $conn->error);
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</div>';
                                    $completed_orders = null;
                                }
                                
                                if ($completed_orders && $completed_orders->num_rows > 0): 
                                while ($order = $completed_orders->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></td>
                                    <td><?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#completedOrderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Completed Order Details Modal -->
                                <div class="modal fade" id="completedOrderDetailsModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تفاصيل الطلب المكتمل #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>رقم الطلب:</strong> <?php echo $order['id']; ?></p>
                                                        <p><strong>المستخدم:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                                                        <p><strong>الخدمة:</strong> <?php echo htmlspecialchars($order['service_name']); ?></p>
                                                        <p><strong>الكمية:</strong> <?php echo number_format($order['quantity'] ?? 0); ?></p>
                                                        <p><strong>المبلغ:</strong> $<?php echo number_format($order['amount'] ?? 0, 2); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>الحالة:</strong> <span class="badge bg-success">مكتمل</span></p>
                                                        <p><strong>تاريخ الطلب:</strong> <?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></p>
                                                        <p><strong>تاريخ الإكمال:</strong> <?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></p>
                                                        <p><strong>الرابط المستهدف:</strong> <a href="<?php echo htmlspecialchars($order['target_url'] ?? ''); ?>" target="_blank"><?php echo htmlspecialchars($order['target_url'] ?? ''); ?></a></p>
                                                        <p><strong>العدد الأولي:</strong> <?php echo number_format($order['start_count'] ?? 0); ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="alert alert-success mt-3">
                                                    <i class="fas fa-check-circle"></i> تم تنفيذ هذا الطلب بنجاح.
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">لا توجد طلبات مكتملة</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Partial, Cancelled, and Failed Orders Tabs - similar structure -->
                <div class="tab-pane fade" id="partial-orders" role="tabpanel" aria-labelledby="partial-tab">
                    <div class="table-responsive">
                        <table class="table table-hover" id="partialOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>المنفذ/الكمية</th>
                                    <th>المبلغ</th>
                                    <th>التقدم</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $partial_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                            FROM orders o 
                                                            JOIN services s ON o.service_id = s.id 
                                                            JOIN users u ON o.user_id = u.id 
                                                            WHERE o.status = 'partial' 
                                                            ORDER BY o.updated_at DESC";
                                    $partial_orders = $conn->query($partial_orders_query);
                                    
                                    if (!$partial_orders) {
                                        throw new Exception("Error executing partial orders query: " . $conn->error);
                                    }
                                    
                                    if ($partial_orders && $partial_orders->num_rows > 0):
                                    while ($order = $partial_orders->fetch_assoc()):
                                        $progress = isset($order['quantity']) && isset($order['remains']) && $order['quantity'] > 0 
                                            ? round((($order['quantity'] - $order['remains']) / $order['quantity']) * 100)
                                            : 0;
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format(($order['quantity'] - $order['remains']) ?? 0); ?> / <?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progress; ?>%</div>
                                        </div>
                                    </td>
                                    <td><?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#partialOrderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Partial Order Details Modal -->
                                <div class="modal fade" id="partialOrderDetailsModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تفاصيل الطلب الجزئي #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Similar to completed order details -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>رقم الطلب:</strong> <?php echo $order['id']; ?></p>
                                                        <p><strong>المستخدم:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                                                        <p><strong>الخدمة:</strong> <?php echo htmlspecialchars($order['service_name']); ?></p>
                                                        <p><strong>الكمية الإجمالية:</strong> <?php echo number_format($order['quantity'] ?? 0); ?></p>
                                                        <p><strong>الكمية المنفذة:</strong> <?php echo number_format(($order['quantity'] - $order['remains']) ?? 0); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>الحالة:</strong> <span class="badge bg-primary">جزئي</span></p>
                                                        <p><strong>المبلغ الأصلي:</strong> $<?php echo number_format($order['amount'] ?? 0, 2); ?></p>
                                                        <p><strong>تاريخ الطلب:</strong> <?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></p>
                                                        <p><strong>تاريخ التحديث:</strong> <?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></p>
                                                        <p><strong>الرابط المستهدف:</strong> <a href="<?php echo htmlspecialchars($order['target_url'] ?? ''); ?>" target="_blank"><?php echo htmlspecialchars($order['target_url'] ?? ''); ?></a></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-4">
                                                    <h6>تقدم الطلب</h6>
                                                    <div class="progress" style="height: 25px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progress; ?>%</div>
                                                    </div>
                                                </div>
                                                
                                                <?php
                                                // Calculate refund amount
                                                $delivered_percentage = ($order['quantity'] - $order['remains']) / $order['quantity'];
                                                $used_amount = $order['amount'] * $delivered_percentage;
                                                $refund_amount = $order['amount'] - $used_amount;
                                                ?>
                                                
                                                <div class="alert alert-info mt-3">
                                                    <p><strong>المبلغ المستخدم:</strong> $<?php echo number_format($used_amount, 2); ?></p>
                                                    <p><strong>المبلغ المسترد:</strong> $<?php echo number_format($refund_amount, 2); ?></p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center">لا توجد طلبات جزئية</td>
                                </tr>
                                <?php
                                endif;
                                } catch (Exception $e) {
                                    echo '<tr><td colspan="8" class="text-center text-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="cancelled-orders" role="tabpanel" aria-labelledby="cancelled-tab">
                    <div class="table-responsive">
                        <table class="table table-hover" id="cancelledOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ الطلب</th>
                                    <th>تاريخ الإلغاء</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $cancelled_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                             FROM orders o 
                                                             JOIN services s ON o.service_id = s.id 
                                                             JOIN users u ON o.user_id = u.id 
                                                             WHERE o.status = 'cancelled' 
                                                             ORDER BY o.updated_at DESC";
                                    $cancelled_orders = $conn->query($cancelled_orders_query);
                                    
                                    if (!$cancelled_orders) {
                                        throw new Exception("Error executing cancelled orders query: " . $conn->error);
                                    }
                                    
                                    if ($cancelled_orders && $cancelled_orders->num_rows > 0):
                                    while ($order = $cancelled_orders->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></td>
                                    <td><?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#cancelledOrderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Cancelled Order Details Modal structure similar to others -->
                                <div class="modal fade" id="cancelledOrderDetailsModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تفاصيل الطلب الملغي #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Basic order details -->
                                                <p><strong>رقم الطلب:</strong> <?php echo $order['id']; ?></p>
                                                <p><strong>المستخدم:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                                                <p><strong>الخدمة:</strong> <?php echo htmlspecialchars($order['service_name']); ?></p>
                                                <p><strong>الكمية:</strong> <?php echo number_format($order['quantity'] ?? 0); ?></p>
                                                <p><strong>المبلغ:</strong> $<?php echo number_format($order['amount'] ?? 0, 2); ?></p>
                                                <p><strong>تاريخ الطلب:</strong> <?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></p>
                                                <p><strong>تاريخ الإلغاء:</strong> <?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></p>
                                                
                                                <div class="alert alert-secondary mt-3">
                                                    <i class="fas fa-info-circle"></i> تم إلغاء هذا الطلب واسترداد المبلغ للمستخدم.
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center">لا توجد طلبات ملغية</td>
                                </tr>
                                <?php
                                endif;
                                } catch (Exception $e) {
                                    echo '<tr><td colspan="8" class="text-center text-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="failed-orders" role="tabpanel" aria-labelledby="failed-tab">
                    <div class="table-responsive">
                        <table class="table table-hover" id="failedOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ الطلب</th>
                                    <th>تاريخ الفشل</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $failed_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                          FROM orders o 
                                                          JOIN services s ON o.service_id = s.id 
                                                          JOIN users u ON o.user_id = u.id 
                                                          WHERE o.status = 'failed' 
                                                          ORDER BY o.updated_at DESC";
                                    $failed_orders = $conn->query($failed_orders_query);
                                    
                                    if (!$failed_orders) {
                                        throw new Exception("Error executing failed orders query: " . $conn->error);
                                    }
                                    
                                    if ($failed_orders && $failed_orders->num_rows > 0):
                                    while ($order = $failed_orders->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></td>
                                    <td><?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#failedOrderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Failed Order Details Modal structure similar to others -->
                                <div class="modal fade" id="failedOrderDetailsModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تفاصيل الطلب الفاشل #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Basic order details -->
                                                <p><strong>رقم الطلب:</strong> <?php echo $order['id']; ?></p>
                                                <p><strong>المستخدم:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                                                <p><strong>الخدمة:</strong> <?php echo htmlspecialchars($order['service_name']); ?></p>
                                                <p><strong>الكمية:</strong> <?php echo number_format($order['quantity'] ?? 0); ?></p>
                                                <p><strong>المبلغ:</strong> $<?php echo number_format($order['amount'] ?? 0, 2); ?></p>
                                                <p><strong>تاريخ الطلب:</strong> <?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></p>
                                                <p><strong>تاريخ الفشل:</strong> <?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></p>
                                                
                                                <div class="alert alert-danger mt-3">
                                                    <i class="fas fa-exclamation-circle"></i> فشل تنفيذ هذا الطلب وتم استرداد المبلغ للمستخدم.
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center">لا توجد طلبات فاشلة</td>
                                </tr>
                                <?php
                                endif;
                                } catch (Exception $e) {
                                    echo '<tr><td colspan="8" class="text-center text-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for handling status change and partial remains field -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle status change for partial remains fields
        const statusSelects = document.querySelectorAll('select[id^="status"]');
        statusSelects.forEach(select => {
            const orderId = select.id.replace('status', '');
            const partialRemainsField = document.getElementById('partialRemains' + orderId);
            
            select.addEventListener('change', function() {
                if (this.value === 'partial') {
                    partialRemainsField.style.display = 'block';
                } else {
                    partialRemainsField.style.display = 'none';
                }
            });
        });
        
        // Handle search functionality
        const orderSearch = document.getElementById('orderSearch');
        const searchButton = document.getElementById('searchButton');
        
        function performSearch() {
            const searchTerm = orderSearch.value.toLowerCase();
            const allRows = document.querySelectorAll('#allOrdersTable tbody tr');
            
            allRows.forEach(row => {
                const username = row.cells[1].textContent.toLowerCase();
                const service = row.cells[2].textContent.toLowerCase();
                
                if (username.includes(searchTerm) || service.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        if (searchButton) {
            searchButton.addEventListener('click', performSearch);
        }
        
        if (orderSearch) {
            orderSearch.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        }
        
        // Handle refresh button
        const refreshButton = document.getElementById('refreshOrders');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                location.reload();
            });
        }
        
        // Handle export to CSV
        const exportButton = document.getElementById('exportOrdersCSV');
        if (exportButton) {
            exportButton.addEventListener('click', function() {
                const activeTab = document.querySelector('.tab-pane.active');
                const activeTable = activeTab.querySelector('table');
                const tableId = activeTable.id;
                
                // Redirect to export script with table ID
                window.location.href = 'export_orders.php?table=' + tableId;
            });
        }
    });
</script>