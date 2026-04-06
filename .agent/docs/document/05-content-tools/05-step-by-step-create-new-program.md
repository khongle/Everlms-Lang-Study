# EverLMS — Hướng dẫn Tạo Chương trình Training Mới (Step by Step)

> Hướng dẫn từng bước để tạo một chương trình training hoàn chỉnh trên EverLMS, từ ý tưởng đến content sẵn sàng trên hệ thống.

## Tổng quan Flow

```
Step 1: Clean data cũ (nếu cần)
  ↓
Step 2: Tạo seed-input.json
  ↓
Step 3: AI generate content → JSON files
  ↓
Step 4: AI generate images → Copy vào Drupal files
  ↓
Step 5: Validate JSON
  ↓
Step 6: Import vào DB
  ↓
Step 7: Kiểm tra kết quả
```

**Thời gian ước tính:** ~15-30 phút (tùy số lượng khóa học)

---

## Step 1: Clean data cũ (nếu cần)

> Bỏ qua bước này nếu đây là lần đầu import hoặc bạn muốn giữ data hiện tại.

### Cách 1: Dùng Drush (khuyến nghị)

```bash
# Xem sẽ xóa gì (dry run)
ddev drush everlms:clean --dry-run

# Xóa tất cả content (không xóa users)
ddev drush everlms:clean -y

# Xóa tất cả kể cả users
ddev drush everlms:clean --include-users -y

# Chỉ xóa một số loại
ddev drush everlms:clean --types=courses,quizzes -y
```

### Cách 2: Dùng Admin UI

1. Vào **Structure → Content Tools → Clean Content** (`/workspace/content-tools/clean`)
2. Tick chọn loại entity muốn xóa
3. Nhấn **Review** → Xem bảng tóm tắt
4. Gõ **CLEAN** vào ô xác nhận
5. Nhấn **Confirm Delete**
6. Chờ xóa xong → Xem kết quả

---

## Step 2: Tạo Seed Input (Thông tin gốc)

Seed input là file JSON chứa mọi thông tin về chương trình training mà AI cần để generate content.

### Cách 1: Tạo thủ công (nhanh nhất)

Tạo file `.agent/seed/seed-input.json` theo cấu trúc sau:

```json
{
  "version": "1.0",
  "generated_at": "2026-03-29T10:00:00+07:00",
  "organization": {
    "name": "Tên công ty/tổ chức",
    "short_name": "Tên ngắn",
    "industry": "Ngành nghề",
    "website": "https://example.com",
    "description": "Mô tả ngắn về tổ chức (2-3 câu)"
  },
  "training_program": {
    "name": "Tên chương trình đào tạo",
    "objectives": "Mục tiêu đào tạo",
    "target_audience": "Đối tượng học viên",
    "language": "vi",
    "tone": "professional",
    "detail_level": "detailed"
  },
  "courses": [
    {
      "id": 1,
      "title": "Tên khóa học",
      "description": "Mô tả khóa học",
      "category": "Other",
      "level": "beginner|intermediate|advanced",
      "chapters_count": 4,
      "lessons_per_chapter": 4,
      "has_final_quiz": true,
      "has_certificate": true,
      "pricing_type": "free|paid",
      "price": null,
      "keywords": "keyword1, keyword2, keyword3",
      "notes": "Ghi chú đặc biệt cho AI khi generate"
    }
  ],
  "instructors": [
    {
      "id": 1,
      "name": "Họ và tên",
      "email": "email@example.com",
      "title": "Chức danh",
      "bio": "Giới thiệu ngắn",
      "expertise": "Chuyên môn"
    }
  ],
  "sample_data": {
    "students_count": 15,
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
    "video_placeholder_url": "",
    "create_prerequisites": false,
    "create_drip_content": false,
    "create_bundles": false,
    "additional_ai_prompt": "Ghi chú thêm cho AI, ví dụ: tham chiếu tiêu chuẩn nào, phong cách viết ra sao..."
  }
}
```

### Cách 2: Dùng Admin UI

1. Vào **Structure → Content Tools → Seed Input** (`/workspace/content-tools/seed-input`)
2. Điền 6 phần: Organization, Training Program, Courses, Instructors, Sample Data, Advanced
3. Dùng nút **Add Course** / **Add Instructor** để thêm nhiều mục
4. Nhấn **View JSON** để xem trước
5. Nhấn **Save** → File lưu tại `.agent/seed/seed-input.json`

### Cách 3: Nhờ Claude Code tạo

Nói với Claude Code:

```
Tạo seed-input.json cho chương trình đào tạo [mô tả đề tài].
Công ty: [tên công ty], ngành [ngành nghề].
Cần [N] khóa học, [N] giảng viên.
```

### Các field quan trọng cần chú ý

| Field | Ý nghĩa | Ảnh hưởng |
|-------|---------|-----------|
| `language` | `vi` / `en` / `bilingual` | Ngôn ngữ content được generate |
| `tone` | `professional` / `friendly` / `academic` | Phong cách viết |
| `detail_level` | `basic` / `detailed` / `comprehensive` | Độ dài bài học (200 / 400-600 / 800+ words) |
| `chapters_count` | Số chapters/course | Cấu trúc khóa học |
| `lessons_per_chapter` | Số lessons/chapter | Tổng lessons = chapters x lessons_per_chapter |
| `keywords` | Keywords cho mỗi course | AI dùng để tạo nội dung chuyên sâu đúng chủ đề |
| `notes` | Ghi chú đặc biệt | AI đọc để customize content (VD: "tham chiếu TCVN") |
| `additional_ai_prompt` | Prompt bổ sung cho AI | Hướng dẫn thêm cho tất cả courses |

---

## Step 3: AI Generate Content

### Cách 1: Nhờ Claude Code generate (khuyến nghị)

Mở Claude Code trong project và nói:

```
Đọc file .agent/seed/seed-input.json và .agent/prompts/CONTENT_GENERATION.md,
sau đó generate toàn bộ content vào .agent/generated/
```

**Claude Code sẽ tự động:**
1. Đọc seed input
2. Tạo thư mục `.agent/generated/`
3. Chạy song song nhiều agent để generate:
   - `users/instructors.json` — Tài khoản giảng viên
   - `users/students.json` — Tài khoản học viên
   - `category/categories.json` — Hệ thống danh mục khóa học (Tự động cào từ mục courses trong seed)
   - `courses/course-{NNN}-{slug}.json` — Mỗi khóa học 1 file (bao gồm chapters + lessons)
   - `quizzes/quiz-course-{NNN}-final.json` — Quiz cho mỗi khóa học
   - `certificates/certificate-templates.json` — Mẫu chứng chỉ
   - `commerce/membership-plans.json` — Gói membership
   - `community/forum-topics.json` — Chủ đề forum
   - `community/badges.json` — Huy hiệu
   - `community/ratings.json` — Đánh giá khóa học
4. Tạo `manifest.json` tổng hợp

**Tips tối ưu:**
- Yêu cầu Claude dùng **Haiku agents** cho sample/demo data (nhanh 5-10x)
- Dùng **Sonnet agents** nếu cần content chất lượng cao cho production
- Yêu cầu chạy **song song** nhiều course cùng lúc

### Cách 2: Generate từng phần

Nếu muốn kiểm soát chặt hơn:

```
# Chỉ generate users
Generate users (instructors + students) theo seed-input.json

# Chỉ generate 1 course
Generate course 001 theo seed-input.json

# Chỉ generate quizzes
Generate quizzes cho tất cả courses theo seed-input.json
```

---

## Step 4: Sinh Ảnh AI (Visual Content)

Với các Content Type hiển thị ra giao diện cần hình ảnh minh họa đẹp (như Course Category, Slideshow, Blog, Introduction), bạn cần:

### 1. Dùng AI tạo ảnh đại diện
Yêu cầu AI (như Claude hoặc Midjourney) sinh ảnh phù hợp với chủ đề của học viện (VD: Chủ đề y tế thì mô tả y tá, bệnh viện). Lưu các ảnh này vào:
- `.agent/images/image_category/`
- `.agent/images/slideshows/`
- `.agent/images/blogs/`
- `.agent/images/introductions/`

### 2. Copy vào Drupal Files System
Chạy lệnh di chuyển vào mảng public của Drupal:
```bash
cp /home/phatle/projects/everlms/.agent/images/*/* /home/phatle/projects/everlms/web/sites/default/files/generated/
```
*(Lưu ý: Riêng ảnh trong `image_category` hệ thống import sẽ tự động đọc thẳng từ `.agent/images` ở thư mục gốc và sao chép vào `public://categories/`, do đó ảnh Category sẽ không bị ảnh hưởng nếu bạn bỏ qua lệnh cp này)*

### 3. Cập nhật tên ảnh vào JSON 
Mở các file `category/categories.json`, `slideshows.json`, `blogs.json`, `introductions.json` và bổ sung key `image_filename` và `image_alt`. Lệnh import sẽ dựa vào mapping này để gán ảnh.

---

## Step 5: Validate JSON

Kiểm tra JSON hợp lệ trước khi import.

### Cách nhanh: dùng Python

```bash
python3 -c "
import json, glob, sys
errors = 0
for f in sorted(glob.glob('.agent/generated/**/*.json', recursive=True)):
    try:
        json.load(open(f))
        print(f'  OK: {f}')
    except json.JSONDecodeError as e:
        print(f'  ERROR: {f} — {e}')
        errors += 1
print(f'\nTotal: {len(list(glob.glob(\".agent/generated/**/*.json\", recursive=True)))} files, {errors} errors')
sys.exit(1 if errors else 0)
"
```

### Nếu có lỗi JSON (thường do AI tạo unescaped quotes trong HTML)

**Cách fix thủ công:**
1. Mở file lỗi, tìm dòng báo lỗi
2. Tìm dấu `"` không được escape trong nội dung HTML
3. Thay bằng `\"` hoặc `&quot;`

**Cách fix tự động:**
```
Nhờ Claude Code: Fix unescaped quotes trong file [tên file] ở .agent/generated/
```

---

## Step 6: Import vào Database

### Dry run trước (kiểm tra không ghi DB)

```bash
ddev drush everlms:import --dry-run -y
```

### Import toàn bộ

```bash
# Import tất cả theo manifest.json
ddev drush everlms:import -y

# Hoặc import từng loại
ddev drush everlms:import --type=users -y
ddev drush everlms:import --type=courses -y
ddev drush everlms:import --type=quizzes -y
ddev drush everlms:import --type=certificates -y
ddev drush everlms:import --type=commerce -y
ddev drush everlms:import --type=community -y

# Hoặc import 1 file cụ thể
ddev drush everlms:import --file=courses/course-001-slug.json -y
```

### Thứ tự import (quan trọng)

Import **phải** theo thứ tự này vì có dependency:

```
1. users/instructors.json      ← Tạo user accounts trước
2. users/students.json
3. courses/course-*.json       ← Cần instructor user đã tồn tại
4. quizzes/quiz-*.json         ← Cần course đã tồn tại (để link)
5. certificates/*.json
6. commerce/*.json
7. community/*.json            ← Cần course + user đã tồn tại
```

`manifest.json` đã định nghĩa sẵn thứ tự này trong field `import_order`.

---

## Step 7: Kiểm tra Kết quả

### Qua Drush

```bash
# Đếm entities
ddev drush everlms:clean --dry-run
```

### Qua Admin UI

1. Vào **Structure → Content Tools** (`/workspace/content-tools`)
2. Xem Dashboard với 3 cards:
   - **Entity Counts** — Số lượng từng loại entity
   - **Seed Input Status** — File seed input hiện tại
   - **Generated Content** — File content đã generate

### Qua Frontend

1. Mở trang chủ → Kiểm tra danh sách khóa học
2. Vào 1 khóa học → Kiểm tra chapters, lessons, nội dung
3. Làm quiz → Kiểm tra câu hỏi, đáp án
4. Xem forum → Kiểm tra topics và replies

---

## Ví dụ: Tạo Chương trình "Kỹ năng Mềm cho Doanh nghiệp"

### Bước 1: Clean data cũ

```bash
ddev drush everlms:clean -y
```

### Bước 2: Tạo seed input

Nói với Claude Code:

```
Tạo seed-input.json cho chương trình đào tạo Kỹ năng Mềm cho nhân viên.
Công ty: Công ty TNHH Công nghệ ABC, ngành phần mềm.
Cần 4 khóa học:
1. Giao tiếp Hiệu quả (beginner)
2. Làm việc Nhóm (beginner)
3. Quản lý Thời gian (intermediate)
4. Thuyết trình Chuyên nghiệp (advanced, paid 300,000 VND)
2 giảng viên, 20 học viên mẫu.
```

### Bước 3: Generate content

```
Đọc .agent/seed/seed-input.json và .agent/prompts/CONTENT_GENERATION.md,
generate toàn bộ content vào .agent/generated/ bằng Haiku agents, chạy song song.
```

### Bước 4: Validate

```bash
python3 -c "import json,glob; [print(f) for f in glob.glob('.agent/generated/**/*.json',recursive=True) if not json.load(open(f))]"
```

### Bước 5: Import

```bash
ddev drush everlms:import -y
```

### Bước 6: Kiểm tra

```bash
ddev drush everlms:clean --dry-run
```

---

## FAQ

### Q: Tôi muốn thêm khóa học vào chương trình đã có, không muốn xóa data cũ?

Hiện tại chưa hỗ trợ incremental import (xem [PROBLEMS.md](./PROBLEMS.md) — mục thảo luận #3). Workaround:
1. Tạo seed input chỉ với khóa học mới
2. Generate content chỉ cho khóa học đó
3. Import file course mới (import sẽ skip entities trùng tên)

### Q: Content do AI generate có chính xác không?

- **Haiku**: Nội dung đúng tổng quan, đủ tốt cho demo/test
- **Sonnet**: Chi tiết hơn, có tham chiếu tiêu chuẩn (TCVN, ISO)
- **Khuyến nghị**: Dùng Haiku generate trước → review → dùng Sonnet generate lại phần cần chi tiết

### Q: Import bị lỗi thì sao?

- Kiểm tra log: `ddev drush everlms:import --dry-run -y`
- Lỗi phổ biến nhất: JSON không hợp lệ (unescaped quotes) → chạy validate ở Step 4
- Lỗi field name: xem [PROBLEMS.md](./PROBLEMS.md) Problem #10 cho danh sách field names đúng
- Nếu import fail giữa chừng: clean rồi import lại (chưa có rollback — xem PROBLEMS.md mục thảo luận #1)

### Q: Có thể dùng AI khác ngoài Claude Code không?

Có — bất kỳ AI nào đọc được file JSON và tạo được output JSON theo format trong [CONTENT_GENERATION.md](../../prompts/CONTENT_GENERATION.md) đều dùng được. Ví dụ: Gemini, GPT-4, Copilot.

### Q: Tốn bao nhiêu thời gian và token?

| Hạng mục | Haiku | Sonnet |
|----------|-------|--------|
| 1 course (4 chapters, 16 lessons) | 1-3 phút | 8-20 phút |
| 5 courses song song | 5-18 phút | 10-58 phút |
| 1 quiz (15 questions) | 1-2 phút | 3-5 phút |
| Tokens/course | 10K-55K | 32K-104K |

---

## Tham khảo

- [01-clean-content.md](./01-clean-content.md) — Chi tiết Clean Content Form
- [02-seed-input-form.md](./02-seed-input-form.md) — Chi tiết Seed Input Form
- [03-ai-content-generator.md](./03-ai-content-generator.md) — Chi tiết AI Content Generator
- [04-report.md](./04-report.md) — Báo cáo chạy thử lần 1
- [PROBLEMS.md](./PROBLEMS.md) — Danh sách vấn đề đã ghi nhận
- [CONTENT_GENERATION.md](../../prompts/CONTENT_GENERATION.md) — Prompt hướng dẫn AI generate
