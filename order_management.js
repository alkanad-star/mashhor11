/**
 * Order Management JavaScript Functions
 * 
 * This file contains the JavaScript functions for managing orders
 * in the admin dashboard.
 */

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeOrderManagement();
});

/**
 * Initialize all order management functionality
 */
function initializeOrderManagement() {
    // Initialize status field handlers
    initializeStatusFields();
    
    // Initialize real-time search
    initializeOrderSearch();
    
    // Initialize DataTables
    initializeDataTables();
    
    // Initialize tab switching
    initializeTabSwitching();
    
    // Initialize order statistics
    loadOrderStatistics();
    
    // Initialize refresh and export buttons
    initializeActionButtons();
}

/**
 * Initialize status field change handlers
 */
function initializeStatusFields() {
    // Show/hide partial remains field based on status selection
    const statusSelects = document.querySelectorAll('select[id^="status"]');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.id.replace('status', '');
            const remainsField = document.getElementById('partialRemains' + orderId);
            
            if (remainsField) {
                if (this.value === 'partial') {
                    remainsField.style.display = 'block';
                } else {
                    remainsField.style.display = 'none';
                }
            }
        });
    });
    
    // Handle order status update via AJAX
    const orderForms = document.querySelectorAll('form[data-order-update="true"]');
    orderForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            updateOrderStatus(this);
        });
    });
}

/**
 * Update order status via AJAX
 * @param {HTMLFormElement} form The form element
 */
function updateOrderStatus(form) {
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    // Disable button and show loading
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحديث...';
    
    // Send AJAX request
    fetch('update_order_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            showAlert('danger', data.error);
        } else {
            showAlert('success', data.message);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
            if (modal) {
                modal.hide();
            }
            
            // Reload page after a delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    })
    .catch(error => {
        console.error('Error updating order:', error);
        showAlert('danger', 'حدث خطأ أثناء تحديث الطلب. يرجى المحاولة مرة أخرى.');
    })
    .finally(() => {
        // Re-enable button
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    });
}

/**
 * Initialize real-time order search
 */
function initializeOrderSearch() {
    const orderSearch = document.getElementById('orderSearch');
    const searchButton = document.getElementById('searchButton');
    
    if (orderSearch) {
        // Perform search on input
        orderSearch.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (searchTerm.length >= 2 || searchTerm === '') {
                performOrderSearch(searchTerm);
            }
        });
        
        // Perform search on button click
        if (searchButton) {
            searchButton.addEventListener('click', function() {
                performOrderSearch(orderSearch.value.trim());
            });
        }
        
        // Handle Enter key press
        orderSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performOrderSearch(this.value.trim());
            }
        });
    }
}

/**
 * Perform real-time order search
 * @param {string} searchTerm The search term
 */
function performOrderSearch(searchTerm) {
    // Get the active tab ID
    const activeTabPane = document.querySelector('.tab-pane.active');
    if (!activeTabPane) return;
    
    const activeTabId = activeTabPane.id;
    let status = 'all';
    
    // Determine status based on active tab
    switch (activeTabId) {
        case 'pending-orders':
            status = 'pending';
            break;
        case 'processing-orders':
            status = 'processing';
            break;
        case 'completed-orders':
            status = 'completed';
            break;
        case 'partial-orders':
            status = 'partial';
            break;
        case 'cancelled-orders':
            status = 'cancelled';
            break;
        case 'failed-orders':
            status = 'failed';
            break;
    }
    
    if (searchTerm.length < 2 && searchTerm !== '') {
        return; // Skip short search terms
    }
    
    // Use client-side filtering for efficiency
    filterOrderTables(searchTerm);
    
    // Only use AJAX for longer searches
    if (searchTerm.length >= 3) {
        // Send AJAX search request for more complex searches
        fetch('search_orders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `search_term=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(status)}`
        })
        .then(response => response.json())
        .then(data => {
            // Handle AJAX search results if needed for complex searches
            console.log("AJAX search results:", data);
        })
        .catch(error => {
            console.error('Error searching orders:', error);
        });
    }
}

/**
 * Filter order tables client-side
 * @param {string} searchTerm The search term
 */
function filterOrderTables(searchTerm) {
    searchTerm = searchTerm.toLowerCase();
    
    // Define all tables to search
    const tables = [
        'allOrdersTable',
        'pendingOrdersTable',
        'processingOrdersTable',
        'completedOrdersTable',
        'partialOrdersTable',
        'cancelledOrdersTable',
        'failedOrdersTable'
    ];
    
    // Search in all tables
    tables.forEach(tableId => {
        const table = document.getElementById(tableId);
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const username = row.cells[1]?.textContent.toLowerCase() || '';
                const serviceName = row.cells[2]?.textContent.toLowerCase() || '';
                const orderId = row.cells[0]?.textContent.toLowerCase() || '';
                
                // Show/hide row based on search term
                if (searchTerm === '' || 
                    username.includes(searchTerm) || 
                    serviceName.includes(searchTerm) ||
                    orderId.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show a message if no results found
            const allHidden = Array.from(rows).every(row => row.style.display === 'none');
            let noResultsRow = table.querySelector('.no-results-row');
            
            if (allHidden && searchTerm !== '') {
                if (!noResultsRow) {
                    const tbody = table.querySelector('tbody');
                    noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-results-row';
                    const td = document.createElement('td');
                    td.colSpan = table.querySelector('thead tr').cells.length;
                    td.className = 'text-center py-3';
                    td.textContent = 'لا توجد نتائج للبحث: "' + searchTerm + '"';
                    noResultsRow.appendChild(td);
                    tbody.appendChild(noResultsRow);
                } else {
                    noResultsRow.querySelector('td').textContent = 'لا توجد نتائج للبحث: "' + searchTerm + '"';
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }
        }
    });
    
    // Highlight search terms in the active tab
    highlightSearchTerms(searchTerm);
}

/**
 * Highlight search terms in the table
 * @param {string} searchTerm The search term
 */
function highlightSearchTerms(searchTerm) {
    if (!searchTerm) return;
    
    // Get the active tab
    const activeTabPane = document.querySelector('.tab-pane.active');
    if (!activeTabPane) return;
    
    const table = activeTabPane.querySelector('table');
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        if (row.style.display !== 'none' && !row.classList.contains('no-results-row')) {
            const cells = row.querySelectorAll('td');
            
            cells.forEach((cell, index) => {
                // Skip action columns and columns with badges/buttons
                if (index === cells.length - 1 || cell.querySelector('button') || cell.querySelector('.badge') || cell.querySelector('.progress')) {
                    return;
                }
                
                const originalText = cell.textContent;
                // Reset any previous highlighting
                cell.innerHTML = originalText;
                
                if (searchTerm) {
                    const lowerText = originalText.toLowerCase();
                    const index = lowerText.indexOf(searchTerm.toLowerCase());
                    
                    if (index >= 0) {
                        const prefix = originalText.substring(0, index);
                        const match = originalText.substring(index, index + searchTerm.length);
                        const suffix = originalText.substring(index + searchTerm.length);
                        
                        cell.innerHTML = prefix + '<span class="bg-warning">' + match + '</span>' + suffix;
                    }
                }
            });
        }
    });
}

/**
 * Initialize DataTables for all order tables
 */
function initializeDataTables() {
    $('.datatable').each(function() {
        const tableId = $(this).attr('id');
        const pageLength = 25;
        
        // Check if table has already been initialized
        if ($.fn.dataTable.isDataTable(this)) {
            return;
        }
        
        $(this).DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
            },
            "order": [[0, "desc"]],
            "pageLength": pageLength,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "الكل"]],
            "dom": '<"top"fl>rt<"bottom"ip>',
            "responsive": true,
            "stateSave": true,
            "drawCallback": function() {
                // Apply highlights after DataTable draw
                const searchTerm = document.getElementById('orderSearch')?.value.trim();
                if (searchTerm) {
                    highlightSearchTerms(searchTerm);
                }
            }
        });
    });
}

/**
 * Initialize tab switching event handlers
 */
function initializeTabSwitching() {
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            // Clear search when changing tabs
            const searchInput = document.getElementById('orderSearch');
            if (searchInput && searchInput.value.trim() !== '') {
                const searchTerm = searchInput.value.trim();
                
                // Re-apply search to ensure the new tab is filtered
                setTimeout(() => {
                    performOrderSearch(searchTerm);
                }, 100);
            }
            
            // Update URL hash to persist tab selection
            const tabId = e.target.getAttribute('data-bs-target').substring(1);
            window.location.hash = tabId;
        });
    });
    
    // Activate tab based on URL hash on page load
    const hash = window.location.hash.substring(1);
    if (hash && document.getElementById(hash)) {
        const tabElement = document.querySelector(`button[data-bs-target="#${hash}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }
}

/**
 * Load order statistics
 */
function loadOrderStatistics() {
    // Check if stats container exists
    const statsContainer = document.getElementById('orderStatistics');
    if (!statsContainer) return;
    
    // Get date range if applicable
    const startDate = document.getElementById('startDate')?.value;
    const endDate = document.getElementById('endDate')?.value;
    
    // Build query string
    let queryString = '';
    if (startDate && endDate) {
        queryString = `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    }
    
    // Fetch statistics
    fetch(`get_order_statistics.php${queryString}`)
        .then(response => response.json())
        .then(data => {
            updateOrderStatsDisplay(data);
            createOrderCharts(data);
        })
        .catch(error => {
            console.error('Error loading order statistics:', error);
            statsContainer.innerHTML = '<div class="alert alert-danger">حدث خطأ أثناء تحميل الإحصائيات</div>';
        });
}

/**
 * Update order statistics display
 * @param {Object} data The statistics data
 */
function updateOrderStatsDisplay(data) {
    // Update counts
    document.getElementById('totalOrdersCount')?.textContent = data.statusCounts.total;
    document.getElementById('pendingOrdersCount')?.textContent = data.statusCounts.pending;
    document.getElementById('processingOrdersCount')?.textContent = data.statusCounts.processing;
    document.getElementById('completedOrdersCount')?.textContent = data.statusCounts.completed;
    document.getElementById('partialOrdersCount')?.textContent = data.statusCounts.partial;
    document.getElementById('cancelledOrdersCount')?.textContent = data.statusCounts.cancelled;
    document.getElementById('failedOrdersCount')?.textContent = data.statusCounts.failed;
    
    // Update amounts
    document.getElementById('totalOrdersAmount')?.textContent = ' + data.statusAmounts.total.toFixed(2);
    document.getElementById('pendingOrdersAmount')?.textContent = ' + data.statusAmounts.pending.toFixed(2);
    document.getElementById('processingOrdersAmount')?.textContent = ' + data.statusAmounts.processing.toFixed(2);
    document.getElementById('completedOrdersAmount')?.textContent = ' + data.statusAmounts.completed.toFixed(2);
    document.getElementById('partialOrdersAmount')?.textContent = ' + data.statusAmounts.partial.toFixed(2);
    document.getElementById('cancelledOrdersAmount')?.textContent = ' + data.statusAmounts.cancelled.toFixed(2);
    document.getElementById('failedOrdersAmount')?.textContent = ' + data.statusAmounts.failed.toFixed(2);
}

/**
 * Create order charts
 * @param {Object} data The statistics data
 */
function createOrderCharts(data) {
    // Create status distribution chart
    const statusChart = document.getElementById('orderStatusChart');
    if (statusChart) {
        const statusCounts = data.statusCounts;
        const statusLabels = {
            'pending': 'قيد الانتظار',
            'processing': 'قيد التنفيذ',
            'completed': 'مكتمل',
            'partial': 'جزئي',
            'cancelled': 'ملغي',
            'failed': 'فشل'
        };
        
        const statusColors = {
            'pending': '#ffc107',
            'processing': '#17a2b8',
            'completed': '#28a745',
            'partial': '#007bff',
            'cancelled': '#6c757d',
            'failed': '#dc3545'
        };
        
        const labels = Object.keys(statusCounts)
            .filter(key => key !== 'total')
            .map(key => statusLabels[key] || key);
            
        const counts = Object.keys(statusCounts)
            .filter(key => key !== 'total')
            .map(key => statusCounts[key]);
            
        const colors = Object.keys(statusCounts)
            .filter(key => key !== 'total')
            .map(key => statusColors[key] || '#000000');
        
        // Create the chart
        new Chart(statusChart, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Create daily orders chart
    const dailyChart = document.getElementById('dailyOrdersChart');
    if (dailyChart && data.dailyStats && data.dailyStats.length > 0) {
        // Reverse the array to show dates in ascending order
        const dailyStats = [...data.dailyStats].reverse();
        
        const labels = dailyStats.map(stat => stat.date);
        const counts = dailyStats.map(stat => stat.count);
        const amounts = dailyStats.map(stat => stat.amount);
        
        new Chart(dailyChart, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'عدد الطلبات',
                        data: counts,
                        backgroundColor: 'rgba(0, 123, 255, 0.5)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'المبلغ ($)',
                        data: amounts,
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            font: {
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            }
                        }
                    },
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'عدد الطلبات',
                            font: {
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: {
                            display: true,
                            text: 'المبلغ ($)',
                            font: {
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            }
                        }
                    }
                }
            }
        });
    }
}

/**
 * Initialize action buttons (refresh, export)
 */
function initializeActionButtons() {
    // Refresh button
    const refreshButton = document.getElementById('refreshOrders');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            window.location.reload();
        });
    }
    
    // Export button
    const exportButton = document.getElementById('exportOrdersCSV');
    if (exportButton) {
        exportButton.addEventListener('click', function() {
            exportTableToCSV();
        });
    }
    
    // Date filter buttons
    const applyDateFilterButton = document.getElementById('applyDateFilter');
    if (applyDateFilterButton) {
        applyDateFilterButton.addEventListener('click', function() {
            loadOrderStatistics();
        });
    }
    
    const resetDateFilterButton = document.getElementById('resetDateFilter');
    if (resetDateFilterButton) {
        resetDateFilterButton.addEventListener('click', function() {
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            loadOrderStatistics();
        });
    }
}

/**
 * Export table to CSV
 */
function exportTableToCSV() {
    // Get the active tab
    const activeTabPane = document.querySelector('.tab-pane.active');
    if (!activeTabPane) return;
    
    const activeTabId = activeTabPane.id;
    let status = 'all';
    
    // Determine status based on active tab
    switch (activeTabId) {
        case 'pending-orders':
            status = 'pending';
            break;
        case 'processing-orders':
            status = 'processing';
            break;
        case 'completed-orders':
            status = 'completed';
            break;
        case 'partial-orders':
            status = 'partial';
            break;
        case 'cancelled-orders':
            status = 'cancelled';
            break;
        case 'failed-orders':
            status = 'failed';
            break;
    }
    
    // Get search term
    const searchTerm = document.getElementById('orderSearch')?.value.trim() || '';
    
    // Build query string
    let queryString = `?status=${encodeURIComponent(status)}`;
    if (searchTerm) {
        queryString += `&search=${encodeURIComponent(searchTerm)}`;
    }
    
    // Download the CSV
    window.location.href = `export_orders.php${queryString}`;
}

/**
 * Show alert message
 * @param {string} type The alert type ('success', 'danger', 'warning', 'info')
 * @param {string} message The alert message
 */
function showAlert(type, message) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4`;
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.maxWidth = '500px';
    
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to document
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => {
            alertDiv.remove();
        }, 150);
    }, 5000);
}