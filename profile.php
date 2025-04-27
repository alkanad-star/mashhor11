<?php
// profile.php
session_start();
$page_title = "تعديل الملف الشخصي - متجر مشهور";

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

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        // Get form data
        $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
        
        // Validate form data
        $errors = [];
        
        if (empty($fullname)) {
            $errors[] = "الاسم الكامل مطلوب";
        }
        
        if (empty($email)) {
            $errors[] = "البريد الإلكتروني مطلوب";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "يرجى إدخال بريد إلكتروني صحيح";
        }
        
        if (empty($phone)) {
            $errors[] = "رقم الهاتف مطلوب";
        }
        
        if (empty($country)) {
            $errors[] = "البلد مطلوب";
        }
        
        // Check if email is already used
        if ($email !== $user['email']) {
            $check_query = "SELECT * FROM users WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "البريد الإلكتروني مستخدم بالفعل";
            }
        }
        
        // Process profile image if uploaded
        $profile_image = $user['profile_image'] ?? 'images/default-profile.png';
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            $file_name = $_FILES['profile_image']['name'];
            $file_size = $_FILES['profile_image']['size'];
            $file_tmp = $_FILES['profile_image']['tmp_name'];
            $file_type = $_FILES['profile_image']['type'];
            
            // Check file type
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "نوع الملف غير مسموح به. يرجى استخدام JPG أو PNG أو GIF فقط.";
            }
            
            // Check file size
            if ($file_size > $max_size) {
                $errors[] = "حجم الملف كبير جدًا. الحد الأقصى المسموح به هو 5 ميجابايت.";
            }
            
            if (empty($errors)) {
                // Create uploads directory if it doesn't exist
                $upload_dir = 'uploads/profiles/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate a unique filename
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_file_name;
                
                // Process and compress the image
                list($width, $height) = getimagesize($file_tmp);
                
                // Set max dimensions while keeping aspect ratio
                $max_width = 500;
                $max_height = 500;
                
                if ($width > $max_width || $height > $max_height) {
                    $ratio = min($max_width / $width, $max_height / $height);
                    $new_width = round($width * $ratio);
                    $new_height = round($height * $ratio);
                } else {
                    $new_width = $width;
                    $new_height = $height;
                }
                
                // Create new image
                $new_image = imagecreatetruecolor($new_width, $new_height);
                
                // Process based on file type
                switch ($file_type) {
                    case 'image/jpeg':
                        $source = imagecreatefromjpeg($file_tmp);
                        break;
                    case 'image/png':
                        $source = imagecreatefrompng($file_tmp);
                        // Preserve transparency
                        imagealphablending($new_image, false);
                        imagesavealpha($new_image, true);
                        break;
                    case 'image/gif':
                        $source = imagecreatefromgif($file_tmp);
                        break;
                }
                
                // Resize the image
                imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                
                // Save the compressed image
                switch ($file_type) {
                    case 'image/jpeg':
                        imagejpeg($new_image, $upload_path, 80); // 80% quality
                        break;
                    case 'image/png':
                        imagepng($new_image, $upload_path, 8); // Compression level 8
                        break;
                    case 'image/gif':
                        imagegif($new_image, $upload_path);
                        break;
                }
                
                // Free up memory
                imagedestroy($source);
                imagedestroy($new_image);
                
                // Update profile image path
                $profile_image = $upload_path;
                
                // Remove old image if it's not the default
                if ($user['profile_image'] !== 'images/default-profile.png' && file_exists($user['profile_image'])) {
                    unlink($user['profile_image']);
                }
            }
        }
        
        // Update user profile
        if (empty($errors)) {
            $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ?, country = ?, profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sssssi", $fullname, $email, $phone, $country, $profile_image, $user_id);
            
            if ($stmt->execute()) {
                // Update session variables
                $_SESSION['full_name'] = $fullname;
                
                // Refresh user data
                $stmt = $conn->prepare($user_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                
                $success_message = "تم تحديث الملف الشخصي بنجاح";
            } else {
                $error_message = "حدث خطأ أثناء تحديث الملف الشخصي: " . $conn->error;
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    } elseif ($action === 'change_password') {
        // Get form data
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate form data
        $errors = [];
        
        if (empty($current_password)) {
            $errors[] = "كلمة المرور الحالية مطلوبة";
        }
        
        if (empty($new_password)) {
            $errors[] = "كلمة المرور الجديدة مطلوبة";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "يجب أن تكون كلمة المرور الجديدة 6 أحرف على الأقل";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "كلمات المرور غير متطابقة";
        }
        
        // Verify current password
        if (empty($errors)) {
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = "كلمة المرور الحالية غير صحيحة";
            }
        }
        
        // Update password
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "تم تغيير كلمة المرور بنجاح";
            } else {
                $error_message = "حدث خطأ أثناء تغيير كلمة المرور: " . $conn->error;
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    }
}

include 'header.php';
?>

<main>
    <section class="profile-section py-5">
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
                                <a href="profile.php" class="list-group-item list-group-item-action active">
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
                    
                    <!-- Profile Photo -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">الصورة الشخصية</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="" enctype="multipart/form-data" id="profile-image-form">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center">
                                        <div class="profile-image-container">
                                            <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'images/default-profile.png'); ?>" alt="Profile" class="img-fluid rounded-circle mb-3" id="profile-image-preview" style="width: 150px; height: 150px; object-fit: cover;">
                                            <div class="profile-image-overlay">
                                                <i class="fas fa-camera"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-9">
                                        <div class="mb-3">
                                            <label for="profile_image" class="form-label">تغيير الصورة الشخصية</label>
                                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpeg, image/png, image/gif">
                                            <small class="text-muted">الأنواع المسموح بها: JPG، PNG، GIF. الحد الأقصى للحجم: 5 ميجابايت.</small>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">تحديث الصورة</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Profile Info -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">المعلومات الشخصية</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fullname" class="form-label">الاسم الكامل *</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">اسم المستخدم</label>
                                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                        <small class="text-muted">لا يمكن تغيير اسم المستخدم</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">البريد الإلكتروني *</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">رقم الهاتف *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="country" class="form-label">البلد *</label>
                                    <select class="form-select" id="country" name="country" required>
                                        <option value="">اختر البلد</option>
                                        <option value="AE" <?php echo $user['country'] === 'AE' ? 'selected' : ''; ?>>الإمارات العربية المتحدة</option>
                                        <option value="SA" <?php echo $user['country'] === 'SA' ? 'selected' : ''; ?>>المملكة العربية السعودية</option>
                                        <option value="EG" <?php echo $user['country'] === 'EG' ? 'selected' : ''; ?>>مصر</option>
                                        <option value="JO" <?php echo $user['country'] === 'JO' ? 'selected' : ''; ?>>الأردن</option>
                                        <option value="BH" <?php echo $user['country'] === 'BH' ? 'selected' : ''; ?>>البحرين</option>
                                        <option value="DZ" <?php echo $user['country'] === 'DZ' ? 'selected' : ''; ?>>الجزائر</option>
                                        <option value="IQ" <?php echo $user['country'] === 'IQ' ? 'selected' : ''; ?>>العراق</option>
                                        <option value="KW" <?php echo $user['country'] === 'KW' ? 'selected' : ''; ?>>الكويت</option>
                                        <option value="LB" <?php echo $user['country'] === 'LB' ? 'selected' : ''; ?>>لبنان</option>
                                        <option value="LY" <?php echo $user['country'] === 'LY' ? 'selected' : ''; ?>>ليبيا</option>
                                        <option value="MA" <?php echo $user['country'] === 'MA' ? 'selected' : ''; ?>>المغرب</option>
                                        <option value="OM" <?php echo $user['country'] === 'OM' ? 'selected' : ''; ?>>عمان</option>
                                        <option value="PS" <?php echo $user['country'] === 'PS' ? 'selected' : ''; ?>>فلسطين</option>
                                        <option value="QA" <?php echo $user['country'] === 'QA' ? 'selected' : ''; ?>>قطر</option>
                                        <option value="SD" <?php echo $user['country'] === 'SD' ? 'selected' : ''; ?>>السودان</option>
                                        <option value="SY" <?php echo $user['country'] === 'SY' ? 'selected' : ''; ?>>سوريا</option>
                                        <option value="TN" <?php echo $user['country'] === 'TN' ? 'selected' : ''; ?>>تونس</option>
                                        <option value="YE" <?php echo $user['country'] === 'YE' ? 'selected' : ''; ?>>اليمن</option>
                                    </select>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">تغيير كلمة المرور</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="" id="change-password-form">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">كلمة المرور الحالية *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">كلمة المرور الجديدة *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <small class="text-muted">يجب أن تكون كلمة المرور 6 أحرف على الأقل</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">تغيير كلمة المرور</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .profile-section {
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
    
    .card-header {
        border-bottom: 1px solid #eee;
    }
    
    .form-label {
        font-weight: 500;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        padding: 10px 20px;
        font-weight: 600;
    }
    
    .btn-primary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }
    
    /* Profile Image */
    .profile-image-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: hidden;
        cursor: pointer;
    }
    
    .profile-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .profile-image-overlay i {
        color: white;
        font-size: 2rem;
    }
    
    .profile-image-container:hover .profile-image-overlay {
        opacity: 1;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Change Password Form Validation
    const changePasswordForm = document.getElementById('change-password-form');
    
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(event) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                event.preventDefault();
                alert('كلمات المرور غير متطابقة');
            }
        });
    }
    
    // Profile Image Preview
    const profileImageInput = document.getElementById('profile_image');
    const profileImagePreview = document.getElementById('profile-image-preview');
    const profileImageContainer = document.querySelector('.profile-image-container');
    
    if (profileImageInput && profileImagePreview) {
        profileImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        profileImageContainer.addEventListener('click', function() {
            profileImageInput.click();
        });
    }
});
</script>

<?php include 'footer.php'; ?>