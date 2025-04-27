<?php
// get_order_statistics.php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../config/db.php';

// Get date range filter if provided
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Build WHERE clause for date filtering
$where_clause = '';
$params = array();
$param_types = '';

if ($start_date && $end_date) {
    // Add one day to end_date to make it inclusive
    $end_date_obj = new DateTime($end_date);
    $end_date_obj->modify('+1 day');
    $end_date = $end_date_obj->format('Y-m-d');
    
    $where_clause = "WHERE o.created_at >= ? AND o.created_at < ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $param_types .= 'ss';
}

// Get total counts for each status
$status_counts = array();
$statuses = ['pending', 'processing', 'completed', 'partial', 'cancelled', 'failed'];

foreach ($statuses as $status) {
    $count_query = "SELECT COUNT(*) as count FROM orders o $where_clause" . 
                  ($where_clause ? " AND" : " WHERE") . " o.status = ?";
    $stmt = $conn->prepare($count_query);
    
    if (!empty($params)) {
        $temp_params = $params;
        $temp_params[] = $status;
        $stmt->bind_param($param_types . 's', ...$temp_params);
    } else {
        $stmt->bind_param('s', $status);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $status_counts[$status] = $result->fetch_assoc()['count'];
}

// Get total orders count
$total_count_query = "SELECT COUNT(*) as count FROM orders o $where_clause";
$stmt = $conn->prepare($total_count_query);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$status_counts['total'] = $result->fetch_assoc()['count'];

// Get total amount for each status
$status_amounts = array();

foreach ($statuses as $status) {
    $amount_query = "SELECT SUM(amount) as total FROM orders o $where_clause" . 
                   ($where_clause ? " AND" : " WHERE") . " o.status = ?";
    $stmt = $conn->prepare($amount_query);
    
    if (!empty($params)) {
        $temp_params = $params;
        $temp_params[] = $status;
        $stmt->bind_param($param_types . 's', ...$temp_params);
    } else {
        $stmt->bind_param('s', $status);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $status_amounts[$status] = $total ? floatval($total) : 0;
}

// Get total amount
$total_amount_query = "SELECT SUM(amount) as total FROM orders o $where_clause";
$stmt = $conn->prepare($total_amount_query);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$total = $result->fetch_assoc()['total'];
$status_amounts['total'] = $total ? floatval($total) : 0;

// Get orders by service category
$category_query = "SELECT c.name as category, COUNT(o.id) as count, SUM(o.amount) as total
                  FROM orders o
                  JOIN services s ON o.service_id = s.id
                  JOIN service_categories c ON s.category_id = c.id
                  $where_clause
                  GROUP BY c.id
                  ORDER BY count DESC";

$stmt = $conn->prepare($category_query);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$categories = array();
while ($row = $result->fetch_assoc()) {
    $categories[] = [
        'name' => $row['category'],
        'count' => intval($row['count']),
        'amount' => floatval($row['total'])
    ];
}

// Get daily orders count for chart
$daily_stats_query = "SELECT DATE(o.created_at) as date, COUNT(*) as count, SUM(o.amount) as total
                      FROM orders o
                      $where_clause
                      GROUP BY DATE(o.created_at)
                      ORDER BY date DESC
                      LIMIT 30";

$stmt = $conn->prepare($daily_stats_query);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$daily_stats = array();
while ($row = $result->fetch_assoc()) {
    $daily_stats[] = [
        'date' => $row['date'],
        'count' => intval($row['count']),
        'amount' => floatval($row['total'])
    ];
}

// Prepare response
$response = [
    'statusCounts' => $status_counts,
    'statusAmounts' => $status_amounts,
    'categories' => $categories,
    'dailyStats' => $daily_stats
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>