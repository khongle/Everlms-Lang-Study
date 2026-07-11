---
page: My notes
drupal_concept: controller list + AJAX
mode: as-built
owner_module: lms_note
route_group: app
route_name: lms_note.my_notes
prototype_type: listing
adequacy: PASS
standalone: tiện ích ghi chú cá nhân (F-015), không thuộc một trong 10 workflow B04 đã đặt tên
---

# C11 Page - My notes

## Purpose
Ghi chú cá nhân tại `/my-notes` (listing); riêng tư cho từng user.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-notes` (AJAX `/api/lms/note/{save,get,delete}`) |
| Route name | `lms_note.my_notes` |
| Primitive | controller list + AJAX CRUD |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | authenticated (own only) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Notes | `lms_note` (own) | own | per-user | save note → appears; other user cannot read |

## Page States
Normal: notes · Empty: none · Access denied: others' notes hidden · Validation error: empty → ignored · Loading: AJAX.

## Listing behavior
| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| n/a — prototype không có dropdown/control sort tường minh; khi ở "All courses" các note được nhóm theo khóa học (đúng thứ tự tab `.segmented`: React & Next.js → Machine Learning → Data Analytics) rồi sắp theo `created` tăng dần trong từng khóa học (vd Jul 6 04:52 → Jul 6 09:15 → Jul 7 03:40 → Jul 7 15:02 → Jul 8 06:20) | course (nhóm theo thứ tự tab lọc), rồi created tăng dần trong nhóm — như đã render sẵn, không có control để đổi sort | n/a — prototype render toàn bộ 7 note trong một `.stack`, không có phân trang | equals(course) qua tab `.segmented` ("All courses (7)" / "React & Next.js (5)" / "Machine Learning (1)" / "Data Analytics (1)"); không có ô tìm kiếm free-text trong prototype | url.query_args (filter chia sẻ/bookmark được) | "No notes for this course yet" — "Open any lesson and use the note panel next to the video to capture a thought — it will show up here instantly, tied to the exact timestamp." CTA: "Open lesson player" |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Page intro (count) | "7 private notes across 3 courses, each linked to the lesson and timestamp where you wrote them." | the module (controller template) |
| Primary CTA (page header + empty-state action) | "Open lesson player" | the module (controller template + no-results markup) |
| Per-note action | "Jump to lesson" | the module (note item markup) |
| Edit action (icon button) | title="Edit", aria-label="Edit note" | the module JS (note action buttons) |
| Delete action (icon button) | title="Delete", aria-label="Delete note" | the module JS (note action buttons) |
| Empty (per-course filter with no notes) | "No notes for this course yet" — "Open any lesson and use the note panel next to the video to capture a thought — it will show up here instantly, tied to the exact timestamp." | the module (controller template, no-results markup) |
| Validation (empty note text on save) | (không hiển thị message — lưu bị bỏ qua âm thầm) | C04 the module.md ## Validation rules; xem `## Page States` ở trên ("Validation error: empty → ignored") |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px (`@media(max-width:575.98px)`): `.container` = 100% (không max-width); `.stack` vẫn là danh sách 1 cột dọc (không có grid nào cần sập cột) nên mỗi `.noteitem` chiếm gần trọn chiều rộng; `.pagehead` (flex, `flex-wrap:wrap`) xếp `.pagehead__t` (tiêu đề + đoạn mô tả) lên trên, nút "Open lesson player" xuống hàng dưới; `.segmented` (inline-flex, `flex-wrap:wrap`) các tab lọc khóa học tự xuống dòng khi không đủ chỗ; trong từng `.noteitem`, `.toolbar` (flex, `justify-content:space-between`, `flex-wrap:wrap`) có thể đẩy link "Jump to lesson" và cụm icon Edit/Delete (`.rowbtns`) xuống 2 hàng; `.noteitem__meta` (flex, `flex-wrap:wrap`) các mốc course/lesson/date tự xuống dòng. |
| tablet | 768–991px (rơi vào `@media(max-width:991.98px)` nhưng không khớp `max-width:767.98px`): `.container` giới hạn theo `--container-md`; `.stack`/`.noteitem` vẫn 1 cột như mobile (danh sách note không có breakpoint đổi số cột); `.pagehead` và `.segmented` vẫn có thể xuống dòng tùy độ dài do `flex-wrap:wrap`, nhưng thường đủ chỗ để nằm 1 hàng hơn mobile. |
| desktop | 992–1199px (`@media(max-width:1199.98px)`, không khớp 991.98px): `.container` giới hạn theo `--container-lg`; `.pagehead__t` và `.pagehead__actions` nằm cùng hàng (`justify-content:space-between`); các tab `.segmented` nằm gọn 1 hàng; `.noteitem` vẫn xếp dọc 1 cột theo `.stack` — danh sách note không chuyển sang nhiều cột ở desktop. |
| wide | ≥1200px (`@media(max-width:1399.98px)` áp `--container-xl`, vượt mốc đó `.container` giữ max-width cố định): layout giống desktop, chỉ rộng thêm khoảng trắng hai bên; `.noteitem` vẫn giữ 1 cột trong `.stack` để giữ độ rộng dòng đọc dễ theo dõi, không kéo giãn thành nhiều cột. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Notes | done | video-timestamp linked |
