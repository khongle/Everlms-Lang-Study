# F-005 Acceptance Criteria - Progress tracking & resume

## Core criteria
1. **Given** một learner đang hoàn thành các lesson, **when** mỗi lesson kết thúc, **then** percent-complete và last-accessed của `lms_user_course` được cập nhật tương ứng.
2. **Given** một course hoàn thành một phần, **when** learner quay lại, **then** "Resume" mở lesson được truy cập gần nhất.
3. **Given** tất cả lesson bắt buộc đã hoàn thành và final quiz đã pass, **when** rollup tính toán lại, **then** course được đánh dấu hoàn thành (kích hoạt F-007).
4. **Given** dữ liệu progress, **when** được truy vấn từ dashboard hoặc mobile API, **then** cùng một percent được trả về trên cả hai surface.

## Evidence expected
- Module: [lms_progress](../../C-Design/C05-modules/lms_progress.md).
- Test: progress-rollup kernel test; resume-link functional test; kiểm tra parity web/mobile (E03).
