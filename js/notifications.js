// /js/notifications.js
document.addEventListener('DOMContentLoaded', () => {
  const icon = document.getElementById('notificationIcon');
  const countEl = document.getElementById('notificationCount');
  const popup = document.getElementById('notificationPopup');

  if (!icon || !countEl || !popup) {
    console.warn('Notification elements not found.');
    return;
  }

  // Fetch unread notifications count
  function updateCount() {
    fetch('/check_notifications.php', { credentials: 'same-origin' })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        const count = parseInt(data.count, 10) || 0;
        if (count > 0) {
          countEl.textContent = count;
          countEl.style.display = 'inline-block';
        } else {
          countEl.style.display = 'none';
        }
      })
      .catch(error => {
        console.error('Failed to fetch notification count:', error);
      });
  }

  // Load full notifications list
  function loadNotifications() {
    popup.innerHTML = '<p class="text-center mb-0">جارٍ التحميل…</p>';
    fetch('/notifications.php', { credentials: 'same-origin' })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        return response.text();
      })
      .then(html => {
        popup.innerHTML = html;
      })
      .catch(error => {
        popup.innerHTML = '<p class="text-danger">خطأ في التحميل</p>';
        console.error('Failed to load notifications:', error);
      });
  }

  // Toggle the notification popup
  icon.addEventListener('click', event => {
    event.preventDefault();
    event.stopPropagation();
    if (popup.classList.contains('show')) {
      popup.classList.remove('show');
    } else {
      loadNotifications();
      popup.classList.add('show');
    }
  });

  // Close popup when clicking outside
  document.addEventListener('click', event => {
    if (!icon.contains(event.target) && !popup.contains(event.target)) {
      popup.classList.remove('show');
    }
  });

  // Initial count load and refresh every minute
  updateCount();
  setInterval(updateCount, 60000);
});
