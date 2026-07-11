# F-006 - Quiz & assessment engine

**Status:** built
**Primary module:** [lms_quiz](../../C-Design/C05-modules/lms_quiz.md)
**Epic:** [E04 Assessment](../../D-Development/D02-epics/E04-assessment.md)
**Related workflows:** [WF-002 Quiz attempt & grading](../B04-business-workflows/WF-002-quiz-attempt-and-grading.md)
**Acceptance:** [B05 F-006](../B05-acceptance-criteria/F-006-quiz-and-assessment-engine.md)
**Source:** `web/modules/custom/lms_quiz/`, `lms_question/`

## Requirement
Instructor xây dựng quiz (`lms_quiz`) từ các question (`lms_question`) thuộc **8 type** (config bundle `lms_question_type`: single_choice, multiple_choice, true_false, short_answer, essay, fill_blank, matching, ordering). Learner làm một quiz (attempt); engine tự động chấm (auto-grade) các type khách quan qua `GradingStrategyFactory` (một strategy cho mỗi type), ghi nhận một `lms_quiz_result` kèm `lms_question_response` cho từng question, tính điểm số và áp dụng pass threshold. Quiz có thể có prerequisite (`field_prerequisite_quizzes`).

## Business value
Assessment xác thực việc học, gate certification, và là điểm khác biệt giữa một LMS thực thụ với một trang nội dung thông thường.

## Scope notes
- Các type khách quan được auto-grade; essay/short_answer có thể cần manual review.
- Pass một final quiz sẽ phát `QuizPassedEvent` → issuance certificate (F-007/WF-003).
- Sub-entity của question: `lms_answer`, `lms_matching_pair`; quiz result chứa `field_responses`.

## Cross references
- Design: [C04 lms_quiz](../../C-Design/C04-entities/lms_quiz.md), [C04 lms_question](../../C-Design/C04-entities/lms_question.md), [C04 lms_quiz_result](../../C-Design/C04-entities/lms_quiz_result.md), [C12 WF-002](../../C-Design/C12-workflows/WF-002-quiz-attempt-and-grading.md)
