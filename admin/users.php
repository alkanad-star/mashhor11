<!-- Users Management -->
<div class="users-section">
    <h1 class="mb-4">إدارة المستخدمين</h1>
    
    <!-- Action Buttons -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="input-group">
                        <input type="text" class="form-control" id="userSearchInput" placeholder="ابحث باسم المستخدم أو البريد الإلكتروني...">
                        <button class="btn btn-outline-secondary" type="button" id="userSearchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-md-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-user-plus"></i> إضافة مستخدم
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="refreshUsers">
                            <i class="fas fa-sync-alt"></i> تحديث
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="exportUsersCSV">
                            <i class="fas fa-file-export"></i> تصدير CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users List -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <?php
            try {
                $all_users_query = "SELECT * FROM users ORDER BY id DESC";
                $all_users = $conn->query($all_users_query);
                
                if (!$all_users) {
                    throw new Exception("Error executing users query: " . $conn->error);
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger m-3">خطأ في استرجاع البيانات: ' . $e->getMessage() . '</div>';
                $all_users = null;
            }
            ?>
            
            <!-- Vertical List View -->
            <div class="list-group list-group-flush">
                <?php if ($all_users && $all_users->num_rows > 0): ?>
                    <?php while ($user = $all_users->fetch_assoc()): ?>
                        <div class="list-group-item user-list-item p-3">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <span class="user-id"><?php echo $user['id']; ?></span>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-2"><?php echo htmlspecialchars($user['username']); ?></h6>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-success'; ?> ms-2">
                                            <?php echo $user['role'] === 'admin' ? 'مدير' : 'مستخدم'; ?>
                                        </span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1 small text-muted"><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($user['full_name']); ?></p>
                                            <p class="mb-1 small text-muted"><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1 small text-muted"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($user['phone'] ?? 'غير متوفر'); ?></p>
                                            <p class="mb-1 small text-muted"><i class="fas fa-flag me-1"></i> <?php echo htmlspecialchars($user['country'] ?? 'غير متوفر'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1 text-primary fw-bold"><i class="fas fa-wallet me-1"></i> $<?php echo number_format($user['balance'], 2); ?></p>
                                    <p class="mb-1 small text-muted"><i class="fas fa-calendar me-1"></i> <?php echo date('Y-m-d', strtotime($user['created_at'])); ?></p>
                                </div>
                                <div class="col-md-2">
                                    <div class="btn-group float-end">
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewUserModal<?php echo $user['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success" onclick="window.location.href='admin.php?section=gifts&username=<?php echo htmlspecialchars($user['username']); ?>'">
                                            <i class="fas fa-gift"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="window.location.href='admin.php?section=notifications&user_id=<?php echo $user['id']; ?>'">
                                            <i class="fas fa-bell"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit User Modal -->
                        <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="editUserModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editUserModalLabel<?php echo $user['id']; ?>">تعديل المستخدم</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post" action="admin.php?section=users">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            
                                            <div class="mb-3">
                                                <label for="username<?php echo $user['id']; ?>" class="form-label">اسم المستخدم</label>
                                                <input type="text" class="form-control" id="username<?php echo $user['id']; ?>" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="full_name<?php echo $user['id']; ?>" class="form-label">الاسم الكامل</label>
                                                <input type="text" class="form-control" id="full_name<?php echo $user['id']; ?>" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="email<?php echo $user['id']; ?>" class="form-label">البريد الإلكتروني</label>
                                                <input type="email" class="form-control" id="email<?php echo $user['id']; ?>" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="phone<?php echo $user['id']; ?>" class="form-label">الهاتف</label>
                                                <input type="text" class="form-control" id="phone<?php echo $user['id']; ?>" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="country<?php echo $user['id']; ?>" class="form-label">البلد</label>
                                                <select class="form-select" id="country<?php echo $user['id']; ?>" name="country">
                                                    <option value="">اختر البلد</option>
                                                    <option value="YE" <?php echo ($user['country'] ?? '') === 'YE' ? 'selected' : ''; ?>>اليمن</option>
                                                    <option value="AE" <?php echo ($user['country'] ?? '') === 'AE' ? 'selected' : ''; ?>>الإمارات</option>
                                                    <option value="SA" <?php echo ($user['country'] ?? '') === 'SA' ? 'selected' : ''; ?>>السعودية</option>
                                                    <option value="EG" <?php echo ($user['country'] ?? '') === 'EG' ? 'selected' : ''; ?>>مصر</option>
                                                    <option value="JO" <?php echo ($user['country'] ?? '') === 'JO' ? 'selected' : ''; ?>>الأردن</option>
                                                    <option value="BH" <?php echo ($user['country'] ?? '') === 'BH' ? 'selected' : ''; ?>>البحرين</option>
                                                    <option value="DZ" <?php echo ($user['country'] ?? '') === 'DZ' ? 'selected' : ''; ?>>الجزائر</option>
                                                    <option value="IQ" <?php echo ($user['country'] ?? '') === 'IQ' ? 'selected' : ''; ?>>العراق</option>
                                                    <option value="KW" <?php echo ($user['country'] ?? '') === 'KW' ? 'selected' : ''; ?>>الكويت</option>
                                                    <option value="LB" <?php echo ($user['country'] ?? '') === 'LB' ? 'selected' : ''; ?>>لبنان</option>
                                                    <option value="LY" <?php echo ($user['country'] ?? '') === 'LY' ? 'selected' : ''; ?>>ليبيا</option>
                                                    <option value="MA" <?php echo ($user['country'] ?? '') === 'MA' ? 'selected' : ''; ?>>المغرب</option>
                                                    <option value="OM" <?php echo ($user['country'] ?? '') === 'OM' ? 'selected' : ''; ?>>عمان</option>
                                                    <option value="PS" <?php echo ($user['country'] ?? '') === 'PS' ? 'selected' : ''; ?>>فلسطين</option>
                                                    <option value="QA" <?php echo ($user['country'] ?? '') === 'QA' ? 'selected' : ''; ?>>قطر</option>
                                                    <option value="SD" <?php echo ($user['country'] ?? '') === 'SD' ? 'selected' : ''; ?>>السودان</option>
                                                    <option value="SY" <?php echo ($user['country'] ?? '') === 'SY' ? 'selected' : ''; ?>>سوريا</option>
                                                    <option value="TN" <?php echo ($user['country'] ?? '') === 'TN' ? 'selected' : ''; ?>>تونس</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="balance<?php echo $user['id']; ?>" class="form-label">الرصيد</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="balance<?php echo $user['id']; ?>" name="balance" value="<?php echo $user['balance']; ?>" step="0.01" min="0" required>
                                                    <span class="input-group-text">$</span>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="role<?php echo $user['id']; ?>" class="form-label">الدور</label>
                                                <select class="form-select" id="role<?php echo $user['id']; ?>" name="role" required>
                                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>مستخدم</option>
                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>مدير</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="referral_percentage<?php echo $user['id']; ?>" class="form-label">نسبة الإحالة الخاصة (اختياري)</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="referral_percentage<?php echo $user['id']; ?>" name="referral_percentage" value="<?php echo $user['referral_percentage'] ?? ''; ?>" step="0.01" min="0" max="100">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                                <div class="form-text">اترك فارغاً لاستخدام النسبة الافتراضية للنظام</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="new_password<?php echo $user['id']; ?>" class="form-label">كلمة المرور الجديدة (اختياري)</label>
                                                <input type="password" class="form-control" id="new_password<?php echo $user['id']; ?>" name="new_password">
                                                <div class="form-text">اترك هذا الحقل فارغاً إذا كنت لا ترغب في تغيير كلمة المرور</div>
                                            </div>
                                            
                                            <div class="d-grid gap-2">
                                                <button type="submit" name="edit_user" class="btn btn-primary">تحديث المستخدم</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- View User Modal -->
                        <div class="modal fade" id="viewUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="viewUserModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewUserModalLabel<?php echo $user['id']; ?>">تفاصيل المستخدم</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">المعلومات الشخصية</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <p><strong>ID:</strong> <?php echo $user['id']; ?></p>
                                                        <p><strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                                                        <p><strong>الاسم الكامل:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                                                        <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                                        <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'غير متوفر'); ?></p>
                                                        <p><strong>البلد:</strong> <?php echo htmlspecialchars($user['country'] ?? 'غير متوفر'); ?></p>
                                                        <p><strong>تاريخ التسجيل:</strong> <?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">معلومات الحساب</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <p><strong>الدور:</strong> 
                                                            <?php if ($user['role'] === 'admin'): ?>
                                                                <span class="badge bg-danger">مدير</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">مستخدم</span>
                                                            <?php endif; ?>
                                                        </p>
                                                        <p><strong>الرصيد:</strong> $<?php echo number_format($user['balance'], 2); ?></p>
                                                        <p><strong>الرصيد قيد الاستخدام:</strong> $<?php echo number_format($user['in_use'] ?? 0, 2); ?></p>
                                                        <p><strong>إجمالي الصرف:</strong> 
                                                            <?php
                                                            // Calculate total spent
                                                            $spent_query = "SELECT SUM(amount) as total FROM transactions WHERE user_id = {$user['id']} AND type = 'purchase' AND status = 'completed'";
                                                            $spent_result = $conn->query($spent_query);
                                                            $total_spent = $spent_result->fetch_assoc()['total'] ?? 0;
                                                            echo '$' . number_format($total_spent, 2);
                                                            ?>
                                                        </p>
                                                        <p><strong>كود الإحالة:</strong> <?php echo htmlspecialchars($user['referral_code'] ?? 'غير متوفر'); ?></p>
                                                        <p><strong>نسبة الإحالة:</strong> 
                                                            <?php 
                                                            if (isset($user['referral_percentage']) && $user['referral_percentage'] > 0) {
                                                                echo $user['referral_percentage'] . '%';
                                                            } else {
                                                                echo 'القيمة الافتراضية للنظام';
                                                            }
                                                            ?>
                                                        </p>
                                                        <p><strong>عدد الإحالات:</strong>
                                                            <?php
                                                            // Count referrals
                                                            $referrals_query = "SELECT COUNT(id) as total FROM referrals WHERE referrer_id = {$user['id']}";
                                                            $referrals_result = $conn->query($referrals_query);
                                                            echo $referrals_result ? $referrals_result->fetch_assoc()['total'] : '0';
                                                            ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">آخر الطلبات</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php
                                                        $orders_query = "SELECT o.*, s.name as service_name 
                                                                       FROM orders o 
                                                                       JOIN services s ON o.service_id = s.id 
                                                                       WHERE o.user_id = {$user['id']} 
                                                                       ORDER BY o.created_at DESC 
                                                                       LIMIT 5";
                                                        $orders_result = $conn->query($orders_query);
                                                        
                                                        if ($orders_result && $orders_result->num_rows > 0):
                                                        ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>الخدمة</th>
                                                                        <th>الكمية</th>
                                                                        <th>المبلغ</th>
                                                                        <th>الحالة</th>
                                                                        <th>التاريخ</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                                                                    <tr>
                                                                        <td><?php echo $order['id']; ?></td>
                                                                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                                                        <td><?php echo number_format($order['quantity']); ?></td>
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
                                                        </div>
                                                        <div class="text-center mt-2">
                                                            <a href="admin.php?section=orders&user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">عرض جميع الطلبات</a>
                                                        </div>
                                                        <?php else: ?>
                                                        <p class="text-center mb-0">لا توجد طلبات لهذا المستخدم</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>" data-bs-dismiss="modal">تعديل المستخدم</button>
                                        <button type="button" class="btn btn-success" onclick="window.location.href='admin.php?section=gifts&username=<?php echo htmlspecialchars($user['username']); ?>'">إرسال هدية</button>
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='admin.php?section=notifications&user_id=<?php echo $user['id']; ?>'">إرسال إشعار</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info m-3">لا يوجد مستخدمين</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">إضافة مستخدم جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="admin.php?section=users">
                    <div class="mb-3">
                        <label for="new_username" class="form-label">اسم المستخدم *</label>
                        <input type="text" class="form-control" id="new_username" name="new_username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_full_name" class="form-label">الاسم الكامل *</label>
                        <input type="text" class="form-control" id="new_full_name" name="new_full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_email" class="form-label">البريد الإلكتروني *</label>
                        <input type="email" class="form-control" id="new_email" name="new_email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_phone" class="form-label">الهاتف</label>
                        <input type="text" class="form-control" id="new_phone" name="new_phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_country" class="form-label">البلد</label>
                        <select class="form-select" id="new_country" name="new_country">
                            <option value="">اختر البلد</option>
                            <option value="YE">اليمن</option>
                            <option value="AE">الإمارات</option>
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
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_balance" class="form-label">الرصيد الأولي</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="new_balance" name="new_balance" value="0" step="0.01" min="0">
                            <span class="input-group-text">$</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_role" class="form-label">الدور *</label>
                        <select class="form-select" id="new_role" name="new_role" required>
                            <option value="user">مستخدم</option>
                            <option value="admin">مدير</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_user_password" class="form-label">كلمة المرور *</label>
                        <input type="password" class="form-control" id="new_user_password" name="new_user_password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="add_user" class="btn btn-primary">إضافة المستخدم</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.user-list-item {
    border-left: none;
    border-right: none;
    border-top: none;
    transition: background-color 0.2s ease;
}

.user-list-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.user-list-item:last-child {
    border-bottom: none;
}

.user-id {
    font-weight: bold;
    font-size: 1.2rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .user-list-item .row > div {
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
$(document).ready(function() {
    // User search functionality
    $('#userSearchButton').on('click', function() {
        const searchTerm = $('#userSearchInput').val().toLowerCase();
        $('.user-list-item').each(function() {
            const username = $(this).find('h6').text().toLowerCase();
            const email = $(this).find('.fa-envelope').parent().text().toLowerCase();
            const fullName = $(this).find('.fa-user').parent().text().toLowerCase();
            const phone = $(this).find('.fa-phone').parent().text().toLowerCase();
            
            if (username.includes(searchTerm) || email.includes(searchTerm) || 
                fullName.includes(searchTerm) || phone.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Enable pressing Enter to search
    $('#userSearchInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#userSearchButton').click();
        }
    });
    
    // Refresh users
    $('#refreshUsers').on('click', function() {
        window.location.reload();
    });
    
    // Export users to CSV
    $('#exportUsersCSV').on('click', function() {
        let csv = 'ID,اسم المستخدم,الاسم الكامل,البريد الإلكتروني,الهاتف,البلد,الرصيد,الدور,تاريخ التسجيل\n';
        
        $('.user-list-item').each(function() {
            const userId = $(this).find('.user-id').text();
            const username = $(this).find('h6').text().trim();
            const fullName = $(this).find('.fa-user').parent().text().replace(/\s+/g, ' ').trim();
            const email = $(this).find('.fa-envelope').parent().text().replace(/\s+/g, ' ').trim();
            const phone = $(this).find('.fa-phone').parent().text().replace(/\s+/g, ' ').trim();
            const country = $(this).find('.fa-flag').parent().text().replace(/\s+/g, ' ').trim();
            const balance = $(this).find('.fa-wallet').parent().text().replace(/\s+/g, ' ').trim();
            const role = $(this).find('.badge').text().trim();
            const date = $(this).find('.fa-calendar').parent().text().replace(/\s+/g, ' ').trim();
            
            csv += `"${userId}","${username}","${fullName}","${email}","${phone}","${country}","${balance}","${role}","${date}"\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', 'users_export_' + new Date().toISOString().slice(0, 10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>