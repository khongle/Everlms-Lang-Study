---
page: Content tools
drupal_concept: Form API + batch
mode: as-built
owner_module: lms_content_tools
route_group: manage
route_name: lms_content_tools.dashboard
form_mode: manage_edit
prototype_type: form
adequacy: PASS
standalone: tiện ích seed/import demo content cho admin, không thuộc một workflow B04 cụ thể
---

# C11 Page - Content tools

## Purpose
Seed/import/dọn dẹp nội dung demo tại `/workspace/content-tools`: nạp seed input, import JSON đã sinh, xoá sạch nội dung đã sinh (F-023). Đây là UI chuẩn của module Content Tools.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/workspace/content-tools` (seed-input, clean) |
| Route name | `lms_content_tools.dashboard` |
| Drupal page primitive | Form API + Batch API |
| Owner module | lms_content_tools |
| Access | administrator |
| Cache | uncacheable (form) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| — | (form only) | content | — |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Actions | seed-input, import (`.agent/generated/*.json`), clean |
| Source | `SeedInputForm` → `ContentToolsManager`; mirrors Drush `lms:import`/`lms:clean` |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Import status/counts | content_tools service | administer | — | import → counts (40 courses) |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| Tool labels | admin UI chrome | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | tool buttons + status |
| Empty | nothing seeded → import CTA |
| Access denied | non-admin → 403 |
| Validation error | bad seed JSON → error |
| Loading | batch progress |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA chính (chạy tool Summarize) | "Generate" | the module (form "Try it: Summarize", nút `.btn.btn--primary`) |
| CTA phụ (mỗi thẻ tool trong lưới `.grid.grid--3`) | "Run" | the module (thẻ `.card .btn--ghost.btn--sm`, dùng chung cho 5 tool: Summarize, Generate quiz questions, Rewrite/translate, Alt-text generator, SEO meta) |
| Cảnh báo minh bạch AI (Normal) | "AI-drafted content" / "Output is a first draft. Review facts, tone and links before publishing — AI tools do not have editorial authority." | the module (`.alert.alert--info` phía trên form) |
| Hành động trên kết quả sinh ra (Success) | "Copy" / "Regenerate" / "Insert into lesson" | the module (`.panel .btn-row` trong khối "Generated result") |
| Xác nhận sau khi import seed data (Success) | "40 courses imported." (minh hoạ theo số liệu đã ghi nhận) | content tools service — xem ## Visible content inventory ở trên, smoke evidence "import → counts (40 courses)"; chưa có mẫu tĩnh trong prototype AI-tools hiện có |
| Chưa seed nội dung (Empty) | "Nothing has been seeded yet. Import demo content to get started." | the module (seed-input empty-state), suy luận từ dòng "Empty \| nothing seeded → import CTA" ở ## Page States — chưa có mẫu tĩnh trong prototype AI-tools |
| Trống trường bắt buộc (Validation error) | "Source text is required before you can generate a summary." | Form API `#required` constraint cho field `#tool-input` (Source text) — chưa có mẫu tĩnh lỗi trong prototype, suy luận từ input bắt buộc trước khi bấm "Generate" |
| Seed JSON không hợp lệ (Validation error) | "The uploaded seed file is not valid JSON." | the module Form API validation (SeedInputForm) — suy luận từ dòng "Validation error \| bad seed JSON → error" ở ## Page States, chưa có mẫu tĩnh trong prototype |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px: lưới 5 thẻ tool `.grid.grid--3` đã về 1 cột từ ngưỡng `@media(max-width:639.98px){.grid--3{grid-template-columns:1fr}}`; khối "Try it: Summarize" `.grid.grid--2` (form nhập + panel "Generated result") cũng về 1 cột ở cùng ngưỡng 639.98px, panel kết quả xếp ngay dưới form theo thứ tự DOM; `.field--row` (Target length / Tone) về 1 cột do `@media(max-width:575.98px){.field--row{grid-template-columns:1fr}}`; `.pagehead` (flex-wrap) xếp tiêu đề/mô tả trên, nút "AI course builder" xuống dòng dưới; `.container` co về 100% width. |
| tablet | 768–991px: vẫn trong vùng `@media(max-width:991.98px)` nên `.grid--3` (5 thẻ tool) co còn `repeat(2,1fr)` (2 cột, thẻ thứ 5 rơi xuống dòng riêng); `.grid--2` (form/kết quả) đã vượt ngưỡng 639.98px nên trở lại 2 cột ngang hàng; `.field--row` cũng đã qua ngưỡng 575.98px nên là 2 cột; `.container` giới hạn theo `--container-sm`/`--container-md`. |
| desktop | 992–1199px: vượt ngưỡng 991.98px nên `.grid--3` trở lại đủ 3 cột (5 thẻ tool xếp 3+2); `.grid--2` giữ 2 cột; `.container` giới hạn theo `--container-lg`; `.pagehead` và nút "AI course builder" nằm cùng hàng, không wrap. |
| wide | ≥1200px: `.container` mở theo `--container-xl` (tới 1399.98px) rồi theo max-width mặc định ở ≥1400px; bố cục giữ nguyên 3 cột lưới tool + 2 cột try-it; khoảng cách giữa các cột cố định `gap:1.4rem` (không dùng `clamp()` nên không giãn thêm theo viewport). |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Seed/import/clean | done | matches Drush commands |
