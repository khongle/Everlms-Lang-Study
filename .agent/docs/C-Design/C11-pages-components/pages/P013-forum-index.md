---
page: Forum index
drupal_concept: View page / controller list
mode: as-built
owner_module: lms_forum
route_group: public
route_name: lms_forum.index
prototype_type: listing
adequacy: PASS
---

# C11 Page - Forum index

## Purpose
Forum cộng đồng tại `/forum`: liệt kê topic theo category kèm bộ đếm tương tác (engagement counter); là lối vào topic detail và new-topic.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/forum` (new: `/forum/new`) |
| Route name | `lms_forum.index` |
| Drupal page primitive | controller/View list |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Owner module | lms_forum |
| Access | anonymous read; authenticated post |
| Cache | tags: `lms_forum_topic_list`; contexts: `user.roles`, `url.query_args` |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| course_card (reused) | Category filter | sidebar_first | filter by `forum_category` |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| List | topics sorted by last-reply; pinned first |
| Counters | replies, views, resolved badge |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Topic rows | `lms_forum_topic` | post: authenticated | topic list tag | new topic → row |
| Categories | `forum_category` taxonomy | content_editor | term cache | add category → filter |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | — | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | topic list |
| Empty | "no topics yet" + new-topic CTA |
| Access denied | post hidden for anonymous |
| Validation error | new-topic form validation |
| Loading | paged |

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| Không có control sort riêng trong prototype; khối "Recent discussions" chỉ lọc theo trạng thái qua chip (All / Unanswered / Resolved), bảng Boards giữ thứ tự cố định theo category | last reply mới nhất trước; pinned/resolved lên đầu (theo Main Content Contract) | 4 topic hiển thị trong khối "Recent discussions" của prototype (không có control phân trang — 1 trang tĩnh); bảng Boards liệt kê 4 board | equals(status: All\|Unanswered\|Resolved) qua chip filter; equals(category) qua link board trong bảng Boards | url.query_args (filter chia sẻ/bookmark được) | "no topics yet" + CTA "New topic" |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Empty | "no topics yet" | View empty-text config (the module View, empty behavior) |
| CTA — tạo topic mới | "New topic" | module string / local action label (the module.links.action.yml`) |
| Filter — trạng thái | "All" · "Unanswered" · "Resolved" | View exposed filter labels (the module View UI) |
| Notice — quyền truy cập anonymous | "Anyone can read the forum. Log in to post a new topic or reply." | the module / block template (anonymous notice) |
| Validation error (new-topic form) | chưa render trong prototype (form `/forum/new` chưa dựng) | C04 the module.md ## Validation rules |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | `<576px`: `.container` co về 100%; `.toolbar` ở hero (tiêu đề + nút "New topic") xuống dòng theo `flex-wrap:wrap`; bảng `.wtable` (Boards) không có wrapper cuộn ngang riêng nên 4 cột (Board/Topics/Posts/Last activity) bị ép hẹp, cột "Last activity" (avatar+tên+thời gian) dễ vỡ dòng; card trong "Recent discussions" vẫn giữ avatar+nội dung+badge trên 1 hàng (flex, không wrap) nên phần text co lại; `.chip-row` (All/Unanswered/Resolved) xuống dòng khi không đủ chỗ |
| tablet | `768–991px`: `.container` giới hạn `--container-md` (720px); cả 2 khối (Boards table + Recent discussions) vẫn xếp dọc 1 cột như ở các breakpoint khác — prototype không dựng sidebar `sidebar_first` cho "Category filter" dù spec Placed Blocks có khai báo |
| desktop | `992–1199px`: `.container` giới hạn `--container-lg` (960px); bảng Boards hiển thị đủ 4 cột, `.toolbar` hero giữ 1 hàng (tiêu đề trái, nút "New topic" phải) |
| wide | `≥1200px`: `.container` mở tới `--container-xl` (1140px) trong khoảng 1200–1399px; từ 1400px trở lên không còn media query nào override nên dùng mặc định `--container-xxl` (1280px); bố cục không thêm cột, chỉ giãn khoảng trắng hai bên |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Forum list + post | done | moderation flags supported |
