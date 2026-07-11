# C05 Module - lms_course

**Name:** Courses & catalog · **Phase:** 2 · **Epic:** [E02](../../D-Development/D02-epics/E02-course-catalog.md) · **Status:** done

## Responsibility
Sở hữu entity course và catalog công khai: browsing, filter theo taxonomy, course detail/landing, và metadata course authoring. Là gốc catalog mà phần còn lại của domain phụ thuộc vào. File routing custom lớn nhất (~37 route).

## Owned / related entities
- [lms_course](../C04-entities/lms_course.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-001](../../B-Requirements/B03-features/F-001-course-catalog-and-discovery.md) | Course catalog & discovery |
| [F-002](../../B-Requirements/B03-features/F-002-course-authoring-and-structure.md) | Course authoring & structure |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-001](../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md) | Enrollment & learning |
| [WF-006](../../B-Requirements/B04-business-workflows/WF-006-course-authoring-and-publishing.md) | Course authoring & publishing |

## Integrations / APIs (B06 / C06)
- Dữ liệu catalog cũng được phục vụ qua mobile API ([F-019](../../B-Requirements/B03-features/F-019-mobile-app-api.md)).

## Backing pages
| Label | URL |
| ----- | --- |
| Catalog | `/courses` |
| Course detail | `/course/{course}` |
| Course learn/player | `/course/{course}/learn` |
| Course manage | `/course/{course}/edit` |

## Cross references
- Entity [C04 lms_course](../C04-entities/lms_course.md); taxonomy vocabulary `course_category/level/language/tags`; trang [C11](../C11-pages-components/pages/_index.md).

## Dependencies
- lms_base, taxonomy.

## Implementation notes
- ~37 route (catalog, detail, learn, CRUD). Dùng Views + controller để listing; entity form để authoring. Route group chính: Public (catalog) + App/Manage (learn/author).
