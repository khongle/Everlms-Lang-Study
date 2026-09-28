/**
 * Accessible workspace navigation for small screens.
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.lmsWorkspaceNavigation = {
    attach: function (context) {
      once('lms-workspace-navigation', '.lms-admin__menu-toggle', context).forEach(function (toggle) {
        var workspace = toggle.closest('.lms-admin');
        var sidebar = workspace.querySelector('.lms-admin__sidebar');
        var backdrop = workspace.querySelector('.lms-admin__menu-backdrop');
        var mobile = window.matchMedia('(max-width: 899px)');

        function setOpen(open, restoreFocus) {
          workspace.classList.toggle('lms-admin--menu-open', open);
          toggle.setAttribute('aria-expanded', String(open));
          backdrop.hidden = !open;
          sidebar.inert = mobile.matches && !open;
          if (open) {
            var firstLink = sidebar.querySelector('a, summary');
            if (firstLink) firstLink.focus();
          } else if (restoreFocus) {
            toggle.focus();
          }
        }

        toggle.addEventListener('click', function () {
          setOpen(toggle.getAttribute('aria-expanded') !== 'true', true);
        });
        backdrop.addEventListener('click', function () { setOpen(false, true); });
        document.addEventListener('keydown', function (event) {
          if (!workspace.classList.contains('lms-admin--menu-open')) return;
          if (event.key === 'Escape') {
            setOpen(false, true);
          } else if (event.key === 'Tab') {
            var focusable = Array.from(sidebar.querySelectorAll('a, summary, button, input')).filter(function (element) {
              return element.getClientRects().length && !element.disabled;
            });
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
              event.preventDefault();
              last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
              event.preventDefault();
              first.focus();
            }
          }
        });
        mobile.addEventListener('change', function () { setOpen(false, false); });
        setOpen(false, false);
      });

      once('lms-workspace-current-page', '.lms-admin__nav-item--active, .lms-admin__subnav-item--active', context).forEach(function (link) {
        link.setAttribute('aria-current', 'page');
      });
    }
  };
})(Drupal, once);
