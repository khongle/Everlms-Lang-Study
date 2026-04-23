---
description: Debug có hệ thống cho Drupal — phân chia Frontend (JS/CSS) & Backend (PHP/DB).
---

# /debug - Debug có hệ thống cho Drupal

Cú pháp: `/debug [be|fe]` (Mặc định: `be`)
- `/debug be`: Debug Backend (PHP, Database, Routing, Cache, Services, Logs).
- `/debug fe`: Debug Frontend (JS, CSS, SCSS, Twig, AJAX UI).

$ARGUMENTS

---

## Quy trình chung (Dành cho Agent)

1. Tùy theo tham số `be` hoặc `fe`, áp dụng các bước điều tra tương ứng dưới đây.
2. Hỏi user Error / Console log hiện tại nếu chưa rõ ràng.
3. Lập bài bản các giả thuyết (Hypothesis) theo thứ tự khả năng.
4. Kiểm tra từng giả thuyết bằng phương pháp loại trừ.
5. Xác định Root Cause, áp dụng Fix và đề xuất phòng tránh.

---

## 🖥 Nhánh 1: Backend Debug (`/debug be`)

Tập trung vào: PHP Error, Cache, Routing, Permissions, Services, Database.

### Công cụ BE tiêu biểu:
// turbo-all
```bash
# Clear cache (rebuild container/routing)
ddev drush cr

# Xem Watchdog logs gần nhất
ddev drush watchdog:show --severity=Error --count=10

# Kiểm tra service hoặc route tồn tại không
ddev drush devel:services --filter='service_name'
ddev drush router:match '/path'
```

### Giả thuyết BE thường gặp:
- File chưa đúng chuẩn PSR-4 hoặc Typehint sai.
- Thiếu/sai cấu hình trong `.routing.yml` hoặc `.services.yml`.
- Logic trong Hook / Event Subscriber bị sai / Override sai chỗ.
- Cache chưa được invalidate.

---

## 🎨 Nhánh 2: Frontend Debug (`/debug fe`)

Tập trung vào: JS Console Errors, AJAX/Batch Requests, CSS/SCSS Styling, Twig Render Variables, Libraries.

### Công cụ FE tiêu biểu:
1. **Source Code Check**: Đọc kỹ `*.libraries.yml` xem config chuẩn chưa.
2. **Twig Check**: Check `{{ attach_library() }}` hoặc array `$build['#attached']`.
3. **Build Check**:

// turbo-all
```bash
# Rebuild SCSS (Nên cd vào theme thích hợp trước, đây là lệnh mẫu chung)
cd web/themes/custom/linne_theme && npm run build

# Clear cache để Drupal nhận template/library mới
ddev drush cr
```

### Giả thuyết FE thường gặp:
- **JS Error**: Module JS không được wrap trong `Drupal.behaviors` nên bị lặp khi AJAX request, hoặc lỗi syntax ES6 chưa compile.
- **Dependencies**: Quên khai báo `core/jquery` hoặc `core/once` ở library.
- **AJAX**: AJAX Form/Link bị bể UI do wrapper ID không khớp giữa Form API và Twig, hoặc Event bắt sai.
- **CSS**: Sửa code SCSS nhưng quên chạy lệnh `npm run build`.

---

## Output Format

```markdown
## 🔍 Debug [BE|FE]: [Mô tả vấn đề]

### 1. Triệu chứng
[Mô tả hiện tượng - VD: PHP exception hoặc JS Console error]

### 2. Giả thuyết
1. ❓ [Nguyên nhân 1]
2. ❓ [Nguyên nhân 2]

### 3. Điều tra & Root Cause
🎯 **[Giải thích kết quả điều tra vì sao lỗi xảy ra]**

### 4. Sửa & Phòng tránh
- [Trình bày Code Fix]
- 🛡️ [Cách ngăn chặn lỗi tương tự về sau]
```
