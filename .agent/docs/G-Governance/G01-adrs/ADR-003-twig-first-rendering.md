# G01-ADR-003: Twig-First Rendering (Controller → #theme → Twig)

**Status:** ACCEPTED
**Date:** 2026-03-08
**Decision Makers:** SA, Agent_Architect

---

## Context

Drupal cho phép nhiều cách render HTML từ PHP code:
1. Return `['#markup' => '<div>...</div>']` từ controller (raw HTML trong PHP)
2. Return render array với `#theme` key → Twig template xử lý HTML
3. Return `Response` object trực tiếp với HTML string

Codebase EverLMS có 13+ controller files, 13+ Twig templates, và sẽ tăng lên. Nếu không có quy tắc rõ ràng, HTML sẽ bị rải rác trong cả PHP và Twig — khó maintain, khó test, và vi phạm separation of concerns.

A-Project-Information/A01-CLAUDE.md quy tắc #2 và #7 đã xác định hướng đi, ADR này ghi lại rationale chính thức.

---

## Decision

**Twig-First Rendering Pattern bắt buộc:**

- Controllers chỉ được chuẩn bị dữ liệu và trả về render array.
- HTML không được viết trong PHP code (`#markup` với raw HTML bị cấm).
- Mọi HTML output phải đi qua Twig template thông qua `#theme` key hoặc `#type` render elements.

Luồng chuẩn:
```
HTTP Request
  → Controller::method()
    → Load entities / services
    → Build data array (scalar values, entity objects, URLs)
    → return ['#theme' => 'lms_course_card', '#course' => $course, '#cache' => [...]]
  → Drupal Render Pipeline
    → hook_theme() maps 'lms_course_card' → templates/lms-course-card.html.twig
  → Twig template renders HTML
    → Output to browser
```

**Cho phép:**
- `#type` Drupal render elements (`form`, `table`, `link`, `html_tag`)
- `#markup` chỉ với plain text đã escaped (không phải HTML tags)

**Bị cấm:**
- `'#markup' => '<div class="course">...</div>'`
- `return new Response('<html>...</html>')` từ non-API controllers
- Heredoc HTML string trong PHP

---

## Rationale

**Separation of concerns:** PHP xử lý business logic, Twig xử lý presentation. Template designer (hoặc AI agent thao tác UI) không cần đọc PHP.

**Security:** Twig auto-escapes variables by default — `{{ course.title }}` an toàn hơn PHP string concatenation. `#markup` với raw HTML bypass Drupal's XSS filtering.

**Cachability:** Render arrays với `#cache` cho phép Drupal cache granular theo tags/contexts. Raw HTML response không cache được ở tầng entity cache.

**Theme override:** Khi dùng `#theme`, theme layer có thể override template mà không cần sửa controller PHP. Khi dùng `#markup`, không thể override.

---

### Options Considered

#### Option 1: Twig-First — Controller → #theme → Twig (CHOSEN)
- **Pros:** Clean separation of concerns, auto-escaping XSS, cacheable render arrays, theme-overridable, consistent pattern toàn dự án
- **Cons:** Cần đăng ký mỗi theme hook trong `hook_theme()`, thêm một bước indirection
- **Fit:** Đúng theo Drupal best practices, phù hợp với codebase scale

#### Option 2: `#markup` với HTML strings (REJECTED)
- **Pros:** Nhanh hơn cho prototype, ít file hơn
- **Cons:** XSS risk (developer phải tự escape), không cacheable granular, không theme-overridable, HTML rải trong PHP
- **Reason rejected:** Vi phạm security best practices; A08 rule #2 đã cấm

#### Option 3: Symfony Response với HTML string (REJECTED)
- **Pros:** Full control, bypass Drupal render pipeline
- **Cons:** Bypass cache system, không tích hợp Drupal blocks/regions, không có theme inheritance
- **Reason rejected:** Chỉ phù hợp cho API JSON endpoints, không phải HTML pages

---

## Consequences

### Positive
- Mọi template file dễ tìm tại `templates/` folder của module
- Theme có thể override bất kỳ template nào không cần sửa PHP
- Drupal cache system hoạt động chính xác với cache tags per entity
- Twig auto-escaping giảm XSS surface area
- AI agent và designer có thể làm việc với Twig độc lập với PHP logic

### Negative
- Mỗi `#theme` key cần khai báo trong `hook_theme()` — thêm ~5 dòng boilerplate per template
- Debug render arrays đôi khi phức tạp hơn raw HTML

### Risks
- Developer mới có thể dùng `#markup` do tiện — enforce bằng PHPCS custom sniff và PR review
- Template không tìm thấy lúc runtime nếu `hook_theme()` sai key — kiểm tra trong Drush cr và xem Drupal watchdog

---

## Related

- **Design docs:** C01-architecture-decision.md (Design Patterns: Twig-First)
- **Coding rules:** A-Project-Information/A01-CLAUDE.md rule #2 (No `#markup`), rule #7 (Twig-first)
- **Theme:** `web/themes/custom/lms_theme/`
- **Other ADRs:** ADR-004 (Bootstrap 5 dark theme), ADR-001 (Drupal 11)
