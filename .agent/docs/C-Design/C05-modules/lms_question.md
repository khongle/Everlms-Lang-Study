# lms_question — Question Bank Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_question` |
| Name | LMS Question |
| Layer | **Assessment** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base` |

## Mô tả

Module quản lý ngân hàng câu hỏi (question bank). Cung cấp 3 entity: `lms_question` (bundled — 8 types), `lms_answer` (options cho MC/SC), và `lms_matching_pair` (cặp ghép cho Matching). Module hoàn toàn data-layer, không có service hay route riêng.

---

## Provided Entities

### `lms_question` (Content Entity — Bundled)

| Attribute | Value |
|-----------|-------|
| Bundle entity | `QuestionType` |
| Form handler | `QuestionForm` |
| Access handler | `QuestionAccessControlHandler` |

8 bundles (từ taxonomy `lms_question_type`):
`multiple_choice`, `single_choice`, `true_false`, `fill_blank`, `essay`, `short_answer`, `ordering`, `matching`

### `lms_answer` (Content Entity)
Câu trả lời cho MC/SC questions: text, is_correct flag, question ref.

### `lms_matching_pair` (Content Entity)
Cặp ghép cho Matching questions: left_item, right_item, question ref.

---

## Provided Routes

Không có routing file.

---

## Provided Services

Không có services file.

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Không có template riêng. Questions được render inline trong `lms-quiz-take` template của `lms_quiz`.

---

## Related Units

- **C03** § Assessment Domain — entities Question, Answer, MatchingPair
- **C05** `02-assessment.md` — layer overview
- `lms_quiz.md` — consumer của question bank
- `lms_base.md` — taxonomy `lms_question_type` defines bundles

---

## Known Issues / Debt

- Question ordering (drag-drop trong quiz builder) chưa có service riêng — logic nằm rải rác.
- Không có question versioning — khi edit câu hỏi sau khi đã có QuizResult, kết quả cũ có thể không còn accurate.
