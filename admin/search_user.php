<?php
// admin/search_user.php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../config/db.php';

// Get search query
$username = isset($_POST['username']) ? $_POST['username'] : '';
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'all';

if (empty($username)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

// Build WHERE clause based on user type
$where_clause = "WHERE (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
$params = ["%$username%", "%$username%", "%$username%"];
$types = "sss";

// Add user type filter if specified
if ($user_type !== 'all') {
    if ($user_type === 'active') {
        $where_clause .= " AND is_active = 1";
    } elseif ($user_type === 'admin') {
        $where_clause .= " AND role = 'admin'";
    }
}

// Search for users
$search_query = "SELECT id, username, email, full_name FROM users $where_clause LIMIT 20";
$stmt = $conn->prepare($search_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($user = $result->fetch_assoc()) {
    $users[] = $user;
}

// Return results as JSON
header('Content-Type: application/json');
echo json_encode($users);