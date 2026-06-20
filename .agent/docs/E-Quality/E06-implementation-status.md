---
documentation-mode: as-built
generated: 2026-06-20
last-updated: 2026-06-20
---

# E06 — Implementation Status

> Honest current-state scorecard for all 28 modules, 29 entities, and 36 Stories. Status reflects the as-built codebase (2026-06-20), not specification intent. Test coverage is 0% — no automated tests exist yet. Known issues are tracked in [E02-issues/](E02-issues/) (18 open issues across CRITICAL/HIGH/MEDIUM/LOW).

---

## Story Progress

| Story | Type | Owning Module(s) | Epic | Status | G1 | G2 | G3 | G4 | Coverage | Known Issues | Last Updated |
|-------|------|-----------------|------|--------|----|----|----|----|----------|-------------|--------------|
| [open_switcher](../D-Development/D03-stories/open_switcher.md) | module | open_switcher | E-01 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_base](../D-Development/D03-stories/lms_base.md) | module | lms_base | E-01 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_content_tools](../D-Development/D03-stories/lms_content_tools.md) | module | lms_content_tools | E-01 | DONE | — | — | — | — | 0% | 1 (community group double-delete) | 2026-06-20 |
| [lms_course](../D-Development/D03-stories/lms_course.md) | module | lms_course | E-02 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_chapter](../D-Development/D03-stories/lms_chapter.md) | module | lms_chapter | E-02 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_lesson](../D-Development/D03-stories/lms_lesson.md) | module | lms_lesson | E-02 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_blog](../D-Development/D03-stories/lms_blog.md) | module | lms_blog | E-02 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_faq](../D-Development/D03-stories/lms_faq.md) | module | lms_faq | E-02 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_quiz](../D-Development/D03-stories/lms_quiz.md) | module | lms_quiz | E-03 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_question](../D-Development/D03-stories/lms_question.md) | module | lms_question | E-03 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_progress](../D-Development/D03-stories/lms_progress.md) | module | lms_progress | E-04 | DONE | — | — | — | — | 0% | 2 (race-condition on cascade update — see E-10; hook_form_alter saves authenticated Role granting full_html — XSS risk) | 2026-06-20 |
| [lms_note](../D-Development/D03-stories/lms_note.md) | module | lms_note | E-04 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_order](../D-Development/D03-stories/lms_order.md) | module | lms_order | E-05 | DONE | — | — | — | — | 0% | 1 (Stripe PaymentIntent on form BUILD, not submit) | 2026-06-20 |
| [lms_payment](../D-Development/D03-stories/lms_payment.md) | module | lms_payment | E-05 | IN_PROGRESS | — | — | — | — | 0% | 2 (PayPal/ZaloPay stubs; no webhook routes anywhere) | 2026-06-20 |
| [lms_stripe](../D-Development/D03-stories/lms_stripe.md) | module | lms_stripe | E-05 | IN_PROGRESS | — | — | — | — | 0% | 1 (no webhook signature verification) | 2026-06-20 |
| [lms_paypal](../D-Development/D03-stories/lms_paypal.md) | module | lms_paypal | E-05 | STUB | — | — | — | — | 0% | 2 (wrong interface + no @PaymentGateway annotation — dead code) | 2026-06-20 |
| [lms_membership](../D-Development/D03-stories/lms_membership.md) | module | lms_membership | E-05 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_wishlist](../D-Development/D03-stories/lms_wishlist.md) | module | lms_wishlist | E-05 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_bundle](../D-Development/D03-stories/lms_bundle.md) | module | lms_bundle | E-05 | STUB | — | — | — | — | 0% | 1 (entity schema only; no checkout integration, no controllers) | 2026-06-20 |
| [lms_certificate](../D-Development/D03-stories/lms_certificate.md) | module | lms_certificate | E-06 | DONE | — | — | — | — | 0% | 1 (field_expiry_date is Drupal datetime/ISO8601; integer timestamp comparisons silently fail — see ISSUE-015) | 2026-06-20 |
| [lms_badge](../D-Development/D03-stories/lms_badge.md) | module | lms_badge | E-06 | STUB | — | — | — | — | 0% | 1 (zero award logic; lms_badge + lms_user_badge tables empty) | 2026-06-20 |
| [lms_forum](../D-Development/D03-stories/lms_forum.md) | module | lms_forum | E-07 | IN_PROGRESS | — | — | — | — | 0% | 2 (ForumController::viewTopic throws HARD DEBUG!; debug messenger left in production) | 2026-06-20 |
| [lms_rating](../D-Development/D03-stories/lms_rating.md) | module | lms_rating | E-07 | IN_PROGRESS | — | — | — | — | 0% | 1 (3 missing runtime routes: .collection, .toggle_approve, .toggle_featured) | 2026-06-20 |
| [lms_notification](../D-Development/D03-stories/lms_notification.md) | module | lms_notification | E-07 | IN_PROGRESS | — | — | — | — | 0% | 2 (email + push channels are stubs; /api/lms/notifications/unread-count uses _access: TRUE — unauthenticated access) | 2026-06-20 |
| [lms_zoom](../D-Development/D03-stories/lms_zoom.md) | module | lms_zoom | E-08 | DONE | — | — | — | — | 0% | 2 (generateSignature() wrong service id; VTT download stub) | 2026-06-20 |
| [lms_ai](../D-Development/D03-stories/lms_ai.md) | module | lms_ai | E-08 | DONE | — | — | — | — | 0% | 2 (no rollback on partial failure; Gemini key via URL param not header) | 2026-06-20 |
| [lms_analytics](../D-Development/D03-stories/lms_analytics.md) | module | lms_analytics | E-08 | DONE | — | — | — | — | 0% | 0 | 2026-06-20 |
| [lms_mobile_api](../D-Development/D03-stories/lms_mobile_api.md) | module | lms_mobile_api | E-09 | DONE | — | — | — | — | 0% | 1 (lms_quiz + lms_wishlist deps undeclared in info.yml) | 2026-06-20 |
| [hardening-security](../D-Development/D03-stories/hardening-security.md) | nfr | all lms_* | E-10 | PLANNED | — | — | — | — | — | 3 (secrets in committed config; forum debug exception; rating missing routes) | — |
| [payment-webhooks](../D-Development/D03-stories/payment-webhooks.md) | integration | lms_stripe, lms_paypal, lms_payment | E-10 | PLANNED | — | — | — | — | — | 2 (no webhook routes anywhere; no signature verification) | — |
| [progress-race-condition](../D-Development/D03-stories/progress-race-condition.md) | nfr | lms_progress | E-10 | PLANNED | — | — | — | — | — | 1 (cascade progress update lacks DB lock/transaction) | — |
| [ai-rollback-compensation](../D-Development/D03-stories/ai-rollback-compensation.md) | cross-cutting | lms_ai | E-10 | PLANNED | — | — | — | — | — | 1 (no rollback/compensation on partial AI course builder failure) | — |
| [cache-performance](../D-Development/D03-stories/cache-performance.md) | nfr | lms_course, lms_analytics, lms_progress, lms_lesson | E-10 | PLANNED | — | — | — | — | — | 0 | — |
| [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md) | nfr | all lms_* | E-10 | PLANNED | — | — | — | — | — | 0 | — |
| [ui-polish](../D-Development/D03-stories/ui-polish.md) | cross-cutting | lms_theme + all content modules | E-11 | PLANNED | — | — | — | — | — | 0 | — |
| [public-surface-seo](../D-Development/D03-stories/public-surface-seo.md) | page | lms_base, lms_course, lms_theme | E-11 | PLANNED | — | — | — | — | — | 0 | — |

**Note on gates (G1–G4):** No gate reviews have been conducted. All gate columns are `—` (not evaluated). G1–G4 will be populated when the [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md) story is executed and CI is in place.

---

## Module + Entity Data Presence

Status for all 28 modules and 29 entities (data presence from `db-schema.md`). "Data present" = table has rows per AUTO_INCREMENT evidence in the dump.

| Module | Epic | Module Status | Entities Owned | Entity Data Present | Notes |
|--------|------|--------------|---------------|---------------------|-------|
| lms_base | E-01 | DONE | — (no custom entity) | N/A | Services + hooks only |
| lms_content_tools | E-01 | DONE | — (no custom entity) | N/A | Services + Drush commands only |
| open_switcher | E-01 | DONE | — (no custom entity) | N/A | Controller + session logic only |
| lms_course | E-02 | DONE | `lms_course` | YES (~127 rows) | `lms_course__field_tags` is empty |
| lms_chapter | E-02 | DONE | `lms_chapter` | YES (~478 rows) | |
| lms_lesson | E-02 | DONE | `lms_lesson` | YES (~1772 rows) | `field_attachments` + `field_h5p_upload` junction tables are empty |
| lms_blog | E-02 | DONE | `lms_article` | YES (~9 rows) | Public frontend uses `node:blog` not lms_article |
| lms_faq | E-02 | DONE | `lms_faq` | YES (~223 rows) | |
| lms_quiz | E-03 | DONE | `lms_quiz`, `lms_quiz_result`, `lms_question_response` | YES (82 / 94 / 182 rows) | `lms_quiz__field_prerequisite_quizzes` junction is empty |
| lms_question | E-03 | DONE | `lms_question`, `lms_answer`, `lms_matching_pair` | YES (336 / 816 / 38 rows) | |
| lms_progress | E-04 | DONE | `lms_user_course`, `lms_user_lesson` | YES (174 / 37 rows) | |
| lms_note | E-04 | DONE | `lms_note` | YES (~264 rows) | |
| lms_order | E-05 | DONE | `lms_order`, `lms_order_item` | YES (76 / 90 rows) | |
| lms_payment | E-05 | IN_PROGRESS | — (no custom entity) | N/A | Plugin host only; PayPal + ZaloPay gateways are stubs |
| lms_stripe | E-05 | IN_PROGRESS | — (no custom entity) | N/A | Plugin only; no webhook verification |
| lms_paypal | E-05 | STUB | — (no custom entity) | N/A | Dead code — wrong interface, no annotation |
| lms_membership | E-05 | DONE | `lms_membership_plan`, `lms_user_membership` | YES (9 / 47 rows) | |
| lms_wishlist | E-05 | DONE | `lms_wishlist` | YES (~84 rows) | |
| lms_bundle | E-05 | STUB | `lms_course_bundle` | NO (0 rows; no AUTO_INCREMENT) | Entity schema exists; no checkout or UI |
| lms_certificate | E-06 | DONE | `lms_certificate_template`, `lms_user_certificate` | YES (35 / 51 rows) | `field_expiry_date` is Drupal `datetime` (ISO8601 string); integer timestamp comparisons silently fail — see ISSUE-015 |
| lms_badge | E-06 | STUB | `lms_badge`, `lms_user_badge` | NO (both tables empty, no AUTO_INCREMENT) | Entity schema only; zero award logic |
| lms_forum | E-07 | IN_PROGRESS | `lms_forum_topic`, `lms_forum_reply` | YES (38 / 163 rows) | `ForumController::viewTopic` throws `HARD DEBUG!` exception |
| lms_rating | E-07 | IN_PROGRESS | `lms_rating` | YES (~159 rows) | 3 runtime routes missing → RouteNotFoundException |
| lms_notification | E-07 | IN_PROGRESS | `lms_notification` | YES (~162 rows) | Email + push channels are stubs; in-app only |
| lms_zoom | E-08 | DONE | — (no custom entity) | N/A | `generateSignature()` uses wrong service id; VTT download is stub |
| lms_ai | E-08 | DONE | — (no custom entity) | N/A | No rollback on partial course builder failure; Gemini key via URL param |
| lms_analytics | E-08 | DONE | — (no custom entity) | N/A | Raw SQL queries; no GA4 despite module description |
| lms_mobile_api | E-09 | DONE | — (no custom entity) | N/A | `lms_quiz` + `lms_wishlist` deps not declared in info.yml |

### All 29 Custom Entities — Status Summary

| # | Entity | Module | Tables | Data Present | Anomalies |
|---|--------|--------|--------|-------------|-----------|
| 1 | `lms_course` | lms_course | lms_course + lms_course_field_data | YES | `field_tags` junction empty |
| 2 | `lms_chapter` | lms_chapter | lms_chapter + lms_chapter_field_data | YES | |
| 3 | `lms_lesson` | lms_lesson | lms_lesson + lms_lesson_field_data | YES | `field_attachments` + `field_h5p_upload` junctions empty |
| 4 | `lms_faq` | lms_faq | lms_faq + lms_faq_field_data | YES | |
| 5 | `lms_article` | lms_blog | lms_article + lms_article_field_data | YES | `thumbnail` uses file pattern (not image) |
| 6 | `lms_quiz` | lms_quiz | lms_quiz + lms_quiz_field_data | YES | prerequisite_quizzes junction empty |
| 7 | `lms_question` | lms_question | lms_question + lms_question_field_data | YES | 8 bundles; `type` column in both tables |
| 8 | `lms_answer` | lms_question | lms_answer (flat, not translatable) | YES | No langcode; no _field_data split |
| 9 | `lms_matching_pair` | lms_question | lms_matching_pair (flat) | YES | |
| 10 | `lms_question_response` | lms_quiz | lms_question_response (flat) | YES | |
| 11 | `lms_quiz_result` | lms_quiz | lms_quiz_result (flat) | YES | `field_result` is FK to taxonomy_term |
| 12 | `lms_user_course` | lms_progress | lms_user_course (flat) | YES | No DB UNIQUE constraint on (uid, course) |
| 13 | `lms_user_lesson` | lms_progress | lms_user_lesson (flat) | YES | |
| 14 | `lms_note` | lms_note | lms_note (flat) | YES | |
| 15 | `lms_order` | lms_order | lms_order (flat) | YES | |
| 16 | `lms_order_item` | lms_order | lms_order_item (flat) | YES | |
| 17 | `lms_membership_plan` | lms_membership | lms_membership_plan (flat) | YES | |
| 18 | `lms_user_membership` | lms_membership | lms_user_membership (flat) | YES | |
| 19 | `lms_wishlist` | lms_wishlist | lms_wishlist (flat) | YES | |
| 20 | `lms_course_bundle` | lms_bundle | lms_course_bundle (flat) | NO | Entity schema exists; no data |
| 21 | `lms_certificate_template` | lms_certificate | lms_certificate_template (flat) | YES | |
| 22 | `lms_user_certificate` | lms_certificate | lms_user_certificate (flat) | YES | `field_expiry_date` is Drupal `datetime` (ISO8601 string); integer comparisons silently fail — see ISSUE-015 |
| 23 | `lms_badge` | lms_badge | lms_badge (flat) | NO | No data; no award logic |
| 24 | `lms_user_badge` | lms_badge | lms_user_badge (flat) | NO | No data; no award logic |
| 25 | `lms_forum_topic` | lms_forum | lms_forum_topic (flat) | YES | denormalized `field_replies_count`, `field_views` |
| 26 | `lms_forum_reply` | lms_forum | lms_forum_reply (flat) | YES | |
| 27 | `lms_rating` | lms_rating | lms_rating (flat) | YES | |
| 28 | `lms_notification` | lms_notification | lms_notification (flat) | YES | |
| 29 | `lms_question_type` | lms_question | key_value (ConfigEntity) | YES | 1 ConfigEntity (not a DB table of its own) — the 8 question bundles |

**Translatable entities (use _field_data split):** lms_course, lms_chapter, lms_lesson, lms_faq, lms_article, lms_quiz, lms_question (7 total).
**Flat (non-translatable) entities:** all remaining 21 ContentEntities.
**ConfigEntity:** lms_question_type (1 — the question bundle definition).

---

## Epic Progress

| Epic | Label | Stories Total | DONE | IN_PROGRESS | STUB | PLANNED | CP4 Status |
|------|-------|:-------------:|:----:|:-----------:|:----:|:-------:|-----------|
| E-01 | Foundation | 3 | 3 | 0 | 0 | 0 | All DONE |
| E-02 | Content | 5 | 5 | 0 | 0 | 0 | All DONE |
| E-03 | Assessment | 2 | 2 | 0 | 0 | 0 | All DONE |
| E-04 | Learning Progress | 2 | 2 | 0 | 0 | 0 | All DONE |
| E-05 | Commerce | 7 | 3 | 2 | 2 | 0 | PARTIAL — PayPal/Bundle stubs; Stripe/payment incomplete |
| E-06 | Certification | 2 | 1 | 0 | 1 | 0 | PARTIAL — badge is stub |
| E-07 | Communication | 3 | 0 | 3 | 0 | 0 | All IN_PROGRESS (partial impl) |
| E-08 | Integration & AI | 3 | 3 | 0 | 0 | 0 | All DONE (with known issues) |
| E-09 | Mobile | 1 | 1 | 0 | 0 | 0 | All DONE |
| E-10 | Hardening | 6 | 0 | 0 | 0 | 6 | NOT_STARTED |
| E-11 | UI/UX Polish | 2 | 0 | 0 | 0 | 2 | NOT_STARTED |
| **Total** | | **36** | **20** | **5** | **3** | **8** | |

---

## Overall Metrics

- **Total Stories:** 36
- **DONE:** 20 (56%) — 18 module + lms_zoom + lms_analytics (counting stories with at-least-partial complete scope as DONE with notes)
- **IN_PROGRESS:** 5 (14%) — lms_payment, lms_stripe, lms_forum, lms_rating, lms_notification
- **STUB:** 3 (8%) — lms_paypal (dead code), lms_bundle (entity-only, no checkout), lms_badge (entity-only, no award logic)
- **PLANNED (not started):** 8 (22%) — all E-10 hardening + E-11 UI/UX polish stories
- **Average test coverage:** 0% — no automated tests exist (Drupal PHPUnit/Cypress/BrowserTestBase); first test suite is [quality-test-suite](../D-Development/D03-stories/quality-test-suite.md) story (PLANNED)
- **G4 findings (open issues):** ~20 total across stories (Critical: 2, High: 4, Medium: 7, Low: 7) — see cross-reference table in [D03-stories/README.md](../D-Development/D03-stories/README.md); canonical tracking deferred to E02-issues/ (planned)
- **Feature flags active:** 0

### Key Issue Summary (pre-E02 materialization)

| Severity | Issue | Story / Module |
|----------|-------|----------------|
| CRITICAL | All third-party API keys (OpenAI, Gemini, Google RSA, Stripe, Zoom, PayPal) stored in plaintext committed config YAML; `.env` with test Stripe/PayPal creds committed | hardening-security |
| CRITICAL | No webhook endpoints exist anywhere; no signature verification for Stripe/PayPal | payment-webhooks |
| HIGH | `lms_forum`: `ForumController::viewTopic()` throws `new \Exception("HARD DEBUG!")` — crashes if routed | lms_forum |
| HIGH | `lms_paypal`: wrong interface, no `@PaymentGateway` annotation — module is dead code, never discovered | lms_paypal |
| HIGH | Stripe PaymentIntent created on form BUILD (every page load), not on submit | lms_order / lms_stripe |
| HIGH | `lms_ai`: Gemini API key passed as URL query parameter (should be Authorization header) | lms_ai |
| HIGH | `lms_rating`: 3 runtime routes missing (`lms_rating.collection`, `.toggle_approve`, `.toggle_featured`) — `RouteNotFoundException` on approve/feature operations | lms_rating |
| HIGH | `lms_zoom`: `generateSignature()` uses `\Drupal::service('lms_zoom.zoom_service')` (wrong ID; correct: `lms_zoom.service`) — `ServiceNotFoundException` for Zoom hosts | lms_zoom |
| HIGH | `lms_notification`: `GET /api/lms/notifications/unread-count` uses `_access: TRUE` — accessible without authentication (defence-in-depth gap) | lms_notification |
| HIGH | `lms_progress` / `lms_mobile_api`: cascade progress update has no DB transaction/lock — race condition on concurrent `mark-complete` | progress-race-condition |
| HIGH | `lms_ai`: no rollback/compensation on partial course builder failure — orphaned courses/chapters/lessons left in DB | lms_ai |
| MEDIUM | `lms_user_certificate.field_expiry_date` declared as Drupal `datetime` (ISO8601 string storage); integer timestamp comparisons silently fail — see ISSUE-015 | lms_certificate |
| MEDIUM | `lms_mobile_api`: `lms_quiz` + `lms_wishlist` not declared as dependencies in `lms_mobile_api.info.yml` | lms_mobile_api |
| MEDIUM | `lms_progress`: `hook_form_alter` loads and saves the `authenticated` Role entity granting `full_html` — stored XSS risk; redundant config save on every lesson page load | lms_progress |
| LOW | `lms_badge`: `field_criteria` documents a JSON format but zero code evaluates it or awards badges | lms_badge |
| LOW | `lms_bundle`: `lms_course_bundle` entity schema exists but no checkout integration, controllers, or UI | lms_bundle |
| MEDIUM | `lms_notification`: email + push notification channels are stubs — only in-app channel functional | lms_notification |
| LOW | `lms_forum`: debug `messenger()->addMessage("FRESH CONTROLLER LOADED!")` left in production code | lms_forum |
| LOW | `lms_content_tools`: `community` group in ENTITY_GROUPS duplicates items from `forums` + `badges` groups — potential double-delete | lms_content_tools |
| LOW | `lms_lesson__field_attachments` + `lms_lesson__field_h5p_upload` junction tables always empty | lms_lesson |
| LOW | `lms_quiz__field_prerequisite_quizzes` junction table always empty (prerequisite quiz feature unused) | lms_quiz |
| LOW | `lms_course__field_tags` junction table always empty (course tags feature unused) | lms_course |

---

## Cross-Links

- Stories: [D03-stories/_index.md](../D-Development/D03-stories/_index.md)
- Epics: [D02-epics/README.md](../D-Development/D02-epics/README.md)
- Module specs: [C05-modules/_index.md](../C-Design/C05-modules/_index.md)
- Known issues tracker: E02-issues/
- Risk register: G02-risk-register.md (planned, layer G — not yet materialized)
