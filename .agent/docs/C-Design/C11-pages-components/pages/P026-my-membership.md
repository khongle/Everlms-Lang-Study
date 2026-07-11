---
page: My membership
drupal_concept: controller + cancel form
mode: as-built
owner_module: lms_membership
route_group: app
route_name: lms_membership.my_membership
prototype_type: detail
adequacy: PASS
---

# C11 Page - My membership

## Purpose
Trạng thái gói thuê bao tại `/my-membership` (detail): gói, hạn sử dụng, huỷ gói.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-membership` (cancel `/my-membership/{m}/cancel`) |
| Route name | `lms_membership.my_membership` |
| Primitive | controller + cancel action |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Membership | `lms_user_membership` (own) | own | per-user | subscribe → shows; cancel → status changes |

## Page States
Normal: status · Empty: none → pricing · Access denied: anon → login · Validation error: cancel confirm · Loading: action.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Current plan — status badge | "Active" | the module controller/Twig template string (render của field status) |
| Current plan — CTA đổi gói | "Change plan" | the module controller/Twig template string, liên kết sang P006-pricing |
| Current plan — CTA hủy gói | "Cancel subscription" | the module controller/Twig template string, trỏ route cancel `/my-membership/{m}/cancel` |
| Payment failed alert — tiêu đề | "We couldn't charge your card ending 4242" | the module controller/Twig template string (render khi invoice gần nhất `status--error`) |
| Payment failed alert — nội dung | "The payment for your Jul 9 renewal failed. Update your payment method within 5 days to keep uninterrupted access to Pro benefits." | the module controller/Twig template string |
| Payment failed alert — CTA | "Update payment method" | the module controller/Twig template string, liên kết sang P030-checkout-membership |
| Billing history — trạng thái invoice lỗi | "Failed" | the module controller/Twig template string (render trạng thái invoice) |
| Billing history — trạng thái invoice thành công | "Paid" | the module controller/Twig template string (render trạng thái invoice) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (≤575.98px) | `.container` rộng 100% (không giới hạn max-width); `.pagehead` (tiêu đề "Manage your plan and billing" + nút "Compare plans") xếp chồng dọc nhờ `flex-wrap`; card gói hiện tại (`.panel`) vẫn giữ nguyên bố cục 1 cột dọc (không chia cột ở bất kỳ breakpoint nào); hàng số liệu Price/Next renewal/Member since (`.grid--3`) về 1 cột (ngưỡng ≤639.98px, đã áp dụng từ mobile); hàng 4 KPI sử dụng trong tháng (`.kpis`) về 1 cột (ngưỡng ≤479.98px, ở dải 480–575px vẫn hiển thị 2 cột); `.btn-row` (nút "Change plan"/"Cancel subscription") xếp chồng khi không đủ chỗ nhờ `flex-wrap`; bảng billing history cuộn ngang trong `.wtable__wrap` (`overflow-x:auto`) thay vì vỡ layout |
| tablet (768–991px) | `.container` max-width = `var(--container-md)` (720px); `.grid--3` (Price/Next renewal/Member since) hiển thị 2 cột (ngưỡng chuyển cột là ≤991.98px nên áp dụng suốt dải tablet); `.kpis` (4 KPI usage) cũng hiển thị 2 cột (cùng ngưỡng ≤991.98px); `.pagehead` và `.btn-row` vẫn tự ngắt dòng qua `flex-wrap` khi cần; bảng billing history có thể vẫn cuộn ngang nếu bề rộng không đủ cho 5 cột |
| desktop (992–1199px) | `.container` max-width = `var(--container-lg)` (960px); `.grid--3` trở lại đủ 3 cột (Price/Next renewal/Member since) vì đã vượt ngưỡng 991.98px; `.kpis` trở lại đủ 4 cột; bảng billing history hiển thị đủ 5 cột (Invoice/Date/Amount/Status/action) không cần cuộn |
| wide (≥1200px) | `.container` max-width = `var(--container-xl)` (1140px) ở dải ≤1399.98px, sau đó dùng biến `--container` mặc định (`--container-xxl`, 1280px) ở ≥1400px; bố cục cột giữ nguyên như desktop (`.grid--3` đủ 3 cột, `.kpis` đủ 4 cột); `.workhero` padding co giãn theo `clamp()` thay vì nhảy bậc thêm |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Membership status | done | auto-renew flag |
