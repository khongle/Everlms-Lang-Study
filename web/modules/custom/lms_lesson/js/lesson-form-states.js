(function (Drupal, once) {
  const groups = {
    video: [
      '.field--name-field-video-url',
      '.field--name-field-video-file',
    ],
    h5p: [
      '.field--name-field-h5p-embed',
      '.field--name-field-h5p',
      '.field--name-field-h5p-interactive-file',
      '.field--name-field-h5p-interactive',
      '.field--name-field-h5p-file',
      '.field--name-field-h5p-content',
      '.field--name-field-h5p-upload',
    ],
    scorm: [
      '.field--name-field-scorm-package',
    ],
    pptx: [
      '.field--name-field-pptx-attachment',
    ],
    zoom: [
      '.field--name-field-zoom-meeting-id',
      '.field--name-field-zoom-schedule',
    ],
    quiz: [
      '.field--name-field-quiz',
    ],
  };

  const allSelectors = Object.values(groups).flat();

  function getLessonScope(select) {
    return select.closest('.ief-form') || select.closest('form') || document;
  }

  function setVisible(element, visible) {
    element.hidden = !visible;
    element.style.display = visible ? '' : 'none';
  }

  function update(select) {
    const scope = getLessonScope(select);
    const selectedType = select.value || 'text';
    const visibleGroups = selectedType === 'mixed'
      ? Object.keys(groups)
      : [selectedType];

    allSelectors.forEach((selector) => {
      scope.querySelectorAll(selector).forEach((element) => setVisible(element, false));
    });

    visibleGroups.forEach((group) => {
      (groups[group] || []).forEach((selector) => {
        scope.querySelectorAll(selector).forEach((element) => setVisible(element, true));
      });
    });
  }

  Drupal.behaviors.lmsLessonFormStates = {
    attach(context) {
      once(
        'lms-lesson-form-states',
        'select[name="field_type"], select[name$="[field_type]"]',
        context,
      ).forEach((select) => {
        update(select);
        select.addEventListener('change', () => update(select));
      });
    },
  };
})(Drupal, once);
