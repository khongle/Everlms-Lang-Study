(function ($, Drupal, once) {
  Drupal.behaviors.zoomCountdown = {
    attach: function (context, settings) {
      $(once('zoomCountdown', '.zoom-countdown-container', context)).each(function () {
        var $container = $(this);
        var scheduleTime = parseInt($container.data('schedule-time'), 10) * 1000;
        var joinUrl = $container.data('join-url');
        var $timerDisplay = $container.find('.timer-display');
        var $joinBtn = $container.find('.btn-join-zoom');

        function updateTimer() {
          var now = new Date().getTime();
          var distance = scheduleTime - now;
          var minutesBeforeToAllowJoin = 15;
          var timeBeforeToAllowJoinMs = minutesBeforeToAllowJoin * 60 * 1000;

          if (distance <= timeBeforeToAllowJoinMs) {
            $timerDisplay.html("The class is open!");
            $joinBtn.removeClass('disabled').removeAttr('disabled').attr('href', joinUrl);
            return;
          }

          var days = Math.floor(distance / (1000 * 60 * 60 * 24));
          var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
          var seconds = Math.floor((distance % (1000 * 60)) / 1000);

          var display = '';
          if (days > 0) display += days + "d ";
          display += hours + "h " + minutes + "m " + seconds + "s ";
          
          $timerDisplay.html("Class starts in: <strong>" + display + "</strong>");
        }

        updateTimer();
        setInterval(updateTimer, 1000);
      });
    }
  };
})(jQuery, Drupal, once);
