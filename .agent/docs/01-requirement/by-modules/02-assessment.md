# Assessment Layer

> 6 modules for the polymorphic quiz engine.

---

## `lms_quiz` — Quiz Engine

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Quiz CRUD, take quiz form, submission flow, result calculation |
| **Entities** | `lms_quiz`, `lms_quiz_type` |
| **Dependencies** | `lms_base`, `lms_question`, `lms_answer` |
| **Services** | `lms_quiz.manager`, `lms_quiz.create`, `lms_quiz.submission_handler` |

### Key Services
- `lms_quiz.manager` — Quiz CRUD, settings management
- `lms_quiz.create` — Quiz creation form builder
- `lms_quiz.submission_handler` — Process submissions, calculate scores, trigger events

---

## `lms_question` — Question Management

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Question CRUD, polymorphic handlers (8 types), question bank |
| **Entities** | `lms_question`, `lms_question_type` |
| **Dependencies** | `lms_base` |
| **Services** | `lms_question.handler_manager` (Strategy Pattern) |

### Handler Architecture (Strategy Pattern)
```
QuestionHandlerManager
├── MultipleChoiceHandler
├── SingleChoiceHandler
├── TrueFalseHandler
├── FillBlankHandler
├── ShortAnswerHandler
├── MatchingHandler
├── OrderingHandler
└── EssayHandler
```

Each handler implements: `buildForm()`, `validateAnswer()`, `calculateScore()`, `getCorrectAnswer()`

---

## `lms_answer` — Answer Choices

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Answer choice entities for MC/SC questions |
| **Entities** | `lms_answer`, `lms_answer_type` |
| **Dependencies** | `lms_base` |

---

## `lms_matching_pair` — Matching Pairs

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Left-right pair data for matching questions |
| **Entities** | `lms_matching_pair` |
| **Dependencies** | `lms_base` |

---

## `lms_quiz_result` — Quiz Results

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Store quiz attempt outcomes, trigger certificate issuance |
| **Entities** | `lms_quiz_result` |
| **Dependencies** | `lms_base`, `lms_quiz` |

---

## `lms_question_response` — Granular Responses

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Per-question user response storage, grading queue |
| **Entities** | `lms_question_response` |
| **Dependencies** | `lms_base`, `lms_question` |
