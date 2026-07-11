# A05-framework-primer - Framework Snapshot

**Last updated:** 2026-06-21

Bản snapshot cô đọng, có thể chia sẻ, của documentation contract mà EverLMS docs tuân theo. Cấu trúc gốc có thẩm quyền: `.agent/_LodeStar/00-Guidelines-v6/A01-target-docs-structure.md` (được giữ cùng SA). Primer này mang phần contract cốt lõi vào shared tree.

## Documentation contract
Cây `.agent/docs/` là một **build contract**, không phải một bản báo cáo: mọi page, entity, workflow và API đều được đặc tả để một implementer có thể build nó và một QA persona có thể verify nó. Đối với đợt rebuild này, chuỗi source-of-truth là **live code → config export (`config/sync/`) → database (`database/db.sql`) → A-U docs → (re)implementation**; layer chuẩn hóa `02-xlsx/03-md` tùy chọn **không** được sử dụng. Nếu một doc và code không khớp nhau, code luôn thắng và doc phải ghi lại điểm khác biệt đó.

## Required layers
Tất cả các layer canonical đều được ship (folder-per-slot; một slot có thể là stub kèm một CP1 ADR ghi lại lý do bị bỏ qua):
- **A** Project-Information · **B** Requirements · **C** Design · **D** Development · **E** Quality · **F** Release · **G** Governance · **H** Operations · **I** External · **K** Knowledge · **M** Maintenance · **S** System · **U** Userguide · **Z** Reviews.
- Mỗi slot cấp 1 (level-1) là một FOLDER `<SlotCode>-<slug>/` có một `_index.md` chính (hoặc `README.md` cho các folder hướng tới người dùng). Không có file `<SlotCode>-<slug>.md` trần dưới một layer.

## Naming conventions
ID registry (phải khớp byte-for-byte với `A01-target-docs-structure.md`):
- **Features + Acceptance** (shared slug): `F-NNN-<slug>` — `B03 ↔ B05 ↔ E05 ↔ U03`
- **Workflows:** `WF-NNN-<slug>` — `B04 ↔ C12 ↔ C13 folder ↔ S04`
- **Integrations:** `INT-NNN-<slug>` — `B06 ↔ C06`
- **Epics:** `E##-<slug>` (hierarchical WBS, D02) · **Stories:** `E##S##-<slug>` (D03) · **Tasks:** `E##S##T##-<slug>` (D04)
- **ADRs:** `ADR-NNN-<slug>` (G01) · **Risks:** `R-NNN` (G02) · **CRs:** `CR-NNN` (G04) · **Issues:** `ISSUE-NNNN` (E02) · **Incidents:** `INC-NNN` (H01)
- **Modules / entities:** snake_case, project prefix **`lms_*`** (+ `open_switcher`) — files `<module_id>.md` / `<entity_id>.md`
- **Pages:** `P###-<slug>` (C11/pages) · **Blocks:** `<block_id>.md` (C11/blocks) · **Usecases:** `UC-WF-NNN-NNN-<slug>`
- **Acceptance criteria:** `AC-NNN` (B05) · **Test case:** `TC-NNN` (E03/E04)
- **Service contracts:** `<from>-to-<to>.md` (S02) · **Feature flags:** `FF_UPPER_SNAKE` (H05)
- Numeric ID là stable contract: không bao giờ đánh số lại để lấp khoảng trống; các ID đã gỡ bỏ vẫn được giữ chỗ (reserve).

## Editorial usability rule
Mọi bề mặt nội dung hiển thị do business sở hữu (course text, lesson body, certificate template, notification copy, FAQ, blog, homepage skin content) đều phải editable bởi một non-developer thông qua management UI — **không được hardcode editorial content** trong PHP/Twig/JS/CSS. Một story render ra content nhưng không trả lời được câu hỏi *"editor thay đổi cái này ở đâu và làm sao để verify nó xuất hiện?"* thì **chưa** implementation-ready. Được enforce tại `C04`, `C11`, `D03/D04`, G4, và CP4.
