---
trigger: always_on
description: Quy tắc Controller/Twig separation, SCSS workflow, JavaScript & Drupal.behaviors.
---

## 1. CONTROLLER & TWIG SEPARATION

### Controller chỉ chuẩn bị dữ liệu
- **TUYỆT ĐỐI KHÔNG** build cấu trúc HTML phức tạp (bảng, badge, card, dropdown...) bằng `#type => 'table'`, `#type => 'html_tag'`, hay `inline_template` lồng nhau trong Controller.
- **TUYỆT ĐỐI KHÔNG** đặt CSS classes (`btn-primary`, `mb-0`, `p-0`...) hoặc `#attributes` trong Controller. Đây là nhiệm vụ của Twig.

### Page-Level Template Pattern
- **BẮT BUỘC** mỗi trang (page) trả về **MỘT `#theme` duy nhất**.
- Controller chỉ chuẩn bị View Model (mảng dữ liệu thuần: strings, booleans, arrays), truyền vào Twig qua `hook_theme()`.
- **Twig template** xử lý toàn bộ: cấu trúc HTML, loop `{% for %}`, classes CSS, icons, badges.

### Refactor pattern
1. Tạo `hook_theme()` entry mới trong `.module` với variables chỉ chứa dữ liệu thuần
2. Tạo file `.html.twig` chứa toàn bộ HTML/CSS/Bootstrap
3. Controller chỉ return `['#theme' => '...', '#var1' => ..., '#var2' => ...]`

## 2. SCSS WORKFLOW

### Quy tắc file
- **KHÔNG** tạo file CSS rời ngoài thư mục `scss/`.
- Mọi style mới phải là SCSS partial (`_component.scss`) và import trong `main.scss`.

### Build SCSS
- **Nếu project dùng DDEV** → build SCSS **trong DDEV**, KHÔNG chạy `npm` trực tiếp trên host:
  ```bash
  # <temp_path> = /tmp/cmd_output.log (macOS/Linux) hoặc $env:TEMP\cmd_output.log (Windows)
  ddev exec bash -c "cd web/themes/custom/<theme_name> && npm run build" > <temp_path> 2>&1
  ```
  > Xem quy tắc temp path đầy đủ tại `command.md` → mục "Xác định temp path theo OS".
- Sau build → `ddev drush cr` để xoá cache.

### Cấu trúc SCSS
```
web/themes/custom/linne_theme/scss/
├── main.scss           ← Entry point, chỉ @import
├── _variables.scss     ← Design tokens
├── base/               ← Reset, typography
├── components/         ← Buttons, cards, badges, forms
├── layouts/            ← Grid, sidebar, page layout
└── pages/              ← Page-specific overrides
```

## 3. JAVASCRIPT & FRONTEND

### Pattern bắt buộc
- jQuery + `once()` + `Drupal.behaviors` pattern bắt buộc
- Dependencies: `core/jquery`, `core/once` trong `libraries.yml`
- Text cho user: dùng `Drupal.t()`
- Đọc `skills/drupal/v11/drupal-module/references/js-guide.md` trước khi viết JS

### Cấm
- **KHÔNG** HTML/CSS trong JS — chỉ UX enhancement (show/hide, keyboard trigger AJAX)
- **KHÔNG** `$.isArray()`, `$.trim()`, `$.isFunction()` (removed in jQuery 4)

### jQuery 4 migration
| Deprecated | Thay bằng |
|---|---|
| `$.isArray()` | `Array.isArray()` |
| `$.trim()` | `String.prototype.trim()` |
| `$.isFunction()` | `typeof fn === 'function'` |