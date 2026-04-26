# Task: Manage Grading Queue — Pending Essay Grading

**ID:** TASK-065
**Unit:** — (extends U-020: Grading service auto + essay route)
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
U-020 đã có grading service với route essay manual grading nhưng chưa có UI tập trung. Task này build trang `/manage/grading` (per C07 sitemap, Instructor+) listing tất cả essay responses đang chờ grading thủ công, với inline grading interface: xem câu hỏi, đọc bài làm student, nhập điểm + feedback, save và chuyển sang case tiếp theo.

Mục tiêu: instructor không phải navigate qua nhiều màn hình để grade; flow "save & next" cho phép xử lý batch nhanh.

## Acceptance Criteria
- [ ] `/manage/grading` — danh sách pending essay responses: student name, course, quiz title, submitted_at, age (giờ từ lúc submit)
- [ ] Filter theo: course (dropdown), instructor (dropdown, Manager+ only), submitted age (oldest first default)
- [ ] Click vào row → inline grading panel (không rời trang hoặc modal/drawer)
- [ ] Grading panel hiển thị: câu hỏi prompt, rubric (nếu có trên Question entity), student answer (formatted)
- [ ] Score input (0–100, với max score từ Question entity), feedback textarea (rich text optional)
- [ ] Save → cập nhật `QuestionResponse` entity: score, feedback, graded_by, graded_at
- [ ] "Save & Next" → lưu + chuyển sang pending item tiếp theo tự động
- [ ] Sau khi grade: notification gửi cho student (via `lms_notification`)
- [ ] Khi tất cả questions trong QuizResult đã grade: trigger tính lại QuizResult total_score + update status
- [ ] Danh sách refresh sau mỗi save (loại bỏ item vừa grade)
- [ ] Access: Instructor+ (chỉ thấy courses của mình); Manager+ thấy tất cả

## Technical Notes
- Module: `web/modules/custom/lms_quiz/` (U-020: `GradingService.php`)
- Entity: `QuestionResponse` entity — cần thêm fields: `graded_by`, `graded_at`, `feedback` nếu chưa có
- Controller: `GradingQueueController.php` với routes `lms_quiz.routing.yml`
- AJAX grading panel: Drupal AJAX API (`#ajax` form element) hoặc custom JS + REST endpoint
- QuizResult re-score: method trong `GradingService::recalculateResult(QuizResult $result)`
- Notification: `lms_notification` service inject vào `GradingService`
- Template: `web/themes/custom/lms_theme/templates/quiz/grading-queue.html.twig`
- SCSS: `_grading-queue.scss`

## Dependencies
- U-020 (Grading service — DONE)
- TASK-009 (U-042 Notification dispatch — cần cho student notification sau grade)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (grading flow + QuizResult recalculation)
- [ ] "Save & Next" flow tested với 5+ pending items
- [ ] Access control: instructor chỉ thấy courses của mình (PHPUnit access test)
- [ ] Student notification verified sau grade
