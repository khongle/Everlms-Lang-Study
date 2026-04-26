# Task: Cohort-Based Course Mode with Fixed Start Dates

**ID:** TASK-168
**Unit:** new (raise)
**Group:** G-LIVE-CLASS
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Mở rộng `lms_course` để hỗ trợ "cohort mode" — mô hình học theo lớp với ngày bắt đầu cố định, tất cả học viên bắt đầu cùng thời điểm và học theo lịch drip đồng bộ. Khác với self-paced (học bất kỳ lúc nào), cohort mode phổ biến với các khóa học cần tương tác nhóm, live class, hoặc project team. Một course có thể có nhiều cohorts chạy song song (e.g., "Lớp Tháng 5", "Lớp Tháng 6").

Cohort entity quản lý: ngày bắt đầu, số lượng học viên tối đa, danh sách enrollment, trạng thái (open/running/completed). Enrollment vào cohort course bắt buộc chọn cohort — không enroll "floating". Drip schedule (lesson unlock) tính từ `cohort.start_date`, không phải `enrollment.created`.

## Acceptance Criteria

- [ ] Config field `field_course_mode` trên `lms_course`: enum `self_paced` (default) | `cohort`
- [ ] Content entity `lms_cohort`: fields `field_course`, `field_name` (string), `field_start_date` (date), `field_end_date` (date, nullable), `field_max_students` (integer), `field_status` (open/running/completed/archived)
- [ ] Admin UI: `/admin/lms/cohorts` — list + CRUD; per-course: `/course/{id}/cohorts`
- [ ] Checkout flow: nếu course mode = cohort → step "Chọn lớp học" hiển thị danh sách cohorts còn open + slot còn trống
- [ ] Enrollment entity: thêm `field_cohort` (nullable entity_ref → lms_cohort)
- [ ] Drip unlock logic: nếu enrollment có cohort → dùng `cohort.start_date` làm T0, không phải enrollment date
- [ ] `CohortService::getAvailableCohorts(int $courseId): array` — chỉ trả cohorts status=open + slots còn
- [ ] Khi `field_max_students` đạt giới hạn → cohort tự chuyển status → `full` (closed for new enrollment)
- [ ] Student "My Learning": hiển thị tên cohort + "Khai giảng: {start_date}"
- [ ] Unit test: drip T0 = cohort.start_date; Kernel test: enroll → cohort slot decremented

## Technical Notes

- Module: `web/modules/custom/lms_course/` (cohort entity), `lms_zoom` (link to class_schedule)
- Entity: `web/modules/custom/lms_course/src/Entity/LmsCohort.php`
- Drip hook: override `LessonAccessService::isUnlocked()` — check enrollment.cohort.start_date
- Slot counter: `lms_cohort.enrolled_count` — cached counter, recalculate on enrollment create/delete
- Checkout step: `CohortSelectionStep` — insert giữa course select và payment
- Link to class_schedule: `field_cohort` trên `class_schedule` entity (TASK-161)
- Coordinate: TASK-161 (class schedules per cohort), TASK-166 (reminders)

## Dependencies

- TASK-161 (class_schedule entity)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
