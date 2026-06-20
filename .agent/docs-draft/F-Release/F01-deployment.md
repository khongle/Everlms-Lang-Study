# F01 — Deployment & Release Checklist

## Release Info

| Field | Value |
|-------|-------|
| Version | v1.0.0-beta |
| Release Date | TBD |
| Release Manager | TBD |
| Branch | release/1.0 |
| Environment | production |

---

## Pre-Release Checklist

### 1. Scope & Requirements
- [ ] Scope release được freeze và approved
- [ ] Tất cả Req ID P0 có test evidence
- [ ] Out-of-scope đã được ghi backlog

### 2. Security
- [ ] Role permissions review toàn bộ routes quan trọng
- [ ] API write endpoints có input validation
- [ ] Payment webhook có verify signature + idempotency
- [ ] Không còn security blocker mức cao
- [ ] CSRF audit hoàn tất (TASK-024)

### 3. Data
- [ ] Entity schema audit — không mismatch nghiêm trọng
- [ ] Backup/restore test thành công
- [ ] Seed/demo data đã loại bỏ khỏi production candidate
- [ ] Database migration scripts verified

### 4. Functional Core
- [ ] Enrollment → learning → quiz → certificate flows pass
- [ ] Checkout → membership → access-control flows pass
- [ ] Notification → forum → rating flows pass
- [ ] Reports quan trọng pass sanity checks

### 5. Performance & Reliability
- [ ] Baseline performance đạt mục tiêu (NFR-001 → NFR-004)
- [ ] Flow nhạy cảm concurrency đã test (progress, checkout)
- [ ] Scheduled tasks hoạt động ổn định

### 6. Operations
- [ ] Log/monitoring cho flow critical đã bật
- [ ] Incident playbook + rollback plan sẵn sàng
- [ ] Release notes + migration notes đầy đủ

### 7. Documentation
- [ ] .agent/docs/ layers cập nhật đồng bộ
- [ ] Traceability matrix requirement → test đầy đủ
- [ ] UAT signoff được lưu trữ

### 8. Known Issues Gate
- [ ] Danh sách issues đã được triage
- [ ] Không defer Sev1/Sev2 khi chưa có phê duyệt

---

## Go / No-Go Decision

| Criteria | Status | Notes |
|----------|--------|-------|
| All P0 requirements verified | ⬜ | |
| Security checklist pass | ⬜ | |
| Performance baseline met | ⬜ | |
| No Sev1/Sev2 open issues | ⬜ | |
| Rollback plan tested | ⬜ | |
| **Decision** | ⬜ GO / NO-GO | |

**GO** khi tất cả mandatory checklist pass.
**NO-GO** nếu có blocker security / payment / access-control.

---

## Deployment Steps

```
1. Tag release branch:
   git tag -a v1.0.0-beta -m "Release v1.0.0-beta"

2. Export configuration:
   drush cex -y

3. Database backup:
   drush sql:dump --result-file=../database/pre-release-backup.sql

4. Deploy to staging:
   ddev deploy staging

5. Run smoke tests on staging:
   drush test:run --group=smoke

6. If pass → deploy to production

7. Post-deploy verification:
   - Check homepage loads <2s
   - Verify enrollment flow
   - Verify checkout flow
   - Check cron runs
```

## Rollback Plan

```
1. Restore database:
   drush sql:cli < ../database/pre-release-backup.sql

2. Revert code:
   git checkout <previous-tag>

3. Import previous config:
   drush cim -y

4. Clear caches:
   drush cr
```

---

*End of F01-deployment.md*
