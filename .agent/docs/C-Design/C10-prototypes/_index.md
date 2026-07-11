# C10-prototypes - Prototype and Mockup Index

**Last updated:** 2026-07-11

EverLMS is a **rebuild of a running system**. **C11 is canonical** (the contract); C10 is
the picture. This folder holds a **per-role HTML prototype** that visualises all 51 C11
pages under one design system and the target navigation model.

## HTML prototype
| Build | Location | Notes |
| ----- | -------- | ----- |
| v4 (current) | [`html/`](html/) | Per-role folders, filed once at lowest accessible role; 51 pages (`P###-<slug>.html`); injected menu shell (front + app), My Account mega menu, no sidebar. See [`html/README.md`](html/README.md). |
| v3 (archived) | [`html-old/`](html-old/) | Prior flat build (kept for reference; superseded). |
| live | running DDEV site (`ddev launch`) | the as-built visual reference for parity checks. |

Structure follows the C10 per-role gate: role folders contain `P###-<slug>.html` pages,
`_shared/` holds the design system, each page filed once. Coverage per page/state is in
[`coverage-checklist.md`](coverage-checklist.md) (51 pages × 5 states).

## Navigation model
- **Front shell** — public main menu + Log in/Register (→ My Account when authenticated).
- **App shell** — role-scoped backend main menu + Home icon + notification bell + **My
  Account mega menu** (role-filtered App/Manage/Admin/Account launcher).
- No sidebar except course/lesson business rails (P003 curriculum, P019 player, P020 quiz).

## Mockups
Screenshots can be captured per page from `html/` or the live site (mirrors C11 `P###`).

## Generation
- `html/` is authored HTML (no build step); open any page over `file://`.
- To refresh live visual reference: `ddev launch` and capture per-page screenshots.
- Each [C11 page](../C11-pages-components/pages/_index.md) maps 1:1 to a `P###` prototype page.
