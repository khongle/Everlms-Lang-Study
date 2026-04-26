# Task: Quiz Time-Extended Accommodation (Accessibility)

**ID:** TASK-172
**Unit:** U-030 (lms_quiz), U-032 (lms_quiz_result)
**Group:** G-ASSESSMENT
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Thêm tính năng gia hạn thời gian làm bài (time accommodation) cho học viên có nhu cầu đặc biệt về khả năng tiếp cận (accessibility). Theo tiêu chuẩn giáo dục phổ biến, học viên có thể được cấp 1.5x hoặc 2x thời gian làm bài tiêu chuẩn. Đây là yêu cầu accessibility (a11y) quan trọng. Chính sách cụ thể (ai được cấp, thủ tục xin) do từng tổ chức quyết định và phải được document.

Override là per-user per-quiz (cụ thể) hoặc per-user global (áp dụng cho tất cả quiz). Instructor/admin cấp override, không phải student tự set. Override không ảnh hưởng đến max attempts (TASK-171) hay grading criteria.

## Acceptance Criteria

- [ ] Config entity `QuizTimeAccommodation`: uid, quiz_id (nullable — null = global), multiplier (1.5 hoặc 2.0), granted_by, granted_at, reason, active (boolean)
- [ ] `QuizTimeLimitService::getEffectiveTimeLimit(int $uid, Quiz $quiz): ?int` — trả seconds (null = unlimited)
- [ ] Lookup priority: quiz-specific override → global override → quiz default `field_time_limit_seconds`
- [ ] Admin UI: `/admin/lms/quiz-accommodation` — list + create/edit/revoke accommodations
- [ ] Admin form: select user (autocomplete), select quiz (autocomplete, optional), multiplier (1.5x/2x), reason text
- [ ] Khi student bắt đầu quiz, timer khởi tạo với effective time limit — không phải raw quiz config
- [ ] Student không thấy "accommodation" label — chỉ thấy timer countdown với đúng thời gian
- [ ] Log: mỗi lần accommodation được apply → ghi audit log (uid, quiz, multiplier, attempt_id)
- [ ] Policy document: thêm `accommodation_policy` text field vào `lms_quiz.settings` config — displayed to students on quiz start page
- [ ] Unit test: uid có 2x global override + quiz có 60 min → effective = 120 min
- [ ] Unit test: uid có 1.5x quiz-specific override beats global 2x? No — quiz-specific wins

## Technical Notes

- Module: `web/modules/custom/lms_quiz_result/` (config entity + service)
- Config entity: `src/Entity/QuizTimeAccommodation.php`
- Service: `src/Service/QuizTimeLimitService.php`
- Integration: `QuizAttemptManager::startAttempt()` → get effective time → store on attempt `field_time_limit_seconds`
- Timer frontend: JavaScript reads `field_time_limit_seconds` from attempt response — không query lại backend
- Audit log: Drupal `logger('lms_quiz_result')->notice('Time accommodation applied...')`
- Coordinate: TASK-171 (attempt limits — distinct concern), TASK-176 (cert quiz time is strict)

## Dependencies

- G-ASSESSMENT (quiz infrastructure)
- TASK-076 (kernel test pattern)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
