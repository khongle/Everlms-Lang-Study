---
page: Glossary
drupal_concept: View page
mode: as-built
owner_module: lms_course
route_group: public
route_name: lms_course.glossary
prototype_type: listing
adequacy: PASS
standalone: trang tham khảo thuật ngữ tĩnh, không phải một bước của workflow giao dịch
---

# C11 Page - Glossary

## Purpose
Bảng thuật ngữ (glossary) tại `/glossary` (listing): định nghĩa A–Z (`node.type` glossary + `field_synonyms`).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/glossary` |
| Route name | `lms_course.glossary` |
| Primitive | View (glossary view exists) |
| Access | anonymous |
| Cache | tags: node list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Glossary terms | `node.type` glossary (+`field_synonyms`) | content_editor | node list | add term → entry |

## Page States
Normal: A–Z list · Empty: none · Access denied: n/a · Validation error: n/a · Loading: paged.

## Listing behavior
| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| Term A–Z (nhóm theo chữ cái đầu, `<h2 id="letter-{X}">`) | Term A–Z | Toàn bộ danh sách trên 1 trang, nhóm theo 13 chữ cái có thuật ngữ (A, C, E, G, I, L, M, O, P, Q, R, S, X — tổng 18 thuật ngữ trong prototype); không có `.pager` trên trang này | contains(term) qua ô tìm kiếm `#glossary-search` (`name="q"`); jump-to-letter qua neo `href="#letter-{X}"` (điều hướng nội trang, không phải filter server-side) | url.query_args (filter/tìm kiếm chia sẻ được qua URL, bookmark được) | Chưa có empty-state dựng sẵn trong prototype cho tìm kiếm không ra kết quả — cần bổ sung copy dạng "No terms found." + CTA xoá từ khoá tìm kiếm |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Search placeholder | "Search a term, e.g. SCORM" | View exposed filter `#glossary-search`, thuộc tính placeholder |
| Search label (ẩn, chỉ dành cho screen reader) | "Search glossary" | `<label for="glossary-search">` class `.hidden` |
| Primary CTA (submit tìm kiếm) | "Search" | Form API `#value` của nút submit trong khối tìm kiếm |
| Jump-to-letter — chữ cái không có thuật ngữ nào | Chip vô hiệu hoá (`opacity:.35;pointer-events:none`), ví dụ chip "B", "D", "F" | Markup của View — điều kiện hiển thị chip theo nhóm chữ cái có kết quả |
| Empty result (tìm kiếm không khớp thuật ngữ nào) | Chưa có copy dựng sẵn trong prototype — cần bổ sung, ví dụ: "No terms found." | View empty-text config (`views.view.glossary`) |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px: `.container` co về `max-width:100%`; form tìm kiếm `.form--inline` (`display:flex;flex-wrap:wrap`, input `flex:1 1 240px`) cho ô nhập và nút "Search" xuống dòng khi không đủ chỗ; nav A–Z (`.chip-row{display:flex;flex-wrap:wrap}`) xuống nhiều hàng; bảng thuật ngữ `.dtable` rộng 100% khung nhìn, cột "Term" giữ 34% (`td:first-child{width:34%}`) nên phần định nghĩa bị bó hẹp. |
| tablet | 768–991px: `.container` giới hạn `max-width:var(--container-md)` (720px, kích hoạt ở ≤991.98px); `.chip-row` A–Z vẫn wrap tự nhiên trên 1–2 hàng tuỳ độ rộng; `.container--narrow` (920px) chưa phải giới hạn chủ đạo vì rule `.container` theo breakpoint (cùng độ đặc trưng, khai báo sau trong CSS) ghi đè. |
| desktop | 992–1199px: `.container` giới hạn `max-width:var(--container-lg)` (960px, ≤1199.98px); A–Z chip-row đủ chỗ hiển thị gần như trên 1 hàng; khối thuật ngữ dưới `.container--narrow` tiệm cận giới hạn 920px của chính nó. |
| wide | ≥1200px: `.container` đạt `max-width:var(--container-xl)` (1140px, ≤1399.98px) hoặc mở rộng tới `--maxw` (1280px) khi >1399.98px; khối danh sách thuật ngữ bị giới hạn thêm bởi `.container--narrow{max-width:920px}` để giữ dòng đọc ngắn, dễ đọc; A–Z chip-row hiển thị đầy đủ trên 1 hàng, căn giữa. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Glossary | done | core `views.view.glossary` |
