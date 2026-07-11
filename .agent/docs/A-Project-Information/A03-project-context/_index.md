# A03-project-context - Living Context

**Last updated:** 2026-06-21

Đây là điểm neo bàn giao (handoff anchor) cho bất kỳ persona nào tiếp tục công việc. Đây là A-slot duy nhất thực sự thay đổi thường xuyên. Nó phản chiếu (mirror) các blocker đang tồn tại từ `G-Governance/G03-blockers/` và verdict readiness mới nhất từ `Z-Reviews/`.

## Current state
Cây `.agent/docs/` đang được **rebuild từ đầu dựa trên source** (live code > config export > DB) thành đầy đủ cấu trúc A-U + Z theo `00-Guidelines` đã cập nhật. Bản thân sản phẩm (EverLMS) là một LMS Drupal 11 đang hoạt động với dữ liệu demo đã seed; tài liệu — chứ không phải code — mới là phần công việc đang được thực hiện. Tình trạng phase của *sản phẩm* là **post-build / maintenance** (phần lớn module đã được build, còn một số gap và risk còn mở); còn của *docs* là **Phase 1-3 regeneration** (requirements/architecture/framework được ghi nhận as-built).

## Recent decisions
| Date | Decision | Rationale | Linked docs |
| ---- | -------- | --------- | ----------- |
| 2026-06-21 | Rebuild `.agent/docs/` fresh from source into full A-U+Z (not synthesise drafts) | Drafts predate the current `00-Guidelines` (folder-per-slot, new slots); source is the only reliable truth for a rebuild | [G01-adrs/ADR-001](../../G-Governance/G01-adrs/ADR-001-rebuild-docs-fresh-from-source.md) |
| 2026-06-21 | Document as-built; mark stubs/dead-code/target explicitly | Honest build contract beats aspirational docs | [E06-implementation-status](../../E-Quality/E06-implementation-status/_index.md) |
| 2026-06-21 | Reference raw sources in-place (do not copy DB/keys into docs) | Avoid duplicating 21 MB DB + leaking secrets into a shareable tree | [00-project-input/README](../../00-project-input/README.md) |
| 2026-06-20 | Quiz grading via static `GradingStrategyFactory` (8 strategies) | Captured as the assessment architecture | [C01-architecture-decision](../../C-Design/C01-architecture-decision/_index.md) |

## Active blockers
| ID | Description | Owner | Since |
| -- | ----------- | ----- | ----- |
| BLK-001 | Third-party secrets committed in config YAML (no Key module) — blocks safe public release | Agent_Architect | 2026-06-20 |
| BLK-002 | No payment webhooks / signature verification — orders can desync from gateway truth | Agent_Implementer | 2026-06-20 |
| BLK-003 | `lms_badge` award logic absent — badges defined but never granted | Agent_Implementer | 2026-06-20 |
| BLK-004 | ~0% automated test coverage — G3 passes vacuously | Agent_QA | 2026-06-20 |

<!-- Phản chiếu (mirror) G03-blockers/_index.md; xóa các row khi đã resolve (ghi log resolution tại G03/E02). -->

## Next steps
Được dẫn dắt bởi verdict readiness mới nhất — `Z-Reviews/2026-07-11-a-d-consistency-review/` (mới nhất) → **GO-WITH-FIXES**; và `Z-Reviews/2026-06-21-initial-build-readiness-review/` (P0: BLK-001 secrets, BLK-002 webhooks; P1: BLK-003 badges, BLK-004 tests).
- [ ] Hoàn tất việc rebuild tài liệu A-U+Z (Agent_Architect, đợt này)
- [ ] Chuyển các third-party secret sang Key module / env (BLK-001, P0)
- [ ] Thêm các payment webhook endpoint + signature verification cho Stripe (BLK-002, P0)
- [ ] Implement trigger trao `lms_badge` hoặc đánh dấu chính thức deferred (BLK-003, P1)
- [ ] Thiết lập baseline PHPUnit kernel/functional cho các core flow (BLK-004, P1)
