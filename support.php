<?php
// support.php
session_start();
$page_title = "الدعم الفني - متجر مشهور";

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

// Process ticket submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_ticket'])) {
        $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_STRING);
        
        // Validate input
        if (empty($subject) || empty($message)) {
            $error_message = "يرجى ملء جميع الحقول المطلوبة";
        } else {
            // Check if support_tickets table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'support_tickets'");
            if ($table_check->num_rows == 0) {
                // Create support_tickets table
                $conn->query("CREATE TABLE support_tickets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    subject VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
                    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )");
            }
            
            // Check if support_replies table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'support_replies'");
            if ($table_check->num_rows == 0) {
                // Create support_replies table
                $conn->query("CREATE TABLE support_replies (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ticket_id INT NOT NULL,
                    user_id INT DEFAULT NULL,
                    admin_id INT DEFAULT NULL,
                    message TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE
                )");
            }
            
            // Insert new ticket
            $insert_query = "INSERT INTO support_tickets (user_id, subject, message, priority) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("isss", $user_id, $subject, $message, $priority);
            
            if ($stmt->execute()) {
                $ticket_id = $conn->insert_id;
                
                // Create notification for admins
                if ($conn->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0) {
                    // Get all admin users
                    $admin_query = "SELECT id FROM users WHERE role = 'admin'";
                    $admin_result = $conn->query($admin_query);
                    
                    if ($admin_result && $admin_result->num_rows > 0) {
                        // Send notification to each admin individually
                        while ($admin = $admin_result->fetch_assoc()) {
                            $notification_query = "INSERT INTO notifications (user_id, title, message, notification_type, icon, action_url) 
                                                VALUES (?, 'تذكرة دعم جديدة', 'تم إنشاء تذكرة دعم جديدة بواسطة ".$_SESSION['username']."', 'system', 'fas fa-headset', 'admin.php?section=support&action=view&id=$ticket_id')";
                            $stmt = $conn->prepare($notification_query);
                            $stmt->bind_param("i", $admin['id']);
                            $stmt->execute();
                        }
                    }
                }
                
                $success_message = "تم إرسال تذكرة الدعم الفني بنجاح. سيتم الرد عليك في أقرب وقت ممكن.";
            } else {
                $error_message = "حدث خطأ أثناء إرسال التذكرة. يرجى المحاولة مرة أخرى.";
            }
        }
    } elseif (isset($_POST['add_reply'])) {
        $ticket_id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
        $reply_message = filter_input(INPUT_POST, 'reply_message', FILTER_SANITIZE_STRING);
        
        // Validate input
        if (empty($reply_message)) {
            $error_message = "يرجى كتابة رد";
        } else {
            // Verify ticket belongs to user
            $check_query = "SELECT id FROM support_tickets WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ii", $ticket_id, $user_id);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                // Insert reply
                $insert_reply = "INSERT INTO support_replies (ticket_id, user_id, message) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_reply);
                $stmt->bind_param("iis", $ticket_id, $user_id, $reply_message);
                
                if ($stmt->execute()) {
                    // Update ticket status to open if it was closed
                    $update_status = "UPDATE support_tickets SET status = 'open', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'closed'";
                    $stmt = $conn->prepare($update_status);
                    $stmt->bind_param("i", $ticket_id);
                    $stmt->execute();
                    
                    // Create notification for admins
                    if ($conn->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0) {
                        // Get all admin users
                        $admin_query = "SELECT id FROM users WHERE role = 'admin'";
                        $admin_result = $conn->query($admin_query);
                        
                        if ($admin_result && $admin_result->num_rows > 0) {
                            // Send notification to each admin individually
                            while ($admin = $admin_result->fetch_assoc()) {
                                $notification_query = "INSERT INTO notifications (user_id, title, message, notification_type, icon, action_url) 
                                                    VALUES (?, 'رد جديد على تذكرة', 'أضاف ".$_SESSION['username']." ردًا جديدًا على تذكرة الدعم #$ticket_id', 'system', 'fas fa-headset', 'admin.php?section=support&action=view&id=$ticket_id')";
                                $stmt = $conn->prepare($notification_query);
                                $stmt->bind_param("i", $admin['id']);
                                $stmt->execute();
                            }
                        }
                    }
                    
                    $success_message = "تم إضافة الرد بنجاح";
                } else {
                    $error_message = "حدث خطأ أثناء إضافة الرد";
                }
            } else {
                $error_message = "لا يمكنك الرد على هذه التذكرة";
            }
        }
    }
}

// Get ticket details if viewing a specific ticket
$ticket = null;
$replies = null;
if (isset($_GET['id'])) {
    $ticket_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    // Get ticket info
    $ticket_query = "SELECT * FROM support_tickets WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($ticket_query);
    $stmt->bind_param("ii", $ticket_id, $user_id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();
    
    if ($ticket) {
        // Get replies
        $replies_query = "SELECT sr.*, u.username as user_username, a.username as admin_username 
                         FROM support_replies sr 
                         LEFT JOIN users u ON sr.user_id = u.id 
                         LEFT JOIN users a ON sr.admin_id = a.id 
                         WHERE sr.ticket_id = ? 
                         ORDER BY sr.created_at ASC";
        $stmt = $conn->prepare($replies_query);
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $replies = $stmt->get_result();
    }
}

// Get user tickets
$tickets_query = "SELECT * FROM support_tickets WHERE user_id = ? ORDER BY updated_at DESC";
$stmt = $conn->prepare($tickets_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tickets = $stmt->get_result();

include 'header.php';
?>

<main>
    <section class="support-section py-5">
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
                                <a href="support.php" class="list-group-item list-group-item-action active">
                                    <i class="fas fa-headset me-2"></i> الدعم الفني
                                </a>
                                <a href="notifications.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-bell me-2"></i> التنبيهات
                                </a>
                                <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success mb-4">
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger mb-4">
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($ticket) && $ticket): ?>
                    <!-- Ticket Details -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">تذكرة #<?php echo $ticket['id']; ?>: <?php echo htmlspecialchars($ticket['subject']); ?></h5>
                            <a href="support.php" class="btn btn-sm btn-outline-secondary">
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
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>التاريخ:</strong></p>
                                        <span><?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?></span>
                                    </div>
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
                                                <span class="badge <?php echo $reply['user_id'] ? 'bg-primary' : 'bg-info'; ?> me-2">
                                                    <?php echo $reply['user_id'] ? 'أنت' : 'الدعم الفني'; ?>
                                                </span>
                                                <span class="reply-name">
                                                    <?php echo $reply['user_id'] ? htmlspecialchars($reply['user_username']) : htmlspecialchars($reply['admin_username']); ?>
                                                </span>
                                            </div>
                                            <span class="reply-date text-muted small">
                                                <?php echo date('Y-m-d H:i', strtotime($reply['created_at'])); ?>
                                            </span>
                                        </div>
                                        <div class="reply-content p-3 <?php echo $reply['user_id'] ? 'bg-light' : 'bg-info bg-opacity-10'; ?> rounded">
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
                                <?php if ($ticket['status'] !== 'closed'): ?>
                                <form method="post" action="" class="reply-form mt-4">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label for="reply_message" class="form-label">إضافة رد:</label>
                                        <textarea class="form-control" id="reply_message" name="reply_message" rows="4" required></textarea>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" name="add_reply" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-1"></i> إرسال الرد
                                        </button>
                                    </div>
                                </form>
                                <?php else: ?>
                                <div class="alert alert-warning mt-4">
                                    <i class="fas fa-lock me-2"></i>
                                    هذه التذكرة مغلقة. إذا كنت بحاجة إلى مزيد من المساعدة، يرجى فتح تذكرة جديدة.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php elseif (isset($_GET['new'])): ?>
                    <!-- New Ticket Form -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">تذكرة جديدة</h5>
                            <a href="support.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-right ms-1"></i> العودة للقائمة
                            </a>
                        </div>
                        
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">الموضوع *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="priority" class="form-label">الأولوية *</label>
                                    <select class="form-select" id="priority" name="priority" required>
                                        <option value="low">منخفضة</option>
                                        <option value="medium" selected>متوسطة</option>
                                        <option value="high">عالية</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">الرسالة *</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                                    <div class="form-text">يرجى وصف مشكلتك أو استفسارك بالتفصيل ليتمكن فريق الدعم من مساعدتك بشكل أفضل.</div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="submit_ticket" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> إرسال التذكرة
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Tickets List -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">تذاكر الدعم الفني</h5>
                            <a href="support.php?new=1" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> تذكرة جديدة
                            </a>
                        </div>
                        
                        <div class="card-body p-0">
                            <?php if ($tickets && $tickets->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>الموضوع</th>
                                            <th>الحالة</th>
                                            <th>الأولوية</th>
                                            <th>آخر تحديث</th>
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($ticket = $tickets->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $ticket['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
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
                                            <td><?php echo date('Y-m-d H:i', strtotime($ticket['updated_at'])); ?></td>
                                            <td>
                                                <a href="support.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center p-4">
                                <div class="mb-3">
                                    <i class="fas fa-ticket-alt fa-3x text-muted"></i>
                                </div>
                                <p class="text-muted mb-4">لا توجد تذاكر دعم فني حتى الآن</p>
                                <a href="support.php?new=1" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> إنشاء تذكرة جديدة
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Alternative Contact Methods -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">تواصل معنا مباشرة</h5>
                        </div>
                        
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <a href="https://t.me/mashhors" target="_blank" class="contact-link">
                                        <div class="contact-icon telegram-icon">
                                            <i class="fab fa-telegram fa-3x"></i>
                                        </div>
                                        <h5 class="mt-3">تواصل عبر تيليجرام</h5>
                                        <p class="text-muted">للدعم الفني السريع راسلنا على تيليجرام</p>
                                    </a>
                                </div>
                                
                                <div class="col-md-6">
                                    <a href="https://wa.me/+1234567890" target="_blank" class="contact-link">
                                        <div class="contact-icon whatsapp-icon">
                                            <i class="fab fa-whatsapp fa-3x"></i>
                                        </div>
                                        <h5 class="mt-3">تواصل عبر واتساب</h5>
                                        <p class="text-muted">احصل على المساعدة مباشرة عبر الواتساب</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Support FAQ -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">الأسئلة الشائعة</h5>
                        </div>
                        
                        <div class="card-body">
                            <div class="accordion" id="faqAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            كيف يمكنني شحن رصيدي؟
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            يمكنك شحن رصيدك من خلال الانتقال إلى صفحة <a href="balance.php">أرصدتي</a> واختيار طريقة الدفع المناسبة لك. نحن ندعم العديد من طرق الدفع مثل البطاقات الائتمانية، USDT، Binance Pay، والتحويل البنكي.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            كم من الوقت يستغرق تنفيذ الطلب؟
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            يختلف وقت تنفيذ الطلب حسب نوع الخدمة وحجم الطلب. بعض الخدمات تبدأ فورًا والبعض الآخر قد يستغرق بضع ساعات للبدء. يمكنك الاطلاع على وقت البدء المتوقع وسرعة التنفيذ في تفاصيل كل خدمة قبل الطلب.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            هل المتابعين/المشاهدات حقيقية؟
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            نعم، نحن نقدم خدمات عالية الجودة. جودة المتابعين/المشاهدات تختلف حسب نوع الخدمة المختارة. بعض الخدمات توفر متابعين حقيقيين نشطين، والبعض الآخر قد يكون من حسابات أقل نشاطًا. يرجى قراءة وصف الخدمة بعناية قبل الطلب.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            ما هي سياسة الضمان لديكم؟
                                        </button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            نحن نقدم ضمانًا على بعض خدماتنا لفترة محددة (تختلف حسب الخدمة). إذا حدث انخفاض في العدد خلال فترة الضمان، سنقوم بتعويض النقص تلقائيًا. تفاصيل الضمان مذكورة في وصف كل خدمة.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFive">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                            كيف يمكنني الحصول على مساعدة إضافية؟
                                        </button>
                                    </h2>
                                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            يمكنك إنشاء تذكرة دعم فني جديدة من خلال النقر على زر "تذكرة جديدة" أعلاه، أو التواصل معنا عبر منصات التواصل الاجتماعي المذكورة في أسفل الصفحة. فريق الدعم الفني متاح للمساعدة على مدار الساعة.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .support-section {
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
    
    /* Reply styles */
    .original-message {
        border-right: 4px solid var(--primary-color);
    }
    
    .reply-item {
        margin-right: 1rem;
        margin-left: 1rem;
    }
    
    .admin-reply .reply-content {
        border-right: 3px solid var(--primary-color);
    }
    
    .user-reply .reply-content {
        border-right: 3px solid var(--secondary-color);
    }
    
    /* Contact methods */
    .contact-link {
        display: block;
        text-decoration: none;
        color: var(--text-color);
        padding: 1rem;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .contact-link:hover {
        background-color: #f8f9fa;
        transform: translateY(-5px);
    }

    .contact-icon {
        height: 70px;
        width: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: white;
    }

    .telegram-icon {
        background-color: #0088cc;
    }

    .whatsapp-icon {
        background-color: #25D366;
    }
    
    /* Accordion customization */
    .accordion-button:not(.collapsed) {
        background-color: #e7f5ff;
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
</style>

<script>
    // Preview uploaded image 
    document.addEventListener('DOMContentLoaded', function() {
        const messageInput = document.getElementById('message');
        if (messageInput) {
            messageInput.focus();
        }
        
        const replyMessage = document.getElementById('reply_message');
        if (replyMessage) {
            replyMessage.focus();
        }
    });
</script>

<?php include 'footer.php'; ?>