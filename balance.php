<?php
// balance.php
session_start();
$page_title = "رصيدي - متجر مشهور";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'config/db.php';

/**
 * Send payment notification to user
 * 
 * @param int $user_id User ID
 * @param int $transaction_id Transaction ID
 * @param string $status Transaction status (completed, pending)
 * @param float $amount Transaction amount
 * @return void
 */
function send_payment_notification($user_id, $transaction_id, $status, $amount) {
    global $conn;
    
    // Get user information
    $user_query = "SELECT email, username, full_name FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Create notification in the database
    $notification_query = "INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())";
    $stmt = $conn->prepare($notification_query);
    
    $title = "إشعار معاملة";
    $message = "";
    
    if ($status == 'completed') {
        $title = "تم شحن رصيدك بنجاح";
        $message = "تم إضافة $amount$ إلى رصيدك بنجاح. رقم المعاملة: #$transaction_id";
    } else if ($status == 'pending') {
        $title = "طلب شحن قيد المراجعة";
        $message = "تم استلام طلب شحن رصيدك بقيمة $amount$. سيتم مراجعته وتحديث رصيدك في أقرب وقت. رقم المعاملة: #$transaction_id";
    }
    
    $stmt->bind_param("iss", $user_id, $title, $message);
    $stmt->execute();
    
    // You could add email notification here if needed
    // Example:
    // mail($user['email'], $title, $message);
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get all user transactions
$transactions_query = "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($transactions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result();

// Get pending transactions
$pending_transactions_query = "SELECT * FROM transactions WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC";
$stmt = $conn->prepare($pending_transactions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_transactions = $stmt->get_result();

// Get settings
$settings_query = "SELECT * FROM settings";
$settings_result = $conn->query($settings_query);
$settings = [];
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Process payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    
    $errors = [];
    
    // Debug file uploads
    if (isset($_FILES['payment_receipt'])) {
        error_log('File upload data: ' . print_r($_FILES['payment_receipt'], true));
    }
    
    // Validate amount
    $min_deposit = $settings['min_deposit'] ?? 2;
    if (!$amount || $amount < $min_deposit) {
        $errors[] = "الحد الأدنى للشحن هو $" . $min_deposit;
    }
    
    // Validate payment method
    if (empty($payment_method)) {
        $errors[] = "يرجى اختيار طريقة الدفع";
    }
    
    // Check if payment receipt is required and provided
    $receipt_required = in_array($payment_method, ['usdt', 'binance', 'bank_transfer', 'karimi_bank', 'local_transfer', 'local_wallet']);
    
    if ($receipt_required) {
        // Check if file was uploaded
        if (!isset($_FILES['payment_receipt']) || $_FILES['payment_receipt']['error'] == UPLOAD_ERR_NO_FILE) {
            $errors[] = "يجب إرفاق إيصال الدفع";
        } elseif ($_FILES['payment_receipt']['error'] != UPLOAD_ERR_OK) {
            // Check for other upload errors
            switch($_FILES['payment_receipt']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $errors[] = "حجم الملف أكبر من الحد المسموح به في إعدادات PHP";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = "حجم الملف أكبر من الحد المسموح به في النموذج";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = "تم تحميل جزء من الملف فقط";
                    break;
                default:
                    $errors[] = "حدث خطأ أثناء تحميل الملف (كود: " . $_FILES['payment_receipt']['error'] . ")";
            }
        } else {
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['payment_receipt']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "نوع الملف غير مسموح به. يجب أن يكون الملف صورة (JPG, PNG, GIF)";
            }
            
            // Validate file size (max 5MB)
            if ($_FILES['payment_receipt']['size'] > 5 * 1024 * 1024) {
                $errors[] = "حجم الملف كبير جدًا. يجب أن لا يتجاوز الملف 5 ميجابايت";
            }
        }
    }
    
    // Process payment if no errors
    if (empty($errors)) {
        // Create transaction record
        $status = $payment_method === 'credit_card' ? 'completed' : 'pending';
        
        // Set description based on payment method
        switch ($payment_method) {
            case 'credit_card':
                $description = "شحن رصيد عبر بطاقة ائتمانية";
                break;
            case 'usdt':
                $description = "شحن رصيد عبر USDT";
                break;
            case 'binance':
                $description = "شحن رصيد عبر Binance Pay";
                break;
            case 'bank_transfer':
                $description = "شحن رصيد عبر تحويل بنكي";
                break;
            case 'karimi_bank':
                $description = "شحن رصيد عبر بنك الكريمي";
                break;
            case 'local_transfer':
                $description = "شحن رصيد عبر حوالة محلية";
                break;
            case 'local_wallet':
                $description = "شحن رصيد عبر محفظة محلية";
                break;
            default:
                $description = "شحن رصيد";
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert transaction
            $insert_transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) VALUES (?, ?, 'deposit', ?, ?)";
            $stmt = $conn->prepare($insert_transaction_query);
            $stmt->bind_param("idss", $user_id, $amount, $status, $description);
            $stmt->execute();
            $transaction_id = $conn->insert_id;
            
            // If payment method is credit card, update user balance
            if ($payment_method === 'credit_card') {
                $update_balance_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
                $stmt = $conn->prepare($update_balance_query);
                $stmt->bind_param("di", $amount, $user_id);
                $stmt->execute();
            }
            
            // Create payment_receipts table if it doesn't exist (do this first)
            $conn->query("CREATE TABLE IF NOT EXISTS payment_receipts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                transaction_id INT NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
            )");
            
            // If receipt is required, upload and save it
            if ($receipt_required && isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] == UPLOAD_ERR_OK) {
                // Create uploads directory if it doesn't exist
                $upload_dir = 'uploads/receipts/';
                
                // Create directory with permissions if it doesn't exist
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        throw new Exception("فشل في إنشاء مجلد التحميل. يرجى التحقق من الصلاحيات.");
                    }
                    // Set proper permissions
                    chmod($upload_dir, 0755);
                }
                
                // Generate a unique filename
                $file_extension = pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION);
                $new_filename = 'receipt_' . $transaction_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                // Move uploaded file
                if (!move_uploaded_file($_FILES['payment_receipt']['tmp_name'], $upload_path)) {
                    throw new Exception("فشل في تحميل الصورة. يرجى المحاولة مرة أخرى.");
                }
                
                // Save receipt info in database
                $insert_receipt_query = "INSERT INTO payment_receipts (transaction_id, file_path) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_receipt_query);
                $stmt->bind_param("is", $transaction_id, $upload_path);
                
                if (!$stmt->execute()) {
                    throw new Exception("فشل في حفظ بيانات الإيصال في قاعدة البيانات.");
                }
            } elseif ($receipt_required) {
                // If we get here, something went wrong with the file upload
                throw new Exception("فشل في معالجة إيصال الدفع. يرجى المحاولة مرة أخرى.");
            }
            
            // Send payment notification to user
            send_payment_notification($user_id, $transaction_id, $status, $amount);
            
            $conn->commit();
            $success_message = "تم إنشاء طلب الشحن بنجاح. سيتم تحديث رصيدك بعد مراجعة الإدارة.";
            
            // If payment method is credit card, show different message
            if ($payment_method === 'credit_card') {
                $success_message = "تم شحن رصيدك بنجاح.";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "حدث خطأ أثناء معالجة طلب الشحن: " . $e->getMessage();
            error_log("Payment error: " . $e->getMessage());
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

include 'header.php';
?>

<main>
    <section class="balance-section py-5">
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
                                <a href="balance.php" class="list-group-item list-group-item-action active">
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
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Current Balance -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h4 class="card-title mb-4">رصيدك الحالي</h4>
                                    <h2 class="balance-amount"><?php echo number_format($user['balance'], 2); ?> <small>$</small></h2>
                                    <p class="text-muted">آخر تحديث: <?php echo date('Y-m-d H:i', strtotime($user['updated_at'])); ?></p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                                        <i class="fas fa-plus-circle me-2"></i> شحن الرصيد
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Balance Stats -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">إحصائيات الرصيد</h4>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="balance-stat-card">
                                        <div class="balance-stat-icon">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div class="balance-stat-content">
                                            <h3 class="balance-stat-value"><?php echo number_format($user['balance'], 2); ?> $</h3>
                                            <p class="balance-stat-label">الرصيد المتاح</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="balance-stat-card">
                                        <div class="balance-stat-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="balance-stat-content">
                                            <h3 class="balance-stat-value"><?php echo number_format($user['spent'] ?? 0, 2); ?> $</h3>
                                            <p class="balance-stat-label">المبلغ المستخدم</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="balance-stat-card">
                                        <div class="balance-stat-icon">
                                            <i class="fas fa-sync-alt"></i>
                                        </div>
                                        <div class="balance-stat-content">
                                            <h3 class="balance-stat-value"><?php echo number_format($user['in_use'], 2); ?> $</h3>
                                            <p class="balance-stat-label">الرصيد قيد الاستخدام</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pending Transactions -->
                    <?php if ($pending_transactions->num_rows > 0): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning bg-opacity-25">
                            <h5 class="card-title mb-0">معاملات قيد الانتظار</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>النوع</th>
                                            <th>المبلغ</th>
                                            <th>طريقة الدفع</th>
                                            <th>التاريخ</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($transaction = $pending_transactions->fetch_assoc()): ?>
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
                                            <td><?php echo $transaction['description'] ?? 'غير محدد'; ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                                            <td><span class="badge bg-warning">قيد الانتظار</span></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Transaction History -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">سجل المعاملات</h5>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-filter me-1"></i> تصفية
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                    <li><a class="dropdown-item" href="#" data-filter="all">جميع المعاملات</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="deposit">الإيداعات</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="withdrawal">عمليات السحب</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="purchase">المشتريات</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="refund">المبالغ المستردة</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if ($transactions->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="transactionsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>النوع</th>
                                            <th>المبلغ</th>
                                            <th>الوصف</th>
                                            <th>التاريخ</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($transaction = $transactions->fetch_assoc()): ?>
                                        <tr data-type="<?php echo $transaction['type']; ?>">
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
                                            <td><?php echo htmlspecialchars($transaction['description'] ?? 'غير محدد'); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
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
                </div>
            </div>
        </div>
    </section>
    
    <!-- Add Funds Modal -->
    <div class="modal fade" id="addFundsModal" tabindex="-1" aria-labelledby="addFundsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFundsModalLabel">شحن الرصيد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <p class="mb-0">الحد الأدنى للشحن هو $<?php echo $settings['min_deposit'] ?? 2; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <form id="addFundsForm" action="" method="post" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">المبلغ (بالدولار الأمريكي)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="amount" name="amount" min="<?php echo $settings['min_deposit'] ?? 2; ?>" step="0.01" required placeholder="أدخل المبلغ">
                                    <span class="input-group-text">$</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">طريقة الدفع</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">اختر طريقة الدفع</option>
                                    <?php if ($settings['enable_credit_card'] ?? 'yes' === 'yes'): ?>
                                    <option value="credit_card">البطاقات الائتمانية (فيزا / ماستر كارد)</option>
                                    <?php endif; ?>
                                    <?php if ($settings['enable_usdt'] ?? 'yes' === 'yes'): ?>
                                    <option value="usdt">الدولار الرقمي (USDT)</option>
                                    <?php endif; ?>
                                    <?php if ($settings['enable_binance'] ?? 'yes' === 'yes'): ?>
                                    <option value="binance">Binance Pay</option>
                                    <?php endif; ?>
                                    <?php if ($settings['enable_bank_transfer'] ?? 'yes' === 'yes'): ?>
                                    <option value="bank_transfer">تحويل بنكي</option>
                                    <?php endif; ?>
                                    <?php if ($settings['enable_karimi_bank'] ?? 'yes' === 'yes'): ?>
                                    <option value="karimi_bank">بنك الكريمي (اليمن)</option>
                                    <?php endif; ?>
                                    <?php if ($settings['enable_local_transfer'] ?? 'yes' === 'yes'): ?>
                                    <option value="local_transfer">حوالة محلية (اليمن)</option>
                                    <?php endif; ?>
                                    <?php if ($settings['enable_local_wallet'] ?? 'yes' === 'yes'): ?>
                                    <option value="local_wallet">المحافظ المحلية (اليمن)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Credit Card Payment Form (Hidden by default) -->
                        <div id="credit_card_form" class="payment-form">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="card_number" class="form-label">رقم البطاقة</label>
                                    <input type="text" class="form-control" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="expiry_date" class="form-label">تاريخ انتهاء الصلاحية</label>
                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                </div>
                                <div class="col-md-6">
                                    <label for="cvv" class="form-label">رمز الأمان (CVV)</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" placeholder="XXX">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="card_holder" class="form-label">اسم حامل البطاقة</label>
                                    <input type="text" class="form-control" id="card_holder" name="card_holder">
                                </div>
                            </div>
                        </div>
                        
                        <!-- USDT Payment Form (Hidden by default) -->
                        <div id="usdt_form" class="payment-form">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="alert alert-secondary">
                                        <p class="mb-0">قم بتحويل المبلغ إلى عنوان المحفظة التالي:</p>
                                        <div class="input-group mt-2">
                                            <input type="text" class="form-control" value="<?php echo $settings['usdt_wallet'] ?? 'TKXLMc82ja9frhtP8gULQoJbpGjEUHFCpN'; ?>" readonly>
                                            <button class="btn btn-outline-secondary" type="button" id="copyUsdtAddress">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">شبكة TRON (TRC20)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="usdt_tx_id" class="form-label">رقم عملية التحويل (TXID)</label>
                                    <input type="text" class="form-control" id="usdt_tx_id" name="usdt_tx_id" placeholder="أدخل رقم عملية التحويل">
                                    <small class="text-muted">أدخل رقم عملية التحويل بعد إتمام عملية الدفع</small>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="payment_receipt_usdt" class="form-label">إيصال الدفع (صورة لشاشة التحويل)</label>
                                    <input type="file" class="form-control" id="payment_receipt_usdt" name="payment_receipt" accept="image/*">
                                    <small class="text-muted">الأنواع المسموح بها: JPG، PNG، GIF. الحد الأقصى للحجم: 5 ميجابايت.</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Other payment methods will have their own forms -->
                        <div id="binance_form" class="payment-form">
                            <div class="alert alert-secondary">
                                <p>يرجى التواصل مع خدمة العملاء للحصول على رابط الدفع عبر Binance Pay.</p>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="payment_receipt_binance" class="form-label">إيصال الدفع</label>
                                    <input type="file" class="form-control" id="payment_receipt_binance" name="payment_receipt" accept="image/*">
                                    <small class="text-muted">الأنواع المسموح بها: JPG، PNG، GIF. الحد الأقصى للحجم: 5 ميجابايت.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div id="bank_transfer_form" class="payment-form">
                            <div class="alert alert-secondary">
                                <p>معلومات الحساب البنكي للتحويل:</p>
                                <ul>
                                    <li>اسم البنك: <?php echo $settings['bank_name'] ?? 'بنك المشرق'; ?></li>
                                    <li>اسم الحساب: <?php echo $settings['account_name'] ?? 'متجر مشهور'; ?></li>
                                    <li>رقم الحساب: <?php echo $settings['account_number'] ?? '1234567890'; ?></li>
                                    <li>رقم IBAN: <?php echo $settings['iban'] ?? 'AE123456789012345678'; ?></li>
                                    <li>SWIFT Code: <?php echo $settings['swift_code'] ?? 'MSHQAE123'; ?></li>
                                </ul>
                                <p>بعد إتمام التحويل، يرجى إرفاق صورة إيصال التحويل.</p>
                            </div>
                            <div class="mb-3">
                                <label for="payment_receipt_bank" class="form-label">إيصال التحويل</label>
                                <input type="file" class="form-control" id="payment_receipt_bank" name="payment_receipt" accept="image/*">
                                <small class="text-muted">الأنواع المسموح بها: JPG، PNG، GIF. الحد الأقصى للحجم: 5 ميجابايت.</small>
                            </div>
                        </div>
                        
                        <div id="local_payment_form" class="payment-form">
                            <div class="alert alert-secondary">
                                <p>للدفع عبر الطرق المحلية في اليمن، يرجى التواصل مع خدمة العملاء على الواتساب أو التيليجرام.</p>
                                <p class="mt-2">
                                    <a href="https://wa.me/<?php echo urlencode($settings['whatsapp_number'] ?? '+1234567890'); ?>" class="btn btn-success btn-sm" target="_blank">
                                        <i class="fab fa-whatsapp me-2"></i> واتساب
                                    </a>
                                    <a href="https://t.me/<?php echo $settings['telegram_username'] ?? 'your_telegram_username'; ?>" class="btn btn-primary btn-sm" target="_blank">
                                        <i class="fab fa-telegram me-2"></i> تيليجرام
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label for="payment_receipt_local" class="form-label">إيصال الدفع</label>
                                <input type="file" class="form-control" id="payment_receipt_local" name="payment_receipt" accept="image/*">
                                <small class="text-muted">الأنواع المسموح بها: JPG، PNG، GIF. الحد الأقصى للحجم: 5 ميجابايت.</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" form="addFundsForm" name="submit_payment" class="btn btn-primary">إتمام الدفع</button>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .balance-section {
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
    
    .balance-amount {
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
    
    .balance-amount small {
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .balance-stat-card {
        display: flex;
        align-items: center;
        padding: 1rem;
        border: 1px solid #eee;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .balance-stat-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-5px);
    }
    
    .balance-stat-icon {
        font-size: 2rem;
        color: var(--primary-color);
        margin-left: 1rem;
    }
    
    .balance-stat-content {
        flex: 1;
    }
    
    .balance-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }
    
    .balance-stat-label {
        font-size: 0.9rem;
        margin-bottom: 0;
        color: #666;
    }
    
    .payment-form {
        display: none;
        padding-top: 1rem;
        border-top: 1px solid #eee;
        margin-top: 1rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const paymentMethodSelect = document.getElementById('payment_method');
    const paymentForms = document.querySelectorAll('.payment-form');
    
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            // Hide all payment forms
            paymentForms.forEach(form => {
                form.style.display = 'none';
            });
            
            // Show the selected payment form
            const selectedMethod = this.value;
            
            if (selectedMethod === 'credit_card') {
                document.getElementById('credit_card_form').style.display = 'block';
            } else if (selectedMethod === 'usdt') {
                document.getElementById('usdt_form').style.display = 'block';
            } else if (selectedMethod === 'binance') {
                document.getElementById('binance_form').style.display = 'block';
            } else if (selectedMethod === 'bank_transfer') {
                document.getElementById('bank_transfer_form').style.display = 'block';
            } else if (['karimi_bank', 'local_transfer', 'local_wallet'].includes(selectedMethod)) {
                document.getElementById('local_payment_form').style.display = 'block';
            }
        });
    }
    
    // Copy USDT address to clipboard
    const copyUsdtAddressBtn = document.getElementById('copyUsdtAddress');
    
    if (copyUsdtAddressBtn) {
        copyUsdtAddressBtn.addEventListener('click', function() {
            const usdtAddressInput = this.previousElementSibling;
            usdtAddressInput.select();
            document.execCommand('copy');
            
            // Feedback to user
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    }
    
    // Form validation for payment receipt
    const addFundsForm = document.getElementById('addFundsForm');
    if (addFundsForm) {
        addFundsForm.addEventListener('submit', function(e) {
            const paymentMethod = document.getElementById('payment_method').value;
            const receiptRequired = ['usdt', 'binance', 'bank_transfer', 'karimi_bank', 'local_transfer', 'local_wallet'].includes(paymentMethod);
            
            if (receiptRequired) {
                let fileInputId;
                
                if (paymentMethod === 'usdt') {
                    fileInputId = 'payment_receipt_usdt';
                } else if (paymentMethod === 'binance') {
                    fileInputId = 'payment_receipt_binance';
                } else if (paymentMethod === 'bank_transfer') {
                    fileInputId = 'payment_receipt_bank';
                } else {
                    fileInputId = 'payment_receipt_local';
                }
                
                const paymentReceipt = document.getElementById(fileInputId);
                if (paymentReceipt && (!paymentReceipt.files || paymentReceipt.files.length === 0)) {
                    e.preventDefault();
                    alert('يجب إرفاق إيصال الدفع');
                }
            }
        });
    }
    
    // Transaction filtering
    const filterLinks = document.querySelectorAll('.dropdown-item[data-filter]');
    
    if (filterLinks.length > 0) {
        filterLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const filter = this.getAttribute('data-filter');
                const rows = document.querySelectorAll('#transactionsTable tbody tr');
                
                rows.forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-type') === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Update the dropdown button text
                const filterText = this.textContent;
                document.getElementById('filterDropdown').innerHTML = `<i class="fas fa-filter me-1"></i> ${filterText}`;
            });
        });
    }
});
</script>

<?php include 'footer.php'; ?>