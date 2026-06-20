# Task: Quarterly Database Restore Drill

**ID:** TASK-099
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thực hiện restore drill hàng quý để xác minh backup từ TASK-098 thực sự có thể restore được. Quy trình: pull một backup ngẫu nhiên (không phải backup mới nhất), restore vào môi trường staging, validate row counts + chạy 5 sample queries kiểm tra tính nhất quán dữ liệu, document RTO thực tế so với target (RTO ≤ 4h per A07). Kết quả được ghi vào H02 runbook entry mới.

Drill này phải được thực hiện ít nhất 1 lần trước khi go-live và sau đó mỗi quý. Task này mô tả lần drill đầu tiên + tạo checklist để các lần sau tự chạy.

## Acceptance Criteria
- [ ] Chọn ngẫu nhiên 1 backup từ 7 ngày trước (không phải backup hôm nay — verify worst-case scenario)
- [ ] Restore thành công vào staging MariaDB 10.11 instance (isolated, không ảnh hưởng prod)
- [ ] Validate row counts: `node`, `users_field_data`, `lms_enrollment`, `lms_progress`, `lms_payment` — counts phải match backup manifest
- [ ] Chạy 5 sample queries (defined dưới đây) và verify kết quả không corrupt:
  - [ ] `SELECT COUNT(*) FROM node WHERE type = 'lms_course'`
  - [ ] `SELECT * FROM lms_enrollment ORDER BY created DESC LIMIT 10`
  - [ ] `SELECT SUM(amount) FROM lms_payment WHERE status = 'completed'`
  - [ ] `SELECT * FROM users_field_data WHERE uid = 1`
  - [ ] `SELECT * FROM lms_progress WHERE completed = 1 ORDER BY changed DESC LIMIT 5`
- [ ] RTO actual (thời gian từ lúc bắt đầu restore đến khi validate xong) được ghi lại
- [ ] RTO actual ≤ 4h (target per A07) — nếu vượt: escalate và tối ưu quy trình
- [ ] Runbook entry tại `H02-runbooks/RUN-009-restore-drill.md` viết xong với kết quả drill lần 1
- [ ] Checklist tái sử dụng cho các lần drill tiếp theo nằm trong runbook
- [ ] Drill kết quả báo cáo cho SA + Dev Lead

## Technical Notes
- Staging DB phải isolated hoàn toàn khỏi prod (không share network segment)
- Dùng credentials read-only IAM user để download backup từ S3 (TASK-098)
- Decrypt backup trước khi restore (GPG passphrase từ secrets manager)
- `mariabackup --prepare` + `mariabackup --copy-back` hoặc `mysql < dump.sql`
- Ghi timestamp ở từng bước: download, decrypt, prepare, restore, validate
- Tham chiếu: `A07-disaster-recovery.md` §RTO tiers; `H02-runbooks/` cho runbook format hiện tại (RUN-001..007)

## Dependencies
- TASK-098 (backup automation phải chạy trước để có backup để drill)
- TASK-095 (staging environment)

## Definition of Done
- [ ] Code complete (drill script + validation queries committed)
- [ ] G1-G4 gates passed
- [ ] Runbook `RUN-009` committed + reviewed
- [ ] RTO documented: actual vs target (A07)
- [ ] Tests written + passing (validation query script tự động)
- [ ] Scheduled cho quý tiếp theo (calendar entry hoặc CI scheduled pipeline)
