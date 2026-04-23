# lms_rating — Course Ratings & Reviews Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_rating` |
| Name | LMS Rating |
| Layer | **Communication** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_course` |

## Mô tả

Module đánh giá và nhận xét khóa học (1-5 sao + text review). Cung cấp entity Rating, RatingBlock để hiển thị form rating, và admin controller để moderate reviews.

---

## Provided Entities

### `lms_rating` (Content Entity)
| Fields | Description |
|--------|-------------|
| uid | Người đánh giá |
| course ref | → lms_course |
| stars | 1-5 |
| review | Text review |
| status | pending/approved/rejected |
| created | Timestamp |

---

## Provided Routes

| Route ID | Path | Controller | Access |
|----------|------|-----------|--------|
| `lms_rating.admin_list` | `/workspace/ratings` | `RatingManagerController::list` | `administer lms_course` |
| `lms_rating.admin_action` | `/workspace/ratings/{lms_rating}/{action}` | `RatingManagerController::executeAction` | `administer lms_course` |

---

## Provided Services

Không có services file.

---

## Provided Plugins

| Plugin Type | Implementation | Purpose |
|-------------|---------------|---------|
| Block | `RatingBlock` | Hiển thị rating form + average score trên course detail page |

---

## Hook Implementations

Không có `.module` file.

---

## Forms

| Form | Purpose |
|------|---------|
| `RatingForm` | Submit rating + review cho khóa học |

---

## Templates & Cache Strategy

`RatingBlock` cache per-user per-route (user-specific vì hiện thị form nếu chưa rate). Admin list cache theo entity list.

---

## Related Units

- **C03** § Communication Domain — entity Rating
- **C05** `04-certificate-communication.md` — layer overview
- `lms_course.md` — `lms_course_detail` template embed rating form + reviews

---

## Known Issues / Debt

- Không có validation chỉ enrolled users mới được rate — bất kỳ user nào có `access content` đều có thể submit.
- Action endpoint `/workspace/ratings/{lms_rating}/{action}` dùng string action thay vì specific routes — SQL injection risk nếu action không validate đúng.
- Không có rate limiting — có thể spam reviews.
