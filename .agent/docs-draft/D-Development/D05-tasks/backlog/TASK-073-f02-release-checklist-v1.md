# Task: Release Checklist for v1.0 (F02)

**ID:** TASK-073
**Unit:** — (release: F02)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Sử dụng `F-Release/F02-release-checklist/TEMPLATE-pre-release.md` và `TEMPLATE-post-release.md` để author các checklist files cụ thể cho v1.0.0. Thay thế tất cả placeholder `{VERSION}`, `{threshold}`, `{backup_cmd}`, etc. với giá trị thực tế. Verify runbooks H02-001..007 còn valid và on-call schedule cấu hình.

## Acceptance Criteria
- [ ] `F-Release/F02-release-checklist/1.0.0-pre-release.md` tạo từ TEMPLATE-pre-release.md với tất cả placeholders filled
- [ ] `F-Release/F02-release-checklist/1.0.0-post-release.md` tạo từ TEMPLATE-post-release.md với tất cả placeholders filled
- [ ] Pre-release: Gate G1-G4 checklists cụ thể (ví dụ: PHPStan level 6, coverage threshold ≥60%)
- [ ] Pre-release: DB backup command cụ thể cho MariaDB 10.11 (per project context: `mysqldump --single-transaction`)
- [ ] Pre-release: Smoke test list — 5+ critical flows: login, course enroll, quiz submit, checkout, cert download
- [ ] Pre-release: Observability ready — dashboards `H07-observability-as-code/dashboards/` deployed; SLOs `H07-observability-as-code/slos/` active
- [ ] Pre-release: Runbooks H02-001..007 verified + linked
- [ ] Pre-release: Feature flags table populated với tất cả FF_FEAT_* từ `H05-feature-flags.md`
- [ ] Post-release: Monitoring period 48h; alert thresholds từ `H07-observability-as-code/alerts/`
- [ ] Post-release: Rollback procedure link đến H02 rollback runbook
- [ ] On-call schedule section: template filled với contact info format

## Technical Notes
- Template source: `F-Release/F02-release-checklist/TEMPLATE-pre-release.md` (đọc kỹ trước khi fill)
- Template source: `F-Release/F02-release-checklist/TEMPLATE-post-release.md`
- Observability references: `H-Operations/H07-observability-as-code/alerts/01-error-rate.md`, `02-payment-webhook-fail.md`, `03-quiz-grading-latency.md`
- SLO references: `H-Operations/H07-observability-as-code/slos/01-course-detail-availability.md`, `02-quiz-submission-success.md`
- Existing pre-release reference: `F-Release/F02-release-checklist/0.1.0-scaffold.md` (nếu tồn tại)
- DB backup: MariaDB 10.11, `mysqldump -u {user} -p{pass} --single-transaction --all-databases > backup_v1.0.0_$(date +%Y%m%d).sql`
- Version branch: `release/1.0.0` theo Git Flow convention

## Dependencies
- TASK-069 (CI pipeline) — G3 gate cần CI pipeline functional
- TASK-067 (E03 test definitions) — G3 gate cần E03 coverage
- TASK-068 (E2E tests) — smoke test items map to E2E specs
- Tất cả P0 hardening tasks (TASK-015..017, TASK-029..031) — release bị block nếu P0 còn BACKLOG

## Definition of Done
- [ ] 2 files tạo: `1.0.0-pre-release.md` và `1.0.0-post-release.md`
- [ ] Zero placeholder `{xxx}` còn lại trong cả 2 files
- [ ] SA review và sign Sign-Off section trong pre-release checklist
- [ ] Runbooks H02-001..007 được verify còn valid (cross-check với H02 dir)
- [ ] Observability dashboards + SLOs links verified còn tồn tại
