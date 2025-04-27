<!-- Gifts Management -->
<div class="gifts-section">
    <h1 class="mb-4">الهدايا والمكافآت</h1>
    
    <!-- Send Gift -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">إرسال هدية لمستخدم</h5>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">اسم المستخدم</label>
                    <div class="position-relative">
                        <input type="text" class="form-control" id="username" name="username" placeholder="ابحث عن مستخدم" required value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>">
                        <div id="searchResults" class="position-absolute w-100 bg-white border rounded p-2 mt-1" style="display: none; z-index: 100; max-height: 200px; overflow-y: auto;"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="gift_amount" class="form-label">قيمة الهدية (بالدولار)</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="gift_amount" name="gift_amount" step="0.01" min="0.01" required>
                        <span class="input-group-text">$</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="gift_reason" class="form-label">سبب الهدية</label>
                    <select class="form-select" id="gift_reason" name="gift_reason" required>
                        <option value="">-- اختر السبب --</option>
                        <option value="مكافأة ولاء">مكافأة ولاء</option>
                        <option value="عيد ميلاد">عيد ميلاد</option>
                        <option value="مناسبة خاصة">مناسبة خاصة</option>
                        <option value="تعويض">تعويض عن مشكلة</option>
                        <option value="مكافأة شحن">مكافأة على شحن الرصيد</option>
                        <option value="أخرى">أخرى</option>
                    </select>
                </div>
                
                <div class="mb-3 custom-reason" style="display: none;">
                    <label for="custom_reason" class="form-label">سبب مخصص</label>
                    <input type="text" class="form-control" id="custom_reason" name="custom_reason">
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="add_gift" class="btn btn-primary">إرسال الهدية</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Gift History -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">سجل الهدايا</h5>
        </div>
        <div class="card-body">
            <?php
            $gifts_query = "SELECT t.*, u.username 
                           FROM transactions t 
                           JOIN users u ON t.user_id = u.id 
                           WHERE t.type = 'deposit' AND t.description LIKE 'هدية من الإدارة%' 
                           ORDER BY t.created_at DESC";
            $gifts = $conn->query($gifts_query);
            ?>
            
            <?php if ($gifts->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>القيمة</th>
                            <th>السبب</th>
                            <th>التاريخ</th>
                            <th>بواسطة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($gift = $gifts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $gift['id']; ?></td>
                            <td><?php echo htmlspecialchars($gift['username']); ?></td>
                            <td>$<?php echo number_format($gift['amount'], 2); ?></td>
                            <td>
                                <?php
                                $reason = $gift['description'];
                                $reason = str_replace('هدية من الإدارة: ', '', $reason);
                                echo htmlspecialchars($reason);
                                ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($gift['created_at'])); ?></td>
                            <td>
                                <span class="badge bg-info">المشرف</span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0">لا توجد هدايا حتى الآن.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show/hide custom reason field
    $('#gift_reason').on('change', function() {
        if ($(this).val() === 'أخرى') {
            $('.custom-reason').show();
            $('#custom_reason').prop('required', true);
        } else {
            $('.custom-reason').hide();
            $('#custom_reason').prop('required', false);
        }
    });
    
    // Modify the form submission to use custom reason if selected
    $('form').on('submit', function(e) {
        if ($('#gift_reason').val() === 'أخرى') {
            // Create a hidden input with the custom reason
            const customReasonInput = $('<input>')
                .attr('type', 'hidden')
                .attr('name', 'gift_reason')
                .val($('#custom_reason').val());
            
            // Replace the original gift_reason value
            $('#gift_reason').prop('disabled', true);
            
            // Add the hidden input to the form
            $(this).append(customReasonInput);
        }
    });
});
</script>