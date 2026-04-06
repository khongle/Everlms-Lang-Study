# Content Tools — Problems & Notes

> Ghi nhận các vấn đề phát hiện trong quá trình implement và chạy thử content tools. Dùng để thảo luận và cải thiện.

## Problem #1: Drush `--yes` không bypass `io()->ask()`

**Mô tả:** Khi chạy `drush everlms:clean -y`, Drush tự động confirm `io()->confirm()` nhưng `io()->ask()` (bước gõ "CLEAN") vẫn chờ input, dẫn tới pipe input không hoạt động đúng.

**Fix:** Detect `$this->input()->getOption('yes')` và skip cả 2 bước confirm khi `-y` được truyền.

**Status:** ✅ Fixed

---

## Problem #2: Drush option `--yes` bị trùng với global option

**Mô tả:** Khi khai báo `'yes' => FALSE` trong options array của Drush command, bị lỗi `An option named "yes" already exists` vì Drush đã có global `--yes/-y`.

**Fix:** Không khai báo `--yes` trong command options, dùng `$this->input()->getOption('yes')` để đọc global option.

**Status:** ✅ Fixed

---

## Problem #3: Entity types thực tế khác với docs

**Mô tả:** Tài liệu 01-requirement đề cập một số entity types không tồn tại trong code thực tế:
- `lms_payment_transaction` — không có (payment info nằm trong `lms_order`)
- `lms_lesson_comment` — không có entity riêng
- `lms_coupon` — không có entity riêng
- `lms_user_chapter` — không có (chỉ có `lms_user_course` và `lms_user_lesson`)
- `lms_student_note` → thực tế là `lms_note`

**Impact:** ContentToolsManager phải dùng đúng entity type IDs từ code, không phải từ docs.

**Status:** ⚠️ Noted — docs-v2 cần cập nhật cho khớp code

---

## Problem #4: Ratings import thiếu cơ chế map course

**Mô tả:** File `ratings.json` dùng `course_title` để reference course, nhưng import script hiện chưa có logic để lookup course by title → gán field_course. Tương tự, `reviewer` field dùng username nhưng chưa có lookup logic.

**Status:** 🔧 Cần bổ sung import logic cho ratings

---

## Problem #5: Instructor role chưa chắc tồn tại

**Mô tả:** Import users gán role `instructor` nhưng role này phải được tạo sẵn trong Drupal. Nếu chưa có role, `$user->addRole('instructor')` sẽ fail silently hoặc throw exception.

**Recommendation:** Kiểm tra role tồn tại trước khi gán, hoặc tạo role trong `lms_base.install`.

**Status:** ⚠️ Cần verify

---

## Problem #6: Course field mapping chưa đầy đủ

**Mô tả:** Import script map `field_price`, `field_description`, `field_short_description`... nhưng chưa verify tất cả field names khớp với entity definition thực tế. Ví dụ:
- `field_duration` có thể là integer (phút) hoặc string
- `field_is_preview` có thể là `field_free_preview`
- `field_display_name` cho user có thể không tồn tại

**Recommendation:** Cần đọc entity class definitions để verify field names trước khi import.

**Status:** 🔧 Cần verify từng field

---

## Problem #7: Generate content tốn token rất nhiều

**Mô tả:** Mỗi course 16-20 lessons x 400-600 words = ~8,000-12,000 words/course. Tổng 5 courses ≈ 50,000+ words output. Với Opus thì token cost rất cao.

**Optimization đã áp dụng:**
- Dùng Sonnet agents thay vì Opus để generate content
- Chạy song song 5 agents cho 5 courses
- Tách file theo course để không cần load tất cả vào 1 context

**Recommendation thêm:**
- Có thể dùng Haiku cho content generation nếu chất lượng vẫn OK
- Hoặc giảm số words/lesson cho lần generate đầu, review rồi generate chi tiết hơn

**Status:** 📝 Observation

---

## Problem #8: DDEV environment — `php` command not found

**Mô tả:** Trên host machine (WSL2), `php` binary không available trực tiếp. Phải dùng `ddev drush` hoặc `ddev exec php` thay vì `php vendor/bin/drush`.

**Impact:** Tài liệu và scripts cần note rõ là dùng `ddev drush` commands.

**Status:** 📝 Noted

---

## Problem #9: Entity relationship model — parent references children

**Mô tả:** Trong EverLMS, entity relationship KHÔNG dùng "child references parent" (ví dụ lesson.field_chapter) mà dùng **"parent references children"**:
- Course.field_chapters → [Chapter IDs]
- Chapter.field_lessons → [Lesson IDs]
- Quiz.field_questions → [Question IDs]
- Question.field_choices → [Answer IDs]
- Question.field_matching_pairs → [MatchingPair IDs]

**Impact:** Import script ban đầu đã set sai (ví dụ `'field_chapter' => $chapter->id()` trên Lesson). Phải tạo children trước, rồi tạo parent và link children vào.

**Fix:** Rewrite import flow: create lessons → create chapter + link lessons → create course + link chapters.

**Status:** ✅ Fixed

---

## Problem #10: Field name mismatches giữa code ban đầu và entity thực tế

| Import code cũ | Entity thực tế | Ghi chú |
|----------------|---------------|---------|
| `field_is_preview` | `field_free_preview` | Lesson |
| `field_instructions` | `field_body` | Quiz |
| `field_pass_threshold` | `field_threshold` | Quiz |
| `field_max_attempts` | `field_take_quiz_number` | Quiz |
| `field_display_style` | `field_form_style` | Quiz |
| `field_is_correct` | `field_correct` | Answer |
| `field_criteria_type` + `field_rarity` | `field_criteria` (JSON) + no rarity | Badge |
| `field_points` | `field_xp_reward` | Badge |
| `field_layout` = "landscape" | `field_orientation` = "landscape", `field_design` = "modern" | Certificate |
| `field_short_description` = string | `field_short_description` = text_long (needs value+format) | Course |

**Status:** ✅ Fixed in import script

---

## Problem #11: Course generation agents rất chậm

**Mô tả:** Mỗi course agent phải generate 12-20 lessons x 400-600 words = file JSON 50-100KB. Với Sonnet agent, thời gian generate mỗi course khoảng 3-5 phút. Tổng 5 courses chạy song song ≈ 5 phút, nhưng nếu chạy tuần tự sẽ mất 15-25 phút.

**Observation:** Đây là bottleneck chính của toàn bộ flow. Quizzes generate nhanh hơn nhiều (15 questions ≈ 1-2 phút/quiz).

**Recommendation:**
- Giảm lesson content xuống 200-300 words cho lần generate đầu
- Hoặc chia nhỏ: generate outline trước, generate content chi tiết sau
- Hoặc dùng Haiku cho content generation (nhanh hơn, rẻ hơn)

**Status:** 📝 Observation

---

## Problem #12: AI-generated JSON có thể chứa unescaped quotes

**Mô tả:** Course 001 JSON bị invalid vì AI (Haiku) tạo content HTML chứa dấu `"` không được escape trong JSON string. Ví dụ: `<em>"thái độ an toàn kém"</em>` → dấu `"` bên trong làm hỏng JSON.

**Fix:** Thêm bước post-processing Python để fix unescaped quotes trong content fields trước khi import.

**Recommendation:** Thêm validation step tự động trong import flow, hoặc hướng dẫn AI dùng `&quot;` thay vì `"` trong HTML content.

**Status:** ✅ Fixed manually, cần tự động hóa

---

## Kết quả chạy thử

**Import thành công:** 378 entities, 0 errors

| Loại | Số lượng |
|------|---------|
| Users (3 instructors + 15 students) | 18 |
| Courses | 5 |
| Chapters | 22 |
| Lessons | 79 |
| Quizzes | 5 |
| Questions + Answers + Pairs | 225 |
| Certificate Templates | 5 |
| Membership Plans | 3 |
| Forum Topics + Replies | 14 |
| Badges | 7 |
| **Tổng** | **378** |

---

## Cần thảo luận thêm

1. **Import rollback:** Nếu import fail giữa chừng, hiện tại không có cơ chế rollback. Cần xem xét dùng database transaction cho mỗi file import.

2. **Content quality review:** Cần workflow để review content đã generate trước khi import. Có thể thêm step preview trong CLI.

3. **Incremental import:** Hiện tại import chỉ skip nếu entity trùng tên. Cần cơ chế update entity nếu đã tồn tại.

4. **Media files:** Courses chỉ có text content. Cần cơ chế để AI generate hoặc link placeholder images, videos.

5. **Ratings import:** Chưa implement — cần lookup course by name rồi gán `field_course`, lookup student by username rồi gán `uid`.

6. **JSON validation tự động:** Cần thêm script validate + fix unescaped quotes trước khi import, không phải fix thủ công.

7. **Sonnet vs Haiku cho content generation:** Sonnet agents bị timeout vì content quá dài. Haiku nhanh hơn nhiều (1-2 phút/course vs 5+ phút) với chất lượng đủ tốt cho sample data. Recommend dùng Haiku cho content generation, Sonnet/Opus cho review.
