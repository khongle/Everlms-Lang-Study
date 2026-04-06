# EverLMS — AI Content Generator

> Cơ chế để AI Agent (Claude Code trên VS Code, Gemini trên Antigravity) đọc seed input → tự generate toàn bộ content → import vào hệ thống. Tối ưu cho việc dùng agent với account Max/Ultra thay vì gọi API.

## 1. Tổng quan

### Tại sao dùng AI Agent thay vì API?
- **Account Max (Claude) / Ultra (Gemini)** đã có sẵn → không tốn thêm chi phí API
- Agent đọc file trực tiếp → **không cần build integration** phức tạp
- Agent có **context window lớn** → generate content chất lượng hơn từng API call nhỏ
- Có thể **review và chỉnh sửa** trước khi import
- Hỗ trợ **nhiều AI provider** — không lock-in vào 1 vendor

### Flow tổng thể

```
┌─────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────┐
│  Admin nhập │     │  AI Agent    │     │  Review &    │     │  Import  │
│  Seed Input │────▶│  Generate    │────▶│  Adjust      │────▶│  vào DB  │
│  (Form/JSON)│     │  Content     │     │  Output      │     │  (Drush) │
└─────────────┘     └──────────────┘     └──────────────┘     └──────────┘
```

---

## 2. Cấu trúc thư mục

```
/home/hainn/projects/everlms/.agent/
├── seed/
│   ├── seed-input.json              ← Input: thông tin gốc admin đã nhập
│   └── seed-history/                ← Lịch sử các lần nhập
├── generated/
│   ├── courses/
│   │   ├── course-001-react-fundamentals.json
│   │   ├── course-002-advanced-nodejs.json
│   │   └── ...
│   ├── users/
│   │   ├── instructors.json
│   │   └── students.json
│   ├── quizzes/
│   │   ├── quiz-course-001-final.json
│   │   └── ...
│   ├── commerce/
│   │   ├── membership-plans.json
│   │   ├── orders.json
│   │   └── coupons.json
│   ├── community/
│   │   ├── forum-topics.json
│   │   ├── ratings.json
│   │   └── badges.json
│   ├── certificates/
│   │   └── certificate-templates.json
│   └── manifest.json               ← Index file liệt kê tất cả generated files
├── scripts/
│   ├── import-all.php               ← Master import script
│   ├── import-courses.php
│   ├── import-users.php
│   ├── import-quizzes.php
│   ├── import-commerce.php
│   ├── import-community.php
│   └── import-certificates.php
└── prompts/
    ├── CONTENT_GENERATION.md        ← Prompt hướng dẫn AI generate
    └── IMPORT_GUIDE.md              ← Hướng dẫn import sau khi generate
```

---

## 3. Prompt hướng dẫn AI Agent

File `.agent/prompts/CONTENT_GENERATION.md` chứa hướng dẫn chi tiết cho AI Agent. Khi user mở Claude Code hoặc Gemini và yêu cầu generate content, AI sẽ đọc file này.

### Nội dung prompt

```markdown
# EverLMS Content Generation Guide

## Bước 1: Đọc thông tin đầu vào
1. Đọc file `.agent/seed/seed-input.json` để lấy thông tin gốc
2. Đọc `.agent/docs-v2/01-requirement/` để hiểu entity structure
3. Nắm rõ các entity types và fields cần generate

## Bước 2: Generate content theo thứ tự
Tạo file JSON cho từng phần, theo thứ tự dependency:

### 2.1 Taxonomy Terms (nếu cần)
- Course Categories (từ categories trong seed input)
- Course Levels: Beginner, Intermediate, Advanced
- Course Languages
- Quiz Difficulty levels
- Badge Types

### 2.1.5 General & Landing Content
- Slideshows: (nếu create_slideshows = true) 3-5 banners trang chủ
- Introductions: (nếu create_introductions = true) 3-5 khối giới thiệu static
- Glossaries: (nếu create_glossaries = true) 10-20 thuật ngữ
- Blogs: (nếu create_blogs = true) 3-5 bài viết chuyên môn
- FAQs: (nếu create_faqs = true) 10 câu hỏi đa dạng (scope: global hoặc course)

### 2.2 Users
- Instructors (từ seed input)
- Students (random generate theo số lượng trong seed input)

### 2.3 Courses → Chapters → Lessons
Cho MỖI course trong seed input:
- Tạo course entity với đầy đủ fields
- Tạo N chapters với tiêu đề và mô tả phù hợp chủ đề
- Tạo M lessons cho mỗi chapter với:
  - Tiêu đề lesson liên quan đến chapter
  - Nội dung bài học chi tiết (text content)
  - Duration ước tính
  - Lesson type theo cấu hình
  - Attachments metadata (nếu có)

### 2.4 Quizzes & Questions
Cho mỗi course có `has_final_quiz = true`:
- Tạo quiz entity với settings phù hợp
- Tạo 10-20 questions đa dạng loại:
  - 3-4 Multiple Choice (4 options, 1+ correct)
  - 2-3 Single Choice (4 options, 1 correct)
  - 2-3 True/False
  - 1-2 Fill in Blank
  - 1-2 Short Answer
  - 1 Matching (4-6 pairs)
  - 1 Ordering (4-6 items)
  - 1 Essay (với rubric)
- Tất cả questions phải **liên quan đến nội dung khóa học**

### 2.5 Certificates
- Tạo certificate template cho mỗi course có certificate
- Tokens: {student_name}, {course_name}, {completion_date}, {certificate_id}

### 2.6 Commerce
- Membership Plans: Basic/Premium/Enterprise (nếu enabled)
- Coupons: 2-3 sample coupons
- Orders: Random orders cho paid courses (nếu enabled)

### 2.7 Community & Engagement
- Forum Topics: 2-3 topics/course với replies
- Ratings: Random 3-5 sao cho mỗi course (nếu enabled)
- Badges: Course completion, Quiz master, Streak badges
- FAQs: 3-5 FAQs cho mỗi course

### 2.8 Progress & Results
- Enroll students vào random courses
- Random progress (30%-100%)
- Quiz results cho enrolled students (nếu enabled)

## Bước 3: Tạo manifest.json
Liệt kê tất cả file đã generate với metadata.

## Bước 4: Thông báo user review
Sau khi generate xong, thông báo user review files trong
`.agent/generated/` trước khi chạy import.
```

---

## 4. JSON Output Format

### 4.1 Course File

File: `.agent/generated/courses/course-001-react-fundamentals.json`

```json
{
  "course": {
    "title": "React.js Fundamentals",
    "description": "<p>Khóa học cơ bản về React.js...</p>",
    "category": "Programming",
    "level": "beginner",
    "language": "vi",
    "pricing_type": "paid",
    "price": 500000,
    "currency": "VND",
    "duration": "20 hours",
    "status": "published",
    "featured": true,
    "instructor_email": "a.nguyen@abctech.vn"
  },
  "chapters": [
    {
      "title": "Giới thiệu React.js",
      "description": "Tổng quan về React và ecosystem",
      "weight": 0,
      "lessons": [
        {
          "title": "React là gì? Tại sao dùng React?",
          "type": "text",
          "content": "<h2>React là gì?</h2><p>React là một thư viện JavaScript...</p>...",
          "duration": 15,
          "is_preview": true,
          "weight": 0
        },
        {
          "title": "Cài đặt môi trường phát triển",
          "type": "text",
          "content": "<h2>Yêu cầu hệ thống</h2><p>Để bắt đầu với React...</p>...",
          "duration": 20,
          "is_preview": false,
          "weight": 1
        }
      ]
    }
  ]
}
```

### 4.2 Quiz File

File: `.agent/generated/quizzes/quiz-course-001-final.json`

```json
{
  "quiz": {
    "title": "Bài kiểm tra cuối khóa: React.js Fundamentals",
    "course_ref": "course-001",
    "instructions": "Bài kiểm tra gồm 15 câu hỏi...",
    "pass_threshold": 70,
    "time_limit": 30,
    "max_attempts": 3,
    "display_style": "question_step"
  },
  "questions": [
    {
      "type": "single_choice",
      "text": "React sử dụng cơ chế nào để tối ưu hiệu suất render?",
      "points": 5,
      "answers": [
        {"text": "Real DOM", "is_correct": false},
        {"text": "Virtual DOM", "is_correct": true},
        {"text": "Shadow DOM", "is_correct": false},
        {"text": "Abstract DOM", "is_correct": false}
      ],
      "explanation": "React sử dụng Virtual DOM để so sánh..."
    },
    {
      "type": "multiple_choice",
      "text": "Những hook nào sau đây là built-in hooks của React?",
      "points": 5,
      "answers": [
        {"text": "useState", "is_correct": true},
        {"text": "useEffect", "is_correct": true},
        {"text": "useComputed", "is_correct": false},
        {"text": "useCallback", "is_correct": true}
      ]
    },
    {
      "type": "true_false",
      "text": "JSX là một ngôn ngữ lập trình mới.",
      "points": 3,
      "correct_answer": false,
      "explanation": "JSX là syntax extension cho JavaScript..."
    },
    {
      "type": "fill_blank",
      "text": "Để khai báo state trong functional component, ta dùng hook ____.",
      "points": 5,
      "correct_answers": ["useState", "React.useState"]
    },
    {
      "type": "matching",
      "text": "Nối khái niệm với mô tả tương ứng:",
      "points": 8,
      "pairs": [
        {"left": "Props", "right": "Dữ liệu truyền từ component cha"},
        {"left": "State", "right": "Dữ liệu nội bộ của component"},
        {"left": "useEffect", "right": "Xử lý side effects"},
        {"left": "useRef", "right": "Tham chiếu DOM element"}
      ]
    },
    {
      "type": "ordering",
      "text": "Sắp xếp đúng thứ tự lifecycle của một React component:",
      "points": 8,
      "items": [
        {"text": "Constructor / Initialization", "correct_position": 1},
        {"text": "Render", "correct_position": 2},
        {"text": "componentDidMount / useEffect", "correct_position": 3},
        {"text": "Update (re-render)", "correct_position": 4},
        {"text": "Cleanup / Unmount", "correct_position": 5}
      ]
    },
    {
      "type": "essay",
      "text": "Giải thích sự khác biệt giữa Controlled và Uncontrolled Components trong React. Cho ví dụ cho mỗi loại.",
      "points": 15,
      "rubric": "5đ: Giải thích đúng khái niệm | 5đ: Ví dụ chính xác | 5đ: So sánh ưu nhược điểm",
      "min_words": 100
    }
  ]
}
```

### 4.3 Users File

File: `.agent/generated/users/students.json`

```json
{
  "students": [
    {
      "username": "student01",
      "email": "student01@example.com",
      "display_name": "Trần Thị Bình",
      "password": "Student@123",
      "role": "student"
    }
  ]
}
```

### 4.4 New Entity Files (Slideshows, Introductions, Glossaries, Blogs, FAQs)

File: `.agent/generated/slideshows/slideshows.json`
```json
{
  "slideshows": [
    {
      "title": "Welcome to EverLMS",
      "body": "Nền tảng học tập trực tuyến hàng đầu.",
      "cta_text": "Khám phá ngay",
      "cta_link": "internal:/courses"
    }
  ]
}
```

File: `.agent/generated/introductions/introductions.json`
```json
{
  "introductions": [
    {
      "title": "Về chúng tôi",
      "subheading": "Nâng tầm tri thức",
      "body": "EverLMS cung cấp giải pháp chuyển đổi số toàn diện...",
      "image_position": "right",
      "background_color": "#f8f9fa",
      "cta_text": "Liên hệ",
      "cta_link": "internal:/contact"
    }
  ]
}
```

File: `.agent/generated/glossaries/glossaries.json`
```json
{
  "glossaries": [
    {
      "term": "Asynchronous Learning",
      "definition": "Học tập không đồng bộ, sinh viên có thể tự học theo tiến độ cá nhân...",
      "synonyms": "Học qua video, Tự học"
    }
  ]
}
```

File: `.agent/generated/blogs/blogs.json`
```json
{
  "blogs": [
    {
      "title": "Xu hướng E-learning 2026",
      "content": "<p>Trong năm 2026, AI Agent sẽ thống trị cách thức phân phối bài giảng...</p>"
    }
  ]
}
```

File: `.agent/generated/faqs/faqs.json`
```json
{
  "faqs": [
    {
      "question": "Làm sao để lấy chứng chỉ?",
      "answer": "Bạn cần hoàn thành 100% video và bài kiểm tra cuối khóa.",
      "scope": "course",
      "weight": 0
    },
    {
      "question": "Hệ thống có hỗ trợ thanh toán Momo không?",
      "answer": "Có, chúng tôi hỗ trợ Momo, VNPay và thẻ tín dụng.",
      "scope": "global",
      "weight": 1
    }
  ]
}
```

### 4.5 Manifest File

File: `.agent/generated/manifest.json`

```json
{
  "generated_at": "2026-03-28T15:00:00+07:00",
  "generated_by": "Claude Code (Opus 4.6)",
  "seed_input_hash": "abc123...",
  "summary": {
    "total_courses": 5,
    "total_chapters": 25,
    "total_lessons": 100,
    "total_quizzes": 5,
    "total_questions": 75,
    "total_instructors": 3,
    "total_students": 20
  },
  "files": [
    {
      "path": "courses/course-001-react-fundamentals.json",
      "type": "course",
      "status": "ready",
      "entities_count": 26
    }
  ],
  "import_order": [
    "users/instructors.json",
    "users/students.json",
    "slideshows/slideshows.json",
    "introductions/introductions.json",
    "glossaries/glossaries.json",
    "blogs/blogs.json",
    "faqs/faqs.json",
    "courses/course-001-react-fundamentals.json",
    "courses/course-002-advanced-nodejs.json",
    "quizzes/quiz-course-001-final.json",
    "certificates/certificate-templates.json",
    "commerce/membership-plans.json",
    "commerce/orders.json",
    "community/ratings.json",
    "community/forum-topics.json",
    "community/badges.json"
  ]
}
```

---

## 5. Import Scripts

### 5.1 Master Import

```bash
# Import tất cả từ generated files
drush everlms:import

# Import theo thứ tự trong manifest
drush everlms:import --from-manifest

# Import chỉ courses
drush everlms:import --type=courses

# Import các content types mới (5 loại đã implement)
drush everlms:import --type=slideshows
drush everlms:import --type=introductions
drush everlms:import --type=glossaries
drush everlms:import --type=blogs
drush everlms:import --type=faqs

# Import 1 file cụ thể
drush everlms:import --file=courses/course-001-react-fundamentals.json

# Dry-run: validate JSON nhưng không import
drush everlms:import --dry-run

# Import với verbose output
drush everlms:import -v
```

### 5.2 Import Flow

```
drush everlms:import
    │
    ├── 1. Đọc manifest.json → lấy import_order
    ├── 2. Validate tất cả JSON files
    ├── 3. Confirm với user: "Sẽ import X courses, Y users, ..."
    │
    ├── 4. Import theo thứ tự:
    │   ├── Users (instructors → students)
    │   ├── Slideshows           ← node:slideshow ✅
    │   ├── Introductions        ← node:introduction ✅
    │   ├── Glossaries           ← node:glossary ✅ (có course reference)
    │   ├── Blogs                ← node:blog ✅ (tạo blog_tags nếu chưa có)
    │   ├── FAQs                 ← lms_faq ✅ (scope: global|course)
    │   ├── Courses → Chapters → Lessons
    │   ├── Quizzes → Questions → Answers
    │   ├── Certificates
    │   ├── Commerce (plans → orders)
    │   └── Community (forums, ratings, badges)
    │
    ├── 5. Tạo entity references (course ↔ instructor, etc.)
    ├── 6. Log kết quả
    │
    ▼
    Hiển thị summary: imported/skipped/errors
```

### 5.3 Xử lý Import

| Tình huống | Xử lý |
|-----------|-------|
| Entity đã tồn tại (trùng title) | Skip + warning |
| JSON không hợp lệ | Dừng file đó, tiếp tục files khác |
| Missing reference (instructor chưa tồn tại) | Tạo placeholder + warning |
| Batch quá lớn | Chunk thành 50 entities/batch |
| Import fail giữa chừng | Rollback file đang import, giữ files đã import xong |

---

## 6. Cơ chế AI Agent tự động

### 6.1 Khi mở Claude Code (VS Code)

User mở Claude Code và gõ lệnh generate, AI sẽ:

1. **Kiểm tra** file `.agent/seed/seed-input.json` tồn tại
2. **Đọc** seed input + đọc prompt guide `.agent/prompts/CONTENT_GENERATION.md`
3. **Đọc** entity specs từ `.agent/docs-v2/01-requirement/by-entities/`
4. **Generate** content theo từng file, ghi vào `.agent/generated/`
5. **Tạo** manifest.json
6. **Thông báo** user review và chạy import

**Ví dụ user prompt:**
```
> Đọc seed input và generate toàn bộ content cho hệ thống training.
```

**AI thực hiện:**
```
1. Đọc .agent/seed/seed-input.json
2. Đọc .agent/prompts/CONTENT_GENERATION.md
3. Generate courses/course-001-react-fundamentals.json (Write tool)
4. Generate courses/course-002-advanced-nodejs.json (Write tool)
5. ... (tiếp tục cho tất cả courses)
6. Generate quizzes, users, commerce, community files
7. Generate manifest.json
8. Thông báo: "Đã generate xong X files. Review tại .agent/generated/ rồi chạy: drush everlms:import"
```

### 6.2 Khi dùng Gemini (Antigravity)

Cơ chế tương tự — Gemini đọc cùng files, generate cùng format JSON. File `.agent/prompts/CONTENT_GENERATION.md` được viết **AI-agnostic** (không phụ thuộc vào Claude hay Gemini).

### 6.3 Tối ưu Token

| Chiến lược | Mô tả |
|-----------|-------|
| **Tách file theo course** | Mỗi course 1 file → AI generate từng course một, không cần load tất cả vào context |
| **Prompt ngắn gọn** | Prompt guide chỉ chứa structure + rules, không chứa example content dài |
| **Seed input compact** | JSON format gọn, không có data thừa |
| **Incremental generate** | Có thể generate 1-2 courses trước, review, rồi generate tiếp → tránh waste nếu output sai format |
| **Template reuse** | Quiz, rating, forum format giống nhau → AI chỉ cần thay content |
| **Không đọc lại** | AI đọc specs 1 lần đầu, các lần generate sau chỉ đọc seed input |

### 6.4 Multi-session Support

Nếu context window không đủ để generate tất cả trong 1 session:

```
Session 1: Generate courses 1-3 + quizzes
Session 2: Generate courses 4-5 + quizzes
Session 3: Generate users, commerce, community
Session 4: Generate progress, results + manifest
```

AI kiểm tra `.agent/generated/` đầu mỗi session để biết đã generate những gì, tiếp tục từ chỗ dở.

---

## 7. Drush Commands tổng hợp

| Command | Mô tả |
|---------|-------|
| `drush everlms:clean` | Xóa content (xem [01-clean-content.md](./01-clean-content.md)) |
| `drush everlms:seed-input --show` | Xem seed input hiện tại |
| `drush everlms:seed-input --validate` | Validate seed input JSON |
| `drush everlms:import` | Import từ generated files |
| `drush everlms:import --dry-run` | Validate generated files |
| `drush everlms:import --type=courses` | Import chỉ courses |
| `drush everlms:import --status` | Xem trạng thái import |

---

## 8. Checklist triển khai

- [ ] Tạo Drupal form `CleanContentForm` với AJAX confirm 2 lượt
- [ ] Tạo Drupal form `SeedInputForm` với 6 sections
- [ ] Tạo Drush command `everlms:clean`
- [ ] Tạo Drush command `everlms:seed-input`
- [ ] Tạo Drush command `everlms:import`
- [ ] Tạo thư mục `.agent/seed/`, `.agent/generated/`, `.agent/prompts/`, `.agent/scripts/`
- [ ] Viết file `CONTENT_GENERATION.md` prompt guide
- [ ] Viết file `IMPORT_GUIDE.md`
- [ ] Viết import scripts PHP (courses, users, quizzes, commerce, community)
- [ ] Test full flow: seed input → AI generate → import → verify
- [ ] Test clean → re-generate → re-import flow
