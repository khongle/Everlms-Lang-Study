# F-006 Acceptance Criteria - Quiz & assessment engine

## Core criteria
1. **Given** một quiz có nhiều loại question khác nhau, **when** learner submit, **then** mỗi objective question được chấm điểm theo strategy của `GradingStrategyFactory` và một `lms_quiz_result` được lưu lại.
2. **Given** một attempt đã được chấm, **when** điểm số được tính, **then** pass/fail được quyết định dựa trên threshold của quiz và hiển thị cho learner.
3. **Given** cả 8 loại question, **when** được author, **then** mỗi loại render đúng và (với objective type) tự động chấm điểm; essay/short-answer được gắn cờ để review thủ công.
4. **Given** một quiz có prerequisite (`field_prerequisite_quizzes`), **when** prerequisite chưa được đáp ứng, **then** quiz bị khóa.
5. **Given** một attempt pass ở final quiz của course, **when** được lưu, **then** `QuizPassedEvent` được kích hoạt (feed cho WF-003).

## Evidence expected
- Modules: [lms_quiz](../../C-Design/C05-modules/lms_quiz.md), [lms_question](../../C-Design/C05-modules/lms_question.md).
- Test: per-strategy grading unit test (8 cái); pass-threshold + event-fire kernel test (E03).
