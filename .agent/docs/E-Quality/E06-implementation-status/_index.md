# E06-implementation-status - Implementation Status Scorecard

**Last updated:** 2026-07-11 · **source_state:** AS_BUILT (honest)

Per-module as-built status, one row per module (29 — 28 `lms_*` + `open_switcher`); `Module` is the [E05](../E05-traceability/_index.md) join key (bidirectional: every module named in an E05 row has a row here, and every row here is named in an E05 row). `Status` uses the rebuild/migration-track enum: `as-built-backed` = working and evidenced in code, `in-progress` = partially built, `blocked` = stub/absent, blocked on a tracked risk/issue. `Built in code` = yes/no (module directory exists and is enabled, regardless of functional completeness). `Tests` = E03 def slug(s) targeting the module, or `-` if none exist yet (see [ISSUE-0004](../E02-issues/open/ISSUE-0004-zero-test-coverage.md) / R-006).

| Module | Status | Built in code | Tests | Notes |
| ------ | ------ | -------------- | ----- | ----- |
| lms_base | as-built-backed | yes | - | shared services + demo Drush |
| open_switcher | as-built-backed | yes | access-matrix (target) | impersonation + restore |
| lms_theme | as-built-backed | yes | skin-switch (target) | 16 regions, 15 skins |
| lms_course | as-built-backed | yes | catalog-listing (target) | catalog/detail/authoring (~37 routes) |
| lms_chapter | as-built-backed | yes | entity-create (target) | curriculum mid-level |
| lms_lesson | as-built-backed | yes | lesson-access (target) | media/SCORM/H5P/drip/preview |
| lms_progress | as-built-backed | yes | gating-matrix (target), progress-rollup (target) | enroll/gating/rollup/resume |
| lms_question | as-built-backed | yes | grading-per-strategy (target) | 8 type bundles |
| lms_quiz | as-built-backed | yes | grading-per-strategy (target) | grading factory + QuizPassedEvent (no grading tests yet) |
| lms_certificate | as-built-backed | yes | issuance-idempotency (target) | mPDF + idempotent issue |
| lms_badge | blocked | yes | award-trigger (target) | entities exist; **no award logic** ([ISSUE-0003](../E02-issues/open/ISSUE-0003-badge-award-logic-absent.md), R-003) |
| lms_payment | in-progress | yes | gateway-capture (target) | host + bank_transfer ok; **no webhooks** ([ISSUE-0002](../E02-issues/open/ISSUE-0002-no-payment-webhooks.md), R-002); secrets in config ([ISSUE-0001](../E02-issues/open/ISSUE-0001-secrets-in-config-yaml.md), R-001) |
| lms_stripe | in-progress | yes | gateway-capture (target) | capture ok; webhook missing (R-002) |
| lms_paypal | blocked | yes | - | **duplicate/dead** of lms_payment PayPal ([ISSUE-0006](../E02-issues/open/ISSUE-0006-duplicate-paypal-module.md), R-004) |
| lms_order | as-built-backed | yes | order-state (target) | custom commerce; state machine |
| lms_bundle | as-built-backed | yes | bundle-fanout (target) | bundle purchase fan-out |
| lms_membership | as-built-backed | yes | entitlement (target) | plans + subscriptions + entitlement |
| lms_forum | as-built-backed | yes | topic-reply-perms (target) | topics/replies/moderation |
| lms_rating | as-built-backed | yes | rating-aggregate (target) | ratings + aggregate + moderation |
| lms_note | as-built-backed | yes | note-privacy (target) | private notes |
| lms_wishlist | as-built-backed | yes | note-privacy (target) | wishlist toggle |
| lms_blog | as-built-backed | yes | publish-visibility (target) | articles + SEO |
| lms_faq | as-built-backed | yes | publish-visibility (target) | categorised FAQ |
| lms_zoom | in-progress | yes | schedule-meeting (target) | scheduling/links; attendance/recording partial ([ISSUE-0009](../E02-issues/open/ISSUE-0009-zoom-depth-partial.md)) |
| lms_notification | in-progress | yes | - | in-app ok; **email/push partial** ([ISSUE-0005](../E02-issues/open/ISSUE-0005-email-push-stubbed.md), R-005) |
| lms_ai | as-built-backed | yes | provider-routing (target) | 3 providers + generator; secrets in config ([ISSUE-0001](../E02-issues/open/ISSUE-0001-secrets-in-config-yaml.md), R-001) |
| lms_mobile_api | in-progress | yes | api-auth-parity (target) | `/api/v1` OTP+token (~22 routes); no rate-limit ([ISSUE-0008](../E02-issues/open/ISSUE-0008-no-api-rate-limiting.md), R-007) |
| lms_analytics | in-progress | yes | report-scope (target) | reports partial; scoping to confirm ([ISSUE-0007](../E02-issues/open/ISSUE-0007-analytics-partial.md)) |
| lms_content_tools | as-built-backed | yes | import-count (target) | import/seed/clean + UI |

## Cross-cutting concerns

These are not modules and intentionally carry no table row (kept out of the pipe-table above so they are not mistaken for module rows); each links its tracking issue/risk instead.

- **Secrets management (Key module):** absent — secrets committed in config YAML. Tracked as [ISSUE-0001](../E02-issues/open/ISSUE-0001-secrets-in-config-yaml.md) / R-001.
- **Payment webhooks/reconciliation:** absent. Tracked as [ISSUE-0002](../E02-issues/open/ISSUE-0002-no-payment-webhooks.md) / R-002.
- **Automated test coverage:** ~0% across all custom modules. Tracked as [ISSUE-0004](../E02-issues/open/ISSUE-0004-zero-test-coverage.md) / R-006.
- **Email/push delivery:** partial (in-app channel only is reliable). Tracked as [ISSUE-0005](../E02-issues/open/ISSUE-0005-email-push-stubbed.md) / R-005.

**Headline:** core LMS (catalog→learn→quiz→certificate→commerce→membership→community→AI→mobile) is **functional and seeded** (40 courses/600 lessons/24 certs). Release-blocking gaps = secrets + webhooks; quality gap = tests. See [Z-Reviews](../../Z-Reviews/README.md) verdict GO-WITH-FIXES.
