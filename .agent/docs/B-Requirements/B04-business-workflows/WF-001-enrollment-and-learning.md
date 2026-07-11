# WF-001 - Enrollment & learning

## Business flow
Người dùng khám phá một course trong catalog (F-001), có được quyền truy cập (enroll miễn phí, đặt order trả phí WF-004, hoặc có active membership WF-005), sau đó học qua các chapter/lesson. Mỗi lần xem lesson sẽ ghi lại tiến độ (`lms_user_lesson`); rollup của course (`lms_user_course`) theo dõi phần trăm hoàn thành và lesson truy cập gần nhất để learner có thể học tiếp. Hoàn thành tất cả lesson bắt buộc và pass quiz cuối (WF-002) đánh dấu course hoàn thành, từ đó kích hoạt việc cấp certificate (WF-003). Learner có thể đánh giá course (F-014) và ghi chú (F-015).

## Participating modules
[lms_course](../../C-Design/C05-modules/lms_course.md), [lms_chapter](../../C-Design/C05-modules/lms_chapter.md), [lms_lesson](../../C-Design/C05-modules/lms_lesson.md), [lms_progress](../../C-Design/C05-modules/lms_progress.md), [lms_order](../../C-Design/C05-modules/lms_order.md), [lms_rating](../../C-Design/C05-modules/lms_rating.md)

## Related features (B03)
[F-001](../B03-features/F-001-course-catalog-and-discovery.md), [F-003](../B03-features/F-003-lesson-delivery-and-media.md), [F-004](../B03-features/F-004-enrollment-and-content-gating.md), [F-005](../B03-features/F-005-progress-tracking-and-resume.md), [F-014](../B03-features/F-014-ratings-and-reviews.md)

## Trigger
User nhấn "Enroll"/"Start learning" trên một course mà họ có quyền truy cập.

## Outcome
Bản ghi enrollment `lms_user_course` được tạo; tiến độ lesson được tích lũy; khi hoàn thành, course được đánh dấu done và điều kiện nhận certificate được thiết lập.

## Design link
[C12 WF-001](../../C-Design/C12-workflows/WF-001-enrollment-and-learning.md)
