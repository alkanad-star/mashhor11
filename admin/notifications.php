<!-- Notifications Management -->
<div class="notifications-section">
    <h1 class="mb-4">إدارة الإشعارات</h1>
    
    <!-- Send Notification -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">إرسال إشعار جديد</h5>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="notification_type" class="form-label">نوع الإشعار</label>
                    <select class="form-select" id="notification_type" name="notification_type" required>
                        <option value="general">إشعار عام</option>
                        <option value="order">إشعار طلب</option>
                        <option value="payment">إشعار مدفوعات</option>
                        <option value="system">إشعار نظام</option>
                        <option value="promotion">إشعار عروض</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="icon" class="form-label">أيقونة الإشعار</label>
                    <select class="form-select" id="icon" name="icon">
                        <option value="fas fa-bell">🔔 جرس (افتراضي)</option>
                        <option value="fas fa-info-circle">ℹ️ معلومات</option>
                        <option value="fas fa-exclamation-triangle">⚠️ تحذير</option>
                        <option value="fas fa-check-circle">✅ نجاح</option>
                        <option value="fas fa-shopping-cart">🛒 طلب</option>
                        <option value="fas fa-wallet">💰 مدفوعات</option>
                        <option value="fas fa-gift">🎁 هدية</option>
                        <option value="fas fa-percent">💯 عرض</option>
                        <option value="fas fa-cog">⚙️ إعدادات</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="target_users" class="form-label">إلى</label>
                    <select class="form-select" id="target_users" name="target_users" required>
                        <option value="all">جميع المستخدمين</option>
                        <option value="specific" <?php echo isset($_GET['user_id']) ? 'selected' : ''; ?>>مستخدم محدد</option>
                        <option value="global">إشعار عام</option>
                    </select>
                </div>
                
                <div class="mb-3 specific-user-container" id="specific_user_container" style="display: <?php echo isset($_GET['user_id']) ? 'block' : 'none'; ?>;">
                    <label for="user_search" class="form-label">البحث عن مستخدم</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="user_search" placeholder="ابحث باسم المستخدم أو البريد الإلكتروني">
                        <button class="btn btn-outline-secondary" type="button" id="search_user_btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="user_search_results" class="list-group mb-2" style="display:none; max-height: 200px; overflow-y: auto;"></div>
                    
                    <select class="form-select" id="user_id" name="user_id">
                        <?php if (isset($_GET['user_id'])): ?>
                            <?php 
                            $user_id = intval($_GET['user_id']);
                            $user_query = "SELECT id, username, email FROM users WHERE id = ?";
                            $stmt = $conn->prepare($user_query);
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $user = $stmt->get_result()->fetch_assoc();
                            ?>
                            <?php if ($user): ?>
                            <option value="<?php echo $user['id']; ?>" selected><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</option>
                            <?php endif; ?>
                        <?php else: ?>
                            <option value="">-- اختر مستخدم --</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="title" class="form-label">عنوان الإشعار</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">محتوى الإشعار</label>
                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="action_url" class="form-label">رابط الإجراء (اختياري)</label>
                    <input type="text" class="form-control" id="action_url" name="action_url" placeholder="مثال: orders.php?id=123">
                    <small class="text-muted">عند النقر على الإشعار، سيتم توجيه المستخدم إلى هذا الرابط</small>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="send_notification" class="btn btn-primary">إرسال الإشعار</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Notifications History -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between">
            <h5 class="card-title mb-0">سجل الإشعارات</h5>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-filter"></i> تصفية
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="?section=notifications">جميع الإشعارات</a></li>
                    <li><a class="dropdown-item" href="?section=notifications&filter=general">إشعارات عامة</a></li>
                    <li><a class="dropdown-item" href="?section=notifications&filter=order">إشعارات الطلبات</a></li>
                    <li><a class="dropdown-item" href="?section=notifications&filter=payment">إشعارات المدفوعات</a></li>
                    <li><a class="dropdown-item" href="?section=notifications&filter=system">إشعارات النظام</a></li>
                    <li><a class="dropdown-item" href="?section=notifications&filter=promotion">إشعارات العروض</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <?php
            // Check if notifications table exists
            $check_table_query = "SHOW TABLES LIKE 'notifications'";
            $table_exists = $conn->query($check_table_query)->num_rows > 0;
            
            if ($table_exists) {
                // Apply filters if any
                $where_clause = "";
                if (isset($_GET['filter']) && !empty($_GET['filter'])) {
                    $filter = $_GET['filter'];
                    $where_clause = "WHERE notification_type = '" . $conn->real_escape_string($filter) . "'";
                }
                
                $notifications_query = "SELECT n.*, u.username
                                      FROM notifications n
                                      LEFT JOIN users u ON n.user_id = u.id
                                      $where_clause
                                      ORDER BY n.created_at DESC
                                      LIMIT 100";
                $notifications = $conn->query($notifications_query);
            } else {
                $notifications = null;
            }
            ?>
            
            <?php if ($table_exists && $notifications && $notifications->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>النوع</th>
                            <th>المستلم</th>
                            <th>العنوان</th>
                            <th>المحتوى</th>
                            <th>الرابط</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($notification = $notifications->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $notification['id']; ?></td>
                            <td>
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
                                    default:
                                        $type_badge_class = 'bg-info';
                                        $type_badge_text = 'عام';
                                }
                                ?>
                                <span class="badge <?php echo $type_badge_class; ?>"><?php echo $type_badge_text; ?></span>
                            </td>
                            <td>
                                <?php if ($notification['user_id']): ?>
                                    <a href="?section=users&action=view&id=<?php echo $notification['user_id']; ?>"><?php echo htmlspecialchars($notification['username']); ?></a>
                                <?php else: ?>
                                    <span class="badge bg-info">عام</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($notification['title']); ?></td>
                            <td>
                                <?php 
                                $message = $notification['message'];
                                if (strlen($message) > 50) {
                                    echo htmlspecialchars(substr($message, 0, 50)) . '...';
                                } else {
                                    echo htmlspecialchars($message);
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($notification['action_url'])): ?>
                                <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($notification['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#notificationModal<?php echo $notification['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <form method="post" action="admin.php?section=notifications&action=delete" class="d-inline">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الإشعار؟');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#resendModal<?php echo $notification['id']; ?>">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Notification Details Modal -->
                        <div class="modal fade" id="notificationModal<?php echo $notification['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">تفاصيل الإشعار #<?php echo $notification['id']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>العنوان:</strong> <?php echo htmlspecialchars($notification['title']); ?></p>
                                        <p><strong>النوع:</strong> <span class="badge <?php echo $type_badge_class; ?>"><?php echo $type_badge_text; ?></span></p>
                                        <p><strong>الأيقونة:</strong> <i class="<?php echo htmlspecialchars($notification['icon']); ?>"></i> <?php echo htmlspecialchars($notification['icon']); ?></p>
                                        <p><strong>المرسل إلى:</strong> 
                                            <?php if ($notification['user_id']): ?>
                                                <?php echo htmlspecialchars($notification['username']); ?>
                                            <?php else: ?>
                                                <span class="badge bg-info">إشعار عام</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>التاريخ:</strong> <?php echo date('Y-m-d H:i', strtotime($notification['created_at'])); ?></p>
                                        <?php if (!empty($notification['action_url'])): ?>
                                        <p><strong>رابط الإجراء:</strong> <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" target="_blank"><?php echo htmlspecialchars($notification['action_url']); ?></a></p>
                                        <?php endif; ?>
                                        <hr>
                                        <div class="notification-content p-3 bg-light rounded">
                                            <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resend Notification Modal -->
                        <div class="modal fade" id="resendModal<?php echo $notification['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">إعادة إرسال الإشعار</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post" action="">
                                            <input type="hidden" name="resend_notification" value="1">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                            
                                            <div class="mb-3">
                                                <label for="resend_target_users" class="form-label">إرسال إلى</label>
                                                <select class="form-select" id="resend_target_users" name="target_users" required>
                                                    <option value="all">جميع المستخدمين</option>
                                                    <option value="specific">مستخدم محدد</option>
                                                    <option value="global">إشعار عام</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3 resend-specific-user-container" style="display: none;">
                                                <label for="resend_user_search" class="form-label">البحث عن مستخدم</label>
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" id="resend_user_search" placeholder="ابحث باسم المستخدم أو البريد الإلكتروني">
                                                    <button class="btn btn-outline-secondary" type="button" id="resend_search_user_btn">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                                <div id="resend_user_search_results" class="list-group mb-2" style="display:none; max-height: 200px; overflow-y: auto;"></div>
                                                
                                                <select class="form-select" id="resend_user_id" name="user_id">
                                                    <option value="">-- اختر مستخدم --</option>
                                                </select>
                                            </div>
                                            
                                            <div class="alert alert-info">
                                                سيتم إرسال الإشعار بنفس المحتوى والعنوان.
                                            </div>
                                            
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary">إرسال</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0">لا توجد إشعارات حتى الآن.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show/hide specific user selection based on target users selection
    $('#target_users').on('change', function() {
        if ($(this).val() === 'specific') {
            $('#specific_user_container').show();
        } else {
            $('#specific_user_container').hide();
        }
    });
    
    // Show/hide specific user for resend
    $('[id^=resend_target_users]').on('change', function() {
        const modalId = $(this).closest('.modal').attr('id');
        if ($(this).val() === 'specific') {
            $('#' + modalId + ' .resend-specific-user-container').show();
        } else {
            $('#' + modalId + ' .resend-specific-user-container').hide();
        }
    });
    
    // User search functionality
    $('#search_user_btn, [id^=resend_search_user_btn]').on('click', function() {
        const isResend = $(this).attr('id').startsWith('resend');
        const inputId = isResend ? 'resend_user_search' : 'user_search';
        const resultsId = isResend ? 'resend_user_search_results' : 'user_search_results';
        const selectId = isResend ? 'resend_user_id' : 'user_id';
        
        const username = $('#' + inputId).val();
        if (username.length >= 2) {
            $.ajax({
                url: 'admin/search_user.php',
                method: 'POST',
                data: { username: username },
                dataType: 'json',
                success: function(response) {
                    let results = '';
                    if (response.length > 0) {
                        response.forEach(function(user) {
                            results += `<a href="#" class="list-group-item list-group-item-action user-result" 
                                        data-id="${user.id}" 
                                        data-username="${user.username}" 
                                        data-email="${user.email}" 
                                        data-target="${selectId}">
                                        ${user.username} - ${user.email}
                                    </a>`;
                        });
                    } else {
                        results = '<div class="list-group-item">لا توجد نتائج</div>';
                    }
                    $('#' + resultsId).html(results).show();
                },
                error: function(xhr, status, error) {
                    console.error("Error searching for users:", error);
                    $('#' + resultsId).html('<div class="list-group-item text-danger">حدث خطأ أثناء البحث</div>').show();
                }
            });
        }
    });
    
    // Handle user selection
    $(document).on('click', '.user-result', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const username = $(this).data('username');
        const email = $(this).data('email');
        const targetSelect = $(this).data('target');
        
        // Clear existing options
        $('#' + targetSelect).empty();
        
        // Add the selected user
        $('#' + targetSelect).append(`<option value="${userId}" selected>${username} (${email})</option>`);
        
        // Hide results
        $(this).closest('.list-group').hide();
    });
    
    // Handle input on search fields to show/clear results
    $('#user_search, [id^=resend_user_search]').on('input', function() {
        const isResend = $(this).attr('id').startsWith('resend');
        const resultsId = isResend ? 'resend_user_search_results' : 'user_search_results';
        
        if ($(this).val().length < 2) {
            $('#' + resultsId).hide();
        }
    });
    
    // Allow pressing Enter to search
    $('#user_search, [id^=resend_user_search]').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const isResend = $(this).attr('id').startsWith('resend');
            const buttonId = isResend ? 'resend_search_user_btn' : 'search_user_btn';
            $('#' + buttonId).click();
        }
    });
    
    // Auto change icon based on notification type
    $('#notification_type').on('change', function() {
        const type = $(this).val();
        switch(type) {
            case 'order':
                $('#icon').val('fas fa-shopping-cart');
                break;
            case 'payment':
                $('#icon').val('fas fa-wallet');
                break;
            case 'system':
                $('#icon').val('fas fa-cog');
                break;
            case 'promotion':
                $('#icon').val('fas fa-percent');
                break;
            default:
                $('#icon').val('fas fa-bell');
        }
    });
});
</script>