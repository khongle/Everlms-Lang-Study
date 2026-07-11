# D06-handoff-protocol - Handoff Protocol

**Last updated:** 2026-07-11 (single-doc)
**Epoch:** 2
**Layer lock:** A–D + C10/C11 khóa (as-built, rà nhất quán 2026-07-11); E–U đang hoàn thiện.

## At session start
1. Nạp Tier-1: [A01-CLAUDE](../../A-Project-Information/A01-CLAUDE/_index.md), [A03-project-context](../../A-Project-Information/A03-project-context/_index.md), [D05-session-status](../D05-session-status/_index.md), file này.
2. Kiểm tra blocker còn mở ([G03](../../G-Governance/G03-blockers/_index.md)) + verdict Z-review mới nhất ([Z-Reviews](../../Z-Reviews/README.md)).
3. Xác nhận environment: `ddev start && ddev drush cr` (xem A01 commands).

## During work
- Cập nhật [D07-code-map](../D07-code-map/README.md) khi tạo/sửa file.
- Giữ thay đổi trong phạm vi story; việc mới ngoài phạm vi (cross-scope) → tạo `G04` change request.
- Tuân thủ gate G1→G4 và editorial-usability invariant.

## At session end
- Cập nhật [D05-session-status](../D05-session-status/_index.md) (done / in-progress / next).
- Ghi log session trong [D08-sessions](../D08-sessions/README.md); bổ sung các thay đổi cấu trúc docs vào [A07-evolution-log](../../A-Project-Information/A07-evolution-log/_index.md).
- Chỉ commit khi user yêu cầu; branch từ `main` trước.
