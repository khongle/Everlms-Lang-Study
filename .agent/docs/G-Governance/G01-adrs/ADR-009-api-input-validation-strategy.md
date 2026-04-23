# G01-ADR-009: API Input Validation Strategy (Per-Route DTO vs Global Middleware)

**Status:** PROPOSED
**Date:** 2026-04-16
**Decision Makers:** SA

**Open decision:** DEC-001 — chưa resolve, ADR này document trade-offs để SA quyết định.

---

## Context

EverLMS expose nhiều HTTP endpoints qua custom controllers:
- `ProgressApiController::markComplete()` — nhận `course_id`, `lesson_id`
- `QuizController::submitAnswer()` — nhận `quiz_id`, `question_id`, `answers[]`
- `PaymentController::processPayment()` — nhận `order_id`, `gateway`, `payment_details`
- v.v.

**Vấn đề hiện tại (RISK-001, P0):**

Validation đang **không nhất quán**:
- Một số endpoints chỉ kiểm tra null (`?? NULL`)
- Thiếu type validation: `course_id` có thể là string `"abc"` — không bị reject
- Thiếu authorization check trước khi load entity
- Thiếu CSRF protection trên một số POST endpoints

Ví dụ từ `ProgressApiController::markComplete()`:
```php
$course_id = $content['course_id'] ?? NULL;
$lesson_id = $content['lesson_id'] ?? NULL;
// Chỉ check null, không validate type, range, ownership
```

TASK-015 (backlog) xác định đây là P0 security issue.

**DEC-001 hỏi:** Nên dùng per-route validation DTO hay global middleware/filter?

---

## Decision (PROPOSED — chờ SA approval)

Chưa có quyết định cuối. ADR này trình bày hai phương án để SA chọn.

---

## Trade-off Analysis

### Option A: Per-Route Request DTO + Validator (RECOMMENDED by Agent)

Mỗi endpoint có DTO class riêng với constraints:

```php
// src/Dto/MarkCompleteRequest.php
final class MarkCompleteRequest {
  public function __construct(
    #[Assert\NotNull]
    #[Assert\Positive]
    public readonly int $course_id,
    #[Assert\NotNull]
    #[Assert\Positive]
    public readonly int $lesson_id,
  ) {}

  public static function fromRequest(Request $request): self {
    $data = json_decode($request->getContent(), TRUE);
    return new self(
      course_id: (int) ($data['course_id'] ?? 0),
      lesson_id: (int) ($data['lesson_id'] ?? 0),
    );
  }
}

// Controller
public function markComplete(Request $request): JsonResponse {
  $dto = MarkCompleteRequest::fromRequest($request);
  $errors = $this->validator->validate($dto);
  if (count($errors) > 0) {
    return new JsonResponse(['errors' => (string) $errors], 400);
  }
  // ... business logic
}
```

**Pros:**
- Validation logic gần code business — dễ trace và debug
- Mỗi endpoint có schema explicit, dễ document (OpenAPI generation khả thi)
- Không cần global infrastructure — hoạt động independent
- Type-safe: DTO properties có PHP type declarations
- Dễ test: DTO có thể validate độc lập

**Cons:**
- Boilerplate: mỗi endpoint cần một DTO class
- Consistency phụ thuộc vào discipline — dễ bị skip trong rush
- Không tự động apply — developer phải nhớ dùng DTO

**Estimated effort:** ~2 giờ/endpoint × 15 endpoints = ~30 giờ

---

### Option B: Global Symfony EventListener / Route Middleware

Một `KernelEvents::REQUEST` listener validate tất cả API routes theo config schema:

```yaml
# validation_schema.yml
routes:
  lms_progress.mark_complete:
    method: POST
    body:
      course_id: {type: integer, required: true, min: 1}
      lesson_id: {type: integer, required: true, min: 1}
  lms_quiz.submit:
    method: POST
    body:
      quiz_id: {type: integer, required: true}
      answers: {type: array, required: true}
```

```php
class ApiValidationListener implements EventSubscriberInterface {
  public function onKernelRequest(RequestEvent $event): void {
    $route = $event->getRequest()->attributes->get('_route');
    $schema = $this->schemaLoader->getSchema($route);
    if ($schema) {
      $this->validateAgainstSchema($event->getRequest(), $schema);
    }
  }
}
```

**Pros:**
- Centralized — một chỗ để audit tất cả validation rules
- Không cần sửa controller code
- Schema file có thể auto-generate API docs

**Cons:**
- YAML schema có thể out-of-sync với actual endpoint logic
- Drupal's event system có priority complexity — cần đảm bảo listener chạy trước route access check
- Validation error format phải chuẩn hóa across tất cả endpoints
- Khó handle nested/conditional validation (ví dụ: `payment_details` structure phụ thuộc vào `gateway` value)

**Estimated effort:** ~40 giờ cho infrastructure + schema cho 15 endpoints

---

### Option C: Drupal Form API Validation (áp dụng cho Form endpoints)

Dùng Form API `#element_validate` và `validateForm()` cho form submissions.

**Pros:** Drupal-native, CSRF tích hợp, Ajax compatible
**Cons:** Chỉ áp dụng cho HTML form submissions, không phải JSON API endpoints

**Verdict:** Bổ sung cho Options A/B, không thay thế.

---

## Agent Recommendation

**Option A (Per-Route DTO)** phù hợp hơn cho EverLMS vì:
1. Drupal không có built-in request validation middleware — Option B cần build infrastructure từ đầu
2. DTO pattern rõ ràng hơn về từng endpoint expect gì
3. Symfony Validator component đã available trong Drupal
4. Với 15 endpoints, boilerplate manageable

Có thể bổ sung: một `BaseApiController::validateDto()` helper method để giảm boilerplate.

---

## Consequences (nếu Option A được chọn)

### Positive
- Mọi API endpoint có explicit input contract
- RISK-001 (G02) được resolve: injection/XSS risk giảm đáng kể
- Cơ sở cho OpenAPI spec generation sau này

### Negative
- ~30 giờ refactor backlog (TASK-015)
- DTO classes thêm vào codebase (~15 files)

### Risks
- Nếu không enforce DTO usage → vẫn có endpoints thiếu validation
- Migration phải test từng endpoint để không break existing behavior

---

## Related

- **Open decision:** DEC-001 in A-Project-Information/A03-project-context.md
- **Risk:** RISK-001 (G02-risk-register.md, P0)
- **Task:** TASK-015 (backlog: validate all write endpoints)
- **Other ADRs:** ADR-001 (Drupal 11 monolith), ADR-002 (strict_types)
- **SA action required:** Chọn Option A, B, hoặc hybrid trước TASK-015 execution
