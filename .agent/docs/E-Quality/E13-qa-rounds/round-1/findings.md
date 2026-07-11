# E13 round-1 — findings (A09 Runtime Acceptance evidence)

**Round verdict:** PASS

Machine-readable summary of the round-1 runtime QA pass (initial full crawl of all 10 C12 workflows), feeding A06 Gate 8.C. Nguồn: `_screens/` (10 thư mục workflow, ảnh trước/sau hành động), `_tooling/` (Playwright capture script mỗi WF), `issues/done/` (29 issue).

## Method
- **Threshold:** mọi trang render không lỗi 5xx (pass); **Masks:** bỏ qua timestamp/random-seed động khi so parity vs C10.
- **Crawl:** real-session **crawl** bằng Playwright headless (`_tooling/capture_wf00N_*.py`, 10 script — một script/workflow) chạy against site DDEV thật (không phải mock/tĩnh); mỗi bước đều `page.goto()` + tương tác form/CTA thật rồi chụp before/after.
- **Role provisioning == C02:** mỗi workflow crawl theo đúng persona role trong registry [C02](../../../C-Design/C02-user-roles/_index.md) — anonymous (forum/catalog/verify trước login), student/learner (enroll/quiz/certificate/checkout), instructor (course authoring, AI builder), manager (order review/approval). Không có role ngoài `user.role.*.yml`.
- **Navigation reachability == C07/C11:** 10 thư mục `_screens/00N-<workflow-slug>/` bám theo route/journey thật của [C07 sitemap](../../../C-Design/C07-sitemap/_index.md) và trang trong [C11](../../../C-Design/C11-pages-components/_index.md); chi tiết per-page verdict (đối chiếu 51 trang C11 với prototype C10) nằm ở [round-2/findings.md](../round-2/findings.md#per-page-crawl-parity-verdict-c11-registry-51-trang) — round-1 crawl cùng phạm vi trang, round-2 verify lại + đối chiếu bổ sung với C10.
- **Visual parity vs C10:** ảnh round-1 là baseline "before" cho phần lớn issue đã fix; so khớp bố cục/label với [C10 prototypes](../../../C-Design/C10-prototypes/html/_screens/) làm căn cứ để mở issue UI (vd label sai, modal bị che, status message hiển thị dạng debug).
- **Zero 5xx:** không có 5xx được ghi nhận trong 29 issue của round-1 (issue là UX/logic/data — xem danh sách dưới), tức crawl không gặp lỗi server chặn luồng.

## 10 workflow crawl (C12 WF-001..010)
`_screens/001-enrollment-and-learning/` .. `_screens/010-ai-assisted-content-generation/` — mỗi thư mục là một **crawl x role** hoàn chỉnh (multi-step, có ảnh trước/sau mỗi hành động).

## Issues found → fixed → retested (29/29 → `issues/done/`, 0 open, 0 resolved-only còn treo)
| Issue | Tóm tắt |
| ----- | ------- |
| WF-001-001 | duplicate course enrollments |
| WF-001-002 | empty status message alert strip |
| WF-001-003 | missing SCORM package renders 404 iframe |
| WF-002-001 | pending quiz result shown as passed |
| WF-003-001 | passed quiz does not issue certificate without course link |
| WF-003-002 | certificate delivery UI and PDF layout rough edges |
| WF-004-001 | bank transfer instructions contradict manual review |
| WF-004-002 | processing orders not filterable or highlighted |
| WF-004-003 | manager orders table truncates critical columns |
| WF-005-001 | membership duplicates and stale active records |
| WF-005-002 | membership empty state and checkout summary copy |
| WF-006-001 | workspace course create is static prototype |
| WF-006-002 | instructor role cannot author courses |
| WF-006-003 | nested lesson inline create breaks |
| WF-006-004 | nested lesson type fields do not collapse |
| WF-006-005 | authoring modal is clipped by sidebar |
| WF-007-001 | status messages render as plain text |
| WF-007-002 | mobile registration API routes disabled |
| WF-007-003 | web registration confirmation message too subtle |
| WF-008-001 | topic detail renders debug status message |
| WF-008-002 | anonymous forum index stays stale after new topic |
| WF-008-003 | forum authoring UI rough edges |
| WF-009-001 | checkout missing coupon redemption |
| WF-009-002 | coupon checkout feedback and contrast |
| WF-010-001 | AI builder access provider and human review broken |
| WF-010-002 | AI builder invalid course hierarchy and partial save risk |
| WF-010-003 | AI provider contract and queue routing inconsistent |
| WF-010-004 | generated draft list actions invisible and delete is placeholder |
| WF-010-005 | AI builder error message uses success styling |

Tất cả 29 issue trên đã fix và retest trong round-1 (`issues/done/`); các module/route liên quan tiếp tục được **round-2 re-verify** (xem [round-2 findings](../round-2/findings.md)) trước khi coi là ổn định.

## Links
[E13 index](../_index.md) · [round-2 findings](../round-2/findings.md) · [C02 roles](../../../C-Design/C02-user-roles/_index.md) · [C07 sitemap](../../../C-Design/C07-sitemap/_index.md) · [C10 prototypes](../../../C-Design/C10-prototypes/html/_screens/) · [C12 workflows](../../../C-Design/C12-workflows/)

> Threshold: 0 lỗi 5xx; mask: timestamp/seed động khi so parity.
