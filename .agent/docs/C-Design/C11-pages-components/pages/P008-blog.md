---
page: Blog / News
drupal_concept: View page
mode: as-built
owner_module: lms_blog
route_group: public
route_name: lms_blog.news
prototype_type: listing
adequacy: PASS
standalone: trang nội dung blog công khai (F-016), không được mô hình hoá thành một trong 10 workflow B04
---

# C11 Page - Blog / News

## Purpose
Danh sách article tại `/news` (listing archetype).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/news` |
| Route name | `lms_blog.news` |
| Primitive | controller/View list of `lms_article` |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | anonymous |
| Cache | tags: article list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Article cards | `lms_article` (published) | content_editor | article list | publish → card |
| Tag filter | `blog_tags` taxonomy | content_editor | term cache | add tag → filter |

## Page States
Normal: article grid · Empty: none · Access denied: n/a · Validation error: n/a · Loading: paged.

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| published date (không có control sort lộ ra cho người dùng — blog `/news` chỉ hiển thị theo ngày đăng, không có dropdown sort như course catalog) | published date DESC (newest first — thứ tự card trong prototype: Jul 8 → Jul 5 → Jul 2 → Jun 28 → Jun 24 → Jun 19 → Jun 15 → Jun 10 → Jun 6 → Jun 1, 2026) | 9 bài/trang trong lưới `.grid--3` (3×3), cộng thêm 1 bài nổi bật (featured post) hiển thị riêng ở đầu trang 1 và không tính vào số trang của pager | equals(tag) qua chip `blog_tags`: "All posts" / "Study tips" / "Web development" / "AI & machine learning" / "Career advice" / "Product updates" — chọn đúng 1 chip tại một thời điểm; trang này không có ô tìm kiếm từ khoá | url.query_args (tag filter đang chọn + số trang hiện tại phải giữ được trong URL để share/bookmark) | Prototype không dựng UI cho trạng thái 0 kết quả (`## Page States` ở trên đã ghi "Empty: none" — không có demo case tag/filter không khớp bài viết nào). Copy fallback thật chưa được thiết kế trong prototype nên không bịa ở đây; cần bổ sung khi hiện thực View (vd tái dùng pattern "no results" chung của site nếu có) |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA — bài nổi bật (featured post) | "Read the full article" | module string the module / template card bài nổi bật trên `P008-blog` |
| CTA — mỗi card trong lưới | "Read more" | module string the module / template `post-link` trên card |
| Category filter — chip mặc định đang chọn | "All posts" | View exposed filter label / term name trong vocabulary blog tags |
| Category filter — các chip còn lại | "Study tips" / "Web development" / "AI & machine learning" / "Career advice" / "Product updates" | term name trong vocabulary blog tags |
| Pagination — nút trước (disabled ở trang 1) | "«" | Views pager config (`.pager .is-disabled`) |
| Pagination — nút sau | "»" | Views pager config |
| Hero eyebrow | "EverLMS blog" | module string / page hero config |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (≤639.98px) | Lưới bài viết `.grid--3` về 1 cột; khối bài nổi bật `.split` (ảnh + nội dung) đã xếp dọc 1 cột từ breakpoint tablet trở xuống; hàng chip lọc theo tag `.chip-row` tự xuống dòng (`flex-wrap:wrap`, không ẩn/gập) vì không có sidebar/aside riêng cho category trên trang này; `.pager` cũng wrap khi không đủ chỗ ngang; `.container` co về 100% width dưới 575.98px |
| tablet (768–991.98px) | `.grid--3` giảm còn 2 cột (`grid-template-columns:repeat(2,1fr)`); `.split` (ảnh trái, nội dung phải của bài nổi bật) chuyển về 1 cột full-width tại ≤991.98px; `.container` giới hạn theo `--container-md` |
| desktop (992–1199.98px) | `.grid--3` đủ 3 cột như thiết kế gốc; `.split` giữ 2 cột (ảnh + nội dung) song song; `.container` giới hạn theo `--container-lg` |
| wide (≥1200px) | Layout đầy đủ: `.container` theo `--container-xl` (≤1399.98px) hoặc max-width mặc định ở trên; hero `.pagehero` giãn padding-block theo `clamp(2.4rem,5vw,4rem)`; lưới 3 cột và khối bài nổi bật 2 cột giữ nguyên như prototype |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Blog listing | done | — |
