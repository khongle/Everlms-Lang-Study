# EverLMS — Clean Content Form

> Form cho phép admin xóa toàn bộ content trong hệ thống với cơ chế xác nhận 2 lượt để tránh xóa nhầm.

## 1. Tổng quan

### Mục đích
- Xóa sạch toàn bộ content đã generate hoặc sample data
- Phục vụ việc re-generate content mới từ đầu
- Chỉ admin có quyền truy cập

### Route & Menu
- **Route:** `/admin/everlms/content-tools/clean`
- **Menu parent:** Content Tools (trong admin toolbar)
- **Permission:** `administer everlms content tools`

---

## 2. Giao diện Form

### Layout

```
┌─────────────────────────────────────────────────────────────┐
│  Clean Content                                    [Breadcrumb] │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ⚠️  WARNING: Thao tác này sẽ xóa toàn bộ content           │
│  trong hệ thống. Hành động này KHÔNG THỂ hoàn tác.           │
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │  Chọn loại content cần xóa:                             │ │
│  │                                                         │ │
│  │  ☑ Courses, Chapters, Lessons         (xxx entities)    │ │
│  │  ☑ Quizzes, Questions, Answers        (xxx entities)    │ │
│  │  ☑ Quiz Results, Responses            (xxx entities)    │ │
│  │  ☑ User Progress (Course/Chapter/     (xxx entities)    │ │
│  │    Lesson)                                              │ │
│  │  ☑ Orders, Order Items                (xxx entities)    │ │
│  │  ☑ Memberships, User Memberships      (xxx entities)    │ │
│  │  ☑ Certificates (Templates &          (xxx entities)    │ │
│  │    User Certs)                                          │ │
│  │  ☑ Ratings, Reviews                   (xxx entities)    │ │
│  │  ☑ Forum Topics, Replies              (xxx entities)    │ │
│  │  ☑ Notifications                      (xxx entities)    │ │
│  │  ☑ Coupons, Wishlists                 (xxx entities)    │ │
│  │  ☑ Badges, User Badges               (xxx entities)    │ │
│  │  ☑ FAQs, Student Notes               (xxx entities)    │ │
│  │  ☑ Lesson Comments                   (xxx entities)     │ │
│  │  ☐ Users (trừ admin hiện tại)         (xxx users)       │ │
│  │  ☐ Taxonomy Terms                     (xxx terms)       │ │
│  │                                                         │ │
│  │  [Select All]  [Deselect All]                           │ │
│  └─────────────────────────────────────────────────────────┘ │
│                                                               │
│  [ 🗑  Clean Selected Content ]                               │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Checkbox Groups

| Group | Entities bị xóa | Mặc định |
|-------|-----------------|----------|
| Introduction Sections | `node:introduction` | ☑ Checked |
| Slideshow Banners | `node:slideshow` | ☑ Checked |
| Glossary Terms | `node:glossary` | ☑ Checked |
| Blog Articles | `node:blog` | ☑ Checked |
| Courses, Chapters, Lessons | `lms_course`, `lms_chapter`, `lms_lesson` + attached files/media | ☑ Checked |
| Quizzes, Questions, Answers | `lms_quiz`, `lms_question`, `lms_answer`, `lms_matching_pair` | ☑ Checked |
| Quiz Results, Responses | `lms_quiz_result`, `lms_question_response` | ☑ Checked |
| User Progress | `lms_user_course`, `lms_user_chapter`, `lms_user_lesson` | ☑ Checked |
| Orders, Order Items | `lms_order`, `lms_order_item`, `lms_payment_transaction` | ☑ Checked |
| Memberships | `lms_membership_plan`, `lms_user_membership` | ☑ Checked |
| Certificates | `lms_certificate_template`, `lms_user_certificate` | ☑ Checked |
| Ratings, Reviews | `lms_rating` | ☑ Checked |
| Forum Topics, Replies | `lms_forum_topic`, `lms_forum_reply` | ☑ Checked |
| Notifications | `lms_notification` | ☑ Checked |
| Coupons, Wishlists | `lms_coupon`, `lms_wishlist` | ☑ Checked |
| Badges | `lms_badge`, `lms_user_badge` | ☑ Checked |
| FAQs, Student Notes | `lms_faq`, `lms_student_note` | ☑ Checked |
| Lesson Comments | `lms_lesson_comment` | ☑ Checked |
| Users | Tất cả users trừ admin đang đăng nhập (uid=1) | ☐ Unchecked |
| Taxonomy Terms | Tất cả terms trong vocabularies LMS | ☐ Unchecked |

> [!IMPORTANT]
> - **Users** và **Taxonomy Terms** mặc định **không checked** vì thường muốn giữ lại
> - Mỗi checkbox hiển thị **số lượng entities** hiện có trong ngoặc để admin biết sẽ xóa bao nhiêu
> - Phải chọn ít nhất 1 group mới enable nút Clean

---

## 3. Cơ chế Confirm 2 lượt

### Flow xác nhận

```
Click "Clean Selected Content"
    │
    ▼
┌──────────────────────────────────────────────────┐
│  CONFIRM LẦN 1 — Modal Dialog                   │
│                                                  │
│  ⚠️  Bạn sắp xóa:                               │
│  • 45 Courses, 120 Chapters, 380 Lessons         │
│  • 30 Quizzes, 200 Questions                     │
│  • 150 Orders                                    │
│  • ... (liệt kê theo group đã chọn)             │
│                                                  │
│  Tổng cộng: 1,234 entities sẽ bị xóa vĩnh viễn. │
│                                                  │
│  [ Hủy ]              [ Tiếp tục xác nhận → ]   │
└──────────────────────────────────────────────────┘
    │
    ▼ (Click "Tiếp tục xác nhận")
┌──────────────────────────────────────────────────┐
│  CONFIRM LẦN 2 — Modal Dialog                   │
│                                                  │
│  🔴 CẢNH BÁO: Hành động này KHÔNG THỂ hoàn tác  │
│                                                  │
│  Gõ CLEAN vào ô bên dưới để xác nhận:           │
│                                                  │
│  ┌──────────────────────────────────────┐        │
│  │                                      │        │
│  └──────────────────────────────────────┘        │
│                                                  │
│  [ Hủy ]              [ 🗑  Xóa vĩnh viễn ]     │
│                                                  │
│  (Nút "Xóa vĩnh viễn" chỉ enable khi gõ        │
│   đúng "CLEAN")                                  │
└──────────────────────────────────────────────────┘
    │
    ▼ (Gõ "CLEAN" + Click "Xóa vĩnh viễn")

    Thực thi xóa (Batch API)
```

### Chi tiết kỹ thuật Confirm

| Bước | Loại | Chi tiết |
|------|------|---------|
| Confirm 1 | Modal dialog (AJAX) | Hiển thị tóm tắt số lượng entities sẽ bị xóa theo từng group |
| Confirm 2 | Modal dialog (AJAX) | Input text field, validate client-side + server-side giá trị = "CLEAN" (case-sensitive) |
| Nút Xóa | Disabled by default | Chỉ enable khi input value === "CLEAN", dùng JavaScript `input` event listener |

---

## 4. Quá trình xóa

### Batch Processing

Sử dụng **Drupal Batch API** để xóa từng group, tránh timeout:

```
Batch bắt đầu
    │
    ├── Step 1: Xóa Lesson Comments
    ├── Step 2: Xóa Student Notes, FAQs
    ├── Step 3: Xóa Forum Replies → Forum Topics
    ├── Step 4: Xóa Notifications
    ├── Step 5: Xóa Ratings/Reviews
    ├── Step 6: Xóa User Badges → Badges
    ├── Step 7: Xóa User Certificates → Certificate Templates
    ├── Step 8: Xóa Question Responses → Quiz Results
    ├── Step 9: Xóa User Progress (Lesson → Chapter → Course)
    ├── Step 10: Xóa Payment Transactions → Order Items → Orders
    ├── Step 11: Xóa User Memberships → Membership Plans
    ├── Step 12: Xóa Coupons, Wishlists
    ├── Step 13: Xóa Answers, Matching Pairs → Questions → Quizzes
    ├── Step 14: Xóa Lessons → Chapters → Courses
    ├── Step 15: Xóa Users (nếu chọn)
    ├── Step 16: Xóa Taxonomy Terms (nếu chọn)
    │
    ▼
Batch hoàn thành → Hiển thị kết quả
```

> [!IMPORTANT]
> **Thứ tự xóa rất quan trọng** — phải xóa entities phụ thuộc trước, entities gốc sau để tránh lỗi foreign key / entity reference.

### Kết quả sau khi xóa

```
┌──────────────────────────────────────────────────┐
│  ✅ Clean Content hoàn thành                     │
│                                                  │
│  Đã xóa:                                        │
│  • 45 Courses, 120 Chapters, 380 Lessons         │
│  • 30 Quizzes, 200 Questions, 600 Answers        │
│  • 150 Quiz Results                              │
│  • ...                                           │
│  ──────────────────────────────────────           │
│  Tổng: 1,234 entities đã được xóa               │
│  Thời gian: 12.5 giây                           │
│                                                  │
│  [ ← Quay lại Content Tools ]                    │
└──────────────────────────────────────────────────┘
```

---

## 5. Drush Command

### `drush everlms:clean`

```bash
# Xóa tất cả content (interactive confirm trong terminal)
drush everlms:clean

# Xóa chỉ courses
drush everlms:clean --types=courses

# Xóa courses + quizzes + orders
drush everlms:clean --types=courses,quizzes,orders

# Xóa tất cả bao gồm users
drush everlms:clean --include-users

# Xóa tất cả bao gồm taxonomy terms
drush everlms:clean --include-taxonomies

# Xóa không cần confirm (dùng trong scripts/CI)
drush everlms:clean --yes

# Dry-run: chỉ hiển thị số lượng sẽ xóa
drush everlms:clean --dry-run
```

### Options

| Option | Mô tả |
|--------|-------|
| `--types` | Danh sách types cách nhau bằng dấu phẩy: `introductions`, `slideshows`, `glossaries`, `articles`, `courses`, `quizzes`, `results`, `progress`, `orders`, `memberships`, `certificates`, `ratings`, `forums`, `notifications`, `coupons`, `badges`, `faqs`, `comments` |
| `--include-users` | Bao gồm xóa users (trừ uid=1) |
| `--include-taxonomies` | Bao gồm xóa taxonomy terms |
| `--yes` | Bỏ qua confirm prompt |
| `--dry-run` | Chỉ đếm và hiển thị, không xóa |

---

## 6. Technical Notes

### Permission
- Form chỉ accessible bởi role **Administrator**
- Permission: `administer everlms content tools`
- Drush command yêu cầu quyền tương đương

### Logging
- Ghi log vào `watchdog` mỗi lần clean: ai thực hiện, xóa những gì, bao nhiêu entities
- Log level: `warning`

### Safety
- Không xóa file vật lý trên server (media files) — chỉ xóa entities
- Admin hiện tại (uid=1) không bao giờ bị xóa
- Nếu batch fail giữa chừng, hiển thị entities đã xóa và lỗi gặp phải
