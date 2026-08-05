(function (Drupal, once) {
  function formatValue(key, value) {
    if (value === null || value === undefined || value === '') {
      return '--';
    }

    if (key === 'average_progress' || key === 'completion_rate') {
      return `${Number(value).toFixed(1)}%`;
    }

    return `${value}`;
  }

  function updatePopup(popup, counts) {
    Object.entries(counts || {}).forEach(([key, value]) => {
      const target = popup.querySelector(`[data-stat-value="${key}"]`);
      if (target) {
        target.textContent = formatValue(key, value);
      }
    });

    const hint = popup.querySelector('[data-course-stats-hint]');
    if (hint) {
      hint.textContent = 'Fresh instructor stats loaded for this course.';
    }
  }

  function loadSummary(card, popup) {
    const summaryUrl = card.dataset.statsSummaryUrl;
    if (!summaryUrl || card.dataset.statsLoaded === 'true' || card.dataset.statsLoading === 'true') {
      return;
    }

    card.dataset.statsLoading = 'true';
    fetch(summaryUrl, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
      })
      .then((payload) => {
        updatePopup(popup, payload.counts || {});
        card.dataset.statsLoaded = 'true';
      })
      .catch(() => {
        const hint = popup.querySelector('[data-course-stats-hint]');
        if (hint) {
          hint.textContent = 'Detailed stats are temporarily unavailable. You can still open the detail page.';
        }
      })
      .finally(() => {
        delete card.dataset.statsLoading;
      });
  }

  function initCourseCard(card) {
    const popup = card.querySelector('[data-course-stats-popup]');
    if (!popup) {
      return;
    }

    const openPopup = () => {
      card.classList.add('is-stats-open');
      loadSummary(card, popup);
    };

    const closePopup = (event) => {
      if (event && event.relatedTarget && card.contains(event.relatedTarget)) {
        return;
      }
      card.classList.remove('is-stats-open');
    };

    card.addEventListener('mouseenter', openPopup);
    card.addEventListener('focusin', openPopup);
    card.addEventListener('mouseleave', closePopup);
    card.addEventListener('focusout', closePopup);
  }

  Drupal.behaviors.lmsInstructorCourseStats = {
    attach(context) {
      once('lms-instructor-course-stats', '[data-course-stats-card]', context).forEach(initCourseCard);
    },
  };
})(Drupal, once);
