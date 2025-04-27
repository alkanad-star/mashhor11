/**
 * Notification Utilities
 * A set of utilities for handling browser notifications
 */

const NotificationUtil = {
    /**
     * Check if browser notifications are supported
     * @returns {boolean} True if notifications are supported
     */
    isSupported: function() {
        return 'Notification' in window;
    },
    
    /**
     * Get the current notification permission status
     * @returns {string} "default", "granted", or "denied"
     */
    getPermissionStatus: function() {
        if (!this.isSupported()) {
            return 'unsupported';
        }
        return Notification.permission;
    },
    
    /**
     * Request notification permission
     * @returns {Promise<string>} Promise resolving to the permission state
     */
    requestPermission: function() {
        if (!this.isSupported()) {
            return Promise.reject('Notifications not supported');
        }
        
        return Notification.requestPermission();
    },
    
    /**
     * Show a notification
     * @param {string} title The notification title
     * @param {Object} options Notification options (body, icon, etc.)
     * @param {Function} clickCallback Function to call when notification is clicked
     * @returns {Notification|null} The notification object or null if not supported/permission denied
     */
    showNotification: function(title, options = {}, clickCallback = null) {
        if (!this.isSupported() || Notification.permission !== 'granted') {
            return null;
        }
        
        // Set default icon if not provided
        if (!options.icon) {
            options.icon = '/images/logo.png';
        }
        
        // Create and show notification
        const notification = new Notification(title, options);
        
        // Handle click event
        if (typeof clickCallback === 'function') {
            notification.onclick = clickCallback;
        }
        
        return notification;
    },
    
    /**
     * Set up notification polling
     * @param {string} endpoint The endpoint to poll for notifications
     * @param {number} interval Polling interval in milliseconds
     * @param {Function} handleNotifications Function to handle received notifications
     * @returns {number} The interval ID for clearing later
     */
    startPolling: function(endpoint, interval, handleNotifications) {
        // Initial check
        this.checkForNotifications(endpoint, handleNotifications);
        
        // Set up interval
        return setInterval(() => {
            this.checkForNotifications(endpoint, handleNotifications);
        }, interval);
    },
    
    /**
     * Stop notification polling
     * @param {number} intervalId The interval ID to clear
     */
    stopPolling: function(intervalId) {
        clearInterval(intervalId);
    },
    
    /**
     * Check for new notifications
     * @param {string} endpoint The endpoint to check for notifications
     * @param {Function} handleNotifications Function to handle received notifications
     */
    checkForNotifications: function(endpoint, handleNotifications) {
        fetch(endpoint)
            .then(response => response.json())
            .then(data => {
                if (typeof handleNotifications === 'function') {
                    handleNotifications(data);
                }
                
                // If we have permission and there are new notifications, show them
                if (this.getPermissionStatus() === 'granted' && data.hasNew && data.newNotifications) {
                    data.newNotifications.forEach(notification => {
                        this.showNotification(
                            notification.title, 
                            { 
                                body: notification.message,
                                tag: 'notification-' + notification.id
                            },
                            function() {
                                // When notification is clicked, navigate to appropriate page
                                if (notification.action_url) {
                                    window.open(notification.action_url, '_blank');
                                } else {
                                    window.open('notifications.php', '_blank');
                                }
                            }
                        );
                    });
                }
            })
            .catch(error => console.error('Error checking for notifications:', error));
    },
    
    /**
     * Initialize notification system
     * @param {Object} options Configuration options
     */
    init: function(options = {}) {
        const defaults = {
            endpoint: 'check_notifications.php',
            interval: 60000, // 1 minute
            showPermissionPrompt: true,
            permissionPromptDelay: 2000,
            handleNotifications: null
        };
        
        const config = {...defaults, ...options};
        let pollingId = null;
        
        // Check if notifications are supported
        if (!this.isSupported()) {
            console.log('Browser notifications are not supported');
            return;
        }
        
        // Handle permission prompt
        if (config.showPermissionPrompt && this.getPermissionStatus() === 'default') {
            setTimeout(() => {
                // Show custom permission prompt
                const permissionCard = document.getElementById('notificationPermissionCard');
                if (permissionCard) {
                    permissionCard.style.display = 'block';
                    
                    // Handle permission button click
                    const permissionBtn = document.getElementById('enableNotificationsBtn');
                    if (permissionBtn) {
                        permissionBtn.addEventListener('click', () => {
                            this.requestPermission().then(permission => {
                                if (permission === 'granted') {
                                    // Update UI
                                    permissionCard.innerHTML = `
                                        <div class="card-body">
                                            <div class="alert alert-success mb-0">
                                                <i class="fas fa-check-circle me-2"></i> تم تفعيل الإشعارات بنجاح!
                                            </div>
                                        </div>
                                    `;
                                    
                                    // Hide the card after 3 seconds
                                    setTimeout(() => {
                                        permissionCard.style.opacity = '0';
                                        setTimeout(() => {
                                            permissionCard.style.display = 'none';
                                        }, 300);
                                    }, 3000);
                                    
                                    // Show welcome notification
                                    this.showNotification(
                                        'مرحباً بك في متجر مشهور',
                                        {
                                            body: 'تم تفعيل الإشعارات بنجاح! ستصلك الآن تنبيهات فورية عند استلام إشعارات جديدة.'
                                        }
                                    );
                                    
                                    // Start polling if permission granted
                                    if (pollingId === null) {
                                        pollingId = this.startPolling(config.endpoint, config.interval, config.handleNotifications);
                                    }
                                } else if (permission === 'denied') {
                                    // Update button to show denied state
                                    permissionBtn.textContent = 'الإشعارات مرفوضة';
                                    permissionBtn.classList.remove('btn-primary');
                                    permissionBtn.classList.add('btn-secondary');
                                    permissionBtn.disabled = true;
                                }
                            });
                        });
                    }
                }
            }, config.permissionPromptDelay);
        } else if (this.getPermissionStatus() === 'granted') {
            // Start polling immediately if permission already granted
            pollingId = this.startPolling(config.endpoint, config.interval, config.handleNotifications);
        }
        
        // Return methods for controlling polling
        return {
            startPolling: () => {
                if (pollingId === null && this.getPermissionStatus() === 'granted') {
                    pollingId = this.startPolling(config.endpoint, config.interval, config.handleNotifications);
                }
            },
            stopPolling: () => {
                if (pollingId !== null) {
                    this.stopPolling(pollingId);
                    pollingId = null;
                }
            },
            checkNow: () => {
                this.checkForNotifications(config.endpoint, config.handleNotifications);
            }
        };
    }
};

// Usage example:
// document.addEventListener('DOMContentLoaded', function() {
//     const notificationSystem = NotificationUtil.init({
//         handleNotifications: function(data) {
//             // Update notification badges
//             if (data.unreadCount > 0) {
//                 // Update badges
//             }
//         }
//     });
// });