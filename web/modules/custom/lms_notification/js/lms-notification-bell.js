/**
 * Real-time notification polling.
 */
(function (Drupal) {
  'use strict';

  Drupal.behaviors.lmsNotificationBell = {
    attach: function (context, settings) {
      // Find the counter element.
      const counter = context.querySelector('.lms-bell-counter');
      if (!counter) return;

      // Function to fetch the unread count.
      const fetchUnreadCount = () => {
        fetch('/api/lms/notifications/unread-count', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data && data.count !== undefined) {
            const count = parseInt(data.count, 10);
            if (count > 0) {
              counter.textContent = count > 99 ? '99+' : count;
              counter.style.display = 'flex';
            } else {
              counter.style.display = 'none';
              counter.textContent = '0';
            }
          }
        })
        .catch(error => console.error('Error fetching unread notifications count:', error));
      };

      // Ensure we only set the interval once using a flag.
      if (!counter.hasAttribute('data-polling-initialized')) {
        counter.setAttribute('data-polling-initialized', 'true');
        
        // Fetch immediately to ensure accurate state relative to page cache.
        // Wait 3 seconds so we don't block main page loading.
        setTimeout(fetchUnreadCount, 3000);

        // Poll every 30 seconds (30000 ms).
        setInterval(fetchUnreadCount, 30000);
      }
    }
  };

})(Drupal);
