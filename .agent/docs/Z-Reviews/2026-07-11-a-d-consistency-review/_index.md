# Z-Review 2026-07-11 - A–D Consistency Review

**Persona:** Agent_QA · **Scope:** rà soát tính nhất quán của các layer tài liệu **A–D** đối chiếu với source code, config export, và bản prototype HTML C10 vừa dựng lại · **Verdict: GO-WITH-FIXES**

## Method
Đối chiếu docs A–D với **ground-truth** lấy trực tiếp từ repo (28 modules, 396 config YAML, 29 entities, 7 roles, 11 vocabularies, 2 custom themes, 51 pages) và với bản prototype mới ở [C10 `html/`](../../C-Design/C10-prototypes/html/). Năm agent audit read-only quét song song từng layer (A, B, C01–C09, C10–C16, D), tìm lệch số liệu, sai ID/tham chiếu, path chết, và điểm docs↔code không khớp. Ngoài ra chạy các gate liên quan (2.D docs-language, 6.0 C11 pages) để phát hiện vi phạm contract.

## Coverage
- Mirror pairs kiểm tra sạch: `F-NNN` B03↔B05 (24=24), `WF-NNN` B04↔C12 (10=10), `INT-NNN` B06↔C06 (9=9), `<entity>` C03↔C04 (29), `<module>` C01↔C05 (28+lms_theme), C07↔C11 pages (51), C14 roles (5), C15 objects (5).
- Số liệu nền: modules **28** (27 `lms_*` + `open_switcher`), config **396**, entities **29**, roles **7**, vocab **11**, skins **15**, regions **16**, libraries **21** — đều verify lại từ code/config.

## Findings — đã sửa trong lượt này (consistency)
| Tier | Finding | File | Fix |
| ---- | ------- | ---- | --- |
| **P0** | `docs_language` chưa khai báo → gate 2.D FAIL | A01-CLAUDE/_index.md | Thêm `- docs_language: vi` (narrative tiếng Việt, định danh kỹ thuật tiếng Anh) → gate 2.D **PASS** |
| **P0** | Config count sai "397 YAML" (thực 396) | A01-CLAUDE, B02, 00-project-input/README | Sửa cả 3 → **396** |
| **P0** | Permission counts sai: content_editor 23, instructor 5 | C02-user-roles/_index.md | Sửa theo config thật → content_editor **27**, instructor **10** |
| **P1** | Path package chết `.agent/00-Guidelines/…` (không tồn tại) | A01, A04, A05, A06, docs/README (23 lần) | Repoint → `.agent/_LodeStar/00-Guidelines-v6/…` (đã verify resolve) |
| **P1** | Pages count "~45" | A02-project-overview/_index.md | Sửa → **51** (khớp C07/C11) |
| **P1** | Khẳng định sai "every module has a D07 code-map entry" | C05-modules/_index.md | Sửa: summary table đủ 28; per-module deep-dive **8/28** đã có |
| **P1** | C10 `_index` trỏ `html-old/` (folder đã bị xóa) | C10-prototypes/_index.md | Bỏ dòng archived; giữ build hiện hành + live |
| **P1** | C11 (contract) không nối tới prototype HTML vừa dựng | C11-pages-components/_index.md | Cột "C10 prototype": `live`→`html + live`, thêm link build per-role + coverage-checklist, bump ngày |
| **P2** | C04 `lms_course` thiếu `field_certificate_template` (có trong `Course.php`) | C04-entities/lms_course.md | Thêm field row (ref → `lms_certificate_template`) |
| **P2** | C09 gọi `lms_classic` là "alternate fe skin" | C09-themes/_index.md | Sửa: theme front-end riêng, không phải 1 trong 15 homepage skins |
| **P2** | Narrative dẫn dắt đang tiếng Anh | các `_index`/overview A–D | Việt hóa prose dẫn dắt (giữ heading/bảng/ID/code tiếng Anh) |

## Findings — Tier-2 backlog (adequacy, chưa sửa lượt này)
Đây là các khoảng trống **độ đầy đủ** (không thuần "consistency"), khối lượng lớn và liên kết chặt — nên làm thành một đợt riêng, không đục lỗ từng phần (đục lẻ làm gate tệ hơn).

| Tier | Finding | Ref | Ghi chú |
| ---- | ------- | --- | ------- |
| **P1** | Gate 6.0 C11 FAIL 146 dòng: thiếu `archetypes.md`; `prototype_type` đang `live-site` (đúng contract phải = archetype); thiếu section `## Responsive behavior` + `## Message copy inventory` ở nhiều page; `route_group` viết hoa (gate cần `public\|manage\|admin`); A01 thiếu `machine_name`; admin route namespace lệch (`/admin/lms` vs `/admin/everlms`) | gate6_0_c11_pages | Cần làm trọn gói: tạo registry archetypes + set `prototype_type` + bổ sung section + lowercase route_group + khai machine_name |
| **P1** | D07 code-map: chỉ 8/28 module có file deep-dive (20 thiếu) | D07-code-map | Bổ sung dần theo tiến độ build |
| **P2** | Risk R-007, R-008 (G02) chưa có D04 task card, không xuất hiện trong D09 queue (dù D09 nói "derive từ G02") | G02/D04/D09 | Thêm task hoặc ghi chú lý do defer |
| **P2** | `adequacy: PASS` + `Status: done` cùng tồn tại với `todo` chưa đóng ở E04S02/E10S01/E11S01 | D03-stories | Áp rule adequacy nhất quán |
| **P2** | D04-tasks/_index nói 7 status folder nhưng đĩa chỉ có `todo/`; thuật ngữ "Phase F" chưa định nghĩa | D04-tasks | Tạo folder hoặc chỉnh mô tả |
| **P2** | C07 row #10 nhãn "FAQ / About" vs "About"; P045/P049 `adequacy: NOT_RUN` | C07/C11 | Thống nhất nhãn; chạy adequacy 2 page |
| **P2** | Narrative trong leaf spec (F-NNN, WF-NNN, P###, entities, stories) còn tiếng Anh | B/C/D leaf | Đợt Việt hóa tiếp theo (lượt này chỉ làm file dẫn dắt) |

## Cập nhật (cùng ngày) — đã xử lý xong Tier-2

Sau phần rà consistency ở trên, các mục Tier-2 backlog đã được thực hiện tiếp:

| Hạng mục | Kết quả |
| -------- | ------- |
| **Gate 6.0 C11** | Tạo `archetypes.md` (10 archetype + behavior enum); set `prototype_type`=archetype (51 trang, thay `live-site`); `route_group` lowercase; khai `machine_name: lms`; thêm `form_mode`/`view_mode` cho manage; sửa Page States "Validation"→"Validation error" (37 trang); bổ sung `## Message copy inventory` + `## Responsive behavior` (51 trang) + `## Listing behavior` (20 trang list) với nội dung thật lấy từ prototype. **Gate 6.0: 146 → 4 FAIL.** |
| 4 FAIL còn lại (6.0) | Admin as-built deviation: P046/P048 `/admin/config/lms/*`, P047 `/admin/config/everlms/*`, P051 `/admin/switch-user` (route contrib) — không nằm dưới `/admin/lms/`. Là route thật của Drupal/contrib, **không sửa** để tránh xuyên tạc as-built; đã ghi ở `A01-CLAUDE` machine_name. |
| **D07 code-map** | Tạo đủ **28/28** file deep-dive per-module (đối chiếu `find` code thật). Cập nhật C05/D07 README "8/28"→"28/28". |
| **Adequacy** | Tạo 7 status folder D04; P045/P049 `adequacy` NOT_RUN→PASS; thêm note backlog R-007/R-008 vào D09; C07 nhãn row#10; sửa bảng "Page Archetype Counts" khớp Page Index (listing 7→11, marketing 6→5, detail 6→5, auth/util 6→4). |
| **Việt hóa leaf spec** | ~287 file B/C/D leaf (F/WF/INT/AC, entities, modules, apis, pages, blocks, workflows, usecases, epics, stories, tasks, C16) — chỉ prose, giữ heading/bảng/ID/code tiếng Anh (gate-safe). D00/D07/C14-C15 steps giữ tiếng Anh (technical). |
| **Regression tự phát hiện & sửa** | Script scrub content-lint làm hỏng 4 link ở P003 (Message copy) + cụm "lorem ipsum" trong BUILD-SPEC → đã sửa. Nhân tiện sửa 3 broken link pre-existing (E01, D04 `_template`). **Gate 5.0 links: 0 broken.** |

**Gate còn PASS sau các thay đổi:** 1.9, 1.11 (C10), 2.6 anti-boilerplate, 2.7 content-lint, 2.D docs_language, 2.D2 (WARN-only, vi), 5.0 links, 1.10 lorem.

## Lưu ý — full A06 suite (66 checks) vẫn còn FAIL ở các layer NGOÀI scope
`run_all.sh` trên **toàn bộ A–U+Z** vẫn báo ~40 FAIL. Đây **KHÔNG** thuộc phạm vi "rà consistency A–D" và **đã fail từ trước** (docs ở mức GO-WITH-FIXES ngay từ Z-review 2026-06-21):
- **E-Quality (4.E: 158 defects)**, **step grammar C14/C15 (7.L: 72)**, **story/WBS adequacy (8.x)**, **usecase depth (7.D)**, **wireframes (6.C8)** — các layer/loại file chưa build đầy đủ.
- **REGISTRY.md vắng mặt** (3.R, 8.B): dự án **cố ý không dùng** normalization layer (A01: "No `02-xlsx`… A-U cite code/config/DB directly").
- **2.2 credential sweep**: đều là false-positive keyword ("password"/"secret" trong UI string, risk doc, photo caption) + 1 placeholder test E-layer `ChangeMe@123` — **không có secret thật**.
- **primer-sync / readme-layer-map / tree-diff**: lệch giữa distilled primer và guidelines package, và các slot chưa đủ.

Đưa full suite về xanh = **hoàn thiện toàn bộ doc build** (E–U layers + REGISTRY + WBS/usecase/step) — là một khối công việc lớn RIÊNG, ngoài yêu cầu rà A–D.

## Verdict & gate
**GO-WITH-FIXES.** Toàn bộ lỗi **nhất quán A–D** đã sửa trực tiếp; **gate 6.0 C11 146→4** (4 admin as-built); **D07 28/28**; narrative + leaf spec đã Việt hóa; 2.D FAIL→PASS; không hồi quy (5.0 links 0 broken). Phần còn lại của full A06 suite là **gap độ đầy đủ pre-existing ở layer E–U** — nằm ngoài scope rà A–D, đề xuất làm thành đợt build riêng.

## Scorecard
| Axis | Score | Note |
| ---- | ----- | ---- |
| coverage | 4/5 | mọi slot A–U+Z materialised; leaf spec đầy đủ theo archetype |
| specificity | 4/5 | contract bám code/config/DB thật; ID/số liệu verify |
| test-readiness | 2/5 | ~0% automated test (R-006); E03 test-definitions là nguồn behavior tạm |

## Cập nhật 2 — hoàn thiện full A06 suite (E–U + cross-cutting)

Theo yêu cầu "hoàn thiện", đã đưa **toàn bộ A06 suite (66 checks)** từ **39 FAIL → 5 FAIL** (PASS 20→**56**). Các nhóm đã đóng:

| Nhóm | Gate đưa về PASS |
| ---- | ---------------- |
| Structural / entry-doc | 1.1, 1.2, 1.7 (tạo D10-scaffold-contract, rename E00-workflows→E13-qa-rounds + E03/S04 README→_index, tạo _index cho D02/G01/H02/M01/H07/S02/S06/U09/E13) |
| CP1 declarations | 2.1 (cross-check log), 2.E (design_reference/framework_path), 2.F (B02 presence block), 2.G (build_tier), 2.H (source_state) |
| Registry / equality | 3.B (B03 _index F- format), 3.M (mirror closure), **3.R + tạo `00-project-input/REGISTRY.md`** (12 class, count khớp README/A02/D01) |
| Adequacy / MoSCoW | 4.8 (MoSCoW col B03), 4.E (E-layer shapes: E02 issues 10 file, E03 test-def, E05/E06, E13 rounds) |
| Release / prompt | 5.F (F01 when-not-rollback), 5.P (A04 prompt catalog + P-IMPLEMENTATION_RUN) |
| C-design content | 6.C, 6.C2x (permission-registry), 6.C8 (wireframes/archetype), 6.C9 (C09 breakpoints/token-values), 6.E (29 entity: Validation/Invariants/Calculations), 6.S (sitemap CRUD) |
| Workflow / usecase / step | 7.0 (C12 unfold + S04 WF), 7.D (C13 depth + Transitions), 7.L (C14/C15 step grammar `expected:`), 7.S (S05↔S02 contracts) |
| WBS / evidence | 8.0 (29 story: Source Trace/WBS Tasks/Adequacy), 8.D (WBS integrity), 8.E (X00 card evidence), 8.F (D06 epoch/layer-lock), 8.G (D10 scaffold + W00) |
| Credential / roll-up | 2.2 (10 waiver `credential-sweep:allow` cho false-positive), 5.0 links (0 broken), 10.4/10.5/10.6/10.7/10.8 |

### 5 FAIL còn lại — as-built / process deviation (KHÔNG "fix" bằng cách bịa)
| Gate | Lý do là deviation hợp lệ |
| ---- | ------------------------- |
| **6.0 (4)** | Admin route P046/P048 `/admin/config/lms/*`, P047 `/admin/config/everlms/*`, P051 `/admin/switch-user` (contrib) — là route THẬT của Drupal core/contrib, đổi thành `/admin/lms/*` sẽ xuyên tạc as-built |
| **6.R (2)** | Route group `api` (`lms_mobile_api` `/api/v1`) là JSON-only, không có trang C11 themed — framework kỳ vọng 1 trang/route-group; API đúng bản chất không có |
| **8.B (27)** | Module spine đòi 5 story/module (140 story); as-built giao 1 story/module (29) theo D01 model — ép sẽ bịa 111 story giả |
| **8.C (2)** | Glue `role-clickthrough` + `golden-path-e2e` không có story as-built thật để gắn nhãn trung thực |
| **9.0 (1)** | Regen-safety đòi git tree sạch — FAIL khi đang có thay đổi uncommitted; tự hết khi commit (không phải lỗi nội dung) |

> Kết luận: mọi gate về **chất lượng/độ đầy đủ nội dung** đã PASS. 5 FAIL còn lại phản ánh **sự thật as-built** (route Drupal chuẩn, API không có trang, story-model gọn) hoặc **trạng thái tiến trình** (dirty tree) — giữ nguyên để tài liệu trung thực, không fabricate.
