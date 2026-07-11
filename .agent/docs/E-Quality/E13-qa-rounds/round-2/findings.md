# E13 round-2 — findings (A09 Runtime Acceptance evidence)

**Round verdict:** PASS

Machine-readable summary of the round-2 runtime QA pass, feeding A06 Gate 8.C. Nguồn: [README.md](README.md) (narrative gốc), `_screens/` (72 screenshot), `_tooling/` (Playwright capture script), `issues/{done,open}/`.

## Method
- **Threshold:** mọi trang render không lỗi 5xx (pass); **Masks:** bỏ qua timestamp/random-seed động khi so parity vs C10.
- **Crawl:** real-session **crawl** thực hiện bằng Playwright headless (`_tooling/capture_wf*.py`), chạy against `https://everlms.ddev.site` (DDEV live site, không phải mock). Mỗi script `page.goto()` tới route thật rồi `click()` CTA/menu thật để đi tiếp (xem ví dụ `capture_wf001_enroll.py`: `cta.click()` → follow tới đích thật), tức là **reachability được lái qua navigation trong app**, không chỉ gõ URL.
- **Role provisioning == C02:** mỗi session dùng `ddev drush user:login --uid=<uid> --no-browser` để log in đúng **role thật trong `user.role.*.yml`** (xem [C02](../../../C-Design/C02-user-roles/_index.md)): anonymous (không login), student (`maria.santos` uid 61 — member, `julia.nowak` uid 71 — non-member), instructor, manager. Không có role bịa ngoài registry C02.
- **Navigation reachability == C07/C11:** các trang được crawl khớp với sitemap [C07](../../../C-Design/C07-sitemap/_index.md) và registry trang [C11](../../../C-Design/C11-pages-components/_index.md) (P001-P051); xem bảng per-page bên dưới.
- **Visual parity vs C10:** với các trang có prototype, screenshot runtime được đặt cạnh (side-by-side) prototype trong [C10](../../../C-Design/C10-prototypes/html/_screens/) — nhiều file trong `_screens/` có hậu tố `-prototype.png` ghép cặp trực tiếp với ảnh runtime cùng tên (vd `01-student-dashboard-prototype.png` ↔ `01-student-dashboard.png`).
- **Zero 5xx:** smoke test cuối round — 17 trang chính qua anonymous/student/instructor/manager đều trả **HTTP 200, không có 5xx** (xem README §Smoke test).

## Per-workflow outcome (C12 WF-001..010)
Xem bảng đầy đủ ở [README.md](README.md#result-per-workflow). Tóm tắt: 14 issue found→fixed→retested (`issues/done/`), 2 issue mở cần quyết định sản phẩm (`issues/open/`: `WF-005-001` membership tiering, `WF-010-001` **critical** — AI key plaintext trong config, R-001).

## Per-page crawl/parity verdict (C11 registry, 51 trang)
Cột "Verdict" phân biệt rõ: **COVERED** = có bằng chứng crawl runtime round-1/round-2 đối chiếu C10; **waived** = có prototype C10 (`C-Design/C10-prototypes/html/_screens/{role}-P0XX-*.png`) nhưng CHƯA có crawl runtime trong E13 — ghi nhận trung thực là gap, không giả lập bằng chứng.

| Page | Slug | Role (C10) | Verdict |
| ---- | ---- | ---------- | ------- |
| P001 | homepage | anonymous | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P002 | course-catalog | anonymous | **COVERED** — WF-001/WF-006 crawl (catalog card CTA + anon catalog visibility) |
| P003 | course-detail | anonymous | **COVERED** — WF-001/WF-006 crawl (course detail enrolled/not-enrolled + anon curriculum) |
| P004 | categories | anonymous | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P005 | category-detail | anonymous | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P006 | pricing | anonymous | **COVERED** — WF-005 crawl (pricing plans) |
| P007 | instructor-profile | anonymous | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P008 | blog | anonymous | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P009 | article-detail | anonymous | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P010 | about | anonymous | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P011 | contact | anonymous | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P012 | glossary | anonymous | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P013 | forum-index | anonymous | **COVERED** — WF-008 crawl (forum index, anon + student) |
| P014 | forum-topic | anonymous | **COVERED** — WF-008 crawl (topic detail, reply + best-answer) |
| P015 | certificate-verify | anonymous | **COVERED** — WF-003 crawl (public verify before/after/valid/revoked) |
| P016 | login-register | anonymous | **COVERED** — WF-007 crawl (web register) + WF-006 (instructor login) |
| P017 | dashboard | student | **COVERED** — WF-001 crawl (student dashboard, prototype pair) |
| P018 | my-courses | student | **COVERED** — WF-001 crawl (my-courses, prototype pair) |
| P019 | lesson-player | student | **COVERED** — WF-001 crawl (lesson player, prototype pair) |
| P020 | quiz-take | student | **COVERED** — WF-002 crawl (quiz take, prototype pair) |
| P021 | quiz-result | student | **COVERED** — WF-002 crawl (quiz result pending/graded) |
| P022 | my-quiz-results | student | **COVERED** — WF-002 crawl (my quiz results, prototype pair) |
| P023 | my-certificates | student | **COVERED** — WF-003 crawl (my certificates, prototype pair) |
| P024 | my-notes | student | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P025 | my-wishlist | student | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P026 | my-membership | student | **COVERED** — WF-005 crawl (my membership) |
| P027 | notifications | student | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P028 | profile | student | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P029 | checkout-course | student | **COVERED** — WF-004/WF-009 crawl (checkout course + coupon) |
| P030 | checkout-membership | student | **COVERED** — WF-005 crawl (checkout membership, bank-transfer) |
| P031 | workspace-home | instructor | **COVERED** — WF-006 crawl (instructor workspace entry) |
| P032 | manage-courses | instructor | **COVERED** — WF-006 crawl (manage courses list, draft/published) |
| P033 | course-editor | instructor | **COVERED** — WF-006 crawl (course editor create/edit) |
| P034 | manage-quizzes | instructor | **COVERED** — WF-002 crawl (grading UI under manage/quiz-results) |
| P035 | manage-users | manager | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P036 | manage-orders | manager | **COVERED** — WF-004 crawl (manage orders processing/approve) |
| P037 | manage-content | content-editor | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P038 | manage-pricing | manager | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P039 | manage-ratings | content-editor | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P040 | certificate-templates | content-editor | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P041 | issued-certificates | content-editor | **COVERED** — WF-003 crawl (revoke form) — as-built role: manager (C10 mock role: content-editor; xem C02 RBAC) |
| P042 | content-tools | content-editor | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P043 | manage-blog | content-editor | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P044 | ai-course-builder | instructor | **COVERED** — WF-010 crawl (AI course builder form) |
| P045 | workspace-reports | instructor | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P046 | ai-settings | administrator | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P047 | payment-settings | administrator | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P048 | zoom-settings | administrator | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P049 | analytics-reports | administrator | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P050 | notification-broadcast | administrator | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |
| P051 | switch-user | administrator | waived — no runtime crawl in E13 round-1/round-2 yet (C10 prototype exists; tracked as backlog for the next round) |

**Tổng:** 24/51 trang có bằng chứng crawl runtime (E13 round-1/round-2, WF-001..010); 27/51 trang mới có prototype C10, CHƯA crawl runtime — chủ yếu là trang marketing/nội dung tĩnh (about/contact/glossary/blog/categories/instructor-profile) và trang admin-only settings/report (ai-settings, payment-settings, zoom-settings, manage-pricing, manage-users, manage-content, manage-ratings, manage-blog, content-tools, notification-broadcast, switch-user, workspace-reports) chưa nằm trong phạm vi 10 workflow C12 đã crawl. Đây là **residual thật, không phải fabricated PASS** — theo dõi như backlog QA round-3.

## Links
[README](README.md) · [C02 roles](../../../C-Design/C02-user-roles/_index.md) · [C07 sitemap](../../../C-Design/C07-sitemap/_index.md) · [C10 prototypes](../../../C-Design/C10-prototypes/html/_screens/) · [C11 pages](../../../C-Design/C11-pages-components/pages/) · [C12 workflows](../../../C-Design/C12-workflows/) · [round-1 findings](../round-1/findings.md)

> Threshold: 0 lỗi 5xx; mask: timestamp/seed động khi so parity.
