# lms_blog — Blog & News Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_blog` |
| Name | LMS Blog |
| Layer | **Content / Additional** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_ai` |

## Mô tả

Module blog/news articles. Cung cấp custom entity `lms_article` (thay vì dùng Drupal node), có tích hợp AI để generate nội dung bài viết. Phụ thuộc `lms_ai` — có thể dùng AI để auto-generate hoặc enhance blog content.

---

## Provided Entities

### `lms_article` (Content Entity — `LmsArticle`)
| Fields | Description |
|--------|-------------|
| title | Tiêu đề bài viết |
| body | Nội dung (rich text) |
| summary | Tóm tắt |
| thumbnail | Ảnh đại diện |
| category | Phân loại |
| tags | Tags |
| author | uid |
| status | Published/draft |
| created/changed | Timestamps |

Handlers: `LmsArticleListBuilder`, `LmsArticleAccessControlHandler`, `LmsArticleForm`

---

## Provided Routes

| Route ID | Path | Controller/Form | Access |
|----------|------|----------------|--------|
| `entity.lms_article.collection` | `/manage/blog` | Entity list | `administer lms_blog` |
| `entity.lms_article.add_form` | `/manage/blog/add` | Entity add form | entity create access |
| `entity.lms_article.edit_form` | `/manage/blog/{lms_article}/edit` | Entity edit form | entity update access |
| `entity.lms_article.delete_form` | `/manage/blog/{lms_article}/delete` | Entity delete form | entity delete access |
| `lms_blog.frontend.collection` | `/news` | `LmsBlogController::index` | public |
| `lms_blog.frontend.canonical` | `/news/{node}` | `LmsBlogController::view` | public |

> Lưu ý: route canonical dùng `{node}` parameter kiểu `entity:node` — có thể là mismatch với `lms_article` entity.

---

## Provided Services

Không có services file.

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Frontend routes public — cache per-page. `/news/{node}` dùng node entity thay vì lms_article — cần clarify.

---

## Related Units

- **C05** `01-foundation-content.md` — content layer
- `lms_ai.md` — AI content generation dependency
- `lms_course.md` — homepage hiển thị `latest_news`

---

## Known Issues / Debt

- Route `lms_blog.frontend.canonical` dùng `{node}` parameter kiểu `entity:node` trong khi entity là `lms_article` — inconsistency, có thể là bug.
- Dependency `lms_ai` khá heavy cho một blog module — nếu AI chỉ là optional feature nên dùng soft dependency.
- Không có comment system cho articles.
- Không rõ AI được tích hợp ở điểm nào trong article creation flow.
