<?php
// admin/referral_settings.php
// Include this file in admin.php as a new section

// Process settings update
if (isset($_POST['update_referral_settings'])) {
    $signup_reward = filter_input(INPUT_POST, 'signup_reward', FILTER_VALIDATE_FLOAT);
    $order_reward_percentage = filter_input(INPUT_POST, 'order_reward_percentage', FILTER_VALIDATE_FLOAT);
    $min_order_amount = filter_input(INPUT_POST, 'min_order_amount', FILTER_VALIDATE_FLOAT);
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    
    // Check if referral_settings table exists
    $check_table = $conn->query("SHOW TABLES LIKE 'referral_settings'");
    if ($check_table->num_rows == 0) {
        // Create table if it doesn't exist
        $conn->query("
            CREATE TABLE referral_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                signup_reward DECIMAL(10,2) DEFAULT 5.00,
                order_reward_percentage DECIMAL(5,2) DEFAULT 5.00,
                min_order_amount DECIMAL(10,2) DEFAULT 10.00,
                enabled BOOLEAN DEFAULT TRUE,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default settings
        $conn->query("
            INSERT INTO referral_settings (signup_reward, order_reward_percentage, min_order_amount, enabled)
            VALUES (5.00, 5.00, 10.00, 1)
        ");
    }
    
    // Update settings
    $update_query = "
        UPDATE referral_settings 
        SET signup_reward = ?, 
            order_reward_percentage = ?, 
            min_order_amount = ?, 
            enabled = ?
        WHERE id = 1
    ";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("dddi", $signup_reward, $order_reward_percentage, $min_order_amount, $enabled);
    
    if ($stmt->execute()) {
        $success_message = "تم تحديث إعدادات نظام الإحالة بنجاح.";
    } else {
        $error_message = "حدث خطأ أثناء تحديث إعدادات نظام الإحالة: " . $conn->error;
    }
}

// Get current settings
$settings_query = "SELECT * FROM referral_settings WHERE id = 1";
$settings_result = $conn->query($settings_query);
$settings = [];

if ($settings_result && $settings_result->num_rows > 0) {
    $settings = $settings_result->fetch_assoc();
} else {
    // Default settings if not found
    $settings = [
        'signup_reward' => 5.00,
        'order_reward_percentage' => 5.00,
        'min_order_amount' => 10.00,
        'enabled' => 1
    ];
}

// Get referral statistics
$stats_query = "
    SELECT 
        COUNT(DISTINCT referred_id) as total_referrals,
        SUM(reward_amount) as total_rewards_paid,
        COUNT(CASE WHEN reward_type = 'signup' THEN 1 END) as signup_referrals,
        COUNT(CASE WHEN reward_type = 'order' THEN 1 END) as order_referrals
    FROM referrals
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get top referrers
$top_referrers_query = "
    SELECT 
        u.id, 
        u.username, 
        u.full_name, 
        u.total_referral_earnings, 
        COUNT(r.id) as referral_count
    FROM users u
    JOIN referrals r ON u.id = r.referrer_id
    GROUP BY u.id
    ORDER BY referral_count DESC
    LIMIT 10
";
$top_referrers = $conn->query($top_referrers_query);

// Get recent referrals
$recent_referrals_query = "
    SELECT 
        r.id,
        r.created_at,
        r.status,
        r.reward_type,
        r.reward_amount,
        u1.username as referrer_username,
        u2.username as referred_username
    FROM referrals r
    JOIN users u1 ON r.referrer_id = u1.id
    JOIN users u2 ON r.referred_id = u2.id
    ORDER BY r.created_at DESC
    LIMIT 10
";
$recent_referrals = $conn->query($recent_referrals_query);
?>

<!-- Referral System Settings -->
<div class="referral-settings-section">
    <h1 class="mb-4">إعدادات نظام الإحالة</h1>
    
    <!-- Referral Stats -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title mb-4">إحصائيات نظام الإحالة</h4>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <div class="stats-card-icon" style="background-color: #4CAF50;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-card-value"><?php echo number_format($stats['total_referrals'] ?? 0); ?></div>
                        <div class="stats-card-label">إجمالي الإحالات</div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <div class="stats-card-icon" style="background-color: #2196F3;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stats-card-value">$<?php echo number_format($stats['total_rewards_paid'] ?? 0, 2); ?></div>
                        <div class="stats-card-label">المكافآت المدفوعة</div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <div class="stats-card-icon" style="background-color: #FF9800;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stats-card-value"><?php echo number_format($stats['signup_referrals'] ?? 0); ?></div>
                        <div class="stats-card-label">إحالات التسجيل</div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <div class="stats-card-icon" style="background-color: #9C27B0;">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stats-card-value"><?php echo number_format($stats['order_referrals'] ?? 0); ?></div>
                        <div class="stats-card-label">إحالات الطلبات</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Referral Settings -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">إعدادات المكافآت</h5>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="signup_reward" class="form-label">مكافأة تسجيل مستخدم جديد</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="signup_reward" name="signup_reward" value="<?php echo $settings['signup_reward'] ?? 5.00; ?>" step="0.01" min="0" required>
                            <span class="input-group-text">$</span>
                        </div>
                        <div class="form-text">المبلغ الذي سيحصل عليه المستخدم عند تسجيل مستخدم جديد من خلال رابط الإحالة الخاص به.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="order_reward_percentage" class="form-label">نسبة مكافأة الطلبات</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="order_reward_percentage" name="order_reward_percentage" value="<?php echo $settings['order_reward_percentage'] ?? 5.00; ?>" step="0.01" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">النسبة التي سيحصل عليها المستخدم من قيمة طلبات المستخدمين الذين قام بدعوتهم.</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="min_order_amount" class="form-label">الحد الأدنى لقيمة الطلب</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" value="<?php echo $settings['min_order_amount'] ?? 10.00; ?>" step="0.01" min="0" required>
                            <span class="input-group-text">$</span>
                        </div>
                        <div class="form-text">الحد الأدنى لقيمة الطلب الذي سيتم احتساب مكافأة عليه.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="enabled" name="enabled" <?php echo ($settings['enabled'] ?? 1) == 1 ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enabled">تفعيل نظام الإحالة</label>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="update_referral_settings" class="btn btn-primary">حفظ الإعدادات</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Top Referrers -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">أفضل المستخدمين في الإحالات</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>الاسم الكامل</th>
                            <th>عدد الإحالات</th>
                            <th>إجمالي الأرباح</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($top_referrers && $top_referrers->num_rows > 0): ?>
                            <?php while ($referrer = $top_referrers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($referrer['username']); ?></td>
                                <td><?php echo htmlspecialchars($referrer['full_name']); ?></td>
                                <td><?php echo number_format($referrer['referral_count']); ?></td>
                                <td>$<?php echo number_format($referrer['total_referral_earnings'], 2); ?></td>
                                <td>
                                    <a href="admin.php?section=users&action=view&id=<?php echo $referrer['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">لا توجد بيانات متاحة</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Referrals -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">أحدث الإحالات</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>المُحيل</th>
                            <th>المُحال</th>
                            <th>النوع</th>
                            <th>المكافأة</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_referrals && $recent_referrals->num_rows > 0): ?>
                            <?php while ($referral = $recent_referrals->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($referral['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($referral['referrer_username']); ?></td>
                                <td><?php echo htmlspecialchars($referral['referred_username']); ?></td>
                                <td>
                                    <?php if ($referral['reward_type'] == 'signup'): ?>
                                        <span class="badge bg-info">تسجيل</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">طلب</span>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($referral['reward_amount'], 2); ?></td>
                                <td>
                                    <?php if ($referral['status'] == 'pending'): ?>
                                        <span class="badge bg-warning">قيد الانتظار</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">مكتمل</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">لا توجد بيانات متاحة</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>