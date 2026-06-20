# D03 — Story Spec: UI Polish

**Story ID:** ui-polish
**Story type:** cross-cutting
**Title:** UI Polish — 14-item UX improvement pass across all public and workspace pages
**Primary source:** E-11 chaos results (draft E-Quality layer); G-UI-POLISH group (draft D02-group-specs/11-G-UI-POLISH.md)
**Owning module(s):** lms_theme (primary), lms_course, lms_quiz, lms_progress, lms_payment, lms_certificate, lms_forum, lms_notification
**Service(s):** HomepageController (lms_course), CourseCatalogService (lms_course), DashboardController (lms_progress)
**Epic:** E-11 (UI/UX Polish)
**Contributes to Feature(s):** F-001 (Course Management), F-002 (Assessment), F-003 (Progress), F-004 (Commerce), F-015 (Customization & Branding)
**Design source(s):** `C-Design/C05-modules/lms_theme` theme specs; `C-Design/C11-pages-components/`; `C-Design/C09-skins.md`
**Round:** N/A
**Driver:** E-11 chaos results; G-UI-POLISH Epic; pre-v1.0 polish gate
**Status:** SPEC
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `story: ui-polish` — maps to draft TASK-033..048

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 4+
**Splitting strategy:**
- S1 (Phase A — core pages): Homepage (U-071), Course detail (U-072), Lesson view (U-073), Quiz UX (U-074) — all lms_theme Twig + SCSS
- S2 (Phase B — user flows): Dashboard (U-075), Checkout flow UI (U-077), Certificate detail page (U-078)
- S3 (Phase C — secondary pages): Management CRUD polish (U-076), Forum UI (U-080), Notification center (U-081), Profile page (U-082)
- S4 (Phase D — cross-cutting): Mobile responsive audit (U-079, touches all pages), Search results UX (U-083), Error/empty states (U-084)

Each session must end at G1-build pass (Twig compiles, SCSS compiles, no PHP errors).

**Prerequisite:** Epic E-10 (G-HARDENING) must be fully complete before this Story opens.

## Scope (from primary source)

Final UX polish pass across the 14 UI items from the G-UI-POLISH group. All work is in `lms_theme` Twig templates, SCSS, and JS unless a specific controller change is required for data availability. Bootstrap 5.3.3 is the CSS framework (loaded via CDN). Design language: dark glassmorphism (#0d1117 background, #667eea/#764ba2 gradient, Inter font family).

**The 14 UI items:**

1. **Homepage redesign (U-071):** hero section (headline, subheadline, "Browse Courses" CTA); featured courses carousel (4–6 cards with enrolled indicator for logged-in users); categories grid with icons and course count; stats bar (total learners, courses, instructors from AnalyticsService). Lighthouse mobile ≥ 80.

2. **Course detail page polish (U-072):** course header with cover image, title, rating stars, enrollment count; structured outline (chapters/lessons expandable); instructor bio card; reviews/ratings section; sticky "Enroll" CTA sidebar. Lighthouse mobile ≥ 80.

3. **Lesson view UX (U-073):** responsive video player with progress bookmark; notes sidebar (lms_note); "Mark complete" button; next/prev lesson navigation; keyboard shortcuts (space = play/pause, ← → seek).

4. **Quiz taking UX (U-074):** question progress indicator (3/10 answered); single-page or per-question navigation toggle; answer review mode after submission; timer display for timed quizzes; essay answer word count indicator.

5. **Dashboard redesign (U-075):** in-progress courses with % bar and "Continue" button; streak calendar widget; recent certificates; quick links (My Courses, Wishlist, Forum, Profile). Mobile-first layout.

6. **Management pages CRUD polish (U-076):** consistent action buttons (Edit/Delete/View) across all `/manage/*` tables; bulk selection with confirmation modal; inline status badges; pagination. Already marked DONE in draft (TASK-027) — verify and document if complete or re-assess.

7. **Checkout flow UI (U-077):** 3-step stepper (Cart → Payment → Confirmation); order summary sidebar; payment method selector (BankTransfer card, Stripe card form, PayPal button, ZaloPay QR); loading spinner during PaymentIntent creation; success/failure redirects.

8. **Certificate detail page (U-078):** public verification page (`/certificate/{uuid}/verify`); certificate preview with PDF download button; shareable link copy-button; QR code linking to verification URL.

9. **Mobile responsive audit (U-079):** test all pages at 375px (iPhone SE), 768px (iPad), 1440px (desktop). Fix breakpoint issues in all Twig templates. Document any layout regressions found.

10. **Forum UI (U-080):** thread list with post count, last-activity, category badge; reply composer with CKEditor 5 basic_html; pagination; report post button. Fix known live bug: `ForumController::viewTopic` throws `"HARD DEBUG!"` (this bug fix is also in Story `hardening-security` — coordinate).

11. **Notification center UI (U-081):** `/notifications` page listing all notifications with type icon, timestamp, read/unread state; "Mark all read" bulk action; filter by type; bell icon badge count in header (lms_notification_bell_block already placed in both theme blocks).

12. **Profile page UI (U-082):** `/user/{uid}/profile` — avatar upload, bio edit, social links; enrolled courses count; certificates count; forum posts count; badge showcase row.

13. **Search results UX (U-083):** `/search/node` with course card layout; filter sidebar (category, level, language, price); sort by relevance/newest/rating; highlighted search terms.

14. **Error/empty state pages (U-084):** consistent 404/403/500 Twig templates (dark glassmorphism, lms_theme branding); empty state components for: no courses enrolled, no notifications, empty cart, no forum posts. Use Twig macro `{% macro empty_state(icon, title, cta_label, cta_url) %}` for reuse.

**Real implementation status (as-built):**
- All 14 items are `SPEC` (not implemented) except U-076 management CRUD which is `DONE` per draft (verify).
- `lms_theme` has 15 switchable homepage skins configured (`homepage_style: minimalist` in current config). The "redesign" must work within the skin system.
- The `lms_notification_bell_block` is already placed in both `lms_theme` and `lms_classic` block configs. The notification center page itself (`/notifications`) is not yet implemented.
- Drupal's built-in search (`/search/node`) is configured (node_search plugin). No Search API or Solr is set up yet. U-083 works with the built-in search first.

## Source Documents

| Source | Why loaded |
|---|---|
| `C-Design/C09-skins.md` | Homepage skin system — 15 switchable skins; redesign must be skin-compatible |
| `C-Design/C11-pages-components/_index.md` | Page/component inventory |
| `C-Design/C05-modules/lms_notification.md` | Notification entity + bell block |
| `C-Design/C05-modules/lms_forum.md` | ForumController bug + thread UI design |
| `C-Design/C05-modules/lms_certificate.md` | Certificate verification page |
| `C-Design/C05-modules/lms_payment.md` | Checkout flow + gateway selector |
| `B05-acceptance-criteria/` | Feature-level ACs for each affected feature |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| All pages render correctly at 375px, 768px, 1440px | B02-technical-requirements.md §Responsive | Non-Functional |
| Lighthouse mobile performance ≥ 80 for Homepage, Course detail, Lesson view | B02-technical-requirements.md §Performance | Non-Functional |
| Empty states and error pages consistent (lms_theme branding) | B02-technical-requirements.md §UX | Non-Functional |
| Forum UI: ForumController debug exception removed | E02-issues/ (bug) | Acceptance |
| Checkout flow: 3-step stepper with payment method selector | B03-features/04-order-membership.md | Acceptance |
| Certificate page: shareable verification link + QR code | B03-features/06-certification-badges.md | Acceptance |
| Dashboard: enrolled courses with % progress + Continue button | B03-features/03-learning-progress.md | Acceptance |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/themes/custom/lms_theme/templates/page--homepage.html.twig` | Modify | Hero + featured courses + categories + stats bar | TASK-033 | — |
| `web/themes/custom/lms_theme/scss/pages/_homepage.scss` | Modify | Homepage SCSS — glassmorphism hero, cards, stats | TASK-033 | — |
| `web/themes/custom/lms_theme/js/homepage.js` | Modify | Courses carousel, stats counter animation | TASK-033 | — |
| `web/modules/custom/lms_course/src/Controller/HomepageController.php` | Modify | Expose featured courses + stats data to template | TASK-033 | — |
| `web/themes/custom/lms_theme/templates/page--course-detail.html.twig` | Modify | Course header, outline, instructor card, enroll sidebar | TASK-034 | — |
| `web/themes/custom/lms_theme/templates/lesson-view.html.twig` | Modify | Video player, notes sidebar, mark-complete, nav | TASK-035 | — |
| `web/themes/custom/lms_theme/templates/quiz-take.html.twig` | Modify | Progress indicator, navigation, essay word count | TASK-036 | — |
| `web/themes/custom/lms_theme/templates/page--dashboard.html.twig` | Modify | In-progress cards, streak calendar, quick links | TASK-037 | — |
| `web/themes/custom/lms_theme/templates/checkout.html.twig` | Modify | 3-step stepper, order summary, payment selector | TASK-026 | — |
| `web/themes/custom/lms_theme/templates/certificate-verify.html.twig` | Modify | Public verification + PDF download + QR code | TASK-038 | — |
| `web/themes/custom/lms_theme/templates/forum-topic.html.twig` | Modify | Thread list, reply composer, pagination | TASK-039 | — |
| `web/themes/custom/lms_theme/templates/notifications.html.twig` | Create | Notification center page | TASK-040 | — |
| `web/themes/custom/lms_theme/templates/profile.html.twig` | Modify | Avatar, bio, stats, badge showcase | TASK-041 | — |
| `web/themes/custom/lms_theme/templates/search-results.html.twig` | Modify | Course card layout, filter sidebar, sort | TASK-042 | — |
| `web/themes/custom/lms_theme/templates/error-page.html.twig` | Create | 404/403/500 — dark glassmorphism branding | TASK-043 | — |
| `web/themes/custom/lms_theme/templates/macros/empty-state.html.twig` | Create | Reusable empty state macro | TASK-043 | — |
| `web/themes/custom/lms_theme/scss/_variables.scss` | Modify | Ensure #0d1117, #667eea, #764ba2 are canonical variables | TASK-033 | — |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| Epic E-10 (G-HARDENING) fully DONE — required before this Story opens | Cross-Epic | D01-dev-overview.md §Build Order |
| Story `cache-performance` — catalog page N+1 fixes affect template rendering | Cross-Epic | this D03-stories/ |
| `lms_certificate` — certificate PDF download button requires mPDF integration complete | Intra-project | `C-Design/C05-modules/lms_certificate.md` |
| Checkout workflow (draft TASK-001) — U-030 must be DONE for checkout UI | Intra-project | D01-dev-overview.md §Commerce |
| `AnalyticsService` stats data — U-046 (done) available for homepage stats bar | Intra-project | `C-Design/C05-modules/lms_analytics.md` |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| `D00-conventions/twig-theming.md` | All `.html.twig` files | Twig lint clean |
| `D00-conventions/php-drupal.md` | Controller changes | PHPCS Drupal standard |
| Bootstrap 5.3.3 utility classes only (no Bootstrap 4 deprecated classes) | All Twig + SCSS | G2 lint check |
| Dark glassmorphism design tokens: `#0d1117`, `#667eea`, `#764ba2`, Inter font | All SCSS | Design review |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Smoke evidence |
|---|---|---|---|---|---|
| Homepage stats bar | Total learners, courses, instructors | AnalyticsService (live query) | N/A (computed) | All users | D08 session log |
| Course detail header | Course title, cover image, description | lms_course entity | `/manage/courses/{id}/edit` | instructor, manager | D08 session log |
| Certificate verification page | Certificate holder name, course, date | lms_user_certificate entity | N/A (auto-generated) | All (public) | D08 session log |
| 15 homepage skins | Homepage layout variant | `lms_theme.settings:homepage_style` | `/admin/appearance/settings/lms_theme` | administrator | D08 session log |

**Hardcoded content exceptions:** Error page text (404 message) is hardcoded in Twig — acceptable per design convention; no ADR required.

## Feature Flags Introduced or Read

| Flag ID | Introduced / Read | Purpose | Rollout plan link |
|---------|-------------------|---------|-------------------|
| — | — | No feature flags for UI polish | — |

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-UI-001 | Functional | Homepage renders hero, courses carousel, stats at 375/768/1440px | TASK-033 AC |
| T-UI-002 | Functional | Lighthouse mobile performance ≥ 80 on homepage | TASK-033 AC |
| T-UI-003 | Functional | Course detail: enroll CTA visible; outline collapses/expands | TASK-034 AC |
| T-UI-004 | Functional | Lesson view: mark complete triggers progress update; next lesson nav works | TASK-035 AC |
| T-UI-005 | Functional | Quiz: progress indicator updates per answered question; review mode visible after submit | TASK-036 AC |
| T-UI-006 | Functional | Dashboard: enrolled courses show % bar + Continue button | TASK-037 AC |
| T-UI-007 | Functional | Checkout 3-step stepper advances through Cart → Payment → Confirmation | TASK-026 AC |
| T-UI-008 | Functional | Certificate verify page: PDF download works; QR code displays | TASK-038 AC |
| T-UI-009 | Functional | Forum thread list renders; reply composer loads; ForumController no debug exception | TASK-039 AC |
| T-UI-010 | Functional | Notification center: mark-all-read updates unread count | TASK-040 AC |
| T-UI-011 | Functional | Profile page: avatar upload saves; stats counts correct | TASK-041 AC |
| T-UI-012 | Functional | Search results: filter sidebar applies category filter | TASK-042 AC |
| T-UI-013 | Functional | 404 page renders with lms_theme branding (not Drupal default) | TASK-043 AC |
| T-UI-014 | Functional | Empty state macro renders with icon, title, CTA for "no courses enrolled" | TASK-043 AC |
| T-UI-015 | Visual | Mobile responsive audit at 375px: no horizontal scroll on any page | TASK-028 AC |

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PENDING | — | 0/3 | — |
| G2 Lint | PENDING | PHPCS + Twig lint + SCSS lint | 0/3 | — |
| G3 Tests | PENDING | Coverage: — | 0/3 | — |
| G4 Adversarial | PENDING | 7 categories: XSS in Twig output, mobile regressions, Lighthouse | 0/3 | — |

## Notes

- **Skin system compatibility:** `lms_theme` has 15 switchable homepage skins. The homepage redesign must either (a) work as a new skin or (b) update the `minimalist` skin (current default per config) without breaking others. Recommend option (b) for v1.0.
- **Forum debug bug:** `ForumController::viewTopic()` throws `"HARD DEBUG!"` live bug. This is also addressed in Story `hardening-security`. Whichever Story is implemented first removes the bug. Cross-reference in both session logs.
- **Management CRUD polish (U-076):** Draft marks this as DONE (TASK-027). Before opening S3, verify by viewing `/manage/courses`, `/manage/quizzes`, etc. If already polished, skip U-076 and document evidence in the session log.
- **Checkout UI dependency:** Story `payment-webhooks` must be complete (Stripe PaymentIntent on submit fix) before the checkout flow UI is polished — the UI must reflect the correct submit-time creation flow.
- **`lms_user_certificate.field_expiry_date` is varchar not int** (known bug). The certificate detail page must handle this gracefully; do not crash if value is non-numeric. Log a bug in `E02-issues/` if not already filed.
- All images in `lms_theme` are served as WebP via the `image_convert_avif` image style effect (confusing name, correct output format). Twig templates should use the `wide` or `large` image style as appropriate; do not bypass image styles.
