# G01-ADR-002: PHP 8.3 với Strict Types Bắt Buộc và Constructor Promotion

**Status:** ACCEPTED
**Date:** 2026-03-08
**Decision Makers:** SA, Agent_Architect

---

## Context

EverLMS cần một bộ quy tắc PHP nhất quán trên toàn bộ 26 custom modules để:
- Bắt lỗi type mismatch sớm tại compile-time thay vì runtime
- Giảm boilerplate code trong Dependency Injection (DI) constructors
- Đảm bảo code dễ đọc, maintain, và AI agent có thể hiểu ngữ nghĩa chính xác

PHP 8.3 là phiên bản PHP mới nhất được Drupal 11 support đầy đủ tại thời điểm khởi động dự án.

---

## Decision

**Hai quy tắc bắt buộc áp dụng cho TẤT CẢ file PHP trong `web/modules/custom/lms_*`:**

1. `declare(strict_types=1);` phải xuất hiện ở đầu mỗi file PHP (sau `<?php`).
2. Constructor Promotion (PHP 8.0+) bắt buộc sử dụng cho tất cả Dependency Injection constructors.

Bổ sung:
- Sử dụng `use` statements, không dùng inline FQN.
- `readonly` properties được phép trừ trong AJAX form classes (Drupal constraint).
- Named arguments và match expressions được khuyến khích dùng khi phù hợp.

Ví dụ chuẩn:
```php
<?php

declare(strict_types=1);

namespace Drupal\lms_progress\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

final class ProgressApiController extends ControllerBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AccountProxyInterface $currentUser,
  ) {}
}
```

---

## Rationale

**`declare(strict_types=1)`:** PHP mặc định thực hiện type coercion (ví dụ: string "123" tự động cast thành int). Trong codebase LMS với nhiều entity IDs và field values, điều này dễ gây silent bugs (ví dụ: so sánh `'0' == false` trả về `true`). Strict types bắt những lỗi này tại thời điểm gọi hàm.

**Constructor Promotion:** Giảm ~50% boilerplate trong DI constructors. Trước đây phải khai báo property, tham số, và assignment riêng biệt. Constructor promotion hợp nhất cả ba vào một dòng.

**PHP 8.3 cụ thể:** `readonly` classes, `json_validate()`, typed class constants — những tính năng này cải thiện type safety mà không breaking Drupal compatibility.

---

### Options Considered

#### Option 1: PHP 8.3 Strict Types + Constructor Promotion (CHOSEN)
- **Pros:** Type safety tối đa, code ngắn gọn, IDE và static analysis (PHPStan) hiệu quả hơn, AI agent dễ hiểu type contracts
- **Cons:** Không thể dùng `readonly` trong AJAX form classes (Drupal giới hạn), cần discipline từ mọi contributor
- **Fit:** Rất phù hợp — team nhỏ, dễ enforce qua code review

#### Option 2: PHP 8.1 với type hints tùy chọn (REJECTED)
- **Pros:** Ít breaking changes hơn với Drupal contrib modules
- **Cons:** Không có Constructor Promotion, thiếu `readonly` properties, type coercion silently che lỗi
- **Reason rejected:** PHP 8.1 EOL gần, Drupal 11 tối ưu cho 8.3+

#### Option 3: Không enforce strict_types (REJECTED)
- **Pros:** Code linh hoạt hơn, ít lỗi unexpected tại runtime
- **Cons:** Silent type coercion bugs, khó maintain, static analysis kém hiệu quả
- **Reason rejected:** Với 26 modules và 40+ entities, type safety là yêu cầu thiết yếu

---

## Consequences

### Positive
- PHPStan và Drupal coding standards checker bắt được nhiều lỗi hơn tại CI
- Constructor DI code giảm 40-50% dòng boilerplate
- Các method signatures rõ ràng hơn giúp AI agent hiểu đúng data flow
- Dễ refactor vì IDE biết exact types

### Negative
- Một số Drupal contrib modules có thể không compatible strict_types (không ảnh hưởng custom modules)
- `readonly` không được dùng trong AJAX form classes — developer phải nhớ exception này
- Cần thêm `declare(strict_types=1)` thủ công hoặc qua code generator

### Risks
- Developer mới hoặc AI agent có thể quên `declare(strict_types=1)` — mitigate bằng PHPCS rule và PR template checklist
- Ghi chú trong G02: **RISK-005** (static service calls bypass DI) vẫn tồn tại và cần TASK-018

---

## Related

- **Design docs:** C01-architecture-decision.md (Technology Decisions)
- **Coding rules:** A-Project-Information/A01-CLAUDE.md §Coding Rules
- **Other ADRs:** ADR-001 (Drupal 11 monolith)
- **Lint command:** `ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/`
