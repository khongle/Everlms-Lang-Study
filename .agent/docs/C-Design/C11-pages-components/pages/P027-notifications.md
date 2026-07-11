---
page: Notifications
drupal_concept: controller list + AJAX
mode: as-built
owner_module: lms_notification
route_group: app
route_name: lms_notification.list
prototype_type: auth-util
adequacy: PASS
---

# C11 Page - Notifications

## Purpose
Trung tâm thông báo tại `/notifications` (auth-util): danh sách, đánh dấu đã đọc, đếm số chưa đọc.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/notifications` (AJAX `/api/lms/notifications/unread-count`) |
| Route name | `lms_notification.list` |
| Primitive | controller list + AJAX count |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Notifications | `lms_notification` (own) | own | per-user | event → notification (in-app ok; email/push partial R-005) |

## Page States
Normal: list · Empty: none · Access denied: anon → login · Validation error: n/a · Loading: AJAX.

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Header subtitle (unread count) | "5 unread updates across your courses and account." | module string (dynamic count) / the module unread query |
| Bulk action button | "Mark all read" | module string / button label config |
| Filter tabs (segmented) | "All (8)" · "Unread (5)" · "Courses (6)" · "System (2)" | View/route exposed filter labels + dynamic counts |
| Notification sample — course update | "New lesson added to your course" — "\"Deployment\" is now available in Complete Modern React & Next.js Bootcamp, Section 4." | the module message template per event type |
| Notification sample — quiz graded | "Your quiz was graded" — "\"Hooks in Depth\" — you scored 92%. Great job, keep it up!" | the module message template per event type |
| Notification sample — certificate ready (with CTA) | "Certificate ready" — "Your certificate for Python for Everybody: Zero to Hero is ready to download." · CTA: "View certificate" | the module message template + CTA link label |
| Notification sample — payment failed (with CTA) | "Your Pro plan payment failed" — "Update your payment method to avoid interruption to your membership." · CTA: "Go to membership" | the module message template (⚠️ payment webhooks partial, R-002) |
| Timestamp format | "2 hours ago" / "Yesterday" / "3 days ago" / "1 week ago" (relative time) | date formatter (relative time) |
| End-of-list state (shown after the 30-day window, not a zero-notification empty state) | "You're all caught up" — "No notifications older than 30 days to show." | View empty-text config |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px: `.container` về `max-width:100%`; `.pagehead` (flex-wrap) xếp tiêu đề "Stay on top of your learning" lên trên, nút "Mark all read" xuống hàng riêng; `.segmented` (filter tabs "All/Unread/Courses/System") wrap xuống nhiều dòng nếu không đủ chỗ; mỗi notification vẫn là 1 `.card` full-width trong `.stack` (danh sách dọc 1 cột, không phải grid), icon + nội dung dùng flex row với `min-width:0` để text co giãn/truncate. |
| tablet | 768–991px: `.container` co về `var(--container-md)` (720px); `.pagehead` và `.toolbar`/`.segmented` thường đủ chỗ nằm trên 1 hàng nhưng vẫn giữ `flex-wrap` dự phòng; danh sách `.card` trong `.stack` vẫn 1 cột dọc. |
| desktop | 992–1199px: `.container` co về `var(--container-lg)` (960px); `.pagehead` hiển thị tiêu đề bên trái và nút "Mark all read" bên phải trên cùng 1 hàng, `.segmented` filter tabs nằm trên 1 hàng; danh sách notification vẫn 1 cột dọc (đây là feed dạng list, không đổi layout theo breakpoint). |
| wide | ≥1200px: `.container` dùng `var(--container-xl)` (1140px, đến 1399.98px) rồi `var(--container-xxl)` (1280px, từ 1400px); layout giống desktop, chỉ tăng khoảng trắng 2 bên do container rộng hơn; không có `.appsidebar`/panel nào trên trang này để ẩn/gập. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| In-app | done | ⚠️ email/push partial (R-005) |
