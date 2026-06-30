# EverLMS Prototype — BUILD SPEC (read fully before building any page)

A self-contained, multi-page HTML prototype for **EverLMS** (Drupal 11 LMS).
Goal: **beautiful, professional, modern, impressive** marketing + product pages
sharing ONE design system. Default skin = **Indigo Premium**.

## Golden rules
1. **Reuse the design system.** All styling comes from `assets/css/everlms.css`
   (+ `assets/css/skins.css`). Do NOT invent new colors/hex — use the class
   vocabulary below and CSS variables (`var(--brand)` etc.). Tiny page-specific
   tweaks may go in a small `<style>` block in `<head>`, using tokens only.
2. **Copy the header & footer partials verbatim** from this folder
   (`header-public.html` / `header-app.html` / `footer.html`). Set
   `aria-current="page"` on the active nav link. Keep brand/topbar identical.
3. **Relative paths only.** CSS `assets/css/…`, JS `assets/js/app.js`, images
   `assets/img/…`. Pages live at the prototype root next to `assets/`.
4. **Real photography** from `assets/img/` (manifest below). Never hotlink the
   web. Pick topically-matching images.
5. **Responsive + accessible.** Semantic landmarks (`<header><main id="main"><footer>`),
   alt text on every `<img>`, labels on inputs, WCAG AA contrast (the tokens
   already satisfy this).
6. **No emojis, no dashes-as-bullets in UI.** Use SVG icons from the library below.
7. End every page with `<script src="assets/js/app.js"></script>`.

## Exact page skeleton (use for every page)
```html
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{Page Title} · EverLMS</title>
<meta name="description" content="{one-line description}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/everlms.css">
<link rel="stylesheet" href="assets/css/skins.css">
</head>
<body>
  <!-- paste header-public.html OR header-app.html here -->
  <main id="main">
     … page sections …
  </main>
  <!-- paste footer.html here -->
  <script src="assets/js/app.js"></script>
</body>
</html>
```

## Page set, filenames & which header
| File | Page | Header | Notes |
|---|---|---|---|
| index.html | Homepage | public + skin switcher | flagship (built as reference) |
| courses.html | Course catalog | public | `.catalog` filters + `.ccard` grid + `.pager` |
| course-detail.html | Course detail | public | course **#1** (React/Next.js); `.enroll` sticky + `.curri` + reviews |
| categories.html | Categories | public | category cards grid + featured |
| pricing.html | Pricing | public | `.plans` 3 tiers + compare table + FAQ |
| instructor.html | Instructor profile | public | **Sarah Chen**; `.insthead` + her courses |
| blog.html | Blog / News | public | featured post + `.ccard`-style article grid |
| about.html | About | public | mission, stats, team `.member`, story |
| contact.html | Contact | public | `.form` + `.icon-list` + `.mapbox` |
| dashboard.html | Learner dashboard | app | `.appshell` sidebar + `.kpis` + continue-learning + `.ring` |
| my-courses.html | My courses | app | `.appshell` + tabs (In progress/Completed/Wishlist) + progress |
| learn.html | Lesson player | none (own `.player__topbar`) | course #1 lesson; `.player` video + rail + tabs |
| quiz.html | Quiz take | none (own `.player__topbar`) | `.quizwrap` question player + dots + timer |
| my-certificates.html | My certificates | app | `.appshell` + `.certcard` grid |

Cross-link pages naturally (course cards → course-detail.html; "Start" →
learn.html; dashboard ↔ my-courses; etc.).

## CONTENT BIBLE (keep identical across pages so it feels like one real product)

**Brand:** EverLMS — tagline "Learn without limits".
**Platform stats:** 1,200+ courses · 350+ expert instructors · 480,000+ learners · 4.8 average rating · 92% completion.
**Logged-in learner:** Alex Morgan (avatar-03.jpg), member since 2024, 7-day streak.

**Categories (8):** Web Development · AI & Machine Learning · Data & Analytics ·
Design & UX · Business · Marketing · Photography & Video · Personal Development.

**Canonical courses** (use these exact titles/instructors/prices/ratings everywhere):
| # | Title | Category | Instructor | Price | Old | Rating (reviews) | Hours · Lessons · Level | Image |
|---|---|---|---|---|---|---|---|---|
| 1 | Complete Modern React & Next.js Bootcamp | Web Development | Sarah Chen | $89.99 | $199.99 | 4.9 (12,480) | 52h · 420 · All levels | course-web-01 |
| 2 | Machine Learning A–Z: Hands-On Python & AI | AI & ML | Dr. Marcus Reed | $94.99 | $189.99 | 4.8 (9,210) | 44h · 380 · Intermediate | course-ai-01 |
| 3 | Data Analytics with SQL, Python & Power BI | Data & Analytics | Priya Sharma | $79.99 | $159.99 | 4.7 (6,540) | 38h · 300 · Beginner | course-data-01 |
| 4 | UI/UX Design Masterclass: Figma to Product | Design & UX | Elena Rossi | $84.99 | $169.99 | 4.9 (8,300) | 30h · 260 · All levels | course-design-01 |
| 5 | The Complete Digital Marketing Course | Marketing | James Carter | $69.99 | $149.99 | 4.6 (15,120) | 41h · 350 · Beginner | course-biz-01 |
| 6 | Python for Everybody: Zero to Hero | Web Development | David Kim | Free | — | 4.8 (22,050) | 28h · 210 · Beginner | course-edu-01 |
| 7 | IELTS & Business English Mastery | Personal Development | Amara Okafor | $59.99 | $119.99 | 4.7 (4,210) | 22h · 180 · All levels | course-lang-01 |
| 8 | Cybersecurity & Ethical Hacking Fundamentals | Data & Analytics | Tomás Alvarez | $99.99 | $199.99 | 4.8 (5,870) | 35h · 290 · Intermediate | course-web-02 |

More thumbnails available: course-ai-0{2,3,4}, course-data-0{2,3,4}, course-design-0{2,3,4}, course-biz-0{2,3,4}, course-edu-0{2,3,4}, course-lang-0{2,3,4}, course-k12-0{1..4}, course-web-0{3,4}.

**Instructors:** Sarah Chen (instructor-01) · Priya Sharma (instructor-02) ·
Elena Rossi (instructor-03) · Dr. Marcus Reed (instructor-m-01) ·
James Carter (instructor-m-02) · David Kim (instructor-m-03) ·
Tomás Alvarez (instructor-m-04) · Amara Okafor (instructor-d-01).
Sarah Chen bio: ex-Google senior engineer, 12 yrs, 8 courses, 84,000 students, 4.9 rating.

**Course #1 curriculum** (use on course-detail.html + learn.html rail):
- Section 1 — Getting Started (4 lessons · 38m): Welcome & setup, How the course works, Dev environment, Your first component
- Section 2 — React Fundamentals (8 lessons · 2h 15m): JSX deep dive, Props & components, **State with useState**, Handling events, Conditional rendering, Lists & keys, Forms, Project: Todo app
- Section 3 — Hooks in Depth (7 lessons · 3h 10m): useEffect, useRef, useMemo & useCallback, Custom hooks, useContext, useReducer, Project: Weather dashboard
- Section 4 — Next.js & Full-Stack (9 lessons · 4h 05m): App Router, Server components, Data fetching, Route handlers, Auth, Database with Prisma, Deployment, Performance, Capstone project
(Current lesson for learn.html = "State with useState" in Section 2; mark earlier lessons done, later locked/upcoming.)

**Pricing plans:** Free ($0 — 1 course track, community, mobile) · Pro ($29/mo, featured — unlimited courses, certificates, offline, 1-on-1 mentoring monthly) · Teams ($99/mo — everything in Pro, 20 seats, analytics dashboard, SSO, priority support).

**Sample reviews:** use avatar-0{1,2,4,5,6,7} portraits; names like "Linh Tran", "Michael O.", "Sofia Garcia", "Daniel W.", "Aisha N.", "Kenji Watanabe"; 4–5 stars; short authentic blurbs.

## IMAGE MANIFEST (assets/img/)
- **Course thumbnails** (16:10 ok): `course-web-01..04`, `course-ai-01..04`,
  `course-data-01..04`, `course-design-01..04`, `course-biz-01..04`,
  `course-edu-01..04`, `course-lang-01..04`, `course-k12-01..04` (.jpg)
- **Instructor portraits:** `instructor-01..08`, `instructor-m-01..06`, `instructor-d-01..06`
- **Learner avatars** (reviews/small): `avatar-01..10`
- **Scenes** (about/lifestyle/classroom/hero bg): `scene-team-01..04`, `scene-life-01..04`, `scene-room-01..04`
All are real photos, ~16:10–1:1. Use `object-fit:cover` (already in component CSS).

## CLASS VOCABULARY (everything you need is already styled)
- Layout: `.container` (`--narrow`,`--prose`), `.section` (`--soft`,`--mist`,`--ink`,`--brand`,`--tight`), `.grid`(`--2/3/4/auto`), `.split`, `.frow`/`.frows`, `.steps`, `.stats`, `.kpis`, `.catalog`(+`.filter`), `.appshell`(+`.appsidebar`,`.appnav`,`.appmain`), `.article`(if needed).
- Type/marketing: `.eyebrow`, `.lede`, `.sec-head`, `.grad-text`, `.quote`, `.ctaband`, `.logos`/`.logoslot`.
- Buttons: `.btn` + `--primary`(gold CTA) `--brand` `--ghost` `--white` `--ink` `--sm` `--lg` `--block`.
- Pills: `.badge`(+`--accent/success/warning/error/info/solid/ink/dot`), `.chip`(+`.is-active`), `.chip-row`.
- Cards: `.card`(+`--soft`,`--pad-lg`), `.card__icon`, `.feature`, `.member`, `.tcard`(testimonial)+`.stars`.
- LMS: `.ccard`(course card — see structure below), `.enroll`(sticky buy card), `.curri`(curriculum accordion), `.review-summary`+`.review-bar`+`.rev`, `.insthead`, `.kpi`, `.contcard`(continue learning), `.ring`(progress ring), `.progress`(bar)+`.progress-label`, `.plans`/`.plan`, `.certcard`, `.quiz*`/`.opt`, `.player*`, `.noteitem`, `.alert`.
- Nav/util: `.pager`, `.tabs`+`[data-tabs]`/`[data-tab]`/`[data-pane]`, `.avstack`, `.faq`(native `<details>`), `.icon-list`, `.mapbox`, `.videobox`, `.crumb`, `.pagehero`.

### Course card markup (use everywhere a course appears)
```html
<article class="ccard">
  <div class="ccard__media">
    <img src="assets/img/course-web-01.jpg" alt="Complete Modern React & Next.js Bootcamp">
    <span class="ccard__cat badge badge--ink">Web Development</span>
    <button class="ccard__bookmark" aria-label="Save"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z" stroke-linejoin="round"/></svg></button>
    <span class="ccard__price"><small>$199.99</small>$89.99</span>
  </div>
  <div class="ccard__body">
    <div class="ccard__meta"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round"/></svg>52h</span><span>420 lessons</span><span>All levels</span></div>
    <h3 class="ccard__title"><a href="course-detail.html">Complete Modern React &amp; Next.js Bootcamp</a></h3>
    <div class="ccard__author"><img src="assets/img/instructor-01.jpg" alt=""> Sarah Chen</div>
    <div class="ccard__foot">
      <span class="ccard__rating"><span class="rating-num">4.9</span><span class="stars"><svg viewBox="0 0 24 24"><path d="m12 2 3 6.5 7 .9-5 4.8 1.3 7L12 18l-6.6 3.2L6.7 14l-5-4.8 7-.9z"/></svg></span><small>(12,480)</small></span>
      <span class="badge badge--accent">Bestseller</span>
    </div>
  </div>
</article>
```

## ICON LIBRARY (inline SVG, 24×24, stroke=currentColor unless noted)
Use `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">…</svg>`.
- clock: `<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>`
- play (fill): `fill="currentColor" stroke="none"` `<path d="M7 4v16l13-8z"/>`
- check: `<path d="M20 6 9 17l-5-5"/>`
- star (fill): `fill="currentColor" stroke="none"` `<path d="m12 2 3 6.5 7 .9-5 4.8 1.3 7L12 18l-6.6 3.2L6.7 14l-5-4.8 7-.9z"/>`
- users: `<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>`
- award/cert: `<circle cx="12" cy="8" r="6"/><path d="M8.2 13 7 22l5-3 5 3-1.2-9"/>`
- book: `<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z"/>`
- video: `<rect x="2" y="6" width="14" height="12" rx="2"/><path d="m22 8-6 4 6 4z"/>`
- chart: `<path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/>`
- lock: `<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>`
- bolt (fill): `fill="currentColor" stroke="none"` `<path d="M13 2 4 14h6l-1 8 9-12h-6z"/>`
- globe: `<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18"/>`
- shield: `<path d="M12 3 5 6v6c0 4 3 7 7 9 4-2 7-5 7-9V6z"/>`
- infinity: `<path d="M6 8a4 4 0 1 0 0 8c3 0 4-4 6-4s3 4 6 4a4 4 0 1 0 0-8c-3 0-4 4-6 4S9 8 6 8z"/>`
- arrow-right: `<path d="M5 12h14M13 6l6 6-6 6"/>`
- chevron-down: `<path d="m6 9 6 6 6-6"/>`
- download: `<path d="M12 3v12M7 10l5 5 5-5M5 21h14"/>`
- pencil: `<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>`

## Look & feel targets
- Generous whitespace; alternate section backgrounds (white ↔ `--soft`/`--mist`) for rhythm.
- One bold gradient/`section--brand` or `section--ink` "moment" per marketing page.
- Hero: eyebrow → big H1 (use `.grad-text` on a keyword) → `.lede` → `.btn-row` → trust row (`.avstack` + rating + "480K+ learners"). Pair with a real photo or product mock + floating stat chips.
- Course grids: 3–4 across, hover lift built-in. Always show rating + price + instructor.
- Keep copy crisp, professional, benefit-led. Real-sounding, never lorem ipsum.
