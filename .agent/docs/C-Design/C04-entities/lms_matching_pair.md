# C04 Entity - lms_matching_pair

**Display name:** Matching Pair
**Owning module(s):** [lms_question](../C05-modules/lms_question.md)
**Mode:** as-built (Content Entity)

## Purpose
Một cặp left/right cho question dạng `matching`.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| field_left_item | left side (prompt) |
| field_right_item | right side (match) |
| weight | display order |

## Relationships
- Được tham chiếu bởi `lms_question.field_matching_pairs`.

## Validation rules
Nguồn: `web/modules/custom/lms_question/src/Entity/MatchingPair.php`.
- `field_left_item`: bắt buộc, tối đa 512 ký tự.
- `field_right_item`: bắt buộc, tối đa 512 ký tự.
- `weight`: integer, mặc định 0, không bắt buộc.

## Data invariants
- Mỗi matching pair thuộc về đúng 1 question (bundle `matching`) qua `lms_question.field_matching_pairs`, quan hệ one-way tương tự `lms_answer`.
- Không có ràng buộc unique nào giữa các `field_left_item`/`field_right_item` trong cùng 1 question — có thể tạo 2 pair trùng nội dung left/right (gap as-built, phụ thuộc kỷ luật biên tập).

## Calculations & derived values
- Entity tự thân không có trường tính toán.
- Được chấm điểm gián tiếp bởi `GradingStrategyFactory::getStrategy('matching')->grade()` trong `lms_quiz/src/Controller/QuizController.php` khi user submit quiz — kết quả (đúng/sai từng cặp, điểm) ghi vào `lms_question_response.field_correct`/`field_score`, không ghi ngược lại `lms_matching_pair`.

## Editorial/data management
Được tạo inline cùng một matching question. Chấm điểm qua matching strategy trong `GradingStrategyFactory`. Xem [lms_question](lms_question.md).
