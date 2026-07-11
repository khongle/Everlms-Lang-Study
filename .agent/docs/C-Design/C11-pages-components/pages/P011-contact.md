---
page: Contact
drupal_concept: Form API
mode: as-built
owner_module: lms_course
route_group: public
route_name: lms_course.contact
prototype_type: marketing
adequacy: PASS
standalone: trang tĩnh liên hệ, không phải một bước của workflow giao dịch
---

# C11 Page - Contact

## Purpose
Trang/form liên hệ tại `/contact` (marketing + form).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/contact` |
| Route name | `lms_course.contact` |
| Primitive | controller + contact form |
| Access | anonymous |
| Cache | uncacheable (form) |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Contact info/copy | content block/config | content_editor | block tag | edit → updates |
| Form | Form API | — | — | submit → email/notification |

## Page States
Normal: form · Empty: n/a · Access denied: n/a · Validation error: required fields → errors · Loading: submit.

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Success (gửi form thành công) | "Message sent." / "Thanks for reaching out — a member of our team will reply to you within one business day." | Form API status message (`\Drupal::messenger()->addStatus()`) trong controller/form class của route the module.contact` |
| Validation error — trường bắt buộc (`name`, `email`, `message` có thuộc tính `required`) | "Full name field is required." | Drupal core Form API — thuộc tính `#required` trên phần tử form (thông báo mặc định của `FormValidator`) |
| Validation error — định dạng email không hợp lệ (`type="email"`) | "The email address %mail is not valid." | Drupal core — render element `email` (`\Drupal\Core\Render\Element\Email::validateEmail()`) |
| Placeholder — trường Message | "Tell us how we can help..." | Form API `#placeholder`, form class của route the module.contact` |
| Primary CTA | "Send message" | Form API `#value` của nút submit, form class của route the module.contact` |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile | <576px: `.split` (form + khối thông tin liên hệ) đã xếp chồng 1 cột từ mốc tablet; bên trong form, `.field--row` (Full name + Email address) chuyển từ 2 cột về 1 cột tại `@media(max-width:575.98px)`; `.container` co về `max-width:100%` với padding-inline `--gut`. |
| tablet | 768–991px: `.split` vẫn xếp chồng 1 cột (`@media(max-width:991.98px){.split{grid-template-columns:1fr}}`) — form hiển thị trước, khối thông tin liên hệ + bản đồ (`.mapbox`) xuống dưới; `.field--row` (Full name / Email) vẫn giữ 2 cột vì mốc gộp cột chỉ kích hoạt ở ≤575.98px; `.container` giới hạn `max-width:var(--container-md)` (720px). |
| desktop | 992–1199px: `.split` trở lại bố cục 2 cột ngang hàng (form bên trái, thông tin liên hệ + bản đồ bên phải); `.container` giới hạn `max-width:var(--container-lg)` (960px). |
| wide | ≥1200px: bố cục 2 cột của `.split` giữ nguyên, khoảng cách cột theo `clamp(1.5rem,4vw,3.5rem)`; `.container` đạt `max-width:var(--container-xl)` (1140px), hoặc mở rộng tới `--maxw` (1280px) khi viewport >1399.98px. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Contact | done | delivery via INT-008 |
