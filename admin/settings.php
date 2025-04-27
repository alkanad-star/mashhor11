<!-- Settings Management -->
<div class="settings-section">
    <h1 class="mb-4">إعدادات النظام</h1>
    
    <?php
    // Process settings update
    if (isset($_POST['update_settings'])) {
        $site_name = filter_input(INPUT_POST, 'site_name', FILTER_SANITIZE_STRING);
        $site_description = filter_input(INPUT_POST, 'site_description', FILTER_SANITIZE_STRING);
        $currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_STRING);
        $currency_symbol = filter_input(INPUT_POST, 'currency_symbol', FILTER_SANITIZE_STRING);
        $whatsapp_number = filter_input(INPUT_POST, 'whatsapp_number', FILTER_SANITIZE_STRING);
        $telegram_username = filter_input(INPUT_POST, 'telegram_username', FILTER_SANITIZE_STRING);
        $min_deposit = filter_input(INPUT_POST, 'min_deposit', FILTER_VALIDATE_FLOAT);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update settings in the database
            $update_queries = [
                "UPDATE settings SET setting_value = ? WHERE setting_key = 'site_name'",
                "UPDATE settings SET setting_value = ? WHERE setting_key = 'site_description'",
                "UPDATE settings SET setting_value = ? WHERE setting_key = 'currency'",
                "UPDATE settings SET setting_value = ? WHERE setting_key = 'currency_symbol'",
                "UPDATE settings SET setting_value = ? WHERE setting_key = 'whatsapp_number'",
                "UPDATE settings SET setting_value = ? WHERE setting_key = 'telegram_username'",
                "UPDATE settings SET setting_value = ? WHERE setting_key = 'min_deposit'"
            ];
            
            $values = [$site_name, $site_description, $currency, $currency_symbol, $whatsapp_number, $telegram_username, $min_deposit];
            
            foreach ($update_queries as $index => $query) {
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $values[$index]);
                $stmt->execute();
            }
            
            $conn->commit();
            echo '<div class="alert alert-success">تم تحديث الإعدادات بنجاح.</div>';
        } catch (Exception $e) {
            $conn->rollback();
            echo '<div class="alert alert-danger">حدث خطأ أثناء تحديث الإعدادات: ' . $e->getMessage() . '</div>';
        }
    }
    
    // Get current settings
    $settings_query = "SELECT * FROM settings";
    $settings_result = $conn->query($settings_query);
    
    $settings = [];
    if ($settings_result) {
        while ($row = $settings_result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    ?>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">الإعدادات العامة</h5>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="site_name" class="form-label">اسم الموقع</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'متجر مشهور'); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="site_description" class="form-label">وصف الموقع</label>
                        <input type="text" class="form-control" id="site_description" name="site_description" value="<?php echo htmlspecialchars($settings['site_description'] ?? 'أرخص موقع زيادة متابعين'); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="currency" class="form-label">العملة</label>
                        <input type="text" class="form-control" id="currency" name="currency" value="<?php echo htmlspecialchars($settings['currency'] ?? 'USD'); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="currency_symbol" class="form-label">رمز العملة</label>
                        <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="whatsapp_number" class="form-label">رقم الواتساب</label>
                        <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? '+1234567890'); ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="telegram_username" class="form-label">معرف التلجرام</label>
                        <input type="text" class="form-control" id="telegram_username" name="telegram_username" value="<?php echo htmlspecialchars($settings['telegram_username'] ?? 'your_telegram_username'); ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="min_deposit" class="form-label">الحد الأدنى للشحن</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="min_deposit" name="min_deposit" value="<?php echo htmlspecialchars($settings['min_deposit'] ?? '2'); ?>" step="0.01" min="0" required>
                            <span class="input-group-text"><?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="update_settings" class="btn btn-primary">حفظ الإعدادات</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Payment Methods -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">طرق الدفع</h5>
        </div>
        <div class="card-body">
            <form method="post" action="admin.php?section=settings&action=payment_methods">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_credit_card" name="enable_credit_card" <?php echo ($settings['enable_credit_card'] ?? 'yes') === 'yes' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_credit_card">بطاقات الائتمان (فيزا / ماستر كارد)</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_usdt" name="enable_usdt" <?php echo ($settings['enable_usdt'] ?? 'yes') === 'yes' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_usdt">الدولار الرقمي (USDT)</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_binance" name="enable_binance" <?php echo ($settings['enable_binance'] ?? 'yes') === 'yes' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_binance">Binance Pay</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_bank_transfer" name="enable_bank_transfer" <?php echo ($settings['enable_bank_transfer'] ?? 'yes') === 'yes' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_bank_transfer">تحويل بنكي</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_karimi_bank" name="enable_karimi_bank" <?php echo ($settings['enable_karimi_bank'] ?? 'yes') === 'yes' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_karimi_bank">بنك الكريمي (اليمن)</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_local_transfer" name="enable_local_transfer" <?php echo ($settings['enable_local_transfer'] ?? 'yes') === 'yes' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_local_transfer">حوالة محلية (اليمن)</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_local_wallet" name="enable_local_wallet" <?php echo ($settings['enable_local_wallet'] ?? 'yes') === 'yes' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_local_wallet">المحافظ المحلية (اليمن)</label>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="mb-3">بيانات USDT</h6>
                
                <div class="mb-3">
                    <label for="usdt_wallet" class="form-label">عنوان محفظة USDT (TRC20)</label>
                    <input type="text" class="form-control" id="usdt_wallet" name="usdt_wallet" value="<?php echo htmlspecialchars($settings['usdt_wallet'] ?? 'TKXLMc82ja9frhtP8gULQoJbpGjEUHFCpN'); ?>">
                </div>
                
                <hr>
                
                <h6 class="mb-3">بيانات التحويل البنكي</h6>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="bank_name" class="form-label">اسم البنك</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($settings['bank_name'] ?? 'بنك المشرق'); ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="account_name" class="form-label">اسم الحساب</label>
                        <input type="text" class="form-control" id="account_name" name="account_name" value="<?php echo htmlspecialchars($settings['account_name'] ?? 'متجر مشهور'); ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="account_number" class="form-label">رقم الحساب</label>
                        <input type="text" class="form-control" id="account_number" name="account_number" value="<?php echo htmlspecialchars($settings['account_number'] ?? '1234567890'); ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="iban" class="form-label">رقم IBAN</label>
                        <input type="text" class="form-control" id="iban" name="iban" value="<?php echo htmlspecialchars($settings['iban'] ?? 'AE123456789012345678'); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="swift_code" class="form-label">SWIFT Code</label>
                    <input type="text" class="form-control" id="swift_code" name="swift_code" value="<?php echo htmlspecialchars($settings['swift_code'] ?? 'MSHQAE123'); ?>">
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="update_payment_methods" class="btn btn-primary">حفظ إعدادات طرق الدفع</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- API Settings -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">إعدادات API</h5>
        </div>
        <div class="card-body">
            <form method="post" action="admin.php?section=settings&action=api">
                <div class="mb-3">
                    <label for="api_key" class="form-label">مفتاح API</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="api_key" name="api_key" value="<?php echo htmlspecialchars($settings['api_key'] ?? '92cf67b864a4ce785caf93b2b542e319'); ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="generateApiKey">إنشاء مفتاح جديد</button>
                    </div>
                    <small class="text-muted">يتم استخدام مفتاح API للوصول إلى خدمات API الخاصة بالنظام.</small>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enable_api" name="enable_api" <?php echo ($settings['enable_api'] ?? 'yes') === 'yes' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="enable_api">تفعيل خدمات API</label>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="update_api_settings" class="btn btn-primary">حفظ إعدادات API</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Generate random API key
    $('#generateApiKey').on('click', function() {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        
        for (let i = 0; i < 32; i++) {
            result += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        
        $('#api_key').val(result);
    });
});
</script>