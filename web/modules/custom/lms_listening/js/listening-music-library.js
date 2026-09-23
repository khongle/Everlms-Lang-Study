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

  function formatTime(seconds) {
    if (!isFinite(seconds) || seconds < 0) {
      seconds = 0;
    }
    var minutes = Math.floor(seconds / 60);
    var remaining = Math.floor(seconds % 60);
    return minutes + ':' + ('0' + remaining).slice(-2);
  }

  function setStatus(el, message, isError) {
    if (!el) {
      return;
    }
    el.textContent = message;
    el.hidden = !message;
    el.classList.toggle('is-error', !!isError);
  }

  function buildPlayer(track) {
    var player = document.createElement('div');
    player.className = 'lms-music-player';
    player.setAttribute('data-music-player', '');

    var audioEl = document.createElement('audio');
    audioEl.preload = 'none';
    audioEl.src = track.url;
    audioEl.setAttribute('data-music-audio', '');
    player.appendChild(audioEl);

    var playBtn = document.createElement('button');
    playBtn.type = 'button';
    playBtn.className = 'lms-music-player__play';
    playBtn.setAttribute('data-music-play', '');
    playBtn.title = Drupal.t('Play');
    playBtn.setAttribute('aria-label', Drupal.t('Play'));
    playBtn.innerHTML = '<i class="fas fa-play" aria-hidden="true"></i>';
    player.appendChild(playBtn);

    var timeEl = document.createElement('span');
    timeEl.className = 'lms-music-player__time';
    timeEl.setAttribute('data-music-time', '');
    timeEl.textContent = '0:00 / 0:00';
    player.appendChild(timeEl);

    var seekEl = document.createElement('input');
    seekEl.type = 'range';
    seekEl.className = 'lms-music-player__seek';
    seekEl.setAttribute('data-music-seek', '');
    seekEl.min = '0';
    seekEl.max = '1000';
    seekEl.value = '0';
    seekEl.step = '1';
    seekEl.setAttribute('aria-label', Drupal.t('Seek'));
    player.appendChild(seekEl);

    var volumeLabel = document.createElement('label');
    volumeLabel.className = 'lms-music-player__volume';
    volumeLabel.innerHTML = '<i class="fas fa-volume-up" aria-hidden="true"></i><span class="visually-hidden">' + Drupal.t('Volume') + '</span>';

    var volumeEl = document.createElement('input');
    volumeEl.type = 'range';
    volumeEl.setAttribute('data-music-volume', '');
    volumeEl.min = '0';
    volumeEl.max = '100';
    volumeEl.value = '100';
    volumeEl.step = '1';
    volumeEl.setAttribute('aria-label', Drupal.t('Volume'));
    volumeLabel.appendChild(volumeEl);
    player.appendChild(volumeLabel);

    return player;
  }

  function initPlayer(player) {
    var audioEl = player.querySelector('[data-music-audio]');
    var playBtn = player.querySelector('[data-music-play]');
    var playIcon = playBtn ? playBtn.querySelector('i') : null;
    var timeEl = player.querySelector('[data-music-time]');
    var seekEl = player.querySelector('[data-music-seek]');
    var volumeEl = player.querySelector('[data-music-volume]');

    if (!audioEl || !playBtn || !seekEl || !volumeEl) {
      return;
    }

    function updateTime() {
      var duration = audioEl.duration || 0;
      var currentTime = audioEl.currentTime || 0;
      if (timeEl) {
        timeEl.textContent = formatTime(currentTime) + ' / ' + formatTime(duration);
      }
      if (duration && !seekEl.matches(':active')) {
        seekEl.value = String(Math.round((currentTime / duration) * 1000));
      }
    }

    function updatePlayState() {
      var isPlaying = !audioEl.paused && !audioEl.ended;
      playBtn.title = isPlaying ? Drupal.t('Pause') : Drupal.t('Play');
      playBtn.setAttribute('aria-label', playBtn.title);
      if (playIcon) {
        playIcon.className = isPlaying ? 'fas fa-pause' : 'fas fa-play';
      }
    }

    function isAtEnd() {
      return !!(audioEl.duration && audioEl.currentTime >= audioEl.duration - 0.2);
    }

    function resetToStart() {
      audioEl.currentTime = 0;
      seekEl.value = '0';
      updateTime();
    }

    function applyVolume() {
      audioEl.volume = (parseInt(volumeEl.value, 10) || 0) / 100;
      audioEl.muted = audioEl.volume === 0;
    }

    playBtn.addEventListener('click', function () {
      if (audioEl.paused || audioEl.ended) {
        if (audioEl.ended || isAtEnd()) {
          resetToStart();
        }
        applyVolume();
        audioEl.play().catch(function () {});
        return;
      }
      audioEl.pause();
    });

    seekEl.addEventListener('input', function () {
      if (!audioEl.duration) {
        return;
      }
      audioEl.currentTime = (parseInt(seekEl.value, 10) / 1000) * audioEl.duration;
      updateTime();
    });

    volumeEl.addEventListener('input', function () {
      applyVolume();
    });

    applyVolume();
    audioEl.addEventListener('loadedmetadata', updateTime);
    audioEl.addEventListener('timeupdate', updateTime);
    audioEl.addEventListener('play', updatePlayState);
    audioEl.addEventListener('pause', updatePlayState);
    audioEl.addEventListener('ended', function () {
      resetToStart();
      applyVolume();
      audioEl.play().catch(function () {});
      updatePlayState();
    });
    updateTime();
    updatePlayState();
  }

  Drupal.behaviors.lmsListeningMusicLibrary = {
    attach: function (context) {
      once('lms-listening-music-player', '[data-music-player]', context).forEach(initPlayer);

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

          var player = buildPlayer(track);
          row.appendChild(player);
          initPlayer(player);

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
