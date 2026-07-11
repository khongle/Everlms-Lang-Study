# C12 Workflow - WF-002 Quiz attempt & grading

**Object:** quiz-result

## Business source
[B04 WF-002](../../B-Requirements/B04-business-workflows/WF-002-quiz-attempt-and-grading.md)

## Modules
[lms_quiz](../C05-modules/lms_quiz.md), [lms_question](../C05-modules/lms_question.md), [lms_progress](../C05-modules/lms_progress.md)

## Related features (B03)
[F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md)

## State model
`lms_quiz_result`:
| State | Meaning |
| ----- | ------- |
| submitted | attempt submitted |
| in_review | essay/short-answer pending manual grade (`field_pending_review`) |
| approved_or_completed | graded; pass/fail set vs `field_threshold` |
| rejected_or_returned | failed attempt (may retry within `field_take_quiz_number`) |

## Result invariant
- `lms_quiz_result.field_result` là nguồn sự thật duy nhất (source of truth) cho state hiển thị với learner.
- Nếu bất kỳ `lms_question_response.field_pending_review` nào là true, kết quả quiz phải giữ nguyên `Pending`/`in_review` kể cả khi điểm objective tạm tính đã trên threshold.
- Chỉ kết quả `Passed` đã finalize mới được fire `QuizPassedEvent` cho WF-003 issue certificate.
- UI kết quả/lịch sử phải hiển thị các attempt đang pending tách riêng khỏi các attempt passed/failed, và gắn nhãn điểm là tạm tính (provisional) cho tới khi review xong.

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| submitted | in_review | có response cần chấm tay (essay/short-answer) |
| in_review | approved_or_completed | instructor chấm, điểm đạt threshold |
| in_review | rejected_or_returned | instructor chấm, điểm dưới threshold |
| rejected_or_returned | submitted | learner retry trong field_take_quiz_number |

## Permission model
- Initiator: learner đã enroll.
- Responsible (chấm tay): instructor chấm các bài essay đang pending.
- Approver: instructor xác nhận/ghi đè điểm số.

## As-built grading (round-2)
- `field_result` là entity reference tới một taxonomy term `quiz_results` (`Passed` / `Failed` / `Pending`), được set bởi `QuizController::submitQuiz`. Bất kỳ response nào có `field_pending_review = 1` đều buộc result term thành `Pending`, bất kể `field_score` tạm tính là bao nhiêu.
- **Manual grading** (bổ sung ở round-2): instructor chấm các response đang pending tại `lms_quiz.grade` → `/manage/quiz-results/{id}/grade` (`QuizGradeForm`). Access = `administer lms_course` HOẶC là course owner của quiz đó HOẶC nằm trong danh sách `field_instructor`. Form cho điểm từng response pending, xóa `field_pending_review`, tính lại phần trăm qua `GradingStrategyFactory::getMaxScore`, đặt lại result term thành `Passed`/`Failed`, và khi pass sẽ fire `QuizPassedEvent($result, $quiz, $resultOwner)` — event này mang theo **learner** (owner của result), không phải người chấm. CTA "Grade this submission" hiện trên trang result cho grader khi result đang Pending.
- **Chuỗi đã verify:** result 98 (maria/quiz 43, essay) → Pending → sabine (instructor) chấm → Passed 100% → certificate `FDB2B9BA40` được issue (WF-003), idempotent (chỉ một certificate cho mỗi learner/course).

## Notifications
Notification kết quả gửi learner; notification pending-review gửi instructor. Pass → fire `QuizPassedEvent` (WF-003).

## Round-2 QA evidence
Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/002-quiz-attempt-and-grading/`. Issue `WF-002-001` (thiếu UI chấm điểm) đã phát hiện + fix.
