# WF-002 - Quiz attempt & grading

## Business flow
Learner mở một quiz gắn với lesson/course. Hệ thống hiển thị các câu hỏi (8 loại) và learner nộp câu trả lời. Khi nộp bài, `GradingStrategyFactory` chọn chiến lược chấm điểm theo từng loại câu hỏi, tự động chấm các câu hỏi khách quan, lưu `lms_question_response` cho từng câu hỏi và một `lms_quiz_result` tổng thể, rồi so sánh điểm với ngưỡng đạt (pass threshold). Quiz khách quan trả kết quả ngay lập tức; các câu tự luận/trả lời ngắn có thể được đánh dấu để review thủ công. Khi pass quiz cuối của một course, hệ thống phát sự kiện `QuizPassedEvent`, được WF-003 tiêu thụ.

## Participating modules
[lms_quiz](../../C-Design/C05-modules/lms_quiz.md), [lms_question](../../C-Design/C05-modules/lms_question.md), [lms_progress](../../C-Design/C05-modules/lms_progress.md)

## Related features (B03)
[F-006](../B03-features/F-006-quiz-and-assessment-engine.md), [F-008](../B03-features/F-008-badges-and-gamification.md)

## Trigger
Learner nộp một lượt làm quiz (quiz attempt).

## Outcome
Các bản ghi `lms_quiz_result` + `lms_question_response` được lưu; điểm số và kết quả pass/fail được tính; sự kiện `QuizPassedEvent` được phát khi pass.

## Design link
[C12 WF-002](../../C-Design/C12-workflows/WF-002-quiz-attempt-and-grading.md)
