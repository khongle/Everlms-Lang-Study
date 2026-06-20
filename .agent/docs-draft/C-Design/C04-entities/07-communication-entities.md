# Communication Entities

> Notification, Forum (Topic + Reply), Rating/Review, Lesson Comment, FAQ.

---

## Notification (`lms_notification`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_recipient` | entity_reference (user) | User nhận thông báo |
| `field_type` | list_string | `enrollment` / `quiz_result` / `certificate` / `payment` / `forum_reply` / `membership_expiry` / `new_lesson` / `announcement` / `reminder` |
| `field_title` | string | Tiêu đề thông báo |
| `field_message` | text_long | Nội dung chi tiết |
| `field_link` | string | URL dẫn đến nội dung liên quan |
| `field_read` | boolean | Đã đọc? |
| `field_channel` | list_string (multi) | `in_app` / `email` / `push` |
| `field_reference_type` | string | Entity type được reference (course, quiz, order...) |
| `field_reference_id` | integer | Entity ID được reference |
| `created` | timestamp | Thời điểm tạo |

### Business Rules
- `field_read = false` → hiện badge count trên bell icon
- User có thể cấu hình preferences: nhận loại nào qua channel nào
- Auto cleanup: xóa notifications > 90 ngày đã đọc

---

## Forum Topic (`lms_forum_topic`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tiêu đề topic |
| `field_body` | text_long | Nội dung (rich text, code blocks, images) |
| `field_author` | entity_reference (user) | Người tạo |
| `field_course` | entity_reference | Course scope (null = global forum) |
| `field_category` | entity_reference (taxonomy: `forum_category`) | Phân loại |
| `field_pinned` | boolean | Ghim lên đầu |
| `field_locked` | boolean | Khóa topic (không cho reply) |
| `field_reply_count` | integer | Số lượng replies (denormalized) |
| `field_last_reply_date` | timestamp | Thời điểm reply cuối |
| `field_views` | integer | Lượt xem |
| `field_resolved` | boolean | Đã giải quyết (answered) |
| `status` | boolean | Published / Hidden |

### Business Rules
- Course-scoped forum: chỉ enrolled students + instructor mới xem/post
- Global forum: tất cả authenticated users
- `field_pinned = true` → luôn hiện trên đầu listing
- `field_locked = true` → disable reply form

---

## Forum Reply (`lms_forum_reply`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_topic` | entity_reference | Parent topic |
| `field_parent_reply` | entity_reference (self) | Parent reply (for nested threads) |
| `field_body` | text_long | Nội dung reply (rich text) |
| `field_author` | entity_reference (user) | Người reply |
| `field_is_answer` | boolean | Được đánh dấu là best answer |
| `field_upvotes` | integer | Số upvotes |
| `status` | boolean | Published / Hidden |

### Business Rules
- `field_is_answer = true` → highlight reply + mark topic as resolved
- Chỉ topic author hoặc instructor mới mark best answer
- Max nesting depth: 3 levels

---

## Rating / Review (`lms_rating`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_course` | entity_reference | Course được rate |
| `field_author` | entity_reference (user) | User rate |
| `field_score` | integer | Rating 1-5 stars |
| `field_title` | string | Review title (optional) |
| `field_body` | text_long | Review text |
| `field_instructor_response` | text_long | Phản hồi từ instructor |
| `field_helpful_count` | integer | Số người thấy hữu ích |
| `field_verified_purchase` | boolean | User đã enroll/mua course |
| `status` | boolean | Published / Pending review |

### Business Rules
- **1 rating per user per course** — unique constraint `(field_course, field_author)`
- Chỉ enrolled users mới rate (`field_verified_purchase = true` auto-set)
- Average rating auto-calculated, cached trên course entity
- Admin phải approve review trước khi public (nếu moderation enabled)

---

## Lesson Comment (`lms_comment`)

> Có thể dùng Drupal core Comment module hoặc custom entity.

| Field | Type | Mô tả |
|-------|------|-------|
| `field_lesson` | entity_reference | Lesson |
| `field_author` | entity_reference (user) | Người comment |
| `field_body` | text_long | Nội dung comment |
| `field_parent` | entity_reference (self) | Parent comment (nested) |
| `field_likes` | integer | Số likes |
| `status` | boolean | Published / Pending |

### Business Rules
- Chỉ enrolled users mới comment trên lesson
- Max nesting: 2 levels
- Instructor reply highlighted với badge "Instructor"

---

## FAQ (`lms_faq`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Câu hỏi |
| `field_answer` | text_long | Trả lời (rich text) |
| `field_scope` | list_string | `global` / `course` / `lesson` |
| `field_course` | entity_reference | Course (nếu scope = course) |
| `field_lesson` | entity_reference | Lesson (nếu scope = lesson) |
| `field_category` | entity_reference (taxonomy: `faq_category`) | Phân loại |
| `weight` | integer | Thứ tự hiển thị |
| `status` | boolean | Published / Draft |

### Business Rules
- `field_scope = global` → hiển thị trên `/faq`
- `field_scope = course` → hiển thị trong course detail page (tab FAQ)
- `field_scope = lesson` → hiển thị dưới lesson content
- Accordion UI: click expand/collapse

---

## Student Note (`lms_note`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_user` | entity_reference (user) | User |
| `field_lesson` | entity_reference | Lesson |
| `field_course` | entity_reference | Course (denormalized for quick lookup) |
| `field_body` | text_long | Nội dung ghi chú (basic rich text) |
| `changed` | timestamp | Lần chỉnh sửa cuối |
| `created` | timestamp | Thời điểm tạo |

### Business Rules
- 1 note per user per lesson — unique constraint `(field_user, field_lesson)`
- Auto-save khi typing (debounce 2s, AJAX)
- User có thể xem tất cả notes tại `/my-notes`
- Export notes dạng PDF/TXT

