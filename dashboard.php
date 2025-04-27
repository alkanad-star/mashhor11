<?php
// dashboard.php
session_start();
$page_title = "لوحة التحكم - متجر مشهور";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'config/db.php';
include_once 'referral_functions.php'; // Include referral functions

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user transactions
$transactions_query = "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($transactions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result();

// Get user orders
$orders_query = "SELECT o.*, s.name as service_name FROM orders o 
                JOIN services s ON o.service_id = s.id 
                WHERE o.user_id = ? 
                ORDER BY o.created_at DESC LIMIT 5";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

// Get referral statistics
$referral_stats = getUserReferralStats($user_id);

include 'header.php';
?>

<main>
    <section class="dashboard-section py-5">
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
                                <a href="dashboard.php" class="list-group-item list-group-item-action active">
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
                                <a href="support.php" class="list-group-item list-group-item-action">
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
                    <!-- Dashboard Content -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">معلومات الحساب</h4>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="dashboard-stat bg-primary bg-gradient">
                                        <div class="stat-icon">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h3 class="stat-value"><?php echo number_format($user['balance'], 2); ?> $</h3>
                                            <p class="stat-label">الرصيد المتاح</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="dashboard-stat bg-success bg-gradient">
                                        <div class="stat-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h3 class="stat-value"><?php echo number_format($user['spent'], 2); ?> $</h3>
                                            <p class="stat-label">المبلغ المستخدم</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="dashboard-stat bg-info bg-gradient">
                                        <div class="stat-icon">
                                            <i class="fas fa-sync-alt"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h3 class="stat-value"><?php echo number_format($user['in_use'], 2); ?> $</h3>
                                            <p class="stat-label">الرصيد قيد الاستخدام</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">إجراءات سريعة</h4>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <a href="add-order.php" class="quick-action-card">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-plus-circle"></i>
                                        </div>
                                        <div class="quick-action-text">
                                            <h5>طلب جديد</h5>
                                            <p>إضافة طلب خدمة جديد</p>
                                        </div>
                                    </a>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <a href="balance" class="quick-action-card">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <div class="quick-action-text">
                                            <h5>شحن الرصيد</h5>
                                            <p>إضافة رصيد لحسابك</p>
                                        </div>
                                    </a>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <a href="support.php" class="quick-action-card">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-headset"></i>
                                        </div>
                                        <div class="quick-action-text">
                                            <h5>الدعم الفني</h5>
                                            <p>تواصل مع فريق الدعم</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Orders -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">آخر الطلبات</h4>
                                <a href="orders.php" class="btn btn-sm btn-primary">عرض الكل</a>
                            </div>
                            
                            <?php if ($orders->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>الخدمة</th>
                                            <th>الكمية</th>
                                            <th>المبلغ</th>
                                            <th>الحالة</th>
                                            <th>التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                            <td><?php echo $order['quantity']; ?></td>
                                            <td><?php echo number_format($order['amount'], 2); ?> $</td>
                                            <td>
                                                <?php 
                                                $status_class = '';
                                                $status_text = '';
                                                
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        $status_class = 'bg-warning';
                                                        $status_text = 'قيد الانتظار';
                                                        break;
                                                    case 'processing':
                                                        $status_class = 'bg-info';
                                                        $status_text = 'قيد التنفيذ';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'bg-success';
                                                        $status_text = 'مكتمل';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'bg-danger';
                                                        $status_text = 'ملغي';
                                                        break;
                                                    case 'failed':
                                                        $status_class = 'bg-danger';
                                                        $status_text = 'فشل';
                                                        break;
                                                    case 'partial':
                                                        $status_class = 'bg-warning';
                                                        $status_text = 'جزئي';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <p class="mb-0">لم تقم بإجراء أي طلبات بعد.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Transactions -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">آخر المعاملات</h4>
                                <a href="transactions.php" class="btn btn-sm btn-primary">عرض الكل</a>
                            </div>
                            
                            <?php if ($transactions->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>النوع</th>
                                            <th>المبلغ</th>
                                            <th>الحالة</th>
                                            <th>التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($transaction = $transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $transaction['id']; ?></td>
                                            <td>
                                                <?php 
                                                $type_class = '';
                                                $type_text = '';
                                                
                                                switch ($transaction['type']) {
                                                    case 'deposit':
                                                        $type_class = 'text-success';
                                                        $type_text = 'إيداع';
                                                        break;
                                                    case 'withdrawal':
                                                        $type_class = 'text-danger';
                                                        $type_text = 'سحب';
                                                        break;
                                                    case 'purchase':
                                                        $type_class = 'text-info';
                                                        $type_text = 'شراء';
                                                        break;
                                                    case 'refund':
                                                        $type_class = 'text-warning';
                                                        $type_text = 'استرداد';
                                                        break;
                                                }
                                                ?>
                                                <span class="<?php echo $type_class; ?>"><?php echo $type_text; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($transaction['type'] == 'deposit' || $transaction['type'] == 'refund'): ?>
                                                <span class="text-success">+<?php echo number_format($transaction['amount'], 2); ?> $</span>
                                                <?php else: ?>
                                                <span class="text-danger">-<?php echo number_format($transaction['amount'], 2); ?> $</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_class = '';
                                                $status_text = '';
                                                
                                                switch ($transaction['status']) {
                                                    case 'pending':
                                                        $status_class = 'bg-warning';
                                                        $status_text = 'قيد الانتظار';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'bg-success';
                                                        $status_text = 'مكتمل';
                                                        break;
                                                    case 'failed':
                                                        $status_class = 'bg-danger';
                                                        $status_text = 'فشل';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'bg-danger';
                                                        $status_text = 'ملغي';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td><?php echo date('Y-m-d', strtotime($transaction['created_at'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <p class="mb-0">لم تقم بإجراء أي معاملات بعد.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Referral System -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">نظام الإحالة</h4>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3">
                                    <div class="dashboard-stat bg-info bg-gradient">
                                        <div class="stat-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h3 class="stat-value"><?php echo number_format($referral_stats['total_referrals']); ?></h3>
                                            <p class="stat-label">عدد الإحالات</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="dashboard-stat bg-success bg-gradient">
                                        <div class="stat-icon">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h3 class="stat-value"><?php echo number_format($referral_stats['total_earnings'], 2); ?> $</h3>
                                            <p class="stat-label">إجمالي الأرباح</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="dashboard-stat bg-warning bg-gradient">
                                        <div class="stat-icon">
                                            <i class="fas fa-link"></i>
                                        </div>
                                        <div class="stat-content">
                                            <h3 class="stat-value">كود الإحالة</h3>
                                            <p class="stat-label" id="referral-code"><?php echo !empty($referral_stats['referral_code']) ? htmlspecialchars($referral_stats['referral_code']) : '...'; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">شارك الكود مع أصدقائك</h5>
                                        <p>احصل على مكافآت عندما يقوم أصدقاؤك بالتسجيل باستخدام كود الإحالة الخاص بك.</p>
                                        
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="referral-link" 
                                                value="<?php echo !empty($referral_stats['referral_code']) ? 
                                                htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . '/login.php?form=register&ref=' . $referral_stats['referral_code']) : 
                                                'Generating your referral code...'; ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" id="copy-referral-link" 
                                                <?php echo empty($referral_stats['referral_code']) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-copy"></i> نسخ الرابط
                                            </button>
                                        </div>
                                        <?php if (empty($referral_stats['referral_code'])): ?>
                                        <div class="alert alert-info">
                                            جاري إنشاء كود الإحالة الخاص بك. يرجى تحديث الصفحة خلال دقيقة.
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="text-center">
                                            <button class="btn btn-sm btn-outline-primary me-2" id="share-facebook">
                                                <i class="fab fa-facebook-f"></i> فيسبوك
                                            </button>
                                            <button class="btn btn-sm btn-outline-info me-2" id="share-twitter">
                                                <i class="fab fa-twitter"></i> تويتر
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" id="share-whatsapp">
                                                <i class="fab fa-whatsapp"></i> واتساب
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($referral_stats['referred_users'])): ?>
                            <div class="table-responsive">
                                <h5>المستخدمون المُحالون</h5>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>المستخدم</th>
                                            <th>تاريخ التسجيل</th>
                                            <th>عدد الطلبات</th>
                                            <th>أرباح الإحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($referral_stats['referred_users'] as $referred_user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($referred_user['username']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($referred_user['created_at'])); ?></td>
                                            <td><?php echo number_format($referred_user['order_count']); ?></td>
                                            <td><?php echo number_format($referred_user['rewards_generated'], 2); ?> $</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <p class="mb-0">لم تقم بإحالة أي مستخدمين بعد. شارك كود الإحالة الخاص بك للبدء في كسب المكافآت.</p>
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
    .dashboard-section {
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
    
    .dashboard-stat {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        border-radius: 10px;
        color: white;
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-left: 1rem;
    }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }
    
    .stat-label {
        font-size: 0.9rem;
        margin-bottom: 0;
        opacity: 0.9;
    }
    
    .quick-action-card {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid #eee;
        transition: all 0.3s ease;
        text-decoration: none;
        color: var(--text-color);
    }
    
    .quick-action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
    }
    
    .quick-action-icon {
        font-size: 2rem;
        color: var(--primary-color);
        margin-left: 1rem;
    }
    
    .quick-action-text h5 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.3rem;
    }
    
    .quick-action-text p {
        font-size: 0.85rem;
        margin-bottom: 0;
        color: #666;
    }
    
    .table th {
        font-weight: 600;
    }
    
    .table td, .table th {
        vertical-align: middle;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy referral link functionality
    document.getElementById('copy-referral-link').addEventListener('click', function() {
        var referralLink = document.getElementById('referral-link');
        referralLink.select();
        document.execCommand('copy');
        
        // Show copied message
        this.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-copy"></i> نسخ الرابط';
        }, 2000);
    });
    
    // Social sharing
    var referralLink = document.getElementById('referral-link').value;
    var shareText = 'انضم إلى متجر مشهور واحصل على خدمات التواصل الاجتماعي بأفضل الأسعار!';
    
    // Only set up sharing if we have a valid referral link
    if (referralLink && !referralLink.includes('Generating')) {
        document.getElementById('share-facebook').addEventListener('click', function() {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(referralLink), '_blank');
        });
        
        document.getElementById('share-twitter').addEventListener('click', function() {
            window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(shareText) + '&url=' + encodeURIComponent(referralLink), '_blank');
        });
        
        document.getElementById('share-whatsapp').addEventListener('click', function() {
            window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(shareText + ' ' + referralLink), '_blank');
        });
    } else {
        // Disable sharing buttons if no referral code yet
        document.getElementById('share-facebook').disabled = true;
        document.getElementById('share-twitter').disabled = true;
        document.getElementById('share-whatsapp').disabled = true;
    }
});
</script>

<?php include 'footer.php'; ?>