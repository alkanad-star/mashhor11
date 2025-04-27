<?php
// get_service_details.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if service ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid service ID']);
    exit;
}

include 'config/db.php';

$service_id = intval($_GET['id']);

// Get service details with category information
$service_query = "SELECT s.*, c.name as category_name 
                FROM services s 
                JOIN service_categories c ON s.category_id = c.id 
                WHERE s.id = ?";
$stmt = $conn->prepare($service_query);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Service not found']);
    exit;
}

$service = $result->fetch_assoc();

// Return service details as JSON
header('Content-Type: application/json');
echo json_encode($service);
?>