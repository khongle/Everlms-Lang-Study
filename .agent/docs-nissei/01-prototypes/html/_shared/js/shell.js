/* ============================================================================
   shell.js — renders every page's chrome from one registry.
   A page carries only:
     <div id="site-header" data-shell="…" data-role="…" data-active="P0NN">
     <div id="site-footer"></div>
   Layout follows references/menu/*.png. Three chromes, one connected system:

     auth   sign-in + THE ROLE PICKER — the door into every role's prototype
     front  the public site, and the employee's own area (/app) behind it
     manage back-office for staff roles; carries the branch SCOPE pill

   The loop that makes it one system:
     login -> any role -> "Public front-end" -> "Switch role" -> login

   The shell is DERIVED from data-role, so pages never state which chrome they
   want: employee -> front(member), staff -> manage. No build step; file:// ok.
   ========================================================================== */
(function () {
'use strict';

/* ------------------------------------------------------------------- ICONS */
var I = {
  grid:'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
  matrix:'<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/>',
  users:'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/>',
  user:'<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
  book:'<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
  award:'<circle cx="12" cy="8" r="6"/><path d="M15.5 13.5 17 22l-5-3-5 3 1.5-8.5"/>',
  chart:'<path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-6"/>',
  cal:'<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
  check:'<path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"/><path d="m9 11 3 3L22 4"/>',
  alert:'<path d="m21.7 18-8-14a2 2 0 0 0-3.5 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3z"/><path d="M12 9v4M12 17h.01"/>',
  globe:'<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20"/>',
  cog:'<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
  shield:'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
  lock:'<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
  up:'<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
  lang:'<path d="m5 8 6 6M4 14l6-6 2-3M2 5h12M7 2h1M22 22l-5-10-5 10M14 18h6"/>',
  list:'<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
  swap:'<path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/>',
  home:'<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
  bell:'<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
  search:'<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
  out:'<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
  chev:'<path d="m6 9 6 6 6-6"/>',
  right:'<path d="M5 12h14M12 5l7 7-7 7"/>',
  play:'<circle cx="12" cy="12" r="10"/><path d="m10 8 6 4-6 4z"/>'
};
function svg(k, s) {
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
    'stroke-linecap="round" stroke-linejoin="round" width="' + (s || 20) + '" height="' + (s || 20) +
    '" aria-hidden="true">' + (I[k] || '') + '</svg>';
}

/* ---------------------------------------------------------------- REGISTRY */
/* f file · r owner folder · t title · d description (shown in menus) · i icon */
var P = {
  /* AUTH + PUBLIC */
  P001:{f:'P001-sign-in.html',r:'anonymous',t:'Sign in',d:'Employee code and password',i:'lock'},
  P002:{f:'P002-password-reset.html',r:'anonymous',t:'Password reset',d:'Recover access',i:'lock'},
  P003:{f:'P003-certificate-verification.html',r:'anonymous',t:'Verify certificate',d:'Check a certificate number',i:'award'},
  P004:{f:'P004-public-home.html',r:'anonymous',t:'Home',d:'Return to the public home',i:'home'},
  /* APP — employee */
  P010:{f:'P010-my-dashboard.html',r:'employee',t:'Overview',d:'What is due and what is overdue',i:'grid'},
  P011:{f:'P011-my-required-training.html',r:'employee',t:'My required training',d:'Set by your job role',i:'check'},
  P012:{f:'P012-course-catalog.html',r:'employee',t:'Course catalog',d:'Required and optional courses',i:'book'},
  P013:{f:'P013-course-detail.html',r:'employee',t:'Course detail',d:'Outline and sessions',i:'book'},
  P014:{f:'P014-lesson-player.html',r:'employee',t:'Lesson player',d:'Continue learning',i:'play'},
  P015:{f:'P015-quiz-attempt.html',r:'employee',t:'Quiz',d:'Assessment',i:'check'},
  P016:{f:'P016-quiz-result.html',r:'employee',t:'Quiz result',d:'Score and breakdown',i:'check'},
  P017:{f:'P017-my-training-record.html',r:'employee',t:'My training record',d:'Full history across branches',i:'list'},
  P018:{f:'P018-my-certificates.html',r:'employee',t:'My certificates',d:'Validity and downloads',i:'award'},
  P019:{f:'P019-notifications.html',r:'employee',t:'Notifications',d:'Reminders and invitations',i:'bell'},
  P020:{f:'P020-my-profile.html',r:'employee',t:'Profile',d:'Language and preferences',i:'user'},
  /* WORKSPACE */
  P021:{f:'P021-workspace-home.html',r:'instructor',t:'Workspace home',d:'Your day at a glance',i:'grid'},
  P022:{f:'P022-training-schedule.html',r:'instructor',t:'Training schedule',d:'Classroom sessions',i:'cal'},
  P023:{f:'P023-session-attendance.html',r:'instructor',t:'Session attendance',d:'Roster and assessment',i:'check'},
  P024:{f:'P024-manage-courses.html',r:'instructor',t:'Manage courses',d:'Courses you author',i:'book'},
  P025:{f:'P025-course-editor.html',r:'instructor',t:'Course editor',d:'Lessons and content',i:'book'},
  P026:{f:'P026-quiz-builder.html',r:'instructor',t:'Quiz builder',d:'Questions and pass mark',i:'check'},
  P027:{f:'P027-learner-progress.html',r:'instructor',t:'Learner progress',d:'Who is where',i:'chart'},
  P028:{f:'P028-site-dashboard.html',r:'site-training-manager',t:'Site dashboard',d:'Compliance at your branch',i:'grid'},
  P029:{f:'P029-employees.html',r:'site-training-manager',t:'Employees',d:'People and their status',i:'users'},
  P030:{f:'P030-employee-record.html',r:'site-training-manager',t:'Employee record',d:'One person, full history',i:'list'},
  P031:{f:'P031-assign-training.html',r:'site-training-manager',t:'Assign training',d:'Exceptions and ad-hoc',i:'up'},
  P032:{f:'P032-overdue-worklist.html',r:'site-training-manager',t:'Due and overdue',d:'Today’s worklist',i:'alert'},
  P033:{f:'P033-reports.html',r:'site-training-manager',t:'Reports',d:'Compliance reporting',i:'chart'},
  P034:{f:'P034-certificates.html',r:'site-training-manager',t:'Certificates',d:'Issued and expiring',i:'award'},
  /* CENTRAL */
  P035:{f:'P035-central-dashboard.html',r:'hr-hse-central',t:'Central dashboard',d:'All branches at a glance',i:'grid'},
  P036:{f:'P036-training-matrix.html',r:'hr-hse-central',t:'Training matrix',d:'Job role by course rules',i:'matrix'},
  P037:{f:'P037-job-roles.html',r:'hr-hse-central',t:'Job roles',d:'What drives obligations',i:'users'},
  P038:{f:'P038-master-catalog.html',r:'hr-hse-central',t:'Master catalog',d:'One catalog for every site',i:'book'},
  P039:{f:'P039-course-versions.html',r:'hr-hse-central',t:'Versions and publishing',d:'Publish to all sites at once',i:'up'},
  P040:{f:'P040-branches.html',r:'hr-hse-central',t:'Branches',d:'Sites and their rulesets',i:'globe'},
  P041:{f:'P041-import-employees.html',r:'hr-hse-central',t:'Import employees',d:'CSV with reconciliation',i:'up'},
  P042:{f:'P042-import-history.html',r:'hr-hse-central',t:'Import history',d:'Legacy records from Excel',i:'up'},
  P043:{f:'P043-certificate-templates.html',r:'hr-hse-central',t:'Certificate templates',d:'Bilingual VI and JA',i:'award'},
  P044:{f:'P044-reminder-rules.html',r:'hr-hse-central',t:'Reminder rules',d:'What closes the loop',i:'bell'},
  P045:{f:'P045-translation-workspace.html',r:'hr-hse-central',t:'Translations',d:'Pinned to source version',i:'lang'},
  P046:{f:'P046-content-approval.html',r:'hr-hse-central',t:'Content approval',d:'Draft, review, publish',i:'check'},
  /* HQ */
  P047:{f:'P047-hq-dashboard.html',r:'hq-viewer',t:'HQ dashboard',d:'The group on one screen',i:'grid'},
  P048:{f:'P048-branch-comparison.html',r:'hq-viewer',t:'Branch comparison',d:'One KPI set, every site',i:'chart'},
  P049:{f:'P049-scheduled-exports.html',r:'hq-viewer',t:'Scheduled exports',d:'What arrives at HQ',i:'up'},
  /* ADMIN */
  P050:{f:'P050-admin-overview.html',r:'administrator',t:'Admin overview',d:'System at a glance',i:'grid'},
  P051:{f:'P051-roles-permissions.html',r:'administrator',t:'Roles and permissions',d:'Seven roles, three scopes',i:'shield'},
  P052:{f:'P052-branch-configuration.html',r:'administrator',t:'Branch configuration',d:'Add a country as data',i:'globe'},
  P053:{f:'P053-language-localization.html',r:'administrator',t:'Language',d:'Locales and fallback',i:'lang'},
  P054:{f:'P054-integrations.html',r:'administrator',t:'Integrations',d:'SMTP, SSO, HRIS',i:'cog'},
  P055:{f:'P055-audit-log.html',r:'administrator',t:'Audit log',d:'Immutable trail',i:'list'},
  P056:{f:'P056-switch-user.html',r:'administrator',t:'Switch user',d:'Impersonation, always logged',i:'swap'},
  P057:{f:'P057-system-health.html',r:'administrator',t:'System health',d:'Jobs and queues',i:'cog'},
  P058:{f:'P058-data-retention.html',r:'administrator',t:'Data retention',d:'Decree 13 and PIPL',i:'shield'}
};

/* Variants: a page built inside a non-owner folder because that role's view
   genuinely differs (roles.txt §5). Key = "role/PID". */
var VARIANT = {
  'site-training-manager/P021':'P021-workspace-home.html',
  'site-training-manager/P022':'P022-training-schedule.html',
  'site-training-manager/P027':'P027-learner-progress.html',
  'hr-hse-central/P029':'P029-employees.html',
  'hr-hse-central/P030':'P030-employee-record.html',
  'hr-hse-central/P031':'P031-assign-training.html',
  'hr-hse-central/P032':'P032-overdue-worklist.html',
  'hr-hse-central/P033':'P033-reports.html',
  'hr-hse-central/P034':'P034-certificates.html',
  'hq-viewer/P033':'P033-reports.html',
  'administrator/P029':'P029-employees.html'
};

/* ------------------------------------------------------------------- ROLES */
/* who/code/job/branch/av : the demo person (content bible, roles.txt §6)
   scope : the reach pill in the manage header — a role's power in one glance
   sections : back-office nav, grouped; each opens a hover mega
   home : where "enter this role" lands from the login role picker */
var ROLES = {
  employee:{
    label:'Employee', who:'Nguyen Van Minh', code:'NEV-004821', job:'Production Operator',
    branch:'NEV-HCM', av:'avatar-01.jpg', lang:'VI', chrome:'front', home:'P010',
    blurb:'My training, my record, my certificates',
    sections:[{n:'My learning',p:['P010','P011','P012','P017','P018']},
              {n:'Account',p:['P019','P020']}]
  },
  instructor:{
    label:'Instructor', who:'Tran Thi Hoa', code:'NEV-001204', job:'HSE Officer',
    branch:'NEV-HCM', av:'avatar-05.jpg', lang:'VI', chrome:'manage', home:'P021',
    blurb:'Sessions, attendance and course authoring',
    scope:'NEV-HCM', scopeKind:'branch',
    sections:[{n:'Teaching',p:['P021','P022','P023']},
              {n:'Content',p:['P024','P025','P026']},
              {n:'Learners',p:['P027']},
              {n:'My learning',p:['P010','P011','P017','P018']},
              {n:'Account',p:['P019','P020']}]
  },
  'site-training-manager':{
    label:'Site Training Manager', who:'Le Quoc Bao', code:'NEH-000377', job:'Training Manager',
    branch:'NEH-HAN', av:'avatar-07.jpg', lang:'VI', chrome:'manage', home:'P028',
    blurb:'One branch: people, overdue, schedule, reports',
    scope:'NEH-HAN', scopeKind:'branch',
    sections:[{n:'Site',p:['P028','P029','P030']},
              {n:'Training',p:['P031','P032','P022','P027']},
              {n:'Records',p:['P033','P034']},
              {n:'My learning',p:['P010','P011','P017','P018']},
              {n:'Account',p:['P019','P020']}]
  },
  'hr-hse-central':{
    label:'HR/HSE Central', who:'Pham Thu Ha', code:'NEV-000045', job:'HSE Manager',
    branch:'Central', av:'avatar-03.jpg', lang:'VI', chrome:'manage', home:'P035',
    blurb:'Owns the standard: matrix, catalog, translations',
    scope:'ALL BRANCHES', scopeKind:'global',
    sections:[{n:'Standards',p:['P036','P037','P038','P039','P046','P045']},
              {n:'People',p:['P029','P030','P031','P041','P042']},
              {n:'Compliance',p:['P035','P032','P033','P034','P043','P044']},
              {n:'Oversight',p:['P040','P047','P048']},
              {n:'Account',p:['P019','P020']}]
  },
  'hq-viewer':{
    label:'HQ Viewer', who:'Sato Kenji', code:'HQ-000012', job:'Global HSE',
    branch:'Hamamatsu HQ', av:'avatar-09.jpg', lang:'JA', chrome:'manage', home:'P047',
    blurb:'Read-only oversight of the whole group',
    scope:'GROUP · READ ONLY', scopeKind:'read',
    sections:[{n:'Oversight',p:['P047','P048','P033','P049']},
              {n:'Account',p:['P019','P020']}]
  },
  administrator:{
    label:'Administrator', who:'Dang Minh Tuan', code:'NEV-000009', job:'IT Administrator',
    branch:'Central', av:'avatar-04.jpg', lang:'EN', chrome:'manage', home:'P050',
    blurb:'Configuration, security and the audit trail',
    scope:'SYSTEM', scopeKind:'system',
    sections:[{n:'System',p:['P050','P052','P053','P054','P057','P058']},
              {n:'Security',p:['P051','P055','P056']},
              {n:'Business surfaces',p:['P035','P036','P029','P033']},
              {n:'Account',p:['P019','P020']}]
  }
};
var ROLE_ORDER = ['employee','instructor','site-training-manager','hr-hse-central','hq-viewer','administrator'];
var LANGS = [['EN','English'],['VI','Tieng Viet'],['JA','Nihongo'],['ZH','Zhongwen']];

/* Resolve a link from folder `from` to page id `id`: prefer a variant that
   lives in the current folder, else the page's owner folder. */
function href(id, from) {
  var v = VARIANT[from + '/' + id];
  if (v) return '../' + from + '/' + v;
  var p = P[id];
  return p ? '../' + p.r + '/' + p.f : '#';
}
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function megaitem(id, from, active) {
  var p = P[id]; if (!p) return '';
  return '<a class="megaitem" href="' + href(id, from) + '"' +
    (id === active ? ' aria-current="page"' : '') + '>' +
    '<span class="megaitem__ic">' + svg(p.i, 16) + '</span>' +
    '<span><b>' + esc(p.t) + '</b><small>' + esc(p.d) + '</small></span></a>';
}

/* ------------------------------------------------- account mega (shared) */
/* Identity header, grouped columns, then the footer that connects the system:
   Public front-end · Switch role · Sign out. */
function acctMega(roleKey, from, active) {
  var R = ROLES[roleKey];
  var cols = R.sections.map(function (s) {
    return '<div class="acctmega__col"><h4>' + esc(s.n) + '</h4>' +
      s.p.map(function (id) { return megaitem(id, from, active); }).join('') + '</div>';
  }).join('');

  return '<div class="acctmega" id="acctmega">' +
    '<div class="acctmega__head">' +
      '<img src="../_shared/img/' + R.av + '" alt="">' +
      '<div><b>' + esc(R.who) + '</b><small>' + esc(R.label + ' · ' + R.job) + '</small>' +
        '<small class="codecell">' + esc(R.code + ' · ' + R.branch) + '</small></div>' +
      '<a class="acctmega__home" href="' + href('P004', from) + '" aria-label="Public home">' +
        svg('home', 20) + '</a>' +
    '</div>' +
    '<div class="acctmega__cols">' + cols + '</div>' +
    '<div class="acctmega__foot">' +
      '<a class="footlink" href="' + href('P004', from) + '">' + svg('home', 18) +
        '<span><b>Public front-end</b><small>Return to the public home</small></span></a>' +
      '<span class="spacer"></span>' +
      '<span class="flex items-center gap-2 text-sm text-muted">' + svg('globe', 16) +
        '<span class="langgrid">' + LANGS.map(function (l) {
          return '<span class="langchip' + (l[0] === R.lang ? ' langchip--live' : '') + '">' +
            l[0] + '</span>'; }).join('') + '</span></span>' +
      '<a class="footlink footlink--switch" href="' + href('P001', from) + '">' + svg('swap', 18) +
        '<span><b>Switch role</b><small>Open another role</small></span></a>' +
      '<a class="footlink footlink--danger" href="' + href('P001', from) + '">' + svg('out', 18) +
        '<span><b>Sign out</b><small>End this session</small></span></a>' +
    '</div>' +
  '</div>';
}

function acctPill(roleKey, from, active) {
  var R = ROLES[roleKey];
  return '<div class="acct">' +
    '<button class="acct__btn" aria-expanded="false" aria-controls="acctmega">' +
      '<img class="nav__avatar" src="../_shared/img/' + R.av + '" alt="">' +
      '<span class="acct__nm text-sm fw-700">' + esc(R.who) + '</span>' + svg('chev', 16) +
    '</button>' + acctMega(roleKey, from, active) + '</div>';
}

/* ------------------------------------------------------------ AUTH CHROME */
function authHeader(from) {
  return '<header class="site-header"><div class="container">' +
    '<nav class="nav" aria-label="Primary" style="justify-content:space-between">' +
      '<a class="brand" href="' + href('P004', from) + '">' +
        '<span class="brand__mark">N</span>' +
        '<span class="brand__name">Nissei Training<small>Compliance system</small></span></a>' +
      '<a class="text-sm fw-700 flex items-center gap-2" href="' + href('P004', from) + '">' +
        svg('home', 16) + 'Back to website</a>' +
    '</nav></div></header>';
}

/* ----------------------------------------------------------- FRONT CHROME */
/* Public site. Guest sees Sign in; a signed-in employee sees My account,
   exactly as in references/menu/frontend-logged-in.png. */
function frontHeader(roleKey, from, active) {
  var links = [
    ['P004','Home'], ['P003','Verify certificate']
  ].map(function (l) {
    return '<li><a href="' + href(l[0], from) + '"' +
      (l[0] === active ? ' aria-current="page"' : '') + '>' + l[1] + '</a></li>';
  }).join('');

  var right = roleKey
    ? '<div class="fnav__cta">' +
        '<button class="icon-btn" aria-label="Notifications" onclick="location.href=\'' +
          href('P019', from) + '\'">' + svg('bell') + '<span class="icon-btn__dot"></span></button>' +
        acctPill(roleKey, from, active) + '</div>'
    : '<div class="fnav__cta">' +
        '<a class="btn btn--ghost btn--sm" href="' + href('P003', from) + '">Verify a certificate</a>' +
        '<a class="btn btn--brand btn--sm" href="' + href('P001', from) + '">Sign in</a></div>';

  return '<div class="topbar"><div class="container">' +
      '<span class="topbar__promo">Annual safety refresher window opens <b>01 Aug 2026</b>. ' +
        '<a href="' + href('P001', from) + '">Sign in to check your due dates</a></span>' +
      '<span class="langgrid">' + LANGS.map(function (l, i) {
        return '<span class="langchip' + (i === 0 ? ' langchip--live' : '') + '">' + l[0] + '</span>';
      }).join('') + '</span>' +
    '</div></div>' +
    '<header class="site-header"><div class="container">' +
      '<nav class="nav" aria-label="Primary">' +
        '<a class="brand" href="' + href('P004', from) + '">' +
          '<span class="brand__mark">N</span>' +
          '<span class="brand__name">Nissei Training<small>Compliance system</small></span></a>' +
        '<ul class="nav__links" id="nav-menu" data-nav>' + links + '</ul>' +
        right +
        '<button class="nav__toggle" aria-label="Menu" aria-controls="nav-menu" aria-expanded="false">' +
          '<span></span><span></span><span></span></button>' +
      '</nav></div></header>';
}

/* ---------------------------------------------------------- MANAGE CHROME */
/* Back-office. Row 1 = brand + SCOPE pill + search + tools. Row 2 = section
   nav with hover megas. Follows references/menu/manage*.png. */
function manageHeader(roleKey, from, active) {
  var R = ROLES[roleKey];
  var activeSection = -1;
  R.sections.forEach(function (s, i) { if (s.p.indexOf(active) > -1) activeSection = i; });

  var nav = R.sections.map(function (s, i) {
    return '<li class="' + (i === activeSection ? 'is-active ' : '') + 'has-mega">' +
      '<a href="' + href(s.p[0], from) + '">' + esc(s.n) + svg('chev', 14) + '</a>' +
      '<div class="mega">' + s.p.map(function (id) {
        return megaitem(id, from, active); }).join('') + '</div></li>';
  }).join('');

  return '<div class="mhead">' +
    '<div class="container"><div class="mhead__r1">' +
      '<a class="brand mbrand" href="' + href(R.home, from) + '">' +
        '<span class="brand__mark">N</span>' +
        '<span class="brand__name">Nissei Training<small>Manage · ' + esc(R.label) + '</small></span></a>' +
      '<span class="scopepill scopepill--' + R.scopeKind + '">' + esc(R.scope) + '</span>' +
      '<span class="nav__search" style="max-width:320px;flex:1">' + svg('search', 18) +
        '<label class="sr-only" for="gsearch">Search</label>' +
        '<input id="gsearch" type="search" placeholder="Search people, courses, certificates..."></span>' +
      '<span class="mhead__tools">' +
        '<a class="icon-btn" href="' + href('P004', from) + '" aria-label="Public front-end">' +
          svg('home') + '</a>' +
        '<a class="icon-btn" href="' + href('P019', from) + '" aria-label="Notifications">' +
          svg('bell') + '<span class="icon-btn__dot"></span></a>' +
        acctPill(roleKey, from, active) +
      '</span>' +
    '</div></div>' +
    '<div class="mhead__r2"><div class="container">' +
      '<ul class="mnav" data-nav>' + nav + '</ul>' +
    '</div></div>' +
  '</div>';
}

/* ---------------------------------------------------------------- FOOTER */
function footer(roleKey, from) {
  var R = roleKey && ROLES[roleKey];
  var scope = R ? (R.scope || 'own record only') : 'Internal system. Sign-in required.';
  return '<footer class="site-footer"><div class="container">' +
    '<div class="footer__legal" style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap">' +
      '<span class="text-sm text-muted">Nissei Electric. Compliance Training System. ' +
        'Prototype with demo data, not production.</span>' +
      '<span class="text-sm text-muted">' +
        (R ? 'Signed in as ' + esc(R.label) + ' · scope: ' + esc(scope) +
             ' · <a href="' + href('P001', from) + '">Switch role</a>'
           : esc(scope)) +
      '</span>' +
    '</div></div></footer>';
}

/* ------------------------------------------------------------------- BOOT */
var h = document.getElementById('site-header');
if (h) {
  var shell = h.getAttribute('data-shell') || 'app';
  var role  = h.getAttribute('data-role') || '';
  var active = h.getAttribute('data-active') || '';
  /* current folder = the page's own directory, used to resolve variants */
  var seg = location.pathname.split('/');
  var from = seg[seg.length - 2] || (P[active] && P[active].r) || 'anonymous';

  var html;
  if (shell === 'auth')        html = authHeader(from);
  else if (shell === 'front')  html = frontHeader(role, from, active);   /* role '' = guest */
  else if (role && ROLES[role] && ROLES[role].chrome === 'manage')
                               html = manageHeader(role, from, active);
  else                         html = frontHeader(role, from, active);   /* employee /app */
  h.outerHTML = html;

  var f = document.getElementById('site-footer');
  if (f) f.outerHTML = footer(shell === 'auth' || shell === 'front' ? (role || '') : role, from);

  /* account mega: base.css opens it via .acct.is-open */
  var btn = document.querySelector('.acct__btn');
  if (btn) {
    var acct = btn.closest('.acct');
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = acct.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', function (e) {
      if (acct.classList.contains('is-open') && !acct.contains(e.target)) {
        acct.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false');
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && acct.classList.contains('is-open')) {
        acct.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); btn.focus();
      }
    });
  }
}

/* Expose the registry so the login role picker can render itself from it —
   one source of truth for "which roles exist and where each one starts". */
window.NISSEI = {P:P, ROLES:ROLES, ROLE_ORDER:ROLE_ORDER, href:href, svg:svg};
})();
