# RUN-003 — Database Backup & Restore

| Field | Value |
|-------|-------|
| **ID** | RUN-003 |
| **Owner** | SA |
| **Last Updated** | 2026-04-16 |
| **Last Tested** | UNTESTED |
| **Last Executed** | — |
| **Severity scope** | SEV1 (restore) / Routine (backup) |

---

## Khi nào dùng

- **Backup:** Trước mỗi deploy lớn, trước khi chạy update hooks, định kỳ hàng ngày
- **Restore:** Sau data corruption, sau incident SEV1/SEV2, rollback failed migration
- **Snapshot:** DDEV nhanh hơn cho local dev cycle

---

## Prerequisites

- [ ] DDEV đang chạy: `ddev status` → active
- [ ] Đủ disk space: `df -h` (backup dump ~= kích thước DB)
- [ ] Biết rõ snapshot/backup nào muốn restore

---

## Part A — DDEV Snapshot (Nhanh nhất cho local)

### Tạo snapshot

```bash
# Tạo named snapshot
ddev snapshot --name pre-deploy-$(date +%Y%m%d-%H%M)

# Hoặc unnamed (auto-named)
ddev snapshot
```

### List snapshots

```bash
ddev snapshot --list
```

### Restore từ snapshot

```bash
# Restore snapshot gần nhất
ddev snapshot restore --latest

# Restore snapshot cụ thể
ddev snapshot restore pre-deploy-20260416-1430
```

---

## Part B — MariaDB Dump (Export portable)

### Tạo backup dump

```bash
# Dump toàn bộ DB (khuyến nghị)
ddev export-db --file=backups/everlms-$(date +%Y%m%d-%H%M).sql.gz

# Hoặc dùng mariadb-dump trực tiếp trong container
ddev exec mariadb-dump -u db -pdb db | gzip > backups/everlms-$(date +%Y%m%d-%H%M).sql.gz
```

### Verify dump

```bash
# Kiểm tra file không rỗng
ls -lh backups/everlms-*.sql.gz | tail -5

# Kiểm tra nội dung (không cần giải nén toàn bộ)
zcat backups/everlms-latest.sql.gz | head -20
```

---

## Part C — Restore từ Dump

### Restore vào DDEV

```bash
# Import dump file
ddev import-db --file=backups/everlms-20260416-1430.sql.gz

# Sau khi import — chạy update hooks nếu cần
ddev drush updb --yes
ddev drush cr
```

### Restore thủ công (nếu ddev import-db fail)

```bash
# Vào container DB
ddev exec -s db bash

# Trong container
zcat /path/to/dump.sql.gz | mariadb -u db -pdb db
exit
```

---

## Part D — Backup Trước Deploy (Checklist)

```
PRE-DEPLOY CHECKLIST:
[ ] ddev snapshot --name pre-deploy-{version}-$(date +%Y%m%d)
[ ] ddev export-db --file=backups/pre-deploy-{version}.sql.gz
[ ] git stash nếu có uncommitted changes
[ ] Ghi note snapshot ID vào F02 release checklist
```

---

## Verification Restore

- [ ] `ddev drush status` → Drupal bootstrap: Successful
- [ ] `ddev drush sql:query "SELECT COUNT(*) FROM users_field_data;"` → số users hợp lý
- [ ] Kiểm tra 1-2 content node/entity quan trọng còn dữ liệu đúng
- [ ] Không có lỗi trong `ddev drush watchdog:show --count=20`

---

## Rollback (nếu restore cũng fail)

```bash
# Rollback về snapshot trước đó
ddev snapshot --list
ddev snapshot restore {snapshot-tên-cũ-hơn}
```

---

## PROPOSED — Production Backup Schedule

> Khi deploy lên production server (chưa triển khai):
>
> | Loại | Tần suất | Retention | Tool |
> |------|----------|-----------|------|
> | Full dump | Hàng ngày 02:00 | 7 ngày | mysqldump + cron |
> | Incremental | Mỗi 6 giờ | 48 giờ | binlog |
> | Pre-deploy | Trước mỗi deploy | 30 ngày | Script |

---

## Tham chiếu

- H01: Log incident nếu restore được trigger bởi SEV1
- F02: Ghi snapshot ID vào release checklist trước deploy
- A07: DR tiers và RPO/RTO targets
