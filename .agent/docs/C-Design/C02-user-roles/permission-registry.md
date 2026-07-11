# C02-user-roles / permission-registry - Closed Permission Registry

**Source:** `config/sync/user.role.*.yml` (as-built, 7 role file, 2026-07-11). Đây là registry ĐÓNG (closed):
mọi permission string được trích dẫn ở [C04-entities](../C04-entities/_index.md), [C06-apis](../C06-apis/_index.md),
[C11-pages-components](../C11-pages-components/_index.md) hoặc [C12-workflows](../C12-workflows/README.md) phải có
một dòng ở đây (Gate 6.C2x).

`administrator` có `is_admin: true` → **toàn bộ permission bên dưới đều ngầm định cấp cho administrator**, kể cả khi
`user.role.administrator.yml` không liệt kê tường minh (`permissions: {}`). Cột **Role** dưới đây liệt kê role nào
được cấp **tường minh** trong config; administrator luôn implicit và không lặp lại ở từng dòng.

## lms_course (entity permission, custom — duy nhất entity có granular permission tường minh trong config)
| Permission machine name | Role | Verb | Object | Notes |
| ------------------------ | ---- | ---- | ------ | ----- |
| `administer lms_course` | manager | administer | lms_course | full admin (bypass own/all split) — manager only, không phải content_editor/instructor |
| `create lms_course` | content_editor, instructor, manager | create | lms_course | 3 role tạo được course mới |
| `edit lms_course` | manager | edit (all) | lms_course | sửa course của người khác, không chỉ của mình |
| `edit own lms_course` | content_editor, instructor, manager | edit own | lms_course | sửa course chính mình tạo — instructor bị giới hạn ở quyền này (không có `edit lms_course`) |
| `delete lms_course` | manager | delete | lms_course | chỉ manager xoá được course |
| `view lms_course` | content_editor, instructor, manager | view | lms_course | xem course (kể cả unpublished/draft theo access callback module) |

> Các entity khác (`lms_quiz`, `lms_order`, `lms_certificate_template`, `lms_membership_plan`, `lms_forum_topic`, `lms_faq`, `lms_article`, ...) **chưa có permission Drupal granular riêng trong config** — access hiện dựa vào role coarse-grained (manager/content_editor/instructor) + ownership check trong code, không phải `entity_access` permission per-bundle như `lms_course`. Đây là gap so với [C02 Editorial management matrix](_index.md#editorial-management-matrix) (cột "Permission" ở đó dùng tên mô tả như "manage quizzes"/"manage certificate templates" — mô tả Ý ĐỊNH thiết kế, KHÔNG map 1:1 tới permission machine name thật trong config; ghi nhận residual, không bịa permission chưa tồn tại).

## Content (core node/article/page + taxonomy, do content_editor sở hữu blog/FAQ/taxonomy)
| Permission machine name | Role | Verb | Object | Notes |
| ------------------------ | ---- | ---- | ------ | ----- |
| `create article content` | content_editor | create | article (blog) | |
| `edit own article content` | content_editor | edit own | article | |
| `delete own article content` | content_editor | delete own | article | |
| `delete article revisions` | content_editor | delete | article revisions | |
| `create page content` | content_editor | create | page (FAQ/static) | |
| `edit own page content` | content_editor | edit own | page | |
| `delete own page content` | content_editor | delete own | page | |
| `delete page revisions` | content_editor | delete | page revisions | |
| `revert all revisions` | content_editor | revert | node revisions | |
| `view all revisions` | content_editor | view | node revisions | |
| `view own unpublished content` | content_editor | view own | unpublished node | |
| `create terms in tags` | content_editor | create | taxonomy term (tags vocab) | |
| `edit terms in tags` | content_editor | edit | taxonomy term (tags vocab) | |
| `administer url aliases` | content_editor | administer | url alias | |
| `create url aliases` | content_editor | create | url alias | |

## Comments (forum reply / rating discussion — core comment module)
| Permission machine name | Role | Verb | Object | Notes |
| ------------------------ | ---- | ---- | ------ | ----- |
| `access comments` | anonymous, authenticated, instructor, student | view | comment | đọc comment/forum reply |
| `post comments` | authenticated, instructor, student | create | comment | đăng reply |
| `edit own comments` | content_editor, instructor, student | edit own | comment | |
| `skip comment approval` | authenticated, instructor, student | administer | comment approval | reply không cần duyệt |
| `administer comments` | instructor | administer | comment | instructor moderate được comment trên bài của mình |

## Files / editor / toolbar (core)
| Permission machine name | Role | Verb | Object | Notes |
| ------------------------ | ---- | ---- | ------ | ----- |
| `delete own files` | authenticated, content_editor | delete own | file | |
| `access files overview` | content_editor | view | `/admin/content/files` | |
| `access h5p editor` | content_editor, instructor, manager | use | H5P editor (interactive lesson content) | |
| `access shortcuts` | authenticated | use | shortcut toolbar | |
| `access contextual links` | content_editor | view | contextual links UI | |
| `access toolbar` | content_editor | view | admin toolbar | |
| `access administration pages` | content_editor | view | `/admin` overview pages | |
| `view the administration theme` | content_editor, instructor, manager | view | Claro admin theme | cho phép vào trang `/admin/*`/`/node/*/edit` core mà không bị redirect |
| `use text format basic_html` | authenticated, manager | use | text format | |
| `use text format full_html` | authenticated, manager | use | text format | |
| `use text format restricted_html` | anonymous, authenticated, manager | use | text format | |

## Platform-level (manager/admin ops)
| Permission machine name | Role | Verb | Object | Notes |
| ------------------------ | ---- | ---- | ------ | ----- |
| `access user profiles` | manager | view | `/user/{uid}` profile | |
| `switch users` | manager | administer | user impersonation (`open_switcher`) | manager có thể switch-user; administrator implicit qua is_admin |
| `search content` | anonymous, authenticated | use | search | |
| `access content` | anonymous, authenticated | view | published content (node access baseline) | |

## Registry closure (Gate 6.C2x harvest — permission thật trích từ C04/C06/C11/C12)
Các permission dưới đây được reference trực tiếp trong C11 page spec (`administer lms_course`, `create lms_course`,
`edit own lms_course`, `view lms_course`, `view the administration theme`) — tất cả đã có dòng ở trên (bảng
`lms_course` + bảng `Files / editor / toolbar`), đóng registry.
