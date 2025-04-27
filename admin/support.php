<!-- Support Management -->
<div class="support-section">
    <h1 class="mb-4">إدارة الدعم الفني</h1>
    
    <?php
    // Process ticket actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_reply'])) {
            $ticket_id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
            $reply_message = filter_input(INPUT_POST, 'reply_message', FILTER_SANITIZE_STRING);
            $admin_id = $_SESSION['user_id'];
            
            if (empty($reply_message)) {
                echo '<div class="alert alert-danger">الرجاء كتابة رد</div>';
            } else {
                // Check if ticket exists
                $check_query = "SELECT * FROM support_tickets WHERE id = ?";
                $stmt = $conn->prepare($check_query);
                $stmt->bind_param("i", $ticket_id);
                $stmt->execute();
                $ticket = $stmt->get_result()->fetch_assoc();
                
                if ($ticket) {
                    // Insert reply
                    $insert_reply = "INSERT INTO support_replies (ticket_id, admin_id, message) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($insert_reply);
                    $stmt->bind_param("iis", $ticket_id, $admin_id, $reply_message);
                    
                    if ($stmt->execute()) {
                        // Update ticket status to in_progress if it was open
                        if ($ticket['status'] == 'open') {
                            $update_status = "UPDATE support_tickets SET status = 'in_progress', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                            $stmt = $conn->prepare($update_status);
                            $stmt->bind_param("i", $ticket_id);
                            $stmt->execute();
                        }
                        
                        // Create notification for user
                        if ($conn->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0) {
                            $notification_query = "INSERT INTO notifications (user_id, title, message, notification_type, icon, action_url) 
                                                VALUES (?, 'رد جديد على تذكرة الدعم', 'تم الرد على تذكرة الدعم الفني الخاصة بك #$ticket_id', 'system', 'fas fa-headset', 'support.php?id=$ticket_id')";
                            $stmt = $conn->prepare($notification_query);
                            $stmt->bind_param("i", $ticket['user_id']);
                            $stmt->execute();
                        }
                        
                        echo '<div class="alert alert-success">تم إضافة الرد بنجاح</div>';
                    } else {
                        echo '<div class="alert alert-danger">حدث خطأ أثناء إضافة الرد</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">التذكرة غير موجودة</div>';
                }
            }
        } elseif (isset($_POST['update_ticket_status'])) {
            $ticket_id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
            $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            
            // Update ticket status
            $update_status = "UPDATE support_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $conn->prepare($update_status);
            $stmt->bind_param("si", $new_status, $ticket_id);
            
            if ($stmt->execute()) {
                // Create notification for user
                if ($conn->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0) {
                    // Get user_id from ticket
                    $user_query = "SELECT user_id FROM support_tickets WHERE id = ?";
                    $stmt = $conn->prepare($user_query);
                    $stmt->bind_param("i", $ticket_id);
                    $stmt->execute();
                    $user_id = $stmt->get_result()->fetch_assoc()['user_id'];
                    
                    $status_text = '';
                    switch ($new_status) {
                        case 'open':
                            $status_text = 'مفتوحة';
                            break;
                        case 'in_progress':
                            $status_text = 'قيد المعالجة';
                            break;
                        case 'closed':
                            $status_text = 'مغلقة';
                            break;
                    }
                    
                    $notification_query = "INSERT INTO notifications (user_id, title, message, notification_type, icon, action_url) 
                                        VALUES (?, 'تحديث حالة التذكرة', 'تم تحديث حالة تذكرة الدعم #$ticket_id إلى $status_text', 'system', 'fas fa-headset', 'support.php?id=$ticket_id')";
                    $stmt = $conn->prepare($notification_query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                }
                
                echo '<div class="alert alert-success">تم تحديث حالة التذكرة بنجاح</div>';
            } else {
                echo '<div class="alert alert-danger">حدث خطأ أثناء تحديث حالة التذكرة</div>';
            }
        } elseif (isset($_POST['update_ticket_priority'])) {
            $ticket_id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
            $new_priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_STRING);
            
            // Update ticket priority
            $update_priority = "UPDATE support_tickets SET priority = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $conn->prepare($update_priority);
            $stmt->bind_param("si", $new_priority, $ticket_id);
            
            if ($stmt->execute()) {
                echo '<div class="alert alert-success">تم تحديث أولوية التذكرة بنجاح</div>';
            } else {
                echo '<div class="alert alert-danger">حدث خطأ أثناء تحديث أولوية التذكرة</div>';
            }
        }
    }
    
    // Get ticket details if viewing a specific ticket
    $ticket = null;
    $replies = null;
    $user_info = null;
    
    if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
        $ticket_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        // Get ticket info with user details
        $ticket_query = "SELECT t.*, u.username, u.email, u.full_name, u.profile_image 
                       FROM support_tickets t 
                       JOIN users u ON t.user_id = u.id 
                       WHERE t.id = ?";
        $stmt = $conn->prepare($ticket_query);
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        
        if ($ticket) {
            // Get replies
            $replies_query = "SELECT sr.*, u.username as user_username, u.profile_image as user_image, 
                            a.username as admin_username, a.profile_image as admin_image
                            FROM support_replies sr 
                            LEFT JOIN users u ON sr.user_id = u.id 
                            LEFT JOIN users a ON sr.admin_id = a.id 
                            WHERE sr.ticket_id = ? 
                            ORDER BY sr.created_at ASC";
            $stmt = $conn->prepare($replies_query);
            $stmt->bind_param("i", $ticket_id);
            $stmt->execute();
            $replies = $stmt->get_result();
            
            // Get user's other tickets
            $user_tickets_query = "SELECT * FROM support_tickets 
                                 WHERE user_id = ? AND id != ? 
                                 ORDER BY created_at DESC LIMIT 5";
            $stmt = $conn->prepare($user_tickets_query);
            $stmt->bind_param("ii", $ticket['user_id'], $ticket_id);
            $stmt->execute();
            $user_tickets = $stmt->get_result();
            
            // Get user info
            $user_info_query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($user_info_query);
            $stmt->bind_param("i", $ticket['user_id']);
            $stmt->execute();
            $user_info = $stmt->get_result()->fetch_assoc();
        }
    }
    
    // Get support tickets
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $tickets_query = "SELECT t.*, u.username 
                    FROM support_tickets t 
                    JOIN users u ON t.user_id = u.id";
    
    if ($status_filter !== 'all') {
        $tickets_query .= " WHERE t.status = '$status_filter'";
    }
    
    $tickets_query .= " ORDER BY 
                        CASE 
                            WHEN t.status = 'open' THEN 1 
                            WHEN t.status = 'in_progress' THEN 2 
                            WHEN t.status = 'closed' THEN 3 
                        END, 
                        CASE 
                            WHEN t.priority = 'high' THEN 1 
                            WHEN t.priority = 'medium' THEN 2 
                            WHEN t.priority = 'low' THEN 3 
                        END, 
                        t.updated_at DESC";
    
    $tickets = $conn->query($tickets_query);
    
    // Get support stats
    $open_tickets_query = "SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'";
    $open_tickets = $conn->query($open_tickets_query)->fetch_assoc()['count'];
    
    $in_progress_tickets_query = "SELECT COUNT(*) as count FROM support_tickets WHERE status = 'in_progress'";
    $in_progress_tickets = $conn->query($in_progress_tickets_query)->fetch_assoc()['count'];
    
    $closed_tickets_query = "SELECT COUNT(*) as count FROM support_tickets WHERE status = 'closed'";
    $closed_tickets = $conn->query($closed_tickets_query)->fetch_assoc()['count'];
    
    $high_priority_query = "SELECT COUNT(*) as count FROM support_tickets WHERE priority = 'high' AND status != 'closed'";
    $high_priority = $conn->query($high_priority_query)->fetch_assoc()['count'];
    ?>
    
    <?php if (isset($ticket) && $ticket): ?>
    <!-- Ticket Details View -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">تذكرة #<?php echo $ticket['id']; ?>: <?php echo htmlspecialchars($ticket['subject']); ?></h5>
                    <a href="admin.php?section=support" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-right ms-1"></i> العودة للقائمة
                    </a>
                </div>
                
                <div class="card-body">
                    <div class="ticket-details mb-4">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>الحالة:</strong></p>
                                <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    switch ($ticket['status']) {
                                        case 'open':
                                            $status_class = 'bg-success';
                                            $status_text = 'مفتوحة';
                                            break;
                                        case 'in_progress':
                                            $status_class = 'bg-warning';
                                            $status_text = 'قيد المعالجة';
                                            break;
                                        case 'closed':
                                            $status_class = 'bg-secondary';
                                            $status_text = 'مغلقة';
                                            break;
                                    }
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                
                                <div class="mt-2">
                                    <form method="post" action="" class="d-inline-block">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <select class="form-select form-select-sm" name="status">
                                                <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>مفتوحة</option>
                                                <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>قيد المعالجة</option>
                                                <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>مغلقة</option>
                                            </select>
                                            <button type="submit" name="update_ticket_status" class="btn btn-sm btn-outline-primary">تحديث</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <p class="mb-1"><strong>الأولوية:</strong></p>
                                <?php 
                                    $priority_class = '';
                                    $priority_text = '';
                                    
                                    switch ($ticket['priority']) {
                                        case 'low':
                                            $priority_class = 'bg-info';
                                            $priority_text = 'منخفضة';
                                            break;
                                        case 'medium':
                                            $priority_class = 'bg-primary';
                                            $priority_text = 'متوسطة';
                                            break;
                                        case 'high':
                                            $priority_class = 'bg-danger';
                                            $priority_text = 'عالية';
                                            break;
                                    }
                                ?>
                                <span class="badge <?php echo $priority_class; ?>"><?php echo $priority_text; ?></span>
                                
                                <div class="mt-2">
                                    <form method="post" action="" class="d-inline-block">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <select class="form-select form-select-sm" name="priority">
                                                <option value="low" <?php echo $ticket['priority'] === 'low' ? 'selected' : ''; ?>>منخفضة</option>
                                                <option value="medium" <?php echo $ticket['priority'] === 'medium' ? 'selected' : ''; ?>>متوسطة</option>
                                                <option value="high" <?php echo $ticket['priority'] === 'high' ? 'selected' : ''; ?>>عالية</option>
                                            </select>
                                            <button type="submit" name="update_ticket_priority" class="btn btn-sm btn-outline-primary">تحديث</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <p><strong>تاريخ الإنشاء:</strong> <?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?></p>
                                <p><strong>آخر تحديث:</strong> <?php echo date('Y-m-d H:i', strtotime($ticket['updated_at'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="user-info d-flex align-items-center p-3 bg-light rounded mb-3">
                            <img src="<?php echo htmlspecialchars($ticket['profile_image'] ?? '../images/default-profile.png'); ?>" alt="User" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($ticket['full_name']); ?> (@<?php echo htmlspecialchars($ticket['username']); ?>)</h6>
                                <p class="mb-0 small text-muted"><?php echo htmlspecialchars($ticket['email']); ?></p>
                            </div>
                            <a href="admin.php?section=users&action=view&id=<?php echo $ticket['user_id']; ?>" class="btn btn-sm btn-outline-primary ms-auto">
                                <i class="fas fa-user me-1"></i> عرض الملف
                            </a>
                        </div>
                        
                        <div class="original-message p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                        </div>
                    </div>
                    
                    <!-- Replies -->
                    <div class="ticket-replies">
                        <h6 class="mb-3">المحادثة</h6>
                        
                        <?php if ($replies && $replies->num_rows > 0): ?>
                        <div class="replies-list">
                            <?php while ($reply = $replies->fetch_assoc()): ?>
                            <div class="reply-item mb-3 <?php echo $reply['user_id'] ? 'user-reply' : 'admin-reply'; ?>">
                                <div class="reply-header d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $reply['user_id'] ? htmlspecialchars($reply['user_image'] ?? '../images/default-profile.png') : htmlspecialchars($reply['admin_image'] ?? '../images/default-profile.png'); ?>" 
                                             alt="User" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                        <span class="badge <?php echo $reply['user_id'] ? 'bg-info' : 'bg-primary'; ?> me-2">
                                            <?php echo $reply['user_id'] ? 'المستخدم' : 'الدعم الفني'; ?>
                                        </span>
                                        <span class="reply-name">
                                            <?php echo $reply['user_id'] ? htmlspecialchars($reply['user_username']) : htmlspecialchars($reply['admin_username']); ?>
                                        </span>
                                    </div>
                                    <span class="reply-date text-muted small">
                                        <?php echo date('Y-m-d H:i', strtotime($reply['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="reply-content p-3 <?php echo $reply['user_id'] ? 'bg-info bg-opacity-10' : 'bg-light'; ?> rounded">
                                    <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            لا توجد ردود حتى الآن
                        </div>
                        <?php endif; ?>
                        
                        <!-- Reply Form -->
                        <form method="post" action="" class="reply-form mt-4">
                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="reply_message" class="form-label">إضافة رد:</label>
                                <textarea class="form-control" id="reply_message" name="reply_message" rows="4" required 
                                    <?php echo $ticket['status'] === 'closed' ? 'disabled' : ''; ?>></textarea>
                                
                                <?php if ($ticket['status'] === 'closed'): ?>
                                <div class="form-text text-warning">
                                    التذكرة مغلقة. قم بتغيير الحالة إلى "مفتوحة" أو "قيد المعالجة" للرد.
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="add_reply" class="btn btn-primary" <?php echo $ticket['status'] === 'closed' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-paper-plane me-1"></i> إرسال الرد
                                </button>
                                
                                <?php if ($ticket['status'] !== 'closed'): ?>
                                <form method="post" action="" class="d-inline ms-2">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    <input type="hidden" name="status" value="closed">
                                    <button type="submit" name="update_ticket_status" class="btn btn-outline-secondary">
                                        <i class="fas fa-check-circle me-1"></i> إغلاق التذكرة
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="post" action="" class="d-inline ms-2">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    <input type="hidden" name="status" value="open">
                                    <button type="submit" name="update_ticket_status" class="btn btn-outline-success">
                                        <i class="fas fa-redo-alt me-1"></i> إعادة فتح التذكرة
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- User Information Card -->
            <?php if ($user_info): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">معلومات المستخدم</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="<?php echo htmlspecialchars($user_info['profile_image'] ?? '../images/default-profile.png'); ?>" alt="User" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                        <h6 class="mb-0"><?php echo htmlspecialchars($user_info['full_name']); ?></h6>
                        <p class="text-muted">@<?php echo htmlspecialchars($user_info['username']); ?></p>
                    </div>
                    
                    <hr>
                    
                    <div class="user-details">
                        <p><strong><i class="fas fa-envelope me-2"></i> البريد الإلكتروني:</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
                        <p><strong><i class="fas fa-phone me-2"></i> الهاتف:</strong> <?php echo htmlspecialchars($user_info['phone'] ?? 'غير متوفر'); ?></p>
                        <p><strong><i class="fas fa-globe me-2"></i> البلد:</strong> <?php echo htmlspecialchars($user_info['country'] ?? 'غير متوفر'); ?></p>
                        <p><strong><i class="fas fa-calendar me-2"></i> تاريخ التسجيل:</strong> <?php echo date('Y-m-d', strtotime($user_info['created_at'])); ?></p>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="admin.php?section=users&action=view&id=<?php echo $user_info['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-user me-1"></i> عرض الملف الكامل
                        </a>
                        
                        <a href="admin.php?section=orders&user_id=<?php echo $user_info['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-shopping-cart me-1"></i> عرض طلبات المستخدم
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- User's Other Tickets -->
            <?php if (isset($user_tickets) && $user_tickets->num_rows > 0): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">تذاكر المستخدم الأخرى</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php while ($user_ticket = $user_tickets->fetch_assoc()): ?>
                        <a href="admin.php?section=support&action=view&id=<?php echo $user_ticket['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <strong>#<?php echo $user_ticket['id']; ?></strong>: <?php echo htmlspecialchars(substr($user_ticket['subject'], 0, 30)); ?>...
                                </span>
                                <?php 
                                    $status_class = '';
                                    
                                    switch ($user_ticket['status']) {
                                        case 'open':
                                            $status_class = 'bg-success';
                                            $status_text = 'مفتوحة';
                                            break;
                                        case 'in_progress':
                                            $status_class = 'bg-warning';
                                            $status_text = 'قيد المعالجة';
                                            break;
                                        case 'closed':
                                            $status_class = 'bg-secondary';
                                            $status_text = 'مغلقة';
                                            break;
                                    }
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </div>
                            <div class="text-muted small mt-1">
                                <?php echo date('Y-m-d H:i', strtotime($user_ticket['created_at'])); ?>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Tickets List View -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #4CAF50;">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stats-card-value"><?php echo $open_tickets; ?></div>
                <div class="stats-card-label">تذاكر مفتوحة</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #FFC107;">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stats-card-value"><?php echo $in_progress_tickets; ?></div>
                <div class="stats-card-label">تذاكر قيد المعالجة</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #9E9E9E;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-card-value"><?php echo $closed_tickets; ?></div>
                <div class="stats-card-label">تذاكر مغلقة</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #F44336;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stats-card-value"><?php echo $high_priority; ?></div>
                <div class="stats-card-label">ذات أولوية عالية</div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter === 'all' ? 'active' : ''; ?>" href="admin.php?section=support">جميع التذاكر</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter === 'open' ? 'active' : ''; ?>" href="admin.php?section=support&status=open">مفتوحة</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter === 'in_progress' ? 'active' : ''; ?>" href="admin.php?section=support&status=in_progress">قيد المعالجة</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter === 'closed' ? 'active' : ''; ?>" href="admin.php?section=support&status=closed">مغلقة</a>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>الموضوع</th>
                            <th>الحالة</th>
                            <th>الأولوية</th>
                            <th>تاريخ الإنشاء</th>
                            <th>آخر تحديث</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tickets && $tickets->num_rows > 0): ?>
                            <?php while ($ticket = $tickets->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $ticket['id']; ?></td>
                                <td><?php echo htmlspecialchars($ticket['username']); ?></td>
                                <td><?php echo htmlspecialchars(substr($ticket['subject'], 0, 40)); ?><?php echo strlen($ticket['subject']) > 40 ? '...' : ''; ?></td>
                                <td>
                                    <?php 
                                        $status_class = '';
                                        $status_text = '';
                                        
                                        switch ($ticket['status']) {
                                            case 'open':
                                                $status_class = 'bg-success';
                                                $status_text = 'مفتوحة';
                                                break;
                                            case 'in_progress':
                                                $status_class = 'bg-warning';
                                                $status_text = 'قيد المعالجة';
                                                break;
                                            case 'closed':
                                                $status_class = 'bg-secondary';
                                                $status_text = 'مغلقة';
                                                break;
                                        }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $priority_class = '';
                                        $priority_text = '';
                                        
                                        switch ($ticket['priority']) {
                                            case 'low':
                                                $priority_class = 'bg-info';
                                                $priority_text = 'منخفضة';
                                                break;
                                            case 'medium':
                                                $priority_class = 'bg-primary';
                                                $priority_text = 'متوسطة';
                                                break;
                                            case 'high':
                                                $priority_class = 'bg-danger';
                                                $priority_text = 'عالية';
                                                break;
                                        }
                                    ?>
                                    <span class="badge <?php echo $priority_class; ?>"><?php echo $priority_text; ?></span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($ticket['updated_at'])); ?></td>
                                <td>
                                    <a href="admin.php?section=support&action=view&id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <?php if ($ticket['status'] !== 'closed'): ?>
                                            <li>
                                                <form method="post" action="">
                                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                    <input type="hidden" name="status" value="closed">
                                                    <button type="submit" name="update_ticket_status" class="dropdown-item">
                                                        <i class="fas fa-check-circle me-1"></i> إغلاق التذكرة
                                                    </button>
                                                </form>
                                            </li>
                                            <?php else: ?>
                                            <li>
                                                <form method="post" action="">
                                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                    <input type="hidden" name="status" value="open">
                                                    <button type="submit" name="update_ticket_status" class="dropdown-item">
                                                        <i class="fas fa-redo-alt me-1"></i> إعادة فتح التذكرة
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <?php if ($ticket['status'] === 'open'): ?>
                                            <li>
                                                <form method="post" action="">
                                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                    <input type="hidden" name="status" value="in_progress">
                                                    <button type="submit" name="update_ticket_status" class="dropdown-item">
                                                        <i class="fas fa-spinner me-1"></i> تعيين قيد المعالجة
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <li><hr class="dropdown-divider"></li>
                                            
                                            <li>
                                                <a href="admin.php?section=users&action=view&id=<?php echo $ticket['user_id']; ?>" class="dropdown-item">
                                                    <i class="fas fa-user me-1"></i> عرض ملف المستخدم
                                                </a>
                                            </li>
                                            
                                            <li>
                                                <a href="admin.php?section=notifications&user_id=<?php echo $ticket['user_id']; ?>" class="dropdown-item">
                                                    <i class="fas fa-bell me-1"></i> إرسال إشعار
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center p-4">
                                    <?php if ($status_filter === 'all'): ?>
                                        <p>لا توجد تذاكر دعم فني حتى الآن</p>
                                    <?php else: ?>
                                        <p>لا توجد تذاكر <?php echo $status_filter === 'open' ? 'مفتوحة' : ($status_filter === 'in_progress' ? 'قيد المعالجة' : 'مغلقة'); ?> حاليًا</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    /* Stats Cards */
    .stats-card {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
    }

    .stats-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 15px;
        font-size: 24px;
        color: white;
    }

    .stats-card-value {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stats-card-label {
        color: #6c757d;
        font-size: 14px;
    }
    
    /* Reply styles */
    .original-message {
        border-right: 4px solid #2196F3;
    }
    
    .reply-item {
        margin-right: 1rem;
        margin-left: 1rem;
    }
    
    .user-reply .reply-content {
        border-right: 3px solid #17a2b8;
    }
    
    .admin-reply .reply-content {
        border-right: 3px solid #6c757d;
    }
    
    /* Tab customization */
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #6c757d;
        padding: 0.5rem 1rem;
    }
    
    .nav-tabs .nav-link.active {
        color: #2196F3;
        border-bottom: 2px solid #2196F3;
        background-color: transparent;
    }
    
    .nav-tabs .nav-link:hover:not(.active) {
        border-color: transparent;
        color: #0d6efd;
    }
</style>

<script>
$(document).ready(function() {
    $('.datatable').DataTable({
        "responsive": true,
        "ordering": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
        },
        "order": [[0, "desc"]]
    });
});
</script>