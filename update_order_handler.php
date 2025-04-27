<?php
// update_order_handler.php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../config/db.php';
include_once '../notification_functions.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get form data
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';
$remains = isset($_POST['remains']) ? intval($_POST['remains']) : 0;
$start_count = isset($_POST['start_count']) ? intval($_POST['start_count']) : 0;

// Validate inputs
if ($order_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

if (!in_array($new_status, ['pending', 'processing', 'completed', 'partial', 'cancelled', 'failed'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

// Get current order
$order_query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Order not found']);
    exit;
}

$order = $result->fetch_assoc();

// Start transaction
$conn->begin_transaction();

try {
    // Update order status and other fields
    $update_fields = ['status = ?'];
    $params = [$new_status];
    $param_types = 's';
    
    // Update remains for partial orders
    if ($new_status === 'partial' && $remains > 0) {
        $update_fields[] = 'remains = ?';
        $params[] = $remains;
        $param_types .= 'i';
    } else if ($new_status === 'partial' && $remains <= 0) {
        // If remains is 0 or negative, change status to completed
        $new_status = 'completed';
        $params[0] = 'completed';
    }
    
    // Update start_count if set and moving to processing
    if ($new_status === 'processing' && $start_count > 0) {
        $update_fields[] = 'start_count = ?';
        $params[] = $start_count;
        $param_types .= 'i';
    }
    
    // Add order ID to params
    $params[] = $order_id;
    $param_types .= 'i';
    
    // Build and execute the update query
    $update_query = "UPDATE orders SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param($param_types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating order: " . $stmt->error);
    }
    
    // Update user balance adjustments if needed
    if (in_array($new_status, ['completed', 'cancelled', 'failed', 'partial']) && $order['status'] !== $new_status) {
        // Release in_use balance
        $update_inuse_query = "UPDATE users SET in_use = in_use - ? WHERE id = ?";
        $stmt = $conn->prepare($update_inuse_query);
        $stmt->bind_param("di", $order['amount'], $order['user_id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating user in_use balance: " . $stmt->error);
        }
        
        // For cancelled and failed orders, or partial orders, refund the appropriate amount
        if (in_array($new_status, ['cancelled', 'failed'])) {
            // Full refund
            $refund_amount = $order['amount'];
            $refund_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $stmt = $conn->prepare($refund_query);
            $stmt->bind_param("di", $refund_amount, $order['user_id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Error refunding user balance: " . $stmt->error);
            }
            
            // Create refund transaction record
            $description = $new_status === 'cancelled' 
                ? "استرداد المبلغ لإلغاء الطلب #" . $order_id 
                : "استرداد المبلغ لفشل الطلب #" . $order_id;
                
            $insert_transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) 
                                        VALUES (?, ?, 'refund', 'completed', ?)";
            $stmt = $conn->prepare($insert_transaction_query);
            $stmt->bind_param("ids", $order['user_id'], $refund_amount, $description);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating refund transaction: " . $stmt->error);
            }
        } elseif ($new_status === 'partial' && $remains > 0) {
            // Partial refund based on remaining quantity
            $delivered_percentage = ($order['quantity'] - $remains) / $order['quantity'];
            $used_amount = $order['amount'] * $delivered_percentage;
            $refund_amount = $order['amount'] - $used_amount;
            
            $refund_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $stmt = $conn->prepare($refund_query);
            $stmt->bind_param("di", $refund_amount, $order['user_id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Error refunding partial amount: " . $stmt->error);
            }
            
            // Create partial refund transaction
            $description = "استرداد جزئي للطلب #" . $order_id;
            $insert_transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) 
                                        VALUES (?, ?, 'refund', 'completed', ?)";
            $stmt = $conn->prepare($insert_transaction_query);
            $stmt->bind_param("ids", $order['user_id'], $refund_amount, $description);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating partial refund transaction: " . $stmt->error);
            }
        }
        
        // Send notification to user about order status update
        send_order_notification($order['user_id'], $order_id, $new_status);
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Return success
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'تم تحديث حالة الطلب بنجاح',
        'new_status' => $new_status
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    // Return error
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>