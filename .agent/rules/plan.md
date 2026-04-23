---
trigger: always_on
description: Quy tắc planning, Socratic Gate, context optimization, và quy trình làm việc với tài liệu.
---

## 1. SOCRATIC GATE (HỎI TRƯỚC KHI LÀM)
Với yêu cầu **phức tạp** (multi-file, schema change, new module, refactor lớn):
- **BẮT BUỘC** hỏi ít nhất **2 câu** làm rõ scope, edge cases, hoặc ràng buộc **TRƯỚC KHI** bắt tay code.
- **Với yêu cầu mơ hồ**: hỏi Mục đích, Đối tượng sử dụng, và Phạm vi.
- **KHÔNG giả định** — nếu 1% chưa rõ, HỎI.
- **Ngoại lệ**: Bug fix đơn giản, sửa 1 file, hoặc user đã cung cấp spec chi tiết → có thể proceed trực tiếp.

## 2. QUY TRÌNH LÊN PLAN TRƯỚC KHI CODE
Khi User yêu cầu các câu lệnh mang tính chất lên kế hoạch như:
- "Lên kế hoạch", "Lên plan"
- "Tạo kế hoạch", "Tạo plan"
- "Lên dùm mình plan", "Lên dùm mình kế hoạch"

**BẮT BUỘC:** Chỉ được tạo file plan (vd: `implementation_plan.md`, `task.md`) và **TUYỆT ĐỐI KHÔNG ĐƯỢC CODE** bất kỳ code thực tế nào. Phải chờ User duyệt (LGTM) và cho phép chỉnh sửa xong xuôi thì mới được bắt đầu viết code.

## 3. ĐỌC DOCS TRƯỚC KHI PLAN
1. **Đọc ALL Design Docs**: Trước khi tạo plan hoặc checklist, đọc mọi file trong `.agent/docs/design/` và `.agent/docs/architectures/ui_page_map.md`.
2. **Cross-Reference Spec ↔ Code**: So sánh spec routes với `.routing.yml` và controllers hiện tại. Chỉ rõ gaps.
3. **Không bỏ qua Docs**: Dù task đơn giản, luôn verify với tài liệu hiện có.

## 4. CHECKLIST QUALITY
- **Granular Items**: Mỗi checklist item phải map 1:1 với requirement cụ thể (route, controller method, form).
- **Không vague**: Tránh "Product CRUD UI". Thay bằng liệt kê từng route và controller cụ thể.

## 5. IMPLEMENTATION RULES
- **Custom Routes Only**: Không link buttons/sidebar/actions đến Drupal admin pages (`/admin/*`). Luôn dùng custom routes theo `ui_page_map.md`.
- **Validate After Build**: Sau mỗi thay đổi SCSS/CSS, chạy `npm run build` và kiểm tra output.
- **Backup After Done**: Database backup (`/backup`) luôn là bước CUỐI CÙNG, sau khi implementation VÀ validation hoàn tất.

## 6. CONFLICT DETECTION & DOCS SYNC
- **Detect Conflicts**: Nếu code/plan xung đột với docs hiện tại → **thông báo user ngay** trước khi tiếp tục.
- **Update Docs**: Sau khi resolve conflict hoặc thay đổi khác spec, cập nhật doc files. Docs phải luôn là single source of truth.
- **Không Silently Diverge**: Không implement khác spec mà không giải thích và xin user approval.

## 7. CONTEXT OPTIMIZATION (ĐỌC CÓ CHỌN LỌC)
Khi tham gia dự án lớn với nhiều tài liệu:
- **KHÔNG** đọc tất cả files markdown trong `.agent/docs/` ngay từ đầu.
- **BẮT BUỘC** đọc `.agent/docs/INDEX.md` trước tiên.
- Chỉ `view_file` các file thực sự cần thiết dựa trên mô tả trong Index.
- Nếu file quá dài, dùng `grep_search` hoặc `view_content_chunk` thay vì đọc toàn bộ.
