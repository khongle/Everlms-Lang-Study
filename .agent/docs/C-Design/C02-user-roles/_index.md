# C02-user-roles - User Roles and Access Baseline

**Last updated:** 2026-06-21
**source_state:** AS_BUILT (from `config/sync/user.role.*.yml`)

## Source
Bảy role được định nghĩa trong config (`user.role.*.yml`). `administrator` được đánh dấu `is_admin: true` (ngầm định có toàn bộ permission, hiển thị 0 permission tường minh). `anonymous`/`authenticated` là role hệ thống của Drupal. Số permission tường minh: manager 13, content_editor 27, instructor 10, student 4.

## Role access summary
| RID | Role | Group | Reachable pages | Denied/skipped | Assignable |
| --- | ---- | ----- | --------------- | -------------- | ---------- |
| administrator | Administrator | Platform admin | all (~45) | 0 | Yes |
| manager | Manager | Operations | ~38 | ~7 | Yes |
| content_editor | Content Editor | Authoring | ~30 | ~15 | Yes |
| instructor | Instructor | Authoring | ~28 | ~17 | Yes |
| student | Student | Learners | ~22 | ~23 | Yes |
| authenticated | Authenticated user | Learners | ~18 | ~27 | No (system) |
| anonymous | Anonymous user | Public | ~10 | ~35 | No (system) |

> Số liệu Reachable/denied là INFERENCE (suy luận) dựa trên sitemap ~45 trang (C07); sẽ tinh chỉnh khi access của C11 được chốt.

## Role groups
| Group | Roles |
| ----- | ----- |
| Platform admin | administrator |
| Operations | manager |
| Authoring | instructor, content_editor |
| Learners | student, authenticated |
| Public | anonymous |

## Route-group access matrix
Cells: `full | scoped | none`.
| Role group | Public | App | Manage | API | Admin |
| ---------- | ------ | --- | ------ | --- | ----- |
| Platform admin | full | full | full | full | full |
| Operations | full | scoped | full | scoped | none |
| Authoring | full | scoped | scoped | scoped | none |
| Learners | full | scoped | none | scoped | none |
| Public | scoped | none | none | scoped (catalog/auth) | none |

> Cột `API` không có trang C11 tương ứng (route group JSON-only, xem residual as-built ở [C01](../C01-architecture-decision/_index.md#route-groups)) — access ở đây mô tả scope bearer-token của `lms_mobile_api`, không phải quyền xem trang.

## Editorial management matrix
Neo (anchor) của bất biến entity-first: mọi surface hiển thị cho business đều phải chỉ rõ một editor role không phải developer + management UI đi kèm.
| Entity / content surface | Owner module | Editor role(s) | Management UI | Permission |
| ------------------------ | ------------ | -------------- | ------------- | ---------- |
| Course / chapter / lesson | [lms_course](../C05-modules/lms_course.md), lms_chapter, lms_lesson | instructor, content_editor | Course/lesson entity forms | manage own/all courses |
| Quiz & questions | [lms_quiz](../C05-modules/lms_quiz.md), lms_question | instructor | Quiz/question entity forms | manage quizzes |
| Certificate template | [lms_certificate](../C05-modules/lms_certificate.md) | content_editor, manager | Certificate template form | manage certificate templates |
| Notification templates | [lms_notification](../C05-modules/lms_notification.md) | manager | Notification settings/templates | administer notifications |
| Blog & FAQ | [lms_blog](../C05-modules/lms_blog.md), lms_faq | content_editor | Article/FAQ entity forms | manage blog/faq |
| Homepage skins & branding | [lms_theme](../C05-modules/lms_theme.md) | administrator | Theme settings (`theme-settings.php`) | administer themes |
| Membership plans | [lms_membership](../C05-modules/lms_membership.md) | manager | Plan entity form | manage memberships |
| Taxonomy (categories/levels/tags) | core taxonomy | content_editor | Taxonomy term UI | administer taxonomy |

## Access design notes
- `administrator` là `is_admin` (super-role) — chỉ cấp khi thật cần thiết; nhân sự vận hành hằng ngày dùng `manager`.
- `content_editor` (23 perms) là role non-admin rộng nhất — quản lý content trên nhiều module nhưng không đụng đến system config.
- `instructor` (5 perms) được thiết kế hẹp có chủ đích — chỉ author/quản lý nội dung giảng dạy của chính mình.
- Mobile API access (F-019) được scope theo bearer-token; anonymous chỉ truy cập được endpoint catalog + auth.
- Impersonation (`open_switcher`) được chặn bởi một permission switch riêng — chỉ dành cho support/admin.
- File này không chứa credential nào. Thiết kế tích hợp auth: [C06 mobile API](../C06-apis/_index.md).
