---
layer: D-Development
file: D04-tasks/_index.md
purpose: Navigation index — all Tasks grouped by Epic → Story → Task; reconciles legacy TASK-NNN IDs to canonical D03 Stories
last_updated: 2026-06-20
total_tasks: 241
---

# D04 — Task Board Index

**Layer:** D-Development / D04-tasks
**Purpose:** Single-page navigation index grouping all 241 task cards by Epic → Story. Each legacy `TASK-NNN` card retains its original detail file; this index reconciles them to canonical Story IDs from [D03-stories/](../D03-stories/).

> Vocabulary: **Epic ⊇ Story ⊇ Task**. Never "Group" or "Unit".
> ID scheme: all cards use the legacy `TASK-NNN` prefix. Future task families may adopt a different scheme (document in D01 if changed).

---

## Board Status Summary

| Status | Count |
|--------|-------|
| done | 8 |
| todo | 7 |
| backlog | 226 |
| in-progress | 0 |
| blocked | 0 |
| in-review | 0 |
| closed | 0 |
| **Total** | **241** |

---

## Reclassification Log (2026-06-20)

The following cards were moved from the draft `done/` folder to reflect real implementation status:

| Task | Draft Status | Canonical Status | Reason |
|------|-------------|-----------------|--------|
| TASK-010 | done | todo | `lms_forum` Story = IN_PROGRESS; `ForumController::viewTopic` throws `"HARD DEBUG!"` exception (live bug). Forum UI requires fix before acceptance. |

All other `done/` cards correspond to Stories marked DONE in D03 with as-built evidence in the facts base.

---

## Epic → Story → Task Map

### E-01 — Foundation

**Stories:** [lms_base](../D03-stories/lms_base.md) · [lms_content_tools](../D03-stories/lms_content_tools.md) · [open_switcher](../D03-stories/open_switcher.md)

All E-01 Stories are **DONE** (as-built). No tasks in backlog/todo target E-01 at this time; future tasks would belong to the `lms_base` or `lms_content_tools` Story.

| Task | Title | Story | Status |
|------|-------|-------|--------|
| — | *(no current cards)* | — | — |

---

### E-02 — Content

**Stories:** [lms_course](../D03-stories/lms_course.md) · [lms_chapter](../D03-stories/lms_chapter.md) · [lms_lesson](../D03-stories/lms_lesson.md) · [lms_blog](../D03-stories/lms_blog.md) · [lms_faq](../D03-stories/lms_faq.md)

All E-02 Stories are **DONE** (as-built). Tasks below target content-layer enhancements not yet in scope.

| Task | Title | Story | Status | Folder |
|------|-------|-------|--------|--------|
| [TASK-115](backlog/TASK-115-course-prerequisites.md) | Course prerequisites | lms_course | backlog | backlog/ |
| [TASK-116](backlog/TASK-116-course-access-duration-expiry.md) | Course access duration / expiry | lms_course | backlog | backlog/ |
| [TASK-117](backlog/TASK-117-course-versioning-draft-publish.md) | Course versioning — draft/publish workflow | lms_course | backlog | backlog/ |
| [TASK-118](backlog/TASK-118-course-bulk-enroll.md) | Course bulk enroll | lms_course | backlog | backlog/ |
| [TASK-119](backlog/TASK-119-course-bulk-grade-essays.md) | Course bulk grade essays | lms_quiz | backlog | backlog/ |
| [TASK-120](backlog/TASK-120-course-import-export.md) | Course import / export | lms_course | backlog | backlog/ |
| [TASK-121](backlog/TASK-121-course-archive.md) | Course archive | lms_course | backlog | backlog/ |
| [TASK-122](backlog/TASK-122-course-duplicate.md) | Course duplicate | lms_course | backlog | backlog/ |
| [TASK-147](backlog/TASK-147-ckeditor-5-config.md) | CKEditor 5 configuration | lms_content_tools | backlog | backlog/ |
| [TASK-148](backlog/TASK-148-media-library-setup.md) | Media library setup | lms_content_tools | backlog | backlog/ |
| [TASK-149](backlog/TASK-149-image-style-presets.md) | Image style presets | lms_content_tools | backlog | backlog/ |
| [TASK-150](backlog/TASK-150-video-handling-cdn.md) | Video handling — CDN offload | lms_lesson | backlog | backlog/ |
| [TASK-228](backlog/TASK-228-drip-content-schedule.md) | Drip content schedule | lms_lesson | backlog | backlog/ |
| [TASK-229](backlog/TASK-229-course-preview-gating.md) | Course preview gating | lms_course | backlog | backlog/ |
| [TASK-234](backlog/TASK-234-migrate-from-moodle.md) | Migrate from Moodle | lms_content_tools | backlog | backlog/ |
| [TASK-235](backlog/TASK-235-migrate-from-thinkific-teachable.md) | Migrate from Thinkific / Teachable | lms_content_tools | backlog | backlog/ |
| [TASK-236](backlog/TASK-236-demo-data-seeder.md) | Demo data seeder | lms_base | backlog | backlog/ |
| [TASK-237](backlog/TASK-237-user-import-templates.md) | User import templates | lms_base | backlog | backlog/ |

---

### E-03 — Assessment

**Stories:** [lms_quiz](../D03-stories/lms_quiz.md) · [lms_question](../D03-stories/lms_question.md)

Both Stories are **DONE** (as-built). Tasks target assessment enhancements.

| Task | Title | Story | Status | Folder |
|------|-------|-------|--------|--------|
| [TASK-065](backlog/TASK-065-manage-essay-grading-queue.md) | Manage essay grading queue | lms_quiz | backlog | backlog/ |
| [TASK-169](backlog/TASK-169-quiz-question-randomization.md) | Quiz question randomization | lms_quiz | backlog | backlog/ |
| [TASK-170](backlog/TASK-170-question-bank-reusable.md) | Question bank — reusable across quizzes | lms_question | backlog | backlog/ |
| [TASK-171](backlog/TASK-171-quiz-attempt-limits.md) | Quiz attempt limits | lms_quiz | backlog | backlog/ |
| [TASK-172](backlog/TASK-172-quiz-time-extended-accommodation.md) | Quiz time extended accommodation | lms_quiz | backlog | backlog/ |
| [TASK-173](backlog/TASK-173-quiz-webcam-proctoring.md) | Quiz webcam proctoring | lms_quiz | backlog | backlog/ |
| [TASK-174](backlog/TASK-174-quiz-results-export.md) | Quiz results export | lms_quiz | backlog | backlog/ |
| [TASK-175](backlog/TASK-175-question-difficulty-analytics.md) | Question difficulty analytics | lms_question | backlog | backlog/ |
| [TASK-176](backlog/TASK-176-practice-vs-cert-quiz-distinction.md) | Practice vs cert quiz distinction | lms_quiz | backlog | backlog/ |
| [TASK-232](backlog/TASK-232-spaced-repetition-flashcards.md) | Spaced-repetition flashcards | lms_quiz | backlog | backlog/ |
| [TASK-233](backlog/TASK-233-adaptive-difficulty.md) | Adaptive difficulty | lms_quiz | backlog | backlog/ |

---

### E-04 — Learning Progress

**Stories:** [lms_progress](../D03-stories/lms_progress.md) · [lms_note](../D03-stories/lms_note.md)

Both Stories are **DONE** (as-built). Tasks address race condition fix and enhancements.

| Task | Title | Story | Status | Folder |
|------|-------|-------|--------|--------|
| [TASK-017](backlog/TASK-017-progress-race-condition.md) | Progress race condition fix | progress-race-condition | backlog | backlog/ |
| [TASK-049](backlog/TASK-049-execute-proto-001-progress-race.md) | Execute PROTO-001 — progress cascade race-condition spike | progress-race-condition | backlog | backlog/ |
| [TASK-177](backlog/TASK-177-recommendations-engine.md) | Recommendations engine | lms_progress | backlog | backlog/ |
| [TASK-178](backlog/TASK-178-personalized-homepage.md) | Personalized homepage | lms_progress | backlog | backlog/ |
| [TASK-179](backlog/TASK-179-recently-viewed.md) | Recently viewed courses | lms_progress | backlog | backlog/ |
| [TASK-230](backlog/TASK-230-cohort-based-courses.md) | Cohort-based courses | lms_progress | backlog | backlog/ |
| [TASK-231](backlog/TASK-231-microlearning-bites.md) | Microlearning bites | lms_lesson | backlog | backlog/ |

---

### E-05 — Commerce

**Stories:** [lms_order](../D03-stories/lms_order.md) · [lms_payment](../D03-stories/lms_payment.md) · [lms_stripe](../D03-stories/lms_stripe.md) · [lms_paypal](../D03-stories/lms_paypal.md) · [lms_membership](../D03-stories/lms_membership.md) · [lms_wishlist](../D03-stories/lms_wishlist.md) · [lms_bundle](../D03-stories/lms_bundle.md)

Status: lms_order/lms_membership/lms_wishlist = DONE; lms_payment/lms_stripe = IN_PROGRESS; lms_paypal/lms_bundle = BLOCKED.

| Task | Title | Story | Status | Folder |
|------|-------|-------|--------|--------|
| [TASK-001](todo/TASK-001-checkout-workflow.md) | Checkout workflow implementation | lms_order | todo | todo/ |
| [TASK-002](backlog/TASK-002-webhook-handler.md) | Webhook handler + signature verification | payment-webhooks | backlog | backlog/ |
| [TASK-003](backlog/TASK-003-coupon-system.md) | Coupon system | lms_order | backlog | backlog/ |
| [TASK-004](todo/TASK-004-membership-lifecycle.md) | Membership lifecycle | lms_membership | todo | todo/ |
| [TASK-005](todo/TASK-005-order-management.md) | Order management admin | lms_order | todo | todo/ |
| [TASK-026](todo/TASK-026-checkout-ui.md) | Checkout flow UI | lms_order | todo | todo/ |
| [TASK-029](backlog/TASK-029-stripe-webhook-sig.md) | Stripe webhook signature verification | payment-webhooks | backlog | backlog/ |
| [TASK-030](backlog/TASK-030-paypal-webhook-sig.md) | PayPal webhook signature verification | payment-webhooks | backlog | backlog/ |
| [TASK-031](backlog/TASK-031-payment-idempotency.md) | Payment idempotency keys | payment-webhooks | backlog | backlog/ |
| [TASK-050](backlog/TASK-050-execute-proto-002-payment-webhook.md) | Execute PROTO-002 — payment webhook signature spike | payment-webhooks | backlog | backlog/ |
| [TASK-123](backlog/TASK-123-commerce-refund-flow.md) | Commerce refund flow | lms_payment | backlog | backlog/ |
| [TASK-124](backlog/TASK-124-commerce-prorated-membership-upgrade.md) | Prorated membership upgrade | lms_membership | backlog | backlog/ |
| [TASK-125](backlog/TASK-125-commerce-failed-payment-retry-dunning.md) | Failed payment retry / dunning | lms_stripe | backlog | backlog/ |
| [TASK-126](backlog/TASK-126-commerce-invoice-pdf.md) | Commerce invoice PDF | lms_order | backlog | backlog/ |
| [TASK-127](backlog/TASK-127-commerce-vat-vn-calculation.md) | VAT / VN tax calculation | lms_payment | backlog | backlog/ |
| [TASK-128](backlog/TASK-128-commerce-promo-pricing.md) | Promotional pricing | lms_order | backlog | backlog/ |
| [TASK-129](backlog/TASK-129-commerce-multi-currency.md) | Multi-currency support | lms_payment | backlog | backlog/ |
| [TASK-130](backlog/TASK-130-commerce-tax-inclusive-pricing.md) | Tax-inclusive pricing | lms_payment | backlog | backlog/ |
| [TASK-145](backlog/TASK-145-coupon-campaigns.md) | Coupon campaigns | lms_order | backlog | backlog/ |
| [TASK-151](backlog/TASK-151-vnpay-gateway.md) | VNPay gateway | lms_payment | backlog | backlog/ |
| [TASK-152](backlog/TASK-152-momo-wallet-gateway.md) | Momo wallet gateway | lms_payment | backlog | backlog/ |
| [TASK-153](backlog/TASK-153-vietqr-bank-transfer.md) | VietQR bank transfer | lms_payment | backlog | backlog/ |
| [TASK-154](backlog/TASK-154-einvoice-decree-123.md) | E-invoice Decree 123 | lms_order | backlog | backlog/ |
| [TASK-160](backlog/TASK-160-bank-slip-receipt-upload.md) | Bank-slip / receipt upload | lms_payment | backlog | backlog/ |
| [TASK-197](backlog/TASK-197-subscription-cancel-pause-resume.md) | Subscription cancel / pause / resume | lms_membership | backlog | backlog/ |
| [TASK-198](backlog/TASK-198-payment-method-management.md) | Payment method management | lms_stripe | backlog | backlog/ |
| [TASK-199](backlog/TASK-199-subscription-history.md) | Subscription history | lms_membership | backlog | backlog/ |
| [TASK-200](backlog/TASK-200-trial-period-management.md) | Trial period management | lms_membership | backlog | backlog/ |
| [TASK-201](backlog/TASK-201-subscription-upgrade-downgrade-preview.md) | Subscription upgrade / downgrade preview | lms_membership | backlog | backlog/ |

---

### E-06 — Certification & Gamification

**Stories:** [lms_certificate](../D03-stories/lms_certificate.md) · [lms_badge](../D03-stories/lms_badge.md)

lms_certificate = DONE; lms_badge = BLOCKED (near-stub, zero award logic).

| Task | Title | Story | Status | Folder |
|------|-------|-------|--------|--------|
| [TASK-006](done/TASK-006-certificate-issuance.md) | Certificate issuance service | lms_certificate | done | done/ |
| [TASK-007](done/TASK-007-certificate-pdf.md) | Certificate PDF generation | lms_certificate | done | done/ |
| [TASK-008](done/TASK-008-cert-verify-endpoint.md) | Public certificate verification endpoint | lms_certificate | done | done/ |
| [TASK-038](backlog/TASK-038-certificate-detail-page.md) | Certificate detail page (UX polish) | lms_certificate | backlog | backlog/ |
| [TASK-053](backlog/TASK-053-execute-proto-005-mpdf-certificate.md) | Execute PROTO-005 — mPDF certificate spike | lms_certificate | backlog | backlog/ |
| [TASK-063](backlog/TASK-063-manage-badges-admin.md) | Manage badges admin UI | lms_badge | backlog | backlog/ |
| [TASK-141](backlog/TASK-141-leaderboard.md) | Leaderboard | lms_badge | backlog | backlog/ |
| [TASK-142](backlog/TASK-142-learning-streaks.md) | Learning streaks | lms_progress | backlog | backlog/ |
| [TASK-143](backlog/TASK-143-points-xp-system.md) | Points / XP system | lms_badge | backlog | backlog/ |
| [TASK-144](backlog/TASK-144-achievements-system.md) | Achievements system | lms_badge | backlog | backlog/ |

---

### E-07 — Communication

**Stories:** [lms_forum](../D03-stories/lms_forum.md) · [lms_rating](../D03-stories/lms_rating.md) · [lms_notification](../D03-stories/lms_notification.md)

All three Stories are IN_PROGRESS (bugs outstanding). TASK-010 moved to todo/ after reclassification.

| Task | Title | Story | Status | Folder |
|------|-------|-------|--------|--------|
| [TASK-009](todo/TASK-009-notification-dispatch.md) | Notification dispatch service (email + push channels) | lms_notification | todo | todo/ |
| [TASK-010](todo/TASK-010-forum-ui.md) | Forum thread UI + permissions (active HARD DEBUG! bug) | lms_forum | todo | todo/ |
| [TASK-011](backlog/TASK-011-email-notification.md) | Email notification integration | lms_notification | backlog | backlog/ |
| [TASK-039](backlog/TASK-039-forum-ui-polish.md) | Forum UI polish | lms_forum | backlog | backlog/ |
| [TASK-040](backlog/TASK-040-notification-center-ui.md) | Notification center UI | lms_notification | backlog | backlog/ |
| [TASK-056](backlog/TASK-056-find-013-notification-anonymous-auth.md) | FIND-013 notification anonymous auth bug | lms_notification | backlog | backlog/ |
| [TASK-057](backlog/TASK-057-find-006-rating-controller-cleanup.md) | FIND-006 rating controller cleanup (3 missing routes) | lms_rating | backlog | backlog/ |
| [TASK-064](backlog/TASK-064-manage-send-notifications.md) | Manage send notifications admin | lms_notification | backlog | backlog/ |
| [TASK-136](backlog/TASK-136-course-qa-section.md) | Course Q&A section | lms_forum | backlog | backlog/ |
| [TASK-137](backlog/TASK-137-lesson-comments-thread.md) | Lesson comments thread | lms_forum | backlog | backlog/ |
| [TASK-138](backlog/TASK-138-mentions-system.md) | @mentions system | lms_forum | backlog | backlog/ |
| [TASK-139](backlog/TASK-139-email-digest-weekly.md) | Email digest — weekly | lms_notification | backlog | backlog/ |
| [TASK-140](backlog/TASK-140-notification-preferences-ui.md) | Notification preferences UI | lms_notification | backlog | backlog/ |
| [TASK-202](backlog/TASK-202-comment-moderation-queue.md) | Comment moderation queue | lms_forum | backlog | backlog/ |
| [TASK-203](backlog/TASK-203-user-report-functionality.md) | User report functionality | lms_forum | backlog | backlog/ |
| [TASK-204](backlog/TASK-204-auto-flag-profanity.md) | Auto-flag profanity | lms_forum | backlog | backlog/ |
| [TASK-205](backlog/TASK-205-moderator-dashboard.md) | Moderator dashboard | lms_forum | backlog | backlog/ |
| [TASK-206](backlog/TASK-206-mod-action-audit-log.md) | Moderation action audit log | lms_forum | backlog | backlog/ |

---

### E-08 — Integration & AI

**Stories:** [lms_zoom](../D03-stories/lms_zoom.md) · [lms_ai](../D03-stories/lms_ai.md) · [lms_analytics](../D03-stories/lms_analytics.md)

All three Stories are **DONE** (as-built). Tasks address known gaps and enhancements.

| Task | Title | Story | Status | Folder |
|------|-------|-------|--------|--------|
| [TASK-012](done/TASK-012-ai-provider-plugin.md) | AI provider plugin architecture | lms_ai | done | done/ |
| [TASK-013](done/TASK-013-ai-course-builder.md) | AI course builder | lms_ai | done | done/ |
| [TASK-014](done/TASK-014-analytics-dashboards.md) | Analytics dashboards | lms_analytics | done | done/ |
| [TASK-023](done/TASK-023-zoom-integration.md) | Zoom meeting integration | lms_zoom | done | done/ |
| [TASK-032](backlog/TASK-032-ai-rollback.md) | AI rollback + compensation logic | ai-rollback-compensation | backlog | backlog/ |
| [TASK-051](backlog/TASK-051-execute-proto-003-ai-rate-rollback.md) | Execute PROTO-003 — AI rate limit + cost cap + rollback spike | ai-rollback-compensation | backlog | backlog/ |
| [TASK-054](backlog/TASK-054-find-008-gemini-key-header.md) | FIND-008 Gemini API key in URL query param (→ header) | lms_ai | backlog | backlog/ |
| [TASK-055](backlog/TASK-055-find-009-paypalgateway-dedup.md) | FIND-009 PayPalGateway deduplication | lms_paypal | backlog | backlog/ |
| [TASK-058](backlog/TASK-058-find-007-sitemap-missing-routes.md) | FIND-007 sitemap missing routes | public-surface-seo | backlog | backlog/ |
| [TASK-071](backlog/TASK-071-ga4-analytics-frontend.md) | GA4 analytics frontend integration | lms_analytics | backlog | backlog/ |
| [TASK-161](backlog/TASK-161-class-schedule-entity.md) | Class schedule entity | lms_zoom | backlog | backlog/ |
| [TASK-162](backlog/TASK-162-zoom-recording-auto-publish.md) | Zoom recording auto-publish | lms_zoom | backlog | backlog/ |
| [TASK-163](backlog/TASK-163-class-attendance-tracking.md) | Class attendance tracking | lms_zoom | backlog | backlog/ |
| [TASK-164](backlog/TASK-164-ical-calendar-export.md) | iCal calendar export | lms_zoom | backlog | backlog/ |
| [TASK-165](backlog/TASK-165-google-calendar-sync.md) | Google Calendar sync | lms_zoom | backlog | backlog/ |
| [TASK-166](backlog/TASK-166-class-reminder-cadence.md) | Class reminder cadence | lms_zoom | backlog | backlog/ |
| [TASK-167](backlog/TASK-167-instructor-office-hours.md) | Instructor office hours | lms_zoom | backlog | backlog/ |
| [TASK-168](backlog/TASK-168-cohort-start-dates.md) | Cohort start dates | lms_progress | backlog | backlog/ |
| [TASK-183](backlog/TASK-183-saml-sso.md) | SAML SSO | lms_base | backlog | backlog/ |
| [TASK-184](backlog/TASK-184-oidc-sso.md) | OIDC SSO | lms_base | backlog | backlog/ |
| [TASK-185](backlog/TASK-185-scim-user-provisioning.md) | SCIM user provisioning | lms_base | backlog | backlog/ |
| [TASK-186](backlog/TASK-186-lti-1-3-tool.md) | LTI 1.3 tool | lms_lesson | backlog | backlog/ |
| [TASK-187](backlog/TASK-187-zapier-make-webhook-outbound.md) | Zapier / Make webhook outbound | lms_notification | backlog | backlog/ |
| [TASK-188](backlog/TASK-188-slack-discord-notifications.md) | Slack / Discord notifications | lms_notification | backlog | backlog/ |
| [TASK-189](backlog/TASK-189-course-completion-rate-report.md) | Course completion rate report | lms_analytics | backlog | backlog/ |
| [TASK-190](backlog/TASK-190-revenue-per-instructor-course-report.md) | Revenue per instructor / course report | lms_analytics | backlog | backlog/ |
| [TASK-191](backlog/TASK-191-student-progress-report.md) | Student progress report | lms_analytics | backlog | backlog/ |
| [TASK-192](backlog/TASK-192-quiz-performance-report.md) | Quiz performance report | lms_analytics | backlog | backlog/ |
| [TASK-193](backlog/TASK-193-funnel-analytics.md) | Funnel analytics | lms_analytics | backlog | backlog/ |
| [TASK-194](backlog/TASK-194-cohort-analysis-report.md) | Cohort analysis report | lms_analytics | backlog | backlog/ |
| [TASK-195](backlog/TASK-195-custom-report-builder.md) | Custom report builder | lms_analytics | backlog | backlog/ |
| [TASK-196](backlog/TASK-196-scheduled-reports-email.md) | Scheduled reports via email | lms_analytics | backlog | backlog/ |
| [TASK-146](backlog/TASK-146-email-campaign-builder.md) | Email campaign builder | lms_notification | backlog | backlog/ |
| [TASK-052](backlog/TASK-052-execute-proto-004-scorm-xapi.md) | Execute PROTO-004 — SCORM/xAPI spike | lms_lesson | backlog | backlog/ |

---

### E-09 — Mobile & Additional Features

**Stories:** [lms_mobile_api](../D03-stories/lms_mobile_api.md) · [lms_wishlist](../D03-stories/lms_wishlist.md) · [lms_bundle](../D03-stories/lms_bundle.md) · [lms_note](../D03-stories/lms_note.md) · [lms_badge](../D03-stories/lms_badge.md) · [lms_faq](../D03-stories/lms_faq.md) · [lms_blog](../D03-stories/lms_blog.md)

lms_mobile_api Story is **DONE**. Additional-feature Stories are mostly DONE with lms_badge/lms_bundle BLOCKED.

| Task | Title | Story | Status | Folder |
|------|-------|-------|--------|--------|
| [TASK-072](backlog/TASK-072-mobile-app-scope-decision.md) | Mobile app scope decision | lms_mobile_api | backlog | backlog/ |
| [TASK-074](backlog/TASK-074-lms-mobile-api-hardening.md) | lms_mobile_api hardening | lms_mobile_api | backlog | backlog/ |

---

### E-10 — Quality & Hardening

**Stories:** [hardening-security](../D03-stories/hardening-security.md) · [payment-webhooks](../D03-stories/payment-webhooks.md) · [progress-race-condition](../D03-stories/progress-race-condition.md) · [ai-rollback-compensation](../D03-stories/ai-rollback-compensation.md) · [cache-performance](../D03-stories/cache-performance.md) · [quality-test-suite](../D03-stories/quality-test-suite.md)

All Stories in E-10 are **SPEC** (Epic status: NOT_STARTED / PLANNED). These are the pre-production hardening tasks — no new features.

#### Story: hardening-security

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-015](backlog/TASK-015-api-input-validation.md) | API input validation — all write endpoints | backlog | backlog/ |
| [TASK-016](backlog/TASK-016-access-control-hardening.md) | Access control hardening — all routes | backlog | backlog/ |
| [TASK-018](backlog/TASK-018-static-service-cleanup.md) | Static service call cleanup (\\Drupal:: → DI) | backlog | backlog/ |
| [TASK-019](backlog/TASK-019-entity-access-audit.md) | Entity access handler audit | backlog | backlog/ |
| [TASK-020](backlog/TASK-020-critical-logging.md) | Logging for critical operations | backlog | backlog/ |
| [TASK-024](backlog/TASK-024-csrf-audit.md) | CSRF protection audit | backlog | backlog/ |
| [TASK-025](backlog/TASK-025-error-handling.md) | Error handling standardization | backlog | backlog/ |
| [TASK-087](backlog/TASK-087-security-pentest-engagement.md) | Security pentest engagement | backlog | backlog/ |
| [TASK-088](backlog/TASK-088-security-owasp-top10-audit.md) | OWASP Top-10 audit | backlog | backlog/ |
| [TASK-089](backlog/TASK-089-security-cve-baseline-monitoring.md) | CVE baseline monitoring | backlog | backlog/ |
| [TASK-090](backlog/TASK-090-security-csp-headers.md) | CSP headers | backlog | backlog/ |
| [TASK-091](backlog/TASK-091-security-rate-limit-per-endpoint.md) | Rate limiting per endpoint | backlog | backlog/ |
| [TASK-092](backlog/TASK-092-security-brute-force-login.md) | Brute-force login protection | backlog | backlog/ |
| [TASK-093](backlog/TASK-093-security-2fa-optin.md) | 2FA opt-in | backlog | backlog/ |
| [TASK-094](backlog/TASK-094-security-session-hardening.md) | Session hardening | backlog | backlog/ |

#### Story: payment-webhooks

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-002](backlog/TASK-002-webhook-handler.md) | Webhook handler + signature verification | backlog | backlog/ |
| [TASK-029](backlog/TASK-029-stripe-webhook-sig.md) | Stripe webhook signature verification | backlog | backlog/ |
| [TASK-030](backlog/TASK-030-paypal-webhook-sig.md) | PayPal webhook signature verification | backlog | backlog/ |
| [TASK-031](backlog/TASK-031-payment-idempotency.md) | Payment idempotency keys | backlog | backlog/ |
| [TASK-050](backlog/TASK-050-execute-proto-002-payment-webhook.md) | Execute PROTO-002 — payment webhook spike | backlog | backlog/ |

#### Story: progress-race-condition

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-017](backlog/TASK-017-progress-race-condition.md) | Progress race condition fix (DB lock/transaction) | backlog | backlog/ |
| [TASK-049](backlog/TASK-049-execute-proto-001-progress-race.md) | Execute PROTO-001 — progress cascade race-condition spike | backlog | backlog/ |

#### Story: ai-rollback-compensation

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-032](backlog/TASK-032-ai-rollback.md) | AI rollback + compensation logic | backlog | backlog/ |
| [TASK-051](backlog/TASK-051-execute-proto-003-ai-rate-rollback.md) | Execute PROTO-003 — AI rate limit + cost cap + rollback spike | backlog | backlog/ |

#### Story: cache-performance

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-021](backlog/TASK-021-cache-strategy.md) | Cache strategy implementation | backlog | backlog/ |
| [TASK-022](backlog/TASK-022-performance-optimization.md) | Performance optimization (N+1 queries) | backlog | backlog/ |

#### Story: quality-test-suite

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-067](backlog/TASK-067-e03-test-definitions-completion.md) | E03 test definitions completion | backlog | backlog/ |
| [TASK-068](backlog/TASK-068-e2e-browser-test-suite.md) | E2E browser test suite | backlog | backlog/ |
| [TASK-069](backlog/TASK-069-ci-pipeline-quality-gates.md) | CI pipeline quality gates | backlog | backlog/ |
| [TASK-075](backlog/TASK-075-test-kernel-foundation-content.md) | Kernel tests — foundation + content modules | backlog | backlog/ |
| [TASK-076](backlog/TASK-076-test-kernel-assessment.md) | Kernel tests — assessment (quiz + question) | backlog | backlog/ |
| [TASK-077](backlog/TASK-077-test-kernel-progress.md) | Kernel tests — progress + notes | backlog | backlog/ |
| [TASK-078](backlog/TASK-078-test-kernel-commerce.md) | Kernel tests — commerce (order/payment/membership) | backlog | backlog/ |
| [TASK-079](backlog/TASK-079-test-kernel-certificate.md) | Kernel tests — certificate | backlog | backlog/ |
| [TASK-080](backlog/TASK-080-test-kernel-communication.md) | Kernel tests — communication (forum/rating/notification) | backlog | backlog/ |
| [TASK-081](backlog/TASK-081-test-functional-controllers.md) | Functional tests — controllers | backlog | backlog/ |
| [TASK-082](backlog/TASK-082-test-api-contract-tests.md) | API contract tests (/api/v1/*) | backlog | backlog/ |
| [TASK-083](backlog/TASK-083-test-visual-regression.md) | Visual regression tests | backlog | backlog/ |
| [TASK-084](backlog/TASK-084-test-performance-benchmark.md) | Performance benchmark tests | backlog | backlog/ |
| [TASK-085](backlog/TASK-085-test-accessibility-suite.md) | Accessibility test suite (WCAG 2.1 AA) | backlog | backlog/ |
| [TASK-086](backlog/TASK-086-test-security-regression.md) | Security regression tests | backlog | backlog/ |
| [TASK-073](backlog/TASK-073-f02-release-checklist-v1.md) | F02 release checklist v1.0 | backlog | backlog/ |

---

### E-11 — UI/UX Polish

**Stories:** [ui-polish](../D03-stories/ui-polish.md) · [public-surface-seo](../D03-stories/public-surface-seo.md)

Both Stories are **SPEC** (Epic status: NOT_STARTED / PLANNED). Depends on E-10 completing first.

#### Story: ui-polish

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-027](done/TASK-027-management-crud-polish.md) | Management CRUD pages polish | done | done/ |
| [TASK-028](todo/TASK-028-mobile-responsive.md) | Mobile responsive audit (375/768/1440px) | todo | todo/ |
| [TASK-033](backlog/TASK-033-homepage-redesign.md) | Homepage redesign (all 15 skins) | backlog | backlog/ |
| [TASK-034](backlog/TASK-034-course-detail-polish.md) | Course detail page polish | backlog | backlog/ |
| [TASK-035](backlog/TASK-035-lesson-view-ux.md) | Lesson view UX improvements | backlog | backlog/ |
| [TASK-036](backlog/TASK-036-quiz-taking-ux.md) | Quiz taking UX | backlog | backlog/ |
| [TASK-037](backlog/TASK-037-dashboard-redesign.md) | Dashboard redesign | backlog | backlog/ |
| [TASK-039](backlog/TASK-039-forum-ui-polish.md) | Forum UI polish | backlog | backlog/ |
| [TASK-040](backlog/TASK-040-notification-center-ui.md) | Notification center UI | backlog | backlog/ |
| [TASK-041](backlog/TASK-041-profile-page-ui.md) | Profile page UI | backlog | backlog/ |
| [TASK-042](backlog/TASK-042-search-results-ux.md) | Search results UX | backlog | backlog/ |
| [TASK-043](backlog/TASK-043-error-empty-states.md) | Error and empty-state pages | backlog | backlog/ |
| [TASK-044](backlog/TASK-044-learning-paths.md) | Learning paths (UI layer) | backlog | backlog/ |
| [TASK-045](backlog/TASK-045-instructor-pages.md) | Instructor pages | backlog | backlog/ |
| [TASK-046](backlog/TASK-046-public-content-pages.md) | Public content pages | backlog | backlog/ |
| [TASK-047](backlog/TASK-047-additional-entity-ui.md) | Additional entity UI | backlog | backlog/ |
| [TASK-048](backlog/TASK-048-student-portal-pages.md) | Student portal pages | backlog | backlog/ |
| [TASK-038](backlog/TASK-038-certificate-detail-page.md) | Certificate detail page | backlog | backlog/ |
| [TASK-107](backlog/TASK-107-a11y-wcag-aa-audit.md) | WCAG 2.1 AA audit | backlog | backlog/ |
| [TASK-108](backlog/TASK-108-a11y-screen-reader-pass.md) | Screen reader pass | backlog | backlog/ |
| [TASK-109](backlog/TASK-109-a11y-color-contrast-tooling.md) | Color contrast tooling | backlog | backlog/ |
| [TASK-213](backlog/TASK-213-pwa-service-worker.md) | PWA service worker | backlog | backlog/ |
| [TASK-214](backlog/TASK-214-critical-css-inline.md) | Critical CSS inline | backlog | backlog/ |
| [TASK-215](backlog/TASK-215-image-lazy-loading.md) | Image lazy loading | backlog | backlog/ |
| [TASK-216](backlog/TASK-216-http-cache-headers-per-route.md) | HTTP cache headers per route | backlog | backlog/ |
| [TASK-217](backlog/TASK-217-http3-quic-support.md) | HTTP/3 QUIC support evaluation | backlog | backlog/ |
| [TASK-218](backlog/TASK-218-high-contrast-theme-variant.md) | High-contrast theme variant | backlog | backlog/ |
| [TASK-219](backlog/TASK-219-reduced-motion-mode.md) | Reduced-motion mode | backlog | backlog/ |
| [TASK-220](backlog/TASK-220-font-size-controls.md) | Font size controls | backlog | backlog/ |
| [TASK-221](backlog/TASK-221-video-captions-subtitles.md) | Video captions / subtitles | backlog | backlog/ |
| [TASK-222](backlog/TASK-222-welcome-email-series.md) | Welcome email series | backlog | backlog/ |
| [TASK-223](backlog/TASK-223-instructor-setup-wizard.md) | Instructor setup wizard | backlog | backlog/ |
| [TASK-224](backlog/TASK-224-first-time-ux-tour.md) | First-time UX tour | backlog | backlog/ |
| [TASK-225](backlog/TASK-225-empty-state-ctas.md) | Empty-state CTAs | backlog | backlog/ |
| [TASK-226](backlog/TASK-226-course-completion-celebration.md) | Course completion celebration | backlog | backlog/ |
| [TASK-227](backlog/TASK-227-re-engagement-campaign.md) | Re-engagement campaign | backlog | backlog/ |

#### Story: public-surface-seo

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-059](backlog/TASK-059-auth-pages-polish.md) | Auth pages polish | backlog | backlog/ |
| [TASK-060](backlog/TASK-060-search-engine-results-page.md) | Search engine results page | backlog | backlog/ |
| [TASK-061](backlog/TASK-061-seo-foundation.md) | SEO foundation (meta, sitemap.xml, structured data) | backlog | backlog/ |
| [TASK-062](backlog/TASK-062-gdpr-consent-legal-pages.md) | GDPR consent + legal pages | backlog | backlog/ |
| [TASK-058](backlog/TASK-058-find-007-sitemap-missing-routes.md) | FIND-007 sitemap missing routes | backlog | backlog/ |
| [TASK-070](backlog/TASK-070-userguide-content-completion.md) | User guide content completion | backlog | backlog/ |

---

### Cross-Epic / Operational Backlog

Tasks that span multiple Epics or concern infrastructure, VN localization, multi-tenancy, or deployment — not mapped to a single module Story.

#### Infrastructure & Deployment

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-095](backlog/TASK-095-prod-hosting-provider-selection.md) | Production hosting provider selection | backlog | backlog/ |
| [TASK-096](backlog/TASK-096-ssl-https-automation.md) | SSL / HTTPS automation | backlog | backlog/ |
| [TASK-097](backlog/TASK-097-dns-cdn-cloudflare-setup.md) | DNS + CDN Cloudflare setup | backlog | backlog/ |
| [TASK-098](backlog/TASK-098-database-backup-automation.md) | Database backup automation | backlog | backlog/ |
| [TASK-099](backlog/TASK-099-database-restore-drill.md) | Database restore drill | backlog | backlog/ |
| [TASK-100](backlog/TASK-100-monitoring-agent-grafana.md) | Monitoring agent + Grafana | backlog | backlog/ |
| [TASK-101](backlog/TASK-101-log-aggregation-loki.md) | Log aggregation — Loki | backlog | backlog/ |
| [TASK-102](backlog/TASK-102-php-fpm-tuning.md) | PHP-FPM tuning | backlog | backlog/ |
| [TASK-103](backlog/TASK-103-nginx-config-hardening.md) | Nginx config hardening | backlog | backlog/ |
| [TASK-104](backlog/TASK-104-redis-config-tuning.md) | Redis config tuning | backlog | backlog/ |
| [TASK-105](backlog/TASK-105-healthcheck-endpoints.md) | Healthcheck endpoints | backlog | backlog/ |
| [TASK-106](backlog/TASK-106-deployment-automation.md) | Deployment automation | backlog | backlog/ |
| [TASK-207](backlog/TASK-207-feature-flag-admin-ui.md) | Feature flag admin UI | backlog | backlog/ |
| [TASK-208](backlog/TASK-208-maintenance-mode-toggle.md) | Maintenance mode toggle | backlog | backlog/ |
| [TASK-209](backlog/TASK-209-public-status-page.md) | Public status page | backlog | backlog/ |
| [TASK-210](backlog/TASK-210-audit-log-ui.md) | Audit log UI | backlog | backlog/ |
| [TASK-211](backlog/TASK-211-banner-announcement-system.md) | Banner announcement system | backlog | backlog/ |
| [TASK-212](backlog/TASK-212-synthetic-monitoring-uptime.md) | Synthetic monitoring / uptime | backlog | backlog/ |

#### Vietnamese Localization (VN)

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-110](backlog/TASK-110-i18n-infrastructure.md) | i18n infrastructure | backlog | backlog/ |
| [TASK-111](backlog/TASK-111-i18n-vietnamese-translation.md) | Vietnamese translation (all UI strings) | backlog | backlog/ |
| [TASK-112](backlog/TASK-112-i18n-date-number-locale.md) | Date + number locale formatting | backlog | backlog/ |
| [TASK-113](backlog/TASK-113-i18n-email-templates.md) | Email templates — Vietnamese | backlog | backlog/ |
| [TASK-114](backlog/TASK-114-i18n-rtl-support-evaluation.md) | RTL support evaluation | backlog | backlog/ |
| [TASK-155](backlog/TASK-155-vn-business-tax-id.md) | VN business tax ID (MST) | backlog | backlog/ |
| [TASK-156](backlog/TASK-156-vn-name-diacritics-handling.md) | VN name diacritics handling | backlog | backlog/ |
| [TASK-157](backlog/TASK-157-vn-phone-format.md) | VN phone number format | backlog | backlog/ |
| [TASK-158](backlog/TASK-158-vietnamese-fonts-pipeline.md) | Vietnamese fonts pipeline | backlog | backlog/ |
| [TASK-159](backlog/TASK-159-vn-holiday-calendar-cron.md) | VN holiday calendar cron | backlog | backlog/ |

#### User Management

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-131](backlog/TASK-131-user-bulk-import-csv.md) | User bulk import CSV | backlog | backlog/ |
| [TASK-132](backlog/TASK-132-user-merge.md) | User merge | backlog | backlog/ |
| [TASK-133](backlog/TASK-133-gdpr-data-export.md) | GDPR data export | backlog | backlog/ |
| [TASK-134](backlog/TASK-134-gdpr-data-deletion.md) | GDPR data deletion | backlog | backlog/ |
| [TASK-135](backlog/TASK-135-user-role-hierarchy.md) | User role hierarchy | backlog | backlog/ |

#### Discovery & Personalization

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-177](backlog/TASK-177-recommendations-engine.md) | Recommendations engine | backlog | backlog/ |
| [TASK-178](backlog/TASK-178-personalized-homepage.md) | Personalized homepage | backlog | backlog/ |
| [TASK-179](backlog/TASK-179-recently-viewed.md) | Recently viewed courses | backlog | backlog/ |
| [TASK-180](backlog/TASK-180-search-autocomplete-suggestions.md) | Search autocomplete suggestions | backlog | backlog/ |
| [TASK-181](backlog/TASK-181-trending-courses.md) | Trending courses | backlog | backlog/ |
| [TASK-182](backlog/TASK-182-saved-searches.md) | Saved searches | backlog | backlog/ |

#### Multi-Tenancy / Org

| Task | Title | Status | Folder |
|------|-------|--------|--------|
| [TASK-238](backlog/TASK-238-org-entity-multi-tenancy.md) | Org entity — multi-tenancy | backlog | backlog/ |
| [TASK-239](backlog/TASK-239-org-custom-branding.md) | Org custom branding | backlog | backlog/ |
| [TASK-240](backlog/TASK-240-subdomain-routing.md) | Subdomain routing | backlog | backlog/ |
| [TASK-241](backlog/TASK-241-org-level-reports.md) | Org-level reports | backlog | backlog/ |

---

## Notes on Naming Scheme

All legacy task cards use the `TASK-NNN` prefix. This is the canonical scheme for this task family. Do not renumber. New tasks generated in future rounds may use `TK-NNN` or round-scoped IDs (e.g. `R01-001`) — document any new scheme in [D01-dev-overview.md](../D01-dev-overview.md) when adopted.

---

*Cross-references: [D02-epics/](../D02-epics/) · [D03-stories/](../D03-stories/) · [D05-session-status.md](../D05-session-status.md)*
