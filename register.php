<?php
// register.php
session_start();
$page_title = "تسجيل حساب جديد - متجر مشهور";
include 'config/db.php'; // Include database connection

// Initialize variables
$errors = [];
$success = false;
$username = $email = $phone = $country = $fullname = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    
    // Validate form data
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
    
    // Check if username or email already exists
    if (empty($errors)) {
        $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['username'] === $username) {
                $errors[] = "اسم المستخدم مستخدم بالفعل";
            }
            if ($user['email'] === $email) {
                $errors[] = "البريد الإلكتروني مستخدم بالفعل";
            }
        }
    }
    
    // If no errors, insert new user
    if (empty($errors)) {
        // First, check if the country and phone columns exist
        // This allows for backward compatibility if the columns don't exist yet
        $check_columns_query = "SHOW COLUMNS FROM users LIKE 'phone'";
        $result = $conn->query($check_columns_query);
        $phone_column_exists = $result->num_rows > 0;
        
        $check_columns_query = "SHOW COLUMNS FROM users LIKE 'country'";
        $result = $conn->query($check_columns_query);
        $country_column_exists = $result->num_rows > 0;
        
        // If columns don't exist, add them
        if (!$phone_column_exists) {
            $alter_query = "ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email";
            $conn->query($alter_query);
        }
        
        if (!$country_column_exists) {
            $alter_query = "ALTER TABLE users ADD COLUMN country VARCHAR(50) AFTER phone";
            $conn->query($alter_query);
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $insert_query = "INSERT INTO users (username, email, password, full_name, phone, country) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssss", $username, $email, $hashed_password, $fullname, $phone, $country);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $fullname;
            
            // Display success message
            $success = true;
            
            // Redirect after successful registration
            header("refresh:3;url=index.php");
        } else {
            $errors[] = "حدث خطأ أثناء التسجيل: " . $conn->error;
        }
    }
}

// Process Google Sign-In
if (isset($_POST['google_token']) && !empty($_POST['google_token'])) {
    $google_token = $_POST['google_token'];
    
    // Verify token with Google (in a production environment, you should properly verify this token)
    // This is a simplified example - in production, you would use Google's API to verify the token
    $google_data = json_decode(base64_decode(explode('.', $google_token)[1]));
    
    if (isset($google_data->email) && !empty($google_data->email)) {
        $email = $google_data->email;
        $fullname = $google_data->name ?? '';
        
        // Check if user exists
        $check_query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // User exists, log them in
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Redirect to home page
            header("Location: index.php");
            exit;
        } else {
            // User doesn't exist, ask for additional information
            $_SESSION['google_email'] = $email;
            $_SESSION['google_name'] = $fullname;
            
            // Set a flag to show the additional info form
            $show_google_form = true;
        }
    } else {
        $errors[] = "فشل في الحصول على بيانات من جوجل";
    }
}

// Process Google additional info form
if (isset($_POST['google_register']) && $_POST['google_register'] === 'true') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $email = $_SESSION['google_email'] ?? '';
    $fullname = $_SESSION['google_name'] ?? '';
    
    // Validate form data
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
    
    // Check if username already exists
    if (empty($errors)) {
        $check_query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "اسم المستخدم مستخدم بالفعل";
        }
    }
    
    // If no errors, insert new user
    if (empty($errors)) {
        // Check if the columns exist (same as above)
        $check_columns_query = "SHOW COLUMNS FROM users LIKE 'phone'";
        $result = $conn->query($check_columns_query);
        $phone_column_exists = $result->num_rows > 0;
        
        $check_columns_query = "SHOW COLUMNS FROM users LIKE 'country'";
        $result = $conn->query($check_columns_query);
        $country_column_exists = $result->num_rows > 0;
        
        if (!$phone_column_exists) {
            $alter_query = "ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email";
            $conn->query($alter_query);
        }
        
        if (!$country_column_exists) {
            $alter_query = "ALTER TABLE users ADD COLUMN country VARCHAR(50) AFTER phone";
            $conn->query($alter_query);
        }
        
        // Generate a random password (user won't need it as they'll use Google sign-in)
        $random_password = bin2hex(random_bytes(8));
        $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
        
        // Insert user
        $insert_query = "INSERT INTO users (username, email, password, full_name, phone, country) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssss", $username, $email, $hashed_password, $fullname, $phone, $country);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $fullname;
            
            // Clear Google session data
            unset($_SESSION['google_email']);
            unset($_SESSION['google_name']);
            
            // Redirect after successful registration
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "حدث خطأ أثناء التسجيل: " . $conn->error;
        }
    }
}

include 'header.php';
?>

<main>
    <section class="register-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h1 class="card-title text-center mb-4">تسجيل حساب جديد</h1>
                            
                            <?php if (!empty($errors)): ?>
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
                                    <p class="mb-0">تم تسجيل حسابك بنجاح! سيتم تحويلك إلى الصفحة الرئيسية...</p>
                                </div>
                            <?php else: ?>
                                <?php if (isset($show_google_form) && $show_google_form): ?>
                                    <!-- Google Additional Info Form -->
                                    <form method="post" action="" id="google-register-form">
                                        <input type="hidden" name="google_register" value="true">
                                        
                                        <div class="alert alert-info">
                                            <p>مرحبًا بك! يرجى إكمال معلوماتك للمتابعة باستخدام حساب جوجل.</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="username" class="form-label">اسم المستخدم *</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">رقم الهاتف *</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="country" class="form-label">البلد *</label>
                                            <select class="form-select" id="country" name="country" required>
                                                <option value="">اختر البلد</option>
                                                <!-- Country list -->
                                                <option value="AE">الإمارات العربية المتحدة</option>
                                                <option value="SA">المملكة العربية السعودية</option>
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
                                                <option value="YE">اليمن</option>
                                                <!-- Add more countries as needed -->
                                            </select>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">إكمال التسجيل</button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <!-- Regular Registration Form -->
                                    <form method="post" action="" id="register-form">
                                        <div class="mb-3">
                                            <label for="fullname" class="form-label">الاسم الكامل *</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="username" class="form-label">اسم المستخدم *</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">البريد الإلكتروني *</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">رقم الهاتف *</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="country" class="form-label">البلد *</label>
                                            <select class="form-select" id="country" name="country" required>
                                                <option value="">اختر البلد</option>
                                                <!-- Country list -->
                                                <option value="AE" <?php echo $country === 'AE' ? 'selected' : ''; ?>>الإمارات العربية المتحدة</option>
                                                <option value="SA" <?php echo $country === 'SA' ? 'selected' : ''; ?>>المملكة العربية السعودية</option>
                                                <option value="EG" <?php echo $country === 'EG' ? 'selected' : ''; ?>>مصر</option>
                                                <option value="JO" <?php echo $country === 'JO' ? 'selected' : ''; ?>>الأردن</option>
                                                <option value="BH" <?php echo $country === 'BH' ? 'selected' : ''; ?>>البحرين</option>
                                                <option value="DZ" <?php echo $country === 'DZ' ? 'selected' : ''; ?>>الجزائر</option>
                                                <option value="IQ" <?php echo $country === 'IQ' ? 'selected' : ''; ?>>العراق</option>
                                                <option value="KW" <?php echo $country === 'KW' ? 'selected' : ''; ?>>الكويت</option>
                                                <option value="LB" <?php echo $country === 'LB' ? 'selected' : ''; ?>>لبنان</option>
                                                <option value="LY" <?php echo $country === 'LY' ? 'selected' : ''; ?>>ليبيا</option>
                                                <option value="MA" <?php echo $country === 'MA' ? 'selected' : ''; ?>>المغرب</option>
                                                <option value="OM" <?php echo $country === 'OM' ? 'selected' : ''; ?>>عمان</option>
                                                <option value="PS" <?php echo $country === 'PS' ? 'selected' : ''; ?>>فلسطين</option>
                                                <option value="QA" <?php echo $country === 'QA' ? 'selected' : ''; ?>>قطر</option>
                                                <option value="SD" <?php echo $country === 'SD' ? 'selected' : ''; ?>>السودان</option>
                                                <option value="SY" <?php echo $country === 'SY' ? 'selected' : ''; ?>>سوريا</option>
                                                <option value="TN" <?php echo $country === 'TN' ? 'selected' : ''; ?>>تونس</option>
                                                <option value="YE" <?php echo $country === 'YE' ? 'selected' : ''; ?>>اليمن</option>
                                                <!-- Add more countries as needed -->
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="password" class="form-label">كلمة المرور *</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="confirm_password" class="form-label">تأكيد كلمة المرور *</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        
                                        <div class="d-grid gap-2 mb-3">
                                            <button type="submit" class="btn btn-primary">تسجيل</button>
                                        </div>
                                        
                                        <div class="text-center mb-3">
                                            <p>أو</p>
                                        </div>
                                        
                                        <div class="d-grid gap-2 mb-3">
                                            <div id="google-signin-button" class="g-signin2"></div>
                                        </div>
                                        
                                        <div class="text-center">
                                            <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .register-section {
        background-color: #f8f9fa;
    }
    
    .card {
        border-radius: 15px;
        border: none;
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
    }
    
    .btn-primary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }
    
    /* Google Sign-In Button */
    .google-btn {
        background-color: #fff;
        color: #757575;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    .google-btn:hover {
        background-color: #f1f1f1;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .google-btn img {
        width: 20px;
        height: 20px;
    }
    
    /* Google Sign-In Button Styling */
    #google-signin-button {
        width: 100%;
        display: flex;
        justify-content: center;
        margin: 0 auto;
    }
</style>

<!-- Add Google Sign-In API -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Google Sign-In
    google.accounts.id.initialize({
        client_id: 'YOUR_GOOGLE_CLIENT_ID', // Replace with your Google Client ID
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
});

function handleGoogleSignIn(response) {
    // Send the ID token to your server
    const token = response.credential;
    
    // Create a form to submit the token
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'register.php';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'google_token';
    input.value = token;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                event.preventDefault();
                alert('كلمات المرور غير متطابقة');
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>