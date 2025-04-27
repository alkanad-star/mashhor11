<?php
// notifications.php
session_start();
$page_title = "التنبيهات - متجر مشهور";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'config/db.php';

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_registration_date = $user['created_at'];

// Handle mark as read functionality
if (isset($_GET['action']) && $_GET['action'] == 'mark_read' && isset($_GET['id'])) {
    $notification_id = intval($_GET['id']);
    
    // Mark notification as read
    $update_query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR user_id IS NULL)";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    
    // Redirect to remove the GET parameters
    header("Location: notifications.php");
    exit;
}

// Handle mark all as read functionality
if (isset($_GET['action']) && $_GET['action'] == 'mark_all_read') {
    // Mark all user notifications as read
    $update_query = "UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR user_id IS NULL)";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Redirect to remove the GET parameters
    header("Location: notifications.php");
    exit;
}

// Handle delete notification functionality
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $notification_id = intval($_GET['id']);
    
    // Delete notification
    $delete_query = "DELETE FROM notifications WHERE id = ? AND (user_id = ? OR user_id IS NULL)";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    
    // Redirect to remove the GET parameters
    header("Location: notifications.php");
    exit;
}

// Get notifications with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$notifications_per_page = 10;
$offset = ($page - 1) * $notifications_per_page;

// Get total count for pagination (only for notifications after registration)
$count_query = "SELECT COUNT(*) as total FROM notifications 
               WHERE (user_id = ? OR user_id IS NULL) 
               AND created_at >= ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("is", $user_id, $user_registration_date);
$stmt->execute();
$total_notifications = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_notifications / $notifications_per_page);

// Get notifications for current page, apply filter if any
if (isset($_GET['filter']) && $_GET['filter'] !== 'all') {
    if ($_GET['filter'] === 'unread') {
        $filter_condition = " AND is_read = 0";
    } else {
        $filter_condition = " AND notification_type = '" . $conn->real_escape_string($_GET['filter']) . "'";
    }
} else {
    $filter_condition = "";
}

// Get notifications for current page
$notifications_query = "SELECT * FROM notifications 
                       WHERE (user_id = ? OR user_id IS NULL) 
                       AND created_at >= ?
                       $filter_condition
                       ORDER BY created_at DESC 
                       LIMIT ?, ?";
$stmt = $conn->prepare($notifications_query);
$stmt->bind_param("isii", $user_id, $user_registration_date, $offset, $notifications_per_page);
$stmt->execute();
$notifications = $stmt->get_result();

// Count unread notifications
$unread_query = "SELECT COUNT(*) as count FROM notifications 
                WHERE (user_id = ? OR user_id IS NULL) 
                AND is_read = 0
                AND created_at >= ?";
$stmt = $conn->prepare($unread_query);
$stmt->bind_param("is", $user_id, $user_registration_date);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['count'];

include 'header.php';
?>

<main>
    <section class="notifications-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <!-- Sidebar -->
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="user-profile text-center p-4 border-bottom">
                                <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'images/default-profile.png'); ?>" alt="Profile" class="img-fluid rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h5>
                                <p class="text-muted">@<?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="dashboard.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-tachometer-alt me-2"></i> لوحة التحكم
                                </a>
                                <a href="profile.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-user-edit me-2"></i> تعديل ملفي الشخصي
                                </a>
                                <a href="balance.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-wallet me-2"></i> أرصدتي
                                </a>
                                <a href="orders.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-shopping-cart me-2"></i> طلباتي
                                </a>
                                <a href="earnings.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-hand-holding-usd me-2"></i> اكسب معنا
                                </a>
                                <a href="support.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-headset me-2"></i> الدعم الفني
                                </a>
                                <a href="notifications.php" class="list-group-item list-group-item-action active">
                                    <i class="fas fa-bell me-2"></i> التنبيهات
                                    <?php if ($unread_count > 0): ?>
                                    <span class="badge bg-danger float-start"><?php echo $unread_count; ?></span>
                                    <?php endif; ?>
                                </a>
                                <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <!-- Notification Permissions Card -->
                    <div class="card shadow-sm mb-4" id="notificationPermissionCard">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="notification-icon me-3">
                                    <i class="fas fa-bell fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">تفعيل الإشعارات</h5>
                                    <p class="card-text text-muted mb-0">
                                        احصل على إشعارات فورية عند استلام تنبيهات جديدة، حتى عندما لا تكون متصلاً بالموقع.
                                    </p>
                                </div>
                                <div>
                                    <button id="enableNotificationsBtn" class="btn btn-primary">تفعيل الإشعارات</button>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <!-- Notifications Header -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0">التنبيهات</h4>
                                <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?> غير مقروءة</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($notifications && $notifications->num_rows > 0): ?>
                            <div class="btn-group">
                                <a href="notifications.php?action=mark_all_read" class="btn btn-sm btn-primary">
                                    <i class="fas fa-check-double me-1"></i> تعيين الكل كمقروء
                                </a>
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterNotifications" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-filter"></i> تصفية
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="filterNotifications">
                                    <li><a class="dropdown-item" href="notifications.php">جميع الإشعارات</a></li>
                                    <li><a class="dropdown-item" href="notifications.php?filter=unread">غير المقروءة</a></li>
                                    <li><a class="dropdown-item" href="notifications.php?filter=system">إشعارات النظام</a></li>
                                    <li><a class="dropdown-item" href="notifications.php?filter=order">إشعارات الطلبات</a></li>
                                    <li><a class="dropdown-item" href="notifications.php?filter=payment">إشعارات المدفوعات</a></li>
                                    <li><a class="dropdown-item" href="notifications.php?filter=promotion">إشعارات العروض</a></li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Notifications List -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <?php if ($notifications && $notifications->num_rows > 0): ?>
                                <div class="notifications-list">
                                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" data-type="<?php echo htmlspecialchars($notification['notification_type']); ?>">
                                        <div class="notification-header">
                                            <div class="d-flex align-items-center">
                                                <div class="notification-icon-container me-2">
                                                    <i class="<?php echo htmlspecialchars($notification['icon'] ?: 'fas fa-bell'); ?>"></i>
                                                </div>
                                                <h5 class="notification-title">
                                                    <?php if (!$notification['is_read']): ?>
                                                    <span class="unread-indicator"></span>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($notification['title']); ?>
                                                </h5>
                                            </div>
                                            <div class="notification-actions dropdown">
                                                <button class="btn btn-sm dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $notification['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $notification['id']; ?>">
                                                    <?php if (!$notification['is_read']): ?>
                                                    <li><a class="dropdown-item" href="notifications.php?action=mark_read&id=<?php echo $notification['id']; ?>"><i class="fas fa-check me-2"></i> تعيين كمقروء</a></li>
                                                    <?php endif; ?>
                                                    <li><a class="dropdown-item" href="notifications.php?action=delete&id=<?php echo $notification['id']; ?>" onclick="return confirm('هل أنت متأكد من حذف هذا الإشعار؟');"><i class="fas fa-trash-alt me-2"></i> حذف</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="notification-content">
                                            <p><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                                            <?php if (!empty($notification['action_url'])): ?>
                                            <div class="notification-actions mt-2">
                                                <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i> عرض التفاصيل
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="notification-footer">
                                            <small class="text-muted"><?php echo date('Y-m-d H:i', strtotime($notification['created_at'])); ?></small>
                                            <?php if ($notification['user_id'] === null): ?>
                                            <span class="badge bg-info">إشعار عام</span>
                                            <?php endif; ?>
                                            
                                            <?php
                                            $type_badge_class = '';
                                            $type_badge_text = '';
                                            
                                            switch($notification['notification_type']) {
                                                case 'order':
                                                    $type_badge_class = 'bg-primary';
                                                    $type_badge_text = 'طلب';
                                                    break;
                                                case 'payment':
                                                    $type_badge_class = 'bg-success';
                                                    $type_badge_text = 'مدفوعات';
                                                    break;
                                                case 'system':
                                                    $type_badge_class = 'bg-warning';
                                                    $type_badge_text = 'النظام';
                                                    break;
                                                case 'promotion':
                                                    $type_badge_class = 'bg-danger';
                                                    $type_badge_text = 'عروض';
                                                    break;
                                            }
                                            
                                            if ($type_badge_text): 
                                            ?>
                                            <span class="badge <?php echo $type_badge_class; ?>"><?php echo $type_badge_text; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['filter']) ? '&filter=' . htmlspecialchars($_GET['filter']) : ''; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['filter']) ? '&filter=' . htmlspecialchars($_GET['filter']) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['filter']) ? '&filter=' . htmlspecialchars($_GET['filter']) : ''; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                                    <h5>لا توجد تنبيهات</h5>
                                    <p class="text-muted">سيتم عرض جميع التنبيهات والإشعارات هنا.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .notifications-section {
        background-color: #f8f9fa;
    }
    
    .user-profile {
        background-color: #f8f9fa;
    }
    
    .list-group-item {
        border: none;
        padding: 0.8rem 1.5rem;
        font-weight: 500;
    }
    
    .list-group-item.active {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .list-group-item:hover:not(.active) {
        background-color: #f0f0f0;
    }
    
    /* Notification Permissions */
    #notificationPermissionCard {
        transition: all 0.3s ease;
    }
    
    .notification-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Notification Styles */
    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .notification-item {
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #eee;
        transition: all 0.3s ease;
    }
    
    .notification-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .notification-item.unread {
        background-color: #f0f8ff;
        border-right: 4px solid var(--primary-color);
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }
    
    .notification-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0;
        display: flex;
        align-items: center;
    }
    
    .notification-icon-container {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: rgba(33, 150, 243, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
    }
    
    .notification-item[data-type="order"] .notification-icon-container {
        background-color: rgba(33, 150, 243, 0.1);
        color: #2196F3;
    }
    
    .notification-item[data-type="payment"] .notification-icon-container {
        background-color: rgba(76, 175, 80, 0.1);
        color: #4CAF50;
    }
    
    .notification-item[data-type="system"] .notification-icon-container {
        background-color: rgba(255, 152, 0, 0.1);
        color: #FF9800;
    }
    
    .notification-item[data-type="promotion"] .notification-icon-container {
        background-color: rgba(244, 67, 54, 0.1);
        color: #F44336;
    }
    
    .unread-indicator {
        width: 10px;
        height: 10px;
        background-color: var(--primary-color);
        border-radius: 50%;
        display: inline-block;
        margin-left: 8px;
    }
    
    .notification-content {
        color: #555;
        margin-bottom: 0.5rem;
    }
    
    .notification-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .notification-header {
            flex-direction: column;
        }
        
        .notification-actions {
            align-self: flex-end;
            margin-top: 0.5rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationPermissionCard = document.getElementById('notificationPermissionCard');
        const enableNotificationsBtn = document.getElementById('enableNotificationsBtn');
        
        // Check if browser supports notifications
        if (!('Notification' in window)) {
            notificationPermissionCard.style.display = 'none';
        } else if (Notification.permission === 'granted') {
            // Permission already granted
            notificationPermissionCard.style.display = 'none';
        } else if (Notification.permission === 'denied') {
            // Permission was denied
            enableNotificationsBtn.textContent = 'الإشعارات مرفوضة';
            enableNotificationsBtn.classList.remove('btn-primary');
            enableNotificationsBtn.classList.add('btn-secondary');
            enableNotificationsBtn.disabled = true;
        }
        
        // Handle notification permission request
        enableNotificationsBtn.addEventListener('click', function() {
            Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    // Show success message
                    notificationPermissionCard.innerHTML = `
                        <div class="card-body">
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle me-2"></i> تم تفعيل الإشعارات بنجاح!
                            </div>
                        </div>
                    `;
                    
                    // Hide the card after 3 seconds
                    setTimeout(function() {
                        notificationPermissionCard.style.opacity = '0';
                        setTimeout(function() {
                            notificationPermissionCard.style.display = 'none';
                        }, 300);
                    }, 3000);
                    
                    // Send test notification
                    const notification = new Notification('مرحباً بك في متجر مشهور', {
                        body: 'تم تفعيل الإشعارات بنجاح! ستصلك الآن تنبيهات فورية عند استلام إشعارات جديدة.',
                        icon: '/images/logo.png'
                    });
                } else if (permission === 'denied') {
                    // Permission denied
                    enableNotificationsBtn.textContent = 'الإشعارات مرفوضة';
                    enableNotificationsBtn.classList.remove('btn-primary');
                    enableNotificationsBtn.classList.add('btn-secondary');
                    enableNotificationsBtn.disabled = true;
                }
            });
        });
        
        // Check for new notifications every minute (if permission granted)
        if (Notification.permission === 'granted') {
            setInterval(checkForNewNotifications, 60000);
        }
        
        function checkForNewNotifications() {
            // Make an AJAX request to check for new notifications
            fetch('check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.hasNew && data.newNotifications.length > 0) {
                        // Show notification for new messages
                        data.newNotifications.forEach(notification => {
                            const notif = new Notification(notification.title, {
                                body: notification.message,
                                icon: '/images/logo.png'
                            });
                            
                            // When notification is clicked, redirect to notifications page
                            notif.onclick = function() {
                                window.open('notifications.php', '_blank');
                            };
                        });
                        
                        // Update unread count in UI if available
                        const unreadBadge = document.querySelector('.list-group-item.active .badge');
                        if (unreadBadge) {
                            unreadBadge.textContent = data.unreadCount;
                            unreadBadge.style.display = data.unreadCount > 0 ? 'inline' : 'none';
                        }
                    }
                })
                .catch(error => console.error('Error checking notifications:', error));
        }
        
        // Apply filters to notification items if filter is set
        const urlParams = new URLSearchParams(window.location.search);
        const filter = urlParams.get('filter');
        
        if (filter) {
            const notificationItems = document.querySelectorAll('.notification-item');
            
            notificationItems.forEach(item => {
                if (filter === 'unread') {
                    if (!item.classList.contains('unread')) {
                        item.style.display = 'none';
                    }
                } else if (filter === 'order' || filter === 'payment' || filter === 'system' || filter === 'promotion') {
                    if (item.getAttribute('data-type') !== filter) {
                        item.style.display = 'none';
                    }
                }
            });
        }
    });
</script>

<?php include 'footer.php'; ?>