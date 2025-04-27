<?php
// login.php - Unified login and registration page
session_start();
$page_title = "تسجيل الدخول - متجر مشهور";
include 'config/db.php'; // Include database connection
include_once 'referral_functions.php'; // Include referral functions

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: home");
    }
    exit;
}

// Initialize variables
$errors = [];
$success = false;
$username = $email = $phone = $country = $fullname = '';
$username_email = '';
$remember = false;
$active_form = isset($_GET['form']) ? $_GET['form'] : 'selector';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    // Get form data
    $username_email = filter_input(INPUT_POST, 'username_email', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate form data
    if (empty($username_email)) {
        $errors[] = "يرجى إدخال اسم المستخدم أو البريد الإلكتروني";
    }
    if (empty($password)) {
        $errors[] = "يرجى إدخال كلمة المرور";
    }

    // Check credentials
    if (empty($errors)) {
        // Determine field type
        $field = filter_var($username_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $query = "SELECT * FROM users WHERE $field = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id']     = $user['id'];
                $_SESSION['username']    = $user['username'];
                $_SESSION['full_name']   = $user['full_name'];
                $_SESSION['role']        = $user['role'];

                // Flag to show notification prompt after login
                $_SESSION['show_notification_prompt'] = true;

                // Remember me functionality
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + 30 * 24 * 60 * 60; // 30 days
                    $expiry_date = date('Y-m-d H:i:s', $expiry);

                    // Ensure columns exist
                    $check = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
                    if ($check->num_rows === 0) {
                        $conn->query("
                            ALTER TABLE users 
                            ADD remember_token VARCHAR(255) DEFAULT NULL, 
                            ADD token_expiry DATETIME DEFAULT NULL
                        ");
                    }

                    // Store token
                    $stmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $token, $expiry_date, $user['id']);
                    $stmt->execute();

                    // Set cookies
                    setcookie('remember_token', $token, $expiry, '/', '', false, true);
                    setcookie('user_id', $user['id'], $expiry, '/', '', false, true);
                }

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: home");
                }
                exit;
            } else {
                $errors[] = "كلمة المرور غير صحيحة";
            }
        } else {
            $errors[] = "اسم المستخدم أو البريد الإلكتروني غير مسجل";
        }
    }

    $active_form = 'login';
}

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    $username         = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email            = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone            = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $country          = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $fullname         = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    
    // Check for referral code
    $referral_code = filter_input(INPUT_POST, 'referral_code', FILTER_SANITIZE_STRING);
    $referrer_id = null;
    
    if (!empty($referral_code)) {
        $referrer_id = validateReferralCode($referral_code);
        if (!$referrer_id) {
            $errors[] = "كود الإحالة غير صالح";
        }
    }

    // Validate
    if (empty($username)) {
        $errors[] = "اسم المستخدم مطلوب";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "يجب أن يكون اسم المستخدم بين 3 و 50 حرف";
    }
    if (empty($email)) {
        $errors[] = "البريد الإلكتروني مطلوب";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "يرجى إدخال بريد إلكتروني صحيح";
    }
    if (empty($password)) {
        $errors[] = "كلمة المرور مطلوبة";
    } elseif (strlen($password) < 6) {
        $errors[] = "يجب أن تكون كلمة المرور 6 أحرف على الأقل";
    }
    if ($password !== $confirm_password) {
        $errors[] = "كلمات المرور غير متطابقة";
    }
    if (empty($phone)) {
        $errors[] = "رقم الهاتف مطلوب";
    }
    if (empty($country)) {
        $errors[] = "يرجى اختيار البلد";
    }
    if (empty($fullname)) {
        $errors[] = "الاسم الكامل مطلوب";
    }

    // Check duplicates including phone
    if (empty($errors)) {
        $check = $conn->prepare("
            SELECT * FROM users 
            WHERE username = ? OR email = ? OR phone = ?
        ");
        $check->bind_param("sss", $username, $email, $phone);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows > 0) {
            $u = $res->fetch_assoc();
            if ($u['username'] === $username) {
                $errors[] = "اسم المستخدم مستخدم بالفعل";
            }
            if ($u['email'] === $email) {
                $errors[] = "البريد الإلكتروني مستخدم بالفعل";
            }
            if ($u['phone'] === $phone) {
                $errors[] = "رقم الهاتف مستخدم بالفعل";
            }
        }
    }

    // Insert new user
    if (empty($errors)) {
        // Ensure phone & country columns
        if ($conn->query("SHOW COLUMNS FROM users LIKE 'phone'")->num_rows === 0) {
            $conn->query("ALTER TABLE users ADD phone VARCHAR(20) AFTER email");
        }
        if ($conn->query("SHOW COLUMNS FROM users LIKE 'country'")->num_rows === 0) {
            $conn->query("ALTER TABLE users ADD country VARCHAR(50) AFTER phone");
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $ins = $conn->prepare("
            INSERT INTO users 
            (username, email, password, full_name, phone, country) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $ins->bind_param("ssssss", $username, $email, $hashed, $fullname, $phone, $country);

        if ($ins->execute()) {
            $uid = $conn->insert_id;
            $_SESSION['user_id']   = $uid;
            $_SESSION['username']  = $username;
            $_SESSION['full_name'] = $fullname;
            
            // Generate and set referral code
            $referral_code = generateReferralCode($uid);
            $update_code = $conn->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
            $update_code->bind_param("si", $referral_code, $uid);
            $update_code->execute();
            
            // Process referral if provided
            if ($referrer_id) {
                createReferral($referrer_id, $uid);
            }
            
            $success = true;
            header("refresh:3;url=home");
        } else {
            $errors[] = "حدث خطأ أثناء التسجيل: " . $conn->error;
        }
    }

    $active_form = 'register';
}

// Process Google Sign-In
if (isset($_POST['google_token']) && !empty($_POST['google_token'])) {
    $google_token = $_POST['google_token'];
    $payload = explode('.', $google_token)[1] ?? '';
    $google_data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

    if (!empty($google_data['email'])) {
        $email = $google_data['email'];
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: home");
            }
            exit;
        } else {
            $_SESSION['google_email'] = $email;
            $_SESSION['google_name']  = $google_data['name'] ?? '';
            $active_form = 'google_register';
        }
    } else {
        $errors[] = "فشل في الحصول على بيانات من جوجل";
    }
}

// Process Google additional info form
if (isset($_POST['google_register']) && $_POST['google_register'] === 'true') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $phone    = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $country  = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $email    = $_SESSION['google_email'] ?? '';
    $fullname = $_SESSION['google_name'] ?? '';
    
    // Check for referral code in Google registration
    $referral_code = filter_input(INPUT_POST, 'referral_code', FILTER_SANITIZE_STRING);
    $referrer_id = null;
    
    if (!empty($referral_code)) {
        $referrer_id = validateReferralCode($referral_code);
        if (!$referrer_id) {
            $errors[] = "كود الإحالة غير صالح";
        }
    }

    if (empty($username)) {
        $errors[] = "اسم المستخدم مطلوب";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "يجب أن يكون اسم المستخدم بين 3 و 50 حرف";
    }
    if (empty($phone)) {
        $errors[] = "رقم الهاتف مطلوب";
    }
    if (empty($country)) {
        $errors[] = "يرجى اختيار البلد";
    }

    // Check username uniqueness
    if (empty($errors)) {
        $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = "اسم المستخدم مستخدم بالفعل";
        }
    }

    if (empty($errors)) {
        // Ensure phone & country columns
        if ($conn->query("SHOW COLUMNS FROM users LIKE 'phone'")->num_rows === 0) {
            $conn->query("ALTER TABLE users ADD phone VARCHAR(20) AFTER email");
        }
        if ($conn->query("SHOW COLUMNS FROM users LIKE 'country'")->num_rows === 0) {
            $conn->query("ALTER TABLE users ADD country VARCHAR(50) AFTER phone");
        }

        $random_password = bin2hex(random_bytes(8));
        $hashed = password_hash($random_password, PASSWORD_DEFAULT);

        $ins = $conn->prepare("
            INSERT INTO users 
            (username, email, password, full_name, phone, country) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $ins->bind_param("ssssss", $username, $email, $hashed, $fullname, $phone, $country);

        if ($ins->execute()) {
            $uid = $conn->insert_id;
            $_SESSION['user_id']   = $uid;
            $_SESSION['username']  = $username;
            $_SESSION['full_name'] = $fullname;
            
            // Generate and set referral code for Google sign up
            $referral_code = generateReferralCode($uid);
            $update_code = $conn->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
            $update_code->bind_param("si", $referral_code, $uid);
            $update_code->execute();
            
            // Process referral if provided
            if ($referrer_id) {
                createReferral($referrer_id, $uid);
            }
            
            unset($_SESSION['google_email'], $_SESSION['google_name']);
            header("Location: home");
            exit;
        } else {
            $errors[] = "حدث خطأ أثناء التسجيل: " . $conn->error;
        }
    }

    $active_form = 'google_register';
}

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'], $_COOKIE['user_id'])) {
    $token   = $_COOKIE['remember_token'];
    $user_id = (int)$_COOKIE['user_id'];

    $stmt = $conn->prepare("
        SELECT * FROM users 
        WHERE id = ? AND remember_token = ? 
          AND token_expiry > NOW()
    ");
    $stmt->bind_param("is", $user_id, $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: home");
        }
        exit;
    } else {
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');
    }
}

include 'header.php';
?>

<main>
    <section class="login-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <?php if ($active_form === 'selector'): ?>
                                <!-- Form Selector -->
                                <h1 class="card-title text-center mb-5">متجر مشهور</h1>
                                <div class="d-grid gap-3 mb-4">
                                    <a href="?form=login" class="btn btn-primary btn-lg">تسجيل الدخول</a>
                                    <a href="?form=register" class="btn btn-outline-primary btn-lg">إنشاء حساب جديد</a>
                                </div>
                                <div class="text-center mt-4">
                                    <div id="google-signin-button" class="g-signin2"></div>
                                </div>

                            <?php elseif ($active_form === 'login'): ?>
                                <!-- Login Form -->
                                <h1 class="card-title text-center mb-4">تسجيل الدخول</h1>
                                <?php if ($errors): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="username_email" class="form-label">اسم المستخدم أو البريد الإلكتروني</label>
                                        <input type="text" class="form-control" id="username_email" name="username_email"
                                               value="<?php echo htmlspecialchars($username_email); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">كلمة المرور</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember" name="remember"
                                            <?php echo $remember ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="remember">تذكرني</label>
                                    </div>
                                    <div class="mb-3 text-end">
                                        <a href="forgot-password.php" class="text-decoration-none">نسيت كلمة المرور؟</a>
                                    </div>
                                    <div class="d-grid gap-2 mb-3">
                                        <button type="submit" name="login_submit" class="btn btn-primary">دخول</button>
                                    </div>
                                    <div class="text-center mb-3"><p>أو</p></div>
                                    <div class="d-grid gap-2 mb-3">
                                        <div id="google-signin-button" class="g-signin2"></div>
                                    </div>
                                    <div class="text-center">
                                        <p>ليس لديك حساب؟ <a href="?form=register">إنشاء حساب جديد</a></p>
                                    </div>
                                </form>

                            <?php elseif ($active_form === 'register'): ?>
                                <!-- Registration Form -->
                                <h1 class="card-title text-center mb-4">إنشاء حساب جديد</h1>
                                <?php if ($errors): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <?php if ($success): ?>
                                    <div class="alert alert-success">
                                        تم تسجيل حسابك بنجاح! جاري التحويل...
                                    </div>
                                <?php else: ?>
                                    <form method="post" action="" id="register-form">
                                        <div class="mb-3">
                                            <label for="fullname" class="form-label">الاسم الكامل *</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname"
                                                   value="<?php echo htmlspecialchars($fullname); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="username" class="form-label">اسم المستخدم *</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                   value="<?php echo htmlspecialchars($username); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">البريد الإلكتروني *</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                   value="<?php echo htmlspecialchars($email); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="country" class="form-label">البلد *</label>
                                            <select class="form-select" id="country" name="country" required>
                                                <option value="">اختر البلد</option>
                                                <option value="YE" <?php echo $country==='YE'?'selected':''; ?>>اليمن</option>
                                                <option value="AE" <?php echo $country==='AE'?'selected':''; ?>>الإمارات</option>
                                                <option value="SA" <?php echo $country==='SA'?'selected':''; ?>>السعودية</option>
                                                <option value="EG" <?php echo $country==='EG'?'selected':''; ?>>مصر</option>
                                                <option value="JO" <?php echo $country==='JO'?'selected':''; ?>>الأردن</option>
                                                <option value="BH" <?php echo $country==='BH'?'selected':''; ?>>البحرين</option>
                                                <option value="DZ" <?php echo $country==='DZ'?'selected':''; ?>>الجزائر</option>
                                                <option value="IQ" <?php echo $country==='IQ'?'selected':''; ?>>العراق</option>
                                                <option value="KW" <?php echo $country==='KW'?'selected':''; ?>>الكويت</option>
                                                <option value="LB" <?php echo $country==='LB'?'selected':''; ?>>لبنان</option>
                                                <option value="LY" <?php echo $country==='LY'?'selected':''; ?>>ليبيا</option>
                                                <option value="MA" <?php echo $country==='MA'?'selected':''; ?>>المغرب</option>
                                                <option value="OM" <?php echo $country==='OM'?'selected':''; ?>>عمان</option>
                                                <option value="PS" <?php echo $country==='PS'?'selected':''; ?>>فلسطين</option>
                                                <option value="QA" <?php echo $country==='QA'?'selected':''; ?>>قطر</option>
                                                <option value="SD" <?php echo $country==='SD'?'selected':''; ?>>السودان</option>
                                                <option value="SY" <?php echo $country==='SY'?'selected':''; ?>>سوريا</option>
                                                <option value="TN" <?php echo $country==='TN'?'selected':''; ?>>تونس</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">رقم الهاتف *</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                   value="<?php echo htmlspecialchars($phone); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">كلمة المرور *</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">تأكيد كلمة المرور *</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="referral_code" class="form-label">كود الإحالة (اختياري)</label>
                                            <input type="text" class="form-control" id="referral_code" name="referral_code" 
                                                   value="<?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ''; ?>">
                                            <div class="form-text">أدخل كود الإحالة إذا تمت دعوتك من قبل صديق</div>
                                        </div>
                                        <div class="d-grid gap-2 mb-3">
                                            <button type="submit" name="register_submit" class="btn btn-primary">تسجيل</button>
                                        </div>
                                        <div class="text-center mb-3"><p>أو</p></div>
                                        <div class="d-grid gap-2 mb-3">
                                            <div id="google-signin-button" class="g-signin2"></div>
                                        </div>
                                        <div class="text-center">
                                            <p>لديك حساب؟ <a href="?form=login">تسجيل الدخول</a></p>
                                        </div>
                                    </form>
                                <?php endif; ?>

                            <?php elseif ($active_form === 'google_register'): ?>
                                <!-- Google Registration Additional Info -->
                                <h1 class="card-title text-center mb-4">استكمال معلومات التسجيل</h1>
                                <?php if ($errors): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <form method="post" action="" id="google-register-form">
                                    <input type="hidden" name="google_register" value="true">
                                    <div class="alert alert-info">
                                        يرجى إكمال معلوماتك للمتابعة باستخدام جوجل.
                                    </div>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">اسم المستخدم *</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="country" class="form-label">البلد *</label>
                                        <select class="form-select" id="country" name="country" required>
                                            <option value="">اختر البلد</option>
                                            <option value="YE">اليمن</option>
                                            <option value="SA">السعودية</option>
                                            <option value="EG">مصر</option>
                                            <option value="JO">الأردن</option>
                                            <option value="BH">البحرين</option>
                                            <option value="DZ">الجزائر</option>
                                            <option value="IQ">العراق</option>
                                            <option value="KW">الكويت</option>
                                            <option value="LB">لبنان</option>
                                            <option value="LY">ليبيا</option>
                                            <option value="MA">المغرب</option>
                                            <option value="OM">عمان</option>
                                            <option value="PS">فلسطين</option>
                                            <option value="QA">قطر</option>
                                            <option value="SD">السودان</option>
                                            <option value="SY">سوريا</option>
                                            <option value="TN">تونس</option>
                                            <option value="AE">الإمارات</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">رقم الهاتف *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="referral_code" class="form-label">كود الإحالة (اختياري)</label>
                                        <input type="text" class="form-control" id="referral_code" name="referral_code" 
                                               value="<?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ''; ?>">
                                        <div class="form-text">أدخل كود الإحالة إذا تمت دعوتك من قبل صديق</div>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">إكمال التسجيل</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.login-section {
    background-color: #f8f9fa;
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
}
.card {
    border-radius: 15px;
    border: none;
    transition: all 0.3s ease;
}
.card:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.card-title {
    color: var(--primary-color);
}
.form-label {
    font-weight: 600;
}
.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    padding: 10px 20px;
    font-weight: 600;
    border-radius: 5px;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    background-color: var(--secondary-color);
    border-color: var(--secondary-color);
    transform: translateY(-2px);
}
.btn-outline-primary {
    color: var(--primary-color);
    border-color: var(--primary-color);
    padding: 10px 20px;
    font-weight: 600;
    border-radius: 5px;
    transition: all 0.3s ease;
}
.btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: #fff;
    transform: translateY(-2px);
}
/* Google Sign-In Button */
#google-signin-button {
    width: 100%;
    display: flex;
    justify-content: center;
    margin: 0 auto;
}
</style>

<!-- Google Sign-In API -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Google button
    google.accounts.id.initialize({
        client_id: 'YOUR_GOOGLE_CLIENT_ID',
        callback: handleGoogleSignIn
    });
    google.accounts.id.renderButton(
        document.getElementById('google-signin-button'), {
            theme: 'outline',
            size: 'large',
            width: 280,
            text: 'continue_with',
            shape: 'rectangular',
            logo_alignment: 'center'
        }
    );

    // Password confirmation on register
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const pw = document.getElementById('password').value;
            const cpw = document.getElementById('confirm_password').value;
            if (pw !== cpw) {
                event.preventDefault();
                alert('كلمات المرور غير متطابقة');
            }
        });
    }

    // Auto‑prefix phone based on country code
    const dialCodes = {
        AE: '+971',
        SA: '+966',
        EG: '+20',
        JO: '+962',
        BH: '+973',
        DZ: '+213',
        IQ: '+964',
        KW: '+965',
        LB: '+961',
        LY: '+218',
        MA: '+212',
        OM: '+968',
        PS: '+970',
        QA: '+974',
        SD: '+249',
        SY: '+963',
        TN: '+216',
        YE: '+967'
    };

    document.querySelectorAll('select[name="country"]').forEach(select => {
        select.addEventListener('change', function() {
            const code = dialCodes[this.value] || '';
            const phoneInput = this.form.querySelector('input[name="phone"]');
            if (phoneInput) {
                phoneInput.value = code;
                phoneInput.setSelectionRange(phoneInput.value.length, phoneInput.value.length);
                phoneInput.focus();
            }
        });
        // trigger initial prefix if country preselected
        if (select.value) {
            select.dispatchEvent(new Event('change'));
        }
    });
});

function handleGoogleSignIn(response) {
    const token = response.credential;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'login.php?form=google';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'google_token';
    input.value = token;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include 'footer.php'; ?>