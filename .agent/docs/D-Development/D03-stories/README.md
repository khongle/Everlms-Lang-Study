# D03 — Stories

Story specs for EverLMS (Drupal 11 LMS). One file per Story. Story = delivery unit that anchors to a module, page, workflow, integration, NFR, or cross-cutting concern.

**Vocabulary:** Epic ⊇ Story ⊇ Task (canonical D-layer terms; see [`D02-epics/README.md`](../D02-epics/README.md)).

**Template:** `00-Guidelines/A08-layer-templates/04-d-development/D03-story.md`

---

## Story Index — All ~35 Stories

Legend: `module` = module-scoped Story | `nfr` = Non-Functional Requirement | `integration` = external system | `cross-cutting` = spans multiple modules | `page` = public/UI surface

### Module Stories (28) — Epics E-01 through E-09

| Story file | Title (short) | Story type | Epic | Status |
|---|---|---|---|---|
| [`open_switcher.md`](open_switcher.md) | Admin impersonation utility | module | E-01 | DONE |
| [`lms_base.md`](lms_base.md) | Foundation utilities, taxonomy bootstrap | module | E-01 | DONE |
| [`lms_content_tools.md`](lms_content_tools.md) | Bulk entity clean, AI seed-input | module | E-01 | DONE |
| [`lms_course.md`](lms_course.md) | Course entity, public learner pages | module | E-02 | DONE |
| [`lms_chapter.md`](lms_chapter.md) | Chapter entity — curriculum middle tier | module | E-02 | DONE |
| [`lms_lesson.md`](lms_lesson.md) | Lesson entity — 9-type content leaf | module | E-02 | DONE |
| [`lms_blog.md`](lms_blog.md) | Dual-track blog (lms_article + node:blog) | module | E-02 | DONE |
| [`lms_faq.md`](lms_faq.md) | FAQ entity with 3-scope taxonomy | module | E-02 | DONE |
| [`lms_quiz.md`](lms_quiz.md) | Quiz engine, grading strategies | module | E-03 | DONE |
| [`lms_question.md`](lms_question.md) | Question bank — 8 bundle types | module | E-03 | DONE |
| [`lms_progress.md`](lms_progress.md) | Enrollment & progress tracking | module | E-04 | DONE |
| [`lms_note.md`](lms_note.md) | Student notes with video timestamp | module | E-04 | DONE |
| [`lms_order.md`](lms_order.md) | Order management, checkout, auto-enroll | module | E-05 | DONE |
| [`lms_payment.md`](lms_payment.md) | Payment gateway plugin host | module | E-05 | IN_PROGRESS |
| [`lms_stripe.md`](lms_stripe.md) | Stripe PaymentIntent + webhook stub | module | E-05 | IN_PROGRESS |
| [`lms_paypal.md`](lms_paypal.md) | PayPal gateway — dead-code stub | module | E-05 | BLOCKED |
| [`lms_membership.md`](lms_membership.md) | Membership plans + subscriptions | module | E-05 | DONE |
| [`lms_wishlist.md`](lms_wishlist.md) | Course wishlist toggle + page | module | E-05 | DONE |
| [`lms_bundle.md`](lms_bundle.md) | Course bundle scaffold | module | E-05 | BLOCKED |
| [`lms_certificate.md`](lms_certificate.md) | Certificate auto-issuance + mPDF | module | E-06 | DONE |
| [`lms_badge.md`](lms_badge.md) | Badge scaffold — no award logic | module | E-06 | BLOCKED |
| [`lms_forum.md`](lms_forum.md) | Course discussion forums | module | E-07 | IN_PROGRESS |
| [`lms_rating.md`](lms_rating.md) | Course ratings & reviews | module | E-07 | IN_PROGRESS |
| [`lms_notification.md`](lms_notification.md) | In-app notifications, bell block | module | E-07 | IN_PROGRESS |
| [`lms_zoom.md`](lms_zoom.md) | Zoom live class integration | module | E-08 | DONE |
| [`lms_ai.md`](lms_ai.md) | AI platform, course builder | module | E-08 | DONE |
| [`lms_analytics.md`](lms_analytics.md) | Internal analytics + CSV export | module | E-08 | DONE |
| [`lms_mobile_api.md`](lms_mobile_api.md) | Mobile JSON API — Flutter backend | module | E-09 | DONE |

### Cross-Cutting & NFR Stories (8) — Epics E-10 and E-11

| Story file | Title (short) | Story type | Epic | Status |
|---|---|---|---|---|
| [`hardening-security.md`](hardening-security.md) | Input validation, access audit, CSRF, error handling, logging, DI cleanup | nfr | E-10 | SPEC |
| [`payment-webhooks.md`](payment-webhooks.md) | Stripe/PayPal/ZaloPay webhook endpoints + signature verification | integration | E-10 | SPEC |
| [`progress-race-condition.md`](progress-race-condition.md) | DB lock/transaction on cascade progress update | nfr | E-10 | SPEC |
| [`ai-rollback-compensation.md`](ai-rollback-compensation.md) | lms_ai course builder rollback & compensation | cross-cutting | E-10 | SPEC |
| [`cache-performance.md`](cache-performance.md) | Redis backend, N+1 elimination, cache tags | nfr | E-10 | SPEC |
| [`ui-polish.md`](ui-polish.md) | 14-item UX polish pass across all public/workspace pages | cross-cutting | E-11 | SPEC |
| [`public-surface-seo.md`](public-surface-seo.md) | Auth-page branding, search, sitemap.xml, GDPR pages | page | E-11 | SPEC (PARTIAL) |
| [`quality-test-suite.md`](quality-test-suite.md) | Kernel/functional/e2e/a11y/security tests + CI pipeline | nfr | E-10 | SPEC |

---

## Status Summary

| Status | Count | Notes |
|---|---|---|
| DONE | 18 | Module stories with confirmed as-built implementation |
| IN_PROGRESS | 5 | Module stories partially implemented (lms_payment, lms_stripe, lms_forum, lms_rating, lms_notification) |
| BLOCKED | 3 | Dead code or stub with no award/checkout logic (lms_paypal, lms_badge, lms_bundle) |
| SPEC | 8 | Cross-cutting / NFR stories — not yet started; all are PLANNED |
| **Total** | **36** | |

---

## Epic → Story Map

| Epic | Label | Stories |
|---|---|---|
| E-01 | Foundation | open_switcher, lms_base, lms_content_tools |
| E-02 | Content | lms_course, lms_chapter, lms_lesson, lms_blog, lms_faq |
| E-03 | Assessment | lms_quiz, lms_question |
| E-04 | Learning Progress | lms_progress, lms_note |
| E-05 | Commerce | lms_order, lms_payment, lms_stripe, lms_paypal, lms_membership, lms_wishlist, lms_bundle |
| E-06 | Certification | lms_certificate, lms_badge |
| E-07 | Communication | lms_forum, lms_rating, lms_notification |
| E-08 | Integration & AI | lms_zoom, lms_ai, lms_analytics |
| E-09 | Mobile | lms_mobile_api |
| E-10 | Hardening | hardening-security, payment-webhooks, progress-race-condition, ai-rollback-compensation, cache-performance, quality-test-suite |
| E-11 | UI/UX Polish | ui-polish, public-surface-seo |

---

## Dependency Build Order (critical path for E-10)

```
hardening-security (S1: LmsException + ValidationService skeleton)
    ↓
payment-webhooks  ←  progress-race-condition  ←  ai-rollback-compensation
    ↓
cache-performance
    ↓
quality-test-suite (spans all Stories above)
    ↓
ui-polish  ←  public-surface-seo   (Epic E-11 — after E-10 complete)
```

---

## Known Issues Cross-Reference

Issues documented in `E02-issues/` that feed these Stories:

| Issue | Story | Severity |
|---|---|---|
| lms_forum ForumController::viewTopic throws "HARD DEBUG!" | hardening-security, ui-polish | HIGH |
| lms_rating 3 missing runtime routes | hardening-security | MEDIUM |
| Stripe PaymentIntent created on form BUILD (not submit) | payment-webhooks | HIGH |
| lms_paypal: wrong interface, no @PaymentGateway annotation — dead code | payment-webhooks, lms_paypal | HIGH |
| lms_user_certificate.field_expiry_date is varchar not int | ui-polish | MEDIUM |
| Zoom generateSignature() references wrong service id | lms_zoom | MEDIUM |
| All third-party API keys in plaintext committed config YAML | hardening-security | CRITICAL |
| lms_ai: Gemini API key passed via URL parameter (should be header) | ai-rollback-compensation | HIGH |
| lms_badge: zero award logic — entities only | lms_badge | LOW |
| No webhook endpoints exist anywhere | payment-webhooks | CRITICAL |

See `G02-risk-register.md` (planned, layer not yet materialized) (planned, layer G — not yet materialized) for RISK-001 through RISK-004.
