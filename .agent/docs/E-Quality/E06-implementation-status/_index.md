# E06-implementation-status - Implementation Status Scorecard

**Last updated:** 2026-06-21 · **source_state:** AS_BUILT (honest)

Per-module as-built status. ✅ working · 🟡 partial · 🔴 stub/blocked.

| Module | Status | Notes |
| ------ | ------ | ----- |
| lms_base | ✅ | shared services + demo Drush |
| open_switcher | ✅ | impersonation + restore |
| lms_theme | ✅ | 16 regions, 15 skins |
| lms_course | ✅ | catalog/detail/authoring (~37 routes) |
| lms_chapter | ✅ | curriculum mid-level |
| lms_lesson | ✅ | media/SCORM/H5P/drip/preview |
| lms_progress | ✅ | enroll/gating/rollup/resume |
| lms_question | ✅ | 8 type bundles |
| lms_quiz | ✅ | grading factory + QuizPassedEvent (⚠️ no grading tests) |
| lms_certificate | ✅ | mPDF + idempotent issue |
| lms_badge | 🔴 | entities exist; **no award logic** (R-003/BLK-003) |
| lms_payment | 🟡 | host + bank_transfer ok; **no webhooks** (R-002); secrets in config (R-001) |
| lms_stripe | ✅/🟡 | capture ok; webhook missing |
| lms_paypal | 🔴 | **duplicate/dead** of lms_payment PayPal (R-004) |
| lms_order | ✅ | custom commerce; state machine |
| lms_bundle | ✅ | bundle purchase fan-out |
| lms_membership | ✅ | plans + subscriptions + entitlement |
| lms_forum | ✅ | topics/replies/moderation |
| lms_rating | ✅ | ratings + aggregate + moderation |
| lms_note | ✅ | private notes |
| lms_wishlist | ✅ | wishlist toggle |
| lms_blog | ✅ | articles + SEO |
| lms_faq | ✅ | categorised FAQ |
| lms_zoom | 🟡 | scheduling/links; attendance/recording partial |
| lms_notification | 🟡 | in-app ok; **email/push partial** (R-005) |
| lms_ai | ✅ | 3 providers + generator (secrets in config, R-001) |
| lms_mobile_api | ✅ | `/api/v1` OTP+token (~22 routes; no rate-limit) |
| lms_analytics | 🟡 | reports partial; scoping to confirm |
| lms_content_tools | ✅ | import/seed/clean + UI |

## Cross-cutting
| Concern | Status | Ref |
| ------- | ------ | --- |
| Secrets management (Key module) | 🔴 absent — secrets in config YAML | R-001 |
| Payment webhooks/reconciliation | 🔴 absent | R-002 |
| Automated test coverage | 🔴 ~0% | R-006 |
| Email/push delivery | 🟡 partial | R-005 |

**Headline:** core LMS (catalog→learn→quiz→certificate→commerce→membership→community→AI→mobile) is **functional and seeded** (40 courses/600 lessons/24 certs). Release-blocking gaps = secrets + webhooks; quality gap = tests. See [Z-Reviews](../../Z-Reviews/README.md) verdict GO-WITH-FIXES.
