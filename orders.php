<?php
// orders.php
session_start();
$page_title = "طلباتي - متجر مشهور";

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

// Handle order cancellation
if (isset($_POST['cancel_order']) && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    
    // Verify that the order belongs to the user and is in a cancellable state
    $check_order_query = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status IN ('pending')";
    $stmt = $conn->prepare($check_order_query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    
    if ($order_result->num_rows > 0) {
        $order = $order_result->fetch_assoc();
        $amount = $order['amount'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update order status
            $update_order_query = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
            $stmt = $conn->prepare($update_order_query);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            
            // Refund user's balance
            $update_balance_query = "UPDATE users SET balance = balance + ?, in_use = in_use - ? WHERE id = ?";
            $stmt = $conn->prepare($update_balance_query);
            $stmt->bind_param("ddi", $amount, $amount, $user_id);
            $stmt->execute();
            
            // Create refund transaction record
            $refund_desc = "استرداد المبلغ لإلغاء الطلب #" . $order_id;
            $insert_transaction_query = "INSERT INTO transactions (user_id, amount, type, status, description) VALUES (?, ?, 'refund', 'completed', ?)";
            $stmt = $conn->prepare($insert_transaction_query);
            $stmt->bind_param("ids", $user_id, $amount, $refund_desc);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Set success message
            $success_message = "تم إلغاء الطلب واسترداد المبلغ بنجاح.";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error_message = "حدث خطأ أثناء إلغاء الطلب: " . $e->getMessage();
        }
        
        // Refresh user data
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    } else {
        $error_message = "لا يمكن إلغاء هذا الطلب.";
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Prepare the WHERE clause based on the filter
$where_clause = "user_id = ?";
$params = array($user_id);
$types = "i";

if ($status_filter != 'all') {
    $where_clause .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Get orders with pagination
$orders_query = "SELECT o.*, s.name as service_name, s.category_id, c.name as category_name 
                FROM orders o 
                JOIN services s ON o.service_id = s.id 
                JOIN service_categories c ON s.category_id = c.id 
                WHERE $where_clause 
                ORDER BY o.created_at DESC 
                LIMIT ?, ?";
$stmt = $conn->prepare($orders_query);
$params[] = $offset;
$params[] = $per_page;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();

// Get total orders count for pagination
$count_query = "SELECT COUNT(*) as total FROM orders WHERE $where_clause";
$stmt = $conn->prepare($count_query);
// Remove the last two parameters (offset and per_page)
array_pop($params);
array_pop($params);
$stmt->bind_param(substr($types, 0, -2), ...$params);
$stmt->execute();
$count_result = $stmt->get_result()->fetch_assoc();
$total_orders = $count_result['total'];
$total_pages = ceil($total_orders / $per_page);

include 'header.php';
?>

<main>
    <section class="orders-section py-5">
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
                                <a href="orders.php" class="list-group-item list-group-item-action active">
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
                    
                    <!-- Orders Header -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title">طلباتي</h4>
                                <a href="add-order.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i> طلب جديد
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Orders Filter -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form action="orders.php" method="get" class="row align-items-end">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="status" class="form-label">تصفية حسب الحالة</label>
                                    <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>جميع الطلبات</option>
                                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>قيد التنفيذ</option>
                                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>مكتمل</option>
                                        <option value="partial" <?php echo $status_filter == 'partial' ? 'selected' : ''; ?>>جزئي</option>
                                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                                        <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>فشل</option>
                                    </select>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="orders-summary">
                                        <span class="badge bg-primary"><?php echo $total_orders; ?> طلب</span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Orders List -->
                    <div class="card shadow-sm">
                        <div class="card-body">
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
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $order['id']; ?></td>
                                            <td>
                                                <span class="order-service-name"><?php echo htmlspecialchars($order['service_name']); ?></span>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['category_name']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($order['status'] == 'partial'): ?>
                                                <span><?php echo number_format($order['quantity'] - $order['remains']); ?> / <?php echo number_format($order['quantity']); ?></span>
                                                <?php else: ?>
                                                <span><?php echo number_format($order['quantity']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="fw-bold"><?php echo number_format($order['amount'], 2); ?> $</span></td>
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
                                            </td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $order['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $order['id']; ?>">
                                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#orderDetailModal" data-order-id="<?php echo $order['id']; ?>"><i class="fas fa-eye me-2"></i> عرض التفاصيل</a></li>
                                                        <?php if ($order['status'] == 'pending'): ?>
                                                        <li>
                                                            <form method="post" action="" class="d-inline" onsubmit="return confirm('هل أنت متأكد من أنك تريد إلغاء هذا الطلب؟');">
                                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                <button type="submit" name="cancel_order" class="dropdown-item text-danger"><i class="fas fa-times-circle me-2"></i> إلغاء الطلب</button>
                                                            </form>
                                                        </li>
                                                        <?php endif; ?>
                                                        <li><a class="dropdown-item" href="support.php?order_id=<?php echo $order['id']; ?>"><i class="fas fa-question-circle me-2"></i> الدعم الفني</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    if ($start_page > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="?status=' . $status_filter . '&page=1">1</a></li>';
                                        if ($start_page > 2) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="?status=' . $status_filter . '&page=' . $i . '">' . $i . '</a></li>';
                                    }
                                    
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link" href="?status=' . $status_filter . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                    }
                                    ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                            
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <p class="mb-0">لا توجد طلبات <?php echo $status_filter != 'all' ? 'بهذه الحالة' : ''; ?> حتى الآن.</p>
                                <a href="add-order.php" class="btn btn-primary mt-3">إضافة طلب جديد</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailModalLabel">تفاصيل الطلب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                    </div>
                    <div id="orderDetails" class="d-none">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>رقم الطلب:</strong> <span id="orderIdDetail"></span></p>
                                <p><strong>الخدمة:</strong> <span id="serviceNameDetail"></span></p>
                                <p><strong>الكمية:</strong> <span id="quantityDetail"></span></p>
                                <p><strong>المبلغ:</strong> <span id="amountDetail"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>الحالة:</strong> <span id="statusDetail"></span></p>
                                <p><strong>تاريخ الطلب:</strong> <span id="dateDetail"></span></p>
                                <p><strong>الرابط المستهدف:</strong> <a href="#" id="targetUrlDetail" target="_blank"></a></p>
                                <p><strong>العدد الأولي:</strong> <span id="startCountDetail"></span></p>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3 d-none" id="remainsInfo">
                            <p class="mb-0"><strong>المتبقي:</strong> <span id="remainsDetail"></span></p>
                        </div>
                        
                        <hr>
                        
                        <div class="order-progress mt-4 mb-3">
                            <div class="progress">
                                <div class="progress-bar" id="progressBar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small>0%</small>
                                <small>100%</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <a href="#" class="btn btn-primary d-none" id="supportBtn">الدعم الفني</a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .orders-section {
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
    
    .orders-summary {
        display: flex;
        gap: 10px;
    }
    
    .orders-summary .badge {
        font-size: 0.9rem;
        padding: 8px 12px;
    }
    
    .order-service-name {
        font-weight: 600;
    }
    
    .page-link {
        color: var(--primary-color);
    }
    
    .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    /* Order Detail Modal */
    .order-progress .progress {
        height: 10px;
        border-radius: 5px;
    }
    
    .order-progress .progress-bar {
        background-color: var(--primary-color);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Order Detail Modal
    const orderDetailModal = document.getElementById('orderDetailModal');
    if (orderDetailModal) {
        orderDetailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            
            // Reset modal content
            document.querySelector('#orderDetails').classList.add('d-none');
            document.querySelector('.spinner-border').classList.remove('d-none');
            document.querySelector('#supportBtn').classList.add('d-none');
            
            // Fetch order details
            fetch('get_order_details.php?id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    // Hide spinner
                    document.querySelector('.spinner-border').classList.add('d-none');
                    document.querySelector('#orderDetails').classList.remove('d-none');
                    
                    // Populate order details
                    document.getElementById('orderIdDetail').textContent = data.id;
                    document.getElementById('serviceNameDetail').textContent = data.service_name;
                    document.getElementById('quantityDetail').textContent = data.quantity;
                    document.getElementById('amountDetail').textContent = parseFloat(data.amount).toFixed(2) + ' $';
                    
                    // Status with badge
                    let statusText = '';
                    let statusClass = '';
                    
                    switch (data.status) {
                        case 'pending':
                            statusText = 'قيد الانتظار';
                            statusClass = 'bg-warning';
                            break;
                        case 'processing':
                            statusText = 'قيد التنفيذ';
                            statusClass = 'bg-info';
                            break;
                        case 'completed':
                            statusText = 'مكتمل';
                            statusClass = 'bg-success';
                            break;
                        case 'cancelled':
                            statusText = 'ملغي';
                            statusClass = 'bg-secondary';
                            break;
                        case 'failed':
                            statusText = 'فشل';
                            statusClass = 'bg-danger';
                            break;
                        case 'partial':
                            statusText = 'جزئي';
                            statusClass = 'bg-primary';
                            break;
                    }
                    
                    document.getElementById('statusDetail').innerHTML = '<span class="badge ' + statusClass + '">' + statusText + '</span>';
                    document.getElementById('dateDetail').textContent = new Date(data.created_at).toLocaleString('ar-SA');
                    
                    const targetUrl = document.getElementById('targetUrlDetail');
                    targetUrl.textContent = data.target_url;
                    targetUrl.setAttribute('href', data.target_url);
                    
                    document.getElementById('startCountDetail').textContent = data.start_count;
                    
                    // Show remains info for partial orders
                    if (data.status === 'partial') {
                        document.getElementById('remainsInfo').classList.remove('d-none');
                        document.getElementById('remainsDetail').textContent = data.remains;
                    } else {
                        document.getElementById('remainsInfo').classList.add('d-none');
                    }
                    
                    // Calculate progress
                    let progress = 0;
                    if (data.status === 'completed') {
                        progress = 100;
                    } else if (data.status === 'partial') {
                        progress = Math.round(((data.quantity - data.remains) / data.quantity) * 100);
                    } else if (data.status === 'processing') {
                        progress = 50; // Arbitrary value for processing
                    }
                    
                    const progressBar = document.getElementById('progressBar');
                    progressBar.style.width = progress + '%';
                    progressBar.setAttribute('aria-valuenow', progress);
                    
                    // Show support button
                    const supportBtn = document.getElementById('supportBtn');
                    supportBtn.classList.remove('d-none');
                    supportBtn.setAttribute('href', 'support.php?order_id=' + data.id);
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    document.querySelector('.spinner-border').classList.add('d-none');
                    document.getElementById('orderDetails').innerHTML = '<div class="alert alert-danger">حدث خطأ أثناء تحميل بيانات الطلب. يرجى المحاولة مرة أخرى.</div>';
                    document.getElementById('orderDetails').classList.remove('d-none');
                });
        });
    }
});
</script>

<?php include 'footer.php'; ?>