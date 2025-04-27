<?php
// home.php - Main homepage after login
session_start();
$page_title = "الصفحة الرئيسية - متجر مشهور";

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

// Get all categories
$categories_query = "SELECT * FROM service_categories ORDER BY display_order";
$categories = $conn->query($categories_query);

// Get popular services
$popular_services_query = "SELECT s.*, c.name as category_name 
                          FROM services s 
                          JOIN service_categories c ON s.category_id = c.id 
                          WHERE s.is_popular = 1 
                          ORDER BY s.display_order, s.id DESC
                          LIMIT 6";
$popular_services = $conn->query($popular_services_query);

// Get recent orders
$recent_orders_query = "SELECT o.*, s.name as service_name, s.category_id, c.name as category_name 
                      FROM orders o 
                      JOIN services s ON o.service_id = s.id 
                      JOIN service_categories c ON s.category_id = c.id 
                      WHERE o.user_id = ? 
                      ORDER BY o.created_at DESC 
                      LIMIT 5";
$stmt = $conn->prepare($recent_orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_orders = $stmt->get_result();

// Get unread notifications count
$notification_query = "SELECT COUNT(*) as count FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0";
$stmt = $conn->prepare($notification_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$unread_notifications = $row['count'];

// Get referral statistics
$referral_stats = getUserReferralStats($user_id);

include 'header.php';
?>

<main>
    <section class="home-section py-4">
        <div class="container">
            <!-- Welcome Banner with balance -->
            <div class="welcome-banner card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="welcome-text">
                                <h2 class="mb-2">مرحباً <?php echo htmlspecialchars($_SESSION['full_name']); ?> 👋</h2>
                                <p class="text-muted mb-0">مرحباً بك في متجر مشهور، منصتك الشاملة لخدمات التواصل الاجتماعي.</p>
                            </div>
                        </div>
                        <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
                            <div class="balance-info">
                                <span class="balance-label">رصيدك الحالي:</span>
                                <span class="balance-amount"><?php echo number_format($user['balance'], 2); ?> $</span>
                                <a href="balance.php" class="btn btn-primary ms-3">
                                    <i class="fas fa-plus-circle me-1"></i> شحن الرصيد
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Main Content Column -->
                <div class="col-lg-8">
                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-sm-6 col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-card-body">
                                    <div class="stat-card-icon bg-primary">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div class="stat-card-info">
                                        <h5><?php echo number_format($user['balance'], 2); ?> $</h5>
                                        <p>الرصيد المتاح</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-card-body">
                                    <div class="stat-card-icon bg-success">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="stat-card-info">
                                        <h5><?php echo number_format($referral_stats['total_earnings'], 2); ?> $</h5>
                                        <p>ارباحي</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 mb-3">
                            <div class="stat-card">
                                <div class="stat-card-body">
                                    <div class="stat-card-icon bg-info">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-card-info">
                                        <h5><?php echo number_format($referral_stats['total_referrals']); ?></h5>
                                        <p>عدد الإحالات</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">إجراءات سريعة</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 col-md-3 mb-3">
                                    <a href="add-order.php" class="quick-action">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-plus-circle"></i>
                                        </div>
                                        <span>طلب جديد</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <a href="orders.php" class="quick-action">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <span>طلباتي</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <a href="balance.php" class="quick-action">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <span>شحن الرصيد</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <a href="support.php" class="quick-action">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-headset"></i>
                                        </div>
                                        <span>الدعم الفني</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Service Categories -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">فئات الخدمات</h5>
                            <a href="services.php" class="btn btn-sm btn-primary">عرض الكل</a>
                        </div>
                        <div class="card-body">
                            <div class="categories-grid">
                                <?php if ($categories && $categories->num_rows > 0): ?>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                    <a href="services.php?category=<?php echo htmlspecialchars($category['slug']); ?>" class="category-item">
                                        <div class="category-icon">
                                            <?php if (!empty($category['icon'])): ?>
                                            <img src="<?php echo htmlspecialchars($category['icon']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                            <?php else: ?>
                                            <i class="fas fa-folder"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                                    </a>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">لا توجد فئات متاحة حالياً.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Popular Services -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">الخدمات الشائعة</h5>
                            <a href="services.php?popular=1" class="btn btn-sm btn-primary">عرض الكل</a>
                        </div>
                        <div class="card-body">
                            <?php if ($popular_services && $popular_services->num_rows > 0): ?>
                            <div class="popular-services">
                                <?php while ($service = $popular_services->fetch_assoc()): ?>
                                <div class="service-card">
                                    <div class="service-info">
                                        <h6 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h6>
                                        <p class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></p>
                                        <div class="service-meta">
                                            <span class="service-price">$<?php echo number_format($service['price'], 3); ?> / <?php echo number_format($service['min_quantity']); ?></span>
                                            <?php 
                                            $quality_class = '';
                                            $quality_text = '';
                                            
                                            switch ($service['quality']) {
                                                case 'low':
                                                    $quality_class = 'bg-warning';
                                                    $quality_text = 'عادية';
                                                    break;
                                                case 'medium':
                                                    $quality_class = 'bg-info';
                                                    $quality_text = 'متوسطة';
                                                    break;
                                                case 'high':
                                                    $quality_class = 'bg-success';
                                                    $quality_text = 'عالية';
                                                    break;
                                                case 'premium':
                                                    $quality_class = 'bg-primary';
                                                    $quality_text = 'ممتازة';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $quality_class; ?>"><?php echo $quality_text; ?></span>
                                        </div>
                                    </div>
                                    <div class="service-action">
                                        <a href="add-order.php?service=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary">طلب</a>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info mb-0">لا توجد خدمات شائعة متاحة حالياً.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Column -->
                <div class="col-lg-4">
                    <!-- User Profile Card -->
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <div class="user-profile-img mb-3">
                                <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'images/default-profile.png'); ?>" alt="Profile" class="rounded-circle">
                            </div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h5>
                            <p class="text-muted">@<?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            <div class="user-actions">
                                <a href="profile.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-user-edit me-1"></i> تعديل الملف
                                </a>
                                <a href="settings.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-cog me-1"></i> الإعدادات
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notifications Card -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">التنبيهات</h5>
                            <a href="notifications.php" class="btn btn-sm btn-primary">عرض الكل</a>
                        </div>
                        <div class="card-body">
                            <?php if ($unread_notifications > 0): ?>
                            <div class="notification-alert">
                                <i class="fas fa-bell"></i>
                                <span>لديك <?php echo $unread_notifications; ?> تنبيهات جديدة</span>
                                <a href="notifications.php" class="stretched-link"></a>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                <p class="mb-0">ليس لديك تنبيهات جديدة</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Referral Stats Card -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">برنامج الربح</h5>
                            <a href="earnings.php" class="btn btn-sm btn-primary">عرض التفاصيل</a>
                        </div>
                        <div class="card-body">
                            <div class="referral-stat mb-3">
                                <div class="referral-stat-label">رمز الإحالة:</div>
                                <div class="referral-stat-value">
                                    <div class="d-flex align-items-center">
                                        <span id="referral-code"><?php echo htmlspecialchars($referral_stats['referral_code']); ?></span>
                                        <button class="btn btn-sm btn-outline-primary ms-2" id="copy-code">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="referral-stat mb-3">
                                <div class="referral-stat-label">الإحالات:</div>
                                <div class="referral-stat-value"><?php echo number_format($referral_stats['total_referrals']); ?></div>
                            </div>
                            <div class="referral-stat">
                                <div class="referral-stat-label">إجمالي الأرباح:</div>
                                <div class="referral-stat-value"><?php echo number_format($referral_stats['total_earnings'], 2); ?> $</div>
                            </div>
                            
                            <div class="mt-3">
                                <button class="btn btn-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#shareReferralModal">
                                    <i class="fas fa-share-alt me-1"></i> مشاركة الرمز
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Orders Card -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">آخر الطلبات</h5>
                            <a href="orders.php" class="btn btn-sm btn-primary">عرض الكل</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                            <div class="recent-orders">
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <div class="recent-order-item">
                                    <div class="recent-order-info">
                                        <div class="recent-order-id">#<?php echo $order['id']; ?></div>
                                        <div class="recent-order-title"><?php echo htmlspecialchars($order['service_name']); ?></div>
                                        <div class="recent-order-date"><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></div>
                                    </div>
                                    <div class="recent-order-status">
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
                                                $status_class = 'bg-secondary';
                                                $status_text = 'ملغي';
                                                break;
                                            case 'failed':
                                                $status_class = 'bg-danger';
                                                $status_text = 'فشل';
                                                break;
                                            case 'partial':
                                                $status_class = 'bg-primary';
                                                $status_text = 'جزئي';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                                <p class="mb-0">لم تقم بإجراء طلبات بعد</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Share Referral Modal -->
<div class="modal fade" id="shareReferralModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">مشاركة رمز الإحالة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">رمز الإحالة الخاص بك</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($referral_stats['referral_code']); ?>" id="referral-code-input" readonly>
                        <button class="btn btn-outline-primary" type="button" id="copy-referral-code">
                            <i class="fas fa-copy"></i> نسخ
                        </button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">رابط الإحالة</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/login.php?form=register&ref=' . htmlspecialchars($referral_stats['referral_code']); ?>" id="referral-link-input" readonly>
                        <button class="btn btn-outline-primary" type="button" id="copy-referral-link">
                            <i class="fas fa-copy"></i> نسخ
                        </button>
                    </div>
                </div>
                
                <div class="mt-4">
                    <p class="text-center">أو شارك مباشرة عبر</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/login.php?form=register&ref=' . $referral_stats['referral_code']); ?>" class="btn btn-primary" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('انضم إلى متجر مشهور واحصل على خدمات التواصل الاجتماعي بأفضل الأسعار!'); ?>&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/login.php?form=register&ref=' . $referral_stats['referral_code']); ?>" class="btn btn-info" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('انضم إلى متجر مشهور واحصل على خدمات التواصل الاجتماعي بأفضل الأسعار! ' . 'https://' . $_SERVER['HTTP_HOST'] . '/login.php?form=register&ref=' . $referral_stats['referral_code']); ?>" class="btn btn-success" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://t.me/share/url?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/login.php?form=register&ref=' . $referral_stats['referral_code']); ?>&text=<?php echo urlencode('انضم إلى متجر مشهور واحصل على خدمات التواصل الاجتماعي بأفضل الأسعار!'); ?>" class="btn btn-primary" target="_blank">
                            <i class="fab fa-telegram-plane"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Home Page Styles */
.home-section {
    background-color: #f8f9fa;
}

/* Welcome Banner Styles */
.welcome-banner {
    border: none;
    border-radius: 15px;
    background: linear-gradient(to right, #ffffff, #f0f7ff);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.welcome-text h2 {
    font-weight: 700;
    color: var(--text-color);
}

.balance-info {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.balance-label {
    font-size: 1rem;
    color: #6c757d;
    margin-right: 8px;
}

.balance-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Stat Cards */
.stat-card {
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    height: 100%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stat-card-body {
    display: flex;
    align-items: center;
    padding: 20px;
}

.stat-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 15px;
    color: white;
    font-size: 1.25rem;
}

.stat-card-info h5 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: var(--text-color);
}

.stat-card-info p {
    font-size: 0.85rem;
    margin-bottom: 0;
    color: #6c757d;
}

/* Quick Actions */
.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    color: var(--text-color);
    text-decoration: none;
    padding: 15px;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.quick-action:hover {
    background-color: #f8f9fa;
    transform: translateY(-5px);
    color: var(--primary-color);
}

.quick-action-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #f1f7ff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-size: 1.25rem;
    color: var(--primary-color);
    transition: all 0.3s ease;
}

.quick-action:hover .quick-action-icon {
    background-color: var(--primary-color);
    color: #fff;
}

/* Category Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 15px;
}

.category-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 15px 10px;
    border-radius: 10px;
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    text-decoration: none;
    color: var(--text-color);
}

.category-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    color: var(--primary-color);
}

.category-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
}

.category-icon img {
    max-width: 100%;
    max-height: 100%;
}

.category-name {
    font-size: 0.85rem;
    font-weight: 600;
}

/* Popular Services */
.popular-services {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.service-card {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    padding: 15px;
    display: flex;
    justify-content: space-between;
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.service-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-color);
}

.service-category {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 8px;
}

.service-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.service-price {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--primary-color);
}

.service-action {
    display: flex;
    align-items: center;
}

/* User Profile Card */
.user-profile-img img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 3px solid #f0f7ff;
}

.user-actions {
    margin-top: 15px;
}

/* Notification Alert */
.notification-alert {
    display: flex;
    align-items: center;
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    position: relative;
}

.notification-alert i {
    font-size: 1.25rem;
    color: var(--primary-color);
    margin-left: 10px;
}

/* Referral Stats */
.referral-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.referral-stat-label {
    color: #6c757d;
    font-size: 0.9rem;
}

.referral-stat-value {
    font-weight: 600;
    color: var(--text-color);
}

/* Recent Orders */
.recent-orders {
    max-height: 300px;
    overflow-y: auto;
}

.recent-order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.recent-order-item:last-child {
    border-bottom: none;
}

.recent-order-id {
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 5px;
}

.recent-order-title {
    font-size: 0.85rem;
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}

.recent-order-date {
    font-size: 0.75rem;
    color: #6c757d;
}

/* Card Styles */
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid #f0f0f0;
    padding: 15px 20px;
}

.card-header h5 {
    font-weight: 600;
    color: var(--text-color);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .balance-info {
        justify-content: flex-start;
        margin-top: 15px;
    }
    
    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
    
    .popular-services {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy referral code functionality
    document.getElementById('copy-code').addEventListener('click', function() {
        const referralCode = document.getElementById('referral-code');
        const tempInput = document.createElement('input');
        tempInput.value = referralCode.textContent;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        
        // Show feedback
        this.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-copy"></i>';
        }, 2000);
    });
    
    // Copy referral code in modal
    document.getElementById('copy-referral-code').addEventListener('click', function() {
        const referralCodeInput = document.getElementById('referral-code-input');
        referralCodeInput.select();
        document.execCommand('copy');
        
        // Show feedback
        this.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-copy"></i> نسخ';
        }, 2000);
    });
    
    // Copy referral link in modal
    document.getElementById('copy-referral-link').addEventListener('click', function() {
        const referralLinkInput = document.getElementById('referral-link-input');
        referralLinkInput.select();
        document.execCommand('copy');
        
        // Show feedback
        this.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-copy"></i> نسخ';
        }, 2000);
    });
});
</script>

<?php include 'footer.php'; ?>