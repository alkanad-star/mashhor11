<?php
// search_orders.php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../config/db.php';

// Get search parameters
$search_term = isset($_POST['search_term']) ? $_POST['search_term'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'all';

if (empty($search_term)) {
    // Return empty array if no search term
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

// Build query based on status filter
$where_clause = "WHERE (u.username LIKE ? OR s.name LIKE ? OR o.id LIKE ?)";

if ($status !== 'all') {
    $where_clause .= " AND o.status = ?";
}

// Build the full query
$search_query = "SELECT o.*, s.name as service_name, u.username 
                FROM orders o
                JOIN services s ON o.service_id = s.id
                JOIN users u ON o.user_id = u.id
                $where_clause
                ORDER BY o.created_at DESC
                LIMIT 100";

// Prepare and execute the query
$stmt = $conn->prepare($search_query);

if ($status !== 'all') {
    $search_param = "%$search_term%";
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $status);
} else {
    $search_param = "%$search_term%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();

// Format results for JSON response
$orders = [];
while ($order = $result->fetch_assoc()) {
    // Format the dates
    $order['created_at_formatted'] = date('Y-m-d H:i', strtotime($order['created_at']));
    $order['updated_at_formatted'] = date('Y-m-d H:i', strtotime($order['updated_at']));
    
    // Calculate progress for partial orders
    if ($order['status'] === 'partial') {
        $order['progress'] = round((($order['quantity'] - $order['remains']) / $order['quantity']) * 100);
    }
    
    $orders[] = $order;
}

// Return results as JSON
header('Content-Type: application/json');
echo json_encode($orders);
?>