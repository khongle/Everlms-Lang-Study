# H02 — Runbook Index

> Operational runbooks cho EverLMS (Drupal 11, DDEV local dev, MariaDB 10.11).
> Mỗi runbook là step-by-step executable — không có prose thừa.
>
> **Trạng thái:** Pre-production. Runbooks viết cho môi trường DDEV local + staging.
> Cập nhật paths khi deploy lên production.

---

## Index

| ID | Tên | Khi nào dùng | Last Tested |
|----|-----|-------------|-------------|
| [RUN-001](RUN-001-drupal-cache-rebuild.md) | Drupal Cache Rebuild | Config thay đổi, site lạ, sau deploy | UNTESTED |
| [RUN-002](RUN-002-config-export-import.md) | Config Export / Import | Sync config giữa môi trường, xung đột | UNTESTED |
| [RUN-003](RUN-003-database-backup-restore.md) | Database Backup & Restore | Backup định kỳ, recovery sau incident | UNTESTED |
| [RUN-004](RUN-004-payment-webhook-replay.md) | Payment Webhook Replay | Webhook bị miss từ Stripe/PayPal | UNTESTED |
| [RUN-005](RUN-005-ai-provider-key-rotation.md) | AI Provider Key Rotation | Rotate API key OpenAI/Gemini | UNTESTED |
| [RUN-006](RUN-006-stuck-progress-cascade.md) | Stuck Progress Cascade | UserCourse/UserLesson progress bị kẹt | UNTESTED |
| [RUN-007](RUN-007-failed-certificate-pdf.md) | Failed Certificate PDF | Certificate không generate được | UNTESTED |

---

## Runbook Format

Mỗi runbook có cấu trúc:
- **Metadata** — ID, owner, last-tested date
- **Khi nào dùng** — trigger conditions
- **Prerequisites** — kiểm tra trước
- **Steps** — numbered, có lệnh cụ thể
- **Verification** — xác nhận thành công
- **Rollback / Escape** — nếu runbook làm vấn đề thêm nặng
- **Tham chiếu** — H01 (log incident), H04 (tech debt), E02 (issue)

---

## Rules

- Runbook phải **tested** trước CP6 (release)
- Mọi alert trong H07 phải có `runbook:` link đến file trong folder này
- Sau mỗi lần thực thi, cập nhật `Last Executed` và ghi note nếu có deviation
- Nếu runbook sai → mở issue trong E02, sửa ngay trong sprint

---

*Cập nhật lần cuối: 2026-04-16*
