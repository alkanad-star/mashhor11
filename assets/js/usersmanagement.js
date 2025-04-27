/**
 * User Management JavaScript Functions
 * For handling dynamic functionality in admin user management panel
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables for user list
    if (document.querySelector('.datatable')) {
        $('.datatable').DataTable({
            "ordering": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
            },
            "order": [[0, "desc"]]
        });
    }

    // User search functionality
    initUserSearch();

    // Toggle password visibility
    initPasswordToggle();

    // Balance adjustment modal
    initBalanceAdjustment();

    // Confirmation modals
    initConfirmationModals();

    // Copy referral code
    initCopyFunctionality();

    // Show partial remains field only when partial status is selected
    initStatusChange();

    // Auto-populate phone based on country selection
    initCountryPhoneCodes();

    // CSV Import validation
    initCSVImportValidation();

    // Generate random password
    initPasswordGenerator();
});

/**
 * Initialize user search functionality
 */
function initUserSearch() {
    const userSearchInput = document.getElementById('userSearch');
    const searchResults = document.getElementById('searchResults');

    if (userSearchInput) {
        userSearchInput.addEventListener('input', function() {
            const username = this.value;
            if (username.length >= 3) {
                fetchUsersByUsername(username);
            } else {
                if (searchResults) searchResults.style.display = 'none';
            }
        });

        // Select user from search results
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('user-result')) {
                userSearchInput.value = e.target.dataset.username;
                if (searchResults) searchResults.style.display = 'none';
            }
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== userSearchInput && searchResults) {
                searchResults.style.display = 'none';
            }
        });
    }
}

/**
 * Fetch users by username via AJAX
 */
function fetchUsersByUsername(username) {
    const searchResults = document.getElementById('searchResults');
    if (!searchResults) return;

    $.ajax({
        url: 'admin/search_user.php',
        method: 'POST',
        data: { username: username },
        dataType: 'json',
        success: function(response) {
            let results = '';
            if (response.length > 0) {
                response.forEach(function(user) {
                    results += `<div class="user-result p-2 border-bottom" data-username="${user.username}" data-id="${user.id}">
                               ${user.username} - ${user.email}
                           </div>`;
                });
            } else {
                results = '<div class="p-2">لا توجد نتائج</div>';
            }
            searchResults.innerHTML = results;
            searchResults.style.display = 'block';
        },
        error: function(xhr, status, error) {
            console.error("Error searching for users:", error);
            searchResults.innerHTML = '<div class="p-2 text-danger">حدث خطأ أثناء البحث</div>';
            searchResults.style.display = 'block';
        }
    });
}

/**
 * Initialize password visibility toggle
 */
function initPasswordToggle() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });
}

/**
 * Initialize balance adjustment modal
 */
function initBalanceAdjustment() {
    // Update preview amount when amount or operation changes
    const balanceModals = document.querySelectorAll('[id^="adjustBalanceModal"]');
    balanceModals.forEach(modal => {
        const operationSelect = modal.querySelector('select[name="operation"]');
        const amountInput = modal.querySelector('input[name="amount"]');
        const currentBalance = modal.querySelector('.current-balance-value')?.textContent || '0.00';
        const previewElement = modal.querySelector('.balance-preview');
        
        if (operationSelect && amountInput && previewElement) {
            const updatePreview = function() {
                const operation = operationSelect.value;
                const amount = parseFloat(amountInput.value) || 0;
                const current = parseFloat(currentBalance.replace(/[^0-9.-]+/g, '')) || 0;
                
                let newBalance = current;
                if (operation === 'add') {
                    newBalance = current + amount;
                } else if (operation === 'subtract') {
                    newBalance = current - amount;
                } else if (operation === 'set') {
                    newBalance = amount;
                }
                
                previewElement.textContent = '$' + newBalance.toFixed(2);
                
                // Apply color based on change
                if (newBalance > current) {
                    previewElement.classList.remove('text-danger');
                    previewElement.classList.add('text-success');
                } else if (newBalance < current) {
                    previewElement.classList.remove('text-success');
                    previewElement.classList.add('text-danger');
                } else {
                    previewElement.classList.remove('text-success', 'text-danger');
                }
            };
            
            operationSelect.addEventListener('change', updatePreview);
            amountInput.addEventListener('input', updatePreview);
            updatePreview(); // Initialize
        }
    });
}

/**
 * Initialize confirmation modals
 */
function initConfirmationModals() {
    // Add extra confirmation for destructive actions
    const dangerousForms = document.querySelectorAll('form[data-confirm="true"]');
    dangerousForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('هل أنت متأكد من تنفيذ هذا الإجراء؟')) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Initialize copy functionality for referral codes
 */
function initCopyFunctionality() {
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-clipboard-text');
            
            // Create temporary input element
            const tempInput = document.createElement('input');
            tempInput.value = textToCopy;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            // Show feedback
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                this.innerHTML = originalHTML;
            }, 2000);
        });
    });
}

/**
 * Show/hide partial remains field based on status
 */
function initStatusChange() {
    const statusSelects = document.querySelectorAll('select[id^="status"]');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.id.replace('status', '');
            const remainsField = document.getElementById('partialRemains' + orderId);
            
            if (this.value === 'partial' && remainsField) {
                remainsField.style.display = 'block';
            } else if (remainsField) {
                remainsField.style.display = 'none';
            }
        });
    });
}

/**
 * Initialize country-based phone code population
 */
function initCountryPhoneCodes() {
    const dialCodes = {
        AE: '+971',
        SA: '+966',
        EG: '+20',
        JO: '+962',
        BH: '+973',
        DZ: '+213',
        IQ: '+964',
        KW: '+965',
        LB: '+961',
        LY: '+218',
        MA: '+212',
        OM: '+968',
        PS: '+970',
        QA: '+974',
        SD: '+249',
        SY: '+963',
        TN: '+216',
        YE: '+967'
    };

    const countrySelects = document.querySelectorAll('select[name="country"]');
    countrySelects.forEach(select => {
        select.addEventListener('change', function() {
            const phoneInput = this.form.querySelector('input[name="phone"]');
            if (phoneInput && (phoneInput.value === '' || phoneInput.value.startsWith('+'))) {
                const code = dialCodes[this.value] || '';
                phoneInput.value = code;
                phoneInput.setSelectionRange(code.length, code.length);
                phoneInput.focus();
            }
        });
    });
}

/**
 * Validate CSV import file
 */
function initCSVImportValidation() {
    const importForm = document.querySelector('form[name="import_users_form"]');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const fileInput = this.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
                    e.preventDefault();
                    alert('يرجى اختيار ملف بتنسيق CSV فقط.');
                } else if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    e.preventDefault();
                    alert('حجم الملف كبير جدًا. الحد الأقصى هو 5 ميجابايت.');
                }
            }
        });
    }
}

/**
 * Generate random password
 */
function initPasswordGenerator() {
    const generateButton = document.querySelector('.generate-password');
    if (generateButton) {
        generateButton.addEventListener('click', function() {
            const passwordInput = document.getElementById('password') || document.getElementById('new_password');
            if (passwordInput) {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
                let password = '';
                
                // Generate a password of length 10
                for (let i = 0; i < 10; i++) {
                    password += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                
                passwordInput.value = password;
                passwordInput.setAttribute('type', 'text');
                
                // Update toggle button icon if exists
                const toggleButton = passwordInput.closest('.input-group')?.querySelector('.toggle-password i');
                if (toggleButton) {
                    toggleButton.classList.remove('fa-eye');
                    toggleButton.classList.add('fa-eye-slash');
                }
            }
        });
    }
}