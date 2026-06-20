# C15-object-steps - Object Operation Steps

Lifecycle flows per business object — exactly 3 `.txt` flows each: `01-create-register`, `02-review-transition`, `03-monitor-export-archive`. Only this README is markdown. `02-review-transition` references the workflow's canonical states.

| Object | Folder | Module | Process evidence | Flow files |
| ------ | ------ | ------ | ---------------- | ---------- |
| Course | [course/](course/) | lms_course | course-authoring-and-publishing (WF-006) | 3 |
| Order | [order/](order/) | lms_order | order-checkout-and-payment (WF-004) | 3 |
| Certificate | [certificate/](certificate/) | lms_certificate | certificate-issuance (WF-003) | 3 |
| Membership | [membership/](membership/) | lms_membership | membership-subscription (WF-005) | 3 |
| Quiz result | [quiz-result/](quiz-result/) | lms_quiz | quiz-attempt-and-grading (WF-002) | 3 |

> Objects map to C04 entities with a lifecycle. Other entities (chapter/lesson/question/etc.) are sub-objects of these.
