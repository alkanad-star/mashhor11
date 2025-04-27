<?php
// export_orders.php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: text/plain');
    echo 'Unauthorized';
    exit;
}

include '../config/db.php';

// Get export parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Build query based on status filter
$where_clause = '';
$params = array();
$param_types = '';

if ($status !== 'all') {
    $where_clause = "WHERE o.status = ?";
    $params[] = $status;
    $param_types .= 's';
}

// Add search term if provided
if (!empty($search_term)) {
    $where_clause = $where_clause ? "$where_clause AND" : "WHERE";
    $where_clause .= " (u.username LIKE ? OR s.name LIKE ? OR o.id LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $param_types .= 'sss';
}

// Build the full query
$orders_query = "SELECT o.id, u.username, s.name as service_name, o.quantity, 
                o.remains, o.amount, o.status, o.target_url, 
                o.start_count, o.created_at, o.updated_at 
                FROM orders o
                JOIN services s ON o.service_id = s.id
                JOIN users u ON o.user_id = u.id
                $where_clause
                ORDER BY o.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($orders_query);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Generate filename
$filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM to fix encoding issues with Arabic text
fputs($output, "\xEF\xBB\xBF");

// Define CSV headers
$csv_headers = [
    'رقم الطلب',
    'المستخدم',
    'الخدمة',
    'الكمية',
    'الكمية المتبقية',
    'المبلغ',
    'الحالة',
    'الرابط المستهدف',
    'العدد الأولي',
    'تاريخ الطلب',
    'آخر تحديث'
];

// Add headers to CSV
fputcsv($output, $csv_headers);

// Add rows to CSV
while ($row = $result->fetch_assoc()) {
    // Format status
    switch ($row['status']) {
        case 'pending':
            $status_text = 'قيد الانتظار';
            break;
        case 'processing':
            $status_text = 'قيد التنفيذ';
            break;
        case 'completed':
            $status_text = 'مكتمل';
            break;
        case 'partial':
            $status_text = 'جزئي';
            break;
        case 'cancelled':
            $status_text = 'ملغي';
            break;
        case 'failed':
            $status_text = 'فشل';
            break;
        default:
            $status_text = $row['status'];
    }
    
    // Format dates
    $created_at = date('Y-m-d H:i', strtotime($row['created_at']));
    $updated_at = date('Y-m-d H:i', strtotime($row['updated_at']));
    
    // Build CSV row
    $csv_row = [
        $row['id'],
        $row['username'],
        $row['service_name'],
        $row['quantity'],
        $row['remains'] ?? 0,
        $row['amount'],
        $status_text,
        $row['target_url'],
        $row['start_count'] ?? 0,
        $created_at,
        $updated_at
    ];
    
    // Write row to CSV
    fputcsv($output, $csv_row);
}

fclose($output);
exit;
?>