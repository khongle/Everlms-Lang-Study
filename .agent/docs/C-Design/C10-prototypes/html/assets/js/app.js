/* EverLMS prototype interactions — progressive enhancement, no dependencies. */
(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ---- mobile nav ---- */
  $$('[data-nav-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var links = $('#nav-links') || $('.nav__links');
      if (links) links.classList.toggle('is-open');
    });
  });

  /* ---- curriculum accordion ---- */
  $$('.curri__head').forEach(function (h) {
    h.addEventListener('click', function () {
      var sec = h.closest('.curri__sec');
      if (sec) sec.classList.toggle('is-open');
    });
  });

  /* ---- tabs ---- */
  $$('[data-tabs]').forEach(function (group) {
    var btns = $$('[data-tab]', group);
    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var name = btn.getAttribute('data-tab');
        btns.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
        $$('[data-pane]', group).forEach(function (p) {
          p.classList.toggle('is-active', p.getAttribute('data-pane') === name);
        });
      });
    });
  });

  /* ---- tab cross-links (data-switch-tab) ---- */
  $$('[data-switch-tab]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      var name = el.getAttribute('data-switch-tab');
      var group = el.closest('[data-tabs]');
      if (!group) return;
      var target = $('[data-tab="' + name + '"]', group);
      if (target) target.click();
    });
  });

  /* ---- quiz option select ---- */
  $$('[data-quiz]').forEach(function (q) {
    $$('.opt', q).forEach(function (opt) {
      opt.addEventListener('click', function () {
        $$('.opt', q).forEach(function (o) { o.classList.remove('is-selected'); });
        opt.classList.add('is-selected');
      });
    });
  });

  /* ---- skin switcher ---- */
  var KEY = 'everlms-skin';
  function applySkin(skin) {
    document.documentElement.setAttribute('data-skin', skin);
    $$('[data-skin-set]').forEach(function (b) {
      b.classList.toggle('is-active', b.getAttribute('data-skin-set') === skin);
    });
    try { localStorage.setItem(KEY, skin); } catch (e) {}
  }
  $$('[data-skin-set]').forEach(function (b) {
    b.addEventListener('click', function () { applySkin(b.getAttribute('data-skin-set')); });
  });
  // restore saved skin only if a switcher is present and page didn't hardcode one
  if ($('[data-skin-set]')) {
    var saved;
    try { saved = localStorage.getItem(KEY); } catch (e) {}
    if (saved) applySkin(saved);
  }

  /* ---- play buttons (prototype: no real video) ---- */
  $$('.videobox__play, .player__playbtn, .enroll__play, .player__stage img').forEach(function (el) {
    el.addEventListener('click', function (e) { e.preventDefault(); });
  });

  /* ---- bookmark toggle ---- */
  $$('.ccard__bookmark').forEach(function (b) {
    b.addEventListener('click', function (e) {
      e.preventDefault();
      b.classList.toggle('is-on');
      b.style.color = b.classList.contains('is-on') ? 'var(--brand)' : '';
    });
  });
})();
