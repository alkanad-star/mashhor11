<?php
// notification_js.php - Include this at the bottom of pages where you want real-time notifications
// This will be included in header.php

// Only show for logged in users
if (isset($_SESSION['user_id'])):
?>
<script src="/notification-utilities.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize notification system
    const notificationSystem = NotificationUtil.init({
        endpoint: 'check_notifications.php',
        interval: 60000, // Check every minute
        handleNotifications: function(data) {
            // Update notification badges
            updateNotificationBadge(data.unreadCount);
        }
    });
    
    // Function to update notification badges
    function updateNotificationBadge(count) {
        // Update desktop notification badge
        const desktopBadge = document.querySelector('.navbar .notification-badge');
        if (desktopBadge) {
            if (count > 0) {
                desktopBadge.textContent = count;
                desktopBadge.style.display = 'inline-block';
            } else {
                desktopBadge.style.display = 'none';
            }
        }
        
        // Update mobile notification badge
        const mobileBadge = document.querySelector('#aside-menu .notification-badge');
        if (mobileBadge) {
            if (count > 0) {
                mobileBadge.textContent = count;
                mobileBadge.style.display = 'inline-block';
            } else {
                mobileBadge.style.display = 'none';
            }
        }
        
        // Update menu item badge
        const menuItemBadge = document.querySelector('.profile-menu-item .notification-badge');
        if (menuItemBadge) {
            if (count > 0) {
                menuItemBadge.textContent = count;
                menuItemBadge.style.display = 'inline-block';
            } else {
                menuItemBadge.style.display = 'none';
            }
        }
    }
});
</script>
<?php endif; ?>