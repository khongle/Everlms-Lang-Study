---
usecase_id: UC-003-02
workflow: WF-003-certificate-issuance
type: edge
---

# UC-003-02 — Student Re-Passes Quiz → Duplicate Certificate Prevented

**One-line purpose:** A student who already holds a certificate for a course passes the same course quiz again (e.g., after a retake or re-enroll). `CertificateManager::checkAndIssueCertificate()` detects the existing certificate and returns without creating a second one.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Has an existing `lms_user_certificate` for this course |
| `CertificateEventListener::onQuizPassed()` | Fires again on the second pass |
| `CertificateManager::checkAndIssueCertificate()` | Performs duplicate check via entity query |

---

## Preconditions

- All preconditions from UC-003-01 are met.
- A `lms_user_certificate` entity already exists with `uid={student}` and `field_course={course}`.
- The student has submitted and passed the quiz a second time (a new `lms_quiz_result` with Passed term exists).

---

## Trigger

`QuizPassedEvent` dispatched for the same student and the same course's quiz, for the second (or later) time.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** student passes the quiz again; `QuizPassedEvent` is dispatched |
| 2 | **When** `CertificateEventListener::onQuizPassed()` fires and traverses Quiz → Lesson → Chapter → Course |
| 3 | **Then** `CertificateManager::checkAndIssueCertificate($user, $course, $quiz_result)` is called |
| 4 | **When** the manager queries `lms_user_certificate` storage: `uid={student}` AND `field_course={course}` |
| 5 | **Then** the query returns the existing certificate entity |
| 6 | **Then** `checkAndIssueCertificate()` detects the existing certificate and returns `false` immediately without creating a new entity |
| 7 | **Then** no new `lms_user_certificate` entity is created |
| 8 | **Then** no new PDF file is generated |
| 9 | **Then** the existing certificate number, PDF, and issuance date are unchanged |
| 10 | **Then** event handler returns; student is redirected to the quiz result page as normal |

---

## Entities Mutated

None — the duplicate prevention path creates no entities and modifies no existing entities.

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Duplicate check | Entity query inside `checkAndIssueCertificate()` | Silent — no log written on detection of existing certificate |
| No new file | File system | `public://certificates/issued/` count does not increase |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-060](../../../B-Requirements/B05-acceptance-criteria/AC-06-certification-badges.md#ac-060-passing-a-quiz-twice-does-not-issue-a-duplicate-certificate) — No duplicate certificate on second pass | TEST-060 |

---

## Notes

- The duplicate check is `uid + field_course` — not `uid + field_quiz`. A student who passes quizzes from two different courses that point to the same certificate template will get separate certificates (one per course), which is correct.
- The check is entity-query based, not a DB unique constraint, so a race condition (two simultaneous passes in rapid succession) could theoretically create duplicates. No locking mechanism is present.
- The returned value from `checkAndIssueCertificate()` on detection of duplicate is `false`. The caller (`CertificateEventListener`) does not log this — silent suppression.
