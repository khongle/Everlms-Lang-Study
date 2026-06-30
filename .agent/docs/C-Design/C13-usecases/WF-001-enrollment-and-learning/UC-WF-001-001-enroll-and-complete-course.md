---
usecase_id: UC-WF-001-001
workflow: WF-001-enrollment-and-learning
status: as-built
primary_actor: Student
modules: [lms_progress, lms_course, lms_lesson]
processes: [enrollment-and-learning]
---

# UC-WF-001-001 - Enroll and complete a course

## Intent
A learner enrolls in a course and works through it to completion.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-001 |
| Business flow | discover → enroll → learn → complete |
| Modules | lms_progress, lms_course, lms_lesson |
| Related features | F-004, F-005, F-003 |
| B04 source | WF-001 |

## Actors
Student (primary); system (progress).

## Preconditions
Authenticated; entitled (free / order / membership).

## Trigger
Learner clicks Enroll / Start learning.

## Main success scenario
1. Open course detail (P003).
2. Enroll (free) or complete checkout (WF-004).
3. `lms_user_course` created if the learner is not already enrolled in that course.
4. Open lesson player (P019); view content.
5. Mark lesson complete → `lms_user_lesson`; progress rolls up.
6. Repeat until all required lessons + final quiz (WF-002) done.
7. Course marked complete → certificate eligibility (WF-003).

## Alternate and exception flows
- Not entitled → gated, CTA to buy.
- Already enrolled → reuse existing enrollment; do not create duplicate learner/course rows.
- Drip-locked lesson → shown locked until `field_drip_days`.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Course detail | Public | /courses/{id} |
| Lesson player | App | /learn/{c}/{l} |
| Dashboard | App | /dashboard |

## Acceptance and test contract
- Enroll creates or reuses one active learner/course enrollment; complete updates lesson progress and course progress; learner dashboards render one course card per course; gating matrix passes (B05 F-004/F-005).
