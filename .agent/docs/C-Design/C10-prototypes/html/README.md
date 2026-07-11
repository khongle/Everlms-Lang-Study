# EverLMS — C10 HTML prototype (v4, per-role)

A self-contained, multi-page HTML prototype for **EverLMS** (Drupal 11 LMS). One shared
design system, one injected navigation shell, **51 pages** organised **by role**.

## How to open
Open any page in a browser (works over `file://`, no server or build step). Start at
[`anonymous/P001-homepage.html`](anonymous/P001-homepage.html) and follow the menus — the
header, My Account mega menu and footer are injected by `_shared/js/shell.js`, so every
page cross-links correctly.

## Folder = role (filed once, lowest accessible role)
Each page lives in the folder of the **lowest role** that can reach it, filed exactly once
(per the C10 per-role gate). Navigation to everything a role can access is provided by the
menu, which links across folders — so a role never needs duplicate copies.

| Folder | Role | Pages |
| ------ | ---- | ----- |
| [`anonymous/`](anonymous/) | Public (anonymous) | P001–P016 (marketing, catalog, forum, auth) |
| [`student/`](student/) | Student / authenticated learner | P017–P030 (dashboard, learning, checkout) |
| [`instructor/`](instructor/) | Instructor | P031–P034, P044, P045 (course/quiz authoring, AI, reports) |
| [`content-editor/`](content-editor/) | Content Editor | P037, P039–P043 (content, blog, certificates, ratings, tools) |
| [`manager/`](manager/) | Manager | P035, P036, P038 (users, orders, pricing) |
| [`administrator/`](administrator/) | Administrator | P046–P051 (AI/payment/zoom settings, analytics, broadcast, switch-user) |

`_shared/` holds the design system and is **not** a role folder.

## Navigation system (per the brief)
Two shells, injected from the page's `data-*` attributes on `#site-header`:

- **Front shell** (`data-shell="front"`, public pages) — the main menu (Courses mega,
  Categories, Pricing, Blog, Forum, About, Contact) with search + **Log in / Sign up** at
  top-right. When logged in, top-right becomes the notification bell + **My Account**.
- **App / backend shell** (`data-shell="app" data-role="…"`, everything behind login) — a
  role-scoped **backend main menu**, plus at top-right a **Home icon** (back to the public
  homepage), the notification bell, and the **My Account mega menu**.

**My Account mega menu** is the role-filtered launcher: it opens on click and lists the
App / Manage / Admin / Account destinations that role can reach (Student sees Learning;
staff see their Workspace; Administrator also sees Admin). This realises the brief's flow:
*log in → My Account (top-right) → mega menu (manage + app) → click a link → land in the
backend with the backend main menu and a Home icon back to the front site.*

**No sidebar navigation** anywhere. The only vertical rails are page **business** content:
the curriculum accordion on the course detail (P003), the lesson rail on the player (P019),
and the question navigator on the quiz (P020).

## Design system (`_shared/`)
- `css/everlms.css` — token-driven design system (colors, type, spacing, components:
  cards, course cards, curriculum, player, quiz, pricing, workspace tables, mega menu…).
- `css/skins.css` — 6 color skins (Indigo default · Ocean · Midnight · Emerald · Sunset ·
  Rose), switchable live on the homepage.
- `js/shell.js` — renders the header/footer/menus from the page registry (single source of
  truth for every link and role).
- `js/app.js` — page behaviors (tabs, curriculum accordion, quiz selection, skin switcher,
  mobile nav, bookmark).
- `img/` — 74 real photographs (course thumbnails, instructor/learner portraits, scenes).
- `BUILD-SPEC.md` — the authoring contract every page follows.

## Breakpoints
Bootstrap 5 exactly, desktop-first (`max-width`): 575.98 · 767.98 · 991.98 · 1199.98 ·
1399.98 px; container widths 540 / 720 / 960 / 1140 / 1320.

## Coverage
See [`../coverage-checklist.md`](../coverage-checklist.md) — 51 pages × 5 canonical states
(Normal · Empty · Access denied · Validation error · Loading).
