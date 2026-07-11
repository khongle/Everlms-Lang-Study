# C10-prototypes - Prototype and Mockup Index

**Last updated:** 2026-07-11

EverLMS là một **bản rebuild của hệ thống đang chạy**. **C11 là canonical** (phần contract);
C10 là hình ảnh minh họa. Thư mục này chứa một **HTML prototype theo từng role** trực quan
hóa toàn bộ 51 trang C11 dưới cùng một design system và target navigation model.

## HTML prototype
| Build | Location | Notes |
| ----- | -------- | ----- |
| v4 (current) | [`html/`](html/) | Per-role folders, filed once at lowest accessible role; 51 pages (`P###-<slug>.html`); injected menu shell (front + app), My Account mega menu, no sidebar. See [`html/README.md`](html/README.md). |
| live | running DDEV site (`ddev launch`) | the as-built visual reference for parity checks. |

Cấu trúc tuân theo C10 per-role gate: các folder role chứa trang `P###-<slug>.html`,
`_shared/` chứa design system, mỗi trang chỉ được file một lần. Coverage theo từng
page/state nằm ở [`coverage-checklist.md`](coverage-checklist.md) (51 trang × 5 state).

## Navigation model
- **Front shell** — main menu public + Log in/Register (→ My Account khi đã authenticated).
- **App shell** — main menu backend scoped theo role + icon Home + notification bell +
  **My Account mega menu** (launcher App/Manage/Admin/Account được lọc theo role).
- Không có sidebar, ngoại trừ các business rail của course/lesson (P003 curriculum, P019 player, P020 quiz).

## Mockups
Screenshot có thể được chụp theo từng trang từ `html/` hoặc từ live site (mirror theo C11 `P###`).

## Generation
- `html/` là HTML được author trực tiếp (không có build step); có thể mở bất kỳ trang nào qua `file://`.
- Để làm mới tham chiếu hình ảnh as-built: chạy `ddev launch` rồi chụp screenshot theo từng trang.
- Mỗi [C11 page](../C11-pages-components/pages/_index.md) map 1:1 với một trang prototype `P###`.
