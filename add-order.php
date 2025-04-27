<?php
// add-order.php
session_start();
$page_title = "إضافة طلب جديد - متجر مشهور";

// Include referral functions
include_once 'referral_functions.php';

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

// Get all categories
$categories_query = "SELECT * FROM service_categories ORDER BY display_order";
$categories = $conn->query($categories_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $target_url = filter_input(INPUT_POST, 'target_url', FILTER_SANITIZE_URL);
    
    // Validate form data
    $errors = [];
    
    if (empty($service_id)) {
        $errors[] = "يرجى اختيار الخدمة";
    }
    
    if (empty($quantity)) {
        $errors[] = "يرجى تحديد الكمية";
    }
    
    if (empty($target_url)) {
        $errors[] = "يرجى إدخال الرابط المستهدف";
    }
    
    // Get service details
    if (empty($errors)) {
        $service_query = "SELECT * FROM services WHERE id = ?";
        $stmt = $conn->prepare($service_query);
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $service = $stmt->get_result()->fetch_assoc();
        
        if (!$service) {
            $errors[] = "الخدمة غير موجودة";
        } else {
            // Validate quantity
            if ($quantity < $service['min_quantity']) {
                $errors[] = "الحد الأدنى للكمية هو " . $service['min_quantity'];
            }
            
            if ($quantity > $service['max_quantity']) {
                $errors[] = "الحد الأقصى للكمية هو " . $service['max_quantity'];
            }
            
            // Calculate order amount
            $amount = ($service['price'] * $quantity) / $service['min_quantity'];
            
            // Check if user has enough balance
            if ($amount > $user['balance']) {
                $errors[] = "رصيدك غير كاف. الرصيد المطلوب: $" . number_format($amount, 2);
            }
        }
    }
    
    // Process order
    if (empty($errors)) {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $insert_order_query = "INSERT INTO orders (user_id, service_id, quantity, amount, target_url, status) VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($insert_order_query);
            $stmt->bind_param("iiids", $user_id, $service_id, $quantity, $amount, $target_url);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Update user balance
            $update_balance_query = "UPDATE users SET balance = balance - ?, in_use = in_use + ? WHERE id = ?";
            $stmt = $conn->prepare($update_balance_query);
            $stmt->bind_param("ddi", $amount, $amount, $user_id);
            $stmt->execute();
            
            // Create purchase transaction
            $description = "طلب جديد #" . $order_id . " - " . $service['name'];
            $insert_transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) VALUES (?, ?, 'purchase', 'completed', ?)";
            $stmt = $conn->prepare($insert_transaction_query);
            $stmt->bind_param("ids", $user_id, $amount, $description);
            $stmt->execute();
            
            // Process referral reward if applicable
            processOrderReferralReward($user_id, $amount, $order_id, false);
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to order details page
            header("Location: orders.php?success=1");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "حدث خطأ أثناء معالجة الطلب: " . $e->getMessage();
        }
    }
}

// Get selected category (if any)
$selected_category = isset($_GET['category']) ? $_GET['category'] : null;

// Get services by category
if ($selected_category) {
    $services_query = "SELECT s.*, c.name as category_name 
                      FROM services s 
                      JOIN service_categories c ON s.category_id = c.id 
                      WHERE c.slug = ? 
                      ORDER BY s.display_order, s.name";
    $stmt = $conn->prepare($services_query);
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $services = $stmt->get_result();
} else {
    // Get popular services if no category is selected
    $services_query = "SELECT s.*, c.name as category_name 
                      FROM services s 
                      JOIN service_categories c ON s.category_id = c.id 
                      WHERE s.is_popular = 1 
                      ORDER BY s.display_order, s.name";
    $services = $conn->query($services_query);
}

include 'header.php';
?>

<main>
    <section class="add-order-section py-5">
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
                                <a href="add-order.php" class="list-group-item list-group-item-action active">
                                    <i class="fas fa-plus-circle me-2"></i> طلب جديد
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
                    
                    <!-- Current Balance -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-body">
                            <h5 class="card-title">رصيدك الحالي</h5>
                            <h3 class="text-primary"><?php echo number_format($user['balance'], 2); ?> $</h3>
                            <a href="balance.php" class="btn btn-outline-primary w-100 mt-2">شحن الرصيد</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Order Form -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h4 class="card-title mb-0">طلب جديد</h4>
                        </div>
                        <div class="card-body">
                            <form id="orderForm" method="post" action="">
                                <div class="row mb-3 categories-selector">
                                    <div class="col-12">
                                        <label class="form-label">اختر الفئة</label>
                                        <div class="categories-row">
                                            <?php while ($category = $categories->fetch_assoc()): ?>
                                            <a href="?category=<?php echo htmlspecialchars($category['slug']); ?>" class="category-item <?php echo $selected_category == $category['slug'] ? 'active' : ''; ?>">
                                                <div class="category-icon">
                                                    <img src="<?php echo htmlspecialchars($category['icon']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                                </div>
                                                <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
                                            </a>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="service_id" class="form-label">اختر الخدمة</label>
                                    <select class="form-select" id="service_id" name="service_id" required>
                                        <option value="">-- اختر الخدمة --</option>
                                        <?php if ($services->num_rows > 0): ?>
                                            <?php while ($service = $services->fetch_assoc()): ?>
                                            <option value="<?php echo $service['id']; ?>" data-min="<?php echo $service['min_quantity']; ?>" data-max="<?php echo $service['max_quantity']; ?>" data-price="<?php echo $service['price']; ?>" data-unit="<?php echo $service['min_quantity']; ?>">
                                                <?php echo htmlspecialchars($service['name']); ?> - $<?php echo number_format($service['price'], 3); ?> / <?php echo $service['min_quantity']; ?>
                                            </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="target_url" class="form-label">الرابط المستهدف</label>
                                    <input type="url" class="form-control" id="target_url" name="target_url" placeholder="https://..." required>
                                    <small class="text-muted" id="urlHelp">أدخل رابط المنشور أو الحساب المراد زيادة المتابعين/التفاعل عليه</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">الكمية</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" min="10" required>
                                    <small class="text-muted" id="quantityHelp">الحد الأدنى: <span id="minQuantity">10</span> - الحد الأقصى: <span id="maxQuantity">10000</span></small>
                                </div>
                                
                                <div class="service-info mb-4 d-none" id="serviceInfo">
                                    <div class="card bg-light">
                                        <div class="card-body p-3">
                                            <h5 class="card-title">معلومات الخدمة</h5>
                                            <ul class="list-unstyled mb-0">
                                                <li><i class="fas fa-check-circle text-success me-2"></i> وقت البدء: <span id="startTime"></span></li>
                                                <li><i class="fas fa-check-circle text-success me-2"></i> السرعة: <span id="speed"></span></li>
                                                <li><i class="fas fa-check-circle text-success me-2"></i> الجودة: <span id="quality"></span></li>
                                                <li><i class="fas fa-check-circle text-success me-2"></i> الضمان: <span id="guarantee"></span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="order-summary p-3 border rounded mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>المجموع:</span>
                                        <span class="fw-bold" id="totalPrice">$0.00</span>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">تأكيد الطلب</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Services Table -->
                    <?php if ($services->num_rows > 0): ?>
                    <?php
                    // Reset the result pointer
                    $services->data_seek(0);
                    ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h4 class="card-title mb-0">قائمة الخدمات <?php echo $selected_category ? '- ' . $services->fetch_assoc()['category_name'] : 'المميزة'; ?></h4>
                            <?php $services->data_seek(0); // Reset again ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الخدمة</th>
                                            <th>السعر</th>
                                            <th>الحد الأدنى</th>
                                            <th>الحد الأقصى</th>
                                            <th>وقت البدء</th>
                                            <th>الجودة</th>
                                            <th>الضمان</th>
                                            <th>طلب</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($service = $services->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <span class="service-name"><?php echo htmlspecialchars($service['name']); ?></span>
                                            </td>
                                            <td>$<?php echo number_format($service['price'], 3); ?> / <?php echo $service['min_quantity']; ?></td>
                                            <td><?php echo number_format($service['min_quantity']); ?></td>
                                            <td><?php echo number_format($service['max_quantity']); ?></td>
                                            <td><?php echo htmlspecialchars($service['start_time']); ?></td>
                                            <td>
                                                <?php
                                                $quality_text = '';
                                                $quality_class = '';
                                                
                                                switch ($service['quality']) {
                                                    case 'low':
                                                        $quality_text = 'عادية';
                                                        $quality_class = 'text-warning';
                                                        break;
                                                    case 'medium':
                                                        $quality_text = 'متوسطة';
                                                        $quality_class = 'text-info';
                                                        break;
                                                    case 'high':
                                                        $quality_text = 'عالية';
                                                        $quality_class = 'text-success';
                                                        break;
                                                    case 'premium':
                                                        $quality_text = 'ممتازة';
                                                        $quality_class = 'text-primary';
                                                        break;
                                                }
                                                ?>
                                                <span class="<?php echo $quality_class; ?>"><?php echo $quality_text; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($service['guarantee_days'] > 0): ?>
                                                <span class="badge bg-success"><?php echo $service['guarantee_days']; ?> يوم</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">لا يوجد</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary select-service" data-service-id="<?php echo $service['id']; ?>">
                                                    اختيار
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <p class="mb-0">لا توجد خدمات متاحة في هذه الفئة حالياً.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .add-order-section {
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
    
    /* Categories Selector */
    .categories-row {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 10px;
        padding-bottom: 10px;
    }
    
    .category-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 80px;
        text-decoration: none;
        color: var(--text-color);
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #eee;
        transition: all 0.3s ease;
    }
    
    .category-item:hover, .category-item.active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-5px);
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
        text-align: center;
    }
    
    /* Service Info */
    .service-info ul li {
        margin-bottom: 0.5rem;
    }
    
    /* Service Table */
    .service-name {
        font-weight: 600;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service_id');
    const quantityInput = document.getElementById('quantity');
    const minQuantitySpan = document.getElementById('minQuantity');
    const maxQuantitySpan = document.getElementById('maxQuantity');
    const totalPriceSpan = document.getElementById('totalPrice');
    const serviceInfo = document.getElementById('serviceInfo');
    
    // Service selection change
    if (serviceSelect) {
        serviceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                const minQuantity = parseInt(selectedOption.dataset.min);
                const maxQuantity = parseInt(selectedOption.dataset.max);
                const price = parseFloat(selectedOption.dataset.price);
                const unit = parseInt(selectedOption.dataset.unit);
                
                // Update min/max values
                minQuantitySpan.textContent = minQuantity;
                maxQuantitySpan.textContent = maxQuantity;
                
                // Update quantity input constraints
                quantityInput.min = minQuantity;
                quantityInput.max = maxQuantity;
                quantityInput.value = minQuantity;
                
                // Calculate total price
                calculateTotal(price, unit);
                
                // Fetch service details
                fetchServiceDetails(selectedOption.value);
            } else {
                // Reset values
                minQuantitySpan.textContent = '10';
                maxQuantitySpan.textContent = '10000';
                quantityInput.min = 10;
                quantityInput.max = 10000;
                quantityInput.value = '';
                totalPriceSpan.textContent = '$0.00';
                serviceInfo.classList.add('d-none');
            }
        });
    }
    
    // Quantity input change
    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            
            if (selectedOption.value) {
                const price = parseFloat(selectedOption.dataset.price);
                const unit = parseInt(selectedOption.dataset.unit);
                
                calculateTotal(price, unit);
            }
        });
    }
    
    // Select service buttons
    const selectServiceButtons = document.querySelectorAll('.select-service');
    
    if (selectServiceButtons.length > 0) {
        selectServiceButtons.forEach(button => {
            button.addEventListener('click', function() {
                const serviceId = this.getAttribute('data-service-id');
                
                // Select the service in the dropdown
                for (let i = 0; i < serviceSelect.options.length; i++) {
                    if (serviceSelect.options[i].value === serviceId) {
                        serviceSelect.selectedIndex = i;
                        serviceSelect.dispatchEvent(new Event('change'));
                        
                        // Scroll to the form
                        document.getElementById('orderForm').scrollIntoView({ behavior: 'smooth' });
                        break;
                    }
                }
            });
        });
    }
    
    // Calculate total price
    function calculateTotal(price, unit) {
        const quantity = parseInt(quantityInput.value) || 0;
        const total = (price * quantity) / unit;
        totalPriceSpan.textContent = '$' + total.toFixed(2);
    }
    
    // Fetch service details
    function fetchServiceDetails(serviceId) {
        fetch('get_service_details.php?id=' + serviceId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('startTime').textContent = data.start_time;
                document.getElementById('speed').textContent = data.speed;
                
                let qualityText = '';
                switch (data.quality) {
                    case 'low':
                        qualityText = 'عادية';
                        break;
                    case 'medium':
                        qualityText = 'متوسطة';
                        break;
                    case 'high':
                        qualityText = 'عالية';
                        break;
                    case 'premium':
                        qualityText = 'ممتازة';
                        break;
                }
                document.getElementById('quality').textContent = qualityText;
                
                if (data.guarantee_days > 0) {
                    document.getElementById('guarantee').textContent = data.guarantee_days + ' يوم';
                } else {
                    document.getElementById('guarantee').textContent = 'لا يوجد';
                }
                
                serviceInfo.classList.remove('d-none');
            })
            .catch(error => {
                console.error('Error fetching service details:', error);
                serviceInfo.classList.add('d-none');
            });
    }
});
</script>

<?php include 'footer.php'; ?>