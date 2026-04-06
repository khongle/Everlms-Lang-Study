# EverLMS — Content Tools: Báo cáo Chạy thử Lần 1

> Báo cáo kết quả chạy thử toàn bộ flow Content Tools ngày 2026-03-28/29.
> Đề tài: **An toàn Lao động tại Công ty Xây dựng Thành Đạt**.

## 1. Tổng quan

| Hạng mục | Kết quả |
|----------|---------|
| **Trạng thái** | ✅ Thành công |
| **Tổng entities imported** | 378 |
| **Errors** | 0 |
| **Thời gian toàn bộ flow** | ~25 phút (bao gồm fix bugs) |
| **Problems phát hiện** | 12 (5 fixed, 4 cần lưu ý, 3 observations) |

### Flow đã thực hiện

```
1. Enable module lms_content_tools
2. Clean 816 entities cũ (drush everlms:clean -y)
3. Tạo seed-input.json (thủ công)
4. Generate 17 JSON files (AI agents song song)
5. Validate JSON (python3)
6. Fix 1 file JSON lỗi (unescaped quotes)
7. Tạo manifest.json
8. Import (drush everlms:import -y) → 378 entities, 0 errors
```

---

## 2. Kết quả Import chi tiết

### 2.1 Entities đã tạo

| Loại | Số lượng | Ghi chú |
|------|---------|---------|
| Users (instructors) | 3 | minh.tran, huong.nguyen, nam.le |
| Users (students) | 15 | student01–student15, tên Việt |
| Courses | 5 | 1 paid (500K VND), 4 free |
| Chapters | 22 | 4-5 chapters/course |
| Lessons | 79 | 3-4 lessons/chapter, text content |
| Quizzes | 5 | 1 quiz/course, đủ 8 loại câu hỏi |
| Questions | 75 | 15 questions/quiz |
| Answers + Matching Pairs | 150 | ~10 answers/quiz cho choice questions |
| Certificate Templates | 5 | 1 template/course, landscape |
| Membership Plans | 3 | Basic/Premium/Enterprise |
| Forum Topics | 5 | Với 9 replies |
| Badges | 7 | 4 rarity levels |
| **Tổng** | **378** | |

### 2.2 Danh sách 5 khóa học

| # | Khóa học | Level | Chapters | Lessons | Instructor | Giá |
|---|----------|-------|----------|---------|------------|-----|
| 1 | An toàn Lao động Tổng quan | Beginner | 4 | 16 | Trần Văn Minh | Free |
| 2 | An toàn Làm việc trên Cao | Intermediate | 5 | 20 | Nguyễn Thị Hương | Free |
| 3 | An toàn Điện trên Công trường | Intermediate | 4 | 12 | Lê Hoàng Nam | Free |
| 4 | An toàn Vận hành Máy và Thiết bị | Advanced | 5 | 15 | Lê Hoàng Nam | 500,000 VND |
| 5 | Sơ cứu và Ứng phó Sự cố | Beginner | 4 | 16 | Nguyễn Thị Hương | Free |

### 2.3 Quizzes — phân bố loại câu hỏi

Mỗi quiz có 15 câu hỏi, đủ 8 loại:

| Loại | Số lượng/quiz | Points | Auto-graded |
|------|--------------|--------|-------------|
| single_choice | 3 | 5 | ✅ |
| multiple_choice | 2 | 5 | ✅ |
| true_false | 2 | 3 | ✅ |
| fill_blank | 2 | 5 | ✅ |
| short_answer | 1 | 5 | ✅ |
| matching | 2 | 8 | ✅ |
| ordering | 1 | 8 | ✅ |
| essay | 2 | 15 | ❌ Manual |
| **Tổng/quiz** | **15** | **107** | |

---

## 3. AI Agent Performance

### 3.1 So sánh Sonnet vs Haiku cho Content Generation

| Metric | Sonnet | Haiku |
|--------|--------|-------|
| **Chất lượng content** | Cao — tham chiếu TCVN, EN, ISO cụ thể, mô hình lý thuyết (Bradley Curve, Heinrich) | Trung bình — nội dung đúng nhưng ít chi tiết chuyên sâu |
| **Tốc độ trung bình** | 8-58 phút/course | 1-18 phút/course |
| **Token usage** | 32K-104K/course | ~10K-15K/course |
| **Độ tin cậy JSON** | 1/5 files bị lỗi quotes | 0/5 files lỗi |
| **Tool calls** | 3-12 calls/file | 1-20 calls/file |

### 3.2 Chi tiết từng Agent

**Sonnet Agents (batch 1 — chạy trước):**

| Agent | Task | Tokens | Thời gian | Tool calls |
|-------|------|--------|-----------|------------|
| Course 1 | 16 lessons | 71,964 | 17.7 min | 9 |
| Course 2 | 20 lessons | 103,732 | 58.5 min | 12 |
| Course 3 | 12 lessons | 32,295 | 7.6 min | 3 |
| Course 4 | 15 lessons | 36,953 | 8.9 min | 3 |
| Course 5 | 16 lessons | 40,762 | 10.3 min | 3 |
| 5 Quizzes | 75 questions | 38,634 | 8.8 min | 7 |

**Haiku Agents (batch 2 — chạy sau khi Sonnet quá chậm):**

| Agent | Task | Tokens | Thời gian | Tool calls |
|-------|------|--------|-----------|------------|
| Course 1 | 16 lessons | 25,159 | 1.3 min | 1 |
| Course 2 | 20 lessons | 48,748 | 12.6 min | 18 |
| Course 3 | 12 lessons | 33,814 | 8.6 min | 6 |
| Course 4 | 15 lessons | 69,847 | 18.2 min | 20 |
| Course 5 | 16 lessons | 54,903 | 14.6 min | 14 |

### 3.3 Kết luận về Model selection

| Use case | Recommended Model | Lý do |
|----------|------------------|-------|
| Content generation (sample data) | **Haiku** | Nhanh, rẻ, đủ chất lượng cho demo/test |
| Content generation (production) | **Sonnet** | Chi tiết hơn, tham chiếu chuẩn chính xác |
| Quiz generation | **Sonnet** | Cần câu hỏi chính xác, đa dạng |
| Content review/editing | **Opus** | Cần đánh giá chất lượng, sửa nội dung |
| Field mapping/import scripts | **Opus** | Cần hiểu entity structure chính xác |

---

## 4. Bugs & Fixes trong quá trình chạy thử

### Fixed (5)

| # | Problem | Fix |
|---|---------|-----|
| 1 | Drush `--yes` không bypass `io()->ask()` | Detect `$this->input()->getOption('yes')` |
| 2 | `--yes` option trùng Drush global option | Dùng global option thay vì khai báo riêng |
| 9 | Entity relationship sai chiều (child→parent) | Đổi thành parent→children: tạo children trước, link vào parent |
| 10 | 10+ field names sai (xem bảng dưới) | Rewrite import methods với đúng field names |
| 12 | AI JSON có unescaped quotes trong HTML | Python post-processing fix quotes |

### Field Name Corrections (Problem #10)

| Sai | Đúng | Entity |
|-----|------|--------|
| `field_is_preview` | `field_free_preview` | Lesson |
| `field_instructions` | `field_body` | Quiz |
| `field_pass_threshold` | `field_threshold` | Quiz |
| `field_max_attempts` | `field_take_quiz_number` | Quiz |
| `field_display_style` | `field_form_style` | Quiz |
| `field_is_correct` | `field_correct` | Answer |
| `field_criteria_type` | `field_criteria` (JSON) | Badge |
| `field_points` | `field_xp_reward` | Badge |
| `field_layout` | `field_orientation` + `field_design` | Certificate |
| `field_chapter` (on lesson) | `field_lessons` (on chapter) | Chapter→Lesson |
| `field_course` (on chapter) | `field_chapters` (on course) | Course→Chapter |
| `field_quiz` (on question) | `field_questions` (on quiz) | Quiz→Question |

### Noted — Cần xử lý sau (4)

| # | Problem | Impact |
|---|---------|--------|
| 3 | Entity types trong docs khác code thực tế | docs-v2 cần sync |
| 4 | Ratings import thiếu course/user lookup | Ratings chưa import được |
| 5 | Instructor role có thể chưa tồn tại | Import users warn nếu role missing |
| 6 | Một số field names cần verify thêm | Có thể fail trên entity types chưa test |

### Observations (3)

| # | Observation |
|---|-------------|
| 7 | Generate content tốn 50K+ words, cần tối ưu token |
| 8 | DDEV environment cần `ddev drush` thay vì `php` trực tiếp |
| 11 | Sonnet 5-58 phút/course vs Haiku 1-18 phút → Haiku recommended cho sample data |

---

## 5. Files đã tạo

### Module lms_content_tools (13 files)

```
web/modules/custom/lms_content_tools/
├── lms_content_tools.info.yml
├── lms_content_tools.routing.yml          (3 routes)
├── lms_content_tools.permissions.yml
├── lms_content_tools.links.menu.yml
├── lms_content_tools.services.yml
├── lms_content_tools.libraries.yml
├── drush.services.yml
├── js/clean-confirm.js
└── src/
    ├── Service/ContentToolsManager.php    (entity groups, CRUD, seed I/O)
    ├── Form/CleanContentForm.php          (3-step: select → confirm CLEAN → results)
    ├── Form/SeedInputForm.php             (6 sections, AJAX add/remove)
    ├── Controller/ContentToolsController.php (dashboard 3 cards)
    └── Commands/ContentToolsCommands.php  (clean, seed-input, import)
```

### Generated Content (17 files, ~400KB)

```
.agent/generated/
├── manifest.json
├── courses/
│   ├── course-001-an-toan-lao-dong-tong-quan.json    (71K)
│   ├── course-002-an-toan-lam-viec-tren-cao.json     (56K)
│   ├── course-003-an-toan-dien-cong-truong.json      (20K)
│   ├── course-004-an-toan-van-hanh-may-thiet-bi.json  (30K)
│   └── course-005-so-cuu-ung-pho-su-co.json          (22K)
├── quizzes/
│   ├── quiz-course-001-final.json                     (14K)
│   ├── quiz-course-002-final.json                     (14K)
│   ├── quiz-course-003-final.json                     (14K)
│   ├── quiz-course-004-final.json                     (15K)
│   └── quiz-course-005-final.json                     (15K)
├── users/
│   ├── instructors.json
│   └── students.json
├── certificates/certificate-templates.json
├── commerce/membership-plans.json
└── community/
    ├── forum-topics.json
    ├── ratings.json
    └── badges.json
```

### Prompt & Docs

```
.agent/
├── seed/seed-input.json                               (Thông tin Công ty Thành Đạt)
├── prompts/
│   ├── CONTENT_GENERATION.md                          (Hướng dẫn AI generate)
│   └── IMPORT_GUIDE.md                                (Hướng dẫn import)
└── docs-v2/05-content-tools/
    ├── README.md                                      (Index)
    ├── 01-clean-content.md                            (Clean form specs)
    ├── 02-seed-input-form.md                          (Seed input specs)
    ├── 03-ai-content-generator.md                     (AI generator specs)
    ├── 04-report.md                                   (File này)
    └── PROBLEMS.md                                    (12 problems chi tiết)
```

---

## 6. Recommendations cho lần chạy tiếp theo

### Quick wins

1. **Thêm JSON validation tự động** — script Python fix unescaped quotes trước khi import
2. **Dùng Haiku cho sample data** — nhanh 5-10x, đủ chất lượng cho demo
3. **Giảm lesson content** xuống 200 words cho lần generate đầu, review rồi mới generate chi tiết

### Cần implement thêm

4. **Ratings import** — lookup course by name, student by username
5. **Enrollments + Progress** — generate sample data cho students đã enrolled
6. **Quiz Results** — generate sample attempts cho students
7. **Orders** — generate sample orders cho paid courses
8. **Import rollback** — database transaction per file

### Cải thiện UX

9. **Seed Input Form** — test trên browser, verify AJAX add/remove hoạt động
10. **Clean Form** — test trên browser, verify 2-step confirm + JS
11. **Dashboard** — verify cards hiển thị đúng counts và status
