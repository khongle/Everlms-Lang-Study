# D01-dev-overview - Development Overview & Epic Registry

**Last updated:** 2026-06-21

EverLMS is an **as-built rebuild**: most delivery is already complete. This layer documents the build spine (epics → stories → tasks) reverse-mapped from the code, with the **open work** (gap-closure for risks) tracked as actionable kanban items in [D04](../D04-tasks/_index.md).

## Epic registry
13 epics (hierarchical WBS `E##`), one per domain area. Status reflects as-built completeness.

| Epic | Title | Modules | Status |
| ---- | ----- | ------- | ------ |
| [E01](../D02-epics/E01-foundation.md) | Foundation & platform | lms_base, open_switcher, lms_theme | done |
| [E02](../D02-epics/E02-course-catalog.md) | Course catalog & structure | lms_course, lms_chapter, lms_lesson | done |
| [E03](../D02-epics/E03-learning-progress.md) | Enrollment & progress | lms_progress | done |
| [E04](../D02-epics/E04-assessment.md) | Assessment & quizzing | lms_quiz, lms_question | done |
| [E05](../D02-epics/E05-certification.md) | Certification & badges | lms_certificate, lms_badge | partial (badges blocked) |
| [E06](../D02-epics/E06-commerce.md) | Commerce & payments | lms_order, lms_bundle, lms_payment, lms_stripe, lms_paypal | partial (webhooks/paypal) |
| [E07](../D02-epics/E07-membership.md) | Memberships | lms_membership | done |
| [E08](../D02-epics/E08-community.md) | Community & engagement | lms_forum, lms_rating, lms_note, lms_wishlist, lms_blog, lms_faq, lms_zoom | done (zoom partial) |
| [E09](../D02-epics/E09-notifications.md) | Notifications | lms_notification | partial (email/push) |
| [E10](../D02-epics/E10-ai.md) | AI authoring | lms_ai | done |
| [E11](../D02-epics/E11-mobile-api.md) | Mobile API | lms_mobile_api | done |
| [E12](../D02-epics/E12-analytics.md) | Analytics & reporting | lms_analytics | partial |
| [E13](../D02-epics/E13-content-tools.md) | Content tools & seeding | lms_content_tools | done |

## WBS scale
- 13 epics · ~28 module stories (one per module, [D03](../D03-stories/_index.md)) + cross-cutting · open tasks in [D04](../D04-tasks/_index.md).
- ID scheme: hierarchical (`E##` / `E##S##` / `E##S##T##`) — collision-free.
- Code map: [D07](../D07-code-map/README.md) (one file per module).

## Coverage note
As-built modules are represented as `resolved`/`done` stories. The **remaining build work** = the open risks (secrets→Key, payment webhooks, badge awarding, test baseline, email/push, paypal dedup) — tracked as `todo`/`blocked` tasks in D04 and risks in [G02](../../G-Governance/G02-risk-register/_index.md).
