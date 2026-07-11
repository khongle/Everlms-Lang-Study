# C04 Entity - lms_quiz_result

**Display name:** Quiz Result
**Owning module(s):** [lms_quiz](../C05-modules/lms_quiz.md)
**Mode:** as-built (Content Entity)

## Purpose
Một quiz attempt của learner: điểm tổng hợp, kết quả pass/fail, thời gian, và các response theo từng question.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | learner |
| field_quiz | ref → lms_quiz |
| field_score | total score |
| field_result | pass/fail (`quiz_results` taxonomy or status) |
| field_start_time, field_end_time | attempt timing |
| field_responses | **multi-ref → lms_question_response** |
| created | timestamp |

## Relationships
- N→1 `lms_quiz`, N→1 `user`; 1→N `lms_question_response`.
- Một kết quả pass sẽ đưa vào `QuizPassedEvent` → `lms_user_certificate` (WF-003) và có thể được tham chiếu từ `lms_user_certificate.field_quiz_result`.

## Validation rules
Nguồn: `web/modules/custom/lms_quiz/src/Entity/QuizResult.php`.
- `field_quiz`: bắt buộc, entity_reference tới `lms_quiz`.
- `field_score`: decimal(5,2), mặc định 0.
- `field_result`: entity_reference tới taxonomy `quiz_results` (bundle `quiz_results`), không bắt buộc ở tầng field-definition dù luôn được set bởi engine (xem Calculations).
- Không có `addConstraint()` tuỳ biến; không giới hạn `field_score` trong khoảng 0-100 ở code (gap as-built tương tự `lms_quiz.field_threshold`).

## Data invariants
- `field_end_time` phải ≥ `field_start_time` về nghiệp vụ, nhưng cặp field `timestamp` này **không được ràng buộc chéo** trong `baseFieldDefinitions()` (gap as-built).
- Một `lms_quiz_result` có `field_result = Passed` sẽ kích hoạt `QuizPassedEvent` dẫn tới issue `lms_user_certificate` (WF-003, đã ghi ở Relationships) — đây là invariant xuyên entity quan trọng: certificate issuance phụ thuộc trực tiếp vào giá trị `field_result` được engine set đúng.

## Calculations & derived values
- Xác nhận trong `web/modules/custom/lms_quiz/src/Controller/QuizController.php` (hàm submit quiz):
  - `$percentage = $max_score > 0 ? ($total_score / $max_score) * 100 : 0;` trong đó `$total_score`/`$max_score` là tổng `field_score`/`getMaxScore()` cộng dồn từ tất cả `lms_question_response` thuộc attempt.
  - `field_score` (của `lms_quiz_result`) = `$percentage`.
  - `field_result` = term `Passed` nếu `$percentage >= $lms_quiz->field_threshold`, ngược lại `Failed`; nếu có bất kỳ response nào `pending_review = TRUE` (câu hỏi essay/short_answer chưa được giáo viên chấm) thì `field_result = Pending` (ưu tiên cao nhất, ghi đè Passed/Failed).
  - `field_start_time`/`field_end_time` hiện được set gần đúng (`$now - 60` và `$now`) tại thời điểm submit, KHÔNG phải thời điểm thực bắt đầu làm bài — giá trị 60 giây là hằng số cứng trong code, không phản ánh thời gian làm bài thật (gap/hạn chế as-built đáng lưu ý cho phân tích thời lượng làm bài).

## Editorial/data management
Được engine tạo khi submit; không do editor viết. Được đọc trên learner dashboard + analytics. Xem [WF-002](../C12-workflows/WF-002-quiz-attempt-and-grading.md).
