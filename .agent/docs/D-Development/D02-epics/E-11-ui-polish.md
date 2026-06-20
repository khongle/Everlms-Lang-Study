# D02 — Epic Spec: UI/UX Polish

**Epic ID:** E-11
**Features contributed to:** F-021 (Homepage UX), F-022 (Course/Lesson UX), F-023 (Quiz UX), F-024 (Dashboard UX), F-025 (Checkout UI), F-026 (Certificate Page), F-027 (Forum/Notification UX), F-028 (Profile Page), F-029 (Search UX), F-030 (Mobile Responsive), F-031 (Error/Empty States)
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA
**Services affected:** lms_theme (primary), lms_classic (secondary), lms_course, lms_quiz, lms_progress, lms_payment, lms_certificate, lms_forum, lms_notification, lms_content_tools
**Dependencies:** E-10 (Hardening) — MUST be fully DONE before this Epic starts; also requires E-05 (Commerce) for checkout flow
**Estimated Duration:** 2 weeks
**Status:** NOT_STARTED (PLANNED)

## Purpose

Final design pass across all user-facing pages and flows. The backend is feature-complete after E-10; this Epic refines Twig templates, SCSS, and JavaScript for production quality. Covers: homepage redesign (15-skin switcher preserved), course/lesson/quiz view polish, student dashboard, checkout flow UI, certificate detail page, forum and notification center UX, profile page, search results, mobile responsive audit at three breakpoints, and consistent error/empty states. Lighthouse score >= 80 is a gate for key public pages. No backend logic changes — template and theme layer only (unless a bug is discovered during audit, in which case log in E02-issues and address in a separate story).

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Service(s) | Feature/AC refs | Dependencies | Est. Effort | Priority |
|---|---|---|---|---|---|---|---|---|---|
| S-11-01 | Homepage redesign (final iteration, all 15 skins) | page | [C09-skins.md](../../C-Design/C09-skins.md) | lms_theme | lms_theme | F-021 | None | 3d | Should |
| S-11-02 | Course detail page polish | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme, lms_course | lms_theme | F-022 | None | 2d | Should |
| S-11-03 | Lesson view UX improvements | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme, lms_progress | lms_theme | F-022 | None | 2d | Should |
| S-11-04 | Quiz taking UX | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme, lms_quiz | lms_theme | F-023 | None | 2d | Should |
| S-11-05 | Dashboard redesign | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme, lms_progress | lms_theme | F-024 | None | 2d | Should |
| S-11-06 | Management pages CRUD polish | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme | lms_theme | F-024 | None | 1d | Could |
| S-11-07 | Checkout flow UI | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme, lms_payment, lms_order | lms_theme | F-025 | E-05 Stories DONE | 2d | Should |
| S-11-08 | Certificate detail page | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme, lms_certificate | lms_theme | F-026 | None | 1d | Should |
| S-11-09 | Forum UI | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme, lms_forum | lms_theme | F-027 | E-07 S-07-05 DONE | 1d | Should |
| S-11-10 | Notification center UI | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme, lms_notification | lms_theme | F-027 | None | 1d | Should |
| S-11-11 | Profile page UI | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme | lms_theme | F-028 | None | 1d | Should |
| S-11-12 | Search results UX | page | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme, lms_content_tools | lms_theme | F-029 | None | 1d | Could |
| S-11-13 | Mobile responsive audit (375px / 768px / 1440px) | cross-cutting | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme | lms_theme | F-030 | All S-11-01–S-11-12 DONE | 3d | Must |
| S-11-14 | Error and empty-state pages | cross-cutting | [C11-pages-components/](../../C-Design/C11-pages-components/) | lms_theme | lms_theme | F-031 | All S-11-01–S-11-12 DONE | 1d | Must |

## Build Order Within Epic

**Phase A — Core pages (parallel):**
1. S-11-01 — Homepage redesign
2. S-11-02 — Course detail page
3. S-11-03 — Lesson view
4. S-11-04 — Quiz UX

**Phase B — User flows (parallel, after Phase A complete):**
5. S-11-05 — Dashboard
6. S-11-07 — Checkout flow (requires E-05 Commerce DONE)
7. S-11-08 — Certificate page

**Phase C — Secondary pages (parallel, can overlap with Phase B):**
8. S-11-06 — Management CRUD polish
9. S-11-09 — Forum UI (requires E-07 S-07-05 forum cleanup DONE)
10. S-11-10 — Notification center
11. S-11-11 — Profile page
12. S-11-12 — Search results

**Phase D — Cross-cutting (after all Phase A–C DONE):**
13. S-11-13 — Mobile responsive audit (touches every page above)
14. S-11-14 — Error/empty states (standardizes across all pages)

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-1101 | All 15 homepage skins render without layout errors; active skin applied via `open_switcher` | F-021 | S-11-01 |
| AC-1102 | Lighthouse performance score >= 80 for homepage (`/`), course detail (`/course/{id}`), lesson view (`/learn/{course}/{lesson}`) | F-021, F-022 | S-11-01, S-11-02, S-11-03 |
| AC-1103 | Course detail page renders instructor, curriculum, rating, enroll CTA without JavaScript errors | F-022 | S-11-02 |
| AC-1104 | Lesson view: video resumes from `field_video_position`; note panel visible; comment section loads with AJAX | F-022 | S-11-03 |
| AC-1105 | Quiz timer countdown shown; question navigation bar; result page shows score badge | F-023 | S-11-04 |
| AC-1106 | Dashboard shows in-progress courses with progress bars; "Continue" deep-links to next incomplete lesson | F-024 | S-11-05 |
| AC-1107 | Checkout page renders product summary, payment method selector, Stripe Elements iframe (when Stripe configured) | F-025 | S-11-07 |
| AC-1108 | Certificate detail page shows `field_certificate_number`, QR/verification link, download PDF button | F-026 | S-11-08 |
| AC-1109 | Forum index lists topics with reply count and last-reply date; topic view shows threaded replies | F-027 | S-11-09 |
| AC-1110 | Notification center filters by type; bell icon shows unread count badge | F-027 | S-11-10 |
| AC-1111 | Profile page shows avatar, bio, enrolled courses count, certificates count; edit profile links to user edit form | F-028 | S-11-11 |
| AC-1112 | Search results show course cards with thumbnail, price, rating at 375px, 768px, 1440px without overflow | F-029, F-030 | S-11-12, S-11-13 |
| AC-1113 | Mobile audit passes at 375px breakpoint: no horizontal scroll on any page; tap targets >= 44x44px | F-030 | S-11-13 |
| AC-1114 | Empty states: no-courses enrolled, no-results search, no-notifications — each shows illustration + CTA | F-031 | S-11-14 |
| AC-1115 | 404 and 500 error pages consistent with theme; include "Go to homepage" link | F-031 | S-11-14 |

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Feature Flag |
|--------|------------|--------------------------|-------------|
| INT-1101 | lms_theme skin switcher → open_switcher module (homepage skin selection) | S-11-01 | — |
| INT-1102 | Lesson view Twig → AJAX comments library + `lms_progress` comment form alter | S-11-03 | — |
| INT-1103 | Checkout Twig → Stripe Elements JS (external CDN `https://js.stripe.com/v3/`) | S-11-07 | FF_FEAT_STRIPE_UI |
| INT-1104 | Certificate page → mPDF download via `lms_certificate.generator` | S-11-08 | — |
| INT-1105 | Notification bell block → `lms_notification_bell_block` plugin + `/api/lms/notifications/unread-count` polling | S-11-10 | — |

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 60% per Story (theme layer; primarily visual/functional testing)
- **Cross-Story integration tests:** (1) Full student journey: homepage → course detail → enroll → lesson → quiz → certificate — verify each page loads without JS errors (S-11-01 → S-11-08 chain). (2) Mobile audit catches regressions in Phase A–C pages (S-11-13 covers all)
- **Cross-Story Integration Test Owner:** S-11-13 — owner of `E03-test-definitions/cross-story/E-11-ui-polish/` (because mobile audit exercises every page)
- **Acceptance tests:** AC-1101 through AC-1115

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_STRIPE_UI | S-11-07 | Enable Stripe Elements JS in checkout UI (requires live Stripe keys) | High | 0%→10%→100% (coordinate with E-05 Stripe key rotation from test to live) |

## CP4 Readiness Criteria

- [ ] Every Story in this Epic has status: DONE
- [ ] G1-G4 passed for every Story
- [ ] Cross-Story integration tests within Epic passing (S-11-13 mobile audit gate)
- [ ] D07 code-map delta complete for all Twig templates, SCSS, JS files touched
- [ ] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/)
- [ ] Feature flags registered for FF_FEAT_STRIPE_UI
- [ ] Lighthouse scores >= 80 documented for homepage, course detail, lesson view
- [ ] Mobile audit sign-off at 375px / 768px / 1440px
- [ ] Open G4 Medium/Low issues logged in E02-issues/
- [ ] Every Story that touched code/config/UI lists relevant `D00-conventions/` files in its D03 or D04 evidence
