(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.lmsAiEngineForm = {
    attach: function (context, settings) {
      if (!settings || typeof settings.lmsAiEngine === 'undefined') {
        return;
      }

      var defaults = settings.lmsAiEngine.defaults || {};

      $('.load-default', context).off('click').on('click', function(e) {
        e.preventDefault();
        var textarea = $(this).closest('.js-form-item, .form-item').find('textarea');
        if (textarea.length > 0) {
          var fieldName = textarea.attr('name');
          if (typeof defaults[fieldName] !== 'undefined') {
             textarea.val(defaults[fieldName]);
          } else {
             console.warn('Default not found for field:', fieldName);
          }
        }
      });

      $('.reset', context).off('click').on('click', function(e) {
        e.preventDefault();
        var textarea = $(this).closest('.js-form-item, .form-item').find('textarea');
        if (textarea.length > 0) {
          var fieldName = textarea.attr('name');
          if (typeof defaults[fieldName] !== 'undefined') {
             textarea.val(defaults[fieldName]);
          }
        }
      });

      $('.clear', context).off('click').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.js-form-item, .form-item').find('textarea').val('');
      });

      // Listener cho auto-run
      $('html').once('lmsAiEngineAutoRun').on('course_detail_continue', function() {
        console.log("Auto triggering next course in 1s...");
        setTimeout(function() {
          // Bắt buộc dùng mousedown với AJAX Drupal thay vì click
          $('#edit-generate-courses-detail').trigger('mousedown');
        }, 1000);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
