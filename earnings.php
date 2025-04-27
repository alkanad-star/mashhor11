<?php
// earnings.php
session_start();
$page_title = "اكسب معنا - متجر مشهور";

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

// Get referral statistics
$referral_stats = getUserReferralStats($user_id);

// Get referral settings
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

include 'header.php';
?>

<main>
    <section class="earnings-section py-5">
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
                                <a href="earnings.php" class="list-group-item list-group-item-action active">
                                    <i class="fas fa-hand-holding-usd me-2"></i> اكسب معنا
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
                    <!-- How to Earn Section -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">كيف تكسب معنا؟</h4>
                            
                            <div class="alert alert-primary">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="alert-heading">نظام الإحالة</h5>
                                        <p>فرصتك لزيادة أرباحك بدأت الآن! 🌟 ادعُ أصدقاءك للتسجيل عبر رابط الإحالة الخاص بك واستمتع بمكافآت مالية مباشرة مع كل طلب جديد يقومون به. والأجمل؟ سيحصل أصدقاؤك على خصم 7% على أول ثلاثة طلبات لهم! لا تفوت الفرصة، وابدأ بمشاركة رابطك اليوم!</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="how-to-earn mt-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="earning-method p-3 border rounded h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="earning-icon me-3">
                                                    <i class="fas fa-user-plus text-primary fa-2x"></i>
                                                </div>
                                                <h5 class="mb-0">مكافأة التسجيل</h5>
                                            </div>
                                            <p>سيحصل صديقك على خصم 7% على أول ثلاثة طلبات من متجرنا</p>
                                            <div class="bg-light p-2 rounded text-center">
                                                <strong>الدعوة → التسجيل → مكافأة فورية!</strong>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="earning-method p-3 border rounded h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="earning-icon me-3">
                                                    <i class="fas fa-shopping-cart text-success fa-2x"></i>
                                                </div>
                                                <h5 class="mb-0">عمولة الطلبات</h5>
                                            </div>
                                            <p>احصل على <strong><?php echo number_format($settings['order_reward_percentage'], 2); ?>%</strong> من قيمة كل طلب يقوم به الأصدقاء الذين دعوتهم (للطلبات بقيمة $<?php echo number_format($settings['min_order_amount'], 2); ?> أو أكثر).</p>
                                            <div class="bg-light p-2 rounded text-center">
                                                <strong>ربح مستمر مع كل طلب!</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h5>كيفية البدء:</h5>
                                    <ol class="list-group list-group-numbered mt-3">
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto text-end w-100">
                                                <div class="fw-bold">انسخ رابط الإحالة الخاص بك</div>
                                                ستجده في الأسفل، يمكنك نسخه والاحتفاظ به
                                            </div>
                                            <span class="badge bg-primary rounded-pill">1</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto text-end w-100">
                                                <div class="fw-bold">شارك الرابط مع أصدقائك</div>
                                                عبر وسائل التواصل الاجتماعي، واتساب، الايميل، أو أي وسيلة أخرى
                                            </div>
                                            <span class="badge bg-primary rounded-pill">2</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto text-end w-100">
                                                <div class="fw-bold">اكسب المال</div>
                                                سيتم إضافة المكافآت تلقائياً إلى رصيدك  عند قيام صديقك بطلب اي خدمة من خدماتنا
                                            </div>
                                            <span class="badge bg-primary rounded-pill">3</span>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Referral Stats -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">إحصائيات الإحالة</h4>
                            
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
                        </div>
                    </div>
                    
                    <!-- Share Referral Link -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">شارك رابط الإحالة الخاص بك</h4>
                            
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
                        </div>
                    </div>
                    
                    <!-- Referred Users -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title mb-4">المستخدمون المُحالون</h4>
                            
                            <?php if (!empty($referral_stats['referred_users'])): ?>
                            <div class="table-responsive">
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
    .earnings-section {
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
    
    .earning-method {
        transition: all 0.3s ease;
    }
    
    .earning-method:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .earning-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
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