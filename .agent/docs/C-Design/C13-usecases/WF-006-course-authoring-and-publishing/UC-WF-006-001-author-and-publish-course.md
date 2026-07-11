---
usecase_id: UC-WF-006-001
workflow: WF-006-course-authoring-and-publishing
status: as-built
primary_actor: Instructor
modules: [lms_course, lms_chapter, lms_lesson, lms_ai]
processes: [course-authoring-and-publishing]
---

# UC-WF-006-001 - Author and publish a course

## Intent
Một instructor dựng và publish một course thật với curriculum được lưu lại.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-006 |
| Business flow | create → curriculum → publish |
| Modules | lms_course, lms_chapter, lms_lesson, lms_ai |
| Related features | F-002, F-018 |
| B04 source | WF-006 |

## Actors
Instructor/content_editor (primary); manager (duyệt publish, tùy chọn).

## Preconditions
- User có role `instructor` hoặc `content_editor`.
- Role đó có thể tạo course, sửa course của chính mình, xem trang workspace course, và
  dùng administration theme.
- Việc tạo course trong workspace render entity form `lms_course` bên trong
  workspace shell.

## Trigger
Instructor bắt đầu một course mới từ `/workspace/courses/create`.

## Main success scenario
1. Instructor đăng nhập qua `/user/login`.
2. Instructor mở `/workspace/courses` và thấy danh sách chỉ gồm course của mình.
3. Instructor mở `/workspace/courses/create`.
4. Instructor điền metadata course, bao gồm title, description, level,
   field category/collection, và draft status.
5. Instructor thêm một chapter qua `field_chapters` của course.
6. Instructor thêm một lesson qua Inline Entity Form lồng nhau ở
   `field_lessons` của chapter.
7. Instructor lưu course draft và quay về `/workspace/courses`.
8. Instructor mở lại `/workspace/courses/{id}/edit`.
9. Instructor kiểm tra published status và lưu.
10. Learner anonymous tìm kiếm `/courses?search=...`, mở `/courses/{id}`, và
    xác minh tab Curriculum hiển thị chapter và lesson.

## Alternate and exception flows
- Lưu draft; publish sau.
- Bước review (manager) trước khi publish nếu có dùng.
- H5P embed có thể được soạn ở nơi đã cấu hình. Widget upload H5P vẫn
  bị ẩn cho tới khi tích hợp widget Drupal 11 được xác minh.
- Hỗ trợ AI builder thuộc WF-010 và có thể sinh cấu trúc draft trước khi
  instructor review và lưu.

## State transitions (evidence)
- `draft` → `published`: instructor tick Published và lưu.
- `published` → `archived`: manager unpublish/retire course.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Login | Account | /user/login |
| Manage courses | Manage | /workspace/courses |
| Create course | Manage | /workspace/courses/create |
| Course editor | Manage | /workspace/courses/{id}/edit |
| Course catalog | Public | /courses |
| Course detail | Public | /courses/{id} |

## Supporting pages
Instructor quản lý danh sách course tại Manage courses (P032) và dựng curriculum tại Course editor (P033); quiz cuối course được gắn tại Manage quizzes (P034). Content editor quản lý FAQ/nội dung phụ trợ tại Manage content (P037) và bài blog liên quan tại Manage blog (P043), theo cùng mô hình publish của B04 WF-006.

## Acceptance and test contract
- Session user thật dùng login form, không dùng one-time login URL.
- Course, chapter, và lesson được tạo qua UI và lưu lại thành các entity
  liên kết với nhau.
- Course draft không được coi là public cho tới khi publish.
- Course đã publish xuất hiện trong catalog và trang detail công khai.
- Tab Curriculum trên trang detail công khai hiển thị chapter và lesson đã lưu.
- Screenshot được lưu trong thư mục evidence round-1 của WF-006, bao gồm
  trạng thái form trước/sau.
