---
page: Forum topic
drupal_concept: entity view + reply form
mode: as-built
owner_module: lms_forum
route_group: public
route_name: lms_forum.topic
prototype_type: detail
adequacy: PASS
---

# C11 Page - Forum topic

## Purpose
Topic kèm các reply dạng thread tại `/forum/{lms_forum_topic}` (detail); đăng reply, đánh dấu best answer, upvote.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/forum/{lms_forum_topic}` (reply `/forum/{topic}/reply`) |
| Route name | `lms_forum.topic` |
| Primitive | entity view + Form API reply |
| Access | anonymous read; authenticated reply |
| Cache | tags: `lms_forum_topic:{id}`, replies |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Topic + replies | `lms_forum_topic`/`lms_forum_reply` | authenticated post | topic tag | reply → appears |

## Page States
Normal: thread · Empty: no replies · Access denied: reply hidden anon · Validation error: empty reply → error · Loading: paged.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Empty | no replies | module string / entity template empty-state (theo Page States) |
| CTA — trả lời bài gốc | "Reply" | module string (nút trên original post, cạnh nút upvote) |
| CTA — gửi reply | "Post reply" | module string / Form API submit button (reply form) |
| Guest hint | "You are replying as a guest — log in to post under your name." | the module (ghi chú dưới reply form) |
| Trạng thái đã giải quyết | "Marked as resolved by Sarah Chen (Instructor)." | the module (resolved banner; tên + role người đánh dấu là dữ liệu động, không phải literal string) |
| Validation error (reply rỗng) | chưa render trong prototype tĩnh (form không submit thật) | C04 the module.md ## Validation rules |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | `<576px`: `.container.container--narrow` co về 100% (rule `.container{max-width:100%}` ở ≤575.98px override `.container--narrow`); `.crumb` (breadcrumb Home/Forum/React/…) xuống dòng qua `flex-wrap:wrap`; card bài gốc (`.card--pad-lg`, avatar 52px) và các card reply (avatar 44px) giữ layout avatar trái – nội dung phải trên 1 hàng (div cha không có `flex-wrap`) nên tiêu đề/nội dung co hẹp lại thay vì avatar rớt xuống dưới; khối code trong bài gốc (`<pre>`) tự cuộn ngang (`overflow-x:auto`); `.btn-row` (Upvote/Reply, "Post reply" + ghi chú guest) xuống dòng khi không đủ chỗ; `textarea` reply full-width theo `.form{display:grid}` |
| tablet | `768–991px`: `.container` giới hạn `--container-sm`/`--container-md` (540–720px tuỳ đúng ngưỡng) vì rule breakpoint của `.container` ghi đè `.container--narrow` (920px) ở dải này; toàn trang vẫn 1 cột dọc — page này không dùng layout 2 cột `.detail` (nội dung + sidebar) như trang course-detail, nên không có sidebar để ẩn/gập |
| desktop | `992–1199px`: `.container` giới hạn `--container-lg` (~960px, breakpoint chung vẫn thắng `.container--narrow`); card bài gốc, danh sách reply và form reply hiển thị đầy đủ với padding chuẩn (`.card--pad-lg` = 2rem) |
| wide | `≥1200px`: ở 1200–1399px container rộng 1140px (breakpoint `--container-xl` vẫn override); từ 1400px trở lên không còn media query nào override nên `.container--narrow` (920px) mới thực sự có hiệu lực, thu hẹp cột đọc lại đúng thiết kế "narrow" cho thread + reply |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Topic + replies | done | best-answer/upvote |
