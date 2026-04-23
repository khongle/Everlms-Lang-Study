# TEST-002 — Quiz Grading (Auto-grade)

| Field | Value |
|-------|-------|
| ID | TEST-002 |
| Type | Unit + Integration |
| Priority | P0 — Must |
| Feature | Assessment / F-QUIZ |
| Units | U-016 (GradingStrategyFactory, 8 strategies), U-020 (Grading service) |
| Traceability | E2E-006 (E01), UT-01..UT-08 (test-plan.md) |
| Owner | Unassigned |

## Scope

Verify auto-grading logic cho 7 loại câu hỏi auto-gradeable (không bao gồm essay). Kiểm tra score calculation, partial scoring, và boundary values.

## Preconditions

- Quiz entity tồn tại, published
- Question entities của từng bundle đã có `correct_answer` configured
- `GradingStrategyFactory` inject đúng strategy theo bundle name

## Test Cases

### TC-002-A: Single choice — correct answer

**Given:** `single_choice` question với `correct = 'A'`  
**When:** `GradingStrategyFactory::getStrategy('single_choice')->grade(['A'])`  
**Then:** score = 1.0 (full points)

---

### TC-002-B: Multiple choice — partial scoring

**Given:** 4 options, correct = {A, C}; user chọn {A, B}  
**When:** `MultipleChoiceStrategy::grade(['A', 'B'])`  
**Then:** score = 0.5 (1 correct / 2 correct answers)

| Submission | Expected Score |
|------------|---------------|
| {A, C} (full correct) | 1.0 |
| {A} (partial) | 0.5 |
| {B, D} (all wrong) | 0.0 |
| {} (empty) | 0.0 |

---

### TC-002-C: Fill blank — case sensitivity

**Given:** `fill_blank` question, answer = "Newton", case_sensitive = true  
**When:** grade("newton"), grade("Newton"), grade("NEWTON")  
**Then:** 0.0, 1.0, 0.0

---

### TC-002-D: Ordering — full vs partial sequence

**Given:** correct order = [1, 2, 3, 4]  
**When:** submit [1, 2, 4, 3]  
**Then:** score < 1.0 (partial — implementation-defined formula)

---

### TC-002-E: Essay — flagged for manual grading

**Given:** `essay` question  
**When:** `EssayStrategy::grade($response)`  
**Then:**
- Returns `null` score (not auto-graded)
- `QuizResult` marked `requires_manual_grading = true`

---

### TC-002-F: Unknown bundle — graceful null

**Given:** `GradingStrategyFactory::getStrategy('unknown_bundle')`  
**Then:** returns `null`, no exception thrown

## Công cụ

- PHPUnit Unit tests (no DB)
- PHPUnit KernelTestBase cho integration với `QuizResult` persistence
