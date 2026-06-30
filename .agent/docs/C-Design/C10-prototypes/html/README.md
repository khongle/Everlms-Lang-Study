# EverLMS — Interactive Prototype (v3)

A complete, responsive, multi-page HTML/CSS prototype for the **EverLMS** learning
platform. Built on one token-driven design system, with real photography and six
swappable color skins. No build step, no dependencies — open the files directly.

## Start here
- **`gallery.html`** — the showcase: live thumbnails of every page + every skin.
- **`index.html`** — the homepage (try the skin switcher, bottom-right).

> Tip: view over a local server for best results (iframes in the gallery and the
> Google Fonts link load fastest that way), e.g. `python3 -m http.server` then
> open `http://localhost:8000/gallery.html`. Opening files directly also works.

## Pages (17)
**Public:** `index` (Homepage) · `courses` (Catalog) · `course-detail` ·
`categories` · `category-detail` · `pricing` · `instructor` · `blog` ·
`article-detail` · `about` · `contact` · `login` (Login/Register).
**Learner app:** `dashboard` · `my-courses` · `learn` (Lesson player) ·
`quiz` · `my-certificates`.

These mirror the canonical [C11 page contract](../../C11-pages-components/_index.md)
(Public + App archetypes) and the [C07 sitemap](../../C07-sitemap/_index.md).

## Color skins (6)
Same homepage markup, different palette — applied via `<html data-skin="…">`:
**Indigo Premium** (default) · **Ocean Scholar** · **Midnight Electric** ·
**Emerald Focus** · **Sunset Coral** · **Rose Quartz**.
Frozen snapshots: `home-ocean.html`, `home-midnight.html`, `home-emerald.html`,
`home-sunset.html`, `home-rose.html`. The default skin + live switcher live on `index.html`.

## How it's built
```
html/
  gallery.html            showcase / index of everything (live screenshot thumbnails)
  index.html              homepage (Indigo + live skin switcher)
  home-<skin>.html        frozen homepage in each of the 5 other skins
  *.html                  the 17 pages
  assets/
    css/everlms.css       the design system (tokens → base → components → LMS)
    css/skins.css         the 6 color skins (override the color tokens only)
    js/app.js             nav, accordion, tabs, quiz, skin switcher
    img/                  74 curated real photos (course, instructor, scene)
  _screens/               full-page PNG screenshot of every page (1440px)
  _partials/              canonical header/footer + BUILD-SPEC (build-time refs)
```

### Design system
- **Tokens** (`everlms.css` `:root`): color, 8pt spacing, fluid type scale,
  radius, shadow, container, z-index. Skins re-map **only** the color group.
- **Type:** Plus Jakarta Sans (display) + Inter (body), via Google Fonts.
- **Components:** full marketing kit + LMS library — course cards, curriculum
  accordion, enroll panel, lesson player, dashboard KPIs/rings, quiz player,
  pricing tiers, reviews, certificates, instructor header, catalog filters.
- **Accessibility:** semantic landmarks, alt text, labelled controls, visible
  focus, AA-minded contrast (WCAG 2.1 AA target per C09).

### Provenance
- Design vocabulary stands on the QA-hardened **Templates** system
  (`/Templates/templates/00-base`), rethemed to Indigo Premium and extended with
  LMS-specific components.
- Photography curated from the shared catalog
  (`/Templates/_shared/01-base/00-images`) and copied into `assets/img/`.

## Status
This is forward-looking **design exploration** to develop the EverLMS product line
(C10). It is not wired to data; copy/figures are representative (see the content
bible in `_partials/BUILD-SPEC.md`). Promote chosen patterns into
[C11](../../C11-pages-components/_index.md) when implementing in `lms_theme`.
