---
page: About
drupal_concept: controller + editor blocks
mode: as-built
owner_module: lms_course
route_group: public
route_name: lms_course.about
prototype_type: marketing
adequacy: PASS
standalone: trang tĩnh giới thiệu công ty/sản phẩm, không phải một bước của workflow giao dịch
---

# C11 Page - About

## Purpose
Trang About marketing tại `/about` (marketing): các content block do editor quản lý.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/about` |
| Route name | `lms_course.about` |
| Primitive | controller + placed content blocks / node |
| Access | anonymous |
| Cache | tags: block content |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| About copy | content block / page node (`node.type` page) | content_editor | block/node tag | edit → updates |

## Page States
Normal: content · Empty: n/a · Access denied: n/a · Validation error: n/a · Loading: n/a.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA chính (băng CTA cuối trang) | "Browse courses" | content block (block content) — editable qua Drupal content editor |
| CTA phụ (băng CTA cuối trang) | "Contact us" | content block (block content) — editable qua Drupal content editor |
| Tiêu đề hero (mission statement) | "Learning should never be limited by where you start" | content block field (hero component) — editable qua Drupal content editor |
| Số liệu chứng minh (stats component) | "92% Completion rate" | content block field (Stats component) — editable qua Drupal content editor |
| Nhãn vai trò lãnh đạo (Leadership component) | "CEO & Co-founder" | content block field (Leadership team component) — editable qua Drupal content editor |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | `.container` rộng 100% viewport (≤575.98px). `.split` (hero "Our mission" + ảnh team) xếp 1 cột — ảnh xuống dưới text (ngưỡng `≤991.98px`). `.stats` (4 chỉ số) xếp 2 cột (ngưỡng `≤767.98px`). `.grid--3` (Values) và `.grid--4` (Leadership) xếp 1 cột do ngưỡng `≤639.98px` ghi đè ngưỡng 2 cột ở `991.98px`. `.timeline` (Our story) vẫn là danh sách dọc 1 cột như mặc định. |
| tablet | `.split` vẫn xếp 1 cột (chưa vượt ngưỡng `991.98px`). `.stats` trở lại 4 cột vì > `767.98px` không còn khớp rule 2 cột. `.grid--3`/`.grid--4` xếp 2 cột (khớp rule `≤991.98px`, không còn khớp rule 1 cột `≤639.98px`). |
| desktop | `.split` trở lại 2 cột (text trái, ảnh phải) vì vượt ngưỡng `991.98px`. `.stats` giữ 4 cột. `.grid--3` (Values) trở lại 3 cột, `.grid--4` (Leadership) trở lại 4 cột — đều là giá trị mặc định. |
| wide | Giữ nguyên bố cục 2 cột cho `.split`, 4 cột cho `.stats`, 3/4 cột cho Values/Leadership; `.container` mở rộng thêm (`--container-lg`/`--container-xl` theo mốc 1199.98/1399.98px), khoảng đệm 2 bên tăng theo `clamp()`. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| About page | done | editor-managed |
