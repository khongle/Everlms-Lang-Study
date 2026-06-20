# D03 — Story Index

**Layer:** D-Development / D03-stories
**Purpose:** Machine-readable story inventory for navigation, dashboards, and cross-reference tooling.
**Last updated:** 2026-06-20
**Total stories:** 36

---

## All Stories

| id | file | title | story_type | epic | owning_modules | status |
|---|---|---|---|---|---|---|
| open_switcher | [open_switcher.md](open_switcher.md) | Administrator user-impersonation utility | module | E-01 | open_switcher | DONE |
| lms_base | [lms_base.md](lms_base.md) | Foundation utilities, taxonomy bootstrap, email-login, demo-content Drush command | module | E-01 | lms_base | DONE |
| lms_content_tools | [lms_content_tools.md](lms_content_tools.md) | Bulk entity clean, AI seed-input capture, and AI-generated content import pipeline | module | E-01 | lms_content_tools | DONE |
| lms_course | [lms_course.md](lms_course.md) | Course entity, public learner pages, /workspace admin shell | module | E-02 | lms_course | DONE |
| lms_chapter | [lms_chapter.md](lms_chapter.md) | Chapter entity — middle tier of Course→Chapter→Lesson hierarchy | module | E-02 | lms_chapter | DONE |
| lms_lesson | [lms_lesson.md](lms_lesson.md) | Lesson entity — 9-type content leaf with SCORM auto-extraction | module | E-02 | lms_lesson | DONE |
| lms_blog | [lms_blog.md](lms_blog.md) | Dual-track blog: lms_article admin entity + node:blog public frontend | module | E-02 | lms_blog | DONE |
| lms_faq | [lms_faq.md](lms_faq.md) | FAQ entity with 3-scope taxonomy and delegated routing | module | E-02 | lms_faq | DONE |
| lms_quiz | [lms_quiz.md](lms_quiz.md) | Quiz engine, GradingStrategyFactory, 8 strategies, quiz-take/result controllers | module | E-03 | lms_quiz | DONE |
| lms_question | [lms_question.md](lms_question.md) | Question bank — 8-bundle entity definitions, admin CRUD | module | E-03 | lms_question | DONE |
| lms_progress | [lms_progress.md](lms_progress.md) | Enrollment & progress tracking, user portal pages, lesson view | module | E-04 | lms_progress | DONE |
| lms_note | [lms_note.md](lms_note.md) | Student notes with video timestamp, JSON API, My Notes page | module | E-04 | lms_note | DONE |
| lms_order | [lms_order.md](lms_order.md) | Order management, checkout dispatch, auto-enrollment | module | E-05 | lms_order | DONE |
| lms_payment | [lms_payment.md](lms_payment.md) | Payment gateway plugin host, settings, BankTransfer | module | E-05 | lms_payment | IN_PROGRESS |
| lms_stripe | [lms_stripe.md](lms_stripe.md) | Stripe PaymentIntent verify and webhook stub | module | E-05 | lms_stripe | IN_PROGRESS |
| lms_paypal | [lms_paypal.md](lms_paypal.md) | PayPal gateway — dead-code stub (not wired) | module | E-05 | lms_paypal | BLOCKED |
| lms_membership | [lms_membership.md](lms_membership.md) | Membership plans, user subscriptions, access control | module | E-05 | lms_membership | DONE |
| lms_wishlist | [lms_wishlist.md](lms_wishlist.md) | Course wishlist toggle, status check, user wishlist page | module | E-05 | lms_wishlist | DONE |
| lms_bundle | [lms_bundle.md](lms_bundle.md) | Course bundle scaffold — no checkout integration | module | E-05 | lms_bundle | BLOCKED |
| lms_certificate | [lms_certificate.md](lms_certificate.md) | Certificate templates, auto-issuance on quiz pass, mPDF | module | E-06 | lms_certificate | DONE |
| lms_badge | [lms_badge.md](lms_badge.md) | Gamification badge scaffold — entities only, zero award logic | module | E-06 | lms_badge | BLOCKED |
| lms_forum | [lms_forum.md](lms_forum.md) | Course discussion forums, threaded topics, AJAX reply | module | E-07 | lms_forum | IN_PROGRESS |
| lms_rating | [lms_rating.md](lms_rating.md) | Course ratings & reviews — 3 routes missing (bug) | module | E-07 | lms_rating | IN_PROGRESS |
| lms_notification | [lms_notification.md](lms_notification.md) | In-app notifications, bell block; email/push stubs | module | E-07 | lms_notification | IN_PROGRESS |
| lms_zoom | [lms_zoom.md](lms_zoom.md) | Zoom live class integration — meeting lifecycle, Web SDK | module | E-08 | lms_zoom | DONE |
| lms_ai | [lms_ai.md](lms_ai.md) | AI platform — multi-provider plugin, course builder, no rollback | module | E-08 | lms_ai | DONE |
| lms_analytics | [lms_analytics.md](lms_analytics.md) | Internal reporting — course, user, quiz analytics with CSV | module | E-08 | lms_analytics | DONE |
| lms_mobile_api | [lms_mobile_api.md](lms_mobile_api.md) | Mobile JSON API — auth (OTP/Bearer), courses, learning, quiz | module | E-09 | lms_mobile_api | DONE |
| hardening-security | [hardening-security.md](hardening-security.md) | Input validation, access audit, CSRF, error handling, logging, DI cleanup | nfr | E-10 | all lms_* | SPEC |
| payment-webhooks | [payment-webhooks.md](payment-webhooks.md) | Stripe/PayPal/ZaloPay webhook endpoints + signature verification | integration | E-10 | lms_stripe, lms_paypal, lms_payment | SPEC |
| progress-race-condition | [progress-race-condition.md](progress-race-condition.md) | DB lock/transaction on cascade progress update | nfr | E-10 | lms_progress | SPEC |
| ai-rollback-compensation | [ai-rollback-compensation.md](ai-rollback-compensation.md) | lms_ai course builder rollback & compensation | cross-cutting | E-10 | lms_ai | SPEC |
| cache-performance | [cache-performance.md](cache-performance.md) | Redis backend, N+1 query elimination, cache tags | nfr | E-10 | lms_course, lms_analytics, lms_progress, lms_lesson | SPEC |
| ui-polish | [ui-polish.md](ui-polish.md) | 14-item UX polish pass — homepage, course, quiz, dashboard, checkout, forum, notifications, profile, search, error states | cross-cutting | E-11 | lms_theme (primary) + all content modules | SPEC |
| public-surface-seo | [public-surface-seo.md](public-surface-seo.md) | Auth-page branding, search, sitemap.xml, GDPR pages | page | E-11 | lms_base, lms_course, lms_theme | SPEC (PARTIAL) |
| quality-test-suite | [quality-test-suite.md](quality-test-suite.md) | Kernel/functional/e2e/a11y/security tests + CI pipeline | nfr | E-10 | all lms_* | SPEC |

---

## Status Counts

| Status | Count |
|---|---|
| DONE | 18 |
| IN_PROGRESS | 5 |
| BLOCKED | 3 |
| SPEC | 7 |
| SPEC (PARTIAL) | 1 |
| SPEC (planned) | 0 |
| **Total** | **36** |

---

## Story Type Counts

| story_type | count |
|---|---|
| module | 28 |
| nfr | 5 |
| cross-cutting | 2 |
| integration | 1 |
| page | 1 |

---

## Quick Navigation by Epic

- **E-01 Foundation:** [open_switcher](open_switcher.md) · [lms_base](lms_base.md) · [lms_content_tools](lms_content_tools.md)
- **E-02 Content:** [lms_course](lms_course.md) · [lms_chapter](lms_chapter.md) · [lms_lesson](lms_lesson.md) · [lms_blog](lms_blog.md) · [lms_faq](lms_faq.md)
- **E-03 Assessment:** [lms_quiz](lms_quiz.md) · [lms_question](lms_question.md)
- **E-04 Learning Progress:** [lms_progress](lms_progress.md) · [lms_note](lms_note.md)
- **E-05 Commerce:** [lms_order](lms_order.md) · [lms_payment](lms_payment.md) · [lms_stripe](lms_stripe.md) · [lms_paypal](lms_paypal.md) · [lms_membership](lms_membership.md) · [lms_wishlist](lms_wishlist.md) · [lms_bundle](lms_bundle.md)
- **E-06 Certification:** [lms_certificate](lms_certificate.md) · [lms_badge](lms_badge.md)
- **E-07 Communication:** [lms_forum](lms_forum.md) · [lms_rating](lms_rating.md) · [lms_notification](lms_notification.md)
- **E-08 Integration & AI:** [lms_zoom](lms_zoom.md) · [lms_ai](lms_ai.md) · [lms_analytics](lms_analytics.md)
- **E-09 Mobile:** [lms_mobile_api](lms_mobile_api.md)
- **E-10 Hardening:** [hardening-security](hardening-security.md) · [payment-webhooks](payment-webhooks.md) · [progress-race-condition](progress-race-condition.md) · [ai-rollback-compensation](ai-rollback-compensation.md) · [cache-performance](cache-performance.md) · [quality-test-suite](quality-test-suite.md)
- **E-11 UI/UX Polish:** [ui-polish](ui-polish.md) · [public-surface-seo](public-surface-seo.md)
