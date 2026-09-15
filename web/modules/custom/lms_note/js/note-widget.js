/**
 * @file
 * Shared "My Note" widget — a small rich-text editor (Bold/Italic/
 * Underline/bullet+numbered lists) + Save/Delete against lms_note's
 * generic context_type/context_id API (see NOTE_EXPANSION_PLAN.md and
 * NoteController::resolveContext()). Ported from the course-lesson note
 * widget in lms_theme/js/main.js (Drupal.behaviors.lmsLessonView), which
 * is left completely untouched — that one still talks to the same API
 * via its own lesson_id/course_id shape, as a plain <textarea> (no
 * formatting). This copy is scoped to its own container (data-*
 * attributes, querySelector on the root) instead of page-wide #ids, so
 * more than one page type can use it, and so a page can host it more
 * than once if it ever needs to.
 *
 * Any module can drop in this markup (one root element, no id required):
 *   <div data-lms-note-widget
 *        data-note-context-type="listening_passage"
 *        data-note-context-id="123">
 *     <span data-note-status></span>
 *     <div data-note-toolbar>
 *       <button type="button" data-note-cmd="bold">B</button>
 *       ... (see templates already using this for the full set)
 *     </div>
 *     <div data-note-textarea contenteditable="true"></div>
 *     <button type="button" data-note-save>Save</button>
 *     <button type="button" data-note-delete hidden>Delete</button>
 *   </div>
 * plus `{ attach: ['lms_note/note_widget'] }` on its render array/library.
 *
 * The editor is a contenteditable div, not a real form field — its
 * content is read as HTML (innerHTML), not plain text, and POSTed as
 * note_text. NoteController::save() re-sanitizes that HTML against
 * ALLOWED_BODY_TAGS server-side (Xss::filter()) regardless of what this
 * toolbar itself would ever produce, since this is a JSON endpoint
 * anyone could POST to directly, not something only this widget can
 * reach — never trust the client here. No attachments (see
 * NOTE_EXPANSION_PLAN.md §2/§10); those stay a course-lesson-only
 * capability.
 */

(function (Drupal, once) {
  'use strict';

  var PLACEHOLDER_LOADING = 'Loading…';
  var PLACEHOLDER_READY = 'Type your notes here…';

  /**
   * Wires up one widget root. Stores a small `{ setContext }` API on the
   * element itself (`root.lmsNoteWidget`) so a page whose content swaps in
   * place — Passive Listening's continuous loop, see
   * js/listening-passive.js — can point this same widget at the next
   * passage instead of needing a fresh widget/instance per passage.
   */
  function attachWidget(root) {
    var textarea = root.querySelector('[data-note-textarea]');
    var toolbar = root.querySelector('[data-note-toolbar]');
    var saveBtn = root.querySelector('[data-note-save]');
    var deleteBtn = root.querySelector('[data-note-delete]');
    var statusEl = root.querySelector('[data-note-status]');
    if (!textarea || !saveBtn || !deleteBtn || !statusEl) {
      return;
    }

    var contextType = root.dataset.noteContextType;
    var contextId = parseInt(root.dataset.noteContextId, 10);
    var originalHtml = '';
    var hasUnsavedChanges = false;
    // Bumped on every load(); a fetch whose token no longer matches when
    // it resolves belongs to a context setContext() has already moved
    // past (e.g. Passive Listening advancing to the next passage while a
    // slow request for the previous one is still in flight) and is
    // discarded instead of overwriting what the student is now looking
    // at.
    var requestToken = 0;

    function setDeleteVisible(visible) {
      deleteBtn.hidden = !visible;
    }

    function setSaveEnabled(enabled) {
      saveBtn.disabled = !enabled;
    }

    function setEditable(enabled) {
      textarea.contentEditable = enabled ? 'true' : 'false';
      textarea.classList.toggle('is-disabled', !enabled);
      if (toolbar) {
        toolbar.classList.toggle('is-disabled', !enabled);
      }
    }

    function setPlaceholder(text) {
      // No native placeholder for contenteditable — CSS reads this via
      // :empty:before { content: attr(data-empty-text) }, see
      // css/note-widget.css.
      textarea.setAttribute('data-empty-text', Drupal.t(text));
    }

    function showStatus(html, tone) {
      statusEl.innerHTML = html;
      statusEl.classList.remove('is-muted', 'is-success', 'is-error');
      statusEl.classList.add('is-visible');
      if (tone) {
        statusEl.classList.add('is-' + tone);
      }
    }

    function hideStatus() {
      statusEl.classList.remove('is-visible', 'is-muted', 'is-success', 'is-error');
    }

    function contextQuery() {
      return 'context_type=' + encodeURIComponent(contextType) + '&context_id=' + encodeURIComponent(contextId);
    }

    /**
     * A browser can leave a lone <br> (or an empty <div><br></div>) behind
     * in a contenteditable after the student types then deletes
     * everything — normalized to '' here so both the :empty:before
     * placeholder CSS and the hasUnsavedChanges comparison treat that the
     * same as a genuinely empty note.
     */
    function normalizeIfEmpty() {
      var html = textarea.innerHTML;
      if (html === '<br>' || html === '<div><br></div>' || html === '<p><br></p>') {
        textarea.innerHTML = '';
      }
    }

    function updateUnsavedState() {
      normalizeIfEmpty();
      hasUnsavedChanges = textarea.innerHTML !== originalHtml;
      setSaveEnabled(hasUnsavedChanges);
    }

    function load() {
      var token = ++requestToken;
      setEditable(false);
      textarea.innerHTML = '';
      setPlaceholder(PLACEHOLDER_LOADING);
      setSaveEnabled(false);
      setDeleteVisible(false);
      hideStatus();

      fetch('/api/lms/note/get?' + contextQuery())
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (token !== requestToken) {
            return;
          }
          setEditable(true);
          setPlaceholder(PLACEHOLDER_READY);
          if (data && data.status === 'found') {
            textarea.innerHTML = data.note_text || '';
            originalHtml = textarea.innerHTML;
            setDeleteVisible(true);
          }
          else {
            originalHtml = '';
          }
          hasUnsavedChanges = false;
          setSaveEnabled(false);
        })
        .catch(function () {
          if (token !== requestToken) {
            return;
          }
          setEditable(true);
          setPlaceholder(PLACEHOLDER_READY);
        });
    }

    textarea.addEventListener('input', updateUnsavedState);

    if (toolbar) {
      toolbar.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-note-cmd]');
        if (!btn || textarea.contentEditable !== 'true') {
          return;
        }
        event.preventDefault();
        textarea.focus();
        // execCommand is long-deprecated but still the only
        // zero-dependency way to do basic rich-text formatting without
        // pulling in a third-party editor library — matches this
        // codebase's existing "plain vanilla JS, no external JS
        // dependencies" convention throughout lms_listening/lms_flashcard.
        document.execCommand(btn.dataset.noteCmd, false, null);
        updateUnsavedState();
      });
    }

    // Same unsaved-changes guard as the course-lesson widget — a global
    // listener is fine even with more than one widget on a page (none
    // currently has more than one), since only whichever one has
    // unsaved changes ever sets its own flag.
    window.addEventListener('beforeunload', function (event) {
      if (hasUnsavedChanges) {
        event.preventDefault();
        event.returnValue = '';
      }
    });

    saveBtn.addEventListener('click', function () {
      showStatus('<i class="fas fa-spinner fa-spin"></i> ' + Drupal.t('Saving…'), 'muted');
      setSaveEnabled(false);

      fetch('/api/lms/note/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          context_type: contextType,
          context_id: contextId,
          note_text: textarea.innerHTML,
        }),
      })
        .then(function (response) {
          if (response.ok) {
            showStatus('<i class="fas fa-check-circle"></i> ' + Drupal.t('Saved!'), 'success');
            setDeleteVisible(true);
            originalHtml = textarea.innerHTML;
            hasUnsavedChanges = false;
            setSaveEnabled(false);
            window.setTimeout(hideStatus, 2000);
          }
          else {
            showStatus('<i class="fas fa-exclamation-triangle"></i> ' + Drupal.t('Error saving'), 'error');
            setSaveEnabled(true);
          }
        })
        .catch(function () {
          showStatus('<i class="fas fa-exclamation-triangle"></i> ' + Drupal.t('Network error'), 'error');
          setSaveEnabled(true);
        });
    });

    deleteBtn.addEventListener('click', function () {
      if (!window.confirm(Drupal.t('Delete this note?'))) {
        return;
      }
      deleteBtn.disabled = true;
      showStatus('<i class="fas fa-spinner fa-spin"></i> ' + Drupal.t('Deleting…'), 'muted');

      fetch('/api/lms/note/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ context_type: contextType, context_id: contextId }),
      })
        .then(function (response) {
          deleteBtn.disabled = false;
          if (response.ok) {
            textarea.innerHTML = '';
            originalHtml = '';
            hasUnsavedChanges = false;
            setDeleteVisible(false);
            setSaveEnabled(false);
            showStatus('<i class="fas fa-trash-alt"></i> ' + Drupal.t('Deleted'), 'success');
            window.setTimeout(hideStatus, 2000);
          }
          else {
            showStatus('<i class="fas fa-exclamation-triangle"></i> ' + Drupal.t('Error deleting'), 'error');
          }
        })
        .catch(function () {
          deleteBtn.disabled = false;
          showStatus('<i class="fas fa-exclamation-triangle"></i> ' + Drupal.t('Network error'), 'error');
        });
    });

    load();

    root.lmsNoteWidget = {
      setContext: function (newContextType, newContextId) {
        contextType = newContextType;
        contextId = newContextId;
        root.dataset.noteContextType = newContextType;
        root.dataset.noteContextId = String(newContextId);
        load();
      },
    };
  }

  Drupal.behaviors.lmsNoteWidget = {
    attach: function (context) {
      once('lms-note-widget', '[data-lms-note-widget]', context).forEach(attachWidget);
    },
  };
})(Drupal, once);
