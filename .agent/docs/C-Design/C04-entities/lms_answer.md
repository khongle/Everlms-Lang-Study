# C04 Entity - lms_answer

**Display name:** Answer (choice option)
**Owning module(s):** [lms_question](../C05-modules/lms_question.md)
**Mode:** as-built (Content Entity)

## Purpose
Một answer/choice option có thể chọn, thuộc về question dạng choice (single/multiple choice, true/false).

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | option label |
| field_body | formatted option text |
| field_correct | whether this option is correct |

## Relationships
- Được tham chiếu bởi `lms_question.field_choices` / `field_correct_answers`.

## Validation rules
Nguồn: `web/modules/custom/lms_question/src/Entity/Answer.php`.
- `name` (Answer Text): bắt buộc (`setRequired(TRUE)`), tối đa 1024 ký tự.
- `field_body`, `field_correct`: không bắt buộc; `field_correct` là boolean, mặc định FALSE.
- Không có `addConstraint()` tuỳ biến nào ở entity này (grep toàn repo không thấy `addConstraint` trên bất kỳ entity content nào) — validation chỉ dừng ở mức field type/required/max_length do Drupal core field system tự áp.

## Data invariants
- Một answer luôn thuộc về đúng 1 question (bundle choice/true_false) qua `lms_question.field_choices`; quan hệ là one-way (answer không tự lưu `field_question` ngược lại).
- Với question dạng single-choice, nghiệp vụ kỳ vọng đúng 1 answer có `field_correct = TRUE`; với multiple-choice có thể ≥1. Ràng buộc này **không được enforce bằng code** (không có validate trong `QuestionForm`/`Answer` entity) — đây là gap as-built, phụ thuộc biên tập viên nhập đúng.

## Calculations & derived values
- Answer entity tự thân không có trường tính toán.
- `field_correct` của các answer được `lms_question` bundle `matching`/`choice` chấm điểm gián tiếp qua `GradingStrategyFactory::getStrategy($bundle)->grade()` (xem `lms_quiz/src/Controller/QuizController.php`), kết quả ghi vào `lms_question_response.field_correct`/`field_score`, không ghi ngược lại `lms_answer`.

## Editorial/data management
Được tạo inline cùng question qua question form (kiểu inline-entity). Cache được bubble lên question cha. Xem [lms_question](lms_question.md).
