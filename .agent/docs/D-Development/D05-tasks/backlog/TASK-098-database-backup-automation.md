# Task: Automated Database Backup (RPO ≤ 1h)

**ID:** TASK-098
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P0
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thiết lập backup tự động cho MariaDB 10.11: incremental backup mỗi 1h (dùng `mariabackup` hoặc `mysqldump` với binary log), daily full snapshot. Backup được upload off-site tới S3-compatible storage (DigitalOcean Spaces, AWS S3, hoặc Backblaze B2). Tất cả backup được mã hóa at-rest (GPG hoặc AWS SSE). Retention 30 ngày. Đạt RPO ≤ 1h theo `A07-disaster-recovery.md`.

Script backup phải alert qua monitoring (TASK-100) nếu backup fail. Verify backup integrity bằng checksum sau mỗi upload.

## Acceptance Criteria
- [ ] Hourly incremental backup chạy qua cron (mỗi giờ, offset 5 phút để tránh peak)
- [ ] Daily full backup lúc 02:00 (UTC+7 = 19:00 UTC)
- [ ] Backup được upload tới S3-compatible bucket trong vòng 5 phút sau khi hoàn thành
- [ ] Encryption at-rest: GPG symmetric key hoặc SSE-S3 — key stored trong secrets manager
- [ ] Checksum (SHA-256) verified sau mỗi upload; mismatch → alert
- [ ] Retention policy: tự động xóa backup > 30 ngày (lifecycle rule trên S3 hoặc cron script)
- [ ] Backup script ghi log vào `/var/log/db-backup.log` với timestamp + size + status
- [ ] Monitoring integration: push metrics tới Prometheus pushgateway (TASK-100) — `db_backup_last_success_timestamp`
- [ ] Alert: Prometheus alert khi `db_backup_last_success_timestamp` > 2h (xem H07-observability-as-code)
- [ ] Test restore từ backup gần nhất trong môi trường staging (smoke test — row count ≥ 100 rows trên bảng `node`)

## Technical Notes
- Tool khuyến nghị: `mariabackup` (faster, supports hot backup) thay vì `mysqldump` cho production
- `mysqldump` dùng cho daily full nếu DB size < 10 GB (đơn giản hơn cho restore drill)
- Script path: `/opt/backup/db-backup.sh`
- S3 bucket naming: `everlms-db-backups-{region}` với versioning off (lifecycle handles retention)
- IAM user cho backup: write-only policy (PutObject + DeleteObject) — không có GetObject
- Restore credentials riêng: read-only IAM user, lưu offline trong password manager
- Tham chiếu: `A07-disaster-recovery.md` cho RPO/RTO tiers; `H02-runbooks/` cho runbook format

## Dependencies
- TASK-095 (prod DB server phải exist)
- TASK-100 (Prometheus/Grafana để wire alerts)

## Definition of Done
- [ ] Code complete (backup scripts committed)
- [ ] G1-G4 gates passed
- [ ] Runbook tại `H02-runbooks/RUN-008-database-backup.md` viết xong
- [ ] Tests written + passing (backup + upload + checksum verify)
- [ ] Restore smoke test documented với kết quả
- [ ] No new debt introduced
