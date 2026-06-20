# G01-ADR-005: Strategy Pattern cho Question Grading (8 loại câu hỏi)

**Status:** ACCEPTED
**Date:** 2026-03-08
**Decision Makers:** SA, Agent_Architect

---

## Context

EverLMS hỗ trợ 8 loại câu hỏi trong module `lms_quiz` và `lms_question`:

| Bundle | Tên | Cách chấm điểm |
|--------|-----|----------------|
| `single_choice` | Single Choice | Đúng 1 đáp án duy nhất |
| `multiple_choice` | Multiple Choice | Đúng tất cả đáp án đúng |
| `true_false` | True/False | Boolean đơn giản |
| `fill_blank` | Fill in the Blank | So sánh text (normalize) |
| `short_answer` | Short Answer | Partial match / pending review |
| `ordering` | Ordering | Thứ tự array |
| `matching` | Matching | Mapping pairs |
| `essay` | Essay | Luôn `pending_review = true` |

Cần thiết kế grading engine:
1. Mở rộng dễ dàng khi thêm loại câu hỏi mới
2. Không dùng `switch/if-else` dài trong controller
3. Nhất quán interface: mọi loại đều trả về cùng format result

---

## Decision

Áp dụng **Strategy Pattern** với `QuestionGradingInterface` và `GradingStrategyFactory`:

**Interface** (`lms_quiz/src/Grading/QuestionGradingInterface.php`):
```php
interface QuestionGradingInterface {
  public function grade(Question $question, mixed $user_submission): array;
  public function getMaxScore(Question $question): float;
}
```

**Factory** (`lms_quiz/src/Grading/GradingStrategyFactory.php`):
```php
final class GradingStrategyFactory {
  public static function getStrategy(string $bundle): ?QuestionGradingInterface {
    return match ($bundle) {
      'single_choice'   => new SingleChoiceStrategy(),
      'multiple_choice' => new MultipleChoiceStrategy(),
      'true_false'      => new TrueFalseStrategy(),
      'fill_blank'      => new FillBlankStrategy(),
      'short_answer'    => new ShortAnswerStrategy(),
      'ordering'        => new OrderingStrategy(),
      'matching'        => new MatchingStrategy(),
      'essay'           => new EssayStrategy(),
      default           => null,
    };
  }
}
```

**Standardized result array** từ `grade()`:
```php
[
  'is_correct'     => bool,
  'score'          => float,
  'text_answer'    => string,
  'answer_ids'     => array,
  'pending_review' => bool,
]
```

Note: Codebase hiện tại dùng `GradingStrategyFactory` (static factory) thay vì `QuestionHandlerManager` (Drupal Plugin manager). ADR này phản ánh implementation thực tế.

---

## Rationale

**Strategy pattern** phù hợp vì:
- Mỗi loại câu hỏi có thuật toán chấm điểm độc lập, không liên quan đến nhau
- Caller (QuizController) không cần biết loại câu hỏi — chỉ gọi `grade()`
- Dễ unit test từng strategy độc lập
- Thêm loại câu hỏi mới: chỉ thêm class mới implement interface + một dòng trong `match`

**PHP `match` expression** thay vì `switch`:
- Strict comparison (`===`)
- Expression-based, có thể dùng trong constructor
- Không có fall-through bug

---

### Options Considered

#### Option 1: Strategy Pattern với GradingStrategyFactory (CHOSEN)
- **Pros:** Mỗi strategy độc lập, dễ test, dễ thêm type mới, interface rõ ràng, PHP `match` type-safe
- **Cons:** Factory static method khó mock trong unit test (dùng `new` thay vì DI) — có thể refactor thành service sau
- **Fit:** Phù hợp với 8 types cố định, ít thay đổi

#### Option 2: Drupal Plugin Manager (QuestionHandlerManager) (REJECTED trong implementation)
- **Pros:** Third-party modules có thể đăng ký question type mới qua hook, Drupal-native extensibility
- **Cons:** Overhead lớn hơn (annotation, plugin discovery, cache), phức tạp hơn cần thiết cho 8 types nội bộ
- **Reason rejected:** Over-engineering — EverLMS không dự kiến third-party question types trong v1

#### Option 3: Monolithic if-else trong QuizController (REJECTED)
- **Pros:** Đơn giản nhất
- **Cons:** God method với 200+ dòng, không extensible, không testable từng type, khó maintain
- **Reason rejected:** Violates Single Responsibility Principle rõ ràng

---

## Consequences

### Positive
- Mỗi strategy có thể unit test hoàn toàn độc lập với mock `Question` entity
- Thêm loại câu hỏi mới: tạo class mới + 1 dòng trong `match` — không sửa logic cũ
- `pending_review = true` cho essay/short_answer cho phép manual grading workflow rõ ràng
- `getMaxScore()` cho phép tính percentage score chính xác cho partial credit

### Negative
- `GradingStrategyFactory::getStrategy()` là static — khó mock trong PHPUnit nếu không refactor
- Nếu câu hỏi không có strategy match → trả về `null`, caller phải handle null case

### Risks
- Thêm question type mới mà không cập nhật `GradingStrategyFactory` → grading trả về `null` silently — mitigate bằng unit test coverage cho factory

---

## Related

- **Design docs:** C01-architecture-decision.md (Design Patterns: Strategy)
- **Implementation:** `lms_quiz/src/Grading/` — `QuestionGradingInterface.php`, `GradingStrategyFactory.php`, `Strategy/*.php`
- **Other ADRs:** ADR-001 (Drupal 11 monolith)
- **Modules:** `lms_quiz`, `lms_question`
