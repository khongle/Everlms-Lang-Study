---
description: Tạo và chạy test cho Drupal — PHPUnit (unit/kernel) + Playwright (browser/E2E).
---

# /test - Tạo & Chạy Test

$ARGUMENTS

---

## Mục đích

Tạo test mới, chạy test hiện có, hoặc kiểm tra coverage cho dự án Drupal.

---

## Sub-commands

```
/test                    - Chạy tất cả tests
/test [file/feature]     - Tạo tests cho target cụ thể
/test phpunit            - Chạy PHPUnit tests
/test playwright         - Chạy Playwright browser tests
/test coverage           - Hiển thị test coverage
```

---

## Quy trình

### Tạo Test (khi user yêu cầu test cho feature/file)

1. **Phân tích code**
   - Xác định functions và methods cần test
   - Tìm edge cases
   - Phát hiện dependencies cần mock

2. **Xác định loại test**
   | Loại | Khi nào dùng | Framework |
   |---|---|---|
   | **Unit** | Logic thuần, không cần Drupal bootstrap | PHPUnit |
   | **Kernel** | Cần services, database, entity API | PHPUnit + Drupal kernel |
   | **Functional** | Cần full Drupal + browser simulation | PHPUnit `BrowserTestBase` |
   | **E2E/Browser** | Test UI flows, JS interactions, AJAX | Playwright |

3. **Viết tests**
   - Follow existing test patterns trong project
   - Dùng Arrange-Act-Assert pattern
   - Mock external dependencies

---

## PHPUnit Tests

### Chạy tests

// turbo-all

```bash
# Chạy tất cả PHPUnit tests cho custom modules
ddev exec vendor/bin/phpunit web/modules/custom

# Chạy test cho module cụ thể
ddev exec vendor/bin/phpunit web/modules/custom/linne_core

# Chạy test file cụ thể
ddev exec vendor/bin/phpunit web/modules/custom/linne_core/tests/src/Unit/ExampleTest.php
```

### Cấu trúc test file

```
web/modules/custom/[module]/
└── tests/
    └── src/
        ├── Unit/           # Unit tests
        │   └── ServiceTest.php
        ├── Kernel/         # Kernel tests
        │   └── EntityTest.php
        └── Functional/     # Functional tests
            └── FormTest.php
```

### Template PHPUnit

```php
<?php

declare(strict_types=1);

namespace Drupal\Tests\[module]\Unit;

use Drupal\Tests\UnitTestCase;

class ExampleServiceTest extends UnitTestCase {

  public function testCalculateTotal(): void {
    // Arrange
    $service = new ExampleService();

    // Act
    $result = $service->calculateTotal(100, 10);

    // Assert
    $this->assertEquals(90, $result);
  }

  public function testInvalidInput(): void {
    $this->expectException(\InvalidArgumentException::class);
    $service = new ExampleService();
    $service->calculateTotal(-1, 10);
  }

}
```

---

## Playwright Tests

### Trước khi chạy

Đọc skill Playwright: `.agent/skills/drupal_playwright/SKILL.md`

### Chạy tests

```bash
# Chạy tất cả Playwright tests
npx playwright test

# Chạy test file cụ thể
npx playwright test tests/[test-file].spec.js

# Chạy với UI mode (debug)
npx playwright test --ui
```

---

## Output Format

### Khi tạo tests

```markdown
## 🧪 Tests cho: [Target]

### Test Plan
| Test Case | Loại | Coverage |
|---|---|---|
| Should create entity | Unit | Happy path |
| Should reject invalid data | Unit | Validation |
| Should handle DB error | Kernel | Error case |
| Should display form correctly | E2E | UI flow |

### Files đã tạo
- `tests/src/Unit/[File]Test.php`
- `tests/[feature].spec.js` (Playwright)

Chạy với: `ddev exec vendor/bin/phpunit [path]`
```

### Khi chạy tests

```markdown
## 🧪 Test Results

✅ ServiceTest.php (5 passed)
✅ FormTest.php (3 passed)
❌ OrderTest.php (2 passed, 1 failed)

**Failed:**
  ✗ testCalculateDiscount
    Expected: 90
    Actual: 100

**Total:** 10 tests (9 passed, 1 failed)
```

---

## Ví dụ sử dụng

```
/test linne_order service
/test form validation cho product form
/test phpunit
/test playwright order creation flow
```

---

## Nguyên tắc

- **Test behavior, không test implementation**
- **Tên test mô tả rõ ràng** hành vi được test
- **Arrange-Act-Assert** pattern
- **Mock external dependencies** (API calls, file system)
- **Mỗi test case kiểm tra 1 behavior** (khi thực tế cho phép)
