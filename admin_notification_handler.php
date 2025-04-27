<?php
// Process notification sending
if (isset($_POST['send_notification'])) {
    // Get form data
    $target_users = filter_input(INPUT_POST, 'target_users', FILTER_SANITIZE_STRING);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $notification_type = filter_input(INPUT_POST, 'notification_type', FILTER_SANITIZE_STRING);
    $icon = filter_input(INPUT_POST, 'icon', FILTER_SANITIZE_STRING);
    $action_url = filter_input(INPUT_POST, 'action_url', FILTER_SANITIZE_URL);
    
    if ($title && $message) {
        try {
            if ($target_users === 'all') {
                // Send to all users using the utility function
                $result = send_notification_to_all($title, $message, $notification_type, $icon, $action_url);
            } else if ($target_users === 'specific' && isset($_POST['user_id']) && !empty($_POST['user_id'])) {
                // Send to specific user
                $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
                $result = send_notification($user_id, $title, $message, $notification_type, $icon, $action_url);
            } else {
                // Send global notification (user_id = NULL)
                $result = send_notification(null, $title, $message, $notification_type, $icon, $action_url);
            }
            
            if ($result) {
                $success_message = "تم إرسال الإشعار بنجاح.";
            } else {
                $error_message = "حدث خطأ أثناء إرسال الإشعار.";
            }
        } catch (Exception $e) {
            $error_message = "حدث خطأ أثناء إرسال الإشعار: " . $e->getMessage();
        }
    } else {
        $error_message = "الرجاء إدخال عنوان ونص الإشعار.";
    }
}

// Process resending a notification
if (isset($_POST['resend_notification']) && isset($_POST['notification_id'])) {
    $notification_id = intval($_POST['notification_id']);
    $target_users = filter_input(INPUT_POST, 'target_users', FILTER_SANITIZE_STRING);
    
    // Get the original notification
    $get_notification_query = "SELECT * FROM notifications WHERE id = ?";
    $stmt = $conn->prepare($get_notification_query);
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
    $notification = $stmt->get_result()->fetch_assoc();
    
    if ($notification) {
        try {
            if ($target_users === 'all') {
                // Send to all users using utility function
                $result = send_notification_to_all(
                    $notification['title'], 
                    $notification['message'], 
                    $notification['notification_type'], 
                    $notification['icon'], 
                    $notification['action_url']
                );
            } else if ($target_users === 'specific' && isset($_POST['user_id']) && !empty($_POST['user_id'])) {
                // Send to specific user
                $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
                $result = send_notification(
                    $user_id, 
                    $notification['title'], 
                    $notification['message'], 
                    $notification['notification_type'], 
                    $notification['icon'], 
                    $notification['action_url']
                );
            } else {
                // Send global notification (user_id = NULL)
                $result = send_notification(
                    null, 
                    $notification['title'], 
                    $notification['message'], 
                    $notification['notification_type'], 
                    $notification['icon'], 
                    $notification['action_url']
                );
            }
            
            if ($result) {
                $success_message = "تم إعادة إرسال الإشعار بنجاح.";
            } else {
                $error_message = "حدث خطأ أثناء إعادة إرسال الإشعار.";
            }
        } catch (Exception $e) {
            $error_message = "حدث خطأ أثناء إعادة إرسال الإشعار: " . $e->getMessage();
        }
    } else {
        $error_message = "الإشعار غير موجود.";
    }
}

// Handle delete notification
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_POST['notification_id'])) {
    $notification_id = intval($_POST['notification_id']);
    
    // Delete notification
    $delete_query = "DELETE FROM notifications WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $notification_id);
    
    if ($stmt->execute()) {
        $success_message = "تم حذف الإشعار بنجاح.";
    } else {
        $error_message = "حدث خطأ أثناء حذف الإشعار.";
    }
    
    // Redirect back to notifications page
    header("Location: admin.php?section=notifications");
    exit;
}