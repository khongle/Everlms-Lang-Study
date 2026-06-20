# D03 — Unit Spec: QuizResult + QuestionResponse Entities

**Unit ID:** U-017
**Group:** G-ASSESSMENT
**Title:** QuizResult + QuestionResponse entities
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_quiz_result` and `lms_question_response` custom content entities that record a user's quiz attempt outcome and per-question answers respectively. Also implements the `QuizPassedEvent` dispatched when a passing result is saved.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_quiz/src/Entity/QuizResult.php` | QuizResult entity class |
| `web/modules/custom/lms_quiz/src/Entity/QuestionResponse.php` | QuestionResponse entity class |
| `web/modules/custom/lms_quiz/src/Event/QuizPassedEvent.php` | Event dispatched on passing result |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_quiz_result` (`QuizResult extends ContentEntityBase`)
- Key fields: `field_quiz` (ref: `lms_quiz`), `uid`, `field_score`, `field_passed` (boolean), `field_started_at`, `field_completed_at`, `field_attempt_number`

**Entity:** `lms_question_response` (`QuestionResponse extends ContentEntityBase`)
- Key fields: `field_quiz_result` (ref: `lms_quiz_result`), `field_question` (ref: `lms_question`), `field_user_answer`, `field_score`, `field_is_correct`, `field_needs_manual_grading`

**Event:** `QuizPassedEvent` — dispatched after a QuizResult is saved with `field_passed = TRUE`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Both entity schemas install correctly |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Result + response entities created on quiz submit |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_quiz_result` entity created on quiz submission
- [x] Per-question `lms_question_response` records created
- [x] `field_passed` computed from score vs. pass threshold
- [x] `QuizPassedEvent` dispatched when `field_passed = TRUE`
- [x] Attempt number incremented correctly on re-take

---

## Known Debt / Issues

None identified.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-013 | Quiz entity referenced by QuizResult |
| U-014 | Question entity referenced by QuestionResponse |
| U-016 | Grading strategies populate per-response scores |
| U-019 | Result view controller displays these entities |
| U-020 | Grading service writes these entities |
| U-035 | CertificateEventListener listens for QuizPassedEvent |

---

*End of U-017-quiz-result-entities.md*
