# D03 — Story Spec: Public Surface & SEO

**Story ID:** public-surface-seo
**Story type:** page
**Title:** Public Surface & SEO — auth-page branding, search, sitemap.xml, GDPR pages
**Primary source:** `C-Design/C07-sitemap.md`; draft TASK-059, TASK-060, TASK-061, TASK-062
**Owning module(s):** lms_base (or lms_theme), lms_course, plus Drupal core search
**Service(s):** Drupal Search (core), metatag (contrib), pathauto (contrib), simple_sitemap or core sitemap
**Epic:** E-11 (UI/UX Polish)
**Contributes to Feature(s):** F-007 (User Management), F-012 (Security & Performance)
**Design source(s):** `C-Design/C07-sitemap.md`, `C-Design/C11-pages-components/`
**Round:** N/A
**Driver:** Pre-launch checklist; C07 sitemap coverage; GDPR compliance (Art. 13/17/20)
**Status:** PARTIAL
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `story: public-surface-seo` — maps to draft TASK-059, TASK-060, TASK-061, TASK-062, TASK-071

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 2-3
**Splitting strategy:**
- S1: Auth page theming (`/login`, `/register`, `/forgot-password`, email verify) with Bootstrap 5.3 dark branding
- S2: Search page (`/search`), sitemap.xml, robots.txt, metatag completion, JSON-LD Course schema
- S3: GDPR pages (`/privacy`, `/terms`, `/cookies`), cookie consent banner, GA4 frontend tracking integration

## Scope (from primary source)

Four public-surface improvements required before v1.0 launch. Current implementation status is PARTIAL — some base plumbing exists (metatag configured, pathauto configured, basic_html filter format) but the specific deliverables below are incomplete or missing.

**Sub-scope:**

1. **Auth-page branding (TASK-059):**
   - `/login`, `/register`, `/forgot-password` — Drupal user-module forms; currently rendered with default Drupal styling
   - Apply Bootstrap 5.3 dark glassmorphism branding from `lms_theme`
   - Use `hook_form_alter()` in `lms_base` (or `lms_theme.theme`) to add CSS classes and wrappers
   - Email verification flow (WF-007): style the "Verify your email" page and the email template
   - **Real status:** Drupal user forms exist and function; they are not styled with lms_theme branding. Partial — branding missing.

2. **Search (TASK-060):**
   - `/search` page — use Drupal core `node_search` plugin (already configured at `search.settings.yml`, min word size 3, CJK overlap enabled)
   - Index: Course (node-based? or lms_course entity?), Blog (node:blog), FAQ (lms_faq entity), Lesson (lms_lesson)
   - Note: `lms_blog` is dual-track — public frontend reads `node:blog`; only `node:blog` nodes are indexed by core node_search. `lms_article` admin entity is NOT public.
   - Expose Drupal core search at `/search` with lms_theme-styled results template
   - Add Search API (DB backend) in v1; Solr-ready hooks in v2 (per INT-008)
   - **Real status:** Core node_search is configured. Search API is not installed. lms_course, lms_lesson, lms_faq entities are NOT indexed in core node_search (they are content entities, not nodes). V1 search covers only `node:blog` content unless Search API is added. PARTIAL — core search works for blog, custom entities not indexed.

3. **Sitemap & SEO (TASK-061):**
   - `sitemap.xml` — must include all public page URLs: course catalog, individual course pages, blog posts, FAQ, static pages
   - `robots.txt` — disallow `/manage/*`, `/admin/*`, `/api/*`; allow crawlers on public pages
   - Metatag completion: `og:image`, `og:description`, `twitter:card` for Course and Blog node pages (existing config has `title` + `description` + `canonical_url` but missing social tags)
   - JSON-LD `Course` schema for course detail pages (per C07 §SEO): `@type: Course`, `name`, `description`, `provider`, `hasCourse`, `offers`
   - **Real status:** `metatag` module configured for global/front/node/taxonomy_term/user/403/404 contexts (title, description, canonical_url). Missing: og:image, og:description, twitter:card, JSON-LD Course schema. sitemap.xml — no contrib module configured (simple_sitemap or xmlsitemap not found in config). PARTIAL.

4. **GDPR pages (TASK-062):**
   - Create static content pages: `/privacy` (Privacy Policy), `/terms` (Terms of Service), `/cookies` (Cookie Policy)
   - Use Drupal `node:page` content type (already exists per content module config)
   - Cookie consent banner: EU Cookie Compliance module or custom lms_base implementation; banner appears on first visit for non-EU users too (conservative approach)
   - Banner links to `/cookies` page; stores consent in localStorage
   - Block GA4 tracking (TASK-071) until consent granted
   - **Real status:** No privacy/terms/cookies pages exist. No cookie consent banner. Not started.

**GA4 tracking (TASK-071):**
- Add Google Analytics 4 tracking snippet to `lms_theme/templates/html.html.twig` (or use `google_analytics` contrib module)
- Gate behind cookie consent (TASK-062)
- Track 8 events: `page_view`, `course_enroll`, `lesson_complete`, `quiz_submit`, `checkout_begin`, `purchase`, `search`, `certificate_download`
- **Real status:** Not started.

## Source Documents

| Source | Why loaded |
|---|---|
| `C-Design/C07-sitemap.md` | Canonical URL map — all public pages to include in sitemap.xml |
| `C-Design/C11-pages-components/` | Page/component specs for auth pages and GDPR pages |
| `B06-integration-requirements/` | INT-008 (Search), INT-009 (Analytics/GA4) |
| `B02-technical-requirements.md` | GDPR compliance requirements |
| `the config export (config/sync/)` §6.8 | Existing search configuration |
| `the config export (config/sync/)` §6.10 | Existing metatag configuration |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Auth pages (/login, /register, /forgot-password) styled with lms_theme Bootstrap 5.3 dark | B05-acceptance-criteria/ §User Management | Acceptance |
| /search page returns courses and blog posts | B03-features/12-security-performance.md | Acceptance |
| sitemap.xml generated and accessible at /sitemap.xml | B02-technical-requirements.md §SEO | Non-Functional |
| robots.txt disallows /manage/*, /admin/*, /api/* | B02-technical-requirements.md §SEO | Non-Functional |
| JSON-LD Course schema on course detail pages | B02-technical-requirements.md §SEO | Non-Functional |
| Cookie consent banner before GA4 tracking | B02-technical-requirements.md §GDPR | Non-Functional |
| /privacy, /terms, /cookies pages exist with site-appropriate content | B02-technical-requirements.md §GDPR | Non-Functional |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_base/lms_base.module` or `web/themes/custom/lms_theme/lms_theme.theme` | Modify | `hook_form_alter()` to apply Bootstrap 5.3 classes to user login/register forms | TASK-059 | — |
| `web/themes/custom/lms_theme/templates/user/user-login-form.html.twig` | Create | Styled login form template | TASK-059 | — |
| `web/themes/custom/lms_theme/templates/user/user-register-form.html.twig` | Create | Styled register form template | TASK-059 | — |
| `web/themes/custom/lms_theme/templates/search-results.html.twig` | Modify | Course-card styled search results (also in ui-polish Story) | TASK-060 | — |
| `config/sync/simple_sitemap.*.yml` or equivalent | Create | Sitemap module configuration | TASK-061 | — |
| `web/robots.txt` | Modify | Disallow /manage/, /admin/, /api/ | TASK-061 | — |
| `config/sync/metatag.metatag_defaults.node.yml` | Modify | Add og:image, og:description, twitter:card to node context | TASK-061 | — |
| `web/modules/custom/lms_course/src/Controller/CourseDetailController.php` | Modify | Add JSON-LD Course schema to `#attached` JS settings or inline script | TASK-061 | — |
| `web/themes/custom/lms_theme/templates/html.html.twig` | Modify | Add GA4 snippet (consent-gated) + cookie consent banner | TASK-062, TASK-071 | — |
| `web/themes/custom/lms_theme/js/cookie-consent.js` | Create | Banner consent logic; localStorage; unblock GA4 on consent | TASK-062 | — |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| Story `ui-polish` — search results template shared (coordinate on template path) | Cross-Story | this D03-stories/ |
| `simple_sitemap` contrib module or Drupal core `sitemap` — must be installed | External | composer.json |
| `google_analytics` contrib or custom GA4 snippet | External | composer.json (or inline) |
| Node content type `page` must exist (already does per content module) | Intra-project | `C-Design/C05-modules/` |
| Metatag module already installed (configured in config-inventory.md §6.10) | Intra-project | config/sync/ |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| `D00-conventions/php-drupal.md` | All PHP hook implementations | PHPCS Drupal standard |
| `D00-conventions/twig-theming.md` | Auth form Twig templates | Twig lint |
| GA4 tracking only after explicit consent | html.html.twig + JS | Code review + G4 adversarial |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Smoke evidence |
|---|---|---|---|---|---|
| /login | Login form | Drupal user module | N/A (core form) | anonymous | D08 session log |
| /privacy | Privacy Policy text | node:page entity | `/node/{nid}/edit` | content_editor, administrator | D08 session log |
| /terms | Terms of Service text | node:page entity | `/node/{nid}/edit` | content_editor, administrator | D08 session log |
| /cookies | Cookie Policy text | node:page entity | `/node/{nid}/edit` | content_editor, administrator | D08 session log |
| sitemap.xml | Auto-generated URLs | sitemap module config | `/admin/config/search/simple-sitemap` | administrator | D08 session log |

**Hardcoded content exceptions:** Cookie consent banner text is hardcoded in Twig/JS — acceptable for v1.0; review for i18n pass (TASK-110/111).

## Feature Flags Introduced or Read

| Flag ID | Introduced / Read | Purpose | Rollout plan link |
|---------|-------------------|---------|-------------------|
| — | — | GA4 is consent-gated (runtime JS check), not a feature flag | — |

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-PS-001 | Functional | /login page renders with lms_theme branding (dark background, Bootstrap 5.3) | TASK-059 AC |
| T-PS-002 | Functional | /register form submits and creates user account | TASK-059 AC |
| T-PS-003 | Functional | /search?keys=drupal returns blog node results with course-card styling | TASK-060 AC |
| T-PS-004 | Functional | /sitemap.xml is accessible and contains course catalog URL | TASK-061 AC |
| T-PS-005 | Functional | robots.txt disallows /manage/ and /admin/ | TASK-061 AC |
| T-PS-006 | Functional | Course detail page HTML contains JSON-LD script tag with @type Course | TASK-061 AC |
| T-PS-007 | Functional | /privacy, /terms, /cookies pages return HTTP 200 with content | TASK-062 AC |
| T-PS-008 | Functional | Cookie consent banner appears on first visit; accepts and dismisses | TASK-062 AC |
| T-PS-009 | Functional | GA4 script NOT loaded before consent; loaded after accept | TASK-071 AC |

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PENDING | — | 0/3 | — |
| G2 Lint | PENDING | — | 0/3 | — |
| G3 Tests | PENDING | Coverage: — | 0/3 | — |
| G4 Adversarial | PENDING | 7 categories: GDPR consent bypass, GA4 pre-consent load, SEO tag injection | 0/3 | — |

## Notes

- **Search scope limitation (v1):** Drupal core node_search indexes `node` entities only. LMS content entities (`lms_course`, `lms_lesson`, `lms_faq`) are not nodes and are NOT indexed by core node_search. V1 search covers: `node:blog` posts only (real data) + potentially `node:page`. This is PARTIAL. Full entity search requires Search API module (planned, see INT-008). Document this limitation in `E02-issues/` as a known gap.
- **`lms_blog` dual-track note:** public reads `node:blog`; admin CRUD uses `lms_article` entity. Core node_search indexes `node:blog` — this is the correct indexing target for blog content.
- **GDPR page content:** The actual privacy policy, terms of service, and cookie policy text must be written by the business owner. This Story creates the pages and template; the owner fills the content via the Drupal admin UI. Placeholder text is acceptable for staging.
- **pathauto configuration:** currently only `blog` pattern is configured (`/blog/[node:title]`). Consider adding patterns for course catalog paths, but note that `lms_course` is a custom entity, not a node — pathauto requires a custom integration.
- **`og:image` metatag:** requires an image field on the course entity to be configured as the og:image token source. The `lms_course` entity has a cover image field — wire it up in the metatag config.
