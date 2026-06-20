# Task: Quiz Results Export (CSV / Excel)

**ID:** TASK-174
**Unit:** U-032 (lms_quiz_result)
**Group:** G-ASSESSMENT
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Cho phép instructor và admin xuất kết quả bài kiểm tra ra file CSV hoặc Excel (.xlsx) từ admin quiz result view. Dữ liệu export bao gồm từng lần làm bài (attempt): thông tin học viên, điểm số, thời gian làm, số câu đúng/sai, và tỷ lệ đúng từng câu hỏi (per-question correctness). Export phục vụ báo cáo kết quả học tập gửi ban giám đốc, phụ huynh, hoặc cơ quan quản lý.

Export phải handle scale lớn (quiz có thể có 500+ students) — dùng streaming hoặc background job để tránh timeout. Format CSV đảm bảo tiếng Việt render đúng trong Excel (BOM UTF-8).

## Acceptance Criteria

- [ ] Admin quiz result page: thêm button "Export CSV" và "Export Excel (.xlsx)"
- [ ] CSV columns: `attempt_id`, `student_name`, `student_email`, `student_phone`, `quiz_name`, `attempt_date`, `score`, `max_score`, `percentage`, `time_taken_seconds`, `status` (passed/failed)
- [ ] Per-question columns (dynamic): `Q1_correct`, `Q1_answer`, `Q2_correct`, ... (max 100 questions)
- [ ] CSV: UTF-8 BOM (`\xEF\xBB\xBF`) để Excel nhận diện tiếng Việt
- [ ] Excel (.xlsx): dùng `phpoffice/phpspreadsheet` — header row bold, freeze top row, column auto-width
- [ ] Filter: export theo date range, quiz, course; tôn trọng filter đang active trên Views page
- [ ] Large export (>500 rows): background job → email link download khi xong (reuse `lms_notification` email)
- [ ] Small export (<= 500 rows): direct download (streaming response)
- [ ] Access control: instructor chỉ export quiz thuộc course của mình; admin export all
- [ ] Unit test: CSV output with Vietnamese name "Nguyễn Văn An" → UTF-8 BOM + correct encoding

## Technical Notes

- Module: `web/modules/custom/lms_quiz_result/`
- Controller: `src/Controller/QuizResultExportController.php`
- Route: `/admin/lms/quiz/{quiz}/results/export?format=csv|xlsx&date_from=&date_to=`
- Streaming CSV: `StreamedResponse` với `Content-Disposition: attachment`
- Excel: `composer require phpoffice/phpspreadsheet`
- Background job: Drupal Queue — `lms_quiz_export` queue, worker emails download link
- Per-question data source: `lms_quiz_attempt_answer` table JOIN `lms_question`
- Coordinate: TASK-175 (analytics — shares data source), TASK-123 (refund export pattern reference)

## Dependencies

- G-ASSESSMENT (quiz_result entity)
- TASK-076 (kernel test pattern)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
