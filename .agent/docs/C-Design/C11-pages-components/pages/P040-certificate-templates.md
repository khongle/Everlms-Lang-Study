---
page: Certificate templates
drupal_concept: entity form + preview
mode: as-built
owner_module: lms_certificate
route_group: manage
route_name: lms_certificate.templates
form_mode: manage_edit
prototype_type: form
adequacy: PASS
---

# C11 Page - Certificate templates

## Purpose
Editor quản lý layout certificate tại `/workspace/certificate-templates`: thiết kế, logo/chữ ký/con dấu, màu sắc, hướng trang; xem trước (preview). Đây là điểm neo biên tập (editorial anchor) cho F-007.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/workspace/certificate-templates` (add, `/{template}/edit`, `/{template}/preview`) |
| Route name | `lms_certificate.templates` |
| Drupal page primitive | entity form + mPDF preview |
| Owner module | lms_certificate |
| Access | content_editor / manager |
| Cache | uncacheable (form) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| — | (form only) | content | — |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Fields | body, design/layout/orientation/color_scheme, background/logo/signature/seal images |
| Preview | render to PDF preview before save |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Template fields | `lms_certificate_template` | manage cert templates | template tag | edit → new certs use layout |
| Preview PDF | mPDF render | editor | — | preview shows changes |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | layout/branding in entity fields | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | template list + edit form |
| Empty | no templates → create CTA |
| Access denied | non-editor → 403 |
| Validation error | required fields → inline |
| Loading | preview render |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA — save draft | "Save draft" | prototype P040-certificate-templates.html — `.pagehead__actions .btn--ghost` |
| CTA — publish | "Publish" | prototype P040-certificate-templates.html — `.pagehead__actions .btn--primary` |
| Hint — body merge tokens | "Use the merge tokens above — they are replaced automatically when a certificate is issued." | prototype P040-certificate-templates.html — `.hint` dưới trường Body text (field body) |
| Hint — background upload | "Drag a background image here, or browse files" · "Recommended 2000×1414px, JPG or PNG, under 4MB." | prototype P040-certificate-templates.html — `.dropzone` (field background) |
| Hint — live preview | "Preview updates as you edit the fields on the left. Regenerate to apply changes to already-issued certificates." | prototype P040-certificate-templates.html — `.panel` Live preview |
| Validation error — required name | "Template name is required." | suy luận hợp lý (form validation bắt buộc) — field `name` / C04 the module.md |
| Validation error — file format | "Please upload a JPG or PNG under 4MB." | suy luận hợp lý từ hint `.dropzone` ở trên — field field background |
| Empty state | "You haven't created any certificate templates yet." + CTA "Create your first template" | suy luận hợp lý (Page States: "no templates → create CTA"; chưa có mockup rỗng trong prototype) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.field--row` (Title/Orientation, Signatory name/title) gập về 1 cột (`@media(max-width:575.98px)`); `.grid--3` (Existing templates) về 1 cột (`@media(max-width:639.98px)`); `.dropzone` co theo chiều rộng khung nội dung |
| tablet (768–991px) | `.editor` two-pane (form nội dung + `.editor__aside` Live preview/Status) gập về 1 cột (`@media(max-width:991.98px)`), aside xuống dưới form; `.grid--3` còn 2 cột |
| desktop (992–1199px) | `.editor` đủ 2 cột (form + `.editor__aside` rộng 340px, sticky `top:90px`); `.field--row` 2 cột; `.grid--3` đủ 3 cột |
| wide (≥1200px) | `.container` đạt `--container-xl`; layout giữ nguyên với lề rộng hơn, aside preview vẫn sticky |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Template editor + preview | done | re-render via regenerate script |
