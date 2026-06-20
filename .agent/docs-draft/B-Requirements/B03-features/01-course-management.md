# Feature: Course Management

## 1. Tổng quan

Quản lý khoá học là core feature của EverLMS, bao gồm toàn bộ lifecycle từ tạo, chỉnh sửa, xuất bản, đến lưu trữ khoá học. Kiến trúc 3 cấp: **Course → Chapter → Lesson**.

---

## 2. Course (Khoá học)

### 2.1 Course Creation
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| C-01 | Tạo khoá học mới | Form tạo: title, slug, description (rich text), thumbnail, banner image |
| C-02 | Category assignment | Gắn khoá học vào 1 hoặc nhiều Category (taxonomy, multi-level) |
| C-03 | Pricing | Loại giá: Free, Paid (fixed price), Membership-only |
| C-04 | Instructor assignment | Gắn 1 hoặc nhiều instructor cho khoá học |
| C-05 | Course level | Beginner / Intermediate / Advanced (taxonomy) |
| C-06 | Course language | Ngôn ngữ giảng dạy (taxonomy) |
| C-07 | Course duration | Tổng thời lượng ước tính (auto-calculate hoặc manual) |
| C-08 | Prerequisites | Gắn khoá học prerequisite (entity reference) |
| C-09 | Tags | Gắn tags cho SEO & search (taxonomy) |
| C-10 | Course status | Draft → Published → Archived |
| C-11 | Bestseller flag | Boolean field, có thể bật/tắt bởi admin |
| C-12 | Featured course | Boolean field, hiển thị trên homepage |

### 2.2 Course Management
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CM-01 | Listing với filters | Filter by: status, category, instructor, level, price type |
| CM-02 | Bulk operations | Publish/Unpublish/Archive nhiều khoá học |
| CM-03 | Duplicate course | Clone course + chapters + lessons (không clone enrollment) |
| CM-04 | Course ordering | Drag-drop sắp xếp thứ tự hiển thị |
| CM-05 | Course analytics | Số enrollment, completion rate, revenue |

---

## 3. Chapter (Chương)

### 3.1 Chapter Creation
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CH-01 | Tạo chapter trong course | Title, description, ordering weight |
| CH-02 | Chapter prerequisites | Bắt buộc hoàn thành chapter trước để mở chapter sau (tuỳ chọn) |
| CH-03 | Chapter status | Published / Draft / Hidden |

### 3.2 Chapter Management
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CHM-01 | Drag-drop reorder | Sắp xếp thứ tự chapters trong course |
| CHM-02 | Inline editing | Sửa title/description trực tiếp từ course edit page |
| CHM-03 | Bulk delete/move | Xoá hoặc di chuyển chapters giữa courses |

---

## 4. Lesson (Bài học)

### 4.1 Lesson Creation
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| L-01 | Tạo lesson trong chapter | Title, content (rich text), ordering weight |
| L-02 | Lesson type | Video, Text, Quiz, H5P, SCORM, Mixed |
| L-03 | Media attachment | Upload files: PDF, slides, documents |
| L-04 | Lesson duration | Thời lượng bài học (minutes) |
| L-05 | Free preview | Flag cho phép xem trước lesson mà không cần mua khoá học |
| L-06 | Lesson prerequisites | Bắt buộc hoàn thành lesson trước (tuỳ chọn) |
| L-07 | Lesson status | Published / Draft |

### 4.2 Lesson Content Types
| Type | Mô tả |
|------|-------|
| **Video** | YouTube / Vimeo embed hoặc upload trực tiếp |
| **Text** | Rich text content với images & formatting |
| **H5P** | Interactive content embedded via H5P integration |
| **SCORM** | SCORM 1.2/2004 package embedded |
| **Quiz** | Liên kết quiz entity |
| **Mixed** | Kết hợp nhiều loại content trong 1 lesson |

### 4.3 Lesson Management
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| LM-01 | Drag-drop reorder | Sắp xếp thứ tự lessons trong chapter |
| LM-02 | Quick edit | Sửa title/type nhanh từ chapter view |
| LM-03 | Comment on lesson | Tính năng comment/discussion cho từng lesson |
| LM-04 | FAQ per lesson | Quản lý FAQ riêng cho từng lesson |

---

## 5. Category Management

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CAT-01 | Multi-level categories | Hỗ trợ cây phân cấp (parent → child) |
| CAT-02 | Category CRUD | Tạo / sửa / xoá / sắp xếp categories |
| CAT-03 | Category image | Thumbnail cho mỗi category |
| CAT-04 | Category description | Mô tả ngắn cho SEO & listing page |
| CAT-05 | Category slug | URL-friendly slug tự động hoặc custom |

---

## 6. Listing & Search

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| LS-01 | Course listing page | Grid/List view, pagination, sort options |
| LS-02 | Faceted search | Filter: category, level, price, rating, language |
| LS-03 | Full-text search | Tìm kiếm theo title, description, instructor name |
| LS-04 | Search suggestions | Autocomplete khi gõ |
| LS-05 | Bestseller section | Hiển thị khoá học bestseller trên listing |
| LS-06 | Related courses | Gợi ý khoá học liên quan dựa trên category/tags |

---

## 7. Learning Paths (Lộ trình học)

> Kết hợp nhiều khoá học theo một trình tự có cấu trúc, dẫn tới mục tiêu career/skill.

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| LP-01 | Learning path creation | Tạo lộ trình: title, description, target career/skill |
| LP-02 | Course sequence | Sắp xếp courses theo thứ tự bắt buộc |
| LP-03 | Prerequisite enforcement | Phải hoàn thành course trước mới mở course sau |
| LP-04 | Path progress | Track tiến độ tổng thể theo learning path |
| LP-05 | Path certificate | Cấp certificate khi hoàn thành toàn bộ path |
| LP-06 | Path listing | Trang listing các learning paths |
| LP-07 | Personalized path | Learner chọn path phù hợp với mục tiêu (placement test optional) |

---

## 8. Content Scheduling & Lesson Locking

> Kiểm soát khi nào content được available và điều kiện unlock.

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CS-01 | Scheduled availability | Hẹn giờ publish/unpublish lesson (start date + end date) |
| CS-02 | Drip content | Mở lesson theo frequency (e.g. 1 lesson/tuần sau enrollment) |
| CS-03 | Progress-based unlock | Mở lesson tiếp theo chỉ khi hoàn thành lesson trước |
| CS-04 | Quiz-based unlock | Mở lesson chỉ khi pass quiz trước đó |
| CS-05 | Date-based unlock | Mở lesson vào ngày cụ thể |
| CS-06 | Manual unlock | Admin/Instructor mở lesson thủ công cho specific users |

---

## 9. AI-Powered Course Creation

> Xem chi tiết → [14-ai-features.md](./14-ai-features.md)

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| AI-01 | AI Course Builder | Tạo toàn bộ course structure từ topic input |
| AI-02 | AI Content Assistant | Generate lesson content, descriptions, quizzes |
| AI-03 | Content duplication/reuse | Copy course structures cho similar training cần thiết |
