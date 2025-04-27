<?php
// admin/orders.php - Order management for admins

// This file is included by admin.php so we don't need to declare the same functions again
// We'll focus on providing the orders UI and specialized functionality

// Get orders statistics for filters
$all_orders_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_orders_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$processing_orders_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")->fetch_assoc()['count'];
$completed_orders_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'")->fetch_assoc()['count'];
$partial_orders_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'partial'")->fetch_assoc()['count'];
$cancelled_orders_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'")->fetch_assoc()['count'];
$failed_orders_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'failed'")->fetch_assoc()['count'];
?>

<!-- Orders Management -->
<div class="orders-section">
    <h1 class="mb-4">إدارة الطلبات</h1>
    
    <!-- Search Box -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="input-group">
                        <input type="text" class="form-control" id="orderSearch" placeholder="ابحث باسم المستخدم أو الخدمة أو رقم الطلب...">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-md-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshOrders">
                            <i class="fas fa-sync-alt"></i> تحديث
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="exportOrdersCSV">
                            <i class="fas fa-file-export"></i> تصدير CSV
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#orderStatsModal">
                            <i class="fas fa-chart-bar"></i> إحصائيات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Statistics Modal -->
    <div class="modal fade" id="orderStatsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إحصائيات الطلبات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">إجمالي الطلبات</h5>
                                    <h2 class="display-4"><?php echo number_format($all_orders_count); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-warning bg-opacity-25">
                                <div class="card-body text-center">
                                    <h5 class="card-title">قيد الانتظار</h5>
                                    <h2 class="display-4"><?php echo number_format($pending_orders_count); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info bg-opacity-25">
                                <div class="card-body text-center">
                                    <h5 class="card-title">قيد التنفيذ</h5>
                                    <h2 class="display-4"><?php echo number_format($processing_orders_count); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success bg-opacity-25">
                                <div class="card-body text-center">
                                    <h5 class="card-title">مكتملة</h5>
                                    <h2 class="display-4"><?php echo number_format($completed_orders_count); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary bg-opacity-25">
                                <div class="card-body text-center">
                                    <h5 class="card-title">جزئية</h5>
                                    <h2 class="display-4"><?php echo number_format($partial_orders_count); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-secondary bg-opacity-25">
                                <div class="card-body text-center">
                                    <h5 class="card-title">ملغية / فاشلة</h5>
                                    <h2 class="display-4"><?php echo number_format($cancelled_orders_count + $failed_orders_count); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php
                    // Get revenue statistics
                    $total_revenue = $conn->query("SELECT SUM(amount) as total FROM orders WHERE status IN ('completed', 'partial')")->fetch_assoc()['total'] ?? 0;
                    $today_revenue = $conn->query("SELECT SUM(amount) as total FROM orders WHERE status IN ('completed', 'partial') AND DATE(updated_at) = CURDATE()")->fetch_assoc()['total'] ?? 0;
                    $month_revenue = $conn->query("SELECT SUM(amount) as total FROM orders WHERE status IN ('completed', 'partial') AND MONTH(updated_at) = MONTH(CURDATE()) AND YEAR(updated_at) = YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
                    ?>
                    
                    <hr>
                    <h5>إيرادات الطلبات</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <h5 class="card-title">إجمالي الإيرادات</h5>
                                    <h2 class="display-6">$<?php echo number_format($total_revenue, 2); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <h5 class="card-title">إيرادات اليوم</h5>
                                    <h2 class="display-6">$<?php echo number_format($today_revenue, 2); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <h5 class="card-title">إيرادات الشهر</h5>
                                    <h2 class="display-6">$<?php echo number_format($month_revenue, 2); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <ul class="nav nav-tabs" id="ordersTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-orders" type="button" role="tab" aria-controls="all-orders" aria-selected="true">
                        جميع الطلبات
                        <span class="badge bg-secondary"><?php echo $all_orders_count; ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-orders" type="button" role="tab" aria-controls="pending-orders" aria-selected="false">
                        قيد الانتظار
                        <span class="badge bg-warning"><?php echo $pending_orders_count; ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing-orders" type="button" role="tab" aria-controls="processing-orders" aria-selected="false">
                        قيد التنفيذ
                        <span class="badge bg-info"><?php echo $processing_orders_count; ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-orders" type="button" role="tab" aria-controls="completed-orders" aria-selected="false">
                        مكتملة
                        <span class="badge bg-success"><?php echo $completed_orders_count; ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="partial-tab" data-bs-toggle="tab" data-bs-target="#partial-orders" type="button" role="tab" aria-controls="partial-orders" aria-selected="false">
                        جزئية
                        <span class="badge bg-primary"><?php echo $partial_orders_count; ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled-orders" type="button" role="tab" aria-controls="cancelled-orders" aria-selected="false">
                        ملغية
                        <span class="badge bg-secondary"><?php echo $cancelled_orders_count; ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="failed-tab" data-bs-toggle="tab" data-bs-target="#failed-orders" type="button" role="tab" aria-controls="failed-orders" aria-selected="false">
                        فاشلة
                        <span class="badge bg-danger"><?php echo $failed_orders_count; ?></span>
                    </button>
                </li>
            </ul>
            
            <div class="tab-content mt-4" id="ordersTabContent">
                <!-- All Orders Tab -->
                <div class="tab-pane fade show active" id="all-orders" role="tabpanel" aria-labelledby="all-tab">
                    <?php
                    try {
                        $all_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                          FROM orders o 
                                          JOIN services s ON o.service_id = s.id 
                                          JOIN users u ON o.user_id = u.id 
                                          ORDER BY o.created_at DESC";
                        $all_orders = $conn->query($all_orders_query);
                        
                        if (!$all_orders) {
                            throw new Exception("Error executing orders query: " . $conn->error);
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</div>';
                        $all_orders = null;
                    }
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover datatable" id="allOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($all_orders && $all_orders->num_rows > 0): ?>
                                <?php while ($order = $all_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td>
                                        <?php if (isset($order['status']) && $order['status'] == 'partial'): ?>
                                        <span><?php echo number_format(isset($order['quantity']) && isset($order['remains']) ? ($order['quantity'] - $order['remains']) : 0); ?> / <?php echo number_format($order['quantity'] ?? 0); ?></span>
                                        <?php else: ?>
                                        <span><?php echo number_format($order['quantity'] ?? 0); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td>
                                        <?php 
                                        $status_class = '';
                                        $status_text = '';
                                        
                                        switch ($order['status'] ?? '') {
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
                                            default:
                                                $status_class = 'bg-secondary';
                                                $status_text = 'غير معروف';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td><?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="admin.php?section=users&action=view&id=<?php echo $order['user_id']; ?>"><i class="fas fa-user me-2"></i> عرض المستخدم</a></li>
                                                <li><a class="dropdown-item" href="admin.php?section=notifications&user_id=<?php echo $order['user_id']; ?>"><i class="fas fa-bell me-2"></i> إرسال إشعار</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                <li><a class="dropdown-item text-success" data-bs-toggle="modal" data-bs-target="#startProcessingModal<?php echo $order['id']; ?>" href="#"><i class="fas fa-play me-2"></i> بدء التنفيذ</a></li>
                                                <li><a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal<?php echo $order['id']; ?>" href="#"><i class="fas fa-times me-2"></i> إلغاء الطلب</a></li>
                                                <?php endif; ?>
                                                <?php if ($order['status'] === 'processing'): ?>
                                                <li><a class="dropdown-item text-success" data-bs-toggle="modal" data-bs-target="#completeOrderModal<?php echo $order['id']; ?>" href="#"><i class="fas fa-check me-2"></i> إكمال الطلب</a></li>
                                                <li><a class="dropdown-item text-primary" data-bs-toggle="modal" data-bs-target="#partialOrderModal<?php echo $order['id']; ?>" href="#"><i class="fas fa-percentage me-2"></i> تسليم جزئي</a></li>
                                                <li><a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#failOrderModal<?php echo $order['id']; ?>" href="#"><i class="fas fa-exclamation-triangle me-2"></i> فشل الطلب</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Order Update Modal -->
                                <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1" aria-labelledby="orderModalLabel<?php echo $order['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="orderModalLabel<?php echo $order['id']; ?>">تحديث حالة الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">المستخدم</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['username']); ?>" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">الخدمة</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['service_name']); ?>" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">الكمية</label>
                                                        <input type="text" class="form-control" value="<?php echo number_format($order['quantity'] ?? 0); ?>" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">الرابط المستهدف</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['target_url'] ?? ''); ?>" readonly>
                                                        <div class="mt-2">
                                                            <a href="<?php echo htmlspecialchars($order['target_url'] ?? '#'); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-external-link-alt me-1"></i> فتح الرابط
                                                            </a>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="status<?php echo $order['id']; ?>" class="form-label">الحالة</label>
                                                        <select class="form-select" id="status<?php echo $order['id']; ?>" name="status" required>
                                                            <option value="pending" <?php echo ($order['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                                            <option value="processing" <?php echo ($order['status'] ?? '') === 'processing' ? 'selected' : ''; ?>>قيد التنفيذ</option>
                                                            <option value="completed" <?php echo ($order['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>مكتمل</option>
                                                            <option value="partial" <?php echo ($order['status'] ?? '') === 'partial' ? 'selected' : ''; ?>>جزئي</option>
                                                            <option value="cancelled" <?php echo ($order['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                                                            <option value="failed" <?php echo ($order['status'] ?? '') === 'failed' ? 'selected' : ''; ?>>فشل</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3 start-count-field" id="startCountField<?php echo $order['id']; ?>" style="display: none;">
                                                        <label for="start_count<?php echo $order['id']; ?>" class="form-label">العدد الأولي</label>
                                                        <input type="number" class="form-control" id="start_count<?php echo $order['id']; ?>" name="start_count" min="0" value="<?php echo $order['start_count'] ?? 0; ?>">
                                                        <div class="form-text">أدخل العدد الأولي للمتابعين/المشاهدات قبل بدء الخدمة.</div>
                                                    </div>
                                                    
                                                    <div class="mb-3 partial-remains-field" id="partialRemains<?php echo $order['id']; ?>" style="display: <?php echo ($order['status'] ?? '') === 'partial' ? 'block' : 'none'; ?>;">
                                                        <label for="remains<?php echo $order['id']; ?>" class="form-label">الكمية المتبقية</label>
                                                        <input type="number" class="form-control" id="remains<?php echo $order['id']; ?>" name="remains" value="<?php echo $order['remains'] ?? 0; ?>" min="1" max="<?php echo isset($order['quantity']) ? $order['quantity'] - 1 : 0; ?>">
                                                        <div class="form-text">يجب أن تكون الكمية المتبقية أقل من الكمية الإجمالية.</div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-primary">تحديث الحالة</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Order Details Modal -->
                                <div class="modal fade" id="orderDetailsModal<?php echo $order['id']; ?>" tabindex="-1" aria-labelledby="orderDetailsModalLabel<?php echo $order['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="orderDetailsModalLabel<?php echo $order['id']; ?>">تفاصيل الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>رقم الطلب:</strong> <?php echo $order['id']; ?></p>
                                                        <p><strong>المستخدم:</strong> <a href="admin.php?section=users&action=view&id=<?php echo $order['user_id']; ?>"><?php echo htmlspecialchars($order['username']); ?></a></p>
                                                        <p><strong>الخدمة:</strong> <?php echo htmlspecialchars($order['service_name']); ?></p>
                                                        <p><strong>الكمية:</strong> <?php echo number_format($order['quantity'] ?? 0); ?></p>
                                                        <p><strong>المبلغ:</strong> $<?php echo number_format($order['amount'] ?? 0, 2); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>الحالة:</strong> <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></p>
                                                        <p><strong>تاريخ الطلب:</strong> <?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></p>
                                                        <p><strong>آخر تحديث:</strong> <?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></p>
                                                        <p><strong>الرابط المستهدف:</strong> <a href="<?php echo htmlspecialchars($order['target_url'] ?? ''); ?>" target="_blank"><?php echo htmlspecialchars($order['target_url'] ?? ''); ?></a></p>
                                                        <p><strong>العدد الأولي:</strong> <?php echo number_format($order['start_count'] ?? 0); ?></p>
                                                        
                                                        <?php if (isset($order['status']) && $order['status'] === 'partial'): ?>
                                                        <p><strong>المتبقي:</strong> <?php echo number_format($order['remains'] ?? 0); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <?php if (isset($order['status']) && ($order['status'] === 'partial' || $order['status'] === 'processing')): ?>
                                                <div class="mt-4">
                                                    <h6>تقدم الطلب</h6>
                                                    <div class="progress">
                                                        <?php 
                                                        $progress = 0;
                                                        if ($order['status'] === 'partial' && isset($order['quantity']) && isset($order['remains']) && $order['quantity'] > 0) {
                                                            $progress = round((($order['quantity'] - $order['remains']) / $order['quantity']) * 100);
                                                        } elseif ($order['status'] === 'processing') {
                                                            $progress = 50; // Processing is halfway
                                                        } elseif ($order['status'] === 'completed') {
                                                            $progress = 100;
                                                        }
                                                        ?>
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progress; ?>%</div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <!-- Order History Section -->
                                                <div class="mt-4">
                                                    <h6>سجل التحديثات</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>التاريخ</th>
                                                                    <th>الحالة السابقة</th>
                                                                    <th>الحالة الجديدة</th>
                                                                    <th>بواسطة</th>
                                                                    <th>ملاحظات</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                // Fetch order history
                                                                $history_query = "SELECT * FROM order_history 
                                                                               WHERE order_id = ? 
                                                                               ORDER BY changed_at DESC 
                                                                               LIMIT 5";
                                                                $stmt = $conn->prepare($history_query);
                                                                $stmt->bind_param("i", $order['id']);
                                                                $stmt->execute();
                                                                $history_result = $stmt->get_result();
                                                                
                                                                if ($history_result && $history_result->num_rows > 0):
                                                                    while ($history = $history_result->fetch_assoc()):
                                                                        // Translate status codes to Arabic
                                                                        $old_status_text = '';
                                                                        $new_status_text = '';
                                                                        
                                                                        switch ($history['old_status']) {
                                                                            case 'pending': $old_status_text = 'قيد الانتظار'; break;
                                                                            case 'processing': $old_status_text = 'قيد التنفيذ'; break;
                                                                            case 'completed': $old_status_text = 'مكتمل'; break;
                                                                            case 'partial': $old_status_text = 'جزئي'; break;
                                                                            case 'cancelled': $old_status_text = 'ملغي'; break;
                                                                            case 'failed': $old_status_text = 'فشل'; break;
                                                                            default: $old_status_text = $history['old_status'];
                                                                        }
                                                                        
                                                                        switch ($history['new_status']) {
                                                                            case 'pending': $new_status_text = 'قيد الانتظار'; break;
                                                                            case 'processing': $new_status_text = 'قيد التنفيذ'; break;
                                                                            case 'completed': $new_status_text = 'مكتمل'; break;
                                                                            case 'partial': $new_status_text = 'جزئي'; break;
                                                                            case 'cancelled': $new_status_text = 'ملغي'; break;
                                                                            case 'failed': $new_status_text = 'فشل'; break;
                                                                            default: $new_status_text = $history['new_status'];
                                                                        }
                                                                ?>
                                                                <tr>
                                                                    <td><?php echo date('Y-m-d H:i', strtotime($history['changed_at'])); ?></td>
                                                                    <td><?php echo $old_status_text; ?></td>
                                                                    <td><?php echo $new_status_text; ?></td>
                                                                    <td><?php echo htmlspecialchars($history['changed_by']); ?></td>
                                                                    <td><?php echo htmlspecialchars($history['notes']); ?></td>
                                                                </tr>
                                                                <?php 
                                                                    endwhile;
                                                                else:
                                                                ?>
                                                                <tr>
                                                                    <td colspan="5" class="text-center">لا يوجد سجل تحديثات بعد</td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>" data-bs-dismiss="modal">
                                                    <i class="fas fa-edit me-1"></i> تحديث الحالة
                                                </button>
                                                <a href="admin.php?section=notifications&user_id=<?php echo $order['user_id']; ?>" class="btn btn-warning">
                                                    <i class="fas fa-bell me-1"></i> إرسال إشعار
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Start Processing Modal -->
                                <div class="modal fade" id="startProcessingModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">بدء تنفيذ الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد بدء تنفيذ هذا الطلب؟</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="processing">
                                                    
                                                    <div class="mb-3">
                                                        <label for="start_count_modal<?php echo $order['id']; ?>" class="form-label">العدد الأولي</label>
                                                        <input type="number" class="form-control" id="start_count_modal<?php echo $order['id']; ?>" name="start_count" min="0" value="0">
                                                        <div class="form-text">أدخل العدد الأولي للمتابعين/المشاهدات قبل بدء الخدمة.</div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-success">بدء التنفيذ</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cancel Order Modal -->
                                <div class="modal fade" id="cancelOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">إلغاء الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد إلغاء هذا الطلب؟ سيتم استرداد المبلغ للمستخدم.</p>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    سيتم إعادة مبلغ $<?php echo number_format($order['amount'] ?? 0, 2); ?> إلى رصيد المستخدم <?php echo htmlspecialchars($order['username']); ?>.
                                                </div>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-danger">تأكيد الإلغاء</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Complete Order Modal -->
                                <div class="modal fade" id="completeOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">إكمال الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد تعيين حالة هذا الطلب كمكتمل؟</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-success">تأكيد الإكمال</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Partial Order Modal -->
                                <div class="modal fade" id="partialOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تسليم جزئي للطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>استخدم هذا الخيار إذا لم تتمكن من إكمال الطلب بالكامل. سيتم استرداد المبلغ المتبقي للمستخدم.</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="partial">
                                                    
                                                    <div class="mb-3">
                                                        <label for="partial_remains<?php echo $order['id']; ?>" class="form-label">الكمية المتبقية</label>
                                                        <input type="number" class="form-control" id="partial_remains<?php echo $order['id']; ?>" name="remains" min="1" max="<?php echo isset($order['quantity']) ? $order['quantity'] - 1 : 0; ?>" required>
                                                        <div class="form-text">أدخل عدد الوحدات التي لم يتم تسليمها. يجب أن تكون أقل من إجمالي الكمية (<?php echo number_format($order['quantity'] ?? 0); ?>).</div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-primary">تأكيد التسليم الجزئي</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Fail Order Modal -->
                                <div class="modal fade" id="failOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">فشل الطلب #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد تعيين حالة هذا الطلب كفاشل؟ سيتم استرداد المبلغ للمستخدم.</p>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    سيتم إعادة مبلغ $<?php echo number_format($order['amount'] ?? 0, 2); ?> إلى رصيد المستخدم <?php echo htmlspecialchars($order['username']); ?>.
                                                </div>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="failed">
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="update_order_status" class="btn btn-danger">تأكيد الفشل</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">لا توجد طلبات حتى الآن</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pending Orders Tab -->
                <div class="tab-pane fade" id="pending-orders" role="tabpanel" aria-labelledby="pending-tab">
                    <?php
                    try {
                        $pending_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                               FROM orders o 
                                               JOIN services s ON o.service_id = s.id 
                                               JOIN users u ON o.user_id = u.id 
                                               WHERE o.status = 'pending' 
                                               ORDER BY o.created_at DESC";
                        $pending_orders = $conn->query($pending_orders_query);
                        
                        if (!$pending_orders) {
                            throw new Exception("Error executing pending orders query: " . $conn->error);
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</div>';
                        $pending_orders = null;
                    }
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover datatable" id="pendingOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pending_orders && $pending_orders->num_rows > 0): ?>
                                <?php while ($order = $pending_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#startProcessingModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد طلبات معلقة</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Processing Orders Tab -->
                <div class="tab-pane fade" id="processing-orders" role="tabpanel" aria-labelledby="processing-tab">
                    <!-- Similar structure with processing orders query -->
                    <div class="table-responsive">
                        <table class="table table-hover datatable" id="processingOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $processing_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                              FROM orders o 
                                                              JOIN services s ON o.service_id = s.id 
                                                              JOIN users u ON o.user_id = u.id 
                                                              WHERE o.status = 'processing' 
                                                              ORDER BY o.created_at DESC";
                                    $processing_orders = $conn->query($processing_orders_query);
                                    
                                    if (!$processing_orders) {
                                        throw new Exception("Error executing processing orders query: " . $conn->error);
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</div>';
                                    $processing_orders = null;
                                }
                                
                                if ($processing_orders && $processing_orders->num_rows > 0):
                                while ($order = $processing_orders->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#completeOrderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#partialOrderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-percentage"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#failOrderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد طلبات قيد التنفيذ</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Completed, Partial, Cancelled, and Failed Tabs (similar structure) -->
                <!-- Completed Orders Tab -->
                <div class="tab-pane fade" id="completed-orders" role="tabpanel" aria-labelledby="completed-tab">
                    <div class="table-responsive">
                        <table class="table table-hover datatable" id="completedOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ الإكمال</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $completed_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                            FROM orders o 
                                                            JOIN services s ON o.service_id = s.id 
                                                            JOIN users u ON o.user_id = u.id 
                                                            WHERE o.status = 'completed' 
                                                            ORDER BY o.updated_at DESC";
                                    $completed_orders = $conn->query($completed_orders_query);
                                    
                                    if (!$completed_orders) {
                                        throw new Exception("Error executing completed orders query: " . $conn->error);
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</div>';
                                    $completed_orders = null;
                                }
                                
                                if ($completed_orders && $completed_orders->num_rows > 0): 
                                while ($order = $completed_orders->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد طلبات مكتملة</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Partial Orders Tab -->
                <div class="tab-pane fade" id="partial-orders" role="tabpanel" aria-labelledby="partial-tab">
                    <div class="table-responsive">
                        <table class="table table-hover datatable" id="partialOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>المنفذ/الكمية</th>
                                    <th>المبلغ</th>
                                    <th>التقدم</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $partial_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                        FROM orders o 
                                                        JOIN services s ON o.service_id = s.id 
                                                        JOIN users u ON o.user_id = u.id 
                                                        WHERE o.status = 'partial' 
                                                        ORDER BY o.updated_at DESC";
                                    $partial_orders = $conn->query($partial_orders_query);
                                    
                                    if (!$partial_orders) {
                                        throw new Exception("Error executing partial orders query: " . $conn->error);
                                    }
                                    
                                    if ($partial_orders && $partial_orders->num_rows > 0):
                                    while ($order = $partial_orders->fetch_assoc()):
                                        $progress = isset($order['quantity']) && isset($order['remains']) && $order['quantity'] > 0 
                                            ? round((($order['quantity'] - $order['remains']) / $order['quantity']) * 100)
                                            : 0;
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format(($order['quantity'] - $order['remains']) ?? 0); ?> / <?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progress; ?>%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد طلبات جزئية</td>
                                </tr>
                                <?php
                                endif;
                                } catch (Exception $e) {
                                    echo '<tr><td colspan="7" class="text-center text-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Cancelled Orders Tab -->
                <div class="tab-pane fade" id="cancelled-orders" role="tabpanel" aria-labelledby="cancelled-tab">
                    <div class="table-responsive">
                        <table class="table table-hover datatable" id="cancelledOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ الإلغاء</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $cancelled_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                             FROM orders o 
                                                             JOIN services s ON o.service_id = s.id 
                                                             JOIN users u ON o.user_id = u.id 
                                                             WHERE o.status = 'cancelled' 
                                                             ORDER BY o.updated_at DESC";
                                    $cancelled_orders = $conn->query($cancelled_orders_query);
                                    
                                    if (!$cancelled_orders) {
                                        throw new Exception("Error executing cancelled orders query: " . $conn->error);
                                    }
                                    
                                    if ($cancelled_orders && $cancelled_orders->num_rows > 0):
                                    while ($order = $cancelled_orders->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد طلبات ملغية</td>
                                </tr>
                                <?php
                                endif;
                                } catch (Exception $e) {
                                    echo '<tr><td colspan="7" class="text-center text-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Failed Orders Tab -->
                <div class="tab-pane fade" id="failed-orders" role="tabpanel" aria-labelledby="failed-tab">
                    <div class="table-responsive">
                        <table class="table table-hover datatable" id="failedOrdersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ الفشل</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                try {
                                    $failed_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                                          FROM orders o 
                                                          JOIN services s ON o.service_id = s.id 
                                                          JOIN users u ON o.user_id = u.id 
                                                          WHERE o.status = 'failed' 
                                                          ORDER BY o.updated_at DESC";
                                    $failed_orders = $conn->query($failed_orders_query);
                                    
                                    if (!$failed_orders) {
                                        throw new Exception("Error executing failed orders query: " . $conn->error);
                                    }
                                    
                                    if ($failed_orders && $failed_orders->num_rows > 0):
                                    while ($order = $failed_orders->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td><?php echo number_format($order['quantity'] ?? 0); ?></td>
                                    <td>$<?php echo number_format($order['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo isset($order['updated_at']) ? date('Y-m-d H:i', strtotime($order['updated_at'])) : ''; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد طلبات فاشلة</td>
                                </tr>
                                <?php
                                endif;
                                } catch (Exception $e) {
                                    echo '<tr><td colspan="7" class="text-center text-danger">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for handling status change and partial remains field -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle status change for fields
        const statusSelects = document.querySelectorAll('select[id^="status"]');
        statusSelects.forEach(select => {
            const orderId = select.id.replace('status', '');
            const partialRemainsField = document.getElementById('partialRemains' + orderId);
            const startCountField = document.getElementById('startCountField' + orderId);
            
            select.addEventListener('change', function() {
                if (this.value === 'partial') {
                    if (partialRemainsField) partialRemainsField.style.display = 'block';
                } else {
                    if (partialRemainsField) partialRemainsField.style.display = 'none';
                }
                
                if (this.value === 'processing') {
                    if (startCountField) startCountField.style.display = 'block';
                } else {
                    if (startCountField) startCountField.style.display = 'none';
                }
            });
            
            // Initialize visibility based on current value
            if (select.value === 'partial' && partialRemainsField) {
                partialRemainsField.style.display = 'block';
            }
            
            if (select.value === 'processing' && startCountField) {
                startCountField.style.display = 'block';
            }
        });
        
        // Handle search functionality
        const orderSearch = document.getElementById('orderSearch');
        const searchButton = document.getElementById('searchButton');
        
        function performSearch() {
            const searchTerm = orderSearch.value.toLowerCase();
            const activeTab = document.querySelector('.tab-pane.active');
            const rows = activeTab.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const id = row.cells[0].textContent.toLowerCase();
                const username = row.cells[1].textContent.toLowerCase();
                const service = row.cells[2].textContent.toLowerCase();
                
                if (id.includes(searchTerm) || username.includes(searchTerm) || service.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        if (searchButton) {
            searchButton.addEventListener('click', performSearch);
        }
        
        if (orderSearch) {
            orderSearch.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        }
        
        // Handle refresh button
        const refreshButton = document.getElementById('refreshOrders');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                location.reload();
            });
        }
        
        // Handle export to CSV
        const exportButton = document.getElementById('exportOrdersCSV');
        if (exportButton) {
            exportButton.addEventListener('click', function() {
                const activeTab = document.querySelector('.tab-pane.active');
                const table = activeTab.querySelector('table');
                
                let csv = [];
                const rows = table.querySelectorAll('tr');
                
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td, th');
                    let rowText = [];
                    
                    cols.forEach((col, index) => {
                        // Skip the actions column
                        if (index !== cols.length - 1) {
                            let text = col.innerText.replace(/"/g, '""');
                            // Remove badge text for status column
                            if (index === 5 && row.querySelector('.badge')) {
                                text = row.querySelector('.badge').innerText;
                            }
                            rowText.push('"' + text + '"');
                        }
                    });
                    
                    csv.push(rowText.join(','));
                });
                
                // Download CSV file
                const csvContent = csv.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                const date = new Date().toISOString().slice(0, 10);
                
                link.setAttribute('href', url);
                link.setAttribute('download', 'orders_export_' + date + '.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
        
        // Initialize DataTables for all tables
        document.querySelectorAll('.datatable').forEach(table => {
            $(table).DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
                },
                "pageLength": 25,
                "order": [[0, "desc"]],
                "responsive": true
            });
        });
    });
</script>
