# C04 Entity - lms_question_response

**Display name:** Question Response
**Owning module(s):** [lms_quiz](../C05-modules/lms_quiz.md)
**Mode:** as-built (Content Entity)

## Purpose
Câu trả lời của learner cho một question trong một quiz attempt, cùng với kết quả chấm điểm (tự động hoặc giáo viên review).

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| field_question | ref → lms_question |
| field_quiz | ref → lms_quiz |
| field_answers | **multi** chosen answer refs |
| field_correct | graded correct/incorrect |
| field_score | points awarded |
| field_text_answer | free-text answer (essay/short) |
| field_pending_review | awaiting manual grading |
| field_teacher_feedback | reviewer feedback (formatted) |

## Relationships
- Thuộc về một `lms_quiz_result` (`field_responses`). Tham chiếu `lms_question` + `lms_quiz`.

## Validation rules
Nguồn: `web/modules/custom/lms_quiz/src/Entity/QuestionResponse.php`.
- `field_question`: bắt buộc, entity_reference tới `lms_question`.
- `field_quiz`: entity_reference tới `lms_quiz`, không bắt buộc (dù thực tế luôn được set khi engine tạo response — xem Calculations).
- `field_correct`: boolean, mặc định FALSE; `field_score`: decimal(5,2), mặc định 0; `field_pending_review`: boolean, mặc định FALSE.
- Không có `addConstraint()` tuỳ biến.

## Data invariants
- `field_pending_review = TRUE` về nghiệp vụ đi kèm `field_text_answer` có nội dung (bundle essay/short_answer chờ giáo viên chấm) và `field_score`/`field_correct` chưa có giá trị cuối cùng cho tới khi review — quan hệ này không được ràng buộc chéo ở entity level, chỉ được đảm bảo bởi luồng nghiệp vụ trong `GradingStrategyFactory`.
- `field_teacher_feedback` chỉ có ý nghĩa khi `field_pending_review` từng là TRUE (đã hoặc đang được review); không có ràng buộc ngăn nhập feedback cho response đã chấm tự động.

## Calculations & derived values
- Xác nhận trong `web/modules/custom/lms_quiz/src/Controller/QuizController.php`: khi submit quiz, với mỗi question, `GradingStrategyFactory::getStrategy($bundle)->grade($question, $user_submission)` trả về `is_correct`, `score`, `text_answer`, `pending_review`, `answer_ids` — các giá trị này được ghi trực tiếp vào `field_correct`, `field_score`, `field_text_answer`, `field_pending_review`, `field_answers` khi tạo `QuestionResponse`. Công thức chấm cụ thể (per-bundle) nằm trong từng grading strategy, khác nhau theo bundle (choice/true_false/fill_blank/matching/essay/ordering).

## Editorial/data management
Được quiz engine tạo khi submit (không do editor viết). Teacher feedback có thể chỉnh sửa bởi instructor cho các mục đang pending-review. Xem [WF-002](../C12-workflows/WF-002-quiz-attempt-and-grading.md).
