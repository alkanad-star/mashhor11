<?php
// get_order_details.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

include 'config/db.php';

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get order details
$order_query = "SELECT o.*, s.name as service_name, s.category_id, c.name as category_name 
                FROM orders o 
                JOIN services s ON o.service_id = s.id 
                JOIN service_categories c ON s.category_id = c.id 
                WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Order not found']);
    exit;
}

$order = $result->fetch_assoc();

// Return order details as JSON
header('Content-Type: application/json');
echo json_encode($order);
?>