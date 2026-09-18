/**
 * @file
 * /manage/listening-music — upload a background music track (AJAX,
 * multipart/form-data to lms_listening.api_music_upload) and delete one of
 * your own (lms_listening.api_music_delete). See
 * BackgroundMusicController's docblock for what this page does and why it
 * has no Drupal entity-form CRUD UI behind it.
 */

(function (Drupal, once) {
  'use strict';

  function setStatus(el, message, isError) {
    if (!el) {
      return;
    }
    el.textContent = message;
    el.hidden = !message;
    el.classList.toggle('is-error', !!isError);
  }

  Drupal.behaviors.lmsListeningMusicLibrary = {
    attach: function (context) {
      once('lms-listening-music-library', '[data-music-upload-form]', context).forEach(function (form) {
        var settings = (drupalSettings.lmsListeningMusicLibrary || {});
        var nameInput = form.querySelector('[data-music-name-input]');
        var fileInput = form.querySelector('[data-music-file-input]');
        var uploadBtn = form.querySelector('[data-music-upload-btn]');
        var statusEl = form.querySelector('[data-music-upload-status]');
        var listEl = document.querySelector('[data-music-track-list]');
        var emptyEl = listEl ? listEl.querySelector('[data-music-empty]') : null;

        function addTrackRow(track) {
          if (emptyEl) {
            emptyEl.hidden = true;
          }
          var row = document.createElement('div');
          row.className = 'lms-music-library__track';
          row.setAttribute('data-music-track-row', '');
          row.setAttribute('data-track-id', track.id);

          var nameEl = document.createElement('span');
          nameEl.className = 'lms-music-library__track-name';
          nameEl.textContent = track.name;
          row.appendChild(nameEl);

          var audioEl = document.createElement('audio');
          audioEl.controls = true;
          audioEl.preload = 'none';
          audioEl.src = track.url;
          row.appendChild(audioEl);

          var deleteBtn = document.createElement('button');
          deleteBtn.type = 'button';
          deleteBtn.className = 'lms-music-library__delete-btn';
          deleteBtn.setAttribute('data-music-delete-btn', '');
          deleteBtn.setAttribute('data-track-id', track.id);
          deleteBtn.title = Drupal.t('Delete');
          deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
          row.appendChild(deleteBtn);

          listEl.insertBefore(row, listEl.firstChild);
        }

        function doUpload() {
          if (!fileInput || !fileInput.files || !fileInput.files.length) {
            setStatus(statusEl, Drupal.t('Choose an audio file first.'), true);
            return;
          }
          if (!settings.uploadUrl) {
            return;
          }

          var payload = new FormData();
          payload.append('file', fileInput.files[0]);
          if (nameInput && nameInput.value.trim()) {
            payload.append('name', nameInput.value.trim());
          }

          uploadBtn.disabled = true;
          setStatus(statusEl, Drupal.t('Uploading…'), false);

          fetch(settings.uploadUrl, { method: 'POST', body: payload, credentials: 'same-origin' })
            .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
            .then(function (result) {
              uploadBtn.disabled = false;
              if (!result.ok) {
                setStatus(statusEl, result.data.error || Drupal.t('Upload failed.'), true);
                return;
              }
              addTrackRow(result.data);
              setStatus(statusEl, Drupal.t('Uploaded!'), false);
              window.setTimeout(function () { setStatus(statusEl, '', false); }, 1800);
              if (nameInput) {
                nameInput.value = '';
              }
              fileInput.value = '';
            })
            .catch(function () {
              uploadBtn.disabled = false;
              setStatus(statusEl, Drupal.t('Upload failed, please try again.'), true);
            });
        }

        if (uploadBtn) {
          uploadBtn.addEventListener('click', doUpload);
        }

        if (listEl) {
          listEl.addEventListener('click', function (event) {
            var deleteBtn = event.target.closest('[data-music-delete-btn]');
            if (!deleteBtn || !settings.deleteUrlTemplate) {
              return;
            }
            if (!window.confirm(Drupal.t('Delete this background music track? This cannot be undone.'))) {
              return;
            }
            var trackId = deleteBtn.getAttribute('data-track-id');
            var url = settings.deleteUrlTemplate.replace('__ID__', trackId);
            deleteBtn.disabled = true;

            fetch(url, { method: 'POST', credentials: 'same-origin' })
              .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
              .then(function (result) {
                if (!result.ok) {
                  deleteBtn.disabled = false;
                  window.alert(result.data.error || Drupal.t('Delete failed.'));
                  return;
                }
                var row = deleteBtn.closest('[data-music-track-row]');
                if (row) {
                  row.remove();
                }
                if (emptyEl && !listEl.querySelector('[data-music-track-row]')) {
                  emptyEl.hidden = false;
                }
              })
              .catch(function () {
                deleteBtn.disabled = false;
                window.alert(Drupal.t('Delete failed, please try again.'));
              });
          });
        }
      });
    },
  };
}(Drupal, once));
