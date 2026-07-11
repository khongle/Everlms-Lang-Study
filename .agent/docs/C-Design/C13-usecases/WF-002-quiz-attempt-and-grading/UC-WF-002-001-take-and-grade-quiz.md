---
usecase_id: UC-WF-002-001
workflow: WF-002-quiz-attempt-and-grading
status: as-built
primary_actor: Student
modules: [lms_quiz, lms_question, lms_progress]
processes: [quiz-attempt-and-grading]
---

# UC-WF-002-001 - Take and grade a quiz

## Intent
Một learner làm một quiz và nhận được một kết quả state đáng tin cậy: pass/fail chấm tự động khi mọi câu hỏi đều objective, hoặc pending review khi có câu hỏi cần instructor chấm tay.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-002 |
| Business flow | attempt → grade → result/review → finalized result → (pass) event |
| Modules | lms_quiz, lms_question, lms_progress |
| Related features | F-006 |
| B04 source | WF-002 |

## Actors
Student (primary); instructor (chấm tay cho essay).

## Preconditions
Đã enroll; đủ prerequisite; còn lượt attempt.

## Trigger
Learner submit quiz.

## Main success scenario
1. Mở quiz (P020); trả lời câu hỏi (8 loại).
2. Submit → `GradingStrategyFactory` chấm các câu objective.
3. `lms_quiz_result` + `lms_question_response` được lưu; cờ pending-review được lưu theo từng response.
4. Nếu không cần manual review, kết quả được finalize thành Passed/Failed dựa trên `field_threshold`.
5. Nếu cần manual review, kết quả giữ nguyên Pending/In review và điểm hiển thị ở dạng tạm tính (provisional).
6. Khi một final quiz được finalize là pass → `QuizPassedEvent` (WF-003).

## Alternate and exception flows
- Hết thời gian → tự động submit.
- Essay → chờ instructor chấm tay.
- Fail → cho retry nếu còn lượt attempt.

## State transitions (evidence)
- `submitted` → `in_review`: có response essay/short-answer cần chấm tay.
- `in_review` → `approved_or_completed`: instructor chấm, điểm đạt threshold.
- `in_review` → `rejected_or_returned`: instructor chấm, điểm dưới threshold.
- `rejected_or_returned` → `submitted`: learner retry trong field_take_quiz_number.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Quiz take | App | /quiz/{quiz}/take |
| Quiz result | App | /quiz-result/{r} |
| Grade submission (instructor) | Manage | /manage/quiz-results/{r}/grade |

## Supporting pages
Sau khi submit, kết quả từng attempt hiển thị tại Quiz result (P021); learner xem lại toàn bộ lịch sử attempt tại My quiz results (P022).

## Acceptance and test contract
- Grading đúng theo từng strategy; pass/fail theo threshold chỉ áp dụng sau khi result đã finalize; attempt đang pending không được tính là passed; event chỉ fire khi đã finalize pass (B05 F-006).
