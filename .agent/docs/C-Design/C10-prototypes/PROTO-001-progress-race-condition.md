# PROTO-001 — Progress Cascade Race Condition

**Spike Area:** Lựa chọn cơ chế chống lost-update / duplicate `CourseCompletedEvent` cho `ProgressApiController::markComplete()` khi học viên gửi nhiều request đồng thời (multi-tab, double-click, retry).
**Hypothesis:** `SELECT ... FOR UPDATE` (Option A trong ADR-010) đủ ngăn race condition cho scale hiện tại của EverLMS (<1k concurrent users) với deadlock rate < 0.1% và P95 added latency < 80ms. Queue worker (Option B) không cần thiết cho v1.
**Timebox:** 1 ngày (8 giờ)
**Priority:** High — block TASK-017 và mọi unit downstream của lms_progress; mitigates RISK-002 (G02 Critical → High khi đã có spike).
**Status:** PENDING

---

## Approach

1. **Setup nhanh** — clone code hiện tại của `ProgressApiController::markComplete()` ra branch `spike/proto-001-progress-lock`. Không sửa production code trong spike.
2. **Reproduce race condition** — viết script PHP CLI dùng `pcntl_fork` (hoặc Drush + GNU parallel) bắn 20 request song song vào `markComplete` cho cùng `(uid, course_id)` ở lesson cuối cùng. Kỳ vọng: ≥ 1 lần dispatch `CourseCompletedEvent` trùng → log certificate cấp 2 lần.
3. **Implement Option A** — wrap cascade trong `\Drupal::database()->startTransaction()` + `SELECT id FROM lms_user_course WHERE uid = :uid AND field_course_target_id = :cid FOR UPDATE`. Đặt try/catch để rollback và bắt `IntegrityConstraintViolationException` (deadlock SQLSTATE 40001 / 1213).
4. **Retry logic** — wrap tại service boundary: max 3 lần retry với exponential backoff (50ms, 100ms, 200ms) khi gặp deadlock; sau đó throw 503.
5. **Re-run script song song** — đo: (a) số `CourseCompletedEvent` dispatch (phải = 1), (b) deadlock count, (c) P50/P95/P99 latency của HTTP response, (d) lock-wait timeout occurrences.
6. **Implement Option C (optimistic lock)** — chỉ trên branch riêng để so sánh: thêm cột `version` (migration tạm) + retry on `affected_rows = 0`. Chạy lại cùng script để có baseline.
7. **Stress test scale** — chạy với 50 và 100 concurrent users (vẫn cùng course) để xem khi nào lock-wait timeout vượt ngưỡng acceptable (>5%).
8. **Document & decide** — fill section Outcome bên dưới; nếu Option A pass tất cả success criteria → đề xuất SA chốt DEC-003 = A; nếu fail → escalate Option B (queue worker).

---

## Success Criteria

- **Correctness:** 0 duplicate `CourseCompletedEvent` trong 100 lần chạy với 20 concurrent requests cho 1 user/course (race window đã đóng).
- **Latency:** Added latency từ `FOR UPDATE` ≤ 80ms ở P95 vs baseline (no lock) trên dataset ≥ 50 enrollments cho user test.
- **Deadlock rate:** ≤ 0.1% trong 1000 cascade calls; tất cả deadlock được retry thành công trong ≤ 3 lần.
- **Lock-wait timeout:** Không transaction nào vượt 1s lock wait ở 20 concurrent users; ≤ 1% timeout ở 50 concurrent users.
- **Backward compatibility:** Không thay đổi schema hiện tại (Option A); Option C chỉ dùng để so sánh, không yêu cầu migration cho v1.

---

## Dependencies

- DB access tới MariaDB 10.11 staging (cần quyền `LOCK TABLES`, `SELECT FOR UPDATE`).
- User test có ≥ 1 enrollment với ≥ 10 lessons để mô phỏng cascade tới 100%.
- Drush ≥ 13 trên môi trường spike để chạy script song song.
- `lms_progress` đã có routing `/api/lms/progress/lesson/complete` (đã tồn tại theo `ProgressApiController.php`).
- ADR-008 (cascade strategy) làm input — nếu cascade đổi shape, spike phải re-run.

---

## Outcome

**Status:** PENDING
**Result:** _(điền sau khi chạy: số duplicate events, latency P50/P95/P99, deadlock count, lock-wait timeout count)_
**Decision:** _(điền sau: A / B / C cùng justification dựa trên success criteria)_

### Artifacts

- _(điền sau)_ Branch URL: `spike/proto-001-progress-lock`
- _(điền sau)_ Stress test script: `.agent/scripts/proto-001-stress.php`
- _(điền sau)_ Latency histogram: `.agent/generated/proto-001/latency.csv`
- _(điền sau)_ Run log: `.agent/generated/proto-001/run-{timestamp}.log`

### Impact on Architecture

- **Nếu VALIDATED (Option A):** Chốt DEC-003 = A trong A03-project-context.md; cập nhật ADR-010 status `ACCEPTED`; mở ADR mới nếu cần document lock ordering rule cho cascade. Refactor `ProgressApiController::markComplete()` thành `ProgressCascadeService::markLessonComplete()` (kết hợp ADR-008) trong unit U-059. Không thay đổi C01.
- **Nếu INVALIDATED (Option A fail latency hoặc deadlock thresholds):** Escalate Option B (queue worker); cần ADR mới mô tả queue contract + frontend polling; cập nhật C01 §Cross-Cutting Concerns thêm "Async cascade via Drupal Queue + Redis"; thêm RISK mới về eventual consistency UX.
- **Nếu INCONCLUSIVE:** Chạy lại với load profile thật từ analytics (sau khi có user data); tạm thời ship Option C (optimistic lock) như interim fix có cost migration thấp.

---

## Related

- **Risk:** RISK-002 (G02-risk-register.md)
- **Open decision:** DEC-003 (A03-project-context.md)
- **ADRs:** ADR-008 (cascade strategy), ADR-010 (race condition strategy — PROPOSED)
- **Implementation reference:** `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php`

---

## Implementation status (as of 2026-06-20)

**PENDING spike — race condition is still present in production code.**

`ProgressApiController::markComplete()` at `/api/lms/progress/mark-complete` (POST) is fully implemented for the happy path: upserts `lms_user_lesson`, recalculates `lms_user_course.field_progress`, dispatches completion. However, **no locking or transaction wrapping exists**. Concurrent requests from the same user (multi-tab, double-click, mobile retry) can produce duplicate `CourseCompletedEvent` dispatches and double-issued certificates.

This spike has **not been executed**. The race condition described is a known RISK (G02-risk-register.md) that remains open. No `SELECT FOR UPDATE`, queue worker, or optimistic lock has been added to the production codebase.

Related code: [../C05-modules/lms_progress.md](../C05-modules/lms_progress.md)
