# C06-02 — Assessment API

> Modules: `lms_quiz` (routes + controllers), `lms_question` (không có routing.yml), `lms_answer` (không có routing.yml).
> Question/Answer CRUD được xử lý qua entity form system — không có JSON API riêng.

---

## Route Table

| Route ID | Path | Method | Controller | Auth | Response |
|----------|------|--------|-----------|------|----------|
| `lms_quiz.take` | `/quiz/{lms_quiz}/take` | GET | `QuizController::takeQuiz` | login | HTML |
| `lms_quiz.submit` | `/quiz/{lms_quiz}/submit` | POST | `QuizController::submitQuiz` | login | JSON/redirect |
| `lms_quiz.result` | `/quiz-result/{lms_quiz_result}` | GET | `QuizController::viewResult` | login | HTML |
| `lms_quiz.my_results` | `/my-quiz-results` | GET | `QuizController::myResults` | login | HTML |

---

## Route Chi Tiết

### GET `/quiz/{lms_quiz}/take`

**Auth:** `_user_is_logged_in: TRUE`
**Controller:** `Drupal\lms_quiz\Controller\QuizController::takeQuiz`
**Path param:** `lms_quiz` — entity upcast tới `lms_quiz` entity

**Business logic:**
- Kiểm tra user đã enroll course chứa quiz chưa.
- Kiểm tra `max_attempts` chưa đạt.
- Kiểm tra `time_limit` — nếu có, inject timer vào render.
- Trả Drupal render array → Twig template.

**Response (HTML render array):**
```
quiz entity fields: title, description, time_limit, question_count, max_attempts
questions[] với answer options per bundle type
```

---

### POST `/quiz/{lms_quiz}/submit`

**Auth:** `_user_is_logged_in: TRUE`
**Controller:** `Drupal\lms_quiz\Controller\QuizController::submitQuiz`
**Content-Type:** `application/json` hoặc `application/x-www-form-urlencoded`

**Request body (expected):**

```json
{
  "responses": {
    "{question_id}": "{answer_id_or_value}"
  },
  "time_taken": 300
}
```

**Response (JSON hoặc redirect):**

```json
{
  "result_id": 42,
  "score": 80.0,
  "passed": true,
  "redirect": "/quiz-result/42"
}
```

**Business logic:**
- Gọi GradingService per question bundle (8 handler types).
- Tạo `lms_quiz_result` và `lms_question_response` entities.
- Fire `QuizCompletedEvent` → trigger certificate issuance nếu pass.
- Cập nhật `lms_user_lesson` progress.

> **RISK-001 / RISK-002:** Không có input schema validation rõ ràng ở routing layer. Race condition nếu user submit nhiều lần đồng thời (concurrent double-submit).

---

### GET `/quiz-result/{lms_quiz_result}`

**Auth:** `_user_is_logged_in: TRUE`
**Path param:** `lms_quiz_result` — entity upcast

**Response (HTML):** Chi tiết kết quả: score, passed, per-question breakdown.

**Gap:** Không có access check rõ ràng rằng `lms_quiz_result` thuộc về current user — lộ nguy cơ đọc kết quả của người khác.

---

### GET `/my-quiz-results`

**Auth:** `_user_is_logged_in: TRUE`
**Response (HTML):** Danh sách tất cả quiz results của current user, paged.

---

## Gap: Question/Answer Management Routes

| Thiếu | Mô tả |
|-------|-------|
| `lms_question` routing.yml | Question CRUD chỉ qua entity form (admin); không có JSON endpoint |
| Quiz builder API | Không có route để add/remove/reorder questions vào quiz qua JSON |
| Question bank search | Không có API để search/filter question bank |
| Essay grading API | Instructor grade essay → chỉ có admin form, không có JSON endpoint |

> Các gaps này ảnh hưởng đến khả năng xây dựng SPA quiz builder. Nếu frontend cần, phải bổ sung routes trong sprint tiếp theo.

---

## Question Bundle Types

8 bundle types (không có routing riêng — xử lý trong submit controller):

| Bundle | Grading Strategy | Response format |
|--------|-----------------|----------------|
| `multiple_choice` | Multi-select → bitmask match | `answer_ids[]` |
| `single_choice` | Single select → exact match | `answer_id` |
| `true_false` | Boolean | `true` / `false` |
| `fill_blank` | String compare (case-insensitive) | `text` |
| `short_answer` | Manual grading (essay) | `text` |
| `matching` | Pair match | `{left_id: right_id}` |
| `ordering` | Sequence match | `ordered_ids[]` |
| `essay` | Manual grading | `text` |

---

## Known Issues

| Ref | Mô tả |
|-----|-------|
| RISK-001 | Submit endpoint thiếu server-side input schema validation |
| RISK-002 | Race condition: double-submit có thể tạo 2 quiz_result records |
| — | `quiz-result/{id}` thiếu ownership check — ai cũng có thể xem nếu biết ID |

---

*Module path: `web/modules/custom/lms_quiz/`, `web/modules/custom/lms_question/`*
