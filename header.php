<?php
// header.php
session_start();
$is_logged_in = isset($_SESSION['user_id']);

// Get user profile image if logged in
$profile_image = 'images/default-profile.png';
$unread_notifications = 0;

if ($is_logged_in) {
    include_once 'config/db.php';
    $user_id = $_SESSION['user_id'];
    
    // Get user profile image
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $profile_image = $row['profile_image'] ?? 'images/default-profile.png';
    }
    
    // Get unread notification count
    $notification_query = "SELECT COUNT(*) as count FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0";
    $stmt = $conn->prepare($notification_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $unread_notifications = $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? $page_title : 'متجر مشهور - أفضل موقع زيادة متابعين'; ?></title>
    <meta name="description" content="متجر مشهور - أفضل موقع لزيادة المتابعين والتفاعل على مواقع التواصل الاجتماعي">
    <link rel="icon" type="image/png" href="/images/logo.png" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --primary-color: #2196F3;
            --secondary-color: #F44336;
            --background-color: #f8f9fa;
            --text-color: #333;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding-top: 60px; /* reduced space for fixed header */
        }

        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: .3rem .8rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1100;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            max-height: 40px; /* smaller logo */
            width: auto;
        }
        .brand-text {
            font-size: 1rem;   /* smaller text */
            font-weight: 700;
            color: var(--text-color);
            margin-right: .4rem;
            white-space: nowrap;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            padding: .3rem .8rem !important; /* tighter */
            border-radius: 18px;
            transition: background .3s, color .3s;
            font-size: .9rem; /* slightly smaller */
        }
        .nav-link:hover {
            background-color: var(--primary-color);
            color: #fff !important;
        }
        .nav-link i {
            margin-left: .4rem;
            color: var(--primary-color);
        }
        .nav-link:hover i {
            color: #fff;
        }

        .btn-register {
            background-color: var(--primary-color);
            color: #fff !important;
            border: none;
            padding: .3rem .8rem;  /* tighter */
            border-radius: 18px;
            font-weight: 600;
            font-size: .9rem;
            transition: background .3s, transform .2s;
        }
        .btn-register:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .mobile-menu-toggle {
            font-size: 1.3rem;  /* smaller hamburger */
            color: var(--primary-color);
            border: none;
            background: none;
        }

        /* Sidebar menu */
        #aside-menu {
            position: fixed;
            top: 0;
            right: -240px;  /* narrower */
            width: 240px;
            height: 100%;
            background: #fff;
            box-shadow: -2px 0 6px rgba(0,0,0,0.1);
            transition: right .3s;
            z-index: 1050;
            display: flex;
            flex-direction: column;
        }
        #aside-menu.active { right: 0; }
        #body-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 1040;
        }
        #body-overlay.active { display: block; }

        .aside-header {
            padding: .8rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .aside-header img {
            max-height: 35px;  /* smaller */
        }

        .aside-scroll {
            flex: 1;
            overflow-y: auto;
            padding: .8rem;
        }
        .aside-nav-link {
            display: flex;
            align-items: center;
            padding: .5rem .8rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 6px;
            transition: background .3s, color .3s;
            margin-bottom: .4rem;
            font-size: .95rem;
            position: relative;
        }
        .aside-nav-link i {
            margin-left: .4rem;
            color: var(--primary-color);
            width: 18px;
            text-align: center;
        }
        .aside-nav-link:hover {
            background: var(--primary-color);
            color: #fff;
        }
        .aside-nav-link:hover i {
            color: #fff;
        }

        .social-links {
            padding: .8rem;
            text-align: center;
            border-top: 1px solid #eee;
        }
        .social-links a {
            display: inline-block;
            width: 32px;
            height: 32px;
            line-height: 32px;
            margin: 0 .3rem;
            border-radius: 50%;
            background: #f5f5f5;
            color: var(--primary-color);
            transition: background .3s, transform .2s;
            font-size: 1rem;
        }
        .social-links a:hover {
            background: var(--primary-color);
            color: #fff;
            transform: translateY(-2px);
        }

        .copyright {
            text-align: center;
            font-size: .7rem;
            color: #999;
            padding: .6rem 0;
        }

        /* Profile dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-color);
            font-weight: 600;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 18px;
            transition: background-color 0.3s;
        }

        .profile-btn:hover {
            background-color: #f1f1f1;
        }
        
        .profile-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .profile-menu {
            position: absolute;
            top: 100%;
            left: 0;
            width: 220px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
            margin-top: 8px;
            padding: 8px 0;
        }
        
        .profile-menu.show {
            display: block;
        }
        
        .profile-menu-item {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            color: var(--text-color);
            text-decoration: none;
            transition: background-color 0.3s;
            position: relative;
        }
        
        .profile-menu-item:hover {
            background-color: #f5f5f5;
        }
        
        .profile-menu-item i {
            margin-left: 12px;
            width: 16px;
            text-align: center;
            color: var(--text-color);
        }
        
        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            left: 5px;
            background-color: #F44336;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Bell icon with badge */
        .bell-container {
            position: relative;
            display: inline-block;
            margin-right: 15px;
        }
        
        .bell-icon {
            color: var(--primary-color);
            font-size: 1.3rem;
            cursor: pointer;
        }

        @media (max-width: 991px) {
            .navbar-collapse { display: none !important; }
        }
        @media (max-width: 576px) {
            .brand-text { font-size: .9rem; }
            .navbar-brand img { max-height: 35px; }
            /* hamburger on left, brand on right */
            .mobile-menu-toggle { position: absolute; left: 1rem; }
            .navbar-brand { margin-left: auto; margin-right: 1rem; }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" 
        integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" 
        crossorigin="anonymous"></script>
</head>
<body>
    <!-- Overlay -->
    <div id="body-overlay" onclick="toggleMobileMenu()"></div>

    <!-- Main Navbar -->
    <nav class="navbar fixed-top navbar-expand-lg">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="mobile-menu-toggle d-lg-none me-2" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand" href="/">
                    <img src="/images/logo.png" alt="متجر مشهور">
                    <span class="brand-text">متجر مشهور</span>
                </a>
            </div>

            <div class="navbar-collapse d-lg-flex justify-content-between">
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="/top"><i class="fas fa-bolt"></i> الأكثر مبيعاً</a></li>
                    <li class="nav-item"><a class="nav-link" href="/services/face"><i class="fab fa-facebook"></i> فيسبوك</a></li>
                    <li class="nav-item"><a class="nav-link" href="/services/insta"><i class="fab fa-instagram"></i> انستجرام</a></li>
                    <li class="nav-item"><a class="nav-link" href="/services/tiktok"><i class="fab fa-tiktok"></i> تيك توك</a></li>
                    <li class="nav-item"><a class="nav-link" href="/services/youtube"><i class="fab fa-youtube"></i> يوتيوب</a></li>
                    <li class="nav-item"><a class="nav-link" href="/services/x"><i class="fab fa-twitter"></i> تويتر</a></li>
                    <li class="nav-item"><a class="nav-link" href="/services/telegram"><i class="fab fa-telegram"></i> تيليجرام</a></li>
                    <li class="nav-item"><a class="nav-link" href="/services/threads"><i class="fas fa-at"></i> ثريدز</a></li>
                    <li class="nav-item"><a class="nav-link" href="/services/other"><i class="fas fa-ellipsis-h"></i> أخرى</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if ($is_logged_in): ?>
                    <!-- Notification bell -->
                    <div class="bell-container d-none d-lg-block">
                        <a href="/notifications.php" class="bell-icon">
                            <i class="fas fa-bell"></i>
                            <?php if ($unread_notifications > 0): ?>
                            <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <div class="profile-dropdown">
                        <button class="profile-btn" onclick="toggleProfileMenu()">
                            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-img">
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </button>
                        <div class="profile-menu" id="profileMenu">
                            <a href="/home" class="profile-menu-item">
                                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                            </a>
                            <a href="/orders" class="profile-menu-item">
                                <i class="fas fa-shopping-cart"></i> طلباتي
                            </a>
                            <a href="/profile" class="profile-menu-item">
                                <i class="fas fa-user-edit"></i> تعديل ملفي الشخصي
                            </a>
                            <a href="/balance" class="profile-menu-item">
                                <i class="fas fa-wallet"></i> أرصدتي
                            </a>
                            <a href="/earnings" class="profile-menu-item">
                                <i class="fas fa-hand-holding-usd"></i> اكسب معنا
                            </a>
                            <a href="/support" class="profile-menu-item">
                                <i class="fas fa-headset"></i> الدعم الفني
                            </a>
                            <a href="/notifications" class="profile-menu-item">
                                <i class="fas fa-bell"></i> التنبيهات
                                <?php if ($unread_notifications > 0): ?>
                                <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="https://t.me/mashhors" target="_blank" class="profile-menu-item">
                                <i class="fab fa-telegram"></i> قناتنا على تيليجرام
                            </a>
                            <a href="/logout" class="profile-menu-item">
                                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="/login" class="btn btn-register">ابدأ الآن</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar Menu -->
    <div id="aside-menu">
        <div class="aside-header">
            <img src="/images/logo.png" alt="متجر مشهور">
            <button class="btn" onclick="toggleMobileMenu()"><i class="fas fa-times"></i></button>
        </div>
        <div class="aside-scroll">
            <?php if ($is_logged_in): ?>
            <div class="text-center mb-3 p-3">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-img mb-2" style="width: 64px; height: 64px;">
                <h6 class="mb-0"><?php echo htmlspecialchars($_SESSION['username']); ?></h6>
            </div>
            <a href="/home" class="aside-nav-link"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a>
            <a href="/orders" class="aside-nav-link"><i class="fas fa-shopping-cart"></i> طلباتي</a>
            <a href="/profile" class="aside-nav-link"><i class="fas fa-user-edit"></i> تعديل ملفي الشخصي</a>
            <a href="/balance" class="aside-nav-link"><i class="fas fa-wallet"></i> أرصدتي</a>
            <a href="/earnings" class="aside-nav-link"><i class="fas fa-hand-holding-usd"></i> اكسب معنا</a>
            <a href="/support" class="aside-nav-link"><i class="fas fa-headset"></i> الدعم الفني</a>
            <a href="/notifications" class="aside-nav-link">
                <i class="fas fa-bell"></i> التنبيهات
                <?php if ($unread_notifications > 0): ?>
                <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                <?php endif; ?>
            </a>
            <a href="https://t.me/mashhors" target="_blank" class="aside-nav-link"><i class="fab fa-telegram"></i> قناتنا على تيليجرام</a>
            <div class="border-bottom my-3"></div>
            <?php endif; ?>
            <a href="/top" class="aside-nav-link"><i class="fas fa-bolt"></i> الأكثر مبيعاً</a>
            <a href="/services/face" class="aside-nav-link"><i class="fab fa-facebook"></i> فيسبوك</a>
            <a href="/services/insta" class="aside-nav-link"><i class="fab fa-instagram"></i> انستجرام</a>
            <a href="/services/tiktok" class="aside-nav-link"><i class="fab fa-tiktok"></i> تيك توك</a>
            <a href="/services/youtube" class="aside-nav-link"><i class="fab fa-youtube"></i> يوتيوب</a>
            <a href="/services/x" class="aside-nav-link"><i class="fab fa-twitter"></i> تويتر</a>
            <a href="/services/telegram" class="aside-nav-link"><i class="fab fa-telegram"></i> تيليجرام</a>
            <a href="/services/threads" class="aside-nav-link"><i class="fas fa-at"></i> ثريدز</a>
            <a href="/earnings.php" class="aside-nav-link"><i class="fas fa-hand-holding-usd"></i> اكسب معنا</a>
            <a href="/services/other" class="aside-nav-link"><i class="fas fa-ellipsis-h"></i> أخرى</a>
            <?php if ($is_logged_in): ?>
            <div class="border-top my-3"></div>
            <a href="/logout.php" class="aside-nav-link"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
            <?php endif; ?>
        </div>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-telegram-plane"></i></a>
        </div>
        <div class="copyright">
            © 2025 متجر مشهور
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            document.getElementById('aside-menu').classList.toggle('active');
            document.getElementById('body-overlay').classList.toggle('active');
        }
        
        function toggleProfileMenu() {
            document.getElementById('profileMenu').classList.toggle('show');
        }
        
        // Close the dropdown menu when clicking outside
        window.addEventListener('click', function(event) {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const profileMenu = document.getElementById('profileMenu');
            
            if (profileDropdown && !profileDropdown.contains(event.target)) {
                if (profileMenu && profileMenu.classList.contains('show')) {
                    profileMenu.classList.remove('show');
                }
            }
        });
    </script>
    
<?php 
// Include notification JavaScript for real-time updates
if ($is_logged_in) {
    include 'notification_js.php';
} 
?>