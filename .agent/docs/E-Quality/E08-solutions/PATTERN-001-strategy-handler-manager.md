# PATTERN-001 — Strategy Handler Manager

| Field | Value |
|-------|-------|
| ID | PATTERN-001 |
| Source Module | `lms_quiz` — `GradingStrategyFactory` |
| Source Files | `src/Grading/GradingStrategyFactory.php`, `src/Grading/Strategy/*.php` |
| Pattern Type | Strategy Pattern (GoF) |
| Problem Solved | Grading logic khác nhau cho 8 loại câu hỏi |

## Bối cảnh

EverLMS hỗ trợ 8 bundle của `lms_question`: `single_choice`, `multiple_choice`, `true_false`, `fill_blank`, `short_answer`, `ordering`, `matching`, `essay`. Mỗi loại có cách tính điểm riêng (partial scoring, case sensitivity, sequence scoring, v.v.).

Nếu dùng `if/elseif` hoặc `switch` trong một service duy nhất, code sẽ vi phạm Open/Closed Principle và rất khó test độc lập từng handler.

## Giải pháp Thực tế

```
lms_quiz/src/Grading/
├── QuestionGradingInterface.php     # Định nghĩa contract: grade(array $response): float
├── GradingStrategyFactory.php       # Factory: bundle → strategy instance
└── Strategy/
    ├── SingleChoiceStrategy.php
    ├── MultipleChoiceStrategy.php
    ├── TrueFalseStrategy.php
    ├── FillBlankStrategy.php
    ├── ShortAnswerStrategy.php
    ├── OrderingStrategy.php
    ├── MatchingStrategy.php
    └── EssayStrategy.php
```

`GradingStrategyFactory::getStrategy(string $bundle)` dùng `match()` expression để map bundle name → strategy. Unknown bundle trả về `null` (graceful degradation).

## Khi nào áp dụng

- Có nhiều "loại" của cùng một hành vi, khác nhau về logic nhưng cùng interface
- Muốn thêm loại mới mà không sửa code hiện tại (Open/Closed)
- Cần unit test độc lập từng variant

## Cách mở rộng

1. Tạo class mới implement `QuestionGradingInterface`
2. Thêm case vào `GradingStrategyFactory::getStrategy()` match expression
3. Thêm test case trong PHPUnit cho strategy mới

## Trade-offs

| Pro | Con |
|-----|-----|
| Mỗi strategy test độc lập | Factory cần update khi thêm type |
| Không vi phạm SRP | Nhiều file nhỏ |
| Dễ thêm type mới | Factory là static — không injectable |

## Bài học

- Static factory (`final class GradingStrategyFactory`) phù hợp khi strategy là stateless value objects
- Nếu strategy cần inject service, chuyển sang Drupal Plugin Manager (xem PATTERN-002)

## Áp dụng trong Issues

- TEST-002 (E03) cover toàn bộ 8 strategies
- Pattern này tránh được god-class anti-pattern trong G4-Architecture category
