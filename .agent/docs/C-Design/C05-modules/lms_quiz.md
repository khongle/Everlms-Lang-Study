# lms_quiz — Quiz Engine Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_quiz` |
| Name | LMS Quiz |
| Layer | **Assessment** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_question` |

## Mô tả

Quiz engine hoàn chỉnh: quiz taking, grading multi-strategy, event dispatch khi pass, và lưu kết quả. Module implement Strategy Pattern cho grading (8 loại câu hỏi × strategy riêng), Factory để resolve strategy, và Event `QuizPassedEvent` được dispatch để trigger certificate issuance.

---

## Provided Entities

### `lms_quiz` (Content Entity)
| Attribute | Value |
|-----------|-------|
| Base table | `lms_quiz` / `lms_quiz_field_data` |
| Translatable | Yes |
| Admin permission | `administer lms_course` |
| Entity keys | id, uuid, label (name), langcode, owner, published |
| Form handler | `QuizForm` |
| Access handler | `QuizAccessControlHandler` |

Key fields: field_questions (ref → lms_question), field_threshold (pass score %), field_time_limit, field_max_attempts.

### `lms_quiz_result` (Content Entity)
Stores one attempt: score, passed (bool), attempt_number, uid, quiz ref.

### `lms_question_response` (Content Entity)
Stores per-question answers within a result: question ref, user answer, correct flag, points_earned.

---

## Provided Routes

| Route ID | Path | Controller | Access |
|----------|------|-----------|--------|
| `lms_quiz.take` | `/quiz/{lms_quiz}/take` | `QuizController::takeQuiz` | logged in |
| `lms_quiz.submit` | `/quiz/{lms_quiz}/submit` [POST] | `QuizController::submitQuiz` | logged in |
| `lms_quiz.result` | `/quiz-result/{lms_quiz_result}` | `QuizController::viewResult` | logged in |
| `lms_quiz.my_results` | `/my-quiz-results` | `QuizController::myResults` | logged in |

---

## Provided Services

Không có services file. Grading services được implement trực tiếp trong `src/Grading/`.

---

## Provided Plugins

Không có Drupal plugin type. Module dùng Strategy Pattern thuần PHP:

| Class | Pattern | Purpose |
|-------|---------|---------|
| `GradingStrategyFactory` | Factory | Resolve grading strategy theo question type |
| `QuestionGradingInterface` | Interface | Contract cho tất cả strategies |
| `MultipleChoiceStrategy` | Strategy | Grading MC questions |
| `SingleChoiceStrategy` | Strategy | Grading SC questions |
| `TrueFalseStrategy` | Strategy | Grading T/F questions |
| `MatchingStrategy` | Strategy | Grading Matching questions |
| `FillBlankStrategy` | Strategy | Grading Fill-in-the-blank |
| `EssayStrategy` | Strategy | Essay — always 0 auto, cần manual grade |
| `ShortAnswerStrategy` | Strategy | Short answer — string match |
| `OrderingStrategy` | Strategy | Ordering questions |

**Event:**
- `QuizPassedEvent` — dispatched khi user pass quiz, được lắng nghe bởi `lms_certificate` module.

---

## Hook Implementations

| Hook | Purpose |
|------|---------|
| `hook_theme()` | Khai báo 3 templates: lms_quiz_take, lms_quiz_result, lms_my_quiz_results |

---

## Templates & Cache Strategy

| Template | Variables |
|----------|-----------|
| `lms-quiz-take` | quiz, questions[], time_limit |
| `lms-quiz-result` | quiz, result, score, result_label, responses[] |
| `lms-my-quiz-results` | results[] |

Quiz pages không được cache (user-specific + time-sensitive).

---

## Related Units

- **C03** § Assessment Domain — entities Quiz, QuizResult, QuestionResponse
- **C05** `02-assessment.md` — layer overview
- `lms_question.md` — question bank
- `lms_certificate.md` — nhận `QuizPassedEvent`
- `lms_progress.md` — lesson_view embed quiz

---

## Known Issues / Debt

- `EssayStrategy` luôn trả 0 điểm — không có workflow manual grading.
- Không có race condition protection khi submit: concurrent double-submit có thể tạo 2 QuizResult cho cùng 1 attempt.
- `field_max_attempts` enforcement cần check trong QuizController nhưng không rõ có guard đầy đủ.
- FIND: Quiz grading thiếu partial credit cho Matching questions (xem issues/).
