# Flow: Quiz Submission

**Trigger:** Enrolled user submits a quiz attempt.
**Outcome:** Quiz graded; `lms_quiz_result` created; lesson/chapter/course progress updated; certificate triggered if course completion condition met.
**Critical path?** Yes — core learning outcome flow.
**Owner:** hainn (modules: `lms_quiz`, `lms_question`, `lms_progress`, `lms_certificate`)

---

## Happy Path Steps

| # | Actor | Action | Implemented? | On Failure |
|---|-------|--------|-------------|-----------|
| 1 | Browser | User clicks "Submit Quiz" on quiz page | Yes | N/A |
| 2 | Browser | POST responses array to quiz submission controller | Yes (`lms_quiz`) | Validation error shown |
| 3 | QuizController | Validate user is enrolled in parent course | Assumed | Redirect to course page |
| 4 | GradingStrategyFactory | Select grading strategy per question type (MC, TF, Fill, Match, Order, Essay) | Yes (`lms_quiz`) | Log error; question skipped |
| 5 | GradingStrategy | Score each `QuestionResponse` against correct answer | Yes (8 strategies implemented) | 0 score for failed question |
| 6 | QuizManager | Aggregate scores → compute total score, pass/fail | Yes | Default to fail |
| 7 | lms_quiz | Create `lms_quiz_result` entity (score, passed, answers) | Yes | Show error to user |
| 8 | lms_progress | Update `lms_user_lesson` progress (lesson = quiz lesson) | Assumed | Progress stuck |
| 9 | lms_progress | Cascade: recalculate `lms_user_chapter` completion % | Assumed | Chapter progress stuck |
| 10 | lms_progress | Cascade: recalculate `lms_user_course` completion % | Assumed | Course progress stuck |
| 11 | lms_quiz | Dispatch `QuizPassedEvent` if `passed === true` | Yes (`QuizPassedEvent`) | Event not fired → certificate not triggered |
| 12 | CertificateEventListener | Listen on `lms_quiz.passed` → check if all quizzes passed for course | Yes (`lms_certificate`) | Certificate not issued |
| 13 | CertificateGenerator | Generate certificate PDF via dompdf | Yes | Log error; certificate not issued |
| 14 | CertificateManager | Create `lms_user_certificate` entity with unique `field_certificate_number` | Yes | Duplicate prevention needed |
| 15 | lms_notification | Dispatch `CertificateIssuedEvent` → send email with certificate | Yes (event) | Log + retry |
| 16 | Browser | Show quiz result page with score and pass/fail | Yes | Show generic error page |

---

## Grading Strategies

| Question Type | Strategy Class | Implemented? |
|--------------|---------------|-------------|
| Multiple Choice | `MultipleChoiceStrategy` | Yes |
| True/False | `TrueFalseStrategy` | Yes |
| Matching | `MatchingStrategy` | Yes |
| Ordering | `OrderingStrategy` | Yes |
| Single Choice | Assumed (same as MC) | Assumed |
| Fill in Blank | Assumed | Assumed |
| Short Answer | Assumed | Assumed |
| Essay | Assumed (manual grading) | Assumed |

---

## Key Entities Created / Updated

| Entity | Action |
|--------|--------|
| `lms_question_response` | Created per question (stores user answer + score) |
| `lms_quiz_result` | Created (aggregated score, pass/fail) |
| `lms_user_lesson` | Updated (lesson completed) |
| `lms_user_chapter` | Updated (chapter completion %) |
| `lms_user_course` | Updated (course completion %) |
| `lms_user_certificate` | Created (if course completion threshold met) |

---

## Failure Modes

| Failure | Impact | Recovery |
|---------|--------|---------|
| DB write fails on quiz result | User sees error; re-submit possible | Duplicate result on re-submit (no idempotency guard) |
| Progress cascade fails | Quiz result recorded but progress not updated | Manual admin recalculation needed |
| QuizPassedEvent not fired | Quiz passed but certificate not triggered | Certificate gap — admin can manually trigger regeneration |
| Certificate generation fails | Completion recorded but no PDF | Admin uses regenerate endpoint (`/workspace/certificates/{id}/regenerate`) |
