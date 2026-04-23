# AC-01 — Course Management

**Feature ID:** F-001
**Epic:** EPIC-A — Content Lifecycle
**Epic Owner:** Instructor / Admin
**Related Workflow:** [WF-006 — Course Creation](../B04-business-workflows/WF-006-course-creation.md), [WF-001 — Enrollment & Learning](../B04-business-workflows/WF-001-enrollment-learning.md)
**Feature Doc:** [B03 F-001 Course Management](../B03-features/01-course-management.md)

---

## Acceptance Criteria

### AC-A01 — Instructor tạo khóa học hoàn chỉnh

**Priority:** Must
**Given** Instructor đã đăng nhập với role Instructor hoặc Admin
**When** Instructor điền form course + thêm ≥1 Chapter + ≥1 Lesson và nhấn Save
**Then** Course record tạo thành công với status=draft trong ≤5 giây
**And** Toàn bộ flow không yêu cầu rời khỏi 1 luồng UI liên tục

### AC-A02 — Learner xem curriculum

**Priority:** Must
**Given** Course đã published và có chapters/lessons
**When** Bất kỳ visitor truy cập course detail page
**Then** Curriculum hiển thị đầy đủ chapters + lessons với trạng thái lock/unlock rõ ràng
**And** Free preview lessons hiển thị accessible; premium lessons hiển thị locked với CTA

### AC-A03 — Multi lesson types render đúng

**Priority:** Must
**Given** Enrolled student truy cập lesson
**When** Lesson type là Video, Text, H5P, SCORM, Quiz, hoặc Zoom
**Then** Mỗi type render đúng player/viewer tương ứng, không có lỗi console
**And** Nội dung hiển thị fully đúng format (video player với controls, SCORM trackable, v.v.)

### AC-A04 — Category management

**Priority:** Must
**Given** Admin ở trang Category management
**When** Admin tạo/sửa/xóa category với image và SEO slug
**Then** CRUD operations thành công; slug unique; image resize đúng dimensions
**And** Multi-level hierarchy (parent/child) lưu đúng và hiển thị đúng trong filter

### AC-A05 — Course search & filter

**Priority:** Must
**Given** User ở trang catalog
**When** User apply filters: category, level, price type, rating, language
**Then** Kết quả search trả về chính xác và nhanh (≤2s với dataset trung bình)
**And** Faceted filter count update theo selection; empty result hiển thị friendly message

### AC-A06 — Drag-drop reorder chapters/lessons

**Priority:** Must
**Given** Instructor ở course edit page
**When** Instructor kéo thả để reorder chapter hoặc lesson
**Then** Thứ tự mới persist vào DB ngay sau drop (không cần save thêm)
**And** Curriculum hiển thị cho student phản ánh thứ tự mới sau reload

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Course không có lesson | Enroll vẫn được nhưng progress = 0 |
| Lesson với empty content | Render gracefully, không crash |
| Category bị xóa khi có courses | Courses retain category ref hoặc unlink gracefully |
| Slug duplicate | Validation error ngay inline |
| Chapter với 0 lesson | Allowed (in-progress creation), không tính vào progress |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Performance | Course detail page load ≤2s (p95) |
| Performance | Curriculum API ≤500ms |
| SEO | Course URL sử dụng slug, canonical tag đúng |
| Accessibility | Curriculum list keyboard-navigable |
