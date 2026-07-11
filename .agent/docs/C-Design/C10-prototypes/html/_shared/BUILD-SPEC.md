# EverLMS C10 prototype — BUILD SPEC (read fully before writing any page)

Self-contained multi-page HTML prototype for **EverLMS** (Drupal 11 LMS). One shared
design system in `_shared/`. Goal: beautiful, professional, consistent, responsive.
Pages are filed **once**, in the folder of the **lowest role** that can access them;
cross-navigation to everything a role can reach is provided by the injected menu.

## 0. Non-negotiables
1. **Chrome is injected — never hand-write header/footer.** Each page has exactly:
   a `<div id="site-header" …>` placeholder, your `<main id="main">`, a
   `<div id="site-footer"></div>`, then the two shared scripts. `shell.js` renders the
   header (main menu + My Account mega menu + Home icon) and footer from data-attributes.
2. **No sidebar navigation anywhere.** The ONLY allowed vertical rails are the
   course/lesson **business** lists: the curriculum accordion on P003, the lesson rail
   on P019, the question rail on P020. Everything else uses the horizontal top menu +
   `.pagehead`, `.segmented`, `.tabs`. (This is a hard rule from the brief.)
3. **Reuse the design system.** All styling comes from `_shared/css/everlms.css`
   (+ `skins.css`). Never invent colors — use the class vocabulary + CSS variables
   (`var(--brand)` …). Tiny page tweaks may use inline `style="…"` with tokens only.
4. **Relative paths only.** CSS/JS/img via `../_shared/…`. Cross-page links via
   `../<role-folder>/<file>` (see the page registry in §4). Every page is one level
   under `html/`, so `../` always reaches a sibling role folder.
5. **Real photos** from `../_shared/img/` (manifest §5). Never hotlink the web.
6. **No emojis, no dashes-as-bullets in UI.** Use inline SVG icons (24×24,
   `stroke="currentColor"`). No placeholder filler text — write real, professional copy.
7. Accessible: semantic landmarks, `alt` on every `<img>`, labels on inputs, WCAG AA.
8. Responsive at Bootstrap 5 breakpoints (already handled by the CSS; only add
   `@media (max-width: 575.98|767.98|991.98|1199.98|1399.98px)` if you must).

## 1. Exact page skeleton (copy for EVERY page)
```html
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{Page title} · EverLMS</title>
<meta name="description" content="{one line}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../_shared/css/everlms.css">
<link rel="stylesheet" href="../_shared/css/skins.css">
</head>
<body>
<div id="site-header" data-shell="{front|app}" {data-role="…"} data-active="{P###}"></div>
<main id="main">
  … your page sections …
</main>
<div id="site-footer"></div>
<script src="../_shared/js/shell.js"></script>
<script src="../_shared/js/app.js"></script>
</body>
</html>
```

### Header data-attributes
- Public pages (P001–P016) → `data-shell="front" data-active="P00X"`. (Front shell = main
  menu + Log in / Sign up. Search + login sit top-right.)
- Everything behind login (P017–P051) → `data-shell="app" data-role="{role}" data-active="P0XX"`.
  App shell = backend main menu + `[Home icon → public home]` + notification bell +
  **My Account** mega menu (role-filtered launcher), top-right.
- `data-role` values: `student` (P017–P030), `instructor` (P031–P034,P044,P045),
  `content-editor` (P037,P039,P040,P041,P042,P043), `manager` (P035,P036,P038),
  `administrator` (P046–P051).
- `data-active` is the page's own P### id — it highlights the matching menu link.

## 2. Which reference page to mimic
- **Front / public (P002–P016)** → mimic `anonymous/P001-homepage.html` idiom:
  `.pagehero`/`.crumb` at top for inner pages, `.section` rhythm, `.ccard`, `.grid`.
- **Student app (P018–P030)** → mimic `student/P017-dashboard.html`: `.workhero` intro,
  `.kpis`, cards, `.tabs`, `.contcard`, `.progress`. NO sidebar.
- **Workspace / admin (P031–P051)** → mimic `instructor/P032-manage-courses.html`:
  `.workhero`+`.crumb`+`.pagehead`, `.segmented` filters, `.tablecard`+`.wtable`,
  `.status` pills, `.rowbtns`, `.pager`. Editors/settings use `.editor` two-pane
  (form + meta aside — this is content, not nav), `.formgrid`, `.panel`, `.switchrow`,
  settings use top `.tabs` (never a side nav).

## 3. Page states to represent (for the coverage checklist; build the DEFAULT state
richly, and where natural show the others inline — empty blocks, error alert, etc.)
Every archetype has 5 canonical states: **default, loading (skeleton), empty,
error, permission-denied**. You build one HTML per page (default/primary). Where it
reads naturally, include an inline example of empty state (`.alert`, empty-list card)
or an error (`.alert--…`). The checklist documents all five per page.

## 4. Page registry — id · file name · role folder · shell · title
Build the file at `html/<role folder>/<file name>`.

| id | file | folder | shell / role | title |
|----|------|--------|--------------|-------|
| P001 | P001-homepage.html | anonymous | front | Homepage *(built)* |
| P002 | P002-course-catalog.html | anonymous | front | Course catalog |
| P003 | P003-course-detail.html | anonymous | front | Course detail *(curriculum accordion + sticky enroll aside allowed)* |
| P004 | P004-categories.html | anonymous | front | Categories |
| P005 | P005-category-detail.html | anonymous | front | Category detail |
| P006 | P006-pricing.html | anonymous | front | Pricing |
| P007 | P007-instructor-profile.html | anonymous | front | Instructor profile |
| P008 | P008-blog.html | anonymous | front | Blog / News |
| P009 | P009-article-detail.html | anonymous | front | Article detail |
| P010 | P010-about.html | anonymous | front | About |
| P011 | P011-contact.html | anonymous | front | Contact |
| P012 | P012-glossary.html | anonymous | front | Glossary |
| P013 | P013-forum-index.html | anonymous | front | Forum index |
| P014 | P014-forum-topic.html | anonymous | front | Forum topic |
| P015 | P015-certificate-verify.html | anonymous | front | Certificate verify |
| P016 | P016-login-register.html | anonymous | front | Login / Register *(use `.authwrap` split)* |
| P017 | P017-dashboard.html | student | app / student | Dashboard *(built)* |
| P018 | P018-my-courses.html | student | app / student | My courses |
| P019 | P019-lesson-player.html | student | app / student | Lesson player *(rail allowed; see §6)* |
| P020 | P020-quiz-take.html | student | app / student | Quiz take *(question rail/dots allowed)* |
| P021 | P021-quiz-result.html | student | app / student | Quiz result |
| P022 | P022-my-quiz-results.html | student | app / student | My quiz results |
| P023 | P023-my-certificates.html | student | app / student | My certificates |
| P024 | P024-my-notes.html | student | app / student | My notes |
| P025 | P025-my-wishlist.html | student | app / student | Wishlist |
| P026 | P026-my-membership.html | student | app / student | My membership |
| P027 | P027-notifications.html | student | app / student | Notifications |
| P028 | P028-profile.html | student | app / student | Profile |
| P029 | P029-checkout-course.html | student | app / student | Checkout (course) |
| P030 | P030-checkout-membership.html | student | app / student | Checkout (membership) |
| P031 | P031-workspace-home.html | instructor | app / instructor | Workspace home |
| P032 | P032-manage-courses.html | instructor | app / instructor | Manage courses *(built)* |
| P033 | P033-course-editor.html | instructor | app / instructor | Course editor *(`.editor` two-pane)* |
| P034 | P034-manage-quizzes.html | instructor | app / instructor | Manage quizzes |
| P035 | P035-manage-users.html | manager | app / manager | Manage users |
| P036 | P036-manage-orders.html | manager | app / manager | Manage orders |
| P037 | P037-manage-content.html | content-editor | app / content-editor | Manage content |
| P038 | P038-manage-pricing.html | manager | app / manager | Manage pricing / plans |
| P039 | P039-manage-ratings.html | content-editor | app / content-editor | Manage ratings |
| P040 | P040-certificate-templates.html | content-editor | app / content-editor | Certificate templates *(`.editor` two-pane)* |
| P041 | P041-issued-certificates.html | content-editor | app / content-editor | Issued certificates |
| P042 | P042-content-tools.html | content-editor | app / content-editor | Content tools |
| P043 | P043-manage-blog.html | content-editor | app / content-editor | Manage blog |
| P044 | P044-ai-course-builder.html | instructor | app / instructor | AI course builder |
| P045 | P045-workspace-reports.html | instructor | app / instructor | Workspace reports |
| P046 | P046-ai-settings.html | administrator | app / administrator | AI settings *(top `.tabs`)* |
| P047 | P047-payment-settings.html | administrator | app / administrator | Payment settings |
| P048 | P048-zoom-settings.html | administrator | app / administrator | Zoom settings |
| P049 | P049-analytics-reports.html | administrator | app / administrator | Analytics reports |
| P050 | P050-notification-broadcast.html | administrator | app / administrator | Notification broadcast |
| P051 | P051-switch-user.html | administrator | app / administrator | Switch user |

## 5. Content bible (keep identical everywhere so it reads like one real product)
**Brand:** EverLMS — "Learn without limits". **Platform:** 1,200+ courses · 350+
instructors · 480,000+ learners · 4.8 avg rating · 92% completion.
**Logged-in learner:** Alex Morgan (avatar-03.jpg), 7-day streak.
**Staff demo users:** Sarah Chen (instructor-01, Instructor), Priya Sharma
(instructor-02, Content Editor), James Carter (instructor-m-02, Manager),
Dana Whitfield (avatar-06, Administrator).

**Categories (8):** Web Development · AI & Machine Learning · Data & Analytics ·
Design & UX · Business · Marketing · Photography & Video · Personal Development.

**Canonical courses** (exact titles/instructors/prices/ratings — reuse everywhere):
| # | Title | Category | Instructor | Price | Old | Rating (reviews) | Hours·Lessons·Level | Image |
|---|-------|----------|-----------|-------|-----|------------------|---------------------|-------|
| 1 | Complete Modern React & Next.js Bootcamp | Web Development | Sarah Chen | $89.99 | $199.99 | 4.9 (12,480) | 52h·420·All | course-web-01 |
| 2 | Machine Learning A–Z: Hands-On Python & AI | AI & ML | Dr. Marcus Reed | $94.99 | $189.99 | 4.8 (9,210) | 44h·380·Intermediate | course-ai-01 |
| 3 | Data Analytics with SQL, Python & Power BI | Data & Analytics | Priya Sharma | $79.99 | $159.99 | 4.7 (6,540) | 38h·300·Beginner | course-data-01 |
| 4 | UI/UX Design Masterclass: Figma to Product | Design & UX | Elena Rossi | $84.99 | $169.99 | 4.9 (8,300) | 30h·260·All | course-design-01 |
| 5 | The Complete Digital Marketing Course | Marketing | James Carter | $69.99 | $149.99 | 4.6 (15,120) | 41h·350·Beginner | course-biz-01 |
| 6 | Python for Everybody: Zero to Hero | Web Development | David Kim | Free | — | 4.8 (22,050) | 28h·210·Beginner | course-edu-01 |
| 7 | IELTS & Business English Mastery | Personal Development | Amara Okafor | $59.99 | $119.99 | 4.7 (4,210) | 22h·180·All | course-lang-01 |
| 8 | Cybersecurity & Ethical Hacking Fundamentals | Data & Analytics | Tomás Alvarez | $99.99 | $199.99 | 4.8 (5,870) | 35h·290·Intermediate | course-web-02 |

**Course #1 curriculum** (P003 accordion + P019 rail):
- S1 Getting Started (4 · 38m): Welcome & setup · How the course works · Dev environment · Your first component
- S2 React Fundamentals (8 · 2h15m): JSX deep dive · Props & components · **State with useState** · Handling events · Conditional rendering · Lists & keys · Forms · Project: Todo app
- S3 Hooks in Depth (7 · 3h10m): useEffect · useRef · useMemo & useCallback · Custom hooks · useContext · useReducer · Project: Weather dashboard
- S4 Next.js & Full-Stack (9 · 4h05m): App Router · Server components · Data fetching · Route handlers · Auth · Database with Prisma · Deployment · Performance · Capstone
(Current lesson on P019 = "State with useState"; earlier = done, later = locked.)

**Pricing plans:** Free ($0 — 1 track, community, mobile) · Pro ($29/mo, featured —
unlimited courses, certificates, offline, monthly mentoring) · Teams ($99/mo —
Pro + 20 seats, analytics, SSO, priority support).
**Reviews:** avatars 01,02,04,05,06,07; names Linh Tran, Michael O., Sofia Garcia,
Daniel W., Aisha N., Kenji Watanabe; 4–5 stars; short authentic blurbs.

## 6. Image manifest (`../_shared/img/`)
- Course thumbs (16:10): `course-web-01..04`, `course-ai-01..04`, `course-data-01..04`,
  `course-design-01..04`, `course-biz-01..04`, `course-edu-01..04`, `course-lang-01..04`,
  `course-k12-01..04`.
- Instructor portraits: `instructor-01..08`, `instructor-m-01..06`, `instructor-d-01..06`.
- Learner avatars: `avatar-01..10`.
- Scenes (hero/about/classroom): `scene-team-01..04`, `scene-life-01..04`, `scene-room-01..04`.

## 7. Component vocabulary (all already styled in everlms.css)
- Layout: `.container`(`--narrow`,`--prose`), `.section`(`--soft`,`--mist`,`--ink`,`--brand`,`--tight`),
  `.grid`(`--2/3/4/auto`), `.split`, `.frow(s)`, `.steps`, `.stats`, `.kpis`, `.detail`, `.article`.
- Marketing: `.eyebrow`, `.lede`, `.sec-head`, `.grad-text`, `.pagehero`(`--ink`,`--brand`), `.crumb`,
  `.ctaband`, `.logos/.logoslot`, `.quote`, `.tcard`+`.stars`, `.member`, `.faq`(native `<details>`),
  `.timeline`, `.icon-list`, `.gallery`, `.videobox`, `.mapbox`.
- Buttons `.btn`(`--primary`gold, `--brand`, `--ghost`, `--white`, `--ink`, `--sm`,`--lg`,`--block`),
  `.badge`(`--accent/success/warning/error/info/ink/solid/dot`), `.chip`(`.is-active`), `.chip-row`.
- Cards `.card`(`--soft`,`--pad-lg`), `.card__icon`(`--accent`), `.feature`.
- LMS: `.ccard` (course card — copy structure from P001), `.enroll`(sticky buy card),
  `.curri`(curriculum accordion; toggle via `.curri__head`, add `.is-open`), `.review-summary`+
  `.review-bar`+`.rev`, `.insthead`, `.kpi`, `.contcard`, `.ring`(`style="--p:NN"`), `.progress`
  (`>span style="width:NN%"`)+`.progress-label`, `.plans/.plan`(`--feature`,`__tag`), `.certcard`+
  `.cert-preview`, `.quizwrap/.quiz__*/.opt`(`.is-selected/.is-correct/.is-wrong`), `.player*`,
  `.noteitem`, `.alert`(`--info/--success`), `.catalog`+`.filter` (course catalog only — this is a
  content filter panel, NOT global nav; ok on P002/P005), `.compare`(pricing table), `.dtable`.
- Workspace (staff): `.workhero`, `.pagehead`(`__t`,`__actions`), `.segmented`(`a.is-active`),
  `.tablecard`, `.wtable`(`.num`,`.wtable__wrap`), `.cell-user`, `.cell-thumb`, `.rowbtns`,
  `.status`(`--success/--warning/--error/--info/--muted`,`--dot`), `.editor`(`__aside`), `.panel`(`--soft`),
  `.formgrid`(`--2`), `.hint`, `.switchrow`+`.swtoggle`(`.is-on`), `.dropzone`, `.barchart`+`.chartcard`,
  `.tabs`+`[data-tabs]`/`[data-tab]`/`[data-pane]`, `.pager`, `.select`, `.form`/`.field`(`--row`).

### Course-card markup (paste wherever a course appears)
```html
<article class="ccard">
  <div class="ccard__media"><img src="../_shared/img/course-web-01.jpg" alt="…">
    <span class="ccard__cat badge badge--ink">Web Development</span>
    <span class="ccard__price"><small>$199.99</small>$89.99</span></div>
  <div class="ccard__body">
    <div class="ccard__meta"><span>52h</span><span>420 lessons</span></div>
    <h3 class="ccard__title"><a href="../anonymous/P003-course-detail.html">Complete Modern React &amp; Next.js Bootcamp</a></h3>
    <div class="ccard__author"><img src="../_shared/img/instructor-01.jpg" alt=""> Sarah Chen</div>
    <div class="ccard__foot"><span class="ccard__rating"><span class="rating-num">4.9</span>
      <span class="stars"><svg viewBox="0 0 24 24"><path d="m12 2 3 6.5 7 .9-5 4.8 1.3 7L12 18l-6.6 3.2L6.7 14l-5-4.8 7-.9z"/></svg></span>
      <small>(12,480)</small></span><span class="badge badge--accent">Bestseller</span></div>
  </div>
</article>
```
Star icon (fill): `<svg viewBox="0 0 24 24"><path d="m12 2 3 6.5 7 .9-5 4.8 1.3 7L12 18l-6.6 3.2L6.7 14l-5-4.8 7-.9z"/></svg>`
Common stroke icons (`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">…</svg>`):
check `M20 6 9 17l-5-5` · clock `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>` ·
arrow-right `M5 12h14M13 6l6 6-6 6` · download `M12 3v12M7 10l5 5 5-5M5 21h14` ·
lock `<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>` ·
play `<circle cx="12" cy="12" r="9"/><path d="M10 8.5v7l6-3.5z"/>` ·
plus `M12 5v14M5 12h14` · trash `M4 7h16M9 7V5h6v2M6 7l1 13h10l1-13` ·
mail `<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/>`.

Cross-link naturally: course cards → P003; "Start/Resume" → P019; enroll/buy → P029;
plan → P030; dashboard ↔ my-courses; workspace list → editor.
