/* ============================================================================
   shell.js — EverLMS prototype chrome (header + My Account mega menu + footer)
   ----------------------------------------------------------------------------
   ONE source of truth for the whole navigation system so every one of the 51
   pages renders an identical, correct menu. No build step, no fetch — the markup
   is authored as template strings so the prototype works offline over file://.

   A page declares its chrome with data-attributes on placeholder elements:
     <div id="site-header" data-shell="front" data-active="P002"></div>
     <div id="site-header" data-shell="app" data-role="student" data-active="P017"></div>
     <div id="site-footer"></div>

   data-shell : "front" (public marketing/catalog) | "app" (everything behind login)
   data-role  : app shell only — student|instructor|content-editor|manager|administrator
   data-active: current page id (P###) — sets aria-current on the matching link
   data-auth  : front shell only — "guest" (default) or a role key to show the
                logged-in My Account state on a public page.

   MENU MODEL (per the brief):
     front  = main menu + (guest) Log in / Sign up   |  (authed) bell + My Account
     app    = backend main menu + [Home -> front] + bell + My Account mega menu
     My Account mega menu = role-filtered launcher for App / Manage / Admin / Account.
     No sidebar anywhere (course/lesson list rails are page-level business UI).
   ============================================================================ */
(function () {
  'use strict';

  /* ---- page registry: id -> { f:file, r:owner role folder, t:title } ------ */
  var P = {
    P001: { f: 'P001-homepage.html',            r: 'anonymous',      t: 'Home' },
    P002: { f: 'P002-course-catalog.html',       r: 'anonymous',      t: 'Courses' },
    P003: { f: 'P003-course-detail.html',        r: 'anonymous',      t: 'Course detail' },
    P004: { f: 'P004-categories.html',           r: 'anonymous',      t: 'Categories' },
    P005: { f: 'P005-category-detail.html',      r: 'anonymous',      t: 'Category' },
    P006: { f: 'P006-pricing.html',              r: 'anonymous',      t: 'Pricing' },
    P007: { f: 'P007-instructor-profile.html',   r: 'anonymous',      t: 'Instructor' },
    P008: { f: 'P008-blog.html',                 r: 'anonymous',      t: 'Blog' },
    P009: { f: 'P009-article-detail.html',       r: 'anonymous',      t: 'Article' },
    P010: { f: 'P010-about.html',                r: 'anonymous',      t: 'About' },
    P011: { f: 'P011-contact.html',              r: 'anonymous',      t: 'Contact' },
    P012: { f: 'P012-glossary.html',             r: 'anonymous',      t: 'Glossary' },
    P013: { f: 'P013-forum-index.html',          r: 'anonymous',      t: 'Forum' },
    P014: { f: 'P014-forum-topic.html',          r: 'anonymous',      t: 'Forum topic' },
    P015: { f: 'P015-certificate-verify.html',   r: 'anonymous',      t: 'Verify certificate' },
    P016: { f: 'P016-login-register.html',       r: 'anonymous',      t: 'Log in / Register' },
    P017: { f: 'P017-dashboard.html',            r: 'student',        t: 'Dashboard',            i: 'grid' },
    P018: { f: 'P018-my-courses.html',           r: 'student',        t: 'My Courses',           i: 'book' },
    P019: { f: 'P019-lesson-player.html',        r: 'student',        t: 'Lesson player',        i: 'play' },
    P020: { f: 'P020-quiz-take.html',            r: 'student',        t: 'Quiz',                 i: 'quiz' },
    P021: { f: 'P021-quiz-result.html',          r: 'student',        t: 'Quiz result',          i: 'chart' },
    P022: { f: 'P022-my-quiz-results.html',      r: 'student',        t: 'My Quiz Results',      i: 'chart' },
    P023: { f: 'P023-my-certificates.html',      r: 'student',        t: 'My Certificates',      i: 'award' },
    P024: { f: 'P024-my-notes.html',             r: 'student',        t: 'My Notes',             i: 'note' },
    P025: { f: 'P025-my-wishlist.html',          r: 'student',        t: 'Wishlist',             i: 'heart' },
    P026: { f: 'P026-my-membership.html',        r: 'student',        t: 'Membership',           i: 'star' },
    P027: { f: 'P027-notifications.html',        r: 'student',        t: 'Notifications',        i: 'bell' },
    P028: { f: 'P028-profile.html',              r: 'student',        t: 'Profile',              i: 'user' },
    P029: { f: 'P029-checkout-course.html',      r: 'student',        t: 'Checkout',             i: 'card' },
    P030: { f: 'P030-checkout-membership.html',  r: 'student',        t: 'Checkout membership',  i: 'card' },
    P031: { f: 'P031-workspace-home.html',       r: 'instructor',     t: 'Workspace',            i: 'grid' },
    P032: { f: 'P032-manage-courses.html',       r: 'instructor',     t: 'Courses',              i: 'book' },
    P033: { f: 'P033-course-editor.html',        r: 'instructor',     t: 'Course editor',        i: 'pencil' },
    P034: { f: 'P034-manage-quizzes.html',       r: 'instructor',     t: 'Quizzes',              i: 'quiz' },
    P035: { f: 'P035-manage-users.html',         r: 'manager',        t: 'Users',                i: 'users' },
    P036: { f: 'P036-manage-orders.html',        r: 'manager',        t: 'Orders',               i: 'cart' },
    P037: { f: 'P037-manage-content.html',       r: 'content-editor', t: 'Content',              i: 'layers' },
    P038: { f: 'P038-manage-pricing.html',       r: 'manager',        t: 'Pricing / Plans',      i: 'tag' },
    P039: { f: 'P039-manage-ratings.html',       r: 'content-editor', t: 'Ratings',              i: 'star' },
    P040: { f: 'P040-certificate-templates.html',r: 'content-editor', t: 'Certificate templates',i: 'award' },
    P041: { f: 'P041-issued-certificates.html',  r: 'content-editor', t: 'Issued certificates',  i: 'award' },
    P042: { f: 'P042-content-tools.html',         r: 'content-editor', t: 'Content tools',        i: 'tool' },
    P043: { f: 'P043-manage-blog.html',          r: 'content-editor', t: 'Blog',                 i: 'edit' },
    P044: { f: 'P044-ai-course-builder.html',    r: 'instructor',     t: 'AI course builder',    i: 'bolt' },
    P045: { f: 'P045-workspace-reports.html',    r: 'instructor',     t: 'Reports',              i: 'chart' },
    P046: { f: 'P046-ai-settings.html',          r: 'administrator',  t: 'AI settings',          i: 'bolt' },
    P047: { f: 'P047-payment-settings.html',     r: 'administrator',  t: 'Payment settings',     i: 'card' },
    P048: { f: 'P048-zoom-settings.html',        r: 'administrator',  t: 'Zoom settings',        i: 'video' },
    P049: { f: 'P049-analytics-reports.html',    r: 'administrator',  t: 'Analytics',            i: 'chart' },
    P050: { f: 'P050-notification-broadcast.html',r:'administrator',  t: 'Broadcast',            i: 'bell' },
    P051: { f: 'P051-switch-user.html',          r: 'administrator',  t: 'Switch user',          i: 'switch' }
  };

  /* ---- icon library (24x24 stroke=currentColor) --------------------------- */
  var PATHS = {
    home:   '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10"/>',
    grid:   '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    book:   '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z"/>',
    play:   '<circle cx="12" cy="12" r="9"/><path d="M10 8.5v7l6-3.5z"/>',
    quiz:   '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.6 2.2c-.8.4-1.1 1-1.1 1.8v.4"/><path d="M12 17h.01"/>',
    chart:  '<path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/>',
    award:  '<circle cx="12" cy="8" r="6"/><path d="M8.2 13 7 22l5-3 5 3-1.2-9"/>',
    note:   '<path d="M4 4h16v12l-4 4H4z"/><path d="M14 20v-4h4"/><path d="M8 9h8M8 13h5"/>',
    heart:  '<path d="M12 20s-7-4.6-9.2-9A4.6 4.6 0 0 1 12 6a4.6 4.6 0 0 1 9.2 5c-2.2 4.4-9.2 9-9.2 9z"/>',
    star:   '<path d="m12 3 2.6 5.4 5.9.8-4.3 4.1 1 5.9L12 16.9 6.8 19.3l1-5.9-4.3-4.1 5.9-.8z"/>',
    bell:   '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
    user:   '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    users:  '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/>',
    cart:   '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.4 12.3a1.5 1.5 0 0 0 1.5 1.2h8.4a1.5 1.5 0 0 0 1.5-1.2L22 7H6"/>',
    layers: '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
    tag:    '<path d="M3 12V4h8l10 10-8 8L3 12z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
    pencil: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
    edit:   '<path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
    tool:   '<path d="M14.7 6.3a4 4 0 0 0-5.3 5.3l-6 6L5 20l6-6a4 4 0 0 0 5.3-5.3l-2.3 2.3-2-2z"/>',
    bolt:   '<path d="M13 2 4 14h6l-1 8 9-12h-6z"/>',
    card:   '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
    video:  '<rect x="2" y="6" width="14" height="12" rx="2"/><path d="m22 8-6 4 6 4z"/>',
    switch: '<path d="M4 8h13l-3-3M20 16H7l3 3"/>',
    logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/>',
    chevron:'<path d="m6 9 6 6 6-6"/>',
    menu:   '<path d="M3 6h18M3 12h18M3 18h18"/>',
    globe:  '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18"/>',
    close:  '<path d="M6 6l12 12M18 6 6 18"/>'
  };
  function svg(name, cls) {
    return '<svg class="' + (cls || '') + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
      'stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + (PATHS[name] || PATHS.grid) + '</svg>';
  }

  /* ---- helpers ------------------------------------------------------------ */
  function href(id) { var p = P[id]; return p ? '../' + p.r + '/' + p.f : '#'; }
  function asset(rel) { return '../_shared/' + rel; }
  function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;'); }

  var ROLE_LABEL = {
    student: 'Student', instructor: 'Instructor', 'content-editor': 'Content Editor',
    manager: 'Manager', administrator: 'Administrator'
  };
  var ROLE_USER = {
    student:          { name: 'Alex Morgan',   sub: 'Student',         img: 'avatar-03.jpg' },
    instructor:       { name: 'Sarah Chen',    sub: 'Instructor',      img: 'instructor-01.jpg' },
    'content-editor': { name: 'Priya Sharma',  sub: 'Content Editor',  img: 'instructor-02.jpg' },
    manager:          { name: 'James Carter',  sub: 'Manager',         img: 'instructor-m-02.jpg' },
    administrator:    { name: 'Dana Whitfield', sub: 'Administrator',  img: 'avatar-06.jpg' }
  };

  /* ---- front (public) main menu ------------------------------------------- */
  var CATS = [
    ['P005', 'Web Development', 'HTML, JS, React, full-stack', 'layers'],
    ['P005', 'AI &amp; Machine Learning', 'LLMs, deep learning, MLOps', 'bolt'],
    ['P005', 'Data &amp; Analytics', 'SQL, Python, visualization', 'chart'],
    ['P005', 'Design &amp; UX', 'Figma, product, branding', 'pencil'],
    ['P005', 'Business', 'Marketing, finance, leadership', 'tag'],
    ['P004', 'Browse all categories', '40+ topics, 1,200+ courses', 'grid']
  ];
  function frontMenu(active) {
    var cur = function (id) { return active === id ? ' aria-current="page"' : ''; };
    var mega = '<div class="mega">' + CATS.map(function (c) {
      return '<a href="' + href(c[0]) + '"><span class="mega__ic">' + svg(c[3]) + '</span>' +
        '<span><b>' + c[1] + '</b><small>' + c[2] + '</small></span></a>';
    }).join('') + '</div>';
    return '<ul class="nav__links" id="nav-links">' +
      '<li class="has-mega"><a href="' + href('P002') + '"' + cur('P002') + '>Courses ' + svg('chevron', 'chev') + '</a>' + mega + '</li>' +
      '<li><a href="' + href('P004') + '"' + cur('P004') + '>Categories</a></li>' +
      '<li><a href="' + href('P006') + '"' + cur('P006') + '>Pricing</a></li>' +
      '<li><a href="' + href('P008') + '"' + cur('P008') + '>Blog</a></li>' +
      '<li><a href="' + href('P013') + '"' + cur('P013') + '>Forum</a></li>' +
      '<li><a href="' + href('P010') + '"' + cur('P010') + '>About</a></li>' +
      '<li><a href="' + href('P011') + '"' + cur('P011') + '>Contact</a></li>' +
      '</ul>';
  }

  /* ---- backend (app) main menu, per role ---------------------------------- */
  var BACKNAV = {
    student:          [['P017', 'Dashboard'], ['P018', 'My Courses'], ['P023', 'Certificates'], ['P024', 'Notes'], ['P025', 'Wishlist'], ['P002', 'Browse']],
    instructor:       [['P031', 'Workspace'], ['P032', 'Courses'], ['P034', 'Quizzes'], ['P044', 'AI Builder'], ['P045', 'Reports'], ['P002', 'Browse']],
    'content-editor': [['P031', 'Workspace'], ['P037', 'Content'], ['P043', 'Blog'], ['P041', 'Certificates'], ['P039', 'Ratings'], ['P042', 'Tools']],
    manager:          [['P031', 'Workspace'], ['P032', 'Courses'], ['P035', 'Users'], ['P036', 'Orders'], ['P038', 'Pricing'], ['P045', 'Reports']],
    administrator:    [['P031', 'Workspace'], ['P035', 'Users'], ['P036', 'Orders'], ['P049', 'Analytics'], ['P046', 'Settings'], ['P051', 'Switch user']]
  };
  function backMenu(role, active) {
    var cur = function (id) { return active === id ? ' aria-current="page"' : ''; };
    return '<ul class="nav__links" id="nav-links">' + (BACKNAV[role] || BACKNAV.student).map(function (m) {
      return '<li><a href="' + href(m[0]) + '"' + cur(m[0]) + '>' + m[1] + '</a></li>';
    }).join('') + '</ul>';
  }

  /* ---- My Account mega menu columns, per role ----------------------------- */
  var LOGOUT = { label: 'Log out', href: href('P016'), i: 'logout' };
  var PUBLIC = { label: 'View public site', href: href('P001'), i: 'globe' };
  var MEGA = {
    student: [
      { h: 'Learning', items: ['P017', 'P018', 'P019', 'P022', 'P023', 'P024', 'P025'] },
      { h: 'Account', items: ['P026', 'P027', 'P028', PUBLIC, LOGOUT] }
    ],
    instructor: [
      { h: 'Teach', items: ['P031', 'P032', 'P033', 'P034'] },
      { h: 'Grow', items: ['P044', 'P045'] },
      { h: 'Account', items: ['P028', 'P027', PUBLIC, LOGOUT] }
    ],
    'content-editor': [
      { h: 'Content', items: ['P031', 'P032', 'P037', 'P043', 'P042'] },
      { h: 'Certificates & reviews', items: ['P040', 'P041', 'P039'] },
      { h: 'Account', items: ['P028', 'P027', PUBLIC, LOGOUT] }
    ],
    manager: [
      { h: 'Operations', items: ['P031', 'P035', 'P036', 'P038'] },
      { h: 'Catalog', items: ['P032', 'P034', 'P037', 'P039', 'P045'] },
      { h: 'Account', items: ['P028', 'P027', PUBLIC, LOGOUT] }
    ],
    administrator: [
      { h: 'Workspace', items: ['P031', 'P032', 'P035', 'P036', 'P037', 'P045'] },
      { h: 'Admin', items: ['P046', 'P047', 'P048', 'P049', 'P050', 'P051'] },
      { h: 'Account', items: ['P028', 'P027', PUBLIC, LOGOUT] }
    ]
  };
  function megaItem(it) {
    if (typeof it === 'string') { var p = P[it]; return '<a href="' + href(it) + '">' + svg(p.i || 'grid') + '<span>' + esc(p.t) + '</span></a>'; }
    return '<a href="' + (it.href || '#') + '">' + svg(it.i || 'grid') + '<span>' + esc(it.label) + '</span></a>';
  }
  function accountMega(role) {
    var u = ROLE_USER[role] || ROLE_USER.student;
    var cols = (MEGA[role] || MEGA.student).map(function (col) {
      return '<div class="acctmega__col"><h4>' + esc(col.h) + '</h4>' + col.items.map(megaItem).join('') + '</div>';
    }).join('');
    return '<div class="acct" data-dropdown>' +
      '<button class="acct__btn" data-dropdown-toggle aria-expanded="false" aria-haspopup="true">' +
        '<img class="nav__avatar" src="' + asset('img/' + u.img) + '" alt="">' +
        '<span class="acct__name">My Account</span>' + svg('chevron', 'chev') +
      '</button>' +
      '<div class="acctmega dropdown__menu">' +
        '<div class="acctmega__head"><img src="' + asset('img/' + u.img) + '" alt="">' +
          '<div><b>' + esc(u.name) + '</b><small>' + esc(u.sub) + '</small></div>' +
          '<a class="acctmega__home" href="' + href('P001') + '" title="Public home">' + svg('home') + '</a></div>' +
        '<div class="acctmega__cols">' + cols + '</div>' +
      '</div></div>';
  }

  /* ---- shared bits -------------------------------------------------------- */
  function brand(link) {
    return '<a class="brand" href="' + link + '" aria-label="EverLMS home">' +
      '<span class="brand__mark">E</span>' +
      '<span class="brand__name">Ever<span>LMS</span><small>Learn without limits</small></span></a>';
  }
  function toggle() {
    return '<button class="nav__toggle" data-nav-toggle aria-label="Menu" aria-expanded="false">' + svg('menu') + '</button>';
  }
  function search(ph) {
    return '<label class="nav__search">' + svg('search') +
      '<input type="search" placeholder="' + (ph || 'Search courses…') + '" aria-label="Search"></label>';
  }
  function bell() {
    return '<a class="icon-btn" href="' + href('P027') + '" aria-label="Notifications">' + svg('bell') + '<span class="icon-btn__dot"></span></a>';
  }
  function homeBtn() {
    return '<a class="icon-btn" href="' + href('P001') + '" aria-label="Public home" title="Public home">' + svg('home') + '</a>';
  }

  /* ---- header renderers --------------------------------------------------- */
  function frontHeader(active, auth) {
    var right = (auth && auth !== 'guest')
      ? '<div class="nav__actions">' + bell() + accountMega(auth) + '</div>'
      : '<div class="nav__actions">' +
          '<a class="btn btn--ghost btn--sm" href="' + href('P016') + '">Log in</a>' +
          '<a class="btn btn--primary btn--sm" href="' + href('P016') + '">Sign up</a></div>';
    return '<a class="skip" href="#main">Skip to content</a>' +
      '<div class="topbar"><div class="container">' +
        '<span class="topbar__promo"><b>New</b>&nbsp;— AI Course Builder is live. Create a course in minutes.</span>' +
        '<nav class="topbar__links" aria-label="Utility">' +
          '<a href="' + href('P007') + '">Become an instructor</a>' +
          '<a href="' + href('P006') + '">Pricing</a>' +
          '<a href="' + href('P015') + '">Verify certificate</a>' +
          '<a href="' + href('P011') + '">Help</a></nav>' +
      '</div></div>' +
      '<header class="site-header"><div class="container nav">' +
        brand(href('P001')) + toggle() + frontMenu(active) +
        '<div class="nav__spacer"></div>' + search() + right +
      '</div></header>';
  }
  function appHeader(role, active) {
    return '<a class="skip" href="#main">Skip to content</a>' +
      '<header class="site-header site-header--app"><div class="container nav">' +
        brand(href(role === 'student' ? 'P017' : 'P031')) + toggle() + backMenu(role, active) +
        '<div class="nav__spacer"></div>' + search() +
        '<div class="nav__actions">' + homeBtn() + bell() + accountMega(role) + '</div>' +
      '</div></header>';
  }

  /* ---- footer ------------------------------------------------------------- */
  function footer() {
    var col = function (h, links) {
      return '<div class="footer__col"><h4>' + h + '</h4><ul>' +
        links.map(function (l) { return '<li><a href="' + href(l[0]) + '">' + l[1] + '</a></li>'; }).join('') + '</ul></div>';
    };
    return '<footer class="site-footer"><div class="container">' +
      '<div class="footer__top">' +
        '<div class="footer__brand">' + brand(href('P001')) +
          '<p>Learn without limits. 1,200+ expert-led courses in tech, business, design and more — with certificates, mentoring and a mobile app.</p>' +
          '<div class="footer__social">' +
            '<a href="#" aria-label="Twitter">' + svg('globe') + '</a>' +
            '<a href="#" aria-label="LinkedIn">' + svg('users') + '</a>' +
            '<a href="#" aria-label="YouTube">' + svg('video') + '</a></div></div>' +
        col('Explore', [['P002', 'All courses'], ['P004', 'Categories'], ['P006', 'Pricing'], ['P008', 'Blog'], ['P013', 'Forum']]) +
        col('Company', [['P010', 'About'], ['P007', 'Instructors'], ['P011', 'Contact'], ['P012', 'Glossary'], ['P015', 'Verify certificate']]) +
        col('Account', [['P016', 'Log in'], ['P017', 'Dashboard'], ['P018', 'My learning'], ['P023', 'Certificates'], ['P026', 'Membership']]) +
        '<div class="footer__col footer__news"><h4>Newsletter</h4>' +
          '<p style="color:rgba(255,255,255,.62);font-size:.9rem">New courses and learning tips, monthly.</p>' +
          '<input type="email" placeholder="you@email.com" aria-label="Email">' +
          '<button class="btn btn--primary btn--block btn--sm">Subscribe</button></div>' +
      '</div>' +
      '<div class="footer__legal"><span>© 2026 EverLMS. All rights reserved.</span>' +
        '<nav aria-label="Legal"><a href="#">Privacy</a> &nbsp;·&nbsp; <a href="#">Terms</a> &nbsp;·&nbsp; <a href="#">Cookies</a></nav></div>' +
    '</div></footer>';
  }

  /* ---- behaviors: mobile toggle + account dropdown ------------------------ */
  function wire(root) {
    var tgl = root.querySelector('[data-nav-toggle]'), links = root.querySelector('.nav__links');
    if (tgl && links) {
      tgl.addEventListener('click', function () {
        var open = links.classList.toggle('is-open');
        tgl.setAttribute('aria-expanded', String(open));
      });
    }
    root.querySelectorAll('[data-dropdown]').forEach(function (dd) {
      var btn = dd.querySelector('[data-dropdown-toggle]');
      if (!btn) return;
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = dd.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', String(open));
      });
    });
    document.addEventListener('click', function (e) {
      root.querySelectorAll('[data-dropdown].is-open').forEach(function (dd) {
        if (!dd.contains(e.target)) { dd.classList.remove('is-open'); var b = dd.querySelector('[data-dropdown-toggle]'); if (b) b.setAttribute('aria-expanded', 'false'); }
      });
    });
  }

  /* ---- boot --------------------------------------------------------------- */
  function boot() {
    var h = document.getElementById('site-header');
    if (h) {
      var shell = h.getAttribute('data-shell') || 'front';
      var active = h.getAttribute('data-active') || '';
      if (shell === 'app') h.innerHTML = appHeader(h.getAttribute('data-role') || 'student', active);
      else h.innerHTML = frontHeader(active, h.getAttribute('data-auth') || 'guest');
      wire(h);
    }
    var f = document.getElementById('site-footer');
    if (f) f.innerHTML = footer();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
