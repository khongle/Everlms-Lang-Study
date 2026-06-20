# C09 — Theme Architecture & Design System

> **Purpose:** Documents the design token set, theme system, breakpoints, component primitives, 15 switchable homepage skins, and mobile design system for EverLMS. All facts are grounded in the live codebase (`web/themes/custom/`, `app/lib/core/theme/`). Status: **as-built** unless noted.

---

## Theme System Overview

EverLMS uses a **2-custom-theme + 1-admin-theme model** on the default Drupal 11 web profile.

| Theme | Machine Name | Status | Route Groups | Purpose |
|-------|-------------|--------|--------------|---------|
| **Frontend / Workspace** | `lms_theme` | DEFAULT | Public `/*`, Workspace `/workspace/*`, App `/app/*`, Manage `/manage/*` | All end-user pages: branded, dark glassmorphism, 15 homepage skins |
| **Classic Alternate** | `lms_classic` | Secondary / inactive | Same route groups | Stripped-down alternate skin sharing identical design tokens; lacks multi-skin switcher |
| **Admin** | `claro` | Stock Drupal | Admin `/admin/*` | Drupal administration UI — NOT customized |

> Config source: `config/sync/system.theme.yml` — `default: lms_theme`, `admin: claro`.

### Theme Negotiation

EverLMS does **not** implement a custom theme negotiator class. Theme selection is the Drupal default: `lms_theme` serves all non-admin routes. The `/admin/*` routes fall through to `claro`. There is no per-route-prefix negotiation separating frontend from workspace — `lms_theme` handles both via body-class switching (`is-workspace`, `is-front-page`) applied in `lms_theme_preprocess_html()`.

```
URL Request
    |
    v
[Drupal Default Theme Negotiator]
    |
    +-- /admin/*   → claro  (admin theme)
    +-- /*         → lms_theme  (all other routes)
         |
         [lms_theme_preprocess_html()]
              adds body class: layout-{style}, is-front-page, is-workspace
```

---

## `lms_theme` — Primary Theme (as-built)

**File:** `web/themes/custom/lms_theme/lms_theme.info.yml`

### Identity

| Property | Value |
|----------|-------|
| Machine name | `lms_theme` |
| Human name | EverLMS Theme |
| Base theme | `false` (no base theme — suppresses classy/base via `libraries-override`) |
| Core requirement | `^11` (Drupal 11) |
| Default library | `lms_theme/global` |
| Build tooling | Dart Sass ^1.77.0; `npm run build` (compressed, no source map), `npm run watch` |
| `node_modules/` | Present (build artifacts ready) |

### Regions (16)

`header`, `primary_menu`, `secondary_menu`, `hero`, `breadcrumb`, `highlighted`, `content_top`, `content`, `content_bottom`, `sidebar_first`, `sidebar_second`, `footer_first`, `footer_second`, `footer_third`, `footer_fourth`, `footer_bottom`

---

## Shared Design System (as-built)

Both `lms_theme` and `lms_classic` share **byte-identical** `_variables.scss` design tokens. The Flutter mobile app mirrors the same values in `lib/core/theme/app_colors.dart`.

### Color Tokens

#### Backgrounds
| SCSS Variable | Value | Usage |
|--------------|-------|-------|
| `$color-bg-primary` | `#0d1117` | Deep dark page background (GitHub-dark) |
| `$color-bg-secondary` | `#161b22` | Slightly lighter surface |
| `$color-bg-tertiary` | `#21262d` | Elevated surfaces, inputs |
| `$color-bg-card` | `rgba(22,27,34,0.85)` | Semi-transparent card background |
| `$color-bg-glass` | `rgba(255,255,255,0.05)` | Glassmorphism surface layer |

#### Text
| SCSS Variable | Value | Usage |
|--------------|-------|-------|
| `$color-text-primary` | `#e6edf3` | Near-white body text |
| `$color-text-secondary` | `#8b949e` | Secondary / supporting text |
| `$color-text-muted` | `#6e7681` | Placeholder, de-emphasized text |
| `$color-text-link` | `#58a6ff` | Hyperlinks (GitHub blue) |

#### Accent / Brand
| SCSS Variable | Value | Usage |
|--------------|-------|-------|
| `$color-accent-primary` | `#667eea` | Brand primary — buttons, active states, CTAs |
| `$color-accent-secondary` | `#764ba2` | Brand secondary — gradient partner |
| `$color-accent-success` | `#43e97b` | Pass / complete / positive states |
| `$color-accent-danger` | `#f5576c` | Error / fail states |
| `$color-accent-warning` | `#fee140` | Warning / pending states |
| `$color-accent-info` | `#4facfe` | Informational callouts |

#### Gradients
| SCSS Variable | Definition | Usage |
|--------------|-----------|-------|
| `$gradient-primary` | `135deg, #667eea → #764ba2` | Brand gradient — hero sections, CTAs |
| `$gradient-secondary` | `135deg, #f093fb → #f5576c` | Secondary accent gradient |
| `$gradient-success` | `135deg, #43e97b → #38f9d7` | Success/completion indicators |
| `$gradient-info` | `135deg, #4facfe → #00f2fe` | Info banners |
| `$gradient-warning` | `135deg, #fa709a → #fee140` | Warning states |

#### Glassmorphism Mixin
```scss
// Applied to cards, panels, modal surfaces on glassmorphism skin
background: rgba(255, 255, 255, 0.05);
border: 1px solid rgba(255, 255, 255, 0.1);
backdrop-filter: blur(10px);
border-radius: 0.75rem;
```

### Typography Tokens

| Property | Value |
|----------|-------|
| Font family | `Inter` (Google Fonts, weights 300–800, CDN) |
| Base size | `15px` (0.9375rem) |
| Scale — h1 | `36px` |
| Scale — h2 | `28px` |
| Scale — h3 | `22px` |
| Scale — h4 | `18px` |
| Scale — sm | `13px` |
| Scale — xs | `12px` |
| Line-height (body) | `1.6` |
| Line-height (headings) | `1.2` |

### Breakpoints

Bootstrap 5 compatible — all themes, web and mobile.

| Name | Min Width | Usage |
|------|-----------|-------|
| `sm` | 576px | Mobile landscape |
| `md` | 768px | Tablet |
| `lg` | 992px | Desktop |
| `xl` | 1200px | Large desktop |
| `xxl` | 1400px | Extra-large / wide |

Container max-width: **1320px**

### Z-Index Scale

| Layer | Value |
|-------|-------|
| Navbar | 1030 |
| Dropdown | 1040 |
| Modal | 1050 |
| Tooltip | 1060 |

### Layout Constants

| Property | Value |
|----------|-------|
| Navbar height | 64px |
| Navbar background | `rgba(13,17,23,0.95)` (dark, translucent) |
| Card border-radius | 12px (mobile) / 0.75rem (web mixin) |

---

## External CDN Dependencies (as-built)

All external libraries are loaded from CDN — there is **no local bundle** for Bootstrap or Font Awesome. This creates a hard runtime dependency on CDN availability.

| Library | Version | CDN Source | Type |
|---------|---------|------------|------|
| Bootstrap | 5.3.3 | `cdn.jsdelivr.net` | CSS + JS bundle |
| Inter font | weights 300–800 | Google Fonts | CSS font |
| Font Awesome | 6.5.1 | `cdnjs.cloudflare.com` | CSS (all.min.css) |
| AOS (Animate on Scroll) | 2.3.4 | `unpkg.com` | CSS + JS |

> **Risk:** CDN failure = broken styles/icons site-wide. No SRI hash pinning verified. No fallback. Consider hosting locally or using a bundler.

Skin-specific font dependencies loaded on demand (Google Fonts CDN):

| Skin | Extra Fonts |
|------|-------------|
| `academy` | Playfair Display + Source Serif 4 |
| `editorial` | DM Sans + DM Serif Display |
| `nature` | Lora + Nunito Sans |
| `neon` | JetBrains Mono + Inter |
| `luxury` | Cormorant Garamond + Inter |
| `creative` | Space Grotesk + Inter |
| `retro` | Playfair Display + Special Elite |
| Others | Inter only (global CDN load) |

---

## 15 Homepage Skins (as-built)

`lms_theme` implements **15 switchable homepage visual styles**. Selection is persisted in Drupal theme settings (`homepage_style`), switched via the Admin UI at `/admin/appearance/settings/lms_theme`. Default: `glassmorphism`.

### Skin Switcher Mechanism

1. `theme-settings.php` exposes a `homepage_style` select field (15 options) in the theme settings form.
2. `lms_theme_preprocess_html()` reads the setting and:
   - Adds `layout-{style}` body class (e.g., `layout-glassmorphism`).
   - Conditionally attaches the matching homepage-style library (`lms_theme/homepage_minimalist`, etc.).
   - Injects `lms_theme/light_mode` for all light-base skins on non-front / non-workspace pages.
3. The homepage route renders the matching Twig template from `templates/home/styles/`.

### Skin Inventory

| # | Key | Label | Dark/Light | CSS File | Twig Template | Extra JS |
|---|-----|-------|-----------|----------|---------------|----------|
| 1 | `glassmorphism` | Style 1: Modern Glassmorphism (Dark) — **default** | Always dark | `homepage.css` | `style-1-glass.html.twig` | `homepage.js` + AOS |
| 2 | `minimalist` | Style 2: Minimalist Clean (Light) | Light content | `minimalist.css` | `style-2-minimal.html.twig` | — |
| 3 | `playful` | Style 3: Playful & Colorful | Light content | `playful.css` | `style-playful.html.twig` | — |
| 4 | `corporate` | Style 4: Corporate B2B | Light content | `corporate.css` | `style-4-corporate.html.twig` | `corporate.js` |
| 5 | `government` | Style 5: Government | Light content | `government.css` | `style-government.html.twig` | — |
| 6 | `academy` | Style 6: Academy | Light content | `academy.css` | `style-academy.html.twig` | — |
| 7 | `editorial` | Style 7: Editorial | Light content | `editorial.css` | `style-editorial.html.twig` | — |
| 8 | `nature` | Style 8: Nature (Organic & Green) | Light content | `nature.css` | `style-nature.html.twig` | — |
| 9 | `neon` | Style 9: Neon (Dark & Electric) | Always dark | `neon.css` | `style-neon.html.twig` | — |
| 10 | `luxury` | Style 10: Luxury (Premium Dark Gold) | Always dark | `luxury.css` | `style-luxury.html.twig` | — |
| 11 | `creative` | Style 11: Creative (Expressive Studio) | Always dark | `creative.css` | `style-creative.html.twig` | — |
| 12 | `gradient` | Style 12: Gradient (Vibrant Light) | Light content | `gradient.css` | `style-gradient.html.twig` | — |
| 13 | `community` | Style 13: Community (Friendly & Social) | Light content | `community.css` | `style-community.html.twig` | `community-homepage.js` |
| 14 | `retro` | Style 14: Retro (Vintage Paper) | Light content | `retro.css` | `style-retro.html.twig` | — |
| 15 | `medical` | Style 15: Medical (Clean & Clinical) | Light content | `medical.css` | `style-medical.html.twig` | — |

**Always-dark skins (no `light_mode` library):** glassmorphism, neon, luxury, creative.

**Light-content skins (inject `light_mode` library on non-front/non-workspace pages):** minimalist, playful, corporate, government, academy, editorial, nature, gradient, community, retro, medical.

### Homepage Section Toggles

Admin also exposes per-section checkbox toggles (also in `theme-settings.php`):

`hero`, `stats`, `continue_learning`, `featured`, `categories`, `introductions`, `bundles`, `testimonials`, `faqs`, `news`, `membership`, `training_process`, `cta_guest`

---

## SCSS / CSS Architecture

### Source Structure (`lms_theme/scss/`)

```
_variables.scss          — Design tokens (colors, typography, spacing, z-index, breakpoints)
base/
  reset.scss             — Resets
  typography.scss        — Type scale
components/
  buttons.scss
  cards.scss
  navbar.scss
  footer.scss
  forms.scss
  badges.scss
  alerts.scss
  dialog.scss
  introduction.scss
  mega-menu.scss
layouts/
  page.scss              — Page shell layout
  grid.scss              — Grid system
pages/
  homepage.scss          — Glassmorphism homepage
  management.scss        — /manage/* and /workspace/* pages
  learn.scss             — /app/learn/* lesson view
  catalog.scss           — Course catalog
  course-detail.scss     — Single course view
  categories.scss        — Category browse
  auth.scss              — Login / register
  profile.scss           — User profile / dashboard
  forum.scss             — Forum pages
  pricing.scss           — Pricing / membership
  dashboard.scss         — Learner dashboard
  my-courses.scss        — My courses list
  certificates.scss      — Certificate views
  quiz-results.scss      — Quiz result pages
  about.scss             — About page
  contact.scss           — Contact page
```

### Compiled CSS Output (`css/`)

`main.css`, `site-branding.css`, `pricing.css`, `admin-fixes.css`, `homepage.css`, `light-mode.css`, `minimalist.css`, `playful.css`, `corporate.css`, `government.css`, `academy.css`, `editorial.css`, `nature.css`, `neon.css`, `luxury.css`, `creative.css`, `gradient.css`, `community.css`, `retro.css`, `medical.css`

---

## JavaScript Behaviors

| File | Library Attachment | Purpose |
|------|--------------------|---------|
| `js/main.js` | `lms_theme/global` (defer) | Global interactions — navbar, dropdowns, Bootstrap init |
| `js/homepage.js` | `lms_theme/homepage` (defer) | Glassmorphism homepage — AOS init, scroll effects |
| `js/corporate.js` | `lms_theme/homepage_corporate` | Corporate homepage interactions |
| `js/community-homepage.js` | `lms_theme/homepage_community` (defer) | Community homepage behaviors |

---

## Theme Preprocess Hooks (as-built)

### `lms_theme_preprocess_page()`

Injects into all page templates:
- `current_route` — active route name
- Site variables: name, tagline, logo, footer contact, social links (Facebook, YouTube, LinkedIn, TikTok, Zalo), App Store URL, Google Play URL
- Impersonation banner from `open_switcher` module
- User auth state, `current_user_name`, `current_user_picture`
- Notification bell block (`lms_notification_bell_block`)
- `is_manager` flag (roles: administrator / manager / instructor)

### `lms_theme_preprocess_html()`

- Reads `homepage_style` setting (default: `glassmorphism`)
- Adds `layout-{style}` body class
- Adds `is-workspace` for `/workspace` URLs; `is-front-page` for front page
- Attaches the matching homepage-style library
- Injects `lms_theme/light_mode` for light-base skins on non-dark pages

### `lms_theme_preprocess_user()`

Injects into user templates:
- `courses_enrolled` (count)
- `courses_completed` (count)
- `certificates_count`
- `quiz_average` (%)
- `recent_quizzes` (last 5)
- `active_courses` (last 3 with progress + thumbnail)

### Bootstrap Form Class Injection

`form-control`, `form-check-input`, `btn`, `form-select` added to Drupal Form API `input`/`select`/`textarea` elements via preprocess hooks.

### Custom Theme Hook

`lms_course_manager_edit` → `form/lms-course-manager-edit-form.html.twig`

---

## Template Structure (as-built)

```
web/themes/custom/lms_theme/templates/
├── layout/
│   └── html.html.twig              — Full HTML document wrapper
├── page/
│   ├── page.html.twig              — Main page layout
│   ├── page--user--login.html.twig
│   ├── page--user--password.html.twig
│   └── page--user--register.html.twig
├── navigation/
│   ├── breadcrumb.html.twig
│   ├── menu-local-task.html.twig
│   ├── menu-local-tasks.html.twig
│   └── pager.html.twig
├── content/
│   ├── comment.html.twig
│   └── node--slideshow.html.twig
├── user/
│   ├── user.html.twig
│   ├── user--full.html.twig        — Learner dashboard template
│   ├── user-login-form.html.twig
│   └── user-register-form.html.twig
├── form/
│   └── lms-course-manager-edit-form.html.twig
└── home/styles/                    — 15 homepage skin templates
    ├── style-1-glass.html.twig     (glassmorphism)
    ├── style-2-minimal.html.twig   (minimalist)
    ├── style-4-corporate.html.twig (corporate)
    ├── style-academy.html.twig
    ├── style-community.html.twig
    ├── style-creative.html.twig
    ├── style-editorial.html.twig
    ├── style-government.html.twig
    ├── style-gradient.html.twig
    ├── style-luxury.html.twig
    ├── style-medical.html.twig
    ├── style-nature.html.twig
    ├── style-neon.html.twig
    ├── style-playful.html.twig
    └── style-retro.html.twig
```

### Theme Suggestion Overrides

`lms_theme_theme_suggestions_user_alter()` forces the `user--full` template on:
- `entity.user.canonical`
- `lms_course.profile`

---

## Footer Configuration (via theme-settings.php)

The footer is fully configurable from the Admin UI under theme settings:

- Address, phone, email, working hours
- Copyright text
- Google Map embed URL
- App Store URL (iOS), Google Play URL (Android)
- Social links: Facebook, YouTube, LinkedIn, TikTok, Zalo

---

## `lms_classic` — Alternate Theme (as-built)

**File:** `web/themes/custom/lms_classic/lms_classic.info.yml`

| Property | Value |
|----------|-------|
| Machine name | `lms_classic` |
| Human name | EverLMS Classic Prestige |
| Base theme | `false` |
| Core requirement | `^11` |
| `node_modules/` | **NOT present** (build has not been run) |

### Differences from `lms_theme`

| Aspect | `lms_theme` | `lms_classic` |
|--------|-------------|--------------|
| Homepage skins | 15 (full switcher) | 1 (single `homepage` library only) |
| `light_mode` library | Yes | No |
| `site-branding.css` in global | Yes | No |
| `theme-settings.php` style switcher | Yes | No (no `preprocess_html` with dynamic switching) |
| Skin Twig templates | 15 under `home/styles/` | 1 flat `lms-homepage.html.twig` |
| `node--slideshow.html.twig` | Yes | No |
| Footer/social settings injection | Yes (preprocess_page) | No (omitted) |
| SCSS pages coverage | Full (about, contact, quiz-results, etc.) | Shorter subset |
| `lms_course.profile` theme suggestion | Yes | No |
| Design tokens (`_variables.scss`) | Canonical | Byte-identical copy |
| Build artifacts | Ready | Not built |

`lms_classic` is effectively a **frozen earlier variant** — same dark glassmorphism tokens, fewer features, no skin switcher. `lms_theme` is the authoritative live theme.

---

## Admin Theme — `claro` (as-built)

| Property | Value |
|----------|-------|
| Machine name | `claro` |
| Route groups | Admin `/admin/*` |
| Customization | **NONE** — stock Drupal 11 admin theme |
| Config | `config/sync/system.theme.yml` → `admin: claro` |

Do not customize Claro. All end-user management screens live under `/manage/*` (served by `lms_theme`).

---

## Flutter Mobile App Design System (as-built)

Source: `app/lib/core/theme/app_colors.dart`, `app/lib/core/theme/app_theme.dart`

### Token Alignment (Web ↔ Mobile)

The Flutter app uses **exactly the same color values** as the web SCSS tokens:

| Web SCSS Token | Value | Flutter `AppColors` Key |
|---------------|-------|------------------------|
| `$color-bg-primary` | `#0D1117` | `bgPrimary` |
| `$color-bg-secondary` | `#161B22` | `bgSecondary` |
| `$color-bg-tertiary` | `#21262D` | `bgTertiary` |
| `$color-accent-primary` | `#667EEA` | `accentPrimary` |
| `$color-accent-secondary` | `#764BA2` | `accentSecondary` |
| `$color-accent-success` | `#43e97b` | Same hex |
| `$color-accent-danger` | `#f5576c` | Same hex |
| `$color-accent-warning` | `#fee140` | Same hex |
| `$color-accent-info` | `#4facfe` | Same hex |

### Theme Modes

| Property | Value |
|----------|-------|
| Default mode | Dark |
| Toggle | User preference via `settings` screen |
| Persistence | SharedPreferences |
| Implementation | `ThemeProvider` (Riverpod), two `ThemeData` instances (`light` / `dark`) |
| Material version | Material 3 (`useMaterial3: true`) |

### Typography (Mobile)

| Property | Value |
|----------|-------|
| Font family | Inter (via `google_fonts: ^6.2.1` — runtime CDN fetch) |
| Additional bundled fonts | Roboto (300/400/500/700), Poppins (Light/Regular/Medium/Bold/Black), Lato Light, Libre Franklin, Atkinson Hyperlegible Mono |
| Scale | 36/28/22/18/16/15/14/13/12px — mirrors web scale |
| Weights | 300–800 |

### Component Tokens (Mobile)

| Component | Dark style | Light style |
|-----------|-----------|------------|
| Cards | 12px radius, zero elevation, border `rgba(255,255,255,0.10)` | Border `rgba(0,0,0,0.08)` |
| Buttons | 48px min height, 8px radius, `accentPrimary` fill | Same |
| Inputs | `bgTertiary` fill, 8px radius, `accentPrimary` focus border | Same |
| Bottom nav | `bgSecondary` background, `accentPrimary` selected icon | — |

---

## Cross-Theme Token Architecture

```
[Shared Brand Tokens — _variables.scss]
  $color-bg-primary:   #0d1117
  $color-accent-primary: #667eea
  $color-accent-secondary: #764ba2
  Typography: Inter, 15px base
       |
  +----+----+
  |         |
[lms_theme] [lms_classic]   [Flutter app — app_colors.dart]
  15 skins    1 skin          Dart constants mirror same hex values
  CSS         CSS             ThemeData light + dark
```

---

## Responsive Layout (Shared — Web)

| Breakpoint | Name | Sidebar / Nav behavior |
|-----------|------|----------------------|
| < 576px | (xs) | Mobile — hamburger menu, stacked layout |
| 576px | sm | Mobile landscape |
| 768px | md | Tablet — partial nav |
| 992px | lg | Desktop — full nav, sidebar visible |
| 1200px | xl | Large desktop |
| 1400px | xxl | Wide — max-width container (1320px) |

Workspace pages (`/workspace/*`, `/manage/*`) add `is-workspace` body class. These share the same `management.scss` page styles regardless of skin.

---

## Related Documents

- Theme negotiation and route group definitions: [C01 — Architecture Decisions](C01-architecture-decision.md)
- Module providing `open_switcher` impersonation banner: [C05 — Modules](C05-modules/)
- Mobile API backend serving the Flutter app: [C05 — Modules / lms_mobile_api](C05-modules/lms_mobile_api.md)
- Entity and field definitions used in preprocess hooks: [C04 — Entities](C04-entities/)
- Notification bell block (rendered in header): module `lms_notification` → [C05 — Modules / lms_notification](C05-modules/lms_notification.md)
