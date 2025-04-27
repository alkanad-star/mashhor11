<!-- Dashboard -->
<div class="dashboard-section">
    <h1 class="mb-4">لوحة التحكم</h1>
    
    <div class="row">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #4CAF50;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-card-value"><?php echo number_format($total_orders); ?></div>
                <div class="stats-card-label">إجمالي الطلبات</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #2196F3;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-card-value"><?php echo number_format($total_users); ?></div>
                <div class="stats-card-label">إجمالي المستخدمين</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #FFC107;">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stats-card-value">$<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stats-card-label">إجمالي الإيرادات</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #F44336;">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="stats-card-value"><?php echo number_format($pending_payments); ?></div>
                <div class="stats-card-label">المدفوعات المعلقة</div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #FF9800;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stats-card-value"><?php echo number_format($pending_orders); ?></div>
                <div class="stats-card-label">الطلبات المعلقة</div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #9C27B0;">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stats-card-value"><?php echo number_format($processing_orders); ?></div>
                <div class="stats-card-label">الطلبات قيد التنفيذ</div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-card-icon" style="background-color: #009688;">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stats-card-value"><?php echo number_format($completed_orders); ?></div>
                <div class="stats-card-label">الطلبات المكتملة</div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0">آخر الطلبات</h5>
                    <a href="admin.php?section=orders" class="btn btn-sm btn-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php
                        $recent_orders_query = "SELECT o.*, s.name as service_name, u.username 
                                              FROM orders o 
                                              JOIN services s ON o.service_id = s.id 
                                              JOIN users u ON o.user_id = u.id 
                                              ORDER BY o.created_at DESC LIMIT 5";
                        $recent_orders = $conn->query($recent_orders_query);
                        ?>
                        
                        <?php if ($recent_orders->num_rows > 0): ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>الخدمة</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td>$<?php echo number_format($order['amount'], 2); ?></td>
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
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="alert alert-info mb-0">لا توجد طلبات حتى الآن.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0">آخر المدفوعات</h5>
                    <a href="admin.php?section=payments" class="btn btn-sm btn-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php
                        $recent_payments_query = "SELECT t.*, u.username 
                                                FROM transactions t 
                                                JOIN users u ON t.user_id = u.id 
                                                WHERE t.type = 'deposit' 
                                                ORDER BY t.created_at DESC LIMIT 5";
                        $recent_payments = $conn->query($recent_payments_query);
                        ?>
                        
                        <?php if ($recent_payments->num_rows > 0): ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $recent_payments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $payment['id']; ?></td>
                                    <td><?php echo htmlspecialchars($payment['username']); ?></td>
                                    <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $status_class = '';
                                        $status_text = '';
                                        
                                        switch ($payment['status']) {
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
                                                $status_class = 'bg-secondary';
                                                $status_text = 'ملغي';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="alert alert-info mb-0">لا توجد مدفوعات حتى الآن.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>