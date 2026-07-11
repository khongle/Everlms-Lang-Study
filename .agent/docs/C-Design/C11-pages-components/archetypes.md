# C11 Page Archetypes

Registry của các **page archetype** dùng chung cho 51 trang C11. Mỗi giá trị `Archetype` trong Page Index
([_index.md](_index.md)) và mỗi frontmatter `prototype_type` phải khớp đúng một dòng `col-1` ở đây. Cột `Behavior`
là machine hook (gate key off cột này, không key off tên archetype): các trang `behavior: list` bắt buộc có section
`## Listing behavior`.

## Archetype registry

| Archetype | Behavior | Purpose | Canonical layout | C08 wireframe | Pages |
| --- | --- | --- | --- | --- | --- |
| marketing | static | Trang marketing/nội dung công khai | hero + sections + CTA band + footer | [public.md](../C08-wireframes/public.md) | P001, P006, P007, P010, P011 |
| listing | list | Danh sách entity lọc được (catalog/blog/forum/learner list) | filter/toolbar + card|table grid + pager | [public.md](../C08-wireframes/public.md) | P002, P004, P005, P008, P012, P013, P018, P022, P023, P024, P025 |
| detail | detail | Trang xem một entity | header + body + sidebar facts | [public.md](../C08-wireframes/public.md) | P003, P009, P014, P021, P026 |
| player | static | Trình phát bài học/quiz (business rail cho phép) | stage + rail + tabs | [account.md](../C08-wireframes/account.md) | P019, P020 |
| dashboard | dashboard | KPI band + widget grid | content_top KPIs + card grid | [account.md](../C08-wireframes/account.md) | P017, P028, P031 |
| form | form | Tạo/sửa entity hoặc checkout | field groups + actions bar | [account.md](../C08-wireframes/account.md) | P029, P030, P033, P040, P042, P044 |
| workspace-list | list | Bảng quản trị của staff (workspace) | pagehead + segmented + tablecard + pager | [workspace.md](../C08-wireframes/workspace.md) | P032, P034, P035, P036, P037, P038, P039, P041, P043 |
| report | dashboard | Báo cáo/analytics (KPI + chart) | date range + KPIs + charts + table | [workspace.md](../C08-wireframes/workspace.md) | P045, P049 |
| config | form | Trang settings admin (system settings form) | top tabs + form panels + save bar | [admin.md](../C08-wireframes/admin.md) | P046, P047, P048, P050 |
| auth-util | static | Trang auth/tiện ích (login, verify, notifications, switch-user) | centered card / list | [public.md](../C08-wireframes/public.md) | P015, P016, P027, P051 |

<!-- Behavior enum: list|detail|form|dashboard|static (một giá trị/row). Gate 6 list-page checks + yêu cầu
`## Listing behavior` fire trên các row `behavior: list`. Tên archetype là project vocabulary tự do. -->

## Ownership contract

| Concern | Owner | Rule |
| --- | --- | --- |
| Header CTA | local actions của owner module (main content) — trên workspace là `.pagehead__actions` | một primary CTA, canh phải; label pattern "+ New <entity>" trên mọi trang list |
| Filter bar | `.filter`/`.segmented`/`.toolbar` block trong content_top (không phải nav) | cùng operator + URL persistence trên mọi trang list; KHÔNG dùng sidebar nav |
| Breadcrumbs | system breadcrumb block ở header (`.crumb`) | Home > Area > Page; không hand-build từng trang |
| Primary navigation | injected shell (front main menu / backend main menu + My Account mega menu) | không sidebar menu, trừ business rail course/lesson (P003/P019/P020) |
