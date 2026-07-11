---
page: Profile
drupal_concept: user form/view
mode: as-built
owner_module: user
route_group: app
route_name: lms_course.profile
prototype_type: dashboard
adequacy: PASS
---

# C11 Page - Profile

## Purpose
Hồ sơ người dùng tại `/profile` (dashboard): chỉnh sửa thông tin cá nhân, ảnh đại diện, tuỳ chọn thông báo.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/profile` |
| Route name | `lms_course.profile` |
| Primitive | user entity form/view (themed) |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Profile fields | `user` fields (`field_first_name`,`field_last_name`,`field_bio`,`user_picture`,`field_receive_notifications`,…) | own | user tag | edit → updates |

## Page States
Normal: form · Empty: n/a · Access denied: anon → login · Validation error: required → errors · Loading: submit.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Profile tab — CTA lưu | "Save changes" | Form API submit button (`#value`), owner module `user` (profile edit form) — theme override |
| Account tab — CTA đổi mật khẩu | "Change password" | Form API button label, owner module `user` (account edit form, Account tab) |
| Profile tab — avatar upload hint | "Drag a photo or click to upload" / "JPG or PNG, up to 5 MB" | user picture field widget help text — Field UI "Manage form display" |
| Account tab — Delete account (destructive CTA + hint) | "Delete account" / "Permanently remove your account and all learning data." | Account cancellation copy — `admin/config/people/accounts` (cancel method) + module string, owner module `user` |
| Privacy tab — CTA export dữ liệu | "Request export" | module string, owner module `user` (Privacy tab, data export action) |
| Validation error — required/invalid field | (không có chuỗi lỗi cụ thể hiển thị trong prototype tĩnh — không có JS validate demo trên trang) | Form API validation constraints trên field (field first name, email, v.v.), owner module `user` — xem `## Page States` |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (≤575.98px) | `.container.container--narrow` về `max-width:100%` (media `.container` ≤575.98px ghi đè `.container--narrow`); `.tabs` (Profile/Account/Notifications/Privacy) không xuống dòng mà cuộn ngang (`overflow-x:auto`); trong tab Profile, `.grid` avatar+form (inline `grid-template-columns:200px 1fr`) KHÔNG có rule breakpoint riêng nên vẫn giữ 2 cột (ảnh 140px + dropzone cạnh form) thay vì xếp chồng 1 cột; `.formgrid--2` (First/Last name, Website/LinkedIn) về 1 cột (ngưỡng ≤639.98px); mỗi `.switchrow` (Account/Notifications/Privacy) vẫn là 1 hàng flex `justify-content:space-between` (không có rule wrap), nhãn/hint bên trái và toggle/nút bên phải giữ nguyên vị trí |
| tablet (768–991px) | `.container.container--narrow` về `max-width:720px` (`var(--container-md)`, media ≤991.98px ghi đè `.container--narrow`); `.formgrid--2` trở lại 2 cột (trên ngưỡng ≤639.98px); `.grid` avatar+form vẫn 200px/1fr như mobile; `.tabs` thường vừa đủ chiều rộng, không cần cuộn |
| desktop (992–1199px) | `.container.container--narrow` về `max-width:960px` (`var(--container-lg)`, media ≤1199.98px ghi đè `.container--narrow`); bố cục tab/form/switchrow giữ như tablet |
| wide (≥1200px) | 1200–1399px: `max-width:1140px` (`var(--container-xl)`, media ≤1399.98px vẫn ghi đè `.container--narrow`); từ 1400px trở lên không còn media `.container` nào áp dụng nên `.container--narrow{max-width:920px}` (rule không điều kiện) thắng, nội dung co lại 920px; layout tab/panel/formgrid giữ nguyên như desktop |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Profile | done | notification prefs gate INT-008/009 |
