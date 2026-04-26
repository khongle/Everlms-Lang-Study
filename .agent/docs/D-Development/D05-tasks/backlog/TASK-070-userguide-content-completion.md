# Task: Userguide Content Completion (U03 feature guides + U06 tutorials)

**ID:** TASK-070
**Unit:** — (docs: U-Userguide layer)
**Group:** — (cross-cutting)
**Priority:** P2
**Estimate:** 24h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Các files trong `U-Userguide/U03-feature-guides/` (16 files) và `U-Userguide/U06-tutorials/` (3 files) hiện là stubs — chỉ có heading và TODO placeholders. Task này viết nội dung đầy đủ cho từng file: mô tả tính năng từ góc độ user, step-by-step instructions, screenshots từ prototype HTML (nếu có), và tips/troubleshooting.

Output bao gồm cả việc build `U09-html` output via documentation generator (mkdocs hoặc tương đương) để userguide có thể serve dưới dạng static site.

## Acceptance Criteria
- [ ] 16 feature guide files trong `U03-feature-guides/` đều có nội dung đầy đủ (≥200 words mỗi file)
- [ ] 3 tutorial files trong `U06-tutorials/` đều có step-by-step nội dung với numbered steps
- [ ] Screenshot references link đến `.agent/docs/C-Design/C10-prototypes/html/` nơi prototype tồn tại
- [ ] Mỗi feature guide cover: What is it, Who is it for, How to use (steps), Tips, Common issues
- [ ] Mỗi tutorial cover: Goal, Prerequisites, Steps (numbered), Expected outcome, Next steps
- [ ] `U09-html/` directory có `mkdocs.yml` (hoặc equivalent) với nav structure
- [ ] `mkdocs build` (hoặc equivalent) tạo thành công static HTML ra `U09-html/site/`
- [ ] Internal links giữa feature guides không broken (`mkdocs build` warns về broken links)
- [ ] Ngôn ngữ: English + Vietnamese sections (bilingual per project style)

## Technical Notes
- Input stubs: `U-Userguide/U03-feature-guides/*.md` (16 files — list via `ls`)
- Input stubs: `U-Userguide/U06-tutorials/*.md` (3 files — list via `ls`)
- Doc generator: `mkdocs` với `material` theme (Python) — `requirements-docs.txt` thêm vào project root
- `mkdocs.yml` tại `U-Userguide/` root
- Output: `U-Userguide/U09-html/site/` (gitignored nếu served via CI artifact)
- Screenshots: copy/link từ `C-Design/C10-prototypes/html/` vào `U-Userguide/docs/assets/img/`
- Feature guides topics (ước tính từ D01 unit registry): Course management, Quiz taking, Cert download, Notification center, Learning paths, Badges, etc.

## Dependencies
- TASK-046 (public content pages — blog/faq/about UI done trước khi viết guide)
- TASK-048 (student portal pages — lesson/quiz UI done trước khi viết tutorial)

## Definition of Done
- [ ] 19 files (16 + 3) có full content
- [ ] `mkdocs build` exits 0, zero warnings
- [ ] `U09-html/site/index.html` exists và readable
- [ ] Spot review: 3 feature guides reviewed bởi SA cho accuracy
- [ ] `U-Userguide/README.md` có instructions để build và serve locally
