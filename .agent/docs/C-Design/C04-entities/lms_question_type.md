# C04 Entity - lms_question_type

**Display name:** Question Type
**Owning module(s):** [lms_question](../C05-modules/lms_question.md)
**Mode:** as-built (**Config Entity** — the bundle definition for `lms_question`)

## Purpose
Định nghĩa 8 bundle question-type. Là config (không phải content), các bundle này được ship trong `config/sync` (`lms_question.question_type.*.yml`) và ổn định qua các lần cài đặt.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id | bundle machine name (e.g. `single_choice`) |
| label | human label |
| description | type description |
| (config) | per-type rendering/grading hints |

## The 8 bundles
`single_choice`, `multiple_choice`, `true_false`, `short_answer`, `essay`, `fill_blank`, `matching`, `ordering`.

## Relationships
- Là bundle cho `lms_question` (`type`). Mỗi bundle ánh xạ tới một grading strategy trong `lms_quiz` `GradingStrategyFactory`.

## Validation rules
Nguồn: `web/modules/custom/lms_question/src/Entity/QuestionType.php`.
- Là **Config Entity** (`ConfigEntityBundleBase`), không dùng `BaseFieldDefinition`/`setRequired()` như content entity — validate machine name (`id`, pattern `[a-z0-9_]+`) và `label` bắt buộc do khung config entity của Drupal core tự áp (`EntityMachineNameConstraint` mặc định của core, không phải constraint tuỳ biến của module).
- `description`: string, mặc định rỗng (`protected string $description = ''`), không bắt buộc.
- `config_export`: chỉ gồm `id`, `label`, `description` — không có field cấu hình nào khác (ví dụ không có config lưu grading strategy class per bundle trong chính entity này).

## Data invariants
- Mỗi bundle id trong 8 giá trị liệt kê ở "The 8 bundles" phải khớp với một `case`/mapping trong `GradingStrategyFactory` (`lms_quiz`) để có strategy chấm điểm tương ứng — quan hệ này là ràng buộc ngầm giữa config (YAML) và code, **không được enforce bằng validate nào** (thêm 1 `lms_question_type` mới qua UI mà không có strategy tương ứng trong code sẽ khiến quiz không chấm được câu hỏi loại đó — gap as-built, đã ghi nhận sẵn ở Editorial/data management: "Type mới yêu cầu một grading strategy bằng code + config bundle").

## Calculations & derived values
- N/A — đây là config entity, không có runtime computed value; toàn bộ giá trị (`id`, `label`, `description`) là cấu hình tĩnh export/import qua `config/sync`, không có trường nào được tính toán khi chạy.

## Editorial/data management
Được quản lý dưới dạng configuration (admin config form / config sync), không phải bởi content editor. Type mới yêu cầu một grading strategy bằng code + config bundle. Xem [F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md).
