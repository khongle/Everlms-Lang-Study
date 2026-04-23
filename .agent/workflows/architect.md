---
description: Thiết kế kiến trúc chi tiết (File/Class/Function) cho một module Drupal trước khi code.
---

# /architect - Drupal Module Architecture Designer

$ARGUMENTS

*(Tự động đọc Plan và tạo doc cho toàn bộ module trong dự án)*

---

## Mục đích
Kích hoạt chế độ ARCHITECT để phân rã một requirement hoặc một module thành bản thiết kế kỹ thuật **cực kỳ chi tiết** đến từng File, Class, Interface, Method và tham số.
Việc này giúp loại bỏ hoàn toàn sự mơ hồ (hallucination) khi AI tiến hành viết code, đảm bảo tuân thủ chuẩn Dependency Injection và kiến trúc thư mục của Drupal.

---

## Quy trình Thực Hiện

Khi gọi `/architect`:

### 1. Quét Kế Hoạch Tổ Tổng Thể (Plan Scanning)
- Hệ thống tự động đọc các file `implementation_plan.md`, `task.md` và `modules_functions.md` để liệt kê dánh sách các custom module cần xây dựng (VD: `affhub_core`, `affhub_api`).

### 1b. Entity Schema Documentation — BẮT BUỘC
- Tạo/cập nhật file `entity_schema.md` trong `.agent/docs/architectures/`.
- Xác định mỗi entity là **Content Entity** hay **Configuration Entity**.
- Xác định mỗi entity có cần **bundles (Entity Types)** hay **single type** (không bundles).
- Liệt kê **TẤT CẢ Custom Content Entities** với đầy đủ:
  - Field name, machine name, base field type, required, mô tả
  - Entity class, module sở hữu
  - Entity Ownership + Relationships
  - Entity Relationship Diagram (Mermaid erDiagram)
  - Code structure mỗi entity cần (Entity class, ListBuilder, Forms, AccessControlHandler)
- Mỗi module architecture doc **PHẢI** bao gồm:
  - **Entity sở hữu:** `#[ContentEntityType]` attribute chi tiết, `baseFieldDefinitions()` fields
  - **Entity tham chiếu:** Entities module khác mà module này sử dụng

### 2. Thiết kế Cấu Trúc Thư Mục (Directory Tree)
- Tự động tạo thư mục `.agent/docs/architectures/`.
- Với mỗi module, vẽ sơ đồ cây thư mục chi tiết (`.info.yml`, `.routing.yml`, `.services.yml`, `src/Controller`...).

### 3. Phân Rã Thành Phần Cốt Lõi (Core Components Breakdown)
Liệt kê chi tiết TỪNG FILE một sẽ được tạo ra cho từng module:
- **File Path:** Đường dẫn vật lý (VD: `src/EventSubscriber/InternalWallSubscriber.php`).
- **Mục đích:** Giải thích rõ Service/Controller này đóng vai trò gì trong hệ thống, tại sao lại cần nó.
- **Class / Interface:** Tên Class và việc nó kế thừa/implement cái gì.
- **Dependency Injection:** Liệt kê các services sẽ được inject vào `__construct()`. Đảm bảo tuân thủ PHP 8.3 constructor property promotion (nhưng không dùng `readonly` cho FormBase).
- **Methods / Functions:** Liệt kê các hàm chính kèm theo:
  - **Chức năng:** Mô tả rõ hàm này nhận đầu vào là gì và xử lý logic gì bên trong.
  - **Tham số:** Kiểu dữ liệu và ý nghĩa của các tham số.
  - **Trị số trả về (Return Type):** Kiểu dữ liệu trả về (VD: `array`, `void`, `JsonResponse`).
- **Core Logic:** Viết mã giả (Pseudocode) khoảng 3-5 dòng mô tả phần lõi.

### 4. Thiết kế Configuration & Routing (YML Files)
- Phác thảo nội dung chính của `.routing.yml` (các route, controller map, permissions).
- Phác thảo `.services.yml` (khai báo service, tags cho event subscriber).

### 5. Tạo UI Page Map (`ui_page_map.md`) — BẮT BUỘC

Sau khi hoàn thành architecture docs cho từng module, **BẮT BUỘC** tạo/cập nhật file `ui_page_map.md` tổng hợp **tất cả pages** trong hệ thống.

**Cách thực hiện:**
1. Quét tất cả `.routing.yml` + Views configs (`config/optional/views.view.*.yml`) của mọi module.
2. Với mỗi route/page, ghi lại các cột **bắt buộc** và *tuỳ chọn*:

| Cột | Bắt buộc? | Mô tả |
|:---|:---|:---|
| **Path** | ✅ | URL route (VD: `/orders/{id}`) |
| **Handler Type** | ✅ | `Controller`, `Views`, `Form`, hoặc `Entity Form` |
| **Handler** | ✅ | Tên class/method cụ thể (VD: `OrderController::view`) |
| **Permission** | ✅ | Permission string |
| *Template* | ⬜ | Twig template chính — bổ sung sau khi implement |
| *SCSS* | ⬜ | File SCSS page-specific — bổ sung sau khi implement |
| *JS* | ⬜ | File JS nếu có — bổ sung sau khi implement |
| *Components* | ⬜ | Danh sách elements trên trang — bổ sung sau khi implement |

> **Lưu ý:** Template, SCSS, JS, Components **không bắt buộc** lúc architect ban đầu. Generate chung chung trước (Path + Handler + Permission), sau khi implement UI thì bổ sung chi tiết.

3. Ghi chú **Global Layout** nếu đã có (sidebar, header...).
4. Ghi chú **Shared UI Components** nếu đã có.

**Mục đích:** Đây là tài liệu giúp agent **không bị lạc** khi sửa UI — biết ngay page nào dùng gì, sửa file nào, ảnh hưởng file nào.

---

## Output Format & Lưu Trữ

Kết quả sẽ tự động lưu thành các file Markdown trong `.agent/docs/architectures/`:
- `entity_schema.md` — **📋 Entity Reference** (tất cả content types, fields, relationships, ER diagram)
- `[tên_module]_architecture.md` — Architecture chi tiết từng module (kèm entity details)
- `ui_page_map.md` — **🗺️ Bản đồ UI tổng hợp** (tất cả pages, handlers, components)

Lập trình viên chỉ cần xem qua, duyệt (LGTM) là System có thể tự động chạy tạo file code đồng loạt.

## Nguyên tắc cốt lõi:
- **Think in OOP:** Mọi logic phải được gói trong Class và Service, hạn chế tối đa viết hàm trong file `.module`. Lấy API First làm gốc.
- **Drupal Ready:** Dependency Injection tĩnh chuẩn PSR-4, Parameter Type Hinting.
- **Dựa hoàn toàn vào Plan:** Không tự tiện bịa thêm chức năng khác.
- **Conflict**: Phản hồi lại cho người dùng khi có bất cứ xung đột nào trong các tài liệu và dễ gây sai lầm khi đưa ra cấu trúc.
- **UI Page Map là BẮT BUỘC:** Không được skip bước tạo `ui_page_map.md`. Đây là "bản đồ" để agent biết đường khi sửa UI.