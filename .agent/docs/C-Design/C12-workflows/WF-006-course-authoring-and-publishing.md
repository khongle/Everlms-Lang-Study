# C12 Workflow - WF-006 Course authoring & publishing

**Object:** course

## Business source
[B04 WF-006](../../B-Requirements/B04-business-workflows/WF-006-course-authoring-and-publishing.md)

## Modules
[lms_course](../C05-modules/lms_course.md), [lms_chapter](../C05-modules/lms_chapter.md), [lms_lesson](../C05-modules/lms_lesson.md), [lms_ai](../C05-modules/lms_ai.md)

## Related features (B03)
[F-002](../../B-Requirements/B03-features/F-002-course-authoring-and-structure.md), [F-018](../../B-Requirements/B03-features/F-018-ai-authoring-assistant.md)

## State model
`lms_course.status` là nguồn sự thật duy nhất (source of truth) cho việc hiển thị trong catalog. Chapter và
lesson dùng giá trị `status` riêng của entity mình và phải được lưu cùng
hệ thống phân cấp curriculum của course.

| State | Meaning |
| ----- | ------- |
| draft (`status = 0`) | Course is being authored and is not visible in the public catalog. |
| published (`status = 1`) | Course is visible in `/courses` and `/courses/{id}`. |
| review/approval | Optional project policy layer; no core Workflow/Content Moderation state is currently used. |
| archived/retired | Course is unpublished or removed from discovery by manager/admin action. |

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| draft | published | instructor tick Published và lưu |
| published | archived | manager unpublish/retire course |

## Permission model
- Initiator: `instructor` hoặc `content_editor`.
- Quyền của instructor/content editor phải theo least privilege:
  `create lms_course`, `edit own lms_course`, `view lms_course`, `view the administration theme`,
  và quyền H5P editor ở nơi H5P authoring được bật.
- Manager/admin có thể quản lý toàn bộ course qua các quyền quản trị course
  rộng hơn.
- `/workspace/courses` có phạm vi theo role: manager/admin thấy toàn bộ course; author
  chỉ thấy và sửa course của chính mình.
- `/workspace/courses/create` phải dùng entity create access, không dùng yêu cầu
  `administer lms_course` áp dụng chung cho tất cả.

## Concrete flow
1. Author đăng nhập qua form `/user/login` thật.
2. Author mở `/workspace/courses`.
3. Author bắt đầu `/workspace/courses/create`.
4. Workspace shell render entity form `lms_course` thật, không phải
   prototype tĩnh. Với user instructor, form tự điền sẵn các field owner/instructor
   bằng tài khoản hiện tại.
5. Author điền metadata course và lưu ở dạng draft bằng cách để `status` chưa được tick.
6. Author dựng curriculum qua Inline Entity Form lồng nhau:
   course `field_chapters` -> chapter `field_lessons`.
7. Việc soạn lesson dùng field tùy theo loại (type-aware). Lesson dạng text ẩn các
   widget chỉ dành cho media không tương thích. Widget upload H5P không tương thích bị ẩn cho tới khi
   đường widget Drupal 11/contrib được xác minh; H5P embed vẫn khả dụng.
8. Author lưu course draft và quay về `/workspace/courses`.
9. Author mở lại `/workspace/courses/{id}/edit`, review nội dung, kiểm tra
   trạng thái published, và lưu.
10. Learner anonymous xác minh course tại `/courses?search=...` và mở trang
    course detail. Tab Curriculum phải hiển thị chapter và lesson đã lưu.

## Authoring UI requirements
- Form trong workspace phải dùng Drupal Form API và entity form để việc validation,
  access check, cacheability, translation, và submit handler vẫn theo chuẩn.
- Dialog inline chapter/lesson phải hiển thị đầy đủ, nổi lên trên workspace
  sidebar cố định trên cả desktop và mobile.
- Các field lesson theo type phải phản ứng đúng cả ở form lesson độc lập lẫn trong
  tên field của Inline Entity Form lồng nhau.
- Trang danh sách phải giữ các action dễ thao tác và hiển thị đủ ngữ cảnh mỗi dòng để
  author phân biệt được course draft và published.

## Acceptance and evidence contract
- Screenshot được lưu tại
  `.agent/docs/E-Quality/E00-workflows/round-1/_screens/006-course-authoring-and-publishing/`.
- Các bước form ghi lại trạng thái trước và sau khi điền.
- Dữ liệu test phải là dữ liệu Drupal thật được tạo qua UI với session user thật.
- Việc verify phải chứng minh được `lms_course -> lms_chapter -> lms_lesson` được lưu (persistence)
  và catalog công khai hiển thị đúng sau khi publish.

## Round-2 QA verification
- `/workspace/courses/create` render entity form `lms_course` thật (các tab: Info / Course Collection / Chapter & lesson / Quiz; body dùng CKEditor; upload thumbnail/banner), không phải prototype tĩnh (fix round-1 `WF-006-001` vẫn giữ nguyên hiệu lực).
- **Đã fix draft-first (`WF-006-002`):** `status` của `Course` giờ mặc định là `FALSE`. Một course do instructor tạo được lưu với `status = 0` (owner = author), bị ẩn khỏi `/courses`, và chỉ xuất hiện sau khi author tick **Published** (tab Course Collection). Đã verify với course 140 (`sabine.mueller`).
- **Đã fix nhãn role (`WF-006-001`):** sidebar workspace hiển thị đúng role thật (Instructor / Manager / Administrator) qua `current_user_role`, không còn hard-code "Manager".

## Notifications
Notification tùy chọn cho editor khi submit/publish. Nội dung được sửa qua entity form (editorial-usability).
