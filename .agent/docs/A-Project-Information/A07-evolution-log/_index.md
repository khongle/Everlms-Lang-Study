# A07-evolution-log - Project Documentation Evolution

Log append-only ghi lại evolution của baseline **documentation** (khác với `M-Maintenance/M05-evolution-log/`, vốn theo dõi sản phẩm). Các row mới được append vào cuối; lịch sử không bao giờ bị viết lại. Một row được append mỗi khi một audit `Z-Reviews/` chạy.

| Date | Event | Impact |
| ---- | ----- | ------ |
| 2026-07-11 | Z-review `2026-07-11-a-d-consistency-review` run (rà nhất quán A–D + gate 6.0 C11 remediation + Việt hóa) | Verdict GO-WITH-FIXES; consistency A–D closed; full A06 suite E–U đang hoàn thiện |
| 2026-06-20 | Prior draft `docs-draft-1` built (A-E, as-built) | 715 files; A-E materialised against live code; predates folder-per-slot + new slots |
| 2026-06-21 | `00-Guidelines` refreshed (folder-per-slot, full A-U+Z, new C09/C14-C16/D09 slots) | Drafts rendered structurally stale; rebuild required |
| 2026-06-21 | **Rebuild `.agent/docs/` fresh from source** into full A-U+Z started | Canonical tree regenerated from live code/config/DB; drafts demoted to `00-project-input` references |
| 2026-06-21 | Z-review `2026-06-21-initial-build-readiness-review` run | Verdict GO-WITH-FIXES; P0 = secrets-in-config + payment webhooks; P1 = badge awarding + test coverage | <!-- credential-sweep:allow: risk description R-001 -->

<!-- Append row mới vào cuối; không bao giờ viết lại lịch sử. -->
