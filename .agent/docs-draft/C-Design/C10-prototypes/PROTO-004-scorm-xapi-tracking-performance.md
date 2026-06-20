# PROTO-004 — SCORM 1.2 / xAPI Tracking Endpoint Performance

**Spike Area:** Validate khả năng phục vụ SCORM 1.2 runtime API (`LMSCommit`/`LMSSetValue`) và xAPI `Statement` POST của EverLMS dưới tải concurrent realistic (50 → 200 students học SCORM song song), không bị bottleneck ở DB write hoặc Drupal bootstrap. SCORM package extraction (`_lms_lesson_extract_scorm`) phải an toàn (ZipSlip, path traversal) và đủ nhanh cho upload mediocre 50MB.
**Hypothesis:** SCORM tracking commits có thể serve qua endpoint Drupal-light (skip full bootstrap) đạt P95 ≤ 100ms ở 200 concurrent users; xAPI Statement queue được trong Redis và worker-flushed không gây contention với progress cascade (PROTO-001). SCORM extraction an toàn với `ZipArchive` + canonical path check.
**Timebox:** 1 ngày (8 giờ — split: 3h SCORM endpoint, 2h xAPI, 2h extraction security, 1h report)
**Priority:** Medium — không block release nếu launch không SCORM, nhưng SCORM lesson type đã có trong `lms_lesson` (`field_scorm_package`) → cần validate trước khi promote feature.
**Status:** PENDING

---

## Approach

### Part A — SCORM 1.2 runtime endpoint

1. **Survey SCORM API surface needed** — SCORM 1.2 yêu cầu:
   - `LMSInitialize`, `LMSFinish`
   - `LMSGetValue`, `LMSSetValue` (most frequent)
   - `LMSCommit` (sends batch to LMS — đây là DB write hot path)
   - `LMSGetLastError`, `LMSGetErrorString`, `LMSGetDiagnostic`
2. **Implement minimal endpoint** trên branch `spike/proto-004-scorm-xapi`:
   - Route: `POST /api/lms/scorm/{lesson_id}/commit` accept JSON `{cmi.core.lesson_status, cmi.core.score.raw, cmi.core.session_time, ...}`.
   - Skip Drupal full bootstrap nếu khả thi (custom kernel listener `kernel.request` priority cao trả response trước khi chạy theme/block init); nếu không khả thi → optimize theo cách (a) `no_cache: TRUE`, (b) inject minimal services chỉ `database` + `current_user`.
   - Persist vào table `lms_scorm_attempts` (cột: `id`, `uid`, `lesson_id`, `cmi_data JSON`, `score`, `status`, `session_time`, `updated INT`).
3. **Bridge to lms_progress** — khi `cmi.core.lesson_status = 'completed' OR 'passed'` → call `ProgressCascadeService::markLessonComplete()` (theo PROTO-001 result). Verify cascade lock không deadlock với SCORM commit traffic.
4. **Load test** — `k6` hoặc `wrk` script:
   - 50 VUs × 60s, mỗi VU commit 1 request mỗi 2-5s (mô phỏng SCORM commit cadence thật: 5-30s/lần).
   - Ramp lên 200 VUs.
   - Đo P50/P95/P99 latency, error rate, DB connection pool saturation.

### Part B — xAPI statement endpoint

5. **Implement xAPI inbox endpoint** — `POST /api/lms/xapi/statements` nhận xAPI Statement JSON.
   - Validate minimal: `actor`, `verb.id`, `object.id`, `timestamp`.
   - Push vào Drupal Queue (Redis backend) `lms_xapi_statements` — KHÔNG xử lý sync. Return `200 + {id: <uuid>}` ngay.
   - Worker `XapiStatementWorker` consume queue → persist vào `lms_xapi_statements` table và bridge sang lms_progress nếu verb match `completed/passed`.
6. **Load test** — 500 statements/s burst trong 30s; verify queue không lost statement, worker drain ≤ 60s sau burst.

### Part C — SCORM package extraction security

7. **Audit current extractor** — `web/modules/custom/lms_lesson/lms_lesson.module` `_lms_lesson_extract_scorm()`:
   - Test ZipSlip: tạo malicious zip với entry `../../../../etc/passwd` → kỳ vọng extraction REJECT (canonicalize path + check `str_starts_with($real, $base_real)`).
   - Test symlink: zip chứa symlink trỏ ra ngoài → reject.
   - Test bomb: zip nén tỉ lệ cao (1KB → 1GB) → enforce extracted size cap (default: 200MB).
   - Test extension whitelist: chỉ cho phép `.html|.htm|.js|.css|.png|.jpg|.gif|.svg|.xml|.mp3|.mp4|.webm|.json|.txt|.woff|.woff2|.ttf|.otf|.eot`; reject `.php|.phtml|.htaccess|...`.
8. **Performance** — extract 50MB SCORM package (Articulate Storyline mock): mục tiêu < 5s, không block request thread (hook_entity_insert đang sync — nếu chậm phải đẩy queue).

---

## Success Criteria

- **SCORM commit latency:**
  - 50 concurrent users: P95 ≤ 80ms.
  - 200 concurrent users: P95 ≤ 150ms, error rate < 1%, DB pool no exhaustion.
- **xAPI throughput:** 500 statements/s burst chấp nhận được không drop; worker drain queue ≤ 60s.
- **Extraction security:** 4/4 test malicious zip bị reject với log entry; 0 file ghi ngoài `private://lessons/scorm/{lesson_id}/`.
- **Extraction performance:** 50MB SCORM extract < 5s (sync) hoặc < 30s (async via queue).
- **Cascade integration:** SCORM commit triggering `markLessonComplete` không gây deadlock thêm so với baseline PROTO-001.
- **No regression:** Existing lms_lesson tests vẫn pass (run `phpunit` cho module).

---

## Dependencies

- PROTO-001 result (cascade lock pattern) — SCORM bridge cần biết approach lock.
- Sample SCORM 1.2 package (use Articulate sample, hoặc generate qua `scorm-test-track` repo).
- `k6` hoặc `wrk` installed cho load test.
- Redis available (đã có trong stack).
- Sample xAPI Statement library (`Rusticisoftware/TinCanPHP` hoặc raw JSON crafting).
- DB migration cho `lms_scorm_attempts`, `lms_xapi_statements` (Phase 1 spike: hook_update_N tạm).

---

## Outcome

**Status:** PENDING
**Result:** _(điền sau: latency số liệu mỗi load level, malicious zip test results, extraction time, cascade integration test result)_
**Decision:** _(điền sau: chốt sync hay async cho SCORM commit; chốt async pattern cho xAPI; chốt extraction approach — sync hay queue)_

### Artifacts

- _(điền sau)_ Branch: `spike/proto-004-scorm-xapi`
- _(điền sau)_ k6 load test script: `.agent/scripts/proto-004-scorm-load.js`
- _(điền sau)_ Latency report: `.agent/generated/proto-004/scorm-latency.html`
- _(điền sau)_ Malicious zip test fixtures: `.agent/generated/proto-004/zips/`
- _(điền sau)_ Extraction security report: `.agent/generated/proto-004/extraction-audit.md`

### Impact on Architecture

- **Nếu VALIDATED:** Confirm SCORM/xAPI là first-class lesson types; tạo ADR-012 (mới) cho SCORM runtime + xAPI inbox endpoints; thêm `H02-runbook` cho SCORM extraction failures; cập nhật C04-entities với `lms_scorm_attempts`, `lms_xapi_statements` content entities; H06 capacity model thêm dimension SCORM concurrent.
- **Nếu INVALIDATED — latency > target:** Move SCORM commit handler ra khỏi Drupal — micro-service Node.js / Go đứng riêng đọc/ghi cùng MariaDB, chỉ bridge progress qua REST callback. Cần ADR architecture-level + thêm vào S03-topology.
- **Nếu INVALIDATED — extraction security:** Hoàn toàn block SCORM lesson type cho v1; chỉ enable sau khi rebuild extractor với strict canonical-path check + isolation (chroot / docker exec). Tạo H04 tech-debt entry.
- **xAPI scope:** Nếu xAPI không validate được trong timebox → defer sang phase 2; SCORM 1.2 đủ cho v1.

---

## Related

- **Risks:** RISK-002 (race condition shared with PROTO-001), không có risk riêng (cần raise nếu spike phát hiện).
- **Find candidates:** Có thể raise FIND mới về extractor security nếu test fail — track trong G02.
- **Affected entity:** `lms_lesson` (đã có `field_scorm_package`)
- **Affected unit:** lms_lesson SCORM type (chưa có unit ID — cần raise).
- **Implementation reference:**
  - `web/modules/custom/lms_lesson/src/Entity/Lesson.php` (`field_scorm_package`, type enum `'scorm'`)
  - `web/modules/custom/lms_lesson/lms_lesson.module` (`_lms_lesson_extract_scorm()`)
  - `web/modules/custom/lms_progress/` (cascade target)
- **External references:** SCORM 1.2 RTE spec; xAPI 1.0.3 spec (URLs to be added by implementer).
