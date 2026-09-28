(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsCourseEditor = {
    attach(context) {
      once('lms-course-editor-help', '.lms-course-editor .field--name-field-chapters', context).forEach((curriculum) => {
        const help = document.createElement('p');
        help.className = 'lms-course-editor__help';
        help.textContent = Drupal.t('Edit chapters and lessons here. Update keeps changes in this form; Save course applies them. Cancel closes the current editor.');
        curriculum.prepend(help);
      });

      once('lms-course-inline-editor', '.lms-course-inline-editor', context).forEach((editor) => {
        // AJAX adds the real IEF form in place. Focus its heading without moving or
        // cloning fields, so uploads, CKEditor and Drupal's submit state remain intact.
        const heading = editor.querySelector('.lms-course-inline-editor__heading');
        if (heading) heading.setAttribute('tabindex', '-1');
        const invalid = editor.querySelector('[aria-invalid="true"], .form-item--error input, .form-item--error select, .form-item--error textarea');
        if (invalid) {
          invalid.focus({ preventScroll: true });
          invalid.scrollIntoView({ block: 'nearest' });
        } else if (heading && context !== document) {
          heading.focus({ preventScroll: true });
          heading.scrollIntoView({ block: 'nearest' });
        }
      });
    },
  };
})(Drupal, once);
