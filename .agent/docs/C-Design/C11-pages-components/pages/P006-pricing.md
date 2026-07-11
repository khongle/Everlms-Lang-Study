---
page: Pricing
drupal_concept: controller + entity list
mode: as-built
owner_module: lms_membership
route_group: public
route_name: lms_membership.pricing
prototype_type: marketing
adequacy: PASS
---

# C11 Page - Pricing

## Purpose
Các gói membership/pricing tại `/pricing` (marketing archetype): plan card + CTA subscribe.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/pricing` |
| Route name | `lms_membership.pricing` |
| Primitive | controller listing `lms_membership_plan` |
| Access | anonymous |
| Cache | tags: plan list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Plan cards | `lms_membership_plan` (+`field_features`,`field_recommended`) | manager | plan tag | add plan → card |

## Page States
Normal: plan cards · Empty: none · Access denied: n/a · Validation error: n/a · Loading: lazy.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Free plan CTA | "Get started free" | the module.pricing controller/template — Free plan card link |
| Pro plan CTA | "Start Pro" | the module.pricing controller/template — Pro plan card link → P030 checkout |
| Teams plan CTA | "Start Teams" | the module.pricing controller/template — Teams plan card link → P030 checkout |
| Pro plan highlight badge | "Most popular" | field recommended trên the module → plan card markup |
| Annual billing toggle savings | "Save 17%" | the module.pricing template — billing toggle labels |
| Bottom CTA band primary | "Start Pro today" | the module.pricing template — CTA band → P030 checkout |
| Bottom CTA band secondary | "Talk to sales" | the module.pricing template — CTA band → P011 contact |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | ở ≤575.98px: `.container` co về 100% width; lưới `.plans` đã ở 1 cột (kích hoạt từ breakpoint ≤991.98px) và giới hạn `max-width:460px; margin-inline:auto` để card Free/Pro/Teams không bị kéo giãn quá rộng; billing `.toggle` (Monthly/Annual) vẫn giữ dạng pill ngang; bảng so sánh `.compare` nằm trong `.wtable__wrap{overflow-x:auto}` nên cuộn ngang thay vì rớt dòng. |
| tablet | 768–991px: `.plans{grid-template-columns:1fr}` (áp dụng tới hết 991.98px) nên 3 plan card xếp dọc theo thứ tự Free → Pro → Teams; card `.plan--feature` (Pro, có tag "Most popular") bỏ `transform:scale(1.02)` để không đè lên card lân cận khi xếp 1 cột. |
| desktop | 992–1199px: `.plans{grid-template-columns:repeat(3,1fr)}` hiển thị đủ 3 cột Free/Pro/Teams cạnh nhau; card Pro (`.plan--feature`) phóng to nhẹ `scale(1.02)` kèm `box-shadow` nổi bật và tag "Most popular"; bảng `.compare` hiển thị đủ 4 cột (Feature/Free/Pro/Teams) không cần cuộn ngang. |
| wide | ≥1200px: `.container` mở rộng theo `--container-xl` (1140px), rồi `--container-xxl` (1280px) ở ≥1400px; layout 3 cột `.plans` và bảng `.compare` giữ nguyên, chỉ tăng khoảng lề hai bên do container giới hạn max-width. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Pricing page | done | subscribe → checkout P030 |
