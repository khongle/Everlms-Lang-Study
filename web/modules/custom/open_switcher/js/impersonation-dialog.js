/**
 * @file
 * One-time confirmation dialog shown after switching user accounts.
 */
(function (Drupal, once, drupalSettings) {
  'use strict';

  function closeDialog(dialog, onClose) {
    dialog.classList.remove('is-visible');
    window.setTimeout(function () {
      dialog.remove();
      onClose();
    }, 160);
  }

  Drupal.behaviors.openSwitcherDialog = {
    attach: function (context) {
      once('open-switcher-dialog', 'body', context).forEach(function () {
        var dialogData = drupalSettings.openSwitcher && drupalSettings.openSwitcher.dialog;
        if (!dialogData || !dialogData.message) {
          return;
        }

        drupalSettings.openSwitcher.dialog = null;

        var overlay = document.createElement('div');
        overlay.className = 'open-switcher-dialog';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'open-switcher-dialog-title');
        overlay.setAttribute('aria-describedby', 'open-switcher-dialog-message');

        var card = document.createElement('div');
        card.className = 'open-switcher-dialog__card open-switcher-dialog__card--' + (dialogData.variant || 'neutral');

        var badge = document.createElement('div');
        badge.className = 'open-switcher-dialog__badge';
        badge.textContent = dialogData.variant === 'success'
          ? 'Impersonation updated'
          : 'Account restored';

        var title = document.createElement('h2');
        title.className = 'open-switcher-dialog__title';
        title.id = 'open-switcher-dialog-title';
        title.textContent = dialogData.title;

        var message = document.createElement('p');
        message.className = 'open-switcher-dialog__message';
        message.id = 'open-switcher-dialog-message';
        message.textContent = dialogData.message;

        var details = document.createElement('p');
        details.className = 'open-switcher-dialog__details';
        details.textContent = dialogData.details || '';
        if (!dialogData.details) {
          details.hidden = true;
        }

        var actions = document.createElement('div');
        actions.className = 'open-switcher-dialog__actions';

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'open-switcher-dialog__button';
        button.textContent = dialogData.confirmText || 'Continue';

        var releaseFocus = function () {
          document.removeEventListener('keydown', handleEscape);
        };

        var handleEscape = function (event) {
          if (event.key === 'Escape') {
            closeDialog(overlay, releaseFocus);
          }
        };

        overlay.addEventListener('click', function (event) {
          if (event.target === overlay) {
            closeDialog(overlay, releaseFocus);
          }
        });

        button.addEventListener('click', function () {
          closeDialog(overlay, releaseFocus);
        });

        actions.appendChild(button);
        card.appendChild(badge);
        card.appendChild(title);
        card.appendChild(message);
        card.appendChild(details);
        card.appendChild(actions);
        overlay.appendChild(card);
        document.body.appendChild(overlay);

        document.addEventListener('keydown', handleEscape);
        window.requestAnimationFrame(function () {
          overlay.classList.add('is-visible');
          button.focus();
        });
      });
    }
  };

})(Drupal, once, drupalSettings);
