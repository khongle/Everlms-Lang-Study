# F-003 Acceptance Criteria - Lesson delivery & media

## Core criteria
1. **Given** một learner đã enroll, **when** họ mở một lesson, **then** body text, video, và attachment (`field_attachments`) được render trong player.
2. **Given** một lesson, **when** learner đánh dấu hoàn thành, **then** một bản ghi `lms_user_lesson` được tạo/cập nhật và lesson tiếp theo có thể truy cập được.
3. **Given** một user chưa enroll, **when** họ cố mở nội dung lesson, **then** quyền truy cập bị từ chối / một CTA mời enroll được hiển thị (gating qua F-004).
4. **Given** một lesson có attachment, **when** learner tải file xuống, **then** file được phục vụ (referenced file, không inline).

## Evidence expected
- Module: [lms_lesson](../../C-Design/C05-modules/lms_lesson.md); player page trong C11.
- Test: lesson-access test (đã enroll vs chưa); complete-lesson progress test (E03).
