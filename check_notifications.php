<?php
// check_notifications.php
session_start();

// Return error if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include 'config/db.php';

$user_id = $_SESSION['user_id'];

// Get user's registration date
$user_query = "SELECT created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_registration_date = $user_data['created_at'];

// Store the ID of the last notification we've checked
$last_checked_id = 0;
if (isset($_SESSION['last_checked_notification_id'])) {
    $last_checked_id = $_SESSION['last_checked_notification_id'];
}

// Get the highest notification ID for this user
$latest_id_query = "SELECT MAX(id) as max_id FROM notifications 
                   WHERE (user_id = ? OR user_id IS NULL) 
                   AND created_at >= ?";
$stmt = $conn->prepare($latest_id_query);
$stmt->bind_param("is", $user_id, $user_registration_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$latest_id = $row['max_id'] ?? 0;

// Count total unread notifications (only those after registration)
$unread_query = "SELECT COUNT(*) as count FROM notifications 
                WHERE (user_id = ? OR user_id IS NULL) 
                AND is_read = 0 
                AND created_at >= ?";
$stmt = $conn->prepare($unread_query);
$stmt->bind_param("is", $user_id, $user_registration_date);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['count'];

// Update the session with the latest ID we've checked
$_SESSION['last_checked_notification_id'] = $latest_id;

// If we have a last checked ID and it's less than the latest ID,
// there are new notifications
$hasNew = ($last_checked_id > 0 && $last_checked_id < $latest_id);

// Get new notifications
$new_notifications = [];
if ($hasNew) {
    $new_query = "SELECT id, title, message, notification_type, icon, action_url, created_at 
                  FROM notifications 
                  WHERE id > ? AND (user_id = ? OR user_id IS NULL) 
                  AND created_at >= ?
                  ORDER BY created_at DESC";
    $stmt = $conn->prepare($new_query);
    $stmt->bind_param("iis", $last_checked_id, $user_id, $user_registration_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($notification = $result->fetch_assoc()) {
        $new_notifications[] = [
            'id' => $notification['id'],
            'title' => $notification['title'],
            'message' => substr(strip_tags($notification['message']), 0, 100), // Truncate message for notification
            'notification_type' => $notification['notification_type'],
            'icon' => $notification['icon'],
            'action_url' => $notification['action_url'],
            'created_at' => date('Y-m-d H:i', strtotime($notification['created_at']))
        ];
    }
}

// Return the result
header('Content-Type: application/json');
echo json_encode([
    'hasNew' => $hasNew,
    'newNotifications' => $new_notifications,
    'unreadCount' => $unread_count,
    'lastCheckedId' => $last_checked_id,
    'latestId' => $latest_id
]);
?>