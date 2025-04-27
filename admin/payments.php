<!-- Payments Management -->
<div class="payments-section">
    <h1 class="mb-4">إدارة المدفوعات</h1>
    
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <ul class="nav nav-tabs" id="paymentsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-payments-tab" data-bs-toggle="tab" data-bs-target="#all-payments" type="button" role="tab" aria-controls="all-payments" aria-selected="true">جميع المدفوعات</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-payments-tab" data-bs-toggle="tab" data-bs-target="#pending-payments" type="button" role="tab" aria-controls="pending-payments" aria-selected="false">المدفوعات المعلقة</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-payments-tab" data-bs-toggle="tab" data-bs-target="#completed-payments" type="button" role="tab" aria-controls="completed-payments" aria-selected="false">المدفوعات المكتملة</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="failed-payments-tab" data-bs-toggle="tab" data-bs-target="#failed-payments" type="button" role="tab" aria-controls="failed-payments" aria-selected="false">المدفوعات الفاشلة</button>
                </li>
            </ul>
            
            <div class="tab-content mt-4" id="paymentsTabContent">
                <!-- All Payments Tab -->
                <div class="tab-pane fade show active" id="all-payments" role="tabpanel" aria-labelledby="all-payments-tab">
                    <?php
                    $all_payments_query = "SELECT t.*, u.username 
                                          FROM transactions t 
                                          JOIN users u ON t.user_id = u.id 
                                          WHERE t.type = 'deposit' 
                                          ORDER BY t.created_at DESC";
                    $all_payments = $conn->query($all_payments_query);
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>المبلغ</th>
                                    <th>طريقة الدفع</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $all_payments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $payment['id']; ?></td>
                                    <td><?php echo htmlspecialchars($payment['username']); ?></td>
                                    <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td>
                                        <?php
                                        $payment_method = '';
                                        
                                        if (strpos($payment['description'], 'بطاقة ائتمانية') !== false) {
                                            $payment_method = 'بطاقة ائتمانية';
                                        } elseif (strpos($payment['description'], 'USDT') !== false) {
                                            $payment_method = 'USDT';
                                        } elseif (strpos($payment['description'], 'Binance') !== false) {
                                            $payment_method = 'Binance Pay';
                                        } elseif (strpos($payment['description'], 'تحويل بنكي') !== false) {
                                            $payment_method = 'تحويل بنكي';
                                        } elseif (strpos($payment['description'], 'الكريمي') !== false) {
                                            $payment_method = 'بنك الكريمي';
                                        } elseif (strpos($payment['description'], 'حوالة محلية') !== false) {
                                            $payment_method = 'حوالة محلية';
                                        } elseif (strpos($payment['description'], 'محفظة محلية') !== false) {
                                            $payment_method = 'محفظة محلية';
                                        } else {
                                            $payment_method = 'أخرى';
                                        }
                                        
                                        echo $payment_method;
                                        ?>
                                    </td>
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
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#paymentDetailsModal<?php echo $payment['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($payment['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approvePaymentModal<?php echo $payment['id']; ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal<?php echo $payment['id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <?php
                                        // Check if this payment has a receipt
                                        $receipt_query = "SELECT * FROM payment_receipts WHERE transaction_id = ?";
                                        $stmt = $conn->prepare($receipt_query);
                                        $stmt->bind_param("i", $payment['id']);
                                        $stmt->execute();
                                        $receipt_result = $stmt->get_result();
                                        
                                        if ($receipt_result && $receipt_result->num_rows > 0) {
                                            $receipt = $receipt_result->fetch_assoc();
                                            echo '<button type="button" class="btn btn-sm btn-secondary view-receipt" data-receipt="' . htmlspecialchars($receipt['file_path']) . '">
                                                <i class="fas fa-file-image"></i>
                                            </button>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                
                                <!-- Payment Details Modal -->
                                <div class="modal fade" id="paymentDetailsModal<?php echo $payment['id']; ?>" tabindex="-1" aria-labelledby="paymentDetailsModalLabel<?php echo $payment['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="paymentDetailsModalLabel<?php echo $payment['id']; ?>">تفاصيل عملية الدفع #<?php echo $payment['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>رقم العملية:</strong> <?php echo $payment['id']; ?></p>
                                                <p><strong>المستخدم:</strong> <?php echo htmlspecialchars($payment['username']); ?></p>
                                                <p><strong>المبلغ:</strong> $<?php echo number_format($payment['amount'], 2); ?></p>
                                                <p><strong>طريقة الدفع:</strong> <?php echo $payment_method; ?></p>
                                                <p><strong>الحالة:</strong> <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></p>
                                                <p><strong>التاريخ:</strong> <?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></p>
                                                <p><strong>الوصف:</strong> <?php echo htmlspecialchars($payment['description']); ?></p>
                                                
                                                <?php if ($receipt_result && $receipt_result->num_rows > 0): ?>
                                                <hr>
                                                <h6>إيصال الدفع</h6>
                                                <div class="text-center">
                                                    <img src="<?php echo htmlspecialchars($receipt['file_path']); ?>" class="img-fluid border" style="max-height: 300px;">
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                
                                                <?php if ($payment['status'] === 'pending'): ?>
                                                <form method="post" action="" class="d-inline">
                                                    <input type="hidden" name="transaction_id" value="<?php echo $payment['id']; ?>">
                                                    <button type="submit" name="approve_payment" class="btn btn-success">اعتماد</button>
                                                </form>
                                                
                                                <form method="post" action="" class="d-inline">
                                                    <input type="hidden" name="transaction_id" value="<?php echo $payment['id']; ?>">
                                                    <button type="submit" name="reject_payment" class="btn btn-danger">رفض</button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Approve Payment Modal -->
                                <div class="modal fade" id="approvePaymentModal<?php echo $payment['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">اعتماد عملية الدفع #<?php echo $payment['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد اعتماد عملية الدفع هذه؟ سيتم إضافة المبلغ $<?php echo number_format($payment['amount'], 2); ?> إلى رصيد المستخدم <?php echo htmlspecialchars($payment['username']); ?>.</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="transaction_id" value="<?php echo $payment['id']; ?>">
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="approve_payment" class="btn btn-success">تأكيد الاعتماد</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Reject Payment Modal -->
                                <div class="modal fade" id="rejectPaymentModal<?php echo $payment['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">رفض عملية الدفع #<?php echo $payment['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد رفض عملية الدفع هذه؟</p>
                                                <form method="post" action="">
                                                    <input type="hidden" name="transaction_id" value="<?php echo $payment['id']; ?>">
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="reject_payment" class="btn btn-danger">تأكيد الرفض</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pending Payments Tab -->
                <div class="tab-pane fade" id="pending-payments" role="tabpanel" aria-labelledby="pending-payments-tab">
                    <?php
                    $pending_payments_query = "SELECT t.*, u.username 
                                             FROM transactions t 
                                             JOIN users u ON t.user_id = u.id 
                                             WHERE t.type = 'deposit' AND t.status = 'pending' 
                                             ORDER BY t.created_at DESC";
                    $pending_payments = $conn->query($pending_payments_query);
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>المبلغ</th>
                                    <th>طريقة الدفع</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pending_payments->num_rows > 0): ?>
                                <?php while ($payment = $pending_payments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $payment['id']; ?></td>
                                    <td><?php echo htmlspecialchars($payment['username']); ?></td>
                                    <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td>
                                        <?php
                                        $payment_method = '';
                                        
                                        if (strpos($payment['description'], 'بطاقة ائتمانية') !== false) {
                                            $payment_method = 'بطاقة ائتمانية';
                                        } elseif (strpos($payment['description'], 'USDT') !== false) {
                                            $payment_method = 'USDT';
                                        } elseif (strpos($payment['description'], 'Binance') !== false) {
                                            $payment_method = 'Binance Pay';
                                        } elseif (strpos($payment['description'], 'تحويل بنكي') !== false) {
                                            $payment_method = 'تحويل بنكي';
                                        } elseif (strpos($payment['description'], 'الكريمي') !== false) {
                                            $payment_method = 'بنك الكريمي';
                                        } elseif (strpos($payment['description'], 'حوالة محلية') !== false) {
                                            $payment_method = 'حوالة محلية';
                                        } elseif (strpos($payment['description'], 'محفظة محلية') !== false) {
                                            $payment_method = 'محفظة محلية';
                                        } else {
                                            $payment_method = 'أخرى';
                                        }
                                        
                                        echo $payment_method;
                                        ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#pendingPaymentDetailsModal<?php echo $payment['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approvePaymentModal<?php echo $payment['id']; ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal<?php echo $payment['id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        
                                        <?php
                                        // Check if this payment has a receipt
                                        $receipt_query = "SELECT * FROM payment_receipts WHERE transaction_id = ?";
                                        $stmt = $conn->prepare($receipt_query);
                                        $stmt->bind_param("i", $payment['id']);
                                        $stmt->execute();
                                        $receipt_result = $stmt->get_result();
                                        
                                        if ($receipt_result && $receipt_result->num_rows > 0) {
                                            $receipt = $receipt_result->fetch_assoc();
                                            echo '<button type="button" class="btn btn-sm btn-secondary view-receipt" data-receipt="' . htmlspecialchars($receipt['file_path']) . '">
                                                <i class="fas fa-file-image"></i>
                                            </button>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                
                                <!-- Payment Details Modal -->
                                <div class="modal fade" id="pendingPaymentDetailsModal<?php echo $payment['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تفاصيل عملية الدفع #<?php echo $payment['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>رقم العملية:</strong> <?php echo $payment['id']; ?></p>
                                                <p><strong>المستخدم:</strong> <?php echo htmlspecialchars($payment['username']); ?></p>
                                                <p><strong>المبلغ:</strong> $<?php echo number_format($payment['amount'], 2); ?></p>
                                                <p><strong>طريقة الدفع:</strong> <?php echo $payment_method; ?></p>
                                                <p><strong>الحالة:</strong> <span class="badge bg-warning">قيد الانتظار</span></p>
                                                <p><strong>التاريخ:</strong> <?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></p>
                                                <p><strong>الوصف:</strong> <?php echo htmlspecialchars($payment['description']); ?></p>
                                                
                                                <?php if ($receipt_result && $receipt_result->num_rows > 0): ?>
                                                <hr>
                                                <h6>إيصال الدفع</h6>
                                                <div class="text-center">
                                                    <img src="<?php echo htmlspecialchars($receipt['file_path']); ?>" class="img-fluid border" style="max-height: 300px;">
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                
                                                <form method="post" action="" class="d-inline">
                                                    <input type="hidden" name="transaction_id" value="<?php echo $payment['id']; ?>">
                                                    <button type="submit" name="approve_payment" class="btn btn-success">اعتماد</button>
                                                </form>
                                                
                                                <form method="post" action="" class="d-inline">
                                                    <input type="hidden" name="transaction_id" value="<?php echo $payment['id']; ?>">
                                                    <button type="submit" name="reject_payment" class="btn btn-danger">رفض</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">لا توجد مدفوعات معلقة</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Completed Payments Tab -->
                <div class="tab-pane fade" id="completed-payments" role="tabpanel" aria-labelledby="completed-payments-tab">
                    <!-- Similar structure with completed payments query -->
                </div>
                
                <!-- Failed Payments Tab -->
                <div class="tab-pane fade" id="failed-payments" role="tabpanel" aria-labelledby="failed-payments-tab">
                    <!-- Similar structure with failed payments query -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Funds Manually -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">إضافة رصيد يدوياً</h5>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="userSearch" class="form-label">اسم المستخدم</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="userSearch" name="username" placeholder="ابحث عن مستخدم" required>
                            <div id="searchResults" class="position-absolute w-100 bg-white border rounded p-2 mt-1" style="display: none; z-index: 100; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="amount" class="form-label">المبلغ (بالدولار)</label>
                        <input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="payment_method" class="form-label">طريقة الدفع</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">-- اختر طريقة الدفع --</option>
                            <option value="بطاقة ائتمانية">بطاقة ائتمانية</option>
                            <option value="USDT">USDT</option>
                            <option value="Binance Pay">Binance Pay</option>
                            <option value="تحويل بنكي">تحويل بنكي</option>
                            <option value="بنك الكريمي">بنك الكريمي</option>
                            <option value="حوالة محلية">حوالة محلية</option>
                            <option value="محفظة محلية">محفظة محلية</option>
                            <option value="أخرى">أخرى</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">ملاحظات (اختياري)</label>
                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="add_funds" class="btn btn-primary">إضافة الرصيد</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">إيصال الدفع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="receiptImage" class="img-fluid" alt="إيصال الدفع">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>