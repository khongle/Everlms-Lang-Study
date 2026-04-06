# EverLMS — Seed Input Form

> Form cho phép admin nhập thông tin gốc về chương trình training. Dữ liệu này được lưu ra file JSON để AI Agent đọc và generate toàn bộ content.

## 1. Tổng quan

### Mục đích
- Thu thập đầy đủ thông tin cần thiết để AI có thể generate content chất lượng
- Lưu thông tin ra file JSON tại vị trí cố định để AI Agent (Claude Code, Gemini) có thể đọc trực tiếp
- Cho phép chỉnh sửa và re-generate nhiều lần

### Route & Menu
- **Route:** `/admin/everlms/content-tools/seed-input`
- **Menu parent:** Content Tools
- **Permission:** `administer everlms content tools`

---

## 2. Cấu trúc Form

Form chia thành **6 sections** dạng vertical tabs hoặc accordion:

### Section 1: Thông tin tổ chức

| Field | Type | Required | Mô tả | Ví dụ |
|-------|------|----------|-------|-------|
| Tên tổ chức / Công ty | textfield | ✅ | Tên đầy đủ | "Công ty TNHH ABC Tech" |
| Tên viết tắt | textfield | | Tên ngắn dùng trong content | "ABC Tech" |
| Lĩnh vực hoạt động | textfield | ✅ | Ngành nghề chính | "Công nghệ thông tin" |
| Website | url | | URL website công ty | "https://abctech.vn" |
| Mô tả tổ chức | textarea | | 2-3 câu giới thiệu | "ABC Tech là công ty..." |
| Logo URL | url | | URL logo để dùng trong certificates | |

### Section 2: Cấu hình chương trình Training

| Field | Type | Required | Mô tả | Ví dụ |
|-------|------|----------|-------|-------|
| Tên chương trình training | textfield | ✅ | Tên tổng thể | "Chương trình đào tạo nội bộ 2026" |
| Mục tiêu training | textarea | ✅ | Mục tiêu tổng thể | "Nâng cao kỹ năng dev team..." |
| Đối tượng học viên | textarea | ✅ | Ai sẽ học | "Nhân viên kỹ thuật 1-3 năm kinh nghiệm" |
| Ngôn ngữ content | select | ✅ | Ngôn ngữ tạo content | Vietnamese / English / Bilingual |
| Tone & Style | select | ✅ | Phong cách viết | Professional / Friendly / Academic |
| Cấp độ chi tiết | select | ✅ | Mức độ chi tiết content | Basic / Intermediate / Detailed |

### Section 3: Danh sách khóa học

Dùng **draggable table** (unlimited entries), mỗi row gồm:

| Field | Type | Required | Mô tả | Ví dụ |
|-------|------|----------|-------|-------|
| Tên khóa học | textfield | ✅ | | "React.js Fundamentals" |
| Mô tả ngắn | textarea | ✅ | 1-2 câu | "Khóa học cơ bản về React..." |
| Category | select | ✅ | Danh mục | Programming / Design / Marketing / Management / Soft Skills |
| Level | select | ✅ | Cấp độ | Beginner / Intermediate / Advanced |
| Số chapters dự kiến | number | ✅ | 2-20 | 5 |
| Số lessons/chapter dự kiến | number | ✅ | 2-15 | 4 |
| Có quiz cuối khóa | checkbox | | | ☑ |
| Có certificate | checkbox | | | ☑ |
| Pricing type | select | ✅ | Kiểu giá | Free / Paid / Membership |
| Giá (nếu Paid) | number | | VND hoặc USD | 500000 |
| Keywords / Topics chính | textarea | | Từ khóa giúp AI generate đúng hướng | "hooks, state, components, JSX" |
| Ghi chú thêm | textarea | | Yêu cầu đặc biệt cho khóa học này | "Focus vào hands-on projects" |

**Nút:** `[+ Thêm khóa học]` `[Import từ CSV]`

### Section 4: Cấu hình Instructors

Dùng **repeatable fieldset**, mỗi entry gồm:

| Field | Type | Required | Mô tả | Ví dụ |
|-------|------|----------|-------|-------|
| Tên instructor | textfield | ✅ | Tên đầy đủ | "Nguyễn Văn A" |
| Email | email | ✅ | Email đăng nhập | "a.nguyen@abctech.vn" |
| Chức danh | textfield | | | "Senior Developer" |
| Bio ngắn | textarea | | 2-3 câu giới thiệu | "10 năm kinh nghiệm..." |
| Chuyên môn | textarea | | Lĩnh vực chuyên sâu | "React, Node.js, AWS" |
| Phụ trách khóa học | checkboxes | | Chọn từ danh sách khóa học ở Section 3 | |

**Nút:** `[+ Thêm instructor]`

### Section 5: Cấu hình Sample Data bổ sung

| Field | Type | Required | Mô tả | Default |
|-------|------|----------|-------|---------|
| Số students mẫu | number | | Số lượng student accounts tạo thêm | 20 |
| Tạo sample enrollments | checkbox | | Enroll random students vào courses | ☑ |
| Tạo sample progress | checkbox | | Random progress cho enrolled students | ☑ |
| Tạo sample quiz results | checkbox | | Random quiz attempts & scores | ☑ |
| Tạo sample ratings/reviews | checkbox | | Random ratings 3-5 sao + review text | ☑ |
| Tạo sample forum topics | checkbox | | Tạo forum discussions cho mỗi course | ☑ |
| Tạo sample orders | checkbox | | Tạo orders cho paid courses | ☑ |
| Tạo membership plans | checkbox | | Tạo 3 plans: Basic/Premium/Enterprise | ☑ |
| Tạo sample notifications | checkbox | | Tạo notifications cho students | ☐ |
| Tạo sample badges | checkbox | | Tạo badge definitions + awards | ☑ |
| Currency | select | | Đơn vị tiền tệ | VND / USD / EUR |

### Section 6: Cấu hình nâng cao

| Field | Type | Required | Mô tả | Default |
|-------|------|----------|-------|---------|
| Lesson content type mặc định | select | | Loại content chính | Text / Video / Mixed |
| Video placeholder URL | url | | URL video mẫu cho video lessons | YouTube sample |
| Tạo prerequisites | checkbox | | Tạo chain prerequisites giữa courses/chapters | ☐ |
| Tạo drip content | checkbox | | Cấu hình drip schedule cho lessons | ☐ |
| Tạo course bundles | checkbox | | Nhóm courses thành bundles | ☐ |
| AI prompt bổ sung | textarea | | Hướng dẫn thêm cho AI khi generate | |

---

## 3. Nút hành động

```
┌─────────────────────────────────────────────────────────────┐
│                                                               │
│  [ 💾 Lưu thông tin ]    [ 👁 Xem JSON ]    [ 📥 Import JSON ] │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

| Nút | Hành động |
|-----|-----------|
| **Lưu thông tin** | Validate form → Lưu file JSON → Hiển thị success message |
| **Xem JSON** | Mở modal hiển thị JSON đã generate (read-only, có nút copy) |
| **Import JSON** | Upload file JSON đã export trước đó → Fill ngược vào form |

---

## 4. File JSON Output

### Vị trí lưu file

```
/home/hainn/projects/everlms/.agent/seed/
├── seed-input.json          ← File chính AI Agent sẽ đọc
├── seed-input.backup.json   ← Backup lần lưu trước
└── seed-history/
    └── seed-input-2026-03-28-143022.json  ← Lịch sử theo timestamp
```

> [!IMPORTANT]
> File lưu trong `.agent/seed/` — nằm trong project directory để AI Agent (Claude Code, Gemini) có thể đọc trực tiếp qua file system. Thư mục `.agent/` đã có sẵn trong project.

### Cấu trúc JSON

```json
{
  "version": "1.0",
  "generated_at": "2026-03-28T14:30:22+07:00",
  "organization": {
    "name": "Công ty TNHH ABC Tech",
    "short_name": "ABC Tech",
    "industry": "Công nghệ thông tin",
    "website": "https://abctech.vn",
    "description": "ABC Tech là công ty chuyên về phát triển phần mềm...",
    "logo_url": "https://abctech.vn/logo.png"
  },
  "training_program": {
    "name": "Chương trình đào tạo nội bộ 2026",
    "objectives": "Nâng cao kỹ năng dev team về modern web technologies",
    "target_audience": "Nhân viên kỹ thuật 1-3 năm kinh nghiệm",
    "language": "vi",
    "tone": "professional",
    "detail_level": "intermediate"
  },
  "courses": [
    {
      "id": 1,
      "title": "React.js Fundamentals",
      "description": "Khóa học cơ bản về React.js cho developer",
      "category": "Programming",
      "level": "beginner",
      "chapters_count": 5,
      "lessons_per_chapter": 4,
      "has_final_quiz": true,
      "has_certificate": true,
      "pricing_type": "paid",
      "price": 500000,
      "keywords": "hooks, state, components, JSX, virtual DOM",
      "notes": "Focus vào hands-on projects"
    },
    {
      "id": 2,
      "title": "Advanced Node.js",
      "description": "Khóa học nâng cao về Node.js và microservices",
      "category": "Programming",
      "level": "advanced",
      "chapters_count": 6,
      "lessons_per_chapter": 5,
      "has_final_quiz": true,
      "has_certificate": true,
      "pricing_type": "membership",
      "price": null,
      "keywords": "microservices, event loop, streams, clustering",
      "notes": ""
    }
  ],
  "instructors": [
    {
      "id": 1,
      "name": "Nguyễn Văn A",
      "email": "a.nguyen@abctech.vn",
      "title": "Senior Developer",
      "bio": "10 năm kinh nghiệm phát triển web applications",
      "expertise": "React, Node.js, AWS",
      "assigned_courses": [1, 2]
    }
  ],
  "sample_data": {
    "students_count": 20,
    "create_enrollments": true,
    "create_progress": true,
    "create_quiz_results": true,
    "create_ratings": true,
    "create_forum_topics": true,
    "create_orders": true,
    "create_membership_plans": true,
    "create_notifications": false,
    "create_badges": true,
    "currency": "VND"
  },
  "advanced": {
    "default_lesson_type": "text",
    "video_placeholder_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
    "create_prerequisites": false,
    "create_drip_content": false,
    "create_bundles": false,
    "additional_ai_prompt": ""
  }
}
```

---

## 5. Drush Command

### `drush everlms:seed-input`

```bash
# Mở interactive form trong terminal
drush everlms:seed-input

# Xem seed input hiện tại
drush everlms:seed-input --show

# Export seed input ra stdout
drush everlms:seed-input --export

# Import từ file
drush everlms:seed-input --import=/path/to/seed-input.json

# Validate file hiện tại
drush everlms:seed-input --validate
```

---

## 6. Validation Rules

| Field | Rule |
|-------|------|
| Tên tổ chức | Required, max 255 chars |
| Tên khóa học | Required, unique trong danh sách, max 255 chars |
| Số chapters | Required, min 2, max 20 |
| Số lessons/chapter | Required, min 2, max 15 |
| Email instructor | Valid email format, unique |
| Giá | Required nếu pricing_type = "paid", min 0 |
| Số students | Min 0, max 100 |
| Ít nhất 1 khóa học | Phải có ít nhất 1 course entry |
| Ít nhất 1 instructor | Phải có ít nhất 1 instructor |

---

## 7. UX Notes

- Form tự **auto-save draft** vào localStorage mỗi 30 giây (tránh mất data khi reload)
- Khi đã có file `seed-input.json`, form **tự load data** từ file khi mở
- Nếu form trống và có file JSON tồn tại → hiển thị banner: *"Đã có seed input. [Load dữ liệu cũ] hoặc [Tạo mới]"*
- Import CSV cho courses: hỗ trợ file CSV với headers match tên fields
