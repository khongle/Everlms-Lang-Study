# C04 Entity - lms_question

**Display name:** Question
**Owning module(s):** [lms_question](../C05-modules/lms_question.md)
**Mode:** as-built (Content Entity, **bundleable** via `type`, translatable)

## Purpose
Một question đơn lẻ. Field `type` chính là bundle (8 bundle `lms_question_type`): single_choice, multiple_choice, true_false, short_answer, essay, fill_blank, matching, ordering. Bundle quyết định field answer nào được áp dụng và grading strategy nào chạy.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid, type (bundle), langcode, default_langcode | keys + bundle + translation |
| name | question text/title |
| field_body | formatted question body |
| field_points | score weight |
| field_explanation | formatted answer explanation |
| field_difficulty | difficulty (`quiz_difficulty` taxonomy) |
| field_correct_answer | correct value (true_false / fill_blank) |
| field_case_sensitive | fill_blank matching flag |
| field_min_words, field_max_words | essay/short_answer constraints |
| field_choices | **multi-ref → lms_answer** (choice options) |
| field_correct_answers | **multi** correct option refs |
| field_matching_pairs | **multi-ref → lms_matching_pair** |
| field_ordered_items | **multi** ordered items |
| created, changed | timestamps |

## Relationships
- Là bundle của `lms_question_type` (config). Sub-entity: `lms_answer`, `lms_matching_pair`.
- Được tham chiếu bởi `lms_quiz.field_questions`; được trả lời qua `lms_question_response`.

## Validation rules
Nguồn: `web/modules/custom/lms_question/src/Entity/Question.php`.
- `name`: bắt buộc, translatable, tối đa 1024 ký tự.
- `field_points`: integer, mặc định 1 (trọng số điểm của question).
- `field_choices`, `field_matching_pairs`: `entity_reference` cardinality unlimited, KHÔNG `setRequired(TRUE)` dù bundle `single_choice`/`multiple_choice`/`matching` về logic cần ít nhất một giá trị.
- `field_correct_answer`, `field_correct_answers`, `field_ordered_items`, `field_min_words`, `field_max_words`: đều là field dùng chung cho tất cả 8 bundle (không phải field riêng theo bundle), không field nào bị `setRequired(TRUE)`.

## Data invariants
- Đây là entity bundleable (`type` = 1 trong 8 `lms_question_type`), nhưng **`baseFieldDefinitions()` không phân biệt field bắt buộc theo bundle** — ví dụ bundle `true_false` cần `field_correct_answer`, bundle `fill_blank` cần `field_correct_answers`, bundle `matching` cần `field_matching_pairs`, nhưng Drupal base field system không hỗ trợ required-theo-bundle ở tầng này; validate theo bundle (nếu có) phải nằm ở `QuestionForm`, chưa được xác nhận trong phạm vi các file đã đọc — gap as-built tiềm ẩn: có thể lưu question thiếu dữ liệu chấm điểm cho bundle của nó.
- `field_min_words` nên ≤ `field_max_words` khi cả hai > 0 (bundle `essay`), nhưng ràng buộc chéo này không được enforce trong code (gap as-built).

## Calculations & derived values
- `field_points` không tự tính — là trọng số nhập tay, được `GradingStrategyFactory::getStrategy($bundle)->getMaxScore($question)` đọc để tính `max_score` khi chấm quiz (xem `lms_quiz/src/Controller/QuizController.php`), nhưng bản thân `field_points` không bị ghi đè/tính lại bởi hệ thống.
- Không có trường nào khác trên `lms_question` được dẫn xuất tự động.

## Editorial/data management
Instructor viết question theo từng type qua form riêng cho từng type. Chấm điểm theo từng bundle qua `GradingStrategyFactory`. Toàn bộ nội dung nằm trong field. Xem [C16 sheet 01](../C16-item-solutions/01-entities.md).
