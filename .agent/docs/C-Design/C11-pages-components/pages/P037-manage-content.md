---
page: Manage content
drupal_concept: View list + entity forms
mode: as-built
owner_module: lms_course
route_group: manage
route_name: lms_course.workspace_content
prototype_type: workspace-list
adequacy: PASS
---

# C11 Page - Manage content

## Purpose
Quản trị nội dung tổng quát tại `/workspace/content/{content_type}` (workspace-list): FAQ, pages, nodes, thao tác hàng loạt (bulk ops).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/content/{content_type}` (add, `{node}/edit/view`, bulk; FAQs `/workspace/content/faqs/*`) |
| Route name | `lms_course.workspace_content` |
| Primitive | typed list + entity forms (controller-based) |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | content_editor/manager |
| Cache | tags: per content type |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Content rows | nodes / `lms_faq` by type | manage content | list tag | add → row |

## Page States
Normal: list · Empty: none → add · Access denied: non-editor → 403 · Validation error: required → errors · Loading: paged.

## Listing behavior
| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| n/a — prototype không gắn link/nút sort trên cột nào (`<th>Title/Type/Status/Updated/Author</th>` chỉ là text tĩnh, không có `data-sort`); thứ tự dòng do View trả về theo từng tab | không tường minh trong prototype — dữ liệu mẫu tab Pages không theo đúng thứ tự cột "Updated" (2 days ago → 1 week ago → 3 weeks ago → Yesterday → 2 months ago → 4 hours ago), tức mock chỉ minh hoạ nội dung cột chứ không phản ánh sort thật của View | n/a — trang không có `nav.pager` (khác các trang workspace-list cùng thư mục có pagination như P039-manage-ratings, P041-issued-certificates, P043-manage-blog); mỗi tab hiển thị trọn bộ dòng mẫu (Pages/FAQ/Taxonomy/Glossary đều 6 dòng), không có "load more" | equals(content_type) qua 4 tab `.tabs` (Pages/FAQ/Taxonomy/Glossary — mỗi tab một `.tabpane`/View riêng); contains(title) qua ô tìm kiếm `.nav__search input[type=search]` placeholder "Search pages…" — CHỈ có ở tab Pages, ba tab FAQ/Taxonomy/Glossary không có ô tìm kiếm nào trong toolbar | url.query_args (filter chia sẻ/bookmark được) | không có markup empty-state trong prototype này (cả 4 tab đều render sẵn dữ liệu mẫu); theo `## Page States` ("Empty: none → add") hành vi kỳ vọng là ẩn `.tablecard` và hiển thị CTA add tương ứng của tab đang mở (vd "New content" ở page header, "New FAQ entry", "New term") — chưa có chuỗi copy empty-state cụ thể để trích dẫn verbatim |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Page intro (count summary) | "42 pages · 28 FAQ entries · 11 taxonomy terms · 64 glossary terms" | the module (workspace content controller/template) |
| Primary CTA (page header) | "New content" | the module (`.pagehead__actions` template) |
| Secondary CTA (page header) | "Content tools" | the module (`.pagehead__actions` template, link → P042) |
| Tab labels | "Pages" / "FAQ" / "Taxonomy" / "Glossary" | the module (`.tabs` markup, `data-tab`) |
| Pages tab search | placeholder "Search pages…", aria-label "Search pages" | the module (Pages `.toolbar` search input) |
| FAQ tab CTA | "New FAQ entry" | the module (FAQ `.toolbar` — route `/workspace/content/faqs/*`) |
| Taxonomy tab CTA | "New term" | the module (Taxonomy `.toolbar`) |
| Glossary tab link (không có CTA "add") | "View public glossary" | the module (Glossary `.toolbar`, trỏ ra P012 public glossary thay vì form thêm mới) |
| Status badges | "Published" / "Draft" / "In review" | the module (`.status--success` / `.status--warning.status--dot` / `.status--info.status--dot`) |
| Row actions (icon button) | aria-label "Edit" / aria-label "View" | the module template/JS (`.rowbtns a`) |
| Validation error | không có trong prototype này (P037 không render form edit, chỉ link ra `{node}/edit`) | theo `## Page States` ("Validation error: required → errors") — copy thuộc form edit từng entity, vd C04-entities/the module.md ## Editorial/data management (FAQ) hoặc form edit node/taxonomy core (Pages/Taxonomy) |
| Success/confirm dialog (vd xoá content) | không có markup confirm-delete/bulk-op trong prototype (dù `## Purpose` nhắc "bulk ops") | chưa xác định trong prototype — cần bổ sung khi hiện thực hoá bulk actions trong the module |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px (`@media(max-width:575.98px)`): `.container` = 100% (không max-width); `.pagehead` (flex, `flex-wrap:wrap`) xếp tiêu đề/mô tả lên trên, 2 nút "Content tools"/"New content" xuống hàng dưới; `.tabs` (`overflow-x:auto`) cuộn ngang nếu 4 tab không đủ chỗ; `.nav__search` bị ẩn hoàn toàn (`display:none`, rule dùng chung với ô tìm kiếm header) nên ô "Search pages…" ở tab Pages KHÔNG hiển thị; `.toolbar` trong mỗi `.tablecard` (`flex-wrap:wrap`) xếp đoạn mô tả lên trên, CTA (nếu có) xuống dưới; bảng `.wtable` cuộn ngang trong `.wtable__wrap` thay vì chuyển thành dạng card. |
| tablet | 768–991px (nằm trong `@media(max-width:991.98px)`, không khớp `max-width:767.98px`): `.container` giới hạn theo `--container-md`; `.nav__search` vẫn bị ẩn (ngưỡng ẩn là ≤991.98px) nên tab Pages vẫn không tìm kiếm được; `.tabs`/`.pagehead`/`.toolbar` hành vi wrap giống mobile nhưng thường đủ chỗ hơn; `.wtable` vẫn có thể cần cuộn ngang tuỳ độ rộng 5 cột Title/Type/Status/Updated/Author. |
| desktop | 992–1199px (`@media(max-width:1199.98px)`, không khớp 991.98px): `.container` giới hạn theo `--container-lg`; `.nav__search` hiển thị trở lại (ngưỡng ẩn chỉ ≤991.98px) nên ô "Search pages…" dùng được; `.pagehead__t` và `.pagehead__actions` đủ chỗ nằm 1 hàng; 4 tab `.tabs` nằm gọn 1 hàng không cần cuộn; bảng `.wtable` hiển thị đủ 6 cột không cần cuộn ngang trong đa số trường hợp. |
| wide | ≥1200px (`@media(max-width:1399.98px)` áp `--container-xl`, vượt mốc đó `.container` giữ `--maxw` cố định): layout giống desktop, chỉ rộng thêm khoảng trắng hai bên container; `.tablecard`/`.wtable` không kéo giãn thêm cột, giữ độ rộng đọc dễ theo dõi. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage content | done | FAQ + node types |
