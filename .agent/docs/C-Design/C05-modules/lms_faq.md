# lms_faq — FAQ Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_faq` |
| Name | LMS FAQ |
| Layer | **Communication** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base` |

## Mô tả

Module FAQ 3-scope: global (site-wide), course-level, lesson-level. Module minimal — chỉ có entity definition, không có route hay service riêng. FAQ được render inline trong các trang tương ứng.

---

## Provided Entities

### `lms_faq` (Content Entity — `Faq`)
| Fields | Description |
|--------|-------------|
| question | Câu hỏi (translatable) |
| answer | Câu trả lời (translatable, rich text) |
| scope | global / course / lesson |
| course ref | Ref nếu scope = course |
| lesson ref | Ref nếu scope = lesson |
| weight | Thứ tự hiển thị |
| status | Published/unpublished |

---

## Provided Routes

Không có routing file riêng. CRUD routes được cung cấp qua `AdminHtmlRouteProvider` + workspace routes trong `lms_course.routing.yml`:

- `/workspace/content/faqs/add` — `WorkspaceContentController::faqCreateForm`
- `/workspace/content/faqs/{lms_faq}/edit` — `WorkspaceContentController::faqEditForm`
- `/workspace/content/faqs/{lms_faq}/view` — `WorkspaceContentController::faqView`

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

Không có template riêng. FAQ list được render inline trong:
- `lms-homepage` — global FAQs
- `lms-course-detail` — course-specific FAQs

---

## Related Units

- **C03** § Communication Domain — entity FAQ
- **C05** `04-certificate-communication.md` — layer overview
- `lms_course.md` — workspace FAQ management routes + homepage/detail FAQs

---

## Known Issues / Debt

- FAQ routing phụ thuộc vào `lms_course` routing — vi phạm separation of concerns.
- Không có search/filter FAQ trên frontend.
- Translatable nhưng không có clear workflow cho multi-language.
